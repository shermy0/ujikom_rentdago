<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * SettingController — Mengelola pengaturan umum aplikasi oleh Admin.
 *
 * Menyediakan fitur melihat dan memperbarui konfigurasi aplikasi seperti
 * nama aplikasi, logo, alamat, jam operasional, integrasi WhatsApp,
 * serta konfigurasi payment gateway Midtrans.
 */
class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan aplikasi.
     *
     * Mengambil satu baris data dari tabel `settings` yang berisi
     * seluruh konfigurasi umum aplikasi.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil data pengaturan (hanya ada 1 baris di tabel settings)
        $setting = DB::table('settings')->first();

        return view('admin.settings.index', [
            'title'       => 'Pengaturan',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['title' => 'Pengaturan', 'url' => '#'],
            ],
            'setting' => $setting,
        ]);
    }

    /**
     * Memperbarui pengaturan aplikasi.
     *
     * Melakukan validasi input, menangani upload logo baru
     * (termasuk menghapus logo lama dari storage), lalu menyimpan
     * seluruh perubahan ke tabel `settings`.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Validasi seluruh field pengaturan aplikasi
        $request->validate([
            'app_name'             => 'required|string|max:100',
            'about'                => 'nullable|string',
            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'wa_endpoint_url'      => 'nullable|url',
            'wa_token'             => 'nullable|string',
            'wa_sender'            => 'nullable|string',
            'address'              => 'nullable|string',
            'open_time'            => 'nullable|string',
            'document_description' => 'nullable|string',
            'footer_text'          => 'nullable|string',
            'midtrans_mode'        => 'nullable|in:sandbox,production',
            'midtrans_client_key'  => 'nullable|string',
            'midtrans_server_key'  => 'nullable|string',
        ]);

        // Siapkan data untuk disimpan, kecuali token CSRF dan logo (ditangani terpisah)
        $data = $request->except(['_token', '_method', 'logo']);

        // Tangani upload logo baru jika ada file yang dikirim
        if ($request->hasFile('logo')) {
            $setting = DB::table('settings')->first();

            // Hapus logo lama dari storage jika ada, sebelum menyimpan yang baru
            if ($setting && $setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            // Simpan logo baru ke folder logos di storage public
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Catat waktu perubahan terakhir
        $data['updated_at'] = now();

        // Perbarui data di tabel settings (update semua kolom sekaligus)
        DB::table('settings')->update($data);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
