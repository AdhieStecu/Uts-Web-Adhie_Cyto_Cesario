// File: assets/js/main.js
// Auto-hide flash messages after 4 detik
document.addEventListener('DOMContentLoaded', function () {
    const flash = document.querySelector('.flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 4000);
    }

    // Mobile nav toggle
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.querySelector('.main-nav .container');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => mainNav.classList.toggle('open'));
    }

    // Global Confirmation Dialog with SweetAlert2
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm]');
        if (btn) {
            e.preventDefault();
            const text = btn.getAttribute('data-confirm') || 'Apakah Anda yakin?';
            const form = btn.closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Tindakan',
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal',
                    background: '#0d1530',
                    color: '#e8f0fe',
                    confirmButtonColor: '#ff4444',
                    cancelButtonColor: '#445577',
                    customClass: {
                        popup: 'swal2-custom-popup',
                        title: 'swal2-custom-title',
                        htmlContainer: 'swal2-custom-text',
                        confirmButton: 'swal2-custom-confirm-btn',
                        cancelButton: 'swal2-custom-cancel-btn'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (form) {
                            if (btn.getAttribute('name')) {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = btn.getAttribute('name');
                                hiddenInput.value = btn.getAttribute('value') || '';
                                form.appendChild(hiddenInput);
                            }
                            form.submit();
                        } else if (btn.tagName === 'A') {
                            window.location.href = btn.getAttribute('href');
                        }
                    }
                });
            } else {
                // Fallback to browser confirm
                if (confirm(text)) {
                    if (form) {
                        form.submit();
                    } else if (btn.tagName === 'A') {
                        window.location.href = btn.getAttribute('href');
                    }
                }
            }
        }
    });
});

// Reusable image cropping helper with Cropper.js
function initImageCropper(fileInputId, hiddenInputId, previewId, aspectRatio = NaN) {
    const fileInput = document.getElementById(fileInputId);
    if (!fileInput) return;

    // Create modal elements dynamically if they don't exist
    let cropModal = document.getElementById('cropModal');
    if (!cropModal) {
        cropModal = document.createElement('div');
        cropModal.id = 'cropModal';
        cropModal.style.cssText = 'display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(5,8,24,0.9);z-index:99999;align-items:center;justify-content:center;padding:20px;';
        cropModal.innerHTML = `
            <div style="background:var(--bg-card);border:1px solid rgba(0, 102, 255, 0.3);border-radius:16px;padding:24px;width:100%;max-width:600px;box-shadow:0 0 30px rgba(0,102,255,0.25);">
                <h3 style="font-family:var(--font-head);margin-bottom:16px;color:var(--text-primary);display:flex;align-items:center;gap:10px;">✂️ Potong & Sesuaikan Gambar</h3>
                <div style="max-height:350px;overflow:hidden;background:#050818;border-radius:8px;border:1px solid var(--border);margin-bottom:20px;display:flex;justify-content:center;align-items:center;">
                    <img id="cropImageSrc" style="max-width:100%;max-height:350px;display:block;">
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" id="btnCancelCrop" class="btn btn-outline">Batal</button>
                    <button type="button" id="btnConfirmCrop" class="btn btn-primary">✂️ Potong Gambar</button>
                </div>
            </div>
        `;
        document.body.appendChild(cropModal);
    }

    const cropImageSrc = document.getElementById('cropImageSrc');
    const btnCancelCrop = document.getElementById('btnCancelCrop');
    const btnConfirmCrop = document.getElementById('btnConfirmCrop');
    let cropper = null;

    fileInput.addEventListener('change', function (e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            const reader = new FileReader();
            reader.onload = function (event) {
                // Destroy old cropper if exists
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }

                // Wait for image source to be loaded before initializing Cropper
                cropImageSrc.onload = function () {
                    cropper = new Cropper(cropImageSrc, {
                        aspectRatio: aspectRatio,
                        viewMode: 1,
                        background: false,
                        responsive: true,
                        autoCropArea: 0.9
                    });
                    // Remove onload listener to prevent multiple triggers
                    cropImageSrc.onload = null;
                };

                cropImageSrc.src = event.target.result;
                cropModal.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    });

    btnCancelCrop.addEventListener('click', function () {
        cropModal.style.display = 'none';
        fileInput.value = ''; // Reset file input
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    btnConfirmCrop.addEventListener('click', function () {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({
                width: 800, // Limit maximum width for performance & storage
                height: 800
            });

            if (canvas) {
                const base64Data = canvas.toDataURL('image/jpeg', 0.85); // Compress slightly

                // Set hidden field value
                let hiddenInput = document.getElementById(hiddenInputId);
                if (!hiddenInput) {
                    hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.id = hiddenInputId;
                    hiddenInput.name = hiddenInputId;
                    fileInput.form.appendChild(hiddenInput);
                }
                hiddenInput.value = base64Data;

                // Set preview image if preview element exists
                const previewEl = document.getElementById(previewId);
                if (previewEl) {
                    previewEl.src = base64Data;
                    previewEl.style.display = 'block';
                }
            } else {
                console.error('Cropper failed to get cropped canvas.');
            }

            cropModal.style.display = 'none';
            cropper.destroy();
            cropper = null;
        }
    });
};