<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('user_verified_at');
            } else {
                $query->whereNull('user_verified_at');
            }
        }

        // Get paginated users
        $users = $query->latest()->paginate(10);

        return view('admin.users.index', compact('users'))->with('title', 'Data User');
    }

    /**
     * Show user detail
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'))->with('title', 'Detail User');
    }


    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'role' => 'required|in:admin,seller,customer',
            'password' => 'required|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload avatar if exists
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Auto verify phone & user untuk admin
        $validated['phone_verified_at'] = now();
        if ($validated['role'] !== 'seller') {
            $validated['user_verified_at'] = now();
        }

        // Create user
        User::create($validated);

        return redirect()->route('admin.users.index')->with('sukses', 'User berhasil ditambahkan!');
    }
}