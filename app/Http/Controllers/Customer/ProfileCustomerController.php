<?php

namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Otp;
use App\Models\SellerRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


/**
 * ProfileCustomerController — Mengelola profil akun Customer.
 *
 * Menyediakan fitur melihat profil, mengedit data diri (nama, HP, avatar),
 * serta alur verifikasi 2 tahap berbasis OTP WhatsApp ketika nomor HP berubah.
 * Juga menangani alur reset password dengan atau tanpa password lama.
 */
class ProfileCustomerController extends Controller
{
    /**
     * Menampilkan halaman profil customer yang sedang login.
     *
     * Turut mengambil data pengajuan seller request terakhir (jika ada)
     * untuk menampilkan status pengajuan menjadi seller.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil pengajuan seller terakhir user (jika ada)
        $sellerRequest = SellerRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return view('customer.profile.index', compact('user', 'sellerRequest'))->with('title', 'Profil');
    }

    /**
     * Menampilkan form edit profil customer.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        return view('customer.profile.edit', compact('user'))->with('title', 'Ubah Profil');
    }

    /**
     * Kirim OTP via WhatsApp
     */
    private function kirimOtp($phone, $nama, $otpCode)
    {
        // Format pesan
        $pesan = "Halo *{$nama}*,\n\n";
        $pesan .= "Kode OTP untuk verifikasi perubahan nomor WhatsApp Anda adalah:\n\n";
        $pesan .= "*{$otpCode}*\n\n";
        $pesan .= "Kode berlaku selama *1 menit*.\n";
        $pesan .= "Jangan bagikan kode ini kepada siapapun!\n\n";
        $pesan .= "_Terima kasih telah menggunakan layanan kami._";
        
        // Panggil helper function kirimWa
        $result = kirimWa($phone, $pesan);
        
        // Log hasil
        if ($result['success']) {
            \Log::info('✅ OTP berhasil dikirim ke ' . $phone);
        } else {
            \Log::error('❌ Gagal kirim OTP ke ' . $phone . ': ' . $result['message']);
        }
        
        return $result;
    }

    /**
     * Memperbarui data profil customer (nama, nomor HP, dan avatar).
     *
     * Jika nomor HP berubah, alur verifikasi 2 tahap via OTP WhatsApp dijalankan:
     * 1. OTP dikirim ke nomor LAMA untuk konfirmasi identitas
     * 2. Setelah terverifikasi, OTP dikirim ke nomor BARU
     * Jika nomor HP tidak berubah, data langsung disimpan.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Update nama
        $user->name = $request->name;

        // Cek apakah nomor telepon berubah
        $phoneChanged = $user->phone !== $request->phone;

        // Update avatar jika ada
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }

            // Upload avatar baru
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        // Jika nomor telepon berubah, mulai proses verifikasi 2 tahap
        if ($phoneChanged) {
            $oldPhone = $user->phone; // Nomor lama (saat ini)
            $newPhone = $request->phone; // Nomor baru yang akan diset

            // Hapus OTP lama untuk nomor lama (jika ada)
            Otp::where('phone', $oldPhone)->delete();

            // Generate OTP baru untuk TAHAP 1
            $otpCode = rand(100000, 999999);

            // Simpan OTP dengan nomor LAMA sebagai key
            Otp::create([
                'user_id'    => $user->id,
                'phone'      => $oldPhone, // Kirim ke nomor lama
                'code'       => $otpCode,
                'expired_at' => Carbon::now()->addMinutes(1),
            ]);

            // Kirim OTP ke nomor LAMA untuk verifikasi
            $this->kirimOtp($oldPhone, $user->name, $otpCode);

            // Simpan data pending ke session
            session([
                'pending_phone_change' => $newPhone, // Nomor baru yang akan di-set setelah verifikasi
                'verification_phone' => $oldPhone, // Nomor lama untuk verifikasi OTP
                'verification_step' => 1, // TAHAP 1: Verifikasi nomor lama
                'pending_user_data' => [
                    'name' => $user->name,
                    'avatar' => $user->avatar
                ]
            ]);

            // Redirect ke halaman verify OTP TAHAP 1 dengan nomor LAMA
            return redirect()->route('profile.verify.otp', ['phone' => $oldPhone])
                ->with('info', 'Kode OTP telah dikirim ke nomor lama Anda untuk verifikasi perubahan.');
        }

        // Jika nomor tidak berubah, langsung simpan
        $user->save();

        return redirect()->route('profile.index')->with('sukses', 'Profil berhasil diperbarui!');
    }

    /**
     * Tampilkan halaman verify OTP untuk profile
     */
    public function showVerifyOtp(Request $request)
    {
        // Cek apakah ada pending phone change
        if (!session('pending_phone_change') && !session('verification_phone') && !session('password_reset_phone')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Tidak ada perubahan nomor telepon yang perlu diverifikasi');
        }

        $step = session('verification_step', 1);
        $phone = session('verification_phone') ?? session('password_reset_phone'); // Nomor yang akan diverifikasi
        $newPhone = session('pending_phone_change'); // Nomor baru (tujuan akhir)
        $isPasswordReset = session('is_password_reset', false);

        return view('customer.profile.verify-otp', compact('phone', 'newPhone', 'step', 'isPasswordReset'));
    }

    /**
     * Verify OTP untuk profile (2 TAHAP)
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string',
        ]);

        $otp = Otp::where('phone', $request->phone)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP tidak valid'], 400);
        }

        if (Carbon::now()->gt($otp->expired_at)) {
            return response()->json(['message' => 'OTP sudah kadaluarsa'], 400);
        }

        $user = Auth::user();
        
        // Cek apakah ini untuk reset password
        if (session('is_password_reset')) {
            // Hapus OTP
            $otp->delete();
            
            // Simpan flag verified di session
            session(['otp_verified_for_password_reset' => true]);
            
            return response()->json([
                'message' => 'Verifikasi berhasil! Silakan buat password baru.',
                'step' => 'password_reset',
                'redirect' => route('profile.reset.password.form')
            ]);
        }

        $step = session('verification_step', 1);

        // TAHAP 1: Verifikasi nomor LAMA berhasil
        if ($step == 1) {
            // Hapus OTP lama
            $otp->delete();

            // Ambil nomor baru dari session
            $newPhone = session('pending_phone_change');

            // Generate OTP baru untuk TAHAP 2 (nomor baru)
            $newOtpCode = rand(100000, 999999);

            // Hapus OTP lama untuk nomor baru (jika ada)
            Otp::where('phone', $newPhone)->delete();

            // Simpan OTP untuk nomor BARU
            Otp::create([
                'user_id'    => $user->id,
                'phone'      => $newPhone, // Kirim ke nomor baru
                'code'       => $newOtpCode,
                'expired_at' => Carbon::now()->addMinutes(1),
            ]);

            // Kirim OTP ke nomor BARU
            $this->kirimOtp($newPhone, $user->name, $newOtpCode);

            // Update session untuk TAHAP 2
            session([
                'verification_step' => 2,
                'verification_phone' => $newPhone // Sekarang verifikasi nomor baru
            ]);

            return response()->json([
                'message' => 'Verifikasi nomor lama berhasil! Kode OTP telah dikirim ke nomor baru Anda.',
                'step' => 2,
                'next_phone' => $newPhone,
                'redirect' => route('profile.verify.otp', ['phone' => $newPhone])
            ]);
        }

        // TAHAP 2: Verifikasi nomor BARU berhasil
        if ($step == 2) {
            // Update nomor telepon user dengan nomor BARU dari session
            $newPhone = session('pending_phone_change');
            
            if ($newPhone) {
                $user->phone = $newPhone;
            }
            
            // Update data lain dari session jika ada
            if (session('pending_user_data')) {
                $pendingData = session('pending_user_data');
                $user->name = $pendingData['name'];
                if (isset($pendingData['avatar'])) {
                    $user->avatar = $pendingData['avatar'];
                }
            }
            
            $user->save();

            // Hapus OTP setelah sukses
            $otp->delete();

            // Hapus session
            session()->forget([
                'pending_phone_change', 
                'verification_phone', 
                'pending_user_data',
                'verification_step'
            ]);

            return response()->json([
                'message' => 'Nomor berhasil diverifikasi dan diperbarui',
                'step' => 'complete',
                'redirect' => route('profile.index')
            ]);
        }

        return response()->json(['message' => 'Terjadi kesalahan pada proses verifikasi'], 400);
    }

    /**
     * Resend OTP untuk profile
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = Auth::user();

        // Hapus OTP lama
        Otp::where('phone', $request->phone)->delete();

        // Generate OTP baru
        $otpCode = rand(100000, 999999);

        Otp::create([
            'user_id'    => $user->id,
            'phone'      => $request->phone,
            'code'       => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(1),
        ]);

        // Kirim OTP baru ke WhatsApp
        $kirimResult = $this->kirimOtp($request->phone, $user->name, $otpCode);

        // Cek hasil pengiriman
        if (!$kirimResult['success']) {
            \Log::warning('⚠️ OTP tersimpan di database tapi gagal dikirim ke WhatsApp: ' . $kirimResult['message']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru telah dikirim'
        ], 200);
    }

    /**
     * Menampilkan form reset password menggunakan password lama.
     *
     * @return \Illuminate\View\View
     */
    public function showResetPassword()
    {
        $user = Auth::user();
        return view('customer.profile.reset-password', compact('user'));
    }

