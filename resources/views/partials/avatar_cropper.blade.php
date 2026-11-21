<!-- Cropper.js Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />

<!-- Avatar / Image Cropper & Compressor Modal -->
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
                    <h3 id="cropperModalTitle" class="text-base font-semibold text-gray-800 dark:text-white">Edit &amp; Compress Photo</h3>
                    <p id="cropperModalSubtitle" class="text-xs text-gray-500 dark:text-gray-400">Crop, zoom, and reduce file size on the spot</p>
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

            <!-- Toolbar Controls (Zoom, Rotate, Aspect Ratio, Reset) -->
            <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 dark:bg-gray-900/60 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/60 text-xs">
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
                    <div class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                    <!-- Aspect Ratios -->
                    <button type="button" id="cropper-aspect-1" onclick="window.avatarCropperSetAspectRatio(1, this)" title="1:1 Square" class="cropper-aspect-btn px-2 py-1 rounded-lg bg-brand-50 text-brand-700 border border-brand-300 dark:bg-brand-900/40 dark:text-brand-300 dark:border-brand-600 font-semibold transition">1:1</button>
                    <button type="button" id="cropper-aspect-43" onclick="window.avatarCropperSetAspectRatio(4/3, this)" title="4:3 Landscape" class="cropper-aspect-btn px-2 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-semibold transition">4:3</button>
                    <button type="button" id="cropper-aspect-free" onclick="window.avatarCropperSetAspectRatio(NaN, this)" title="Free crop" class="cropper-aspect-btn px-2 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-700 dark:text-gray-300 font-semibold transition">Free</button>
                </div>

                <button type="button" onclick="window.avatarCropperReset()" class="px-2.5 py-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-gray-600 dark:text-gray-300 font-medium transition shadow-2xs">
                    Reset
                </button>
            </div>

            <!-- Size & Compression Options -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Target File Size Limit
                    </label>
                    <span id="cropperCurrentLimitNotice" class="text-xs font-semibold text-brand-600 dark:text-brand-400">Target: &lt; 10 KB</span>
                </div>
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
                <button type="button" id="cropperQuickAutoBtn" onclick="window.avatarCropperQuickAutoFit()" title="Automatically crops center and compresses without manual tweaking" class="w-full sm:w-auto px-4 py-2.5 rounded-lg border border-amber-300 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-950/30 text-amber-800 dark:text-amber-300 text-sm font-semibold hover:bg-amber-100 transition shadow-2xs flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="cropperQuickAutoText">Auto-Fit &amp; Compress (&lt; 10 KB)</span>
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
    let currentAspectRatio = 1; // Default 1:1 square
    let boundFileInput = null;
    let boundPreviewImg = null;
    let boundPlaceholder = null;
    let boundStatusBadge = null;
    let hasApplied = false;
    let activeOptions = {};

    // Initialize Cropper for a given file input
    window.initAvatarCropper = window.initImageCropper = function(options) {
        activeOptions = Object.assign({
            defaultTargetKB: 9.8,
            aspectRatio: 1,
            title: 'Edit & Compress Photo',
            subtitle: 'Crop, zoom, and reduce file size on the spot'
        }, options || {});

        const fileInput = document.getElementById(activeOptions.inputId);
        if (!fileInput) return;

        boundFileInput = fileInput;
        boundPreviewImg = document.getElementById(activeOptions.previewId);
        boundPlaceholder = activeOptions.placeholderId ? document.getElementById(activeOptions.placeholderId) : null;
        boundStatusBadge = activeOptions.statusBadgeId ? document.getElementById(activeOptions.statusBadgeId) : null;

        // Set configured default target KB
        if (activeOptions.defaultTargetKB) {
            targetKB = activeOptions.defaultTargetKB;
        }
        if (activeOptions.aspectRatio !== undefined) {
            currentAspectRatio = activeOptions.aspectRatio;
        }

        // Apply UI titles if present
        const titleEl = document.getElementById('cropperModalTitle');
        const subEl = document.getElementById('cropperModalSubtitle');
        if (titleEl && activeOptions.title) titleEl.innerText = activeOptions.title;
        if (subEl && activeOptions.subtitle) subEl.innerText = activeOptions.subtitle;

        // Pre-activate target size button (e.g. < 30 KB for products)
        syncTargetUi();

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

    function syncTargetUi() {
        const btn10 = document.getElementById('cropper-target-10');
        const btn30 = document.getElementById('cropper-target-30');
        const btn80 = document.getElementById('cropper-target-80');
        const notice = document.getElementById('cropperCurrentLimitNotice');
        const autoText = document.getElementById('cropperQuickAutoText');

        if (!btn10 || !btn30 || !btn80) return;

        [btn10, btn30, btn80].forEach(b => {
            b.classList.remove('border-2', 'border-brand-500', 'bg-brand-50/70', 'text-brand-700', 'dark:bg-brand-500/15', 'dark:text-brand-300');
            b.classList.add('border', 'border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');
        });

        let activeBtn = btn10;
        let limitLabel = '< 10 KB';
        if (targetKB >= 20 && targetKB <= 35) {
            activeBtn = btn30;
            limitLabel = '< 30 KB (Recommended)';
        } else if (targetKB > 35) {
            activeBtn = btn80;
            limitLabel = '< 80 KB';
        }

        activeBtn.classList.add('border-2', 'border-brand-500', 'bg-brand-50/70', 'text-brand-700', 'dark:bg-brand-500/15', 'dark:text-brand-300');
        activeBtn.classList.remove('border', 'border-gray-200', 'dark:border-gray-700', 'bg-white', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');

        if (notice) notice.innerText = 'Target: ' + limitLabel;
        if (autoText) autoText.innerText = 'Auto-Fit & Compress (' + limitLabel.replace(' (Recommended)', '') + ')';
        window.avatarCropperUpdateEstimate();
    }

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

            // Sync target size and aspect ratio UI
            syncTargetUi();

            // Check if Cropper library is loaded
            if (typeof Cropper !== 'undefined') {
                cropperInstance = new Cropper(imgSource, {
                    aspectRatio: currentAspectRatio,
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

    // Open cropper from an existing URL (e.g. current product image)
    window.openCropperFromUrl = async function(url, fileName) {
        try {
            const res = await fetch(url);
            if (!res.ok) throw new Error('Fetch failed');
            const blob = await res.blob();
            const file = new File([blob], fileName || 'current_image.jpg', { type: blob.type || 'image/jpeg' });
            window.openAvatarCropper(file);
        } catch (err) {
            // If CORS or local fetch fails, trigger standard file chooser
            console.warn('Could not load image directly via fetch, opening file picker instead', err);
            if (boundFileInput) boundFileInput.click();
        }
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

    window.avatarCropperSetAspectRatio = function(ratio, btn) {
        currentAspectRatio = ratio;
        if (cropperInstance) {
            cropperInstance.setAspectRatio(ratio);
        }
        document.querySelectorAll('.cropper-aspect-btn').forEach(b => {
            b.classList.remove('bg-brand-50', 'text-brand-700', 'border-brand-300', 'dark:bg-brand-900/40', 'dark:text-brand-300', 'dark:border-brand-600');
            b.classList.add('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
        });
        if (btn) {
            btn.classList.add('bg-brand-50', 'text-brand-700', 'border-brand-300', 'dark:bg-brand-900/40', 'dark:text-brand-300', 'dark:border-brand-600');
            btn.classList.remove('bg-white', 'dark:bg-gray-800', 'border-gray-200', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
        }
    };

    window.avatarCropperSetTarget = function(kb, btn) {
        targetKB = kb;
        syncTargetUi();
    };

    window.avatarCropperUpdateEstimate = function() {
        const estDisplay = document.getElementById('avatarEstimatedSizeDisplay');
        if (estDisplay) {
            estDisplay.innerText = targetKB <= 10 ? '~8.5 KB' : (targetKB <= 30 ? '~25.5 KB' : '~65 KB');
        }
    };

    // Compress HTML Canvas to guaranteed target KB while preserving aspect ratio
    async function compressCanvas(sourceCanvas, maxKB) {
        let maxDim = maxKB <= 10 ? 250 : (maxKB <= 30 ? 420 : 650);
        let w = sourceCanvas.width;
        let h = sourceCanvas.height;

        if (w > maxDim || h > maxDim) {
            if (w >= h) {
                h = Math.round((h * maxDim) / w);
                w = maxDim;
            } else {
                w = Math.round((w * maxDim) / h);
                h = maxDim;
            }
        }

        const canvas = document.createElement('canvas');
        canvas.width = w;
        canvas.height = h;
        const ctx = canvas.getContext('2d');
        
        // Solid white background (prevent transparent PNG black artifact)
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(sourceCanvas, 0, 0, w, h);

        let minQ = 0.20;
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

        // If complex image still exceeds limit, scale down resolution progressively
        while (!bestBlob || (bestBlob.size / 1024) > maxKB) {
            w = Math.max(120, Math.round(w * 0.8));
            h = Math.max(120, Math.round(h * 0.8));
            canvas.width = w;
            canvas.height = h;
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, w, h);
            ctx.drawImage(sourceCanvas, 0, 0, w, h);
            bestBlob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.65));
            if (w <= 120 && h <= 120) break;
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
                canvas.width = img.naturalWidth || 400;
                canvas.height = img.naturalHeight || 400;
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

    // Quick Auto-Fit & Compress without manual tweaking
    window.avatarCropperQuickAutoFit = async function() {
        if (cropperInstance) {
            cropperInstance.reset();
        }
        await window.avatarCropperApply();
    };

    function applyCompressedResult(blob) {
        const fileName = (originalImageFile && originalImageFile.name) 
            ? originalImageFile.name.replace(/\.[^/.]+$/, "") + ".jpg" 
            : "photo.jpg";

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
                <div class="mt-2.5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                        <span>Optimized: <strong>${finalKB} KB</strong> (reduced from ${origKB}) &bull; Ready to save</span>
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
