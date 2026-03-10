<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    /**
     * Tentukan apakah user authorized untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Ambil validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|unique:shops,user_id',
            'name_store' => 'required|string|max:255',
            'description' => 'nullable|string',
            // Alamat lengkap dari Nominatim atau input manual
            'address_store' => 'required|string|max:1000',
            // Latitude: -90 hingga 90
            'latitude' => 'required|numeric|between:-90,90',
            // Longitude: -180 hingga 180
            'longitude' => 'required|numeric|between:-180,180',
            // Logo upload
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Custom messages untuk validation errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Penjual harus dipilih.',
            'user_id.exists' => 'Penjual tidak ditemukan.',
            'user_id.unique' => 'Penjual ini sudah memiliki toko.',
            'name_store.required' => 'Nama toko harus diisi.',
            'name_store.max' => 'Nama toko tidak boleh lebih dari 255 karakter.',
            'address_store.required' => 'Alamat toko harus diisi.',
            'address_store.max' => 'Alamat toko tidak boleh lebih dari 1000 karakter.',
            'latitude.required' => 'Latitude harus diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus antara -90 dan 90.',
            'longitude.required' => 'Longitude harus diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 dan 180.',
            'logo.required' => 'Logo toko harus diunggah.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPEG, PNG, JPG, GIF, atau WebP.',
            'logo.max' => 'Ukuran logo tidak boleh lebih dari 2MB.',
        ];
    }
}
