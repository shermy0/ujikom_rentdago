<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $setting = DB::table('settings')->first();

        return view('admin.settings.index', [
            'title' => 'Pengaturan',
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['title' => 'Pengaturan', 'url' => '#']
            ],
            'setting' => $setting
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'about' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'wa_endpoint_url' => 'nullable|url',
            'wa_token' => 'nullable|string',
            'wa_sender' => 'nullable|string',
            'address' => 'nullable|string',
            'open_time' => 'nullable|string',
            'document_description' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'midtrans_mode' => 'nullable|in:sandbox,production', 
            'midtrans_client_key' => 'nullable|string',
            'midtrans_server_key' => 'nullable|string',
        ]);

        $data = $request->except(['_token', '_method', 'logo']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $setting = DB::table('settings')->first();

            // Delete old logo if exists
            if ($setting && $setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $logoPath;
        }

        $data['updated_at'] = now();

        DB::table('settings')->update($data);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
