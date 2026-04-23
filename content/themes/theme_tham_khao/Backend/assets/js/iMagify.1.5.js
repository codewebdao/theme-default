// iMagify - Image Editor Library
// Crop Boxes change layer size
(function () {
    // Color strategy configuration
    const COLOR_STRATEGY = 'srgb';
    const LOCK_PREVIEW_TO_OUTPUT = true;
    
    // Color Theme System
    const iMagifyTheme = {
        // Primary Colors
        primary: {
            bg: '#1f2937',      // Dark gray background
            border: '#374151',   // Medium gray border
            text: '#e5e7eb',     // Light gray text
            accent: '#3b82f6'    // Blue accent
        },
        
        // Secondary Colors
        secondary: {
            bg: '#111827',       // Darker background
            border: '#4b5563',   // Lighter border
            text: '#cbd5e1',     // Medium gray text
            hover: '#4b5563'     // Hover state
        },
        
        // Content Areas
        content: {
            bg: '#333',          // Editor background
            border: '#555',      // Content border
            text: '#fff'         // Content text
        },
        
        // Button Colors
        button: {
            primary: {
                bg: 'linear-gradient(180deg, #3b82f6 0%, #2563eb 100%)',
                border: '#1f4ed8',
                text: '#fff',
                hover: 'rgba(59, 130, 246, 0.2)'
            },
            danger: {
                bg: 'linear-gradient(180deg, #dc2626 0%, #b91c1c 100%)',
                border: '#991b1b',
                text: '#fff',
                hover: 'rgba(220, 38, 38, 0.2)'
            },
            warning: {
                bg: 'linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%)',
                border: '#d97706',
                text: '#111827',
                hover: 'rgba(251, 191, 36, 0.2)'
            }
        },
        
        // Watermark Toggle
        watermark: {
            bg: 'rgba(59, 130, 246, 0.1)',
            border: 'rgba(59, 130, 246, 0.3)',
            text: '#93c5fd',
            hover: 'rgba(59, 130, 246, 0.2)',
            toggleOn: '#10b981',
            toggleOff: '#ef4444'
        },
        
        // Crop Box Colors
        cropBox: [
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
        ]
    };
    
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
        // Use async toBlob + ObjectURL to avoid heavy sync toDataURL and allow revocation
        canvas.toBlob((blob) => {
          if (!blob) return;
          const url = URL.createObjectURL(blob);
          const prev = imgEl.dataset && imgEl.dataset.previewUrl;
          if (prev) {
            try { URL.revokeObjectURL(prev); } catch (_) {}
          }
          imgEl.src = url;
          if (imgEl.dataset) imgEl.dataset.previewUrl = url;
        }, 'image/png');
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
      // Không cần cropStates nữa vì đã sử dụng dataManager.process
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
      
      // Auto add original size option (default: true)
      this.autoAddOriginalSize = options.autoAddOriginalSize !== false;
      
    // Crop box colors from theme
    this.cropBoxColors = iMagifyTheme.cropBox;
    
    // Centralized Data Management System
    this.dataManager = new iMagifyDataManager(this.options);
    }

    function setupGlobalErrorHandlers() {
      if (!window.__iMagifyErrorHooked) {
        window.addEventListener('error', function (e) {
        });
        window.addEventListener('unhandledrejection', function (e) {
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
        display: "grid",
        gridTemplateColumns: "200px 1fr",
        gridTemplateRows: "80px 1fr",
        gridTemplateAreas: `
          "thumbnails thumbnails"
          "sidebar content"
        `
      });
      document.body.appendChild(this.modal);
    }

    function createCloseButton() {
      this.closeBtn = document.createElement("div");
      this.closeBtn.textContent = "×";
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
        gridArea: "thumbnails",
        display: "flex",
        overflowX: "auto",
        padding: "8px",
        backgroundColor: iMagifyTheme.secondary.bg,
        alignItems: "center",
        borderBottom: `1px solid ${iMagifyTheme.primary.border}`,
        gap: "8px"
      });
      this.modal.appendChild(this.thumbnailContainer);
      
      // Thêm nút upload thêm ảnh
      createAddImageButton.call(this);
    }

    function createAddImageButton() {
      const addBtn = document.createElement("div");
      addBtn.className = "iMagify-add-image-btn";
      addBtn.textContent = "+";
      
      Object.assign(addBtn.style, {
        width: "60px",
        height: "60px",
        backgroundColor: iMagifyTheme.primary.bg,
        border: `2px dashed ${iMagifyTheme.primary.border}`,
        borderRadius: "8px",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        cursor: "pointer",
        color: iMagifyTheme.primary.text,
        fontSize: "24px",
        fontWeight: "bold",
        transition: "all 0.2s ease",
        flexShrink: "0",
        userSelect: "none"
      });
      
      // Hover effects
      addBtn.addEventListener("mouseenter", () => {
        addBtn.style.backgroundColor = iMagifyTheme.secondary.hover;
        addBtn.style.borderColor = iMagifyTheme.primary.accent;
        addBtn.style.color = iMagifyTheme.primary.accent;
        addBtn.style.transform = "scale(1.05)";
      });
      
      addBtn.addEventListener("mouseleave", () => {
        addBtn.style.backgroundColor = iMagifyTheme.primary.bg;
        addBtn.style.borderColor = iMagifyTheme.primary.border;
        addBtn.style.color = iMagifyTheme.primary.text;
        addBtn.style.transform = "scale(1)";
      });
      
      // Click event để upload thêm ảnh
      addBtn.addEventListener("click", () => {
        uploadAdditionalImage.call(this);
      });
      
      this.thumbnailContainer.appendChild(addBtn);
    }

    function uploadAdditionalImage() {
      const fileInput = document.createElement("input");
      fileInput.type = "file";
      fileInput.accept = "image/*";
      fileInput.multiple = true;
      fileInput.style.display = "none";
      
      fileInput.addEventListener("change", (e) => {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
          addImagesToGallery.call(this, files);
        }
        document.body.removeChild(fileInput);
      }, { once: true });
      
      document.body.appendChild(fileInput);
      fileInput.click();
    }

    function addImagesToGallery(files) {
      files.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const dataUrl = e.target.result;
          
          // Thêm ảnh mới vào dataManager
          const newImageIndex = this.dataManager.addImage(file, dataUrl);
          
          // Thêm vào options.images để tương thích với code cũ
          const newImage = {
            src: dataUrl,
            file: file,
            name: file.name
          };
          // Cập nhật vào dataManager.process thay vì options
          const processImage = this.dataManager.getProcessImage(newImageIndex);
          if (processImage) {
            processImage.image = dataUrl;
            processImage.file = file;
            processImage.name = file.name;
          }
          
          // Tạo thumbnail cho ảnh mới
          const thumbnail = createSingleThumbnail.call(this, newImage, newImageIndex);
          // Thêm thumbnail trước nút "+" (nút cuối cùng)
          const addBtn = this.thumbnailContainer.querySelector('.iMagify-add-image-btn');
          if (addBtn) {
            this.thumbnailContainer.insertBefore(thumbnail, addBtn);
          } else {
            this.thumbnailContainer.appendChild(thumbnail);
          }
          
          // Nếu đây là ảnh đầu tiên được thêm, chuyển sang ảnh đó
          // Kiểm tra số lượng ảnh từ dataManager.process
          const totalImages = this.dataManager.process.length;
          if (totalImages === 1) {
            this.currentImageIndex = 0;
            this.mainImage.src = dataUrl;
            this.mainImage.onload = () => {
              this.updateCropBoxes();
              this.createSaveButton();
              this.buildSidebar();
            };
          } else {
            // Nếu đã có ảnh trước đó, chuyển sang ảnh mới được upload
            switchToNewUploadedImage.call(this, newImageIndex, dataUrl);
          }
        };
        reader.readAsDataURL(file);
      });
    }

    function createContentContainer() {
      this.contentContainer = document.createElement("div");
      this.contentContainer.id = "iMagify-content";
      Object.assign(this.contentContainer.style, {
        gridArea: "content",
        display: "grid",
        gridTemplateRows: "50px 1fr 60px",
        gridTemplateAreas: `
          "topbar"
          "editor"
          "buttons"
        `,
        backgroundColor: iMagifyTheme.content.bg
      });
      this.modal.appendChild(this.contentContainer);
    }

    function createEditorContainer() {
      this.editorContainer = document.createElement("div");
      this.editorContainer.id = "iMagify-editor";
      Object.assign(this.editorContainer.style, {
        gridArea: "editor",
        position: "relative",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        backgroundColor: iMagifyTheme.content.bg,
        overflow: "auto"
      });
      this.contentContainer.appendChild(this.editorContainer);
    }

    function createTopbar() {
      this.topbar = document.createElement("div");
      this.topbar.id = "iMagify-topbar";
      Object.assign(this.topbar.style, {
        gridArea: "topbar",
        background: `linear-gradient(135deg, ${iMagifyTheme.primary.bg} 0%, ${iMagifyTheme.primary.border} 100%)`,
        borderBottom: `1px solid ${iMagifyTheme.secondary.border}`,
        display: "flex",
        alignItems: "center",
        padding: "0 16px",
        boxShadow: "0 2px 4px rgba(0,0,0,0.1)"
      });
      
      // Container cho các tools
      this.toolsContainer = document.createElement("div");
      Object.assign(this.toolsContainer.style, {
        display: "flex",
        alignItems: "center",
        gap: "12px",
        flex: "1"
      });
      this.topbar.appendChild(this.toolsContainer);
      
      this.contentContainer.appendChild(this.topbar);
    }

    function createWatermarkToggleTool() {
      // Kiểm tra xem có watermark không từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const hasWatermark = processImage?.watermark;
      
      if (!hasWatermark) return null;
      
      const toggleBtn = document.createElement("button");
      toggleBtn.className = "iMagify-watermark-toggle-tool";
      Object.assign(toggleBtn.style, {
        display: "flex",
        alignItems: "center",
        gap: "4px",
        padding: "6px 10px",
        background: iMagifyTheme.watermark.bg,
        border: `1px solid ${iMagifyTheme.watermark.border}`,
        borderRadius: "4px",
        color: iMagifyTheme.watermark.text,
        cursor: "pointer",
        fontSize: "12px",
        fontWeight: "500",
        transition: "all 0.2s ease",
        minWidth: "80px",
        height: "32px"
      });
      
      // Thêm label ngắn gọn
      const label = document.createElement("span");
      label.textContent = "Watermark";
      toggleBtn.appendChild(label);
      
      // Thêm mũi tên dropdown
      const arrow = document.createElement("span");
      arrow.textContent = "▼";
      Object.assign(arrow.style, {
        fontSize: "10px",
        transition: "transform 0.2s ease",
        marginLeft: "4px"
      });
      toggleBtn.appendChild(arrow);
      
      // Thêm toggle switch
      const toggleSwitch = document.createElement("div");
      toggleSwitch.className = "watermark-toggle-switch";
      Object.assign(toggleSwitch.style, {
        width: "24px",
        height: "14px",
        background: iMagifyTheme.watermark.toggleOn,
        borderRadius: "7px",
        position: "relative",
        cursor: "pointer",
        transition: "all 0.2s ease"
      });
      
      // Thêm toggle knob
      const toggleKnob = document.createElement("div");
      toggleKnob.className = "watermark-toggle-knob";
      Object.assign(toggleKnob.style, {
        width: "12px",
        height: "12px",
        background: "#fff",
        borderRadius: "50%",
        position: "absolute",
        top: "1px",
        right: "1px",
        transition: "all 0.2s ease",
        boxShadow: "0 1px 3px rgba(0,0,0,0.2)"
      });
      
      toggleSwitch.appendChild(toggleKnob);
      toggleBtn.appendChild(toggleSwitch);
      
      // Hover effects
      toggleBtn.addEventListener("mouseenter", () => {
        toggleBtn.style.background = iMagifyTheme.watermark.hover;
        toggleBtn.style.borderColor = "rgba(59, 130, 246, 0.5)";
        toggleBtn.style.transform = "translateY(-1px)";
      });
      
      toggleBtn.addEventListener("mouseleave", () => {
        toggleBtn.style.background = iMagifyTheme.watermark.bg;
        toggleBtn.style.borderColor = iMagifyTheme.watermark.border;
        toggleBtn.style.transform = "translateY(0)";
      });
      
      // Event handler cho toggle và dropdown
      toggleBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        
        // Kiểm tra xem click vào toggle switch hay vào phần khác
        const isToggleClick = e.target.closest('.watermark-toggle-switch');
        
        if (isToggleClick) {
          // Click vào toggle switch - toggle watermark của layer hiện tại
          this.toggleCurrentLayerWatermark();
          
          // Cập nhật trạng thái toggle switch dựa trên layer hiện tại
          const isVisible = this.isCurrentLayerWatermarkVisible();
          
          // Cập nhật toggle switch
          if (isVisible) {
            toggleSwitch.style.background = iMagifyTheme.watermark.toggleOn;
            toggleKnob.style.right = "1px";
          } else {
            toggleSwitch.style.background = iMagifyTheme.watermark.toggleOff;
            toggleKnob.style.right = "11px";
          }
          
          toggleBtn.style.opacity = isVisible ? "1" : "0.6";
        } else {
          // Click vào phần khác - hiển thị dropdown
          this.toggleWatermarkDropdown(toggleBtn, arrow);
        }
      });
      
      // Cập nhật toggle button theo layer hiện tại
      setTimeout(() => {
        this.updateWatermarkToggleButton();
      }, 100);
      
      return toggleBtn;
    }

    function toggleAllWatermarks() {
      
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (!processImage || !processImage.watermark) return;
      
      const isVisible = processImage.watermark.isVisible;
      const newVisibility = !isVisible;
      
      // Cập nhật trong dataManager
      this.dataManager.updateWatermarkVisibility(this.currentImageIndex, newVisibility);
      
      // Cập nhật UI
      this.cropBoxes.forEach((box, index) => {
        if (box.watermarkContainer) {
          box.watermarkContainer.style.display = newVisibility ? "block" : "none";
        } else {
        }
      });
    }

    // Toggle watermark chỉ cho layer hiện tại
    iMagify.prototype.toggleCurrentLayerWatermark = function() {
      // Kiểm tra xem có layer nào được chọn không
      if (this.selectedCropIndex === undefined || this.selectedCropIndex < 0 || this.selectedCropIndex >= this.cropBoxes.length) {
        return;
      }
      
      const currentBox = this.cropBoxes[this.selectedCropIndex];
      if (!currentBox || !currentBox.watermarkContainer) {
        return;
      }
      
      // Lấy trạng thái hiện tại của watermark
      const isCurrentlyVisible = currentBox.watermarkContainer.style.display !== 'none';
      const newVisibility = !isCurrentlyVisible;
      
      // Cập nhật UI cho layer hiện tại
      currentBox.watermarkContainer.style.display = newVisibility ? "block" : "none";
      
      // Cập nhật trong dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (processImage && processImage.watermark) {
        processImage.watermark.visible = newVisibility;
      }
      
      // Cập nhật toggle button sau khi toggle
      this.updateWatermarkToggleButton();
    };

    // Kiểm tra watermark của layer hiện tại có visible không
    iMagify.prototype.isCurrentLayerWatermarkVisible = function() {
      if (this.selectedCropIndex === undefined || this.selectedCropIndex < 0 || this.selectedCropIndex >= this.cropBoxes.length) {
        return false;
      }
      
      const currentBox = this.cropBoxes[this.selectedCropIndex];
      if (!currentBox || !currentBox.watermarkContainer) {
        return false;
      }
      
      return currentBox.watermarkContainer.style.display !== 'none';
    };
    
    // Cập nhật toggle button theo layer hiện tại
    iMagify.prototype.updateWatermarkToggleButton = function() {
      const toggleBtn = document.querySelector('.iMagify-watermark-toggle-tool');
      if (!toggleBtn) {
        return;
      }
      
      const toggleSwitch = toggleBtn.querySelector('.watermark-toggle-switch');
      const toggleKnob = toggleBtn.querySelector('.watermark-toggle-knob');
      
      if (!toggleSwitch || !toggleKnob) {
        return;
      }
      
      // Kiểm tra visibility của layer hiện tại
      const isVisible = this.isCurrentLayerWatermarkVisible();
      
      
      // Cập nhật toggle switch
      if (isVisible) {
        toggleSwitch.style.background = iMagifyTheme.watermark.toggleOn;
        toggleKnob.style.right = "1px";
        toggleBtn.style.opacity = "1";
      } else {
        toggleSwitch.style.background = iMagifyTheme.watermark.toggleOff;
        toggleKnob.style.right = "11px";
        toggleBtn.style.opacity = "0.6";
      }
      
    };

    function areWatermarksVisible() {
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (!processImage || !processImage.watermark) return false;
      
      return processImage.watermark.isVisible;
    }

    // Toggle watermark dropdown với thông số realtime
    iMagify.prototype.toggleWatermarkDropdown = function(toggleBtn, arrow) {
      // Kiểm tra xem dropdown đã tồn tại chưa
      const existingDropdown = document.getElementById('iMagify-watermark-dropdown');
      
      if (existingDropdown) {
        // Đóng dropdown nếu đã mở
        document.body.removeChild(existingDropdown);
        arrow.style.transform = 'rotate(0deg)';
        return;
      }
      
      // Tạo dropdown container
      const dropdown = document.createElement('div');
      dropdown.id = 'iMagify-watermark-dropdown';
      
      // Lấy vị trí của toggle button
      const toggleRect = toggleBtn.getBoundingClientRect();
      
      Object.assign(dropdown.style, {
        position: 'fixed',
        top: (toggleRect.bottom + 5) + 'px',
        left: toggleRect.left + 'px',
        backgroundColor: iMagifyTheme.primary.bg,
        border: `1px solid ${iMagifyTheme.primary.border}`,
        borderRadius: '6px',
        padding: '0',
        minWidth: '280px',
        maxWidth: '400px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
        zIndex: '20000',
        fontSize: '12px',
        color: iMagifyTheme.primary.text
      });
      
      // Lấy thông tin watermark từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const watermarkData = processImage?.watermark;
      
      // Lấy thông tin realtime từ DOM chỉ cho layer hiện tại
              const realtimeInfo = [];
              if (this.selectedCropIndex !== undefined && this.selectedCropIndex >= 0 && this.selectedCropIndex < this.cropBoxes.length) {
                const currentBox = this.cropBoxes[this.selectedCropIndex];
                if (currentBox && currentBox.watermarkContainer && currentBox.watermarkContainer.style.display !== 'none') {
                  // Sử dụng hàm tính toán chung
                  const wm = currentBox.watermarkContainer;
                  const position = calculateWatermarkPositionFromDOM(wm, currentBox);
                  
                  realtimeInfo.push({
                    boxIndex: this.selectedCropIndex + 1,
                    topPercent: position.topPercent.toFixed(2),
                    leftPercent: position.leftPercent.toFixed(2),
                    widthPercent: position.widthPercent.toFixed(2)
                  });
                }
              }
      
      // Tạo nội dung dropdown ngắn gọn
      let dropdownContent = '';
      
      if (realtimeInfo.length > 0) {
        // Hiển thị thông số ngắn gọn
        realtimeInfo.forEach(info => {
          dropdownContent += `
            <div style="background: ${iMagifyTheme.secondary.bg}; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 12px;">
              <div style="color: ${iMagifyTheme.primary.text};">Top: ${info.topPercent}% | Left: ${info.leftPercent}% | Width: ${info.widthPercent}%</div>
            </div>
          `;
        });
      } else {
        dropdownContent += `
          <div style="background: ${iMagifyTheme.secondary.bg}; padding: 8px; border-radius: 4px; font-size: 12px; color: ${iMagifyTheme.secondary.text};">
            ${this.selectedCropIndex !== undefined ? `Layer ${this.selectedCropIndex + 1} không có watermark` : 'Chưa chọn layer'}
          </div>
        `;
      }
      
      
      dropdown.innerHTML = dropdownContent;
      
      // Thêm dropdown vào DOM
      document.body.appendChild(dropdown);
      
      // Xoay mũi tên
      arrow.style.transform = 'rotate(180deg)';
      
      // Chỉ đóng dropdown khi click vào mũi tên hoặc toggle button
      const closeDropdown = (e) => {
        // Chỉ đóng nếu click vào toggle button (bao gồm mũi tên)
        if (toggleBtn.contains(e.target)) {
          document.body.removeChild(dropdown);
          arrow.style.transform = 'rotate(0deg)';
          document.removeEventListener('click', closeDropdown);
        }
      };
      
      // Delay để tránh đóng ngay lập tức
      setTimeout(() => {
        document.addEventListener('click', closeDropdown, { once: true });
      }, 100);
      
      // Cập nhật thông số realtime khi watermark được di chuyển
      const updateRealtimeInfo = () => {
        const existingDropdown = document.getElementById('iMagify-watermark-dropdown');
        if (!existingDropdown) return;
        
        // Lấy thông tin realtime mới
        const newRealtimeInfo = [];
        if (this.selectedCropIndex !== undefined && this.selectedCropIndex >= 0 && this.selectedCropIndex < this.cropBoxes.length) {
          const currentBox = this.cropBoxes[this.selectedCropIndex];
          if (currentBox && currentBox.watermarkContainer && currentBox.watermarkContainer.style.display !== 'none') {
            // Sử dụng hàm tính toán chung
            const wm = currentBox.watermarkContainer;
            const position = calculateWatermarkPositionFromDOM(wm, currentBox);
            
            newRealtimeInfo.push({
              boxIndex: this.selectedCropIndex + 1,
              topPercent: position.topPercent.toFixed(2),
              leftPercent: position.leftPercent.toFixed(2),
              widthPercent: position.widthPercent.toFixed(2)
            });
          }
        }
        
        // Cập nhật nội dung dropdown
        let newDropdownContent = '';
        if (newRealtimeInfo.length > 0) {
          newRealtimeInfo.forEach(info => {
            newDropdownContent += `
              <div style="background: ${iMagifyTheme.secondary.bg}; padding: 8px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                <div style="color: ${iMagifyTheme.primary.text};">Top: ${info.topPercent}% | Left: ${info.leftPercent}% | Width: ${info.widthPercent}%</div>
              </div>
            `;
          });
        } else {
          newDropdownContent += `
            <div style="background: ${iMagifyTheme.secondary.bg}; padding: 8px; border-radius: 4px; font-size: 12px; color: ${iMagifyTheme.secondary.text};">
              ${this.selectedCropIndex !== undefined ? `Layer ${this.selectedCropIndex + 1} không có watermark` : 'Chưa chọn layer'}
            </div>
          `;
        }
        
        existingDropdown.innerHTML = newDropdownContent;
      };
      
      // Lưu reference để có thể gọi từ bên ngoài
      this.updateWatermarkDropdown = updateRealtimeInfo;
    };

    function createSidebar() {
      this.sidebar = document.createElement("div");
      this.sidebar.id = "iMagify-sidebar";
      Object.assign(this.sidebar.style, {
        gridArea: "sidebar",
        background: iMagifyTheme.primary.bg,
        color: iMagifyTheme.primary.text,
        borderRight: `1px solid ${iMagifyTheme.primary.border}`,
        padding: "8px 6px",
        overflowY: "auto",
        display: "flex",
        flexDirection: "column"
      });
      this.modal.appendChild(this.sidebar);
    }

    function createButtonArea() {
      this.buttonArea = document.createElement("div");
      this.buttonArea.id = "iMagify-buttons";
      Object.assign(this.buttonArea.style, {
        gridArea: "buttons",
        display: "flex",
        justifyContent: "flex-end",
        alignItems: "center",
        padding: "8px 16px",
        backgroundColor: iMagifyTheme.primary.bg,
        borderTop: `1px solid ${iMagifyTheme.primary.border}`,
        gap: "8px"
      });
      this.contentContainer.appendChild(this.buttonArea);
    }

    function createMainImage() {
      this.mainImage = document.createElement("img");
      this.mainImage.id = "iMagify-mainImage";
      Object.assign(this.mainImage.style, {
        maxWidth: "calc(100% - 40px)",
        maxHeight: "calc(100vh - 200px)",
        width: "auto",
        height: "auto",
        position: "relative",
        filter: "none",
        opacity: "1",
        mixBlendMode: "normal",
        objectFit: "contain",
        margin: "auto",
        display: "block"
      });
      
      this.editorContainer.appendChild(this.mainImage);
    }

    // Thumbnail Management Functions
    function createThumbnails() {
      // Sử dụng dataManager.process thay vì options.images
      this.dataManager.process.forEach((processImage, index) => {
        const imgSrc = processImage.image || processImage.src;
        const thumb = createSingleThumbnail.call(this, imgSrc, index);
        // Thêm thumbnail trước nút "+" (nút cuối cùng)
        const addBtn = this.thumbnailContainer.querySelector('.iMagify-add-image-btn');
        if (addBtn) {
          this.thumbnailContainer.insertBefore(thumb, addBtn);
        } else {
          this.thumbnailContainer.appendChild(thumb);
        }
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
        switchToImage.call(this, index, imgSrc);
      });
      
      return thumb;
    }

    function switchToNewUploadedImage(index, dataUrl) {
      // Cập nhật current image index
      this.currentImageIndex = index;
      
      // Highlight thumbnail mới
      updateThumbnailSelection.call(this, index);
      
      // Cập nhật main image
      this.mainImage.src = dataUrl;
      
      // Khi main image load xong, refresh UI
      this.mainImage.onload = () => {
        // Auto add original size crop box cho ảnh mới upload
        autoAddOriginalSizeCropBox.call(this);
        
        refreshEditorUI.call(this);
        // Reset crop state cho ảnh mới trong dataManager.process
        const processImage = this.dataManager.getProcessImage(index);
        if (processImage) {
          processImage.cropBoxes = null;
        }
      };
    }

    function switchToImage(index, imgSrc) {
      // Cập nhật thumbnail selection
      updateThumbnailSelection.call(this, index);
      
      // Cập nhật current image index
      this.currentImageIndex = index;
      
      // Cập nhật main image
      this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;
      
      // Khi main image load xong, refresh UI
      this.mainImage.onload = () => {
        // Auto add original size crop box cho ảnh mới
        autoAddOriginalSizeCropBox.call(this);
        
        refreshEditorUI.call(this);
        // Restore crop state cho ảnh này
        this.applyCropState();
      };
    }

    function updateThumbnailSelection(index) {
      // Bỏ highlight tất cả thumbnails
      document.querySelectorAll(".iMagify-thumbnail").forEach(el => 
        el.style.border = "2px solid transparent"
      );
      
      // Highlight thumbnail được chọn
      const thumb = document.querySelectorAll(".iMagify-thumbnail")[index];
      if (thumb) {
        thumb.style.border = "2px solid #4CAF50";
      }
    }

    // Crop Box Management Functions
    function createCropBoxes() {
      this.cropBoxes = [];
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      // Nếu chưa có cropBoxes trong dataManager.process, tạo từ this.dataManager.init.cropBoxes
      if (!processImage?.cropBoxes && this.dataManager.init.cropBoxes) {
        processImage.cropBoxes = this.dataManager.init.cropBoxes.map(size => ({
          width: size.width,
          height: size.height,
          leftPct: 0.1, // Default position
          topPct: 0.1,
          widthPct: 0.3, // Default size
          heightPct: 0.3
        }));
      }

      // Sử dụng dataManager.process làm nguồn dữ liệu chính
      if (processImage && processImage.cropBoxes) {
        processImage.cropBoxes.forEach((cropBoxData, index) => {
          try {
            const box = createSingleCropBox.call(this, cropBoxData, index);
            if (box) {
              this.editorContainer.appendChild(box);
              this.cropBoxes.push(box);
            }
          } catch (err) {
          }
        });
      }
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
      box.dataset.name = size.name || `Layer ${index + 1}`; // ✅ Lưu name vào dataset
      
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
    function addWatermarkToCropBox(box, size, index) {
      const wmContainer = createWatermarkContainer.call(this, box, size, index);
      createWatermarkToggle.call(this, box, wmContainer, index); // Chỉ kiểm tra config, không tạo button
      
      box.appendChild(wmContainer);
      box.watermarkContainer = wmContainer;
    }

    function createWatermarkContainer(box, size, index) {
      const wmContainer = document.createElement("div");
      wmContainer.className = "iMagify-cropBox-watermark";
      wmContainer.dataset.boxIndex = index;
      // Force reset tất cả positioning properties để tránh conflict
      wmContainer.style.cssText = '';
      
      Object.assign(wmContainer.style, {
        position: "absolute",
        cursor: "move",
        width: "20%", // Chuyển sang phần trăm
        height: "auto",
        zIndex: "1200",
        display: "block",
        minWidth: "5%", // Chuyển sang phần trăm
        minHeight: "5%", // Chuyển sang phần trăm
        left: "0%", // Sử dụng left thay vì right
        top: "0%", // Sử dụng top thay vì bottom
        right: "auto", // Reset right
        bottom: "auto", // Reset bottom
        inset: "auto", // Reset inset để tránh conflict
        margin: "0", // Reset margin
        padding: "0", // Reset padding
        transform: "none" // Reset transform
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
      
      // Lấy config từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const watermarkData = processImage?.watermark;
      const wmSrc = watermarkData?.src;
      
      
      if (wmSrc) {
        wmImage.src = wmSrc;
      }
      
      Object.assign(wmImage.style, {
        width: "100%",
        height: "auto",
        objectFit: "contain",
        opacity: watermarkData?.opacity || 1.0
      });

      return wmImage;
    }

    function createWatermarkToggle(box, wmContainer, index) {
      // Không tạo toggle button nữa vì đã có trong topbar
      // Chỉ kiểm tra và ẩn watermark nếu không có config từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const watermarkData = processImage?.watermark;
      const wmSrc = watermarkData?.src;
      
      if (!wmSrc) {
        wmContainer.style.display = "none";
      }

      return null; // Không trả về button nào
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
        transition: "box-shadow 0.3s ease",
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
      }
      
      // Resize functionality
      try {
        makeElementResizable(box, this.editorContainer, ratioNumber, this.mainImage, () => { 
          handleCropBoxResizeEnd.call(this);
        });
      } catch (e) {
      }
    }

    function handleCropBoxDragEnd() {
      // Khi kéo crop box, watermark tự động neo theo với giá trị % (không cần render lại)
      // Watermark giữ nguyên vị trí phần trăm, không cần constrain để tránh nhảy vị trí
      // this.constrainWatermark();
      updateZIndex.call(this);
      
      // Lưu crop state (bao gồm watermark state)
      setTimeout(() => {
        try { 
          this.saveCropState(this.currentImageIndex); 
        } catch(e) { 
        }
      }, 10);
    }

    function handleCropBoxResizeEnd() {
      // Khi resize crop box, watermark tự động neo theo với giá trị % (không cần render lại)
      // Không cần constrain watermark để tránh nhảy vị trí
      // this.constrainWatermark();
      updateZIndex.call(this);
      
      // Lưu crop state (bao gồm watermark state)
      setTimeout(() => {
        try { 
          this.saveCropState(this.currentImageIndex); 
        } catch(e) { 
        }
      }, 10);
    }

    // Watermark Management Functions
    
    // Function tính toán watermark position và size chung
    iMagify.prototype.calculateWatermarkPosition = function(box, watermarkData) {
      if (!box.watermarkContainer || !watermarkData) {
        return null;
      }
      
      const position = watermarkData.position || 'bottom-right';
      const padding = watermarkData.padding || 10;
      const widthPercent = parseFloat(watermarkData.width) || 20; // Đảm bảo là number
      
      
      // Lấy kích thước crop box
      const boxRect = box.getBoundingClientRect();
      
      // Quy đổi padding từ pixel sang phần trăm
      const paddingLeftPercent = (padding / boxRect.width) * 100;
      const paddingTopPercent = (padding / boxRect.height) * 100;
      
      // Tính chiều cao của watermark dựa trên aspect ratio
      const wmImage = box.watermarkContainer.querySelector('img');
      let heightPercent = widthPercent; // Default fallback
      
      if (wmImage && wmImage.naturalWidth && wmImage.naturalHeight) {
        const watermarkWidthPixels = (widthPercent / 100) * boxRect.width;
        const watermarkHeightPixels = watermarkWidthPixels * (wmImage.naturalHeight / wmImage.naturalWidth);
        heightPercent = (watermarkHeightPixels / boxRect.height) * 100;
      } else {
        // Fallback: sử dụng dataset.aspect (width/height) nếu có
        const dsAspect = box.watermarkContainer.dataset && box.watermarkContainer.dataset.aspect;
        const aspectWH = dsAspect ? parseFloat(dsAspect) : NaN; // width/height
        if (aspectWH && isFinite(aspectWH) && aspectWH > 0) {
          const hwRatio = 1 / aspectWH; // height/width
          const watermarkWidthPixels = (widthPercent / 100) * boxRect.width;
          const watermarkHeightPixels = watermarkWidthPixels * hwRatio;
          heightPercent = (watermarkHeightPixels / boxRect.height) * 100;
        } else if (typeof watermarkData.height === 'number') {
          // If explicit height percent provided in config
          heightPercent = parseFloat(watermarkData.height);
        }
      }
      
      // Tính toán vị trí dựa trên position
      let leftPercent, topPercent;
      
      
      switch (position) {
        case 'top-left':
          leftPercent = paddingLeftPercent;
          topPercent = paddingTopPercent;
          break;
        case 'top-right':
          leftPercent = 100 - widthPercent - paddingLeftPercent;
          topPercent = paddingTopPercent;
          break;
        case 'bottom-left':
          leftPercent = paddingLeftPercent;
          topPercent = 100 - heightPercent - paddingTopPercent;
          break;
        case 'bottom-right':
          leftPercent = 100 - widthPercent - paddingLeftPercent;
          topPercent = 100 - heightPercent - paddingTopPercent;
          break;
        case 'center':
          leftPercent = 50;
          topPercent = 50;
          break;
        default:
          // Fallback về bottom-right
          leftPercent = 100 - widthPercent - paddingLeftPercent;
          topPercent = 100 - heightPercent - paddingTopPercent;
      }
      
      
      const result = {
        leftPercent: Math.max(0, Math.min(100, leftPercent)),
        topPercent: Math.max(0, Math.min(100, topPercent)),
        widthPercent: widthPercent,
        heightPercent: heightPercent,
        paddingLeftPercent: paddingLeftPercent,
        paddingTopPercent: paddingTopPercent,
        position: position,
        padding: padding,
        boxRect: boxRect,
        watermarkImage: wmImage ? {
          naturalWidth: wmImage.naturalWidth,
          naturalHeight: wmImage.naturalHeight,
          aspectRatio: (wmImage.naturalHeight / wmImage.naturalWidth).toFixed(2)
        } : null
      };
      
      
      return result;
    };
    
    // Cập nhật watermark vào dataManager.process khi user tương tác
    iMagify.prototype.updateWatermarkInDataManager = function(box, index) {
      if (!box.watermarkContainer || box.watermarkContainer.style.display === 'none') {
        return;
      }
      
      // Lấy watermark data từ dataManager
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const watermarkData = processImage?.watermark;
      
      if (!watermarkData) {
        return;
      }
      
      // Sử dụng hàm tính toán chung
      const wmContainer = box.watermarkContainer;
      const position = calculateWatermarkPositionFromDOM(wmContainer, box);
      
      // Cập nhật dataManager.process với vị trí thực tế cho crop box cụ thể
      if (processImage.cropBoxes && processImage.cropBoxes[index]) {
        if (!processImage.cropBoxes[index].wm) {
          processImage.cropBoxes[index].wm = {};
        }
        processImage.cropBoxes[index].wm.topPercent = position.topPercent;
        processImage.cropBoxes[index].wm.leftPercent = position.leftPercent;
        processImage.cropBoxes[index].wm.widthPercent = position.widthPercent;
        processImage.cropBoxes[index].wm.visible = true;
      }
      
      // Cũng cập nhật watermark chung để tương thích
      processImage.watermark.topPercent = position.topPercent;
      processImage.watermark.leftPercent = position.leftPercent;
      processImage.watermark.width = position.widthPercent;
    };
    
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
        // Ngăn watermark drag events lan ra crop box
        wmContainer.addEventListener('mousedown', (e) => {
          e.stopPropagation();
        });

        // Thêm resize functionality với percentage positioning
        makeWatermarkResizable(
          wmContainer,
          box,
          aspect,
          box,
          () => {
            // Khi resize watermark: cập nhật dataManager.process ngay lập tức
            this.updateWatermarkInDataManager(box, index);
            // Lưu state sau khi resize watermark
            try { this.saveCropState(this.currentImageIndex); } catch(_) {}
            // Không gọi constrainWatermark để tránh nhảy vị trí
            // this.constrainWatermark();
            // Cập nhật dropdown realtime
            if (this.updateWatermarkDropdown) {
              this.updateWatermarkDropdown();
            }
          }
        );
        
        // Thêm drag functionality với percentage positioning
        makeWatermarkDraggable(
          wmContainer,
          box,
          box,
          () => {
            // Khi drag watermark: cập nhật dataManager.process ngay lập tức
            this.updateWatermarkInDataManager(box, index);
            
            // Lưu state sau khi kéo watermark
            try { 
              this.saveCropState(this.currentImageIndex); 
            } catch(e) {
              console.error('Error saving crop state:', e);
            }
            
            // Không gọi constrainWatermark để tránh nhảy vị trí
            // this.constrainWatermark();
            
            // Cập nhật dropdown realtime
            if (this.updateWatermarkDropdown) {
              this.updateWatermarkDropdown();
            }
          }
        );

        // Điều chỉnh kích thước watermark ban đầu (chỉ khi chưa có state)
        const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
        const hasStateForBox = processImage?.watermark?.topPercent !== undefined;
        if (!hasStateForBox) {
          this.adjustWatermarkSize(box);
        }
      };
    }

    function updateWatermarkPosition() {
      this.cropBoxes.forEach((box, index) => {
        const wm = box.watermarkContainer;
        if (wm && wm.style.display !== "none") {
          // Lấy watermark data từ dataManager
          const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
          const watermarkData = processImage?.watermark;
          
          if (watermarkData && watermarkData.topPercent !== undefined) {
            // Sử dụng hàm áp dụng position chung
            const position = {
              leftPercent: watermarkData.leftPercent,
              topPercent: watermarkData.topPercent,
              widthPercent: watermarkData.width
            };
            applyWatermarkPositionToDOM(wm, position, box);
          }
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
      
      if (!this.mainImage) {
        return;
      }
      
      const imageRect = this.mainImage.getBoundingClientRect();
      
      // Lấy init cropBoxes để preserve width/height gốc
      const initCropBoxes = this.dataManager.init.cropBoxes || [];
      
      const states = this.cropBoxes.map((box, index) => {
        const rect = box.getBoundingClientRect();
        const leftPct = (rect.left - imageRect.left) / imageRect.width;
        const topPct = (rect.top - imageRect.top) / imageRect.height;
        const widthPct = rect.width / imageRect.width;
        const heightPct = rect.height / imageRect.height;
        
        let wm = null;
        if (box.watermarkContainer) {
          wm = saveWatermarkState.call(this, box, rect);
        }
        
        // Parse width/height từ dataset.ratio để preserve original dimensions
        const ratio = box.dataset.ratio || '';
        const [width, height] = ratio.split('x').map(v => parseInt(v, 10));
        const name = box.dataset.name || initCropBoxes[index]?.name || `Layer ${index + 1}`;
        
        // Preserve original config từ init
        const initBox = initCropBoxes[index] || {};
        
        return { 
          leftPct, 
          topPct, 
          widthPct, 
          heightPct, 
          wm,
          // ✅ Preserve original dimensions và config
          width: width || initBox.width,
          height: height || initBox.height,
          name: name,
          id: initBox.id || `crop_${imageIndex}_${index}`,
          size: initBox.size || 'original',
          aspectRatio: initBox.aspectRatio || (width / height),
          quality: initBox.quality || 0.9,
          format: initBox.format || 'jpeg',
          watermark: initBox.watermark,
          isActive: true
        };
      });
      
      // Lưu vào dataManager.process
      const processImage = this.dataManager.getProcessImage(imageIndex);
      if (processImage) {
        processImage.cropBoxes = states;
        console.log('💾 Saved crop state with', states.length, 'boxes:', states.map(s => `${s.width}x${s.height}`).join(', '));
      } else {
        console.log('❌ saveCropState: No processImage found for imageIndex:', imageIndex);
      }
    }

    function saveWatermarkState(box, rect) {
      const wmDisplay = box.watermarkContainer.style.display;
      
      if (wmDisplay !== 'none') {
        // Sử dụng hàm tính toán chung để lấy vị trí hiện tại từ DOM
        const wmContainer = box.watermarkContainer;
        const position = calculateWatermarkPositionFromDOM(wmContainer, box);
        
        // Tính heightPercent dựa trên aspect ratio của watermark image
        const wmImage = wmContainer.querySelector('img');
        let heightPercent = position.widthPercent; // Default fallback
        
        if (wmImage && wmImage.naturalWidth && wmImage.naturalHeight) {
          const boxRect = box.getBoundingClientRect();
          const watermarkWidthPixels = (position.widthPercent / 100) * boxRect.width;
          const watermarkHeightPixels = watermarkWidthPixels * (wmImage.naturalHeight / wmImage.naturalWidth);
          heightPercent = (watermarkHeightPixels / boxRect.height) * 100;
        }
        
        const result = {
          topPercent: position.topPercent,
          leftPercent: position.leftPercent,
          widthPercent: position.widthPercent,
          heightPercent: heightPercent,
          visible: true
        };
        return result;
      } else {
        return { visible: false };
      }
    }

    function applyCropState(imageIndex) {
      // Sử dụng dataManager.process thay vì cropStates
      const processImage = this.dataManager.getProcessImage(imageIndex);
      const states = processImage?.cropBoxes;
      
      // Nếu không có states hoặc không có mainImage, không làm gì cả (tránh vòng lặp)
      if (!states || !this.mainImage) { 
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
      // Đảm bảo watermark được neo đúng cách theo phần trăm
      this.anchorWatermarkToCropBox();
    }

    // Chỉ áp dụng vị trí crop box, KHÔNG reset watermark
    function applyCropBoxPositionOnly(imageIndex) {
      const processImage = this.dataManager.getProcessImage(imageIndex);
      const states = processImage?.cropBoxes;
      
      if (!states || !this.mainImage) { 
        return; 
      }
      
      const imageRect = this.mainImage.getBoundingClientRect();
      const containerRect = this.editorContainer.getBoundingClientRect();
      const imageOffsetLeft = imageRect.left - containerRect.left;
      const imageOffsetTop = imageRect.top - containerRect.top;
      
      this.cropBoxes.forEach((box, i) => {
        const st = states[i];
        if (!st) return;
        // Chỉ áp dụng vị trí crop box, KHÔNG áp dụng watermark
        const boxWidth = st.widthPct * imageRect.width;
        const boxHeight = st.heightPct * imageRect.height;
        const left = imageOffsetLeft + st.leftPct * imageRect.width;
        const top = imageOffsetTop + st.topPct * imageRect.height;
        
        box.style.width = boxWidth + 'px';
        box.style.height = boxHeight + 'px';
        box.style.left = left + 'px';
        box.style.top = top + 'px';
        
      });
      
    }

    // Đặt vị trí watermark ban đầu theo config và tính toán percentage cho từng crop box
    function setInitialWatermarkPosition(box, index) {
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      
      // Đọc watermark từ cropBoxes[index].watermark (riêng cho từng crop box)
      let watermarkData = processImage?.cropBoxes?.[index]?.watermark;
      
      // Fallback: nếu không có config riêng, dùng config chung
      if (!watermarkData) {
        watermarkData = processImage?.watermark;
      }
      
      if (!watermarkData) {
        return;
      }
      
      const wmContainer = box.watermarkContainer;
      const position = watermarkData.position || 'bottom-right';
      const padding = watermarkData.padding || 10;
      const widthPercent = watermarkData.width || 20;
      
      // Reset tất cả positioning properties
      wmContainer.style.right = "auto";
      wmContainer.style.bottom = "auto";
      wmContainer.style.inset = "auto";
      wmContainer.style.transform = "none";
      
      // Lấy crop box dimensions để tính toán percentage
      const boxRect = box.getBoundingClientRect();
      const wmWidth = (widthPercent / 100) * boxRect.width;
      const wmHeight = wmWidth; // Assuming square watermark
      
      let leftPercent, topPercent;
      
      // Tính toán percentage position dựa trên crop box size
      switch (position) {
        case "top-right":
          leftPercent = ((boxRect.width - wmWidth - padding) / boxRect.width) * 100;
          topPercent = (padding / boxRect.height) * 100;
          break;
        case "bottom-right":
          leftPercent = ((boxRect.width - wmWidth - padding) / boxRect.width) * 100;
          topPercent = ((boxRect.height - wmHeight - padding) / boxRect.height) * 100;
          break;
        case "bottom-left":
          leftPercent = (padding / boxRect.width) * 100;
          topPercent = ((boxRect.height - wmHeight - padding) / boxRect.height) * 100;
          break;
        case "center":
          leftPercent = 50;
          topPercent = 50;
          wmContainer.style.transform = "translate(-50%, -50%)";
          break;
        default:
          leftPercent = (padding / boxRect.width) * 100;
          topPercent = (padding / boxRect.height) * 100;
          break;
      }
      
      // Áp dụng position bằng percentage
      wmContainer.style.left = leftPercent + "%";
      wmContainer.style.top = topPercent + "%";
      wmContainer.style.width = widthPercent + "%";
      
      // Lưu position vào cropBoxes[].wm để render đúng
      if (!processImage.cropBoxes) {
        processImage.cropBoxes = [];
      }
      if (!processImage.cropBoxes[index]) {
        processImage.cropBoxes[index] = {};
      }
      
      processImage.cropBoxes[index].wm = {
        leftPercent: leftPercent,
        topPercent: topPercent,
        widthPercent: widthPercent,
        visible: true
      };
    }

    function anchorWatermarkToCropBox() {
      this.cropBoxes.forEach((box, index) => {
        if (box.watermarkContainer && box.watermarkContainer.style.display !== 'none') {
          const wmContainer = box.watermarkContainer;
          
          // Lấy watermark data từ dataManager
          const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
          const watermarkData = processImage?.watermark;
          
          if (watermarkData) {
            // Sử dụng function tính toán chung
            const calculation = this.calculateWatermarkPosition(box, watermarkData);
            
            if (calculation) {
              // Áp dụng vị trí từ calculation
              wmContainer.style.left = calculation.leftPercent + '%';
              wmContainer.style.top = calculation.topPercent + '%';
              wmContainer.style.width = calculation.widthPercent + '%';
              
              // Cập nhật dataManager.process
              processImage.watermark.topPercent = calculation.topPercent;
              processImage.watermark.leftPercent = calculation.leftPercent;
              processImage.watermark.width = calculation.widthPercent;
            }
          }
        }
      });
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
      }
    }

    function applyWatermarkState(box, wmState, boxWidth, boxHeight) {
      // Reset tất cả positioning properties
      box.watermarkContainer.style.left = "auto";
      box.watermarkContainer.style.top = "auto";
      box.watermarkContainer.style.right = "auto";
      box.watermarkContainer.style.bottom = "auto";
      box.watermarkContainer.style.transform = "none";
      
      // Lấy watermark data từ dataManager để có đầy đủ thông tin
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const watermarkData = processImage?.watermark;
      
      if (watermarkData) {
        // Sử dụng function tính toán chung với saved state
        const modifiedWatermarkData = {
          ...watermarkData,
          width: wmState.widthPercent || watermarkData.width,
          position: watermarkData.position || 'bottom-right',
          padding: watermarkData.padding || 10
        };
        
        const calculation = this.calculateWatermarkPosition(box, modifiedWatermarkData);
        
        if (calculation) {
          // Áp dụng vị trí từ calculation
          box.watermarkContainer.style.left = calculation.leftPercent + '%';
          box.watermarkContainer.style.top = calculation.topPercent + '%';
          box.watermarkContainer.style.width = calculation.widthPercent + '%';
          box.watermarkContainer.style.height = '';
          
          // Xử lý center position với transform
          if (calculation.position === 'center') {
            box.watermarkContainer.style.transform = "translate(-50%, -50%)";
          } else {
            box.watermarkContainer.style.transform = "none";
          }
        } else {
          // Fallback về saved state trực tiếp
          const wmTopPercent = wmState.topPercent || 0;
          const wmLeftPercent = wmState.leftPercent || 0;
          const wmWidthPercent = wmState.widthPercent || 20;
          
          box.watermarkContainer.style.left = wmLeftPercent + '%';
          box.watermarkContainer.style.top = wmTopPercent + '%';
          box.watermarkContainer.style.width = wmWidthPercent + '%';
          box.watermarkContainer.style.height = '';
        }
      } else {
        // Fallback về saved state trực tiếp
        const wmTopPercent = wmState.topPercent || 0;
        const wmLeftPercent = wmState.leftPercent || 0;
        const wmWidthPercent = wmState.widthPercent || 20;
        
        box.watermarkContainer.style.left = wmLeftPercent + '%';
        box.watermarkContainer.style.top = wmTopPercent + '%';
        box.watermarkContainer.style.width = wmWidthPercent + '%';
        box.watermarkContainer.style.height = '';
      }
          
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
      
      // ✅ Lấy tên từ dataset hoặc processImage
      const box = this.cropBoxes[index];
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const cropBoxName = box?.dataset?.name || processImage?.cropBoxes?.[index]?.name || `Layer ${index + 1}`;
      
      const tabName = document.createElement('div');
      tabName.textContent = cropBoxName; // ✅ Hiển thị tên thay vì "Layer 1"
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
      
      // Chỉ áp dụng saved crop state khi CHƯA có watermark state (lần đầu render)
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (processImage && processImage.cropBoxes) {
        // Kiểm tra xem có watermark state chưa
        const hasWatermarkState = processImage.watermark && 
                                 processImage.watermark.topPercent !== undefined;
        
        if (!hasWatermarkState) {
          // Lần đầu render: áp dụng crop state để crop box có vị trí đúng
          this.applyCropState(this.currentImageIndex);
        } else {
          // Đã có watermark state: chỉ áp dụng crop box position, KHÔNG reset watermark
          this.applyCropBoxPositionOnly(this.currentImageIndex);
        }
      }
      
      updateCropBoxVisibility.call(this, index);
      updateTabStyles.call(this, index);
      updateTabContent.call(this, index);
      scrollToSelectedCropBox.call(this, index);
      
      // Cập nhật toggle button theo layer mới
      this.updateWatermarkToggleButton();
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
        const box = this.cropBoxes[index];
        const ratio = box?.dataset?.ratio || '';
        const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
        
        // ✅ Lấy tên từ dataset hoặc processImage
        const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
        const cropBoxName = box?.dataset?.name || processImage?.cropBoxes?.[index]?.name || `Layer ${index + 1}`;
        
        tabContentArea.innerHTML = `
          <div style="text-align: center; margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: bold; color: ${boxColor.color}; margin-bottom: 4px;">
              ${cropBoxName}
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
    function clearAllButtons() {
      // Xóa tất cả buttons trong buttonArea
      if (this.buttonArea) {
        this.buttonArea.innerHTML = '';
      }
      
      // Reset button references
      this.saveBtn = null;
    }

    function createSaveButton() {
      // Xóa tất cả buttons cũ
      clearAllButtons.call(this);

      this.saveBtn = createSaveButtonElement.call(this);
      addSaveButtonEvents.call(this);
      
      // Thêm các nút khác cho single file
      // Kiểm tra số lượng ảnh từ dataManager.process
      const totalImages = this.dataManager.process.length;
      if (totalImages === 1) {
        addSingleFileButtons.call(this, this.buttonArea);
      }

      this.buttonArea.appendChild(this.saveBtn);
    }

    function createSaveButtonElement() {
      const saveBtn = document.createElement("button");
      saveBtn.className = "iMagify-saveBtn btn btn-primary";
      saveBtn.innerText = "Xử lý";
      Object.assign(saveBtn.style, {
        padding: "8px 14px",
        fontSize: "13px",
        color: iMagifyTheme.button.primary.text,
        border: `1px solid ${iMagifyTheme.button.primary.border}`,
        borderRadius: "8px",
        cursor: "pointer",
        background: iMagifyTheme.button.primary.bg,
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

    function addSingleFileButtons(buttonArea) {
      const clearBtn = createClearButton.call(this);
      const replaceBtn = createReplaceButton.call(this);
      
      buttonArea.appendChild(clearBtn);
      buttonArea.appendChild(replaceBtn);
    }

    function createClearButton() {
      const clearBtn = document.createElement("button");
      clearBtn.className = "iMagify-clearBtn btn btn-danger";
      clearBtn.innerText = "Xóa";
      Object.assign(clearBtn.style, {
        padding: "8px 12px",
        fontSize: "12px",
        color: iMagifyTheme.button.danger.text,
        border: `1px solid ${iMagifyTheme.button.danger.border}`,
        borderRadius: "8px",
        cursor: "pointer",
        boxShadow: "0 2px 6px rgba(153,27,27,0.35)",
        transition: "all 0.3s ease",
        background: iMagifyTheme.button.danger.bg
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
            const processImage = this.dataManager.getProcessImage(0);
            const currentImage = processImage || { id: 'unknown' };
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
        color: iMagifyTheme.button.warning.text,
        border: `1px solid ${iMagifyTheme.button.warning.border}`,
        borderRadius: "8px",
        cursor: "pointer",
        boxShadow: "0 2px 6px rgba(217,119,6,0.35)",
        transition: "all 0.3s ease",
        background: iMagifyTheme.button.warning.bg
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
        }, { once: true });

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
      const processImage = this.dataManager.getProcessImage(0);
      
      // Cập nhật image data trong dataManager.process
      if (processImage) {
        processImage.image = dataUrl;
        processImage.file = file;
        processImage.name = file.name;
      }
      
      // Cập nhật image data trong dataManager
      if (this.dataManager.process[this.currentImageIndex]) {
        this.dataManager.process[this.currentImageIndex].image = dataUrl;
        this.dataManager.process[this.currentImageIndex].file = file;
        this.dataManager.process[this.currentImageIndex].name = file.name;
      }
      
      // Cập nhật UI một cách có tổ chức
      refreshImageUI.call(this, dataUrl);
    }

    function refreshImageUI(dataUrl) {
      // 1. Cập nhật thumbnail trước
      updateThumbnail.call(this, dataUrl);
      
      // 2. Cập nhật main image
      this.mainImage.src = dataUrl;
      
      // 3. Khi main image load xong, refresh toàn bộ UI
      this.mainImage.onload = () => {
        // Auto add original size crop box cho ảnh replaced
        autoAddOriginalSizeCropBox.call(this);
        
        refreshEditorUI.call(this);
      };
    }

    function updateThumbnail(dataUrl) {
      const thumbnails = document.querySelectorAll(".iMagify-thumbnail");
      if (thumbnails[this.currentImageIndex]) {
        thumbnails[this.currentImageIndex].src = dataUrl;
      }
    }

    function refreshEditorUI() {
      // Reset crop states cho ảnh mới trong dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (processImage) {
        processImage.cropBoxes = null;
      }
      
      // Cập nhật crop boxes
      this.updateCropBoxes();
      
      // Tạo lại save button
      this.createSaveButton();
      
      // Build lại sidebar
      this.buildSidebar();
    }

    // Image Processing Functions
    function prepareFinalDataForProcessing() {
      // Chuẩn bị final data để render
      const finalData = this.dataManager.prepareFinalData();
      
      if (finalData.length === 0) {
        return null;
      }
      
      return finalData;
    }
    
    function processImageData(imageData, index) {
      
      // Xử lý crop boxes
      if (imageData.cropBoxes && imageData.cropBoxes.length > 0) {
        imageData.cropBoxes.forEach((cropBox, cropIndex) => {
          this.processCropBox(imageData, cropBox, cropIndex);
        });
      }
    }
    
    function processCropBox(imageData, cropBox, cropIndex) {
      
      // Xử lý crop data nếu có
      if (cropBox.cropData) {
        // Thực hiện crop logic ở đây
      }
      
      // Xử lý watermark nếu có
      if (imageData.watermark && imageData.watermark.isVisible) {
        // Thực hiện watermark logic ở đây
      }
    }

    async function processCurrentUpload() {
      let thumbnails = document.querySelectorAll(".iMagify-thumbnail");
      
      try {
        let localData = await this.buildUploadResult();

        hideCurrentThumbnail.call(this, thumbnails);
        saveProcessedResult.call(this, localData);
        
        // ✅ Trigger uploadCallback NGAY (không await) để canvas processing tiếp tục
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
        // ✅ Gọi callback ngay, KHÔNG await (fire and forget)
        // Client tự handle submit saves song song
        console.log('🚀 Trigger onUpload callback cho ảnh', this.currentImageIndex + 1, ':', localData.filename);
        this.uploadCallback(localData);
      }
    }

    function hasMoreImages() {
      // ✅ Tìm ảnh CHƯA UPLOAD tiếp theo (bất kỳ index nào)
      return this.uploaded.some((uploaded, index) => !uploaded);
    }

    function moveToNextImage() {
      // ✅ Tìm index của ảnh CHƯA UPLOAD tiếp theo
      const nextIndex = this.uploaded.findIndex((uploaded, index) => !uploaded);
      
      if (nextIndex === -1) {
        // Không còn ảnh nào chưa upload
        handleAllImagesProcessed.call(this);
        return;
      }
      
      console.log('🔄 Chuyển sang ảnh chưa xử lý:', nextIndex + 1, '/', this.dataManager.process.length);
      
      this.currentImageIndex = nextIndex;
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      this.mainImage.src = processImage?.image || processImage?.src || '';
      
      highlightThumbnail.call(this, this.currentImageIndex);
      this.createSaveButton();
      this.isProcessing = false;
      
      if (this.saveBtn) {
        this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Đang sẵn sàng xử lý ảnh tiếp theo...`;
      }
    }

    function handleAllImagesProcessed() {
      // ✅ GỌI callback nhưng KHÔNG tự động đóng modal
      // Để client code quyết định có đóng hay không (dựa vào save results)
        if (typeof this.completeCallback === 'function') {
        console.log('✅ Tất cả ảnh đã được xử lý, gọi onComplete callback');
          this.completeCallback(this.results);
        }
        this.isProcessing = false;
        
        if (this.saveBtn) {
          this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Hoàn tất xử lý.`;
        }
        
      // ✅ KHÔNG tự động đóng modal nữa - để client code quyết định
      // setTimeout(() => { 
      //   document.body.removeChild(this.modal); 
      // }, 800);
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
    
    // Hàm tính toán chung cho watermark positioning
    function calculateWatermarkPositionFromDOM(wmContainer, cropBox) {
      const boxRect = cropBox.getBoundingClientRect();
      const wmRect = wmContainer.getBoundingClientRect();
      
      // Tính toán percentage so với crop box
      const leftPercent = ((wmRect.left - boxRect.left) / boxRect.width) * 100;
      const topPercent = ((wmRect.top - boxRect.top) / boxRect.height) * 100;
      const widthPercent = (wmRect.width / boxRect.width) * 100;
      
      const result = {
        leftPercent: Math.max(0, Math.min(100, leftPercent)),
        topPercent: Math.max(0, Math.min(100, topPercent)),
        widthPercent: Math.max(5, Math.min(95, widthPercent))
      };
      
      return result;
    }
    
    // Hàm áp dụng position từ percentage vào DOM
    function applyWatermarkPositionToDOM(wmContainer, position, cropBox) {
      const boxRect = cropBox.getBoundingClientRect();
      
      // Force reset positioning properties để tránh conflict với inset
      wmContainer.style.right = "auto";
      wmContainer.style.bottom = "auto";
      wmContainer.style.inset = "auto";
      wmContainer.style.transform = "none";
      
      // Set position từ percentage
      wmContainer.style.left = position.leftPercent + "%";
      wmContainer.style.top = position.topPercent + "%";
      wmContainer.style.width = position.widthPercent + "%";
    }
    
    function makeWatermarkDraggable(el, container, boundEl, onDragEnd) {
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
        
        // Set position using pixel temporarily for smooth dragging
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

    function makeWatermarkResizable(el, container, aspectRatio, boundEl, onResizeEnd) {
      const resizer = createResizerElement.call(this);
      el.appendChild(resizer);
      
      let originalWidth = 0, originalHeight = 0, originalMouseX = 0, originalMouseY = 0;
      
      resizer.addEventListener("mousedown", (e) => {
        e.stopPropagation();
        e.preventDefault();
        
        originalWidth = el.offsetWidth;
        originalHeight = el.offsetHeight;
        originalMouseX = e.clientX;
        originalMouseY = e.clientY;
        
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
          
          // Convert pixel to percentage relative to container
          const containerRect = container.getBoundingClientRect();
          const widthPercent = (newWidth / containerRect.width) * 100;
          
          // Set size using percentage
          el.style.width = widthPercent + "%";
          el.style.height = "auto"; // Let aspect-ratio handle height
        }

        function mouseUpResize() {
          document.removeEventListener("mousemove", mouseMoveResize);
          document.removeEventListener("mouseup", mouseUpResize);
          if (onResizeEnd) onResizeEnd();
        }
        
        document.addEventListener("mousemove", mouseMoveResize);
        document.addEventListener("mouseup", mouseUpResize);
      });
    }

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

        // Không tự động adjust watermark size khi resize crop box
        // Vì watermark size đã được lưu trong saved state và sẽ được áp dụng lại
        // thông qua applyWatermarkState()
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
        #iMagify-modal {
          display: grid !important;
          grid-template-columns: 200px 1fr;
          grid-template-rows: 80px 1fr;
          grid-template-areas: 
            "thumbnails thumbnails"
            "sidebar content";
        }
        
        /* Content Container Grid */
        #iMagify-content {
          display: grid !important;
          grid-template-rows: 50px 1fr 60px;
          grid-template-areas: 
            "topbar"
            "editor"
            "buttons";
        }
        
        /* Editor Container */
        #iMagify-editor {
          overflow: auto !important;
          display: flex !important;
          justify-content: center !important;
          align-items: flex-start !important;
          box-sizing: border-box !important;
          max-height: 100% !important;
        }
        
        /* Main Image */
        #iMagify-mainImage {
          max-width: calc(100% - 40px) !important;
          max-height: calc(100vh - 200px) !important;
          width: auto !important;
          height: auto !important;
          object-fit: contain !important;
          display: block !important;
          margin: auto !important;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
          #iMagify-modal {
            grid-template-columns: 1fr;
            grid-template-rows: 60px auto 1fr;
            grid-template-areas: 
              "thumbnails"
              "sidebar"
              "content";
          }
          
          #iMagify-content {
            grid-template-rows: 50px 1fr 60px;
            grid-template-areas: 
              "topbar"
              "editor"
              "buttons";
          }
          
          #iMagify-sidebar {
            overflow-y: auto;
          }
        }
        
        @media (max-width: 480px) {
          #iMagify-modal {
            grid-template-rows: 50px auto 1fr;
          }
          
          #iMagify-content {
            grid-template-rows: 45px 1fr 50px;
          }
          
          #iMagify-topbar {
            padding: 0 8px;
          }
          
          #iMagify-editor {
            padding: 10px !important;
          }
          
          #iMagify-mainImage {
            max-width: calc(100% - 20px) !important;
            max-height: calc(100vh - 150px) !important;
          }
        }

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

  // --- Auto Add Original Size Feature ---
  function autoAddOriginalSizeCropBox() {
    if (!this.autoAddOriginalSize) {
      console.log('⏭️ Auto add original size is disabled');
      return;
    }
    
    if (!this.mainImage || !this.mainImage.naturalWidth) {
      console.log('⚠️ Main image not ready for auto add original size');
      return;
    }
    
    const naturalWidth = this.mainImage.naturalWidth;
    const naturalHeight = this.mainImage.naturalHeight;
    
    console.log(`📸 Auto adding original size: ${naturalWidth}x${naturalHeight}`);
    
    // Kiểm tra xem đã có size này chưa
    const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
    if (!processImage || !processImage.cropBoxes) {
      console.log('⚠️ No process image or crop boxes found');
      return;
    }
    
    const hasOriginalSize = processImage.cropBoxes.some(box => 
      box.width === naturalWidth && box.height === naturalHeight
    );
    
    if (hasOriginalSize) {
      console.log('✅ Original size already exists, skipping');
      return;
    }
    
    // Thêm original size vào cuối danh sách
    const originalSizeConfig = {
      id: `crop_${this.currentImageIndex}_original`,
      name: 'original', // ✅ Tên cho ảnh gốc
      size: 'original',
      width: naturalWidth,
      height: naturalHeight,
      aspectRatio: naturalWidth / naturalHeight,
      quality: 0.95,
      format: 'jpeg',
      watermark: processImage.watermark, // Inherit watermark config
      isActive: true,
      cropData: null,
      cropStates: {}
    };
    
    // Thêm vào cuối danh sách (last position)
    processImage.cropBoxes.push(originalSizeConfig);
    
    console.log('✅ Added original size crop box at end:', originalSizeConfig);
    console.log('📊 Total crop boxes:', processImage.cropBoxes.length);
    
    // Recreate crop boxes DOM
    this.recreateCropBoxes();
  }

  // --- Data Manager Class ---
  class iMagifyDataManager {
    constructor(options) {
      this.init = {};      // Object chứa các thông số mặc định
      this.process = [];   // Array đang xử lý, thay đổi theo user interaction
      this.final = [];     // Array cuối cùng để render
      
      this.initializeFromOptions(options);
    }
    
    initializeFromOptions(options) {
      // Khởi tạo init object từ options (chỉ chứa các thông số cấu hình chung)
      this.init = {
        cropBoxes: this.parseCropBoxConfig(options, 0),
        output: options.output || { jpg: { name: 'jpg', q: 95 }, webp: { q: 95 }, png: {} },
        preserveColor: options.preserveColor || false,
        forcePNG: options.forcePNG || false,
        original: options.original || true,
        server: options.server || '',
        debugMode: options.debugMode || false
      };
      
      // Khởi tạo process array từ options.images
      this.process = options.images.map((image, index) => ({
        id: `image_${index}`,
        image: image.src || image,
        file: image.file || null,
        name: image.name || `image_${index + 1}`,
        watermark: this.parseWatermarkConfig(options, index),
        cropBoxes: this.parseCropBoxConfig(options, index),
        metadata: {
          originalWidth: null,
          originalHeight: null,
          format: null,
          size: null
        }
      }));
      
    }
    
    // Function tính toán watermark dimensions và position (có thể tái sử dụng)
    calculateWatermarkDimensions(watermarkConfig, cropBoxWidth, cropBoxHeight, watermarkAspectRatio = 1) {
      const watermark = typeof watermarkConfig === 'string' 
        ? { src: watermarkConfig } 
        : watermarkConfig;
      
      if (!watermark) return null;
      
      const padding = watermark.padding || 10;
      const position = watermark.position || 'bottom-right';
      
      // Lấy kích thước watermark (phần trăm của crop box)
      const watermarkWidthPercent = parseFloat(watermark.width || 20);
      
      // Tính toán chiều cao watermark dựa trên tỷ lệ khung hình thực tế của ảnh watermark
      let watermarkHeightPercent;
      if (watermark.height) {
        watermarkHeightPercent = parseFloat(watermark.height);
      } else {
        // Tính chiều cao dựa trên tỷ lệ khung hình của ảnh watermark
        const watermarkWidthInPx = (watermarkWidthPercent / 100) * cropBoxWidth;
        const watermarkHeightInPx = watermarkWidthInPx * watermarkAspectRatio;
        watermarkHeightPercent = (watermarkHeightInPx / cropBoxHeight) * 100;
      }
      
      // Chuyển đổi padding từ pixel sang phần trăm dựa trên crop box size
      const paddingPx = watermark.padding || 10;
      const paddingXPercent = (paddingPx / cropBoxWidth) * 100;
      const paddingYPercent = (paddingPx / cropBoxHeight) * 100;
      
      // Tính toán tọa độ topPercent và leftPercent dựa trên position, kích thước và padding
      let topPercent, leftPercent;
      
      switch (position) {
        case "top-right":
          topPercent = paddingYPercent; // Từ top edge
          leftPercent = 100 - watermarkWidthPercent - paddingXPercent; // Từ right edge, trừ kích thước và padding
          break;
        case "bottom-right":
          topPercent = 100 - watermarkHeightPercent - paddingYPercent; // Từ bottom edge, trừ kích thước và padding
          leftPercent = 100 - watermarkWidthPercent - paddingXPercent; // Từ right edge, trừ kích thước và padding
          break;
        case "bottom-left":
          topPercent = 100 - watermarkHeightPercent - paddingYPercent; // Từ bottom edge, trừ kích thước và padding
          leftPercent = paddingXPercent; // Từ left edge
          break;
        case "center":
          topPercent = 50 - (watermarkHeightPercent / 2); // Center theo chiều dọc
          leftPercent = 50 - (watermarkWidthPercent / 2); // Center theo chiều ngang
          break;
        default: // top-left
          topPercent = paddingYPercent; // Từ top edge
          leftPercent = paddingXPercent; // Từ left edge
          break;
      }
      
      return {
        src: watermark.src,
        position: position,
        width: watermarkWidthPercent, // Phần trăm của crop box width
        height: watermarkHeightPercent, // Phần trăm của crop box height (đã tính toán)
        opacity: watermark.opacity || 1,
        // Chỉ lưu 2 tọa độ chính
        topPercent: topPercent,
        leftPercent: leftPercent,
        isVisible: true
      };
    }

    parseWatermarkConfig(options, imageIndex) {
      // Chỉ lấy watermark từ sizes, không cần global watermark nữa
      const sizeWatermark = options.sizes && options.sizes.some(size => size.watermark) 
        ? options.sizes.find(size => size.watermark)?.watermark 
        : null;
      
      const watermarkConfig = sizeWatermark;
      
      if (!watermarkConfig) return null;
      
      // Lấy crop box size từ options.sizes nếu có
      const cropBoxSize = options.sizes && options.sizes[imageIndex] ? options.sizes[imageIndex] : null;
      const cropBoxWidth = cropBoxSize ? parseFloat(cropBoxSize.width || 200) : 200;
      const cropBoxHeight = cropBoxSize ? parseFloat(cropBoxSize.height || 400) : 400;
      
      // Sử dụng function tính toán chung
      return this.calculateWatermarkDimensions(watermarkConfig, cropBoxWidth, cropBoxHeight);
    }
    
    parseCropBoxConfig(options, imageIndex) {
      if (!options.sizes) return [];
      
      return options.sizes.map((size, index) => {
        // Parse watermark config cho từng crop box
        let watermarkConfig = null;
        if (size.watermark) {
          // Tạo options tạm thời để parse watermark cho crop box này
          const tempOptions = {
            ...options,
            sizes: [size] // Chỉ lấy size hiện tại
          };
          watermarkConfig = this.parseWatermarkConfig(tempOptions, 0);
        }
        
        return {
          id: `crop_${imageIndex}_${index}`,
          name: size.name || `Layer ${index + 1}`, // ✅ Thêm name từ config
          size: size.size || 'original',
          width: size.width || null,
          height: size.height || null,
          aspectRatio: size.aspectRatio || null,
          quality: size.quality || 0.9,
          format: size.format || 'jpeg',
          watermark: watermarkConfig,
          isActive: true,
          cropData: null,
          cropStates: {} // Lưu trạng thái crop của từng size
        };
      });
    }
    
    // Thêm ảnh mới vào process array
    addImage(imageFile, imageSrc) {
      const newIndex = this.process.length;
      
      // Lấy crop box config từ init để tính toán watermark
      const cropBoxConfig = this.init?.cropBoxes || [];
      
      const newImage = {
        id: `image_${newIndex}`,
        image: imageSrc,
        file: imageFile,
        name: imageFile.name,
        watermark: this.parseWatermarkConfig({ 
          sizes: cropBoxConfig.map(cb => ({
            width: cb.width,
            height: cb.height,
            watermark: cb.watermark
          }))
        }, newIndex),
        cropBoxes: this.parseCropBoxConfig({ sizes: cropBoxConfig }, newIndex),
        metadata: {
          originalWidth: null,
          originalHeight: null,
          format: null,
          size: null
        }
      };
      
      this.process.push(newImage);
      
      return newIndex;
    }
    
    // Cập nhật crop data cho một ảnh
    updateCropData(imageIndex, cropIndex, cropData) {
      if (this.process[imageIndex] && this.process[imageIndex].cropBoxes[cropIndex]) {
        this.process[imageIndex].cropBoxes[cropIndex].cropData = cropData;
        this.process[imageIndex].cropBoxes[cropIndex].cropStates[`crop_${cropIndex}`] = cropData;
        
        ('[iMagifyDataManager] Updated crop data:', {
          imageIndex,
          cropIndex,
          cropData,
          updatedProcess: this.process[imageIndex]
        });
      }
    }
    
    // Cập nhật watermark visibility
    updateWatermarkVisibility(imageIndex, isVisible) {
      if (this.process[imageIndex] && this.process[imageIndex].watermark) {
        this.process[imageIndex].watermark.isVisible = isVisible;
      }
    }
    
    // Cập nhật metadata của ảnh
    updateImageMetadata(imageIndex, metadata) {
      if (this.process[imageIndex]) {
        Object.assign(this.process[imageIndex].metadata, metadata);
      }
    }
    
    // Lấy dữ liệu process của một ảnh
    getProcessImage(index) {
      return this.process[index] || null;
    }
    
    // Lấy tất cả dữ liệu process
    getAllProcessImages() {
      return this.process;
    }
    
    // Chuẩn bị final array để render
    prepareFinalData() {
      this.final = this.process.map(image => ({
        id: image.id,
        image: image.image,
        name: image.name,
        watermark: image.watermark,
        cropBoxes: image.cropBoxes.filter(box => box.isActive),
        metadata: image.metadata
      }));
      
      return this.final;
    }
    
    // Export dữ liệu để xử lý
    exportData() {
      const exportData = {
        init: this.init,
        process: this.process,
        final: this.final
      };
      
      return exportData;
    }
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
      createContentContainer.call(this);
      createTopbar.call(this);
      createEditorContainer.call(this);
      createSidebar.call(this);
      createButtonArea.call(this);
      createMainImage.call(this);

      // When main image loads, update crop boxes
      this.mainImage.onload = () => {
        this.ensureImageDisplay();
        
        const imgRect = this.mainImage.getBoundingClientRect();
        if (imgRect.width === 0 || imgRect.height === 0) {
          setTimeout(() => {
            // Auto add original size crop box
            autoAddOriginalSizeCropBox.call(this);
            
            this.updateCropBoxes();
            this.createSaveButton();
            this.buildSidebar();
          }, 100);
        } else {
          // Auto add original size crop box
          autoAddOriginalSizeCropBox.call(this);
          
          this.updateCropBoxes();
          this.createSaveButton();
          this.buildSidebar();
        }
      };

      // Create thumbnails and load main image
      createThumbnails.call(this);

      // Ensure image displays correctly
      this.ensureImageDisplay = () => {
        try {
          Object.assign(this.mainImage.style, {
            maxWidth: "calc(100% - 40px)",
            maxHeight: "calc(100vh - 200px)",
            width: "auto",
            height: "auto",
            position: "relative",
            filter: "none",
            opacity: "1",
            mixBlendMode: "normal",
            display: "block",
            visibility: "visible",
            objectFit: "contain",
            margin: "auto"
          });
        } catch (error) {
          // Silent fail
        }
      };
   
      // Create crop boxes based on sizes
      createCropBoxes.call(this);    
      
      // Log dataManager sau khi tạo init xong
      console.log('📊 dataManager after init:', {
        init: this.dataManager.init,
        process: this.dataManager.process,
        final: this.dataManager.final
      });
      
      // Update crop boxes to set proper dimensions - sẽ được gọi trong mainImage.onload
  
      // Add watermark toggle tool to topbar
      const watermarkToggle = createWatermarkToggleTool.call(this);
      if (watermarkToggle && this.toolsContainer) {
        this.toolsContainer.appendChild(watermarkToggle);
      }

      // Build sidebar after crop boxes are created từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (processImage?.cropBoxes && processImage.cropBoxes.length > 0) {
          setTimeout(() => {
            this.buildSidebar();
        }, 200);
      }
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

      // Check if we have saved crop states for current image từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const hasSavedState = processImage?.cropBoxes && processImage.cropBoxes.length > 0;

      // TEMPORARY: Always set default dimensions for first load
      // TODO: Fix saved state logic later

      // CropBoxes đã được tạo trong createCropBoxes()

      const containerRect = this.editorContainer.getBoundingClientRect();
      const imageOffsetLeft = imgRect.left - containerRect.left;
      const imageOffsetTop = imgRect.top - containerRect.top;

      this.cropBoxes.forEach((box, index) => {
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

        // Adjust watermark size and position after updating crop box
        const idx = this.cropBoxes.indexOf(box);
        const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
        const hasStateForBox = processImage?.watermark?.topPercent !== undefined;
        
        
        if (box.watermarkContainer) {
          // Kiểm tra xem watermark có đang có vị trí hiện tại trong DOM không
          // Chỉ coi là có position khi có cả left, top và width, VÀ không phải là giá trị mặc định
          const hasCurrentPosition = box.watermarkContainer.style.left && 
                                   box.watermarkContainer.style.top && 
                                   box.watermarkContainer.style.width &&
                                   box.watermarkContainer.style.left !== '0%' &&
                                   box.watermarkContainer.style.top !== '0%';
          
          console.log('🔍 updateCropBoxes watermark check:', {
            boxIndex: idx,
            hasCurrentPosition: hasCurrentPosition,
            currentDOMStyle: {
              left: box.watermarkContainer.style.left,
              top: box.watermarkContainer.style.top,
              width: box.watermarkContainer.style.width
            },
            hasStateForBox: hasStateForBox
          });
          
          if (hasCurrentPosition) {
            // Nếu có vị trí hiện tại trong DOM, ưu tiên sử dụng vị trí DOM
            // Convert vị trí DOM hiện tại thành percentage và áp dụng lại
            const currentPosition = calculateWatermarkPositionFromDOM(box.watermarkContainer, box);
            
            console.log('📍 Using current DOM position:', {
              boxIndex: idx,
              calculatedPosition: currentPosition
            });
            
            applyWatermarkPositionToDOM(box.watermarkContainer, currentPosition, box);
          } else if (hasStateForBox) {
            // Kiểm tra saved state từ cropBoxes[].wm (riêng cho từng crop box)
            const cropBoxState = processImage.cropBoxes?.[idx]?.wm;
            
            if (cropBoxState && cropBoxState.visible !== false) {
              // Sử dụng saved state từ cropBoxes[].wm
              const position = {
                leftPercent: cropBoxState.leftPercent,
                topPercent: cropBoxState.topPercent,
                widthPercent: cropBoxState.widthPercent
              };
              applyWatermarkPositionToDOM(box.watermarkContainer, position, box);
            } else {
              // Fallback: kiểm tra watermark chung
              const watermarkData = processImage.watermark;
              if (watermarkData && watermarkData.leftPercent !== undefined) {
                const position = {
                  leftPercent: watermarkData.leftPercent,
                  topPercent: watermarkData.topPercent,
                  widthPercent: watermarkData.width
                };
                applyWatermarkPositionToDOM(box.watermarkContainer, position, box);
              } else {
                // Không có saved state, đặt vị trí ban đầu theo config
                this.setInitialWatermarkPosition(box, idx);
              }
            }
          } else {
            // Chỉ auto-size nếu chưa có state và chưa có vị trí hiện tại
            
            // Đặt vị trí watermark ban đầu theo config
            this.setInitialWatermarkPosition(box, idx);
          }
        }
      });

      // Initialize watermark for each crop box using dataManager only
      if (!this._watermarkInitialized) {
        this.cropBoxes.forEach((box, index) => {
          if (box.watermarkContainer) {
            // Chỉ sử dụng dataManager.process làm nguồn dữ liệu
            const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
            
            // Đọc watermark từ cropBoxes[index].watermark (riêng cho từng crop box)
            let watermarkData = processImage?.cropBoxes?.[index]?.watermark;
            
            // Fallback: nếu không có config riêng, dùng config chung
            if (!watermarkData) {
              watermarkData = processImage?.watermark;
            }
            
            if (watermarkData) {
              // Đảm bảo watermark container có kích thước trước khi tính position
              const applyInitialPosition = () => {
                // Chỉ set vị trí mặc định nếu chưa có state đã lưu VÀ chưa có vị trí hiện tại
                const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
                const hasState = processImage?.watermark?.topPercent !== undefined;
                const hasCurrentPosition = box.watermarkContainer.style.left && box.watermarkContainer.style.top && box.watermarkContainer.style.width;
                
                if (hasState) {
                  const wm = processImage.watermark;
                  const position = { leftPercent: wm.leftPercent, topPercent: wm.topPercent, widthPercent: wm.width };
                  applyWatermarkPositionToDOM(box.watermarkContainer, position, box);
                } else if (!hasCurrentPosition) {
                  box.watermarkContainer.style.left = "auto";
                  box.watermarkContainer.style.top = "auto";
                  box.watermarkContainer.style.right = "auto";
                  box.watermarkContainer.style.bottom = "auto";
                  box.watermarkContainer.style.transform = "none";
                  const widthPercent = watermarkData.width || 20;
                  box.watermarkContainer.style.width = widthPercent + "%";
                  const calc = this.calculateWatermarkPosition(box, watermarkData);
                  if (calc) {
                    box.watermarkContainer.style.left = calc.leftPercent + "%";
                    box.watermarkContainer.style.top = calc.topPercent + "%";
                    box.watermarkContainer.style.width = calc.widthPercent + "%";
                  }
                }
                
                // Apply opacity to watermark image
                const wmImage = box.watermarkContainer.querySelector('img');
                if (wmImage && watermarkData.opacity !== undefined) {
                  wmImage.style.opacity = watermarkData.opacity;
                }
              };
              // Đợi 1 frame để DOM ổn định, sau đó đảm bảo ảnh watermark đã load
              setTimeout(() => {
                const img = box.watermarkContainer.querySelector('img');
                if (img && (!img.complete || !img.naturalWidth)) {
                  img.addEventListener('load', applyInitialPosition, { once: true });
                } else {
                  applyInitialPosition();
                }
              }, 0);
            }
          }
        });
        this._watermarkInitialized = true;
      }
      
      // Đảm bảo watermark được neo đúng cách theo phần trăm
      this.anchorWatermarkToCropBox();
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

    iMagify.prototype.applyCropBoxPositionOnly = function(imageIndex) {
      // Sử dụng function applyCropBoxPositionOnly() đã được refactor
      applyCropBoxPositionOnly.call(this, imageIndex);
    };

    iMagify.prototype.setInitialWatermarkPosition = function(box, index) {
      // Sử dụng function setInitialWatermarkPosition() đã được refactor
      setInitialWatermarkPosition.call(this, box, index);
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
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      if (!processImage?.watermark) return;
      
      this.cropBoxes.forEach((box, index) => {
        const wm = box.watermarkContainer;
        if (wm && wm.style.display !== "none") {
          // Lấy giá trị phần trăm hiện tại
          let wmLeftPercent = parseFloat(wm.style.left) || 0;
          let wmTopPercent = parseFloat(wm.style.top) || 0;
          let wmWidthPercent = parseFloat(wm.style.width) || 20;
          
          // Clamp trong bounds (0% - 100%)
          if (wmLeftPercent < 0) wmLeftPercent = 0;
          if (wmTopPercent < 0) wmTopPercent = 0;
          if (wmLeftPercent + wmWidthPercent > 100) wmLeftPercent = 100 - wmWidthPercent;
          if (wmTopPercent + wmWidthPercent > 100) wmTopPercent = 100 - wmWidthPercent;
          
          // Chỉ áp dụng lại nếu có thay đổi
          if (wmLeftPercent !== parseFloat(wm.style.left) || 
              wmTopPercent !== parseFloat(wm.style.top)) {
            wm.style.left = wmLeftPercent + '%';
            wm.style.top = wmTopPercent + '%';
          }
        }
      });
    };
  
    // Adjust watermark size - chỉ sử dụng phần trăm và dataManager
    iMagify.prototype.adjustWatermarkSize = function(box) {
      if (!box.watermarkContainer || box.watermarkContainer.style.display === "none") return;
    
      const wmContainer = box.watermarkContainer;
      const wmImage = wmContainer.querySelector('img');
      if (!wmImage) return;
    
     
    
      // Kiểm tra xem có saved state cho watermark không từ dataManager.process
      const boxIndex = this.cropBoxes.indexOf(box);
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const hasSavedState = processImage?.watermark?.topPercent !== undefined;
      
      if (hasSavedState) {
        return;
      }
    
      // Lấy watermark config từ dataManager.process
      const watermarkData = processImage?.watermark;
      
      if (watermarkData) {
        // Sử dụng kích thước từ dataManager (đã là phần trăm)
        wmContainer.style.width = watermarkData.width + '%';
        wmContainer.style.height = '';
      } else {
        // Fallback: 20% mặc định
        wmContainer.style.width = '20%';
        wmContainer.style.height = '';
        
       
      }
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
      // Extract config từ dataManager.init thay vì processImage
      const initConfig = this.dataManager.init;
      const outputs = initConfig?.output || { jpg: { name: 'jpg', q: 95 }, webp: { q: 95 }, png: {} };
      const preserveColor = !!initConfig?.preserveColor;
      const forcePNG = !!initConfig?.forcePNG;
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
      const sizes = []; // ✅ Đổi từ object sang array
      
      // Store original visibility states
      const originalStates = this.cropBoxes.map(box => ({
        visibility: box.style.visibility,
        opacity: box.style.opacity
      }));
      
      // Make all boxes visible for processing và restore watermark position từ saved state
      this.cropBoxes.forEach((box, i) => {
        box.style.visibility = 'visible';
        box.style.opacity = '1';
        
        // Restore watermark position từ saved state trước khi xử lý
        const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
        const cropBoxState = processImage?.cropBoxes?.[i]?.wm;
        
        if (cropBoxState && cropBoxState.visible !== false && box.watermarkContainer) {
          // Restore watermark position từ saved state
          box.watermarkContainer.style.left = cropBoxState.leftPercent + "%";
          box.watermarkContainer.style.top = cropBoxState.topPercent + "%";
          box.watermarkContainer.style.width = cropBoxState.widthPercent + "%";
          box.watermarkContainer.style.right = "auto";
          box.watermarkContainer.style.bottom = "auto";
          box.watermarkContainer.style.inset = "auto";
          box.watermarkContainer.style.transform = "none";
        }
      });
      
      for (let i = 0; i < this.cropBoxes.length; i++) {
        updateCallback(`Đang xử lý crop box ${i+1}/${this.cropBoxes.length}...`);
        await nextFrame();
        
        const box = this.cropBoxes[i];
        const ratioKey = box.dataset.ratio;
        const cropBoxName = box.dataset.name || `Layer ${i + 1}`;
        
        // Parse width và height từ ratio
        const [width, height] = ratioKey.split('x').map(v => parseInt(v, 10));
        
        const cropData = await this.processCropBox(box, i, primaryFmt, primaryQuality, preserveColor, forcePNG);
        
        // ✅ Push vào array với đầy đủ thông tin
        sizes.push({
          width: width,
          height: height,
          name: cropBoxName,
          data: cropData
        });
      }
      
      // Restore original visibility states và watermark position
      this.cropBoxes.forEach((box, i) => {
        box.style.visibility = originalStates[i].visibility;
        box.style.opacity = originalStates[i].opacity;
        
        // Restore watermark position từ saved state sau khi xử lý
        const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
        const cropBoxState = processImage?.cropBoxes?.[i]?.wm;
        
        if (cropBoxState && cropBoxState.visible !== false && box.watermarkContainer) {
          // Restore watermark position từ saved state
          box.watermarkContainer.style.left = cropBoxState.leftPercent + "%";
          box.watermarkContainer.style.top = cropBoxState.topPercent + "%";
          box.watermarkContainer.style.width = cropBoxState.widthPercent + "%";
          box.watermarkContainer.style.right = "auto";
          box.watermarkContainer.style.bottom = "auto";
          box.watermarkContainer.style.inset = "auto";
          box.watermarkContainer.style.transform = "none";
        }
      });
      
      // Tách original size ra khỏi sizes array
      // Tìm size có name = "original"
      const originalSizeIndex = sizes.findIndex(s => s.name === 'original');
      let original = "";
      
      if (originalSizeIndex !== -1) {
        // Lấy data của original size
        original = sizes[originalSizeIndex].data;
        // Remove original size khỏi sizes array
        sizes.splice(originalSizeIndex, 1);
        console.log('✂️ Extracted original size, remaining sizes:', sizes.length);
      } else if (initConfig?.original) {
        // Fallback: Process original image từ baseCanvas nếu không có original size
        updateCallback("Đang xử lý ảnh gốc...");
        await nextFrame();
        
        original = await this.exportCanvas(baseCanvas, primaryFmt, primaryQuality, preserveColor, forcePNG);
      }
      
      // Create result từ dataManager.process
      const currentProcessImage = this.dataManager.getProcessImage(this.currentImageIndex);
      const currentURL = currentProcessImage?.image || currentProcessImage?.src || '';
      
      // ✅ Remove CHỈ extension cuối cùng, giữ nguyên tên có dấu chấm
      // VD: "Screenshot 2025-10-21 at 14.52.47.jpg" → "Screenshot 2025-10-21 at 14.52.47"
      let baseName = '';
      if (currentProcessImage?.name) {
        const name = currentProcessImage.name;
        const lastDotIndex = name.lastIndexOf('.');
        baseName = lastDotIndex > 0 ? name.substring(0, lastDotIndex) : name;
      } else {
        const urlFilename = currentURL.split('/').pop();
        const lastDotIndex = urlFilename.lastIndexOf('.');
        baseName = lastDotIndex > 0 ? urlFilename.substring(0, lastDotIndex) : urlFilename;
      }
      
      const result = {
        filename: baseName,
        original: original,
        sizes: sizes,
        format: primaryExt,
        quality: primaryQuality,
        webp: webpRequested,
        optimize: true
      };
      
      console.log('📦 Final result:', {
        filename: baseName,
        originalSize: (original.length / 1024).toFixed(2) + ' KB',
        sizesCount: sizes.length,
        sizesList: sizes.map(s => `${s.name}(${s.width}x${s.height})`).join(', ')
      });
      
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
      
      // Get watermark config for this specific crop box từ dataManager.process
      const processImage = this.dataManager.getProcessImage(this.currentImageIndex);
      
      // Đọc watermark từ cropBoxes[index].watermark (riêng cho từng crop box)
      let wmConfig = processImage?.cropBoxes?.[index]?.watermark;
      
      // Fallback: nếu không có config riêng, dùng config chung
      if (!wmConfig) {
        wmConfig = processImage?.watermark;
      }
      
      const opacity = wmConfig?.opacity || 1.0;
      
      // Get watermark position từ saved state thay vì DOM
      const boxRect = box.getBoundingClientRect();
      const aspect = parseFloat(box.watermarkContainer.dataset.aspect) || (wmImage.naturalWidth / wmImage.naturalHeight);
      
      // Lấy saved state từ cropBoxes[].wm
      const cropBoxState = processImage?.cropBoxes?.[index]?.wm;
      
      let wmLeft, wmTop, wmWidth;
      
      if (cropBoxState && cropBoxState.visible !== false) {
        // Sử dụng saved state từ cropBoxes[].wm
        wmLeft = (cropBoxState.leftPercent / 100) * boxRect.width;
        wmTop = (cropBoxState.topPercent / 100) * boxRect.height;
        wmWidth = (cropBoxState.widthPercent / 100) * boxRect.width;
      } else {
        // Fallback: đọc từ DOM nếu không có saved state
        const wmRect = box.watermarkContainer.getBoundingClientRect();
        wmLeft = wmRect.left - boxRect.left;
        wmTop = wmRect.top - boxRect.top;
        wmWidth = wmRect.width;
      }
      
      // Scale to canvas coordinates with locked aspect
      const wmX = wmLeft * scaleX;
      const wmY = wmTop * scaleY;
      const wmWidthScaled = wmWidth * scaleX;
      const wmHeightScaled = wmWidthScaled / aspect;
      
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

    // Hàm toggleAllWatermarks: bật/tắt tất cả watermark
    iMagify.prototype.toggleAllWatermarks = function () {
      toggleAllWatermarks.call(this);
    };

    // Hàm areWatermarksVisible: kiểm tra trạng thái watermark
    iMagify.prototype.areWatermarksVisible = function () {
      return areWatermarksVisible.call(this);
    };

    // Hàm clearAllButtons: xóa tất cả buttons
    iMagify.prototype.clearAllButtons = function () {
      clearAllButtons.call(this);
    };

    // Hàm refreshImageUI: refresh UI khi thay đổi ảnh
    iMagify.prototype.refreshImageUI = function (dataUrl) {
      refreshImageUI.call(this, dataUrl);
    };

    // Hàm refreshEditorUI: refresh editor UI
    iMagify.prototype.refreshEditorUI = function () {
      refreshEditorUI.call(this);
    };

    // Hàm updateThumbnail: cập nhật thumbnail
    iMagify.prototype.updateThumbnail = function (dataUrl) {
      updateThumbnail.call(this, dataUrl);
    };

    // Hàm switchToNewUploadedImage: chuyển sang ảnh mới upload
    iMagify.prototype.switchToNewUploadedImage = function (index, dataUrl) {
      switchToNewUploadedImage.call(this, index, dataUrl);
    };

    // DataManager Access Methods
    iMagify.prototype.getDataManager = function () {
      return this.dataManager;
    };

    iMagify.prototype.getInitData = function () {
      return this.dataManager.init;
    };

    iMagify.prototype.getProcessData = function () {
      return this.dataManager.process;
    };

    iMagify.prototype.getFinalData = function () {
      return this.dataManager.final;
    };

    iMagify.prototype.exportAllData = function () {
      return this.dataManager.exportData();
    };

    iMagify.prototype.prepareFinalDataForProcessing = function () {
      return prepareFinalDataForProcessing.call(this);
    };

    // Hàm anchorWatermarkToCropBox: neo watermark theo crop box
    iMagify.prototype.anchorWatermarkToCropBox = function () {
      anchorWatermarkToCropBox.call(this);
    };

    // Recreate all crop boxes từ dataManager
    iMagify.prototype.recreateCropBoxes = function() {
      console.log('🔄 Recreating crop boxes...');
      
      // Xóa tất cả crop boxes hiện tại
      this.cropBoxes.forEach(box => {
        if (box && box.parentNode) {
          box.parentNode.removeChild(box);
        }
      });
      this.cropBoxes = [];
      
      // Tạo lại crop boxes từ dataManager
      createCropBoxes.call(this);
      
      // Update dimensions
      this.updateCropBoxes();
      
      // Rebuild sidebar
      this.buildSidebar();
      
      // Select first box
      if (this.cropBoxes.length > 0) {
        this.switchToTab(0);
      }
      
      console.log('✅ Crop boxes recreated:', this.cropBoxes.length);
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

        // Không tự động adjust watermark size khi resize crop box
        // Vì watermark size đã được lưu trong saved state và sẽ được áp dụng lại
        // thông qua applyWatermarkState()
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
  
    // Thêm function tính toán watermark vào prototype
    iMagify.prototype.calculateWatermarkDimensions = function(watermarkConfig, cropBoxWidth, cropBoxHeight, watermarkAspectRatio = 1) {
      return this.dataManager.calculateWatermarkDimensions(watermarkConfig, cropBoxWidth, cropBoxHeight, watermarkAspectRatio);
    };
  
    window.iMagify = function (options) {
      return new iMagify(options);
    };
  })();