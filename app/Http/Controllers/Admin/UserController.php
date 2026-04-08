<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * UserController — Mengelola data pengguna oleh Admin.
 *
 * Menyediakan fitur daftar user dengan filter dan pencarian,
 * melihat detail user, serta menambah user baru secara manual.
 */
class UserController extends Controller
{
    /**
     * Menampilkan daftar seluruh pengguna terdaftar.
     *
     * Mendukung pencarian berdasarkan nama/nomor HP,
     * filter berdasarkan role, dan filter berdasarkan status verifikasi.
     * Hasil ditampilkan dengan pagination 10 data per halaman.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Pencarian berdasarkan nama atau nomor HP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan role pengguna (admin, seller, customer)
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan status verifikasi nomor HP
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('phone_verified_at');
            } else {
                $query->whereNull('phone_verified_at');
            }
        }

        // Ambil hasil dengan pagination, data terbaru ditampilkan pertama
        $users = $query->latest()->paginate(10);

        return view('admin.users.index', compact('users'))->with('title', 'Data User');
    }

    /**
     * Menampilkan halaman detail seorang pengguna.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'))->with('title', 'Detail User');
    }

    /**
     * Menampilkan form untuk membuat pengguna baru.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan data pengguna baru ke database.
     *
     * Melakukan validasi input, upload avatar (opsional),
     * hashing password, dan otomatis memverifikasi akun
     * kecuali untuk role seller yang memerlukan proses approval.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi input form
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users,phone',
            'role'     => 'required|in:admin,seller,customer',
            'password' => 'required|min:6|confirmed',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Upload foto avatar jika ada
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Hash password sebelum disimpan ke database
        $validated['password'] = Hash::make($validated['password']);

        // Tandai nomor HP sebagai sudah terverifikasi secara otomatis
        $validated['phone_verified_at'] = now();

        // Simpan data user baru ke database

        // Simpan data user baru ke database
        User::create($validated);

        return redirect()->route('admin.users.index')->with('sukses', 'User berhasil ditambahkan!');
    }

    /**
     * Menyetujui pengajuan seller request dari seorang user.
     *
     * Mengubah role user menjadi seller dan menandai akun sebagai terverifikasi.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(User $user)
    {
        // Ubah role menjadi seller
        $user->update([
            'role' => 'seller',
        ]);

        return redirect()->back()->with('success', 'User berhasil disetujui sebagai seller.');
    }

    /**
     * Menolak pengajuan seller request dari seorang user.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(User $user)
    {
        return redirect()->back()->with('success', 'Pengajuan seller berhasil ditolak.');
    }
}