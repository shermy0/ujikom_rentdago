<?php

namespace App\Http\Controllers;

use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Storage facade dihapus — menggunakan move() ke public/ langsung
use Illuminate\Support\Facades\Log;
use App\Helpers\CustomerNotificationHelper;

class SellerRequestController extends Controller
{
    /**
     * Helper untuk normalisasi nomor HP
     */
    private function normalizePhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

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
     * Tampilkan form pengajuan seller (customer upload KTP + data toko)
     */
    public function create()
    {
        // Cek apakah user sudah pernah mengajukan
        $existingRequest = SellerRequest::where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan yang sedang diproses atau sudah disetujui.');
        }

        return view('seller.seller-request.create');
    }

    /**
     * Simpan pengajuan seller (TANPA create toko dulu)
     */
    public function store(Request $request)
    {
        $request->validate([
            'ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'ktp_photo.required' => 'Foto KTP wajib diupload',
            'ktp_photo.image' => 'File harus berupa gambar',
            'ktp_photo.mimes' => 'Format file harus jpeg, png, atau jpg',
            'ktp_photo.max' => 'Ukuran file maksimal 2MB'
        ]);

        DB::beginTransaction();
        try {
            // Upload KTP
            if (!file_exists(public_path('ktp'))) {
                mkdir(public_path('ktp'), 0755, true);
            }
            $ktpFile = $request->file('ktp_photo');
            $ktpFilename = time() . '_' . uniqid() . '.' . $ktpFile->getClientOriginalExtension();
            $ktpFile->move(public_path('ktp'), $ktpFilename);
            $ktpPath = 'ktp/' . $ktpFilename;

            // Simpan ke seller_requests HANYA KTP
            $sellerRequest = SellerRequest::create([
                'user_id' => auth()->id(),
                'ktp_photo' => $ktpPath,
                'status' => 'pending',
            ]);

            Log::info('📝 Seller request created', [
                'user_id' => auth()->id(),
                'request_id' => $sellerRequest->id,
                'ktp_path' => $ktpPath
            ]);

            // ✅ Kirim notifikasi WA ke customer
            $user = auth()->user();
            
            if (!empty($user->phone)) {
                // Normalisasi nomor HP
                $normalizedPhone = $this->normalizePhone($user->phone);
                
                Log::info('📱 Persiapan kirim WA pengajuan seller', [
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'phone_original' => $user->phone,
                    'phone_normalized' => $normalizedPhone
                ]);

                // Format pesan
                $pesan = "Halo *{$user->name}*,\n\n";
                $pesan .= "Terima kasih telah mengajukan menjadi seller di platform kami! 🎉\n\n";
                $pesan .= "📋 *Detail Pengajuan:*\n";
                $pesan .= "• Status: Menunggu Persetujuan Admin\n";
                $pesan .= "• Tanggal Pengajuan: " . $sellerRequest->created_at->format('d/m/Y H:i') . "\n\n";
                $pesan .= "Pengajuan Anda sedang dalam proses verifikasi oleh tim admin kami. ";
                $pesan .= "Kami akan menginformasikan hasilnya segera setelah proses verifikasi selesai.\n\n";
                $pesan .= "Mohon tunggu konfirmasi lebih lanjut ya! 🙏\n\n";
                $pesan .= "Terima kasih atas kesabaran Anda.";

                // Kirim WA menggunakan helper
                $waResult = kirimWa($normalizedPhone, $pesan);

                // Log hasil pengiriman
                if ($waResult['success']) {
                    Log::info('✅ WA pengajuan seller berhasil dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone
                    ]);
                } else {
                    Log::error('❌ WA pengajuan seller gagal dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone,
                        'error' => $waResult['message']
                    ]);
                }
            } else {
                Log::warning('⚠️ User tidak punya nomor HP', [
                    'user_id' => $user->id,
                    'nama' => $user->name
                ]);
            }

            
            // ✅ Kirim notifikasi Bell Customer
            CustomerNotificationHelper::notifySellerRequestSubmitted($user);

            // 🔥 Kirim notifikasi ke Admin
            CustomerNotificationHelper::notifyAdminSellerRequest($user);

            DB::commit();

            return redirect()->route('home')->with('success', 'Pengajuan menjadi seller berhasil dikirim! Tunggu persetujuan admin.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Error saat store seller request: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Tampilkan daftar pengajuan seller
     */
    public function index(Request $request)
    {
        $query = SellerRequest::with('user');

        // Filter berdasarkan status - default to pending if no status specified
        if ($request->has('status') && $request->status != '') {
            if ($request->status === 'all') {
                // Show all requests
            } else {
                $query->where('status', $request->status);
            }
        } else {
            // Default to pending status
            $query->where('status', 'pending');
        }

        $requests = $query->latest()->paginate(10);

        return view('admin.seller-request.index', compact('requests'))->with('title', 'Pengajuan Seller');
    }

    /**
     * Admin: Tampilkan detail pengajuan
     */
    public function show($id)
    {
        $sellerRequest = SellerRequest::with(['user', 'reviewer'])->findOrFail($id);

        return view('admin.seller-requests.show', compact('sellerRequest'));
    }

    /**
     * Admin: Approve pengajuan seller
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ], [
            'admin_notes.max' => 'Catatan admin maksimal 500 karakter'
        ]);

        DB::beginTransaction();
        try {
            $sellerRequest = SellerRequest::findOrFail($id);

            // Cek apakah sudah diproses
            if ($sellerRequest->status !== 'pending') {
                return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
            }

            // Update status seller request
            $sellerRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            Log::info('✅ Seller request approved', [
                'request_id' => $sellerRequest->id,
                'user_id' => $sellerRequest->user_id,
                'reviewed_by' => auth()->id()
            ]);

            // Update role user jadi seller (TANPA buat toko dulu)
            $user = User::find($sellerRequest->user_id);
            $user->update(['role' => 'seller']);

            Log::info('👤 User role updated to seller', [
                'user_id' => $user->id,
                'old_role' => 'customer',
                'new_role' => 'seller'
            ]);

            // ✅ Kirim notifikasi WA ke customer (APPROVED)
            if (!empty($user->phone)) {
                // Normalisasi nomor HP
                $normalizedPhone = $this->normalizePhone($user->phone);
                
                Log::info('📱 Persiapan kirim WA approval seller', [
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'phone_original' => $user->phone,
                    'phone_normalized' => $normalizedPhone
                ]);

                // Format pesan
                $pesan = "Selamat *{$user->name}*! 🎉\n\n";
                $pesan .= "Pengajuan Anda untuk menjadi seller telah *DISETUJUI* oleh admin! ✅\n\n";
                $pesan .= "📋 *Informasi:*\n";
                $pesan .= "• Status: Disetujui\n";
                $pesan .= "• Tanggal Disetujui: " . now()->format('d/m/Y H:i') . "\n";
                
                if (!empty($request->admin_notes)) {
                    $pesan .= "• Catatan Admin: {$request->admin_notes}\n";
                }
                
                $pesan .= "\nAnda sekarang dapat membuka toko dan mulai berjualan di platform kami! 🛍️\n\n";
                $pesan .= "Silakan login dan lengkapi data toko Anda untuk memulai.\n\n";
                $pesan .= "Selamat berjualan! 🚀";

                // Kirim WA menggunakan helper
                $waResult = kirimWa($normalizedPhone, $pesan);

                // Log hasil pengiriman
                if ($waResult['success']) {
                    Log::info('✅ WA approval seller berhasil dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone
                    ]);
                } else {
                    Log::error('❌ WA approval seller gagal dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone,
                        'error' => $waResult['message']
                    ]);
                }
            } else {
                Log::warning('⚠️ User approved tidak punya nomor HP', [
                    'user_id' => $user->id,
                    'nama' => $user->name
                ]);
            }

            
            // ✅ Kirim notifikasi Bell Customer
            CustomerNotificationHelper::notifySellerRequestApproved($user);

            DB::commit();

            return redirect()->route('admin.seller-requests.index')
                ->with('success', 'Pengajuan seller berhasil disetujui! User sekarang dapat membuka toko.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Error saat approve seller request: ' . $e->getMessage(), [
                'request_id' => $id,
                'admin_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Admin: Reject pengajuan seller
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Alasan penolakan wajib diisi',
            'admin_notes.max' => 'Alasan penolakan maksimal 500 karakter'
        ]);

        DB::beginTransaction();
        try {
            $sellerRequest = SellerRequest::findOrFail($id);

            // Cek apakah sudah diproses
            if ($sellerRequest->status !== 'pending') {
                return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
            }

            // Update status seller request
            $sellerRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            Log::info('❌ Seller request rejected', [
                'request_id' => $sellerRequest->id,
                'user_id' => $sellerRequest->user_id,
                'reviewed_by' => auth()->id(),
                'reason' => $request->admin_notes
            ]);

            // ✅ Kirim notifikasi WA ke customer (REJECTED)
            $user = User::find($sellerRequest->user_id);
            
            if (!empty($user->phone)) {
                // Normalisasi nomor HP
                $normalizedPhone = $this->normalizePhone($user->phone);
                
                Log::info('📱 Persiapan kirim WA rejection seller', [
                    'user_id' => $user->id,
                    'nama' => $user->name,
                    'phone_original' => $user->phone,
                    'phone_normalized' => $normalizedPhone
                ]);

                // Format pesan
                $pesan = "Halo *{$user->name}*,\n\n";
                $pesan .= "Mohon maaf, pengajuan Anda untuk menjadi seller *DITOLAK* oleh admin. ❌\n\n";
                $pesan .= "📋 *Informasi:*\n";
                $pesan .= "• Status: Ditolak\n";
                $pesan .= "• Tanggal Ditolak: " . now()->format('d/m/Y H:i') . "\n";
                $pesan .= "• Alasan Penolakan: {$request->admin_notes}\n\n";
                $pesan .= "Jika Anda merasa ada kesalahan atau ingin mengajukan kembali, ";
                $pesan .= "silakan perbaiki dokumen dan data Anda sesuai catatan di atas.\n\n";
                $pesan .= "Terima kasih atas pengertiannya. 🙏";

                // Kirim WA menggunakan helper
                $waResult = kirimWa($normalizedPhone, $pesan);

                // Log hasil pengiriman
                if ($waResult['success']) {
                    Log::info('✅ WA rejection seller berhasil dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone
                    ]);
                } else {
                    Log::error('❌ WA rejection seller gagal dikirim', [
                        'user_id' => $user->id,
                        'phone' => $normalizedPhone,
                        'error' => $waResult['message']
                    ]);
                }
            } else {
                Log::warning('⚠️ User rejected tidak punya nomor HP', [
                    'user_id' => $user->id,
                    'nama' => $user->name
                ]);
            }

            
            // ✅ Kirim notifikasi Bell Customer
            CustomerNotificationHelper::notifySellerRequestRejected($user, $request->admin_notes);

            DB::commit();

            return redirect()->route('admin.seller-requests.index')
                ->with('success', 'Pengajuan seller ditolak.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Error saat reject seller request: ' . $e->getMessage(), [
                'request_id' => $id,
                'admin_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * User: Cek status pengajuan seller sendiri
     */
    public function myRequest()
    {
        $sellerRequest = SellerRequest::where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('seller-request.my-request', compact('sellerRequest'));
    }
}