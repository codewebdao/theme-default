// iMagify - Image Editor Library
// Crop Boxes change layer size
(function () {
    // Color strategy configuration
    const COLOR_STRATEGY = 'srgb';
    const LOCK_PREVIEW_TO_OUTPUT = true;
    
    // Decode bitmap with proper color space
    async function decodeBitmap(srcElOrURL, wantP3) {
      const opts = wantP3
        ? { colorSpaceConversion: 'none', premultiplyAlpha: 'none' }
        : { colorSpaceConversion: 'default', premultiplyAlpha: 'none' };

      try {
        const src = srcElOrURL?.src || srcElOrURL;
        if (typeof src === 'string' && (src.startsWith('http') || src.startsWith('data:') || src.startsWith('blob:'))) {
          const r = await fetch(src); 
          const b = await r.blob();
          return await createImageBitmap(b, opts);
        }
      } catch (_) {}
      return await createImageBitmap(srcElOrURL, opts);
    }
    
    // Canvas 2D context with proper color space
    function supportsP3Canvas2D() {
      try { 
        return !!document.createElement('canvas').getContext('2d', { colorSpace: 'display-p3' }); 
      } catch { 
        return false; 
      }
    }

    function get2D(canvas, opts = {}, wantP3 = false) {
      try {
        const desiredCS = wantP3 ? 'display-p3' : 'srgb';
        const ctx = canvas.getContext('2d', Object.assign({ colorSpace: desiredCS }, opts));
        if (ctx) return ctx;
      } catch (_) {}
      return canvas.getContext('2d', opts);
    }
    
    // Draw base canvas
    async function drawBase(mainImage, wantP3) {
      const c = document.createElement('canvas');
      c.width = mainImage.naturalWidth;
      c.height = mainImage.naturalHeight;
      
      const ctx = get2D(c, { alpha: true }, wantP3);
      const bmp = await decodeBitmap(mainImage, wantP3);
      
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      ctx.filter = 'none';
      ctx.globalCompositeOperation = 'source-over';
      
      ctx.drawImage(bmp, 0, 0, c.width, c.height);
      return c;
    }
    
    // Export to sRGB data URL
    function toSRGBDataURLFrom(canvas, mime = 'image/png', quality) {
      const ctx = canvas.getContext('2d');
      const cs = (ctx && ctx.colorSpace) || 'srgb';
      
      if (cs === 'srgb') {
        return canvas.toDataURL(mime, quality);
      }
      
      const out = document.createElement('canvas');
      out.width = canvas.width;
      out.height = canvas.height;
      const octx = get2D(out, { alpha: true }, false);
      octx.imageSmoothingEnabled = true;
      octx.imageSmoothingQuality = 'high';
      octx.drawImage(canvas, 0, 0);
      
      return out.toDataURL(mime, quality);
    }

    function exportCanvas(canvas, fmt = 'png', quality = 0.95, wantP3 = false) {
      if (fmt === 'png') {
        return toSRGBDataURLFrom(canvas, 'image/png');
      }
      if (fmt === 'jpeg' || fmt === 'jpg') {
        const t = document.createElement('canvas');
        t.width = canvas.width; 
        t.height = canvas.height;
        const tctx = get2D(t, { alpha: false }, false);
        tctx.fillStyle = '#ffffff';
        tctx.fillRect(0, 0, t.width, t.height);
        tctx.drawImage(canvas, 0, 0);
        const q = Math.min(1, Math.max(0.85, quality));
        return t.toDataURL('image/jpeg', q);
      }
      return toSRGBDataURLFrom(canvas, 'image/' + fmt, Math.min(1, Math.max(0.85, quality)));
    }
    
    // Lock preview to output
    function lockPreviewTo(canvas, imgEl) {
      try {
        const url = toSRGBDataURLFrom(canvas, 'image/png');
        if (imgEl.src !== url) {
          imgEl.src = url;
        }
      } catch (error) {
        // Silent fail
      }
    }
    // Core Initialization Functions
    function initializeCore(options) {
      this.options = options;
      this.currentImageIndex = 0;
      this.cropBoxes = [];
      this.selectedCropIndex = 0;
      this.cropStates = {};
      this.isProcessing = false;
      
      // Color strategy configuration
      this.colorStrategy = (options && options.colorStrategy) || COLOR_STRATEGY;
      this.hasP3 = supportsP3Canvas2D();
      this.wantP3 = (this.colorStrategy === 'p3') || (this.colorStrategy === 'auto' && this.hasP3);
      this.lockPreview = LOCK_PREVIEW_TO_OUTPUT;
      
      // Watermark and upload state
      this._watermarkInitialized = false;
      this.uploaded = new Array(options.images.length).fill(false);
      this.results = [];
      this.completeCallback = null;
      this.uploadCallback = null;
      
      // Crop box colors
      this.cropBoxColors = [
        { color: '#4CAF50', bgColor: '#388E3C' },
        { color: '#2196F3', bgColor: '#1976D2' },
        { color: '#9C27B0', bgColor: '#7B1FA2' },
        { color: '#FF9800', bgColor: '#F57C00' },
        { color: '#E91E63', bgColor: '#C2185B' },
        { color: '#00BCD4', bgColor: '#0097A7' },
        { color: '#FFC107', bgColor: '#FFA000' },
        { color: '#795548', bgColor: '#5D4037' },
        { color: '#607D8B', bgColor: '#455A64' },
        { color: '#F44336', bgColor: '#D32F2F' }
      ];
    }

    function setupGlobalErrorHandlers() {
      if (!window.__iMagifyErrorHooked) {
        window.addEventListener('error', function (e) {
          console.error('[iMagify]', e.message, e.filename+':'+e.lineno+':'+e.colno);
        });
        window.addEventListener('unhandledrejection', function (e) {
          console.error('[iMagify]', e.reason);
        });
        window.__iMagifyErrorHooked = true;
      }
    }

    // UI Creation Functions
    function createModalContainer() {
      this.modal = document.createElement("div");
      this.modal.id = "iMagify-modal";
      Object.assign(this.modal.style, {
        position: "fixed",
        top: "0",
        left: "0",
        width: "100vw",
        height: "100vh",
        backgroundColor: "#FFF",
        zIndex: "10000",
        display: "flex",
        flexDirection: "column"
      });
      document.body.appendChild(this.modal);
    }

    function createCloseButton() {
      this.closeBtn = document.createElement("div");
      this.closeBtn.innerHTML = "&#10006;";
      Object.assign(this.closeBtn.style, {
        position: "absolute",
        top: "10px",
        right: "10px",
        fontSize: "24px",
        cursor: "pointer",
        zIndex: "1200"
      });
      this.closeBtn.addEventListener("click", () => {
        document.body.removeChild(this.modal);
      });
      this.modal.appendChild(this.closeBtn);
    }

    function createThumbnailContainer() {
      this.thumbnailContainer = document.createElement("div");
      this.thumbnailContainer.id = "iMagify-thumbnails";
      Object.assign(this.thumbnailContainer.style, {
        flex: "0 0 80px",
        display: "flex",
        overflowX: "auto",
        padding: "8px",
        backgroundColor: "#111827",
        zIndex: "1100"
      });
      this.modal.appendChild(this.thumbnailContainer);
    }

    function createEditorContainer() {
      this.editorContainer = document.createElement("div");
      this.editorContainer.id = "iMagify-editor";
      Object.assign(this.editorContainer.style, {
        flex: "1",
        position: "relative",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        backgroundColor: "#333",
        overflow: "auto",
        paddingBottom: "100px"
      });
      this.modal.appendChild(this.editorContainer);
    }

    function createSidebar() {
      this.sidebar = document.createElement("div");
      this.sidebar.id = "iMagify-sidebar";
      Object.assign(this.sidebar.style, {
        position: "absolute",
        left: "0",
        top: "80px",
        bottom: "0",
        width: "180px",
        background: "#1f2937",
        color: "#e5e7eb",
        borderRight: "1px solid #374151",
        padding: "8px 6px",
        overflowY: "auto",
        zIndex: "1150"
      });
      this.modal.appendChild(this.sidebar);
      
      // Adjust editor margin for sidebar
      try { 
        this.editorContainer.style.marginLeft = '180px'; 
      } catch(_) {}
    }

    function createMainImage() {
      this.mainImage = document.createElement("img");
      this.mainImage.id = "iMagify-mainImage";
      Object.assign(this.mainImage.style, {
        maxWidth: "90%",
        maxHeight: "90%",
        position: "relative",
        filter: "none",
        opacity: "1",
        mixBlendMode: "normal"
      });
      
      this.editorContainer.appendChild(this.mainImage);
    }

    // Thumbnail Management Functions
    function createThumbnails() {
      this.options.images.forEach((imgSrc, index) => {
        const thumb = createSingleThumbnail.call(this, imgSrc, index);
        this.thumbnailContainer.appendChild(thumb);
      });
    }

    function createSingleThumbnail(imgSrc, index) {
      const thumb = document.createElement("img");
      thumb.className = "iMagify-thumbnail";
      thumb.src = imgSrc.src ? imgSrc.src : imgSrc;
      Object.assign(thumb.style, {
        width: "60px",
        height: "60px",
        objectFit: "cover",
        marginRight: "10px",
        cursor: "pointer",
        borderRadius: "4px",
        border: "2px solid transparent"
      });
      
      if (index === 0) {
        thumb.style.border = "2px solid #4CAF50";
        this.currentImageIndex = 0;
        this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;
      }
      
      thumb.addEventListener("click", () => {
        this.switchToImage(index, imgSrc);
      });
      
      return thumb;
    }

    function switchToImage(index, imgSrc) {
      document.querySelectorAll(".iMagify-thumbnail").forEach(el => 
        el.style.border = "2px solid transparent"
      );
      
      const thumb = document.querySelectorAll(".iMagify-thumbnail")[index];
      thumb.style.border = "2px solid #4CAF50";
      
      this.currentImageIndex = index;
      this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;
      
      // Restore crop state cho ảnh này
      this.applyCropState();
      
      this.mainImage.onload = () => { 
        this.updateCropBoxes(); 
        this.createSaveButton(); 
        this.buildSidebar();
      };
    }

    // Crop Box Management Functions
    function createCropBoxes() {
      if (!this.options.sizes) return;
      
      this.options.sizes.forEach((size, index) => {
        try {
          const box = createSingleCropBox.call(this, size, index);
          if (box) {
            this.editorContainer.appendChild(box);
            this.cropBoxes.push(box);
          }
        } catch (err) {
          console.error('[iMagify] Error creating cropBox index=' + index, err, size);
        }
      });
    }

    function createSingleCropBox(size, index) {
      const w = parseFloat(size.width);
      const h = parseFloat(size.height);
      
      if (!isFinite(w) || !isFinite(h) || w <= 0 || h <= 0) {
        return null;
      }
      
      const box = document.createElement("div");
      box.className = "iMagify-cropBox";
      box.dataset.ratio = w + "x" + h;
      
      // Tạo label
      addCropBoxLabel.call(this, box, size, index);
      
      // Tạo watermark container
      addWatermarkToCropBox.call(this, box, size, index);
      
      // Thiết lập styling
      setupCropBoxStyling.call(this, box, index);
      
      // Thêm event handlers
      addCropBoxEventHandlers.call(this, box, index);
      
      // Thêm drag & resize functionality
      addCropBoxInteractions.call(this, box, index, w, h);
      
      return box;
    }

    function addCropBoxLabel(box, size, index) {
      const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
      
      const label = document.createElement("div");
      label.className = "iMagify-cropBox-label";
      label.innerText = size.width + "x" + size.height;
      Object.assign(label.style, {
        position: "absolute",
        top: "-25px",
        left: "0",
        color: boxColor.color,
        fontWeight: "bold",
        backgroundColor: "rgba(255,255,255,0.9)",
        padding: "2px 5px",
        borderRadius: "3px",
        boxShadow: "0 1px 3px rgba(0,0,0,0.2)"
      });
      
      box.appendChild(label);
    }

    function addWatermarkToCropBox(box, size, index) {
      const wmContainer = createWatermarkContainer.call(this, box, size, index);
      const toggleBtn = createWatermarkToggle.call(this, box, wmContainer, index);
      
      box.appendChild(wmContainer);
      box.appendChild(toggleBtn);
      box.watermarkContainer = wmContainer;
    }

    function createWatermarkContainer(box, size, index) {
      const wmContainer = document.createElement("div");
      wmContainer.className = "iMagify-cropBox-watermark";
      wmContainer.dataset.boxIndex = index;
      Object.assign(wmContainer.style, {
        position: "absolute",
        cursor: "move",
        width: "100px",
        height: "auto",
        zIndex: "1200",
        display: "block",
        minWidth: "30px",
        minHeight: "30px",
        right: "10px",
        bottom: "10px"
      });

      // Tạo watermark image
      const wmImage = createWatermarkImage.call(this, size, index);
      wmContainer.appendChild(wmImage);

      // Setup watermark interactions
      setupWatermarkInteractions.call(this, wmContainer, box, index);

      return wmContainer;
    }

    function createWatermarkImage(size, index) {
      const wmImage = document.createElement("img");
      
      // Lấy config từ size cụ thể hoặc global config
      const wmConfig = size?.watermark || this.options.watermark;
      const wmSrc = (typeof wmConfig === 'object') ? (wmConfig?.src) : wmConfig;
      
      
      if (wmSrc) {
        wmImage.src = wmSrc;
      }
      
      Object.assign(wmImage.style, {
        width: "100%",
        height: "auto",
        objectFit: "contain",
        opacity: wmConfig?.opacity || 1.0
      });

      // Debug log: create watermark image element
      this._log('==>imagify watermark', {
        action: 'create-image',
        index,
        src: wmSrc,
        opacity: wmConfig?.opacity || 1.0
      });

      return wmImage;
    }

    function createWatermarkToggle(box, wmContainer, index) {
      const toggleBtn = document.createElement("button");
      toggleBtn.className = "iMagify-watermark-toggle";
      toggleBtn.innerHTML = "🌊";
      Object.assign(toggleBtn.style, {
        position: "absolute",
        top: "-25px",
        right: "0",
        background: "none",
        border: "none",
        cursor: "pointer",
        fontSize: "16px",
        padding: "2px 5px",
        opacity: "1"
      });

      // Ẩn toggle nếu không có watermark
      const wmConfig = this.options.sizes[index]?.watermark || this.options.watermark;
      const wmSrc = (typeof wmConfig === 'object') ? (wmConfig?.src) : wmConfig;
      
      if (!wmSrc) {
        wmContainer.style.display = "none";
        toggleBtn.style.display = "none";
      }

      // Event handler cho toggle
      toggleBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        const isVisible = wmContainer.style.display === "block";
        wmContainer.style.display = isVisible ? "none" : "block";
        toggleBtn.style.opacity = isVisible ? "0.5" : "1";

        this._log('==>imagify watermark', {
          action: 'toggle',
          index,
          visible: !isVisible
        });
      });

      return toggleBtn;
    }

    function setupCropBoxStyling(box, index) {
      const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
      
      Object.assign(box.style, {
        border: "1px dashed " + boxColor.color,
        position: "absolute",
        cursor: "move",
        backgroundColor: boxColor.color + "33",
        zIndex: "700",
        boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
        transition: "box-shadow 0.3s ease"
      });
      
      // Hover effects
      box.addEventListener("mouseenter", () => {
        box.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
      });
      box.addEventListener("mouseleave", () => {
        box.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
      });
    }

    function addCropBoxEventHandlers(box, index) {
      // Click để select box
      box.addEventListener('mousedown', () => {
        this.switchToTab(index);
      });
    }

    function addCropBoxInteractions(box, index, w, h) {
      const ratioNumber = w / h;
      
      // Drag functionality
      try {
        makeElementDraggable(box, this.editorContainer, this.mainImage, () => { 
          handleCropBoxDragEnd.call(this);
        });
      } catch (e) {
        console.error('[iMagify] Error adding drag to crop box:', e);
      }
      
      // Resize functionality
      try {
        makeElementResizable(box, this.editorContainer, ratioNumber, this.mainImage, () => { 
          handleCropBoxResizeEnd.call(this);
        });
      } catch (e) {
        console.error('[iMagify] Error adding resize to crop box:', e);
      }
    }

    function handleCropBoxDragEnd() {
      this.constrainWatermark();
      updateWatermarkPosition.call(this);
      updateZIndex.call(this);
      
      // Lưu crop state
      setTimeout(() => {
        try { 
          this.saveCropState(this.currentImageIndex); 
        } catch(e) { 
          console.error('[iMagify] Error saving crop state after drag:', e);
        }
      }, 10);
    }

    function handleCropBoxResizeEnd() {
      this.constrainWatermark();
      updateWatermarkPosition.call(this);
      updateZIndex.call(this);
      
      // Lưu crop state
      setTimeout(() => {
        try { 
          this.saveCropState(this.currentImageIndex); 
        } catch(e) { 
          console.error('[iMagify] Error saving crop state after resize:', e);
        }
      }, 10);
    }

    // Watermark Management Functions
    function setupWatermarkInteractions(wmContainer, box, index) {
      const wmImage = wmContainer.querySelector('img');
      
      wmImage.onload = () => {
    const aspect = wmImage.naturalWidth / wmImage.naturalHeight;
    wmContainer.dataset.aspect = String(aspect);
    wmContainer.style.aspectRatio = `${wmImage.naturalWidth} / ${wmImage.naturalHeight}`;
    const imgEl = wmContainer.querySelector('img');
    if (imgEl) {
      Object.assign(imgEl.style, { width: '100%', height: '100%', objectFit: 'contain' });
    }
        this._log('==>imagify watermark', {
          action: 'ready',
          index,
          aspect
        });
        
        // Ngăn watermark drag events lan ra crop box
        wmContainer.addEventListener('mousedown', (e) => {
          e.stopPropagation();
        });

        // Thêm resize functionality
        makeElementResizable(
          wmContainer,
          box,
          aspect,
          box,
          () => {
            // Lưu state sau khi resize watermark để không bị auto-size đè
            try { this.saveCropState(this.currentImageIndex); } catch(_) {}
            this.constrainWatermark();
          }
        );
        
        // Thêm drag functionality
        makeElementDraggable(
          wmContainer,
          box,
          box,
          () => {
            // Lưu state sau khi kéo watermark
            try { this.saveCropState(this.currentImageIndex); } catch(_) {}
            this.constrainWatermark();
          }
        );

        // Điều chỉnh kích thước watermark ban đầu (chỉ khi chưa có state)
        const hasStateForBox = this.cropStates[this.currentImageIndex] && this.cropStates[this.currentImageIndex][index]?.wm;
        if (!hasStateForBox) {
          this.adjustWatermarkSize(box);
        }
      };
    }

    function updateWatermarkPosition() {
      this.cropBoxes.forEach(box => {
        const wm = box.watermarkContainer;
        if (wm && wm.style.display !== "none") {
          const boxRect = box.getBoundingClientRect();
          const wmRect = wm.getBoundingClientRect();

          // Tính vị trí hiện tại theo toạ độ tuyệt đối rồi quy về relative của crop box
          let wmLeft = wmRect.left - boxRect.left;
          let wmTop = wmRect.top - boxRect.top;

          // Clamp trong bounds
          if (wmLeft < 0) wmLeft = 0;
          if (wmTop < 0) wmTop = 0;
          if (wmLeft + wmRect.width > boxRect.width) wmLeft = boxRect.width - wmRect.width;
          if (wmTop + wmRect.height > boxRect.height) wmTop = boxRect.height - wmRect.height;

          // Áp dụng lại bằng left/top tuyệt đối, clear right/bottom để tránh xung đột
          wm.style.right = 'auto';
          wm.style.bottom = 'auto';
          wm.style.left = wmLeft + 'px';
          wm.style.top = wmTop + 'px';

          this._log('==>imagify watermark', {
            action: 'update-position',
            left: wmLeft,
            top: wmTop,
            boxSize: { width: boxRect.width, height: boxRect.height },
            wmSize: { width: wmRect.width, height: wmRect.height }
          });
        }
      });
    }

    function updateZIndex() {
      this.cropBoxes.forEach(box => {
        const maxZIndex = Math.max(...this.cropBoxes.map(b => parseInt(b.style.zIndex)));
        box.style.zIndex = (maxZIndex + 1).toString();
      });
    }

    // Crop State Management Functions
    function saveCropState(imageIndex) {
      if (!this.mainImage) return;
      
      const imageRect = this.mainImage.getBoundingClientRect();
      const states = this.cropBoxes.map(box => {
        const rect = box.getBoundingClientRect();
        const leftPct = (rect.left - imageRect.left) / imageRect.width;
        const topPct = (rect.top - imageRect.top) / imageRect.height;
        const widthPct = rect.width / imageRect.width;
        const heightPct = rect.height / imageRect.height;
        
        let wm = null;
        if (box.watermarkContainer) {
          wm = saveWatermarkState.call(this, box, rect);
        }
        
        return { leftPct, topPct, widthPct, heightPct, wm };
      });
      
      this.cropStates[imageIndex] = states;
    }

    function saveWatermarkState(box, rect) {
      const wmDisplay = box.watermarkContainer.style.display;
      const wmVisibility = box.watermarkContainer.style.visibility;
      const wmOpacity = box.watermarkContainer.style.opacity;
      
      
      if (wmDisplay !== 'none') {
        const wmRect = box.watermarkContainer.getBoundingClientRect();
        const wmLeft = wmRect.left - rect.left;
        const wmTop = wmRect.top - rect.top;

        const result = {
          leftPct: wmLeft / rect.width,
          topPct: wmTop / rect.height,
          widthPct: wmRect.width / rect.width,
          heightPct: wmRect.height / rect.height,
          visible: true
        };

        this._log('==>imagify watermark', {
          action: 'save-state',
          left: wmLeft,
          top: wmTop,
          width: wmRect.width,
          height: wmRect.height
        });

        return result;
      } else {
        return { visible: false };
      }
    }

    function applyCropState(imageIndex) {
      const states = this.cropStates[imageIndex];
      if (!states || !this.mainImage) { 
        this.updateCropBoxes(); 
        return; 
      }
      
      const imageRect = this.mainImage.getBoundingClientRect();
      const containerRect = this.editorContainer.getBoundingClientRect();
      const imageOffsetLeft = imageRect.left - containerRect.left;
      const imageOffsetTop = imageRect.top - containerRect.top;
      
      this.cropBoxes.forEach((box, i) => {
        const st = states[i];
        if (!st) return;
        
        applySingleCropState.call(this, box, st, imageRect, imageOffsetLeft, imageOffsetTop, i);
      });
      
      this.constrainWatermark();
    }

    function applySingleCropState(box, state, imageRect, imageOffsetLeft, imageOffsetTop, index) {
      const boxWidth = state.widthPct * imageRect.width;
      const boxHeight = state.heightPct * imageRect.height;
      const left = imageOffsetLeft + state.leftPct * imageRect.width;
      const top = imageOffsetTop + state.topPct * imageRect.height;
      
      box.style.width = boxWidth + 'px';
      box.style.height = boxHeight + 'px';
      box.style.left = left + 'px';
      box.style.top = top + 'px';
      
      // Áp dụng watermark state
      if (box.watermarkContainer && state.wm) {
        applyWatermarkState.call(this, box, state.wm, boxWidth, boxHeight);
        this._log('==>imagify watermark', {
          action: 'apply-state',
          index,
          left: state.wm.leftPct * boxWidth,
          top: state.wm.topPct * boxHeight,
          width: state.wm.widthPct * boxWidth,
          height: state.wm.heightPct * boxHeight,
          visible: state.wm.visible !== false
        });
      }
    }

    function applyWatermarkState(box, wmState, boxWidth, boxHeight) {
      const wmLeft = wmState.leftPct * boxWidth;
      const wmTop = wmState.topPct * boxHeight;
      const wmWidth = (wmState.widthPct ?? 0.2) * boxWidth;
      
      box.watermarkContainer.style.left = wmLeft + 'px';
      box.watermarkContainer.style.top = wmTop + 'px';
      box.watermarkContainer.style.width = wmWidth + 'px';
      box.watermarkContainer.style.height = '';
          
          if (wmState.visible === false) {
            box.watermarkContainer.style.display = 'none';
          } else {
            box.watermarkContainer.style.display = 'block';
          }
    }

    // Sidebar Management Functions
    function buildSidebar() {
      if (!this.sidebar) return;
      
      this.sidebar.innerHTML = '';
      
      addSidebarTitle.call(this);
      addSidebarTabs.call(this);
      addTabContentArea.call(this);
      
      // Khởi tạo với tab đầu tiên được chọn
      this.switchToTab(this.selectedCropIndex);
    }

    function addSidebarTitle() {
      const title = document.createElement('div');
      title.textContent = 'Layer Sizes';
      Object.assign(title.style, { 
        fontWeight: 'bold', 
        fontSize: '12px', 
        marginBottom: '8px', 
        color: '#93c5fd', 
        letterSpacing: '0.3px',
        textAlign: 'center',
        paddingBottom: '8px',
        borderBottom: '1px solid #374151'
      });
      this.sidebar.appendChild(title);
    }

    function addSidebarTabs() {
      const tabContainer = document.createElement('div');
      Object.assign(tabContainer.style, {
        display: 'flex',
        flexDirection: 'column',
        gap: '4px',
        marginBottom: '12px'
      });

      this.cropBoxes.forEach((box, index) => {
        const tab = createSidebarTab.call(this, box, index);
        tabContainer.appendChild(tab);
      });

      this.sidebar.appendChild(tabContainer);
    }

    function createSidebarTab(box, index) {
      const tab = document.createElement('div');
      tab.className = 'iMagify-sidebar-tab';
      tab.dataset.tabIndex = index;
      const ratio = box.dataset.ratio || '';
      const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
      
      setupTabStyling.call(this, tab, index, boxColor);
      addTabContent.call(this, tab, index, ratio);
      addTabEventHandlers.call(this, tab, index);
      
      return tab;
    }

    function setupTabStyling(tab, index, boxColor) {
      Object.assign(tab.style, {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
        padding: '8px 10px',
        borderRadius: '6px',
        cursor: 'pointer',
        background: index === this.selectedCropIndex ? boxColor.bgColor : '#374151',
        color: '#e5e7eb',
        border: index === this.selectedCropIndex ? `2px solid ${boxColor.color}` : '1px solid #4b5563',
        transition: 'all 0.2s ease',
        fontSize: '12px',
        fontWeight: index === this.selectedCropIndex ? 'bold' : 'normal'
      });
    }

    function addTabContent(tab, index, ratio) {
      const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
      
      // Color indicator
      const colorDot = document.createElement('div');
      Object.assign(colorDot.style, {
        width: '12px',
        height: '12px',
        borderRadius: '50%',
        background: boxColor.color,
        border: '1px solid rgba(0,0,0,0.2)',
        flexShrink: 0
      });

      // Tab content
      const tabContent = document.createElement('div');
      Object.assign(tabContent.style, { 
        display: 'flex', 
        flexDirection: 'column',
        flex: 1
      });
      
      const tabName = document.createElement('div');
      tabName.textContent = `Layer ${index + 1}`;
      Object.assign(tabName.style, { 
        fontSize: '12px', 
        lineHeight: '14px', 
        color: '#e5e7eb',
        fontWeight: 'bold'
      });
      
      const tabSize = document.createElement('div');
      tabSize.textContent = ratio;
      Object.assign(tabSize.style, { 
        fontSize: '10px', 
        lineHeight: '12px', 
        color: '#cbd5e1',
        opacity: 0.8
      });
      
      tabContent.appendChild(tabName);
      tabContent.appendChild(tabSize);

      tab.appendChild(colorDot);
      tab.appendChild(tabContent);
    }

    function addTabEventHandlers(tab, index) {
      // Tab click handler
      tab.addEventListener('click', () => {
        this.switchToTab(index);
      });

      // Hover effects
      tab.addEventListener('mouseenter', () => {
        if (index !== this.selectedCropIndex) {
          tab.style.background = '#4b5563';
          tab.style.transform = 'translateX(2px)';
        }
      });
      
      tab.addEventListener('mouseleave', () => {
        if (index !== this.selectedCropIndex) {
          tab.style.background = '#374151';
          tab.style.transform = 'none';
        }
      });
    }

    function addTabContentArea() {
      const tabContentArea = document.createElement('div');
      tabContentArea.className = 'iMagify-tab-content';
      Object.assign(tabContentArea.style, {
        flex: 1,
        padding: '8px',
        background: '#1f2937',
        borderRadius: '6px',
        border: '1px solid #374151'
      });
      this.sidebar.appendChild(tabContentArea);
    }

    function switchToTab(index) {
      if (index < 0 || index >= this.cropBoxes.length) return;
      
      this.selectedCropIndex = index;
      
      // Áp dụng saved crop state trước khi chuyển tab
      if (this.cropStates[this.currentImageIndex]) {
        this.applyCropState(this.currentImageIndex);
      }
      
      updateCropBoxVisibility.call(this, index);
      updateTabStyles.call(this, index);
      updateTabContent.call(this, index);
      scrollToSelectedCropBox.call(this, index);
    }

    function updateCropBoxVisibility(index) {
      this.cropBoxes.forEach((box, i) => {
        if (i === index) {
          box.style.visibility = 'visible';
          box.style.opacity = '1';
          box.style.zIndex = '800';
        } else {
          box.style.visibility = 'hidden';
          box.style.opacity = '0';
          box.style.zIndex = '700';
        }
      });
    }

    function updateTabStyles(index) {
      const tabs = this.sidebar.querySelectorAll('.iMagify-sidebar-tab');
      tabs.forEach((tab, i) => {
        const boxColor = this.cropBoxColors[i % this.cropBoxColors.length];
        if (i === index) {
          tab.style.background = boxColor.bgColor;
          tab.style.border = `2px solid ${boxColor.color}`;
          tab.style.fontWeight = 'bold';
        } else {
          tab.style.background = '#374151';
          tab.style.border = '1px solid #4b5563';
          tab.style.fontWeight = 'normal';
        }
      });
    }

    function updateTabContent(index) {
      const tabContentArea = this.sidebar.querySelector('.iMagify-tab-content');
      if (tabContentArea) {
        const ratio = this.cropBoxes[index].dataset.ratio || '';
        const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
        
        tabContentArea.innerHTML = `
          <div style="text-align: center; margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: bold; color: ${boxColor.color}; margin-bottom: 4px;">
              Layer ${index + 1}
            </div>
            <div style="font-size: 12px; color: #cbd5e1;">
              ${ratio}
            </div>
          </div>
          <div style="font-size: 11px; color: #9ca3af; text-align: center;">
            Chỉnh sửa crop box này
          </div>
        `;
      }
    }

    function scrollToSelectedCropBox(index) {
      try { 
        this.cropBoxes[index].scrollIntoView({ 
          block: 'center', 
          inline: 'center', 
          behavior: 'smooth' 
        }); 
      } catch(_) {}
    }

    // Button Management Functions
    function createSaveButton() {
      // Xóa nút cũ nếu có
      if (this.saveBtn) {
        this.saveBtn.remove();
      }

      const buttonContainer = createButtonContainer.call(this);
      this.saveBtn = createSaveButtonElement.call(this);
      addSaveButtonEvents.call(this);
      
      // Thêm các nút khác cho single file
      if (this.options.images.length === 1) {
        addSingleFileButtons.call(this, buttonContainer);
      }

      buttonContainer.appendChild(this.saveBtn);
      this.modal.appendChild(buttonContainer);
    }

    function createButtonContainer() {
      const buttonContainer = document.createElement("div");
      Object.assign(buttonContainer.style, {
        position: "fixed",
        bottom: "16px",
        right: "16px",
        display: "flex",
        gap: "8px",
        zIndex: "9999"
      });
      return buttonContainer;
    }

    function createSaveButtonElement() {
      const saveBtn = document.createElement("button");
      saveBtn.className = "iMagify-saveBtn btn btn-primary";
      saveBtn.innerText = "Xử lý";
      Object.assign(saveBtn.style, {
        padding: "8px 14px",
        fontSize: "13px",
        color: "#fff",
        border: "1px solid #1f4ed8",
        borderRadius: "8px",
        cursor: "pointer",
        background: "linear-gradient(180deg, #3b82f6 0%, #2563eb 100%)",
        boxShadow: "0 2px 6px rgba(37,99,235,0.35)",
        transition: "all 0.3s ease",
        display: "flex",
        alignItems: "center",
        gap: "6px"
      });

      addSaveButtonHoverEffects.call(this, saveBtn);
      return saveBtn;
    }

    function addSaveButtonHoverEffects(saveBtn) {
      saveBtn.addEventListener("mouseenter", () => {
        saveBtn.style.filter = "brightness(1.05)";
        saveBtn.style.boxShadow = "0 4px 10px rgba(37,99,235,0.45)";
      });
      
      saveBtn.addEventListener("mouseleave", () => {
        saveBtn.style.filter = "none";
        saveBtn.style.boxShadow = "0 2px 6px rgba(37,99,235,0.35)";
      });
    }

    function addSaveButtonEvents() {
      this.saveBtn.addEventListener("click", () => {
        if (this.isProcessing) {
          return;
        }
        
        this.isProcessing = true;
        
        setSaveButtonProcessingState.call(this);
        this.processCurrentUpload();
      });
    }

    function setSaveButtonProcessingState() {
      this.saveBtn.disabled = true;
      this.saveBtn.style.opacity = "0.7";
      this.saveBtn.style.cursor = "not-allowed";
      this.saveBtn.innerHTML = '';
      
      const spinner = createSpinner.call(this);
      const text = createProcessingText.call(this);
      
      this.saveBtn.appendChild(spinner);
      this.saveBtn.appendChild(text);
    }

    function createSpinner() {
      const spinner = document.createElement("div");
      spinner.className = "iMagify-spinner";
      Object.assign(spinner.style, {
        width: "16px",
        height: "16px",
        border: "2px solid #ffffff",
        borderTop: "2px solid transparent",
        borderRadius: "50%",
        animation: "iMagify-spin 1s linear infinite",
        display: "inline-block",
        marginRight: "6px",
        verticalAlign: "middle"
      });
      return spinner;
    }

    function createProcessingText() {
      const text = document.createElement("span");
      text.textContent = "Đang xử lý...";
      text.style.verticalAlign = "middle";
      text.style.fontSize = "12px";
      return text;
    }

    function addSingleFileButtons(buttonContainer) {
      const clearBtn = createClearButton.call(this);
      const replaceBtn = createReplaceButton.call(this);
      
      buttonContainer.appendChild(clearBtn);
      buttonContainer.appendChild(replaceBtn);
    }

    function createClearButton() {
      const clearBtn = document.createElement("button");
      clearBtn.className = "iMagify-clearBtn btn btn-danger";
      clearBtn.innerText = "Xóa";
      Object.assign(clearBtn.style, {
        padding: "8px 12px",
        fontSize: "12px",
        color: "#fff",
        border: "1px solid #991b1b",
        borderRadius: "8px",
        cursor: "pointer",
        boxShadow: "0 2px 6px rgba(153,27,27,0.35)",
        transition: "all 0.3s ease",
        background: "linear-gradient(180deg, #dc2626 0%, #b91c1c 100%)"
      });

      addClearButtonHoverEffects.call(this, clearBtn);
      addClearButtonEvents.call(this, clearBtn);
      
      return clearBtn;
    }

    function addClearButtonHoverEffects(clearBtn) {
      clearBtn.addEventListener("mouseenter", () => {
        clearBtn.style.filter = "brightness(1.05)";
        clearBtn.style.boxShadow = "0 4px 10px rgba(153,27,27,0.45)";
      });
      
      clearBtn.addEventListener("mouseleave", () => {
        clearBtn.style.filter = "none";
        clearBtn.style.boxShadow = "0 2px 6px rgba(153,27,27,0.35)";
      });
    }

    function addClearButtonEvents(clearBtn) {
      clearBtn.addEventListener("click", () => {
        if (confirm("Bạn có chắc chắn muốn xóa ảnh này?")) {
          if (typeof this.completeCallback === 'function') {
            const currentImage = this.options.images[0];
            const fileData = {
              action: 'clear',
              id: currentImage.id,
              name: currentImage.name,
              path: currentImage.path
            };
            this.completeCallback([fileData]);
          }
          document.body.removeChild(this.modal);
        }
      });
    }

    function createReplaceButton() {
      const replaceBtn = document.createElement("button");
      replaceBtn.className = "iMagify-replaceBtn btn btn-warning";
      replaceBtn.innerText = "Thay thế";
      Object.assign(replaceBtn.style, {
        padding: "8px 12px",
        fontSize: "12px",
        color: "#111827",
        border: "1px solid #d97706",
        borderRadius: "8px",
        cursor: "pointer",
        boxShadow: "0 2px 6px rgba(217,119,6,0.35)",
        transition: "all 0.3s ease",
        background: "linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%)"
      });

      addReplaceButtonHoverEffects.call(this, replaceBtn);
      addReplaceButtonEvents.call(this, replaceBtn);
      
      return replaceBtn;
    }

    function addReplaceButtonHoverEffects(replaceBtn) {
      replaceBtn.addEventListener("mouseenter", () => {
        replaceBtn.style.filter = "brightness(1.05)";
        replaceBtn.style.boxShadow = "0 4px 10px rgba(217,119,6,0.45)";
      });
      
      replaceBtn.addEventListener("mouseleave", () => {
        replaceBtn.style.filter = "none";
        replaceBtn.style.boxShadow = "0 2px 6px rgba(217,119,6,0.35)";
      });
    }

    function addReplaceButtonEvents(replaceBtn) {
      replaceBtn.addEventListener("click", () => {
        const fileInput = document.createElement("input");
        fileInput.type = "file";
        fileInput.accept = "image/*";
        fileInput.style.display = "none";
        document.body.appendChild(fileInput);

        fileInput.addEventListener("change", (e) => {
          handleFileReplace.call(this, e, fileInput);
        });

        fileInput.click();
      });
    }

    function handleFileReplace(e, fileInput) {
      if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        
        const reader = new FileReader();
        reader.onload = (e) => {
          updateImageWithNewFile.call(this, e.target.result, file);
        };
        reader.readAsDataURL(file);
      }
      document.body.removeChild(fileInput);
    }

    function updateImageWithNewFile(dataUrl, file) {
      const currentImage = this.options.images[0];
      currentImage.src = dataUrl;
      currentImage.file = file;
      currentImage.name = file.name;
      
      this.mainImage.src = dataUrl;
      
      this.mainImage.onload = () => {
        this.updateCropBoxes();
        this.createSaveButton();
      };
    }

    // Image Processing Functions
    async function processCurrentUpload() {
      let thumbnails = document.querySelectorAll(".iMagify-thumbnail");
      
      try {
        let localData = await this.buildUploadResult();

        hideCurrentThumbnail.call(this, thumbnails);
        saveProcessedResult.call(this, localData);
        handleUploadCallback.call(this, localData);
        
        if (hasMoreImages.call(this)) {
          moveToNextImage.call(this);
        } else {
          handleAllImagesProcessed.call(this);
        }
        
      } catch (err) {
        handleProcessingError.call(this, err, thumbnails);
      }
    }

    function hideCurrentThumbnail(thumbnails) {
      if (thumbnails[this.currentImageIndex]) {
        thumbnails[this.currentImageIndex].style.display = "none";
      }
    }

    function saveProcessedResult(localData) {
      const processedIndex = this.currentImageIndex;
      this.results[processedIndex] = localData;
      this.uploaded[processedIndex] = true;
    }

    function handleUploadCallback(localData) {
      if (typeof this.uploadCallback === 'function') {
        this.uploadCallback(localData);
      }
    }

    function hasMoreImages() {
      return this.currentImageIndex < this.options.images.length - 1;
    }

    function moveToNextImage() {
      this.currentImageIndex++;
      this.mainImage.src = this.options.images[this.currentImageIndex].src ? 
        this.options.images[this.currentImageIndex].src : 
        this.options.images[this.currentImageIndex];
      
      highlightThumbnail.call(this, this.currentImageIndex);
      this.createSaveButton();
      this.isProcessing = false;
      
      if (this.saveBtn) {
        this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Đang sẵn sàng xử lý ảnh tiếp theo...`;
      }
    }

    function handleAllImagesProcessed() {
      if (this.uploaded.every(Boolean)) {
        if (typeof this.completeCallback === 'function') {
          this.completeCallback(this.results);
        }
        this.isProcessing = false;
        
        if (this.saveBtn) {
          this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Hoàn tất xử lý.`;
        }
        
        setTimeout(() => { 
          document.body.removeChild(this.modal); 
        }, 800);
      }
    }

    function handleProcessingError(err, thumbnails) {
      console.error('[iMagify] Lỗi local process', err);
      this.isProcessing = false;
      
      const failedIndex = this.currentImageIndex;
      if (thumbnails[failedIndex]) {
        thumbnails[failedIndex].style.display = "block";
      }
      
      setSaveButtonErrorState.call(this);
    }

    function setSaveButtonErrorState() {
      if (this.saveBtn) {
        this.saveBtn.disabled = false;
        this.saveBtn.style.opacity = "1";
        this.saveBtn.style.cursor = "pointer";
        this.saveBtn.innerHTML = `
          <div style="display: flex; align-items: center; gap: 8px;">
            <span style="color: #dc3545;">⚠️</span>
            <span>Xử lý thất bại - Click để thử lại</span>
          </div>
        `;
        
        this.saveBtn.onclick = () => {
          this.saveBtn.innerHTML = `
            <div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>
            Đang xử lý ảnh...
          `;
          this.isProcessing = true;
          this.processCurrentUpload();
        };
      }
    }

    function highlightThumbnail(index) {
      document.querySelectorAll(".iMagify-thumbnail").forEach((el, i) => {
        el.style.border = (i === index) ? "2px solid #2563eb" : "none";
      });
    }

    // Utility Functions
    function makeElementDraggable(el, container, boundEl, onDragEnd) {
      let pos = { top: 0, left: 0, x: 0, y: 0 };
      
      const mouseDownHandler = function (e) {
        // Ngăn chặn sự kiện lan ra các phần tử khác
        e.stopPropagation();
        pos = { left: el.offsetLeft, top: el.offsetTop, x: e.clientX, y: e.clientY };
        document.addEventListener("mousemove", mouseMoveHandler);
        document.addEventListener("mouseup", mouseUpHandler);
        e.preventDefault();
      };
      
      const mouseMoveHandler = function (e) {
        const dx = e.clientX - pos.x;
        const dy = e.clientY - pos.y;
        let newLeft = pos.left + dx;
        let newTop = pos.top + dy;
        
        // Constrain to bounds
        const containerRect = container.getBoundingClientRect();
        const bound = boundEl ? boundEl.getBoundingClientRect() : containerRect;
        const offsetLeft = bound.left - containerRect.left;
        const offsetTop = bound.top - containerRect.top;
        const elRect = el.getBoundingClientRect();
        const maxLeft = bound.right - containerRect.left - elRect.width;
        const maxTop = bound.bottom - containerRect.top - elRect.height;
        
        if (newLeft < offsetLeft) newLeft = offsetLeft;
        if (newTop < offsetTop) newTop = offsetTop;
        if (newLeft > maxLeft) newLeft = maxLeft;
        if (newTop > maxTop) newTop = maxTop;
        
        el.style.left = newLeft + "px";
        el.style.top = newTop + "px";
      };
      
      const mouseUpHandler = function () {
        document.removeEventListener("mousemove", mouseMoveHandler);
        document.removeEventListener("mouseup", mouseUpHandler);
        if (onDragEnd) onDragEnd();
      };
      
      el.addEventListener("mousedown", mouseDownHandler);
    }

    function makeElementResizable(el, container, aspectRatio, boundEl, onResizeEnd) {
      const resizer = createResizerElement.call(this);
      el.appendChild(resizer);
      
      let originalWidth = 0, originalHeight = 0, originalMouseX = 0, originalMouseY = 0;
      
      resizer.addEventListener("mousedown", (e) => {
        handleResizeStart.call(this, e, resizer, el, originalWidth, originalHeight, originalMouseX, originalMouseY);
      });

      setupResizeHandlers.call(this, el, container, aspectRatio, boundEl, onResizeEnd, resizer);
    }

    function createResizerElement() {
      const resizer = document.createElement("div");
      Object.assign(resizer.style, {
        width: "16px",
        height: "16px",
        background: "rgba(255, 255, 255, 0.8)",
        border: "1px solid rgba(0, 0, 0, 0.3)",
        position: "absolute",
        right: "0",
        bottom: "0",
        cursor: "se-resize",
        zIndex: "9999",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        borderRadius: "2px",
        boxShadow: "0 1px 2px rgba(0,0,0,0.1)"
      });

      resizer.innerHTML = getResizerIcon();
      return resizer;
    }

    function getResizerIcon() {
      return `
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M13.8284 13.8284L20.8995 20.8995M20.8995 20.8995L20.7816 15.1248M20.8995 20.8995L15.1248 20.7816" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.89948 13.8284L2.82841 20.8995M2.82841 20.8995L8.60312 20.7816M2.82841 20.8995L2.94626 15.1248" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M13.8284 9.8995L20.8995 2.82843M20.8995 2.82843L15.1248 2.94629M20.8995 2.82843L20.7816 8.60314" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.89947 9.89951L2.8284 2.82844M2.8284 2.82844L2.94626 8.60315M2.8284 2.82844L8.60311 2.94629" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      `;
    }

    function handleResizeStart(e, resizer, el, originalWidth, originalHeight, originalMouseX, originalMouseY) {
      e.stopPropagation();
      originalWidth = parseFloat(getComputedStyle(el, null).getPropertyValue("width").replace("px", ""));
      originalHeight = parseFloat(getComputedStyle(el, null).getPropertyValue("height").replace("px", ""));
      originalMouseX = e.clientX;
      originalMouseY = e.clientY;
      document.addEventListener("mousemove", mouseMoveResize);
      document.addEventListener("mouseup", mouseUpResize);
      e.preventDefault();
    }

    function setupResizeHandlers(el, container, aspectRatio, boundEl, onResizeEnd, resizer) {
      function mouseMoveResize(e) {
        let dx = e.clientX - originalMouseX;
        let dy = e.clientY - originalMouseY;
        
        // Maintain aspect ratio if specified
        if (aspectRatio) {
          if (Math.abs(dx) > Math.abs(dy)) {
            dy = dx / aspectRatio;
          } else {
            dx = dy * aspectRatio;
          }
        }
        
        let newWidth = originalWidth + dx;
        let newHeight = originalHeight + dy;
        
        // Apply constraints
        const constraints = calculateResizeConstraints.call(this, el, container, boundEl, aspectRatio);
        newWidth = Math.max(constraints.minWidth, Math.min(newWidth, constraints.maxWidth));
        newHeight = Math.max(constraints.minHeight, Math.min(newHeight, constraints.maxHeight));
        
        el.style.width = newWidth + "px";
        el.style.height = newHeight + "px";

        // Adjust watermark size if this is a crop box
        if (el.classList.contains('iMagify-cropBox')) {
          const iMagifyInstance = window.iMagifyInstance;
          if (iMagifyInstance) {
            iMagifyInstance.adjustWatermarkSize(el);
          }
        }
      }

      function mouseUpResize() {
        document.removeEventListener("mousemove", mouseMoveResize);
        document.removeEventListener("mouseup", mouseUpResize);
        if (onResizeEnd) onResizeEnd();
      }
    }

    function calculateResizeConstraints(el, container, boundEl, aspectRatio) {
      const minSize = 30;
      const containerRect = container.getBoundingClientRect();
      let boundRect = boundEl ? boundEl.getBoundingClientRect() : containerRect;
      const elRect = el.getBoundingClientRect();
      const elOffsetLeft = elRect.left - containerRect.left;
      const elOffsetTop = elRect.top - containerRect.top;
      
      const maxWidth = boundRect.right - containerRect.left - elOffsetLeft;
      const maxHeight = boundRect.bottom - containerRect.top - elOffsetTop;
      
      let minWidth = minSize;
      let minHeight = minSize;
      
      if (aspectRatio) {
        minHeight = minSize / aspectRatio;
        minWidth = minSize * aspectRatio;
      }
      
      return {
        minWidth: Math.min(minWidth, maxWidth),
        minHeight: Math.min(minHeight, maxHeight),
        maxWidth,
        maxHeight
      };
    }

    function addUtilityStyles() {
      const style = document.createElement('style');
      style.textContent = getUtilityCSS();
      document.head.appendChild(style);
    }

    function getUtilityCSS() {
      return `
        .draggable-item {
          transition: transform 0.2s, box-shadow 0.2s;
          touch-action: none;
          user-select: none;
        }
        .draggable-item.dragging {
          opacity: 0.5;
          background: #f0f0f0;
          box-shadow: 0 2px 8px rgba(0,0,0,0.1);
          z-index: 1000;
        }
        .draggable-item:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(0,0,0,0.15);
          z-index: 10;
        }
        @media (hover: none) {
          .draggable-item:hover {
            transform: none;
            box-shadow: none;
          }
          .draggable-item.dragging {
            transform: scale(1.05);
          }
        }

        /* Animation cho icon loading */
        @keyframes iMagify-spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }

        .iMagify-spinner {
          display: inline-block;
          width: 20px;
          height: 20px;
          border: 3px solid #ffffff;
          border-top: 3px solid transparent;
          border-radius: 50%;
          animation: iMagify-spin 1s linear infinite;
        }
      `;
    }

    function iMagify(options) {
      // Initialize core properties
      initializeCore.call(this, options);
      
      window.iMagifyInstance = this;
      this.init();
    }
  
    // Event handlers
    iMagify.prototype.onComplete = function (callback) {
      this.completeCallback = callback;
    };
    
    iMagify.prototype.onUpload = function (callback) {
      this.uploadCallback = callback;
    };
  
    iMagify.prototype.init = function () {
      // Setup global error handlers
      setupGlobalErrorHandlers.call(this);
      
      // Add utility styles
      addUtilityStyles.call(this);
      
      // Create UI components
      createModalContainer.call(this);
      createCloseButton.call(this);
      createThumbnailContainer.call(this);
      createEditorContainer.call(this);
      createSidebar.call(this);
      createMainImage.call(this);
  
      // Simple logging helper
      this._log = (label, payload) => {
        this.options.debugMode = true;
        if (this.options.debugMode) {
          try {
            const data = (payload === undefined)
              ? { note: '(no payload)' }
              : payload;
            console.log(label, data);
          } catch(_) {}
        }
      };

      // Create thumbnails and load main image
      createThumbnails.call(this);

      // When main image loads, update crop boxes
      this.mainImage.onload = () => {
        this.ensureImageDisplay();
        
        const imgRect = this.mainImage.getBoundingClientRect();
        if (imgRect.width === 0 || imgRect.height === 0) {
          setTimeout(() => {
            this.updateCropBoxes();
            this.createSaveButton();
            this.buildSidebar();
          }, 100);
        } else {
          this.updateCropBoxes();
          this.createSaveButton();
          this.buildSidebar();
        }
      };

      // Ensure image displays correctly
      this.ensureImageDisplay = () => {
        try {
          Object.assign(this.mainImage.style, {
            maxWidth: "90%",
            maxHeight: "90%",
            position: "relative",
            filter: "none",
            opacity: "1",
            mixBlendMode: "normal",
            display: "block",
            visibility: "visible"
          });
        } catch (error) {
          // Silent fail
        }
      };
   
      // Create crop boxes based on sizes
      createCropBoxes.call(this);    
  
  
      // Build sidebar after crop boxes are created
      if (this.options.sizes && this.options.sizes.length > 0) {
          setTimeout(() => {
            this.buildSidebar();
        }, 200);
      }
      
        this._log('init() completed successfully');
    };
  
  
    // Update crop boxes and set default watermark if not set
    iMagify.prototype.updateCropBoxes = function () {
      if (!this.mainImage || !this.editorContainer) return;

      const imgRect = this.mainImage.getBoundingClientRect();
      if (imgRect.width === 0 || imgRect.height === 0) {
        setTimeout(() => {
          this.updateCropBoxes();
          this.createSaveButton();
        }, 100);
        return;
      }

      // Check if we have saved crop states for current image
      const hasSavedState = this.cropStates[this.currentImageIndex] && 
                           this.cropStates[this.currentImageIndex].length > 0;

      // If we have saved state, apply it instead of centering
      if (hasSavedState) {
        this.applyCropState(this.currentImageIndex);
        return;
      }

      const containerRect = this.editorContainer.getBoundingClientRect();
      const imageOffsetLeft = imgRect.left - containerRect.left;
      const imageOffsetTop = imgRect.top - containerRect.top;

      this.cropBoxes.forEach(box => {
        if (!box || !box.dataset.ratio) return;

        const ratioParts = box.dataset.ratio.split("x");
        if (ratioParts.length !== 2) return;

        const ratio = parseFloat(ratioParts[0]) / parseFloat(ratioParts[1]);
        let boxWidth, boxHeight;

        if (imgRect.width / ratio <= imgRect.height) {
          boxWidth = imgRect.width;
          boxHeight = imgRect.width / ratio;
        } else {
          boxHeight = imgRect.height;
          boxWidth = imgRect.height * ratio;
        }

        box.style.width = boxWidth + "px";
        box.style.height = boxHeight + "px";
        box.style.left = imageOffsetLeft + (imgRect.width - boxWidth) / 2 + "px";
        box.style.top = imageOffsetTop + (imgRect.height - boxHeight) / 2 + "px";

        // Adjust watermark size after updating crop box
        // Chỉ auto-size nếu chưa có state đã lưu cho box này
        const idx = this.cropBoxes.indexOf(box);
        const hasStateForBox = this.cropStates[this.currentImageIndex] && this.cropStates[this.currentImageIndex][idx]?.wm;
        if (!hasStateForBox) {
          this.adjustWatermarkSize(box);
        }
      });

      // Initialize watermark for each crop box using sizes config
      if (!this._watermarkInitialized) {
        this.cropBoxes.forEach((box, index) => {
          if (box.watermarkContainer) {
            const sizeConfig = this.options.sizes[index];
            const wmConfig = sizeConfig?.watermark || this.options.watermark;
            
            if (wmConfig) {
              const pad = wmConfig.padding || 10;
              const position = wmConfig.position || "bottom-right";
              
              // Đảm bảo watermark container có kích thước trước khi tính position
              setTimeout(() => {
                const boxRect = box.getBoundingClientRect();
                const wmWidth = box.watermarkContainer.offsetWidth || 100;
                const wmHeight = box.watermarkContainer.offsetHeight || 50;
                
                // Chỉ set vị trí mặc định nếu chưa có state đã lưu
                const hasState = this.cropStates[this.currentImageIndex] && this.cropStates[this.currentImageIndex][index]?.wm;
                if (!hasState) {
                  // Set watermark position based on config using CSS positioning
                  box.watermarkContainer.style.left = "auto";
                  box.watermarkContainer.style.top = "auto";
                  box.watermarkContainer.style.right = "auto";
                  box.watermarkContainer.style.bottom = "auto";
                  
                  switch (position) {
                    case "top-right":
                      box.watermarkContainer.style.right = pad + "px";
                      box.watermarkContainer.style.top = pad + "px";
                      break;
                    case "bottom-right":
                      box.watermarkContainer.style.right = pad + "px";
                      box.watermarkContainer.style.bottom = pad + "px";
                      break;
                    case "bottom-left":
                      box.watermarkContainer.style.left = pad + "px";
                      box.watermarkContainer.style.bottom = pad + "px";
                      break;
                    case "center":
                      box.watermarkContainer.style.left = "50%";
                      box.watermarkContainer.style.top = "50%";
                      box.watermarkContainer.style.transform = "translate(-50%, -50%)";
                      break;
                    default:
                      box.watermarkContainer.style.left = pad + "px";
                      box.watermarkContainer.style.top = pad + "px";
                      break;
                  }
                }
                
                // Apply opacity to watermark image
                const wmImage = box.watermarkContainer.querySelector('img');
                if (wmImage && wmConfig.opacity !== undefined) {
                  wmImage.style.opacity = wmConfig.opacity;
                }
                // Debug log: apply watermark config
                this._log('imagify watermark', {
                  action: 'apply-config',
                  index,
                  position,
                  padding: pad,
                  opacity: wmConfig.opacity,
                  boxSize: { width: boxRect.width, height: boxRect.height },
                  wmSize: { width: wmWidth, height: wmHeight }
                });
              }, 100);
            }
          }
        });
        this._watermarkInitialized = true;
      }
    };

    // Save crop state for current image (relative percentages to main image)
    iMagify.prototype.saveCropState = function(imageIndex) {
      // Sử dụng function saveCropState() đã được refactor
      saveCropState.call(this, imageIndex);
    };

    // Apply saved crop state
    iMagify.prototype.applyCropState = function(imageIndex) {
      // Sử dụng function applyCropState() đã được refactor
      applyCropState.call(this, imageIndex);
    };

    // Create sidebar with tab system for crop boxes
    iMagify.prototype.buildSidebar = function () {
      // Sử dụng function buildSidebar() đã được refactor
      buildSidebar.call(this);
    };

    // Switch to specific tab and show only that crop box
    iMagify.prototype.switchToTab = function (index) {
      // Sử dụng function switchToTab() đã được refactor
      switchToTab.call(this, index);
    };

    // Select crop box by index, bring to front, highlight sidebar, and scroll into view
    iMagify.prototype.selectCropBox = function (index) {
      this.switchToTab(index);
    };
  
    // Calculate intersection of crop boxes (allowed region)
    iMagify.prototype.getAllowedRect = function () {
      let allowed = null;
      const editorRect = this.editorContainer.getBoundingClientRect();
      this.cropBoxes.forEach(box => {
        const rect = box.getBoundingClientRect();
        const rel = {
          left: rect.left - editorRect.left,
          top: rect.top - editorRect.top,
          right: rect.right - editorRect.left,
          bottom: rect.bottom - editorRect.top
        };
        if (!allowed) {
          allowed = rel;
        } else {
          allowed.left = Math.max(allowed.left, rel.left);
          allowed.top = Math.max(allowed.top, rel.top);
          allowed.right = Math.min(allowed.right, rel.right);
          allowed.bottom = Math.min(allowed.bottom, rel.bottom);
        }
      });
      return allowed;
    };
  
    // Constrain watermark: ensure watermarkContainer maintains original ratio and doesn't exceed allowed bounds
    iMagify.prototype.constrainWatermark = function () {
      if (!this.options.watermark) return;
      this._log('==>imagify watermark', {
        action: 'constrain',
        data: this.options.watermark
      });
      // Sử dụng function updateWatermarkPosition() đã được refactor
      updateWatermarkPosition.call(this);

      this._log('==>imagify watermark', {
        action: 'constrain'
      });
    };
  
    // Adjust watermark size
    iMagify.prototype.adjustWatermarkSize = function(box) {
      if (!box.watermarkContainer || box.watermarkContainer.style.display === "none") return;
    
      const boxRect = box.getBoundingClientRect();
      const wmContainer = box.watermarkContainer;
      const wmImage = wmContainer.querySelector('img');
      if (!wmImage) return;
    
      // Scale factor for watermark size (20% of crop box width)
      const scaleFactor = 1;
    
      // New size = 20% * scaleFactor of crop box width
      const newWidth = boxRect.width * 0.2 * scaleFactor;
    
      // Apply new size - chỉ set width, để height chạy theo aspect-ratio
      wmContainer.style.width  = newWidth + "px";
      wmContainer.style.height = "";
    
      // Chỉ set size, không override position nếu đã được config
      // Position sẽ được set bởi watermark config trong updateCropBoxes
    
      // Ensure watermark doesn't exceed crop box bounds
      this.constrainWatermark();

      // Debug log: resize watermark
      this._log('==>imagify watermark', {
        action: 'resize',
        width: newWidth,
        boxIndex: this.cropBoxes.indexOf(box)
      });
    };
    
  
    // Helper for yielding control
    function nextFrame() {
      return new Promise(resolve => requestAnimationFrame(resolve));
    }

    // Safe stringify for logging
  function safeStringify(v) {
    try { return typeof v === 'string' ? v : JSON.stringify(v); } catch(_) { return '[Unserializable]'; }
  }


    // Main image processing function
    iMagify.prototype.processImage = async function (updateCallback) {
      // Extract config from options.output
      const outputs = this.options.output || { jpg: { name: 'jpg', q: 95 }, webp: { q: 95 }, png: {} };
      const preserveColor = !!this.options.preserveColor;
      const forcePNG = !!this.options.forcePNG;
      const webpRequested = preserveColor ? false : !!outputs.webp;
      
      // Determine primary format from options.output
      let primaryFmt = 'jpg'; // default
      if (outputs.jpg) primaryFmt = 'jpg';
      else if (outputs.jpeg) primaryFmt = 'jpeg';
      else if (outputs.png) primaryFmt = 'png';
      else if (outputs.webp) primaryFmt = 'webp';
      
      if (preserveColor || forcePNG) primaryFmt = 'png';
      
      // Get quality from the specific format config
      const formatConfig = outputs[primaryFmt] || outputs.jpg || outputs.jpeg || {};
      const primaryQuality = preserveColor || forcePNG ? 100 : (formatConfig.q || 90);
      
      // Get extension name from config
      const primaryExt = preserveColor || forcePNG ? 'png' : (formatConfig.name || primaryFmt);
      
      // Create base canvas with original image
      updateCallback("Đang tạo canvas gốc...");
      await nextFrame();
      
      const baseCanvas = await this.createBaseCanvas();
      
      // Process each crop box (temporarily show all boxes for processing)
      const sizes = {};
      
      // Store original visibility states
      const originalStates = this.cropBoxes.map(box => ({
        visibility: box.style.visibility,
        opacity: box.style.opacity
      }));
      
      // Make all boxes visible for processing
      this.cropBoxes.forEach(box => {
        box.style.visibility = 'visible';
        box.style.opacity = '1';
      });
      
      for (let i = 0; i < this.cropBoxes.length; i++) {
        updateCallback(`Đang xử lý crop box ${i+1}/${this.cropBoxes.length}...`);
        await nextFrame();
        
        const box = this.cropBoxes[i];
        const ratioKey = box.dataset.ratio;
        
        const cropData = await this.processCropBox(box, i, primaryFmt, primaryQuality, preserveColor, forcePNG);
        sizes[ratioKey] = cropData;
      }
      
      // Restore original visibility states
      this.cropBoxes.forEach((box, i) => {
        box.style.visibility = originalStates[i].visibility;
        box.style.opacity = originalStates[i].opacity;
      });
      
      // Process original image if needed
      let original = "";
      if (this.options.original) {
        updateCallback("Đang xử lý ảnh gốc...");
        await nextFrame();
        
        original = await this.exportCanvas(baseCanvas, primaryFmt, primaryQuality, preserveColor, forcePNG);
      }
      
      // Create result
      const currentImage = this.options.images[this.currentImageIndex];
      const currentURL = (typeof currentImage === 'object' && currentImage.src) ? currentImage.src : currentImage;
      const baseName = (typeof currentImage === 'object' && currentImage.name) ? currentImage.name.split('.')[0] : currentURL.split('/').pop().split('.')[0];
      
      const result = {
        filename: baseName,
        original: original,
        sizes: sizes,
        format: primaryExt,
        quality: primaryQuality,
        webp: webpRequested,
        optimize: true
      };
      
      return result;
    };
    
    // Create base canvas from main image
    iMagify.prototype.createBaseCanvas = async function () {
      try {
        const baseCanvas = await drawBase(this.mainImage, this.wantP3);
        
        // Lock preview if needed
        if (this.lockPreview) {
          lockPreviewTo(baseCanvas, this.mainImage);
        }
        
        return baseCanvas;
      } catch (error) {
        throw error;
      }
    };
    
    // Process a specific crop box
    iMagify.prototype.processCropBox = async function (box, index, primaryFmt, primaryQuality, preserveColor, forcePNG) {
      const ratioKey = box.dataset.ratio;
      
      // Temporarily make box visible to get accurate dimensions
      const originalVisibility = box.style.visibility;
      const originalOpacity = box.style.opacity;
      box.style.visibility = 'visible';
      box.style.opacity = '1';
      
      // Force layout recalculation
      box.offsetHeight;
      
      const boxRect = box.getBoundingClientRect();
      const imageRect = this.mainImage.getBoundingClientRect();
      const scaleX = this.mainImage.naturalWidth / imageRect.width;
      const scaleY = this.mainImage.naturalHeight / imageRect.height;
      
      const cropX = Math.round((boxRect.left - imageRect.left) * scaleX);
      const cropY = Math.round((boxRect.top - imageRect.top) * scaleY);
      const cropWidth = Math.round(boxRect.width * scaleX);
      const cropHeight = Math.round(boxRect.height * scaleY);
      
      
      // Restore original visibility
      box.style.visibility = originalVisibility;
      box.style.opacity = originalOpacity;
      
      // Create crop canvas
      const cropCanvas = document.createElement("canvas");
      cropCanvas.width = cropWidth;
      cropCanvas.height = cropHeight;
      
      const cropCtx = get2D(cropCanvas, { alpha: true }, this.wantP3);
      
      const isOneToOne = Math.abs(scaleX - 1) < 0.01 && Math.abs(scaleY - 1) < 0.01;
      cropCtx.imageSmoothingEnabled = !isOneToOne;
      cropCtx.imageSmoothingQuality = isOneToOne ? 'low' : 'high';
      cropCtx.filter = 'none';
      
      // Draw image to crop canvas
      try {
        const bmp = await decodeBitmap(this.mainImage, this.wantP3);
        cropCtx.globalCompositeOperation = 'source-over';
        cropCtx.drawImage(bmp, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
      } catch(error) {
        cropCtx.drawImage(this.mainImage, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
      }
      
      // Draw watermark if present
      await this.drawWatermarkOnCanvas(box, cropCtx, scaleX, scaleY, index);
      
      // Export crop canvas
      return await this.exportCanvas(cropCanvas, primaryFmt, primaryQuality, preserveColor, forcePNG);
    };
    
    // Draw watermark on canvas
    iMagify.prototype.drawWatermarkOnCanvas = async function (box, ctx, scaleX, scaleY, index) {
      if (!box.watermarkContainer || box.watermarkContainer.style.display === "none") {
        return;
      }
      
      const wmImage = box.watermarkContainer.querySelector('img');
      if (!wmImage) return;
      
      // Get watermark config for this specific crop box
      const sizeConfig = this.options.sizes[index];
      const wmConfig = sizeConfig?.watermark || this.options.watermark;
      const opacity = wmConfig?.opacity || 1.0;
      
      // Get watermark position relative to crop box (not main image)
      const boxRect = box.getBoundingClientRect();
      const wmRect = box.watermarkContainer.getBoundingClientRect();
      const aspect = parseFloat(box.watermarkContainer.dataset.aspect) || (wmImage.naturalWidth / wmImage.naturalHeight);
      
      // Calculate position relative to crop box
      const wmLeft = wmRect.left - boxRect.left;
      const wmTop = wmRect.top - boxRect.top;
      const wmWidth = wmRect.width;
      
      // Scale to canvas coordinates with locked aspect
      const wmX = wmLeft * scaleX;
      const wmY = wmTop * scaleY;
      const wmWidthScaled = wmWidth * scaleX;
      const wmHeightScaled = wmWidthScaled / aspect;
      
      // Debug log: draw watermark on canvas
      this._log('==>imagify watermark', {
        action: 'draw-on-canvas',
        index,
        x: wmX,
        y: wmY,
        width: wmWidthScaled,
        height: wmHeightScaled,
        opacity
      });

      // Save current context state
      ctx.save();
      
      try {
        // Set opacity for watermark
        ctx.globalAlpha = opacity;
        ctx.globalCompositeOperation = 'source-over';
        
        // Optimal watermark drawing
        const wmbmp = await decodeBitmap(wmImage, this.wantP3);
        ctx.drawImage(wmbmp, wmX, wmY, wmWidthScaled, wmHeightScaled);
      } catch (error) {
        // Fallback
        try {
          ctx.drawImage(wmImage, wmX, wmY, wmWidthScaled, wmHeightScaled);
        } catch (fallbackError) {
          console.error('[iMagify] Error drawing watermark:', fallbackError);
        }
      } finally {
        // Restore context state
        ctx.restore();
      }
    };
    
    // Export canvas to base64
    iMagify.prototype.exportCanvas = async function (canvas, primaryFmt, primaryQuality, preserveColor, forcePNG) {
      // Determine optimal format and quality
      let outFmt = primaryFmt;
      let outQuality = primaryQuality;
      
      if (preserveColor || forcePNG) {
        outFmt = 'png';
        outQuality = 1.0;
      } else if (primaryFmt === 'jpeg' || primaryFmt === 'jpg') {
        outFmt = 'jpeg';
        outQuality = Math.min(1, Math.max(0.85, primaryQuality / 100));
      }
      
      // Use optimal formula
      const dataURL = exportCanvas(canvas, outFmt, outQuality, this.wantP3);
      
      return dataURL;
    };

    // Build upload result: create object containing upload data for current image
    iMagify.prototype.buildUploadResult = async function () {
      const btn = this.saveBtn;
      const update = text => { 
        if (btn) {
          btn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>${text}`;
        }
      };
    
      // Step 1: Prepare
      update("Đang chuẩn bị xử lý ảnh...");
      await nextFrame();
    
      // Call main image processing function
      return await this.processImage(update);
    };
  
  
    // Hàm createSaveButton: tạo nút lưu riêng cho ảnh hiện tại
    iMagify.prototype.createSaveButton = function () {
      // Sử dụng function createSaveButton() đã được refactor
      createSaveButton.call(this);
    };
  
    // Hàm processCurrentUpload (v3 local-only): xử lý ảnh và trả kết quả qua callback, không gọi API
    iMagify.prototype.processCurrentUpload = async function () {
      // Sử dụng function processCurrentUpload() đã được refactor
      return await processCurrentUpload.call(this);
    };
  
    // Hàm highlightThumbnail: đánh dấu thumbnail của ảnh hiện tại
    iMagify.prototype.highlightThumbnail = function (index) {
      // Sử dụng function highlightThumbnail() đã được refactor
      highlightThumbnail.call(this, index);
    };
  
  
    // --- Utility: Resizable ---
    function makeElementResizable(el, container, aspectRatio, boundEl, onResizeEnd) {
      const resizer = document.createElement("div");
      Object.assign(resizer.style, {
        width: "16px",
        height: "16px",
        background: "rgba(255, 255, 255, 0.8)",
        border: "1px solid rgba(0, 0, 0, 0.3)",
        position: "absolute",
        right: "0",
        bottom: "0",
        cursor: "se-resize",
        zIndex: "9999",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        borderRadius: "2px",
        boxShadow: "0 1px 2px rgba(0,0,0,0.1)"
      });

      resizer.innerHTML = `
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M13.8284 13.8284L20.8995 20.8995M20.8995 20.8995L20.7816 15.1248M20.8995 20.8995L15.1248 20.7816" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.89948 13.8284L2.82841 20.8995M2.82841 20.8995L8.60312 20.7816M2.82841 20.8995L2.94626 15.1248" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M13.8284 9.8995L20.8995 2.82843M20.8995 2.82843L15.1248 2.94629M20.8995 2.82843L20.7816 8.60314" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.89947 9.89951L2.8284 2.82844M2.8284 2.82844L2.94626 8.60315M2.8284 2.82844L8.60311 2.94629" 
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      `;

      el.appendChild(resizer);
      let originalWidth = 0, originalHeight = 0, originalMouseX = 0, originalMouseY = 0;
      
      resizer.addEventListener("mousedown", function (e) {
        e.stopPropagation();
        originalWidth = parseFloat(getComputedStyle(el, null).getPropertyValue("width").replace("px", ""));
        originalHeight = parseFloat(getComputedStyle(el, null).getPropertyValue("height").replace("px", ""));
        originalMouseX = e.clientX;
        originalMouseY = e.clientY;
        document.addEventListener("mousemove", mouseMoveResize);
        document.addEventListener("mouseup", mouseUpResize);
        e.preventDefault();
      });

      function mouseMoveResize(e) {
        let dx = e.clientX - originalMouseX;
        let dy = e.clientY - originalMouseY;
        
        if (aspectRatio) {
          if (Math.abs(dx) > Math.abs(dy)) {
            dy = dx / aspectRatio;
          } else {
            dx = dy * aspectRatio;
          }
        }
        
        let newWidth = originalWidth + dx;
        let newHeight = originalHeight + dy;
        
        const minSize = 30;
        if (newWidth < minSize) {
          newWidth = minSize;
          newHeight = minSize / aspectRatio;
        }
        if (newHeight < minSize) {
          newHeight = minSize;
          newWidth = minSize * aspectRatio;
        }
        
        const containerRect = container.getBoundingClientRect();
        let boundRect = boundEl ? boundEl.getBoundingClientRect() : containerRect;
        const elRect = el.getBoundingClientRect();
        const elOffsetLeft = elRect.left - containerRect.left;
        const elOffsetTop = elRect.top - containerRect.top;
        const maxWidth = boundRect.right - containerRect.left - elOffsetLeft;
        const maxHeight = boundRect.bottom - containerRect.top - elOffsetTop;
        
        if (newWidth > maxWidth) {
          newWidth = maxWidth;
          newHeight = maxWidth / aspectRatio;
        }
        if (newHeight > maxHeight) {
          newHeight = maxHeight;
          newWidth = maxHeight * aspectRatio;
        }
        
        el.style.width = newWidth + "px";
        el.style.height = newHeight + "px";

        // Điều chỉnh kích thước watermark nếu đây là crop box
        if (el.classList.contains('iMagify-cropBox')) {
          const iMagifyInstance = window.iMagifyInstance;
          if (iMagifyInstance) {
            iMagifyInstance.adjustWatermarkSize(el);
          }
        }
      }

      function mouseUpResize() {
        document.removeEventListener("mousemove", mouseMoveResize);
        document.removeEventListener("mouseup", mouseUpResize);
        if (onResizeEnd) onResizeEnd();
      }
    }
  
    // Thêm CSS cho tính năng kéo thả
    const style = document.createElement('style');
    style.textContent = `
        .draggable-item {
            transition: transform 0.2s, box-shadow 0.2s;
            touch-action: none;
            user-select: none;
        }
        .draggable-item.dragging {
            opacity: 0.5;
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .draggable-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10;
        }
        @media (hover: none) {
            .draggable-item:hover {
                transform: none;
                box-shadow: none;
            }
            .draggable-item.dragging {
                transform: scale(1.05);
            }
        }

        /* Animation cho icon loading */
        @keyframes iMagify-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .iMagify-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: iMagify-spin 1s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* Animation cho thông báo */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
  
    window.iMagify = function (options) {
      return new iMagify(options);
    };
  })();