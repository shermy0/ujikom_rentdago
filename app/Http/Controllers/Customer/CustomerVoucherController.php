<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerVoucherController extends Controller
{
    /**
     * Get available vouchers for a specific shop that user has claimed
     */
    public function getAvailable(Request $request)
    {
        try {
            $shopId = $request->input('shop_id');
            
            if (!$shopId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop ID required'
                ], 400);
            }

            $userId = Auth::id();
            
            // Query voucher yang sudah di-claim user untuk shop ini
            $userVouchers = UserVoucher::where('user_id', $userId)
                ->whereHas('voucher', function($q) use ($shopId) {
                    $q->where('shop_id', $shopId)
                      ->where('is_active', true)
                      ->where(function($query) {
                          $query->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', now());
                      })
                      ->where(function($query) {
                          $query->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', now());
                      });
                })
                ->with('voucher')
                ->get();

            $availableVouchers = [];

            foreach ($userVouchers as $userVoucher) {
                $voucher = $userVoucher->voucher;
                
                if (!$voucher) {
                    continue;
                }
                
                // Cek apakah user sudah pernah menggunakan voucher ini
                $hasUsed = $voucher->hasBeenUsedBy($userId);
                
                if ($hasUsed) {
                    continue; // Skip voucher yang sudah pernah digunakan
                }
                
                $availableVouchers[] = [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'description' => $voucher->description,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'max_discount' => $voucher->max_discount,
                    'min_transaction_amount' => $voucher->min_transaction,
                    'claimed_at' => $userVoucher->claimed_at,
                ];
            }

            Log::info('Available vouchers loaded', [
                'user_id' => $userId,
                'shop_id' => $shopId,
                'voucher_count' => count($availableVouchers)
            ]);

            return response()->json([
                'success' => true,
                'vouchers' => $availableVouchers
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get available vouchers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Claim voucher dari halaman toko
     */
    public function claim($voucherId)
    {
        $voucher = Voucher::findOrFail($voucherId);
        $userId = Auth::id();

        // Check if voucher can be claimed
        if (!$voucher->canBeClaimed($userId)) {
            return back()->with('error', 'Voucher tidak dapat diklaim.');
        }

        try {
            DB::beginTransaction();

            // Create user voucher relationship
            UserVoucher::create([
                'user_id' => $userId,
                'voucher_id' => $voucher->id,
                'claimed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Voucher berhasil diklaim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to claim voucher', [
                'voucher_id' => $voucherId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal mengklaim voucher. Silakan coba lagi.');
        }
    }

    /**
     * Daftar voucher yang sudah diklaim user
     */
    public function myVouchers()
    {
        $vouchers = Auth::user()->vouchers()
            ->with('shop')
            ->withPivot('claimed_at')
            ->orderBy('user_vouchers.claimed_at', 'desc')
            ->get()
            ->map(function($voucher) {
                $voucher->has_been_used = $voucher->hasBeenUsedBy(Auth::id());
                return $voucher;
            });

        return view('customer.vouchers.my-vouchers', compact('vouchers'));
    }

    /**
     * Validasi voucher untuk order (AJAX)
     */
    public function validate(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'shop_id' => 'required|exists:shops,id',
            'amount' => 'required|integer|min:1',
        ]);

        $voucher = Voucher::where('code', strtoupper($request->voucher_code))
            ->where('shop_id', $request->shop_id)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Kode voucher tidak ditemukan'
            ], 404);
        }

        $userId = Auth::id();

        // Check if user has claimed this voucher
        $userVoucher = $voucher->users()->where('user_id', $userId)->first();

        if (!$userVoucher) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum mengklaim voucher ini'
            ], 403);
        }

        // Check if voucher can be used
        if (!$voucher->canBeUsed($userId, $request->amount)) {
            $reasons = [];

            if (!$voucher->isValid()) {
                $reasons[] = 'Voucher tidak aktif atau sudah kadaluarsa';
            }

            if ($voucher->hasBeenUsedBy($userId)) {
                $reasons[] = 'Anda sudah pernah menggunakan voucher ini';
            }

            if ($request->amount < $voucher->min_transaction) {
                $reasons[] = 'Minimal transaksi Rp ' . number_format($voucher->min_transaction, 0, ',', '.');
            }

            return response()->json([
                'success' => false,
                'message' => implode(', ', $reasons)
            ], 400);
        }

        // Calculate discount
        $discountAmount = $voucher->calculateDiscount($request->amount);
        $finalAmount = $request->amount - $discountAmount;

        return response()->json([
            'success' => true,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
            ],
            'original_amount' => $request->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'message' => 'Voucher berhasil diterapkan!'
        ]);
    }
}