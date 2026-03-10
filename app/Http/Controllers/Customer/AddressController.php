<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Tampilkan daftar alamat user
     */
    public function index()
    {
        $addresses = auth()->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('customer.address.index', compact('addresses'))->with('title', 'Alamat');
    }

    /**
     * Hapus alamat (jika tidak dipakai order aktif)
     */
public function destroy(UserAddress $address)
{
    if ($address->user_id !== auth()->id()) {
        abort(403);
    }

    // ❗ cek order aktif YANG DELIVERY
    $hasActiveDeliveryOrder = \App\Models\Order::where('user_address_id', $address->id)
        ->where('delivery_method', 'delivery')
        ->whereIn('status', ['pending', 'confirmed', 'ongoing'])
        ->exists();

    if ($hasActiveDeliveryOrder) {
        return back()->with('error', 'Alamat ini sedang digunakan pada pesanan aktif dan tidak bisa dihapus.');
    }

    $isDefault = $address->is_default;
    $user = auth()->user();

    $address->delete();

    // kalau default dihapus → set default baru
    if ($isDefault) {
        $nextAddress = $user->addresses()->latest()->first();
        if ($nextAddress) {
            $nextAddress->update(['is_default' => true]);
        }
    }

    return back()->with('success', 'Alamat berhasil dihapus.');
}



    
public function create(Request $request)
{
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

public function store(Request $request)
{
    $request->validate([
        'label' => 'required|string|max:50',
        'receiver_name' => 'required|string|max:255',
        'receiver_phone' => 'required|string|max:20',
        'address' => 'required|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'notes' => 'nullable|string|max:255',
    ]);

    $user = auth()->user();

    UserAddress::create([
        'user_id' => $user->id,
        'label' => $request->label,
        'receiver_name' => $request->receiver_name,
        'receiver_phone' => $request->receiver_phone,
        'address' => $request->address,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'notes' => $request->notes,
        'is_default' => $user->addresses()->count() === 0,
    ]);

    // ✅ JIKA DARI MODAL SEWA
    if (session()->has('rent_context')) {
        $context = session()->pull('rent_context');

return redirect()
    ->route('customer.checkout', $context['product_id'])
    ->with('success', 'Alamat berhasil ditambahkan.');

    }

    // ❌ JIKA TAMBAH ALAMAT BIASA
    return redirect()
        ->route('customer.addresses.index')
        ->with('success', 'Alamat berhasil ditambahkan.');
}


public function edit(UserAddress $address)
{
    // Pastikan alamat milik user login
    if ($address->user_id !== auth()->id()) {
        abort(403);
    }

    return view('customer.address.edit', compact('address'));
}

public function update(Request $request, UserAddress $address)
{
    // Pastikan alamat milik user login
    if ($address->user_id !== auth()->id()) {
        abort(403);
    }

    $validated = $request->validate([
        'label' => 'required|string|max:50',
        'receiver_name' => 'required|string|max:255',
        'receiver_phone' => 'required|string|max:20',
        'address' => 'required|string',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'notes' => 'nullable|string',
    ]);

    $address->update($validated);

    return redirect()
        ->route('customer.addresses.index')
        ->with('success', 'Alamat berhasil diperbarui');
}

public function setDefault(UserAddress $address)
{
    if ($address->user_id !== auth()->id()) {
        abort(403);
    }

    // reset semua alamat user
    auth()->user()->addresses()->update([
        'is_default' => false
    ]);

    // set alamat ini jadi default
    $address->update([
        'is_default' => true
    ]);

    return back()->with('success', 'Alamat utama berhasil diperbarui');
}


}
