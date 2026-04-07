<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

/**
 * AddressController — Mengelola alamat pengiriman milik Customer.
 *
 * Menyediakan fitur melihat daftar alamat, menambah alamat baru,
 * mengedit, menghapus (dengan pengecekan pesanan aktif),
 * dan menentukan alamat utama (default) untuk keperluan pengiriman.
 */
class AddressController extends Controller
{
    /**
     * Menampilkan daftar seluruh alamat milik customer yang sedang login.
     *
     * Alamat default ditampilkan paling atas, diikuti alamat terbaru.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $addresses = auth()->user()
            ->addresses()
            ->orderByDesc('is_default') // Alamat default tampil pertama
            ->latest()
            ->get();

        return view('customer.address.index', compact('addresses'))->with('title', 'Alamat');
    }

    /**
     * Menampilkan form tambah alamat baru.
     *
     * Jika berasal dari halaman sewa (from=rent), konteks sewa disimpan ke session
     * agar setelah alamat ditambahkan, customer diarahkan kembali ke halaman checkout.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Simpan konteks sewa ke session jika customer datang dari halaman sewa
        if ($request->from === 'rent') {
            session([
                'rent_context' => [
                    'product_id'        => $request->product_id,
                    'product_rental_id' => $request->product_rental_id,
                    'start_time'        => $request->start_time,
                    'delivery_method'   => $request->delivery_method,
                ]
            ]);
        }

        return view('customer.address.create');
    }

    /**
     * Menyimpan alamat baru ke database.
     *
     * Jika ini adalah alamat pertama milik user, otomatis dijadikan alamat default.
     * Jika berasal dari konteks sewa, customer diarahkan kembali ke halaman checkout.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validasi seluruh field form alamat
        $request->validate([
            'label'          => 'required|string|max:50',
            'receiver_name'  => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'address'        => 'required|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'notes'          => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        // Simpan alamat baru ke database
        // Jika belum punya alamat sama sekali, jadikan ini sebagai default
        UserAddress::create([
            'user_id'        => $user->id,
            'label'          => $request->label,
            'receiver_name'  => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address'        => $request->address,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'notes'          => $request->notes,
            'is_default'     => $user->addresses()->count() === 0,
        ]);

        // Jika customer datang dari halaman checkout sewa, kembalikan ke sana
        if (session()->has('rent_context')) {
            $context = session()->pull('rent_context');

            return redirect()
                ->route('customer.checkout', $context['product_id'])
                ->with('success', 'Alamat berhasil ditambahkan.');
        }

        // Redirect ke daftar alamat untuk penambahan alamat biasa
        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit alamat yang sudah ada.
     *
     * Memastikan bahwa alamat yang diedit benar-benar milik customer yang login.
     *
     * @param \App\Models\UserAddress $address
     * @return \Illuminate\View\View
     */
    public function edit(UserAddress $address)
    {
        // Pastikan alamat adalah milik customer yang sedang login
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return view('customer.address.edit', compact('address'));
    }

    /**
     * Memperbarui data alamat yang sudah ada.
     *
     * Memastikan kepemilikan alamat sebelum melakukan update.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\UserAddress $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, UserAddress $address)
    {
        // Pastikan alamat adalah milik customer yang sedang login
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Validasi input form edit alamat
        $validated = $request->validate([
            'label'          => 'required|string|max:50',
            'receiver_name'  => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'address'        => 'required|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'notes'          => 'nullable|string',
        ]);

        // Perbarui data alamat di database
        $address->update($validated);

        return redirect()
            ->route('customer.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui');
    }

    /**
     * Menghapus alamat milik customer.
     *
     * Alamat tidak dapat dihapus jika sedang digunakan pada pesanan aktif
     * dengan metode pengiriman delivery. Jika alamat yang dihapus adalah default,
     * sistem otomatis menentukan alamat lainnya sebagai default baru.
     *
     * @param \App\Models\UserAddress $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(UserAddress $address)
    {
        // Pastikan alamat adalah milik customer yang sedang login
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Cek apakah alamat ini sedang dipakai pada pesanan delivery yang aktif
        $hasActiveDeliveryOrder = \App\Models\Order::where('user_address_id', $address->id)
            ->where('delivery_method', 'delivery')
            ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
            ->exists();

        // Tolak penghapusan jika ada pesanan aktif yang menggunakan alamat ini
        if ($hasActiveDeliveryOrder) {
            return back()->with('error', 'Alamat ini sedang digunakan pada pesanan aktif dan tidak bisa dihapus.');
        }

        $isDefault = $address->is_default;
        $user      = auth()->user();

        // Hapus alamat dari database
        $address->delete();

        // Jika alamat yang dihapus adalah default, otomatis set alamat lain sebagai default baru
        if ($isDefault) {
            $nextAddress = $user->addresses()->latest()->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * Menetapkan sebuah alamat sebagai alamat utama (default).
     *
     * Reset semua alamat user menjadi non-default terlebih dahulu,
     * lalu tandai alamat yang dipilih sebagai default.
     *
     * @param \App\Models\UserAddress $address
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDefault(UserAddress $address)
    {
        // Pastikan alamat adalah milik customer yang sedang login
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Reset semua alamat milik user menjadi non-default
        auth()->user()->addresses()->update(['is_default' => false]);

        // Tetapkan alamat yang dipilih sebagai alamat default
        $address->update(['is_default' => true]);

        return back()->with('success', 'Alamat utama berhasil diperbarui');
    }
}