    /**
     * Proses reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = Auth::user();

        // Validasi password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai'
            ])->withInput();
        }

        // Cek apakah password baru sama dengan password lama
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'Password baru tidak boleh sama dengan password lama'
            ])->withInput();
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Logout user dan redirect ke login
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('sukses', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }

    /**
     * Mengirim OTP ke nomor HP customer untuk keperluan reset password
     * tanpa perlu memasukkan password lama.
     *
     * OTP disimpan ke database terlebih dahulu sebelum dikirim.
     * Jika pengiriman WhatsApp gagal, OTP tetap valid dan user dapat
     * meminta kirim ulang melalui tombol "Kirim Ulang".
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestPasswordResetOtp(Request $request)
{
    $user = Auth::user();
    $phone = $user->phone;

    // Hapus OTP lama
    Otp::where('phone', $phone)->delete();

    // Generate OTP baru
    $otpCode = rand(100000, 999999);

    // Simpan OTP ke database TERLEBIH DAHULU
    Otp::create([
        'user_id'    => $user->id,
        'phone'      => $phone,
        'code'       => $otpCode,
        'expired_at' => Carbon::now()->addMinutes(1),
    ]);

    // Simpan session untuk password reset
    session([
        'password_reset_phone' => $phone,
        'is_password_reset' => true
    ]);

    // Kirim OTP ke WhatsApp (NON-BLOCKING)
    // Jika gagal kirim, OTP tetap tersimpan dan user bisa input manual
    try {
        $pesan = "Halo *{$user->name}*,\n\n";
        $pesan .= "Kode OTP untuk reset password Anda adalah:\n\n";
        $pesan .= "*{$otpCode}*\n\n";
        $pesan .= "Kode berlaku selama *1 menit*.\n";
        $pesan .= "Jangan bagikan kode ini kepada siapapun!\n\n";
        $pesan .= "_Terima kasih telah menggunakan layanan kami._";
        
        // Kirim dengan timeout yang lebih pendek dan async handling
        $kirimResult = kirimWa($phone, $pesan);

        if (!$kirimResult['success']) {
            \Log::warning('⚠️ OTP tersimpan tapi gagal dikirim ke WhatsApp: ' . $kirimResult['message']);
            
            // Tetap redirect ke halaman verify, tapi beri info tambahan
            return response()->json([
                'success' => true,
                'message' => 'OTP berhasil dibuat. Jika tidak menerima WhatsApp, silakan hubungi admin atau coba lagi.',
                'warning' => 'Pengiriman WhatsApp mengalami kendala',
                'redirect' => route('profile.verify.otp', ['phone' => $phone])
            ]);
        }

        // Berhasil kirim
        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke nomor Anda',
            'redirect' => route('profile.verify.otp', ['phone' => $phone])
        ]);

    } catch (\Exception $e) {
        // Log error tapi tetap lanjutkan proses
        \Log::error('❌ Error saat kirim OTP (non-fatal): ' . $e->getMessage());
        
        // User tetap bisa lanjut verifikasi karena OTP sudah tersimpan
        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dibuat. Jika tidak menerima WhatsApp dalam 1 menit, silakan gunakan tombol "Kirim Ulang".',
            'warning' => 'Terjadi kendala teknis saat mengirim WhatsApp',
            'redirect' => route('profile.verify.otp', ['phone' => $phone])
        ]);
    }
}

    /**
     * Tampilkan form reset password baru (setelah verifikasi OTP)
     */
    public function showResetPasswordForm()
    {
        // Cek apakah OTP sudah diverifikasi
        if (!session('otp_verified_for_password_reset')) {
            return redirect()->route('profile.reset.password')
                ->with('error', 'Silakan verifikasi OTP terlebih dahulu');
        }

        $user = Auth::user();
        return view('customer.profile.reset-password-form', compact('user'));
    }

    /**
     * Update password baru (setelah verifikasi OTP)
     */
    public function updatePasswordWithOtp(Request $request)
    {
        // Cek apakah OTP sudah diverifikasi
        if (!session('otp_verified_for_password_reset')) {
            return back()->with('error', 'Silakan verifikasi OTP terlebih dahulu');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = Auth::user();

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Hapus session
        session()->forget([
            'otp_verified_for_password_reset',
            'password_reset_phone',
            'is_password_reset'
        ]);

        // Logout user dan redirect ke login
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('sukses', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }
}