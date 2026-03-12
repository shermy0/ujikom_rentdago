<style>
/* --- Layout Utama --- */
.scan-container {
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
}

.scan-content {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* --- Camera Container --- */
.camera-container {
    background: #000;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    margin-bottom: 1.5rem;
    position: relative;
    min-height: 300px;
}

/* --- FIX DOBLE & VIDEO --- */
#preview {
    width: 100%;
    height: 100%;
    position: relative;
}

#preview video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border-radius: 16px;
}

#qr-shaded-region,
#preview>div:not(video) {
    display: none !important;
    opacity: 0 !important;
}

/* --- Overlay Custom --- */
.camera-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 260px;
    height: 260px;
    z-index: 10;
    box-shadow: 0 0 0 100vmax rgba(0, 0, 0, 0.6);
    border-radius: 20px;
    pointer-events: none;
}

.camera-corner {
    position: absolute;
    width: 40px;
    height: 40px;
    border: 4px solid #fff;
    border-radius: 4px;
}

.top-left {
    top: -2px;
    left: -2px;
    border-right: 0;
    border-bottom: 0;
}

.top-right {
    top: -2px;
    right: -2px;
    border-left: 0;
    border-bottom: 0;
}

.bottom-left {
    bottom: -2px;
    left: -2px;
    border-right: 0;
    border-top: 0;
}

.bottom-right {
    bottom: -2px;
    right: -2px;
    border-left: 0;
    border-top: 0;
}

.scan-laser {
    position: absolute;
    width: 100%;
    height: 2px;
    background: #ff5722;
    box-shadow: 0 0 4px #770C0C;
    top: 0;
    animation: scanMove 2s infinite linear;
    opacity: 0.7;
}

@keyframes scanMove {
    0% {
        top: 0;
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        top: 100%;
        opacity: 0;
    }
}

.scan-instruction-overlay {
    position: absolute;
    bottom: 30px;
    left: 0;
    width: 100%;
    text-align: center;
    color: #fff;
    z-index: 20;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8);
    font-size: 0.95rem;
}

.retry-button-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 30;
    padding: 12px 24px;
    background: #fff;
    color: #770C0C;
    border: none;
    border-radius: 30px;
    font-weight: bold;
    display: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    cursor: pointer;
}

/* Manual Input Card */
.manual-input-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
}

.manual-input-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}

.manual-input-icon {
    width: 36px;
    height: 36px;
    background: #fff4e6;
    color: #770C0C;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.manual-input-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
}

.input-group {
    display: flex;
    gap: 0.5rem;
}

.manual-code-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.manual-code-input:focus {
    outline: none;
    border-color: #770C0C;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.manual-code-input::placeholder {
    color: #adb5bd;
}

.btn-verify-manual {
    padding: 12px 24px;
    background: linear-gradient(135deg, #770C0C 0%, #A20B0B 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
}

.btn-verify-manual:active {
    transform: scale(0.98);
}

.btn-verify-manual:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Alert */
.alert {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: none;
    font-size: 0.9rem;
}

.alert.show {
    display: block;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* SweetAlert Custom */
.small-swal-button {
    padding: 8px 16px !important;
    font-size: 0.85rem !important;
}

.swal2-title {
    font-size: 1.1rem !important;
    padding-top: 0 !important;
}

.swal2-html-container {
    font-size: 0.85rem !important;
}

.swal2-cancel {
    color: #666 !important;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.confirmed {
    background: #fff3cd;
    color: #856404;
}

.status-badge.ongoing {
    background: #d4edda;
    color: #155724;
}

.camera-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 3 / 4;
    max-height: 400px;
    background: #000;
    overflow: hidden;
    border-radius: 16px;
}


#cameraPreview,
#photoCanvas {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.scan-tabs {
    display: flex;
    border-bottom: 1px solid #ddd;
    margin-bottom: 16px;
}

.tab-button {
    flex: 1;
    padding: 10px;
    background: #f8f9fa;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: 0.2s;
}

.tab-button.active {
    border-bottom: 3px solid #A20B0B;
    background: #fff;
    color: #A20B0B;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

</style>