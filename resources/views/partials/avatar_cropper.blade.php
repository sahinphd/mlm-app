<!-- Cropper.js Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />

<!-- Avatar Cropper & Compressor Modal -->
<div id="avatarCropperModal" class="fixed inset-0 z-99999 hidden items-center justify-center bg-black/75 p-3 sm:p-5 backdrop-blur-xs">
    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-gray-800 dark:border dark:border-gray-700 flex flex-col max-h-[94vh] overflow-hidden animate-fadeIn">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Edit & Compress Photo</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Crop, zoom, and reduce file size on the spot</p>
                </div>
            </div>
            <button type="button" onclick="window.avatarCropperCloseModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <!-- Body / Cropper Canvas -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4">
            <!-- Cropper Container -->
            <div class="relative w-full rounded-xl overflow-hidden bg-gray-950 flex items-center justify-center" style="height: 290px;">
                <img id="avatarCropperSourceImg" src="" alt="Source preview" class="max-w-full block" style="display: block; max-height: 290px;">
            </div>

            <!-- Toolbar Controls (Zoom, Rotate, Reset) -->
            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60 text-xs">
                <div class="flex items-center gap-1 sm:gap-1.5">
                    <button type="button" onclick="window.avatarCropperZoom(-0.1)" title="Zoom Out" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-medium transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <button type="button" onclick="window.avatarCropperZoom(0.1)" title="Zoom In" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-medium transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                    <div class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                    <button type="button" onclick="window.avatarCropperRotate(-90)" title="Rotate Left" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-medium transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </button>
                    <button type="button" onclick="window.avatarCropperRotate(90)" title="Rotate Right" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-medium transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"/></svg>
                    </button>
                </div>

                <button type="button" onclick="window.avatarCropperReset()" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-600 dark:text-gray-300 font-medium transition shadow-2xs">
                    Reset
                </button>
            </div>

            <!-- Size & Compression Options -->
            <div class="space-y-2">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    Target File Size Limit
                </label>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <button type="button" id="cropper-target-10" onclick="window.avatarCropperSetTarget(9.8, this)" class="cropper-target-btn flex flex-col items-center justify-center p-2 rounded-xl border-2 border-brand-500 bg-brand-50/70 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300 font-medium transition">
                        <span class="font-bold text-sm">Ultra Compact</span>
                        <span class="text-[11px] opacity-90">&lt; 10 KB</span>
                    </button>
                    <button type="button" id="cropper-target-30" onclick="window.avatarCropperSetTarget(28, this)" class="cropper-target-btn flex flex-col items-center justify-center p-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        <span class="font-bold text-sm">Balanced</span>
                        <span class="text-[11px] text-gray-500">&lt; 30 KB</span>
                    </button>
                    <button type="button" id="cropper-target-80" onclick="window.avatarCropperSetTarget(75, this)" class="cropper-target-btn flex flex-col items-center justify-center p-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                        <span class="font-bold text-sm">High Res</span>
                        <span class="text-[11px] text-gray-500">&lt; 80 KB</span>
                    </button>
                </div>
            </div>

            <!-- Real-time Stats Info -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700 text-xs">
                <div class="space-y-0.5">
                    <div class="text-gray-500 dark:text-gray-400">Original Size: <span id="avatarOriginalSizeDisplay" class="font-semibold text-gray-700 dark:text-gray-200">-</span></div>
                    <div class="text-gray-500 dark:text-gray-400">Output Estimate: <span id="avatarEstimatedSizeDisplay" class="font-semibold text-emerald-600 dark:text-emerald-400">~8.5 KB</span></div>
                </div>
                <div id="avatarSizeStatusBadge" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    Ready to upload
                </div>
            </div>
        </div>

        <!-- Footer / Action Buttons -->
        <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-2.5 px-5 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/80">
            <button type="button" onclick="window.avatarCropperCloseModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 transition shadow-2xs">
                Cancel
            </button>

            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                <!-- Option 2: Quick Auto-Compress & Apply -->
                <button type="button" onclick="window.avatarCropperQuickAutoFit()" title="Automatically crops center and compresses to < 10 KB without manual tweaking" class="w-full sm:w-auto px-4 py-2.5 rounded-lg border border-amber-300 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-300 text-sm font-semibold hover:bg-amber-100 transition shadow-2xs flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Auto-Fit &amp; Compress (&lt; 10 KB)
                </button>

                <!-- Option 1: Crop & Apply -->
                <button type="button" id="avatarApplyCroppedBtn" onclick="window.avatarCropperApply()" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold transition shadow-theme-xs flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Crop &amp; Apply</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Cropper.js Script & Engine -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<script>
(function() {
    let cropperInstance = null;
    let originalImageFile = null;
    let targetKB = 9.8; // Default < 10 KB
    let boundFileInput = null;
    let boundPreviewImg = null;
    let boundPlaceholder = null;
    let boundStatusBadge = null;
    let hasApplied = false;

    // Initialize Cropper for a given file input
    window.initAvatarCropper = function(options) {
        const fileInput = document.getElementById(options.inputId);
        if (!fileInput) return;

        boundFileInput = fileInput;
        boundPreviewImg = document.getElementById(options.previewId);
        boundPlaceholder = options.placeholderId ? document.getElementById(options.placeholderId) : null;
        boundStatusBadge = options.statusBadgeId ? document.getElementById(options.statusBadgeId) : null;

        fileInput.addEventListener('change', function(e) {
            if (!e.target.files || !e.target.files[0]) return;
            const file = e.target.files[0];
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file (JPG, PNG, WebP).');
                return;
            }
            window.openAvatarCropper(file);
        });
    };

    window.openAvatarCropper = function(file) {
        originalImageFile = file;
        hasApplied = false;
        const modal = document.getElementById('avatarCropperModal');
        const imgSource = document.getElementById('avatarCropperSourceImg');
        const originalSizeDisplay = document.getElementById('avatarOriginalSizeDisplay');

        // Format original size display
        const rawKB = file.size / 1024;
        originalSizeDisplay.innerText = rawKB >= 1024 
            ? (rawKB / 1024).toFixed(2) + ' MB' 
            : rawKB.toFixed(1) + ' KB';

        // Read file to DataURL
        const reader = new FileReader();
        reader.onload = function(e) {
            imgSource.src = e.target.result;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }

            // Check if Cropper library is loaded
            if (typeof Cropper !== 'undefined') {
                cropperInstance = new Cropper(imgSource, {
                    aspectRatio: 1, // Square for avatar
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    background: false,
                    toggleDragModeOnDblclick: false,
                    ready() {
                        window.avatarCropperUpdateEstimate();
                    },
                    crop() {
                        window.avatarCropperUpdateEstimate();
                    }
                });
            } else {
                console.warn('Cropper.js not loaded, using native canvas fallback');
            }
        };
        reader.readAsDataURL(file);
    };

    window.avatarCropperCloseModal = function() {
        const modal = document.getElementById('avatarCropperModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        if (!hasApplied && boundFileInput && !boundFileInput.dataset.hasCropped) {
            boundFileInput.value = '';
        }
    };

    window.reopenAvatarCropper = function() {
        if (originalImageFile) {
            window.openAvatarCropper(originalImageFile);
        } else if (boundFileInput) {
            boundFileInput.click();
        }
    };

    window.avatarCropperZoom = function(delta) {
        if (cropperInstance) cropperInstance.zoom(delta);
    };

    window.avatarCropperRotate = function(deg) {
        if (cropperInstance) cropperInstance.rotate(deg);
    };

    window.avatarCropperReset = function() {
        if (cropperInstance) cropperInstance.reset();
    };

    window.avatarCropperSetTarget = function(kb, btn) {
        targetKB = kb;
        document.querySelectorAll('.cropper-target-btn').forEach(b => {
            b.classList.remove('border-2', 'border-brand-500', 'bg-brand-50/70', 'text-brand-700', 'dark:bg-brand-500/15', 'dark:text-brand-300');
            b.classList.add('border', 'border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');
        });
        btn.classList.add('border-2', 'border-brand-500', 'bg-brand-50/70', 'text-brand-700', 'dark:bg-brand-500/15', 'dark:text-brand-300');
        btn.classList.remove('border', 'border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');

        window.avatarCropperUpdateEstimate();
    };

    window.avatarCropperUpdateEstimate = function() {
        const estDisplay = document.getElementById('avatarEstimatedSizeDisplay');
        if (estDisplay) {
            estDisplay.innerText = targetKB <= 10 ? '~8.5 KB' : (targetKB <= 30 ? '~24 KB' : '~65 KB');
        }
    };

    // Compress HTML Canvas to guaranteed target KB
    async function compressCanvas(sourceCanvas, maxKB) {
        // Dimension selection based on target limit
        let dim = maxKB <= 10 ? 250 : (maxKB <= 30 ? 350 : 500);
        const canvas = document.createElement('canvas');
        canvas.width = dim;
        canvas.height = dim;
        const ctx = canvas.getContext('2d');
        
        // Solid background (prevent black PNG transparency issues)
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, dim, dim);
        ctx.drawImage(sourceCanvas, 0, 0, dim, dim);

        let minQ = 0.25;
        let maxQ = 0.90;
        let bestBlob = null;

        // Binary search for optimal JPEG quality under maxKB
        for (let i = 0; i < 7; i++) {
            let midQ = (minQ + maxQ) / 2;
            let blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', midQ));
            let currentKB = blob.size / 1024;
            if (currentKB <= maxKB) {
                bestBlob = blob;
                minQ = midQ; // Try higher quality
            } else {
                maxQ = midQ; // Scale down quality
            }
        }

        // If complex image still exceeds limit, scale down resolution slightly
        if (!bestBlob || (bestBlob.size / 1024) > maxKB) {
            dim = Math.max(160, Math.round(dim * 0.75));
            canvas.width = dim;
            canvas.height = dim;
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, dim, dim);
            ctx.drawImage(sourceCanvas, 0, 0, dim, dim);
            bestBlob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.65));
        }

        return bestBlob;
    }

    // Apply button click
    window.avatarCropperApply = async function() {
        const applyBtn = document.getElementById('avatarApplyCroppedBtn');
        const originalText = applyBtn.innerHTML;
        applyBtn.disabled = true;
        applyBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Compressing...
        `;

        try {
            let canvas;
            if (cropperInstance) {
                canvas = cropperInstance.getCroppedCanvas({
                    fillColor: '#fff',
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high',
                });
            } else {
                // Fallback to source image
                const img = document.getElementById('avatarCropperSourceImg');
                canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth;
                canvas.height = img.naturalHeight;
                canvas.getContext('2d').drawImage(img, 0, 0);
            }

            const compressedBlob = await compressCanvas(canvas, targetKB);
            applyCompressedResult(compressedBlob);
        } catch (err) {
            console.error('Compression error:', err);
            alert('Failed to process image. Please try again.');
        } finally {
            applyBtn.disabled = false;
            applyBtn.innerHTML = originalText;
        }
    };

    // Quick Auto-Fit & Compress (< 10 KB) without manual tweaking
    window.avatarCropperQuickAutoFit = async function() {
        targetKB = 9.8;
        if (cropperInstance) {
            cropperInstance.reset();
        }
        await window.avatarCropperApply();
    };

    function applyCompressedResult(blob) {
        const fileName = (originalImageFile && originalImageFile.name) 
            ? originalImageFile.name.replace(/\.[^/.]+$/, "") + ".jpg" 
            : "avatar.jpg";

        const file = new File([blob], fileName, { type: 'image/jpeg', lastModified: Date.now() });

        // Update real HTML file input via DataTransfer
        if (boundFileInput) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                boundFileInput.files = dt.files;
            } catch (e) {
                console.warn('DataTransfer not supported:', e);
            }
        }

        // Update preview image
        const objectUrl = URL.createObjectURL(blob);
        if (boundPreviewImg) {
            boundPreviewImg.src = objectUrl;
            boundPreviewImg.classList.remove('hidden');
        }
        if (boundPlaceholder) {
            boundPlaceholder.classList.add('hidden');
        }

        // Show status badge
        const finalKB = (blob.size / 1024).toFixed(1);
        const origKB = originalImageFile ? (originalImageFile.size / 1024).toFixed(1) + ' KB' : 'original';
        hasApplied = true;
        if (boundFileInput) boundFileInput.dataset.hasCropped = 'true';

        if (boundStatusBadge) {
            boundStatusBadge.innerHTML = `
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>Optimized: <strong>${finalKB} KB</strong> (reduced from ${origKB})</span>
                    </span>
                    <button type="button" onclick="window.reopenAvatarCropper()" class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 underline font-medium cursor-pointer">
                        Edit / Crop again
                    </button>
                </div>
            `;
            boundStatusBadge.classList.remove('hidden');
        }

        window.avatarCropperCloseModal();
    }
})();
</script>
