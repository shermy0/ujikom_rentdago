@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-eye me-2"></i>Detail Barang
            </h5>
        </div>
        <div class="card-body">
            <!-- Product Images -->
            @if ($product->images->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3">
                        <i class="bi bi-images me-2"></i>Gambar Produk
                    </h6>
                    <div class="row g-3">
                        @foreach ($product->images as $image)
                            <div class="col-md-3">
                                <div class="image-card">
                                    <img src="{{ asset($image->image_path) }}" alt="Product Image"
                                        class="img-thumbnail"
                                        style="width: 100%; height: 200px; object-fit: cover; cursor: pointer;"
                                        onclick="showImageModal('{{ asset($image->image_path) }}')">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <hr>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Kode Produk</th>
                                <td><code class="fs-6">{{ $product->code }}</code></td>
                            </tr>
                            <tr>
                                <th>Nama Barang</th>
                                <td><strong>{{ $product->name }}</strong></td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td><span class="badge bg-info">{{ $product->category->name }}</span></td>
                            </tr>
                            <tr>
                                <th>Kondisi</th>
                                <td>{{ $product->condition ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if ($product->is_maintenance)
                                        <span class="badge bg-danger"><i class="bi bi-tools me-1"></i>Maintenance</span>
                                    @else
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Dibuat Pada</th>
                                <td>{{ $product->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Terakhir Update</th>
                                <td>{{ $product->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            @if ($product->description)
                <hr>
                <div class="mb-3">
                    <strong class="d-block mb-2">Deskripsi:</strong>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
            @endif

            <hr class="my-4">

            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Modal untuk preview gambar full size -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <img id="modalImage" src="" alt="Full Image" style="width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .card {
                border: none;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
                border-radius: 12px;
            }

            .card-header {
                background: white;
                border-bottom: 1px solid #f0f0f0;
                padding: 20px 25px;
                border-radius: 12px 12px 0 0 !important;
            }

            .card-body {
                padding: 25px;
            }

            .btn-primary {
                background: linear-gradient(135deg, #ee4d2d, #ff6b35);
                border: none;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #d94429, #e55a2b);
            }

            .table th {
                font-weight: 600;
                color: #666;
            }

            .badge {
                font-weight: 500;
                padding: 6px 10px;
            }

            code {
                color: #666;
                background: #f8f9fa;
                padding: 4px 8px;
                border-radius: 4px;
            }

            .image-card {
                border: 2px solid #f0f0f0;
                border-radius: 8px;
                padding: 5px;
                background: white;
                transition: transform 0.2s;
            }

            .image-card:hover {
                transform: scale(1.05);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function showImageModal(imageSrc) {
                document.getElementById('modalImage').src = imageSrc;
                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            }
        </script>
    @endpush
@endsection
