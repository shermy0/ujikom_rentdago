<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        // Selalu mulai dari root (parent) kategori saja
        // Children di-render lewat nested loop di view, bukan query utama
        $query = Category::with(['parent', 'children'])
            ->whereNull('parent_id');

        // Search by name — cari di parent dan anak-anaknya
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('children', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by parent: tampilkan children dari parent yg dipilih
        if ($request->filled('parent') && $request->parent !== 'root') {
            $query = Category::with(['parent', 'children'])
                ->where('parent_id', $request->parent);
        }

        $categories = $query->orderBy('name', 'asc')->paginate(10);

        // Get root categories for filter dropdown
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        $data = [
            'title' => 'Kategori Barang',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Kategori Barang', 'url' => '#'],
            ],
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ];

        return view('admin.categories.index', $data)->with('title', 'Kategori Barang');
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        // Get all categories for parent dropdown
        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        $data = [
            'title' => 'Tambah Kategori',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Kategori Barang', 'url' => route('admin.categories.index')],
                ['title' => 'Tambah Kategori', 'url' => '#'],
            ],
            'parentCategories' => $parentCategories,
        ];

        return view('admin.categories.create', $data);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'icon.required' => 'Icon kategori wajib diupload.',
            'icon.image' => 'File yang diupload harus berupa gambar.',
            'icon.mimes' => 'Format gambar harus jpeg, png, jpg, gif, svg, atau webp.',
            'icon.max' => 'Ukuran gambar maksimal 2MB.'
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $categoryData = [
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('categories', 'public');
            $categoryData['icon'] = $iconPath;
        }

        Category::create($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        // Get all root categories except current and its children for parent dropdown
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        $data = [
            'title' => 'Edit Kategori',
            'breadcrumbs' => [
                ['title' => 'Admin', 'url' => route('admin.dashboard')],
                ['title' => 'Kategori Barang', 'url' => route('admin.categories.index')],
                ['title' => 'Edit Kategori', 'url' => '#'],
            ],
            'category' => $category,
            'parentCategories' => $parentCategories,
        ];

        return view('admin.categories.edit', $data);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                // Prevent setting self as parent
                Rule::notIn([$category->id]),
            ],
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $categoryData = [
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
        ];

        // Generate unique slug if name changed
        if ($category->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $categoryData['slug'] = $slug;
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }
            
            $iconPath = $request->file('icon')->store('categories', 'public');
            $categoryData['icon'] = $iconPath;
        }

        // Handle remove icon checkbox
        if ($request->has('remove_icon') && $request->remove_icon) {
            if ($category->icon && Storage::disk('public')->exists($category->icon)) {
                Storage::disk('public')->delete($category->icon);
            }
            $categoryData['icon'] = null;
        }

        $category->update($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->hasChildren()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena memiliki sub-kategori.');
        }

        // Check if category is being used by products
        if ($category->isInUse()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena sedang digunakan oleh produk.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
