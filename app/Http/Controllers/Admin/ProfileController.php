<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * ProfileController — Mengelola profil akun Admin.
 *
 * Menyediakan fitur melihat profil, mengedit nama/nomor HP/avatar,
 * serta mengubah password dengan verifikasi password lama.
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profil admin yang sedang login.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Menampilkan form edit profil admin.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Memperbarui data profil admin (nama, nomor HP, dan avatar).
     *
     * Jika ada foto avatar baru, foto lama akan dihapus terlebih dahulu
     * sebelum foto baru disimpan ke storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return redirect()->route('admin.profile.index')
            ->with('sukses', 'Profil berhasil diperbarui!');
    }

    /**
     * Memperbarui password admin.
     *
     * Memvalidasi password lama sebelum menyimpan password baru yang sudah di-hash.
     * Mengembalikan error jika password lama tidak cocok.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai!']);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('admin.profile.index')
            ->with('sukses', 'Password berhasil diperbarui!');
    }
}
