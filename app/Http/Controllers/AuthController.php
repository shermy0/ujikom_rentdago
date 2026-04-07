<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Kirim OTP via WhatsApp
     */
    private function kirimOtp($phone, $nama, $otpCode)
    {
        // ✅ NORMALISASI FORMAT NOMOR HP
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Konversi format Indonesia ke format internasional
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1); // 08xx -> 628xx
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone; // 8xx -> 628xx
        }
        
        Log::info('📱 Normalisasi nomor:', [
            'original' => $phone,
            'normalized' => $phone
        ]);

        // Format pesan
        $pesan = "Halo *{$nama}*,\n\n";
        $pesan .= "Kode OTP untuk verifikasi nomor WhatsApp Anda adalah:\n\n";
        $pesan .= "*{$otpCode}*\n\n";
        $pesan .= "Kode berlaku selama *1 menit*.\n";
        $pesan .= "Jangan bagikan kode ini kepada siapapun!\n\n";
        $pesan .= "_Terima kasih telah menggunakan layanan kami._";
        
        Log::info('📤 Mencoba kirim OTP', [
            'phone' => $phone,
            'nama' => $nama,
            'otp' => $otpCode
        ]);
        
        // Panggil helper function kirimWa
        $result = kirimWa($phone, $pesan);
        
        // Log hasil
        if ($result['success']) {
            Log::info('✅ OTP berhasil dikirim ke ' . $phone);
        } else {
            Log::error('❌ Gagal kirim OTP ke ' . $phone . ': ' . $result['message']);
        }
        
        return $result;
    }

    /**
     * REGISTER USER
     */
    public function register(Request $request)
    {
        // Tidak dinormalisasi lagi saat registrasi, melainkan di validasi agar harus 62


        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'phone'    => ['required', 'string', 'unique:users,phone', 'regex:/^62[0-9]+$/'],
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi',
            'phone.required' => 'Nomor telepon wajib diisi',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'phone.regex' => 'Nomor telepon harus diawali dengan 62 dan hanya berisi angka',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Gunakan nomor langsung dari input (karena sudah divalidasi 62)
            $normalizedPhone = $request->phone;

            // create user (role default customer)
            $user = User::create([
                'name'     => $request->name,
                'phone'    => $normalizedPhone,
                'role'     => 'customer',
                'password' => Hash::make($request->password),
            ]);

            // generate OTP
            $otpCode = rand(100000, 999999);

            Otp::create([
                'user_id'    => $user->id,
                'phone'      => $normalizedPhone,
                'code'       => $otpCode,
                'expired_at' => Carbon::now()->addMinutes(1),
            ]);

            Log::info('💾 OTP tersimpan di database', [
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
                'code' => $otpCode
            ]);

            // ✅ KIRIM OTP KE WHATSAPP
            $kirimResult = $this->kirimOtp($normalizedPhone, $user->name, $otpCode);

            // Cek hasil pengiriman
            if (!$kirimResult['success']) {
                Log::warning('⚠️ OTP tersimpan tapi gagal dikirim: ' . $kirimResult['message']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi berhasil. Kode OTP tersimpan, namun pengiriman WhatsApp mengalami kendala. Silakan gunakan fitur "Kirim Ulang OTP".',
                    'warning' => $kirimResult['message'],
                    'user_id' => $user->id
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Kode OTP telah dikirim ke WhatsApp Anda',
                'user_id' => $user->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('❌ Error saat register: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * RESEND OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ], [
            'phone.required' => 'Nomor telepon wajib diisi'
        ]);

        try {
            // Normalisasi nomor
            $normalizedPhone = $this->normalizePhone($request->phone);

            $user = User::where('phone', $normalizedPhone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor tidak ditemukan'
                ], 404);
            }

            // Cek apakah sudah terverifikasi
            if ($user->phone_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor sudah terverifikasi'
                ], 400);
            }

            // Hapus OTP lama
            Otp::where('phone', $normalizedPhone)->delete();

            // Generate OTP baru
            $otpCode = rand(100000, 999999);

            Otp::create([
                'user_id'    => $user->id,
                'phone'      => $normalizedPhone,
                'code'       => $otpCode,
                'expired_at' => Carbon::now()->addMinutes(1),
            ]);

            Log::info('🔄 OTP baru dibuat', [
                'user_id' => $user->id,
                'phone' => $normalizedPhone,
                'code' => $otpCode
            ]);

            // ✅ KIRIM OTP BARU KE WHATSAPP
            $kirimResult = $this->kirimOtp($normalizedPhone, $user->name, $otpCode);

            // Cek hasil pengiriman
            if (!$kirimResult['success']) {
                Log::warning('⚠️ OTP tersimpan di database tapi gagal dikirim ke WhatsApp: ' . $kirimResult['message']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Kode OTP baru telah dibuat, namun pengiriman WhatsApp mengalami kendala. Silakan periksa koneksi atau coba lagi.',
                    'warning' => $kirimResult['message']
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP baru telah dikirim ke WhatsApp Anda'
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error saat resend OTP: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * VERIFY OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string',
        ], [
            'phone.required' => 'Nomor telepon wajib diisi',
            'code.required' => 'Kode OTP wajib diisi'
        ]);

        try {
            // Normalisasi nomor
            $normalizedPhone = $this->normalizePhone($request->phone);

            $otp = Otp::where('phone', $normalizedPhone)
                ->where('code', $request->code)
                ->latest()
                ->first();

            if (!$otp) {
                Log::warning('❌ OTP tidak valid', [
                    'phone' => $normalizedPhone,
                    'code' => $request->code
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid'
                ], 400);
            }

            if (Carbon::now()->gt($otp->expired_at)) {
                Log::warning('⏰ OTP expired', [
                    'phone' => $normalizedPhone,
                    'expired_at' => $otp->expired_at
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang OTP.'
                ], 400);
            }

            $user = User::where('phone', $normalizedPhone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $user->update([
                'phone_verified_at' => now(),
                
            ]);

            // Login user otomatis setelah verifikasi
            Auth::login($user);

            // hapus OTP setelah sukses
            $otp->delete();

            Log::info('✅ Verifikasi OTP berhasil', [
                'user_id' => $user->id,
                'phone' => $normalizedPhone
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor berhasil diverifikasi',
                'redirect' => route('home')
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error saat verify OTP: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper untuk normalisasi nomor HP
     */
    private function normalizePhone($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Konversi ke format internasional (62xxx)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }

    /**
     * Tampilkan form lupa password
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses kirim link reset password via WhatsApp
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ], [
            'phone.required' => 'Nomor telepon wajib diisi'
        ]);

        try {
            // Normalisasi nomor
            $normalizedPhone = $this->normalizePhone($request->phone);

            // Cek apakah nomor terdaftar
            $user = User::where('phone', $normalizedPhone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor telepon tidak terdaftar'
                ], 404);
            }

            // Hapus token reset password lama (jika ada)
            \DB::table('password_resets')->where('phone', $normalizedPhone)->delete();

            // Generate token unik
            $token = \Str::random(60);

            // Simpan token ke database
            \DB::table('password_resets')->insert([
                'phone' => $normalizedPhone,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            // Generate link reset password
            $resetLink = route('auth.reset-password-form', ['token' => $token]);

            // Format pesan WhatsApp
            $pesan = "Halo *{$user->name}*,\n\n";
            $pesan .= "Anda telah meminta reset password untuk akun Anda.\n\n";
            $pesan .= "Klik link berikut untuk reset password:\n";
            $pesan .= "{$resetLink}\n\n";
            $pesan .= "Link berlaku selama *15 menit*.\n";
            $pesan .= "Jika Anda tidak merasa meminta reset password, abaikan pesan ini.\n\n";
            $pesan .= "_Terima kasih telah menggunakan layanan kami._";

            // Kirim link via WhatsApp
            $kirimResult = kirimWa($normalizedPhone, $pesan);

            if (!$kirimResult['success']) {
                Log::warning('⚠ Token tersimpan tapi gagal kirim WA: ' . $kirimResult['message']);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Link reset password berhasil dibuat. Jika tidak menerima WhatsApp, silakan hubungi admin.',
                    'warning' => 'Pengiriman WhatsApp mengalami kendala: ' . $kirimResult['message']
                ]);
            }

            Log::info('✅ Link reset password berhasil dikirim ke ' . $normalizedPhone);

            return response()->json([
                'success' => true,
                'message' => 'Link reset password telah dikirim ke WhatsApp Anda'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error saat kirim link reset: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan form reset password (dari link WA)
     */
    public function showResetPasswordForm($token)
    {
        // Cek apakah token valid
        $passwordReset = \DB::table('password_resets')
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            return redirect()->route('login')
                ->with('error', 'Link reset password tidak valid');
        }

        // Cek apakah token sudah expired (15 menit)
        $createdAt = Carbon::parse($passwordReset->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 15) {
            // Hapus token yang expired
            \DB::table('password_resets')->where('token', $token)->delete();
            
            return redirect()->route('login')
                ->with('error', 'Link reset password sudah kadaluarsa. Silakan request ulang.');
        }

        // Ambil user berdasarkan phone
        $user = User::where('phone', $passwordReset->phone)->first();

        return view('auth.reset-password-form', compact('token', 'user'));
    }

    /**
     * Proses reset password dari link WA
     */
    public function resetPasswordFromLink(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'Token tidak valid',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        // Cek token
        $passwordReset = \DB::table('password_resets')
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return back()->with('error', 'Token tidak valid');
        }

        // Cek expired
        $createdAt = Carbon::parse($passwordReset->created_at);
        if (Carbon::now()->diffInMinutes($createdAt) > 15) {
            \DB::table('password_resets')->where('token', $request->token)->delete();
            return back()->with('error', 'Link sudah kadaluarsa');
        }

        // Update password
        $user = User::where('phone', $passwordReset->phone)->first();
        
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Hapus token setelah berhasil
        \DB::table('password_resets')->where('token', $request->token)->delete();

        // Kirim notifikasi WA (opsional)
        try {
            $pesan = "Halo *{$user->name}*,\n\n";
            $pesan .= "Password Anda telah berhasil diubah.\n\n";
            $pesan .= "Jika Anda tidak melakukan perubahan ini, segera hubungi kami.\n\n";
            $pesan .= "_Terima kasih._";
            
            kirimWa($user->phone, $pesan);
        } catch (\Exception $e) {
            Log::error('Error kirim notifikasi reset password: ' . $e->getMessage());
        }

        return redirect()->route('login')
            ->with('sukses', 'Password berhasil diubah! Silakan login dengan password baru Anda.');
    }

    /**
     * LOGIN
     */
     public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        // Normalisasi nomor sebelum kueri
        $normalizedPhone = $this->normalizePhone($request->phone);

        $user = User::where('phone', $normalizedPhone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Nomor atau password salah'
            ], 401);
        }

        // Cek verifikasi phone untuk customer & seller (admin & courier tidak perlu verifikasi)
        if (in_array($user->role, ['customer', 'seller']) && !$user->phone_verified_at) {
            return response()->json([
                'message' => 'Nomor belum diverifikasi. Silakan verifikasi terlebih dahulu.'
            ], 403);
        }

      

        // Login user
        Auth::login($user);
// SET DEFAULT MODE SETELAH LOGIN
if ($user->role === 'seller') {
    session(['account_mode' => 'seller']);
} else {
    session(['account_mode' => 'customer']);
}
        return response()->json([
            'message' => 'Login berhasil',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
                'role'  => $user->role,
            ],
            'redirect' => $this->getRedirectUrl($user->role)
        ]);
    }
    
    private function getRedirectUrl($role)
    {
        switch ($role) {
            case 'admin':
                return route('admin.dashboard');
            case 'seller':
                return route('seller.dashboard.index');
            case 'courier':
                return route('kurir.dashboard');
            default:
                return route('home');
        }
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Berhasil logout.');
    }
}