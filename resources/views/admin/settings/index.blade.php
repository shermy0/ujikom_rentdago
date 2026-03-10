@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-gear me-2 text-primary"></i>
                    Pengaturan APP
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Aplikasi Section -->
                    <div class="mb-5">
                        <h6 class="fw-semibold mb-3 text-dark">Aplikasi</h6>
                        
                        <!-- Logo -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Logo</label>
                            @if($setting && $setting->logo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-width: 150px;">
                            </div>
                            @endif
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF. Max: 2MB</small>
                        </div>

                        <!-- Nama Aplikasi -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Aplikasi</label>
                            <input type="text" name="app_name" class="form-control" 
                                   value="{{ old('app_name', $setting->app_name ?? '') }}" 
                                   placeholder="Contoh: Sserafim" required>
                        </div>

                        <!-- Tentang -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Tentang</label>
                            <textarea name="about" class="form-control" rows="4" 
                                      placeholder="Deskripsi tentang aplikasi...">{{ old('about', $setting->about ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- WhatsApp Gateway Section -->
                    <div class="mb-5">
                        <h6 class="fw-semibold mb-3 text-dark">WhatsApp Gateway</h6>
                        
                        <!-- WA Endpoint URL -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">WA Endpoint URL</label>
                            <input type="url" name="wa_endpoint_url" class="form-control" 
                                   value="{{ old('wa_endpoint_url', $setting->wa_endpoint_url ?? '') }}" 
                                   placeholder="https://app.japati.id/api/send-message">
                        </div>

                        <!-- Token -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Token</label>
                            <textarea name="wa_token" class="form-control" rows="3" 
                                      placeholder="API-TOKEN-xxx">{{ old('wa_token', $setting->wa_token ?? '') }}</textarea>
                        </div>

                        <!-- WA Sender -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">WA Sender</label>
                            <input type="text" name="wa_sender" class="form-control" 
                                   value="{{ old('wa_sender', $setting->wa_sender ?? '') }}" 
                                   placeholder="628997440314">
                            <small class="text-muted">Format: 628xxxxxxxxxx (tanpa +)</small>
                        </div>
                    </div>

                    <!-- Data Toko Section -->
                    <div class="mb-5">
                        <h6 class="fw-semibold mb-3 text-dark">Data Toko</h6>
                        
                        <!-- Alamat -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Alamat</label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Alamat lengkap toko...">{{ old('address', $setting->address ?? '') }}</textarea>
                        </div>

                        <!-- Jam Buka -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Jam Buka</label>
                            <input type="text" name="open_time" class="form-control" 
                                   value="{{ old('open_time', $setting->open_time ?? '') }}" 
                                   placeholder="08:00 am - 12:00 pm">
                        </div>

                        <!-- Deskripsi Dokumen -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Deskripsi Dokumen</label>
                            <textarea name="document_description" class="form-control" rows="3" 
                                      placeholder="Dokumen yang diperlukan untuk sewa...">{{ old('document_description', $setting->document_description ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Footer Section -->
                    <div class="mb-5">
                        <h6 class="fw-semibold mb-3 text-dark">Footer</h6>
                        
                        <!-- Teks Footer -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Teks Footer</label>
                            <textarea name="footer_text" class="form-control" rows="2" 
                                      placeholder='Supported by <a href="https://example.com" target="_blank">Example</a>'>{{ old('footer_text', $setting->footer_text ?? '') }}</textarea>
                            <small class="text-muted">Anda bisa menggunakan HTML</small>
                        </div>
                    </div>

                    <!-- Payment Gateway Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3 text-dark">Payment Gateway (Midtrans)</h6>
                        
                        <!-- Midtrans Mode -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Midtrans Mode</label>
                            <select name="midtrans_mode" class="form-select">
                                <option value="sandbox">Sandbox</option>
                                <option value="production">Production</option>
                            </select>
                        </div>

                        <!-- Midtrans Client Key -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Midtrans Client Key</label>
                            <input type="text" name="midtrans_client_key" class="form-control" 
                                   value="{{ old('midtrans_client_key', $setting->midtrans_client_key ?? '') }}" 
                                   placeholder="SB-Mid-client-xxx">
                        </div>

                        <!-- Midtrans Server Key -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Midtrans Server Key</label>
                            <input type="text" name="midtrans_server_key" class="form-control" 
                                   value="{{ old('midtrans_server_key', $setting->midtrans_server_key ?? '') }}" 
                                   placeholder="SB-Mid-server-xxx">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="reset" class="btn btn-light px-4">
                            <i class="bi bi-x-circle me-2"></i>Reset
                        </button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i>Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border-radius: 8px;
    }
    
    .card-header {
        border-radius: 8px 8px 0 0;
    }
    
    .form-label {
        color: #333;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 0.625rem 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #ee4d2d;
        box-shadow: 0 0 0 0.2rem rgba(238, 77, 45, 0.15);
    }
    
    .btn {
        border-radius: 6px;
        padding: 0.625rem 1.25rem;
        font-weight: 500;
    }
    
    .btn-primary {
        background-color: #ee4d2d;
        border-color: #ee4d2d;
    }
    
    .btn-primary:hover {
        background-color: #d43d1d;
        border-color: #d43d1d;
    }
    
    .btn-light {
        background-color: #f8f9fa;
        border-color: #e0e0e0;
        color: #666;
    }
    
    .btn-light:hover {
        background-color: #e9ecef;
        border-color: #d0d0d0;
    }
    
    h6 {
        color: #333;
        font-size: 1rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0.5rem;
    }
</style>
@endpush
@endsection