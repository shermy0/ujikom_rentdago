<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
// Storage facade dihapus — menggunakan move() ke public/ langsung
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * CategoryController — Mengelola data kategori produk oleh Admin.
 *
 * Menyediakan fitur CRUD lengkap untuk kategori hierarkis (parent & sub-kategori),
 * termasuk upload icon, generate slug unik, dan validasi sebelum penghapusan.
 */
class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori dengan pencarian dan filter parent.
     *
     * Secara default hanya menampilkan kategori root (tanpa parent_id).
     * Sub-kategori di-render lewat nested loop di view, bukan query utama.
     * Mendukung pencarian berdasarkan nama di parent maupun sub-kategori.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Selalu mulai dari root (parent) kategori saja
        // Sub-kategori di-render lewat nested loop di view
        $query = Category::with(['parent', 'children'])
            ->whereNull('parent_id');

        // Pencarian berdasarkan nama (di parent dan sub-kategorinya)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('children', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan parent: tampilkan sub-kategori dari parent yang dipilih
        if ($request->filled('parent') && $request->parent !== 'root') {
            $query = Category::with(['parent', 'children'])
                ->where('parent_id', $request->parent);
        }

        $categories = $query->orderBy('name', 'asc')->paginate(10);

        // Ambil semua kategori root untuk dropdown filter
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
     * Menampilkan form untuk membuat kategori baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ambil semua kategori root untuk dropdown pilihan parent
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
     * Menyimpan kategori baru ke database.
     *
     * Melakukan validasi input, generate slug unik dari nama kategori,
     * upload icon ke storage, lalu menyimpan data kategori.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
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

        // Generate slug unik dari nama kategori (ditambahkan angka jika sudah ada)
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

        // Upload icon kategori jika ada file yang dikirim
        if ($request->hasFile('icon')) {
            $filename = time() . '_' . uniqid() . '.' . $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('categories'), $filename);
            $categoryData['icon'] = 'categories/' . $filename;
        }

        Category::create($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kategori.
     *
     * Mengambil semua kategori root kecuali diri sendiri untuk dropdown pilihan parent
     * (mencegah loop hierarki tak terbatas).
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\View\View
     */
    public function edit(Category $category)
    {
        // Ambil semua kategori root kecuali kategori saat ini untuk dropdown parent
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
     * Memperbarui data kategori di database.
     *
     * Mencegah kategori menjadi parent dirinya sendiri dengan Rule::notIn.
     * Jika nama berubah, slug di-generate ulang secara unik.
     * Icon lama dihapus sebelum icon baru disimpan (jika ada upload baru).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                // Cegah kategori menjadi parent dirinya sendiri
                Rule::notIn([$category->id]),
            ],
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $categoryData = [
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
        ];

        // Generate ulang slug jika nama berubah
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

        // Upload icon baru jika ada file yang dikirim
        if ($request->hasFile('icon')) {
            // Hapus icon lama dari public sebelum menyimpan yang baru
            if ($category->icon && file_exists(public_path($category->icon))) {
                @unlink(public_path($category->icon));
            }
            $filename = time() . '_' . uniqid() . '.' . $request->file('icon')->getClientOriginalExtension();
            $request->file('icon')->move(public_path('categories'), $filename);
            $categoryData['icon'] = 'categories/' . $filename;
        }

        // Hapus icon jika checkbox "Hapus Icon" dicentang oleh admin
        if ($request->has('remove_icon') && $request->remove_icon) {
            if ($category->icon && file_exists(public_path($category->icon))) {
                @unlink(public_path($category->icon));
            }
            $categoryData['icon'] = null;
        }

        $category->update($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori dari database.
     *
     * Penghapusan ditolak jika kategori masih memiliki sub-kategori
     * atau masih digunakan oleh produk yang ada.
     *
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Cek apakah kategori memiliki sub-kategori
        if ($category->hasChildren()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena memiliki sub-kategori.');
        }

        // Cek apakah kategori sedang digunakan oleh produk
        if ($category->isInUse()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Kategori tidak dapat dihapus karena sedang digunakan oleh produk.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
