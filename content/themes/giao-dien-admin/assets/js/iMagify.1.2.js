// xoi thư viện chưa thể dùng cho mobile, khi kích thước của sổ thay đổi thì các ô crop box không được cập nhật
(function () {
    function iMagify(options) {
      this.options = options;
      this.currentImageIndex = 0; // chỉ số của ảnh đang chỉnh sửa
      this.cropBoxes = []; // mảng crop box (được tạo từ sizes)
      this._watermarkInitialized = false;
      this._wmResizableApplied = false;
      // Save trạng thái upload cho mỗi ảnh (true: đã upload thành công)
      this.uploaded = new Array(this.options.images.length).fill(false);
          this.results = [];
          this.completeCallback = null; // callback complete
          this.uploadCallback = null; // callback upload
      
      // Cập nhật mảng màu sắc đẹp hơn
      this.cropBoxColors = [
        { color: '#4CAF50', bgColor: '#388E3C' }, // Xanh lá
        { color: '#2196F3', bgColor: '#1976D2' }, // Xanh dương
        { color: '#9C27B0', bgColor: '#7B1FA2' }, // Tím
        { color: '#FF9800', bgColor: '#F57C00' }, // Cam
        { color: '#E91E63', bgColor: '#C2185B' }, // Hồng
        { color: '#00BCD4', bgColor: '#0097A7' }, // Xanh ngọc
        { color: '#FFC107', bgColor: '#FFA000' }, // Vàng
        { color: '#795548', bgColor: '#5D4037' }, // Nâu
        { color: '#607D8B', bgColor: '#455A64' }, // Xám xanh
        { color: '#F44336', bgColor: '#D32F2F' }  // Đỏ
      ];
      console.log('[iMagify] iMagify version 1.2');
      window.iMagifyInstance = this;
      console.log('iMagifyInstance', window.iMagifyInstance);
      console.log('[iMagify] Options:', options);
      this.init();
    }
  
     // Cho phép đăng ký event complete
    iMagify.prototype.onComplete = function (callback) {
      this.completeCallback = callback;
    };
    // Cho phép đăng ký event upload khi tải xong từng files
    iMagify.prototype.onUpload = function (callback) {
      this.uploadCallback = callback;
    };
  
    iMagify.prototype.init = function () {
      console.log('[iMagify] init() bắt đầu');
      // Global error handlers để không bị đứt luồng im lặng
      if (!window.__iMagifyErrorHooked) {
        window.addEventListener('error', function (e) {
          console.error('[iMagify][window.onerror]', e.message, e.filename+':'+e.lineno+':'+e.colno, e.error);
        });
        window.addEventListener('unhandledrejection', function (e) {
          console.error('[iMagify][unhandledrejection]', e.reason);
        });
        window.__iMagifyErrorHooked = true;
      }
      // Tạo dialog full màn hình, nền trắng, có nút đóng X
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
  
      // Nút đóng (X)
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
  
      // Thanh thumbnails (80px)
      this.thumbnailContainer = document.createElement("div");
      this.thumbnailContainer.id = "iMagify-thumbnails";
      Object.assign(this.thumbnailContainer.style, {
        flex: "0 0 80px",
        display: "flex",
        overflowX: "auto",
        padding: "10px",
        backgroundColor: "#222",
        zIndex: "1100"
      });
      this.modal.appendChild(this.thumbnailContainer);
  
      // Editor container
      this.editorContainer = document.createElement("div");
      this.editorContainer.id = "iMagify-editor";
      Object.assign(this.editorContainer.style, {
        flex: "1",
        position: "relative",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        backgroundColor: "#333",
        overflow: "auto"
      });
      this.modal.appendChild(this.editorContainer);
  
      // Image chính
      this.mainImage = document.createElement("img");
      this.mainImage.id = "iMagify-mainImage";
      Object.assign(this.mainImage.style, {
        maxWidth: "90%",
        maxHeight: "90%",
        position: "relative"
      });
      
      // Watermark Container
      this.editorContainer.appendChild(this.mainImage);
  
      // Tạo crop boxes theo sizes
      if(this.options.sizes) {
        console.log('[iMagify] Bắt đầu tạo cropBoxes, count =', this.options.sizes.length);
        this.options.sizes.forEach((size, index) => {
          try {
            console.log('[iMagify] version 1.2', { index, size });
          console.log('[iMagify] Tạo cropBox', { index, size });
            const w = parseFloat(size.width);
            const h = parseFloat(size.height);
            if (!isFinite(w) || !isFinite(h) || w <= 0 || h <= 0) {
              console.warn('[iMagify] Bỏ qua size không hợp lệ', size);
              return;
            }
          var box = document.createElement("div");
          box.className = "iMagify-cropBox";
          box.dataset.ratio = w + "x" + h;
          
          // Lấy màu từ mảng
          const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
          
          // Thêm chú thích cho crop box với kích thước
          var label = document.createElement("div");
          label.className = "iMagify-cropBox-label";
          label.innerText = size.width + "x" + size.height;
          label.style.position = "absolute";
          label.style.top = "-25px";
          label.style.left = "0";
          label.style.color = boxColor.color;
          label.style.fontWeight = "bold";
          label.style.backgroundColor = "rgba(255,255,255,0.9)";
          label.style.padding = "2px 5px";
          label.style.borderRadius = "3px";
          label.style.boxShadow = "0 1px 3px rgba(0,0,0,0.2)";
          box.appendChild(label);
          
          // Tạo watermark container cho mỗi crop box
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
          const wmImage = document.createElement("img");
          const wmOption = this.options.watermark;
          const wmSrc = (typeof wmOption === 'object') ? (wmOption?.src) : wmOption;
          if (wmSrc) {
            wmImage.src = wmSrc;
          }
          Object.assign(wmImage.style, {
            width: "100%",
            height: "100%",
            objectFit: "contain"
          });
          wmContainer.appendChild(wmImage);

          // Thêm nút toggle watermark
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
          box.appendChild(toggleBtn);

          // Ẩn toggle và watermark nếu không có src
          if (!wmSrc) {
            wmContainer.style.display = "none";
            toggleBtn.style.display = "none";
            console.log('[iMagify] Không có watermark, ẩn container/toggle cho box', { boxIndex: index });
          }

          // Thêm watermark container vào box
          box.appendChild(wmContainer);
          box.watermarkContainer = wmContainer;

          // Xử lý sự kiện toggle watermark
          toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            const isVisible = wmContainer.style.display === "block";
            wmContainer.style.display = isVisible ? "none" : "block";
            toggleBtn.style.opacity = isVisible ? "0.5" : "1";
            console.log('[iMagify] Toggle watermark', { boxIndex: index, visible: !isVisible });
          });

          // Cài đặt draggable và resizable cho watermark (nếu có src)
          wmImage.onload = () => {
            const aspect = wmImage.naturalWidth / wmImage.naturalHeight;
            console.log('[iMagify] Watermark loaded', {
              naturalWidth: wmImage.naturalWidth,
              naturalHeight: wmImage.naturalHeight,
              aspect
            });
            
            // Ngăn chặn sự kiện kéo từ watermark lan ra crop box
            wmContainer.addEventListener('mousedown', (e) => {
              e.stopPropagation();
            });

            makeElementResizable(
              wmContainer,
              box,
              aspect,
              box,
              () => { this.constrainWatermark(); }
            );
            
            makeElementDraggable(
              wmContainer,
              box,
              box,
              () => { this.constrainWatermark(); }
            );

            // Điều chỉnh kích thước watermark ban đầu
            this.adjustWatermarkSize(box);
          };

          // Cập nhật vị trí watermark khi crop box di chuyển
          const updateWatermarkPosition = () => {
            if (wmContainer.style.display !== "none") {
              const boxRect = box.getBoundingClientRect();
              const wmRect = wmContainer.getBoundingClientRect();
              
              // Giữ watermark trong phạm vi crop box
              let wmLeft = parseInt(wmContainer.style.left) || 0;
              let wmTop = parseInt(wmContainer.style.top) || 0;
              
              if (wmLeft < 0) wmLeft = 0;
              if (wmTop < 0) wmTop = 0;
              if (wmLeft + wmRect.width > boxRect.width) wmLeft = boxRect.width - wmRect.width;
              if (wmTop + wmRect.height > boxRect.height) wmTop = boxRect.height - wmRect.height;
              
              wmContainer.style.left = wmLeft + "px";
              wmContainer.style.top = wmTop + "px";
            }
          };

          // Thêm sự kiện di chuyển cho crop box
          box.addEventListener("mousemove", updateWatermarkPosition);
          
          Object.assign(box.style, {
            border: "1px dashed " + boxColor.color,
            position: "absolute",
            cursor: "move",
            backgroundColor: boxColor.color + "33",
            zIndex: "700",
            boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
            transition: "box-shadow 0.3s ease"
          });
          
          // Thêm hiệu ứng hover
          box.addEventListener("mouseenter", () => {
            box.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
          });
          box.addEventListener("mouseleave", () => {
            box.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
          });
          
          this.editorContainer.appendChild(box);
          this.cropBoxes.push(box);
            console.log('[iMagify] Đã thêm cropBox vào DOM', { index, ratio: box.dataset.ratio });

          // Cập nhật z-index khi kéo crop box
          const updateZIndex = () => {
            const maxZIndex = Math.max(...this.cropBoxes.map(b => parseInt(b.style.zIndex)));
            box.style.zIndex = (maxZIndex + 1).toString();
          };

          try {
            makeElementDraggable(box, this.editorContainer, this.mainImage, () => { 
              this.constrainWatermark();
              updateWatermarkPosition();
              updateZIndex();
            });
          } catch (e) {
            console.error('[iMagify] Lỗi makeElementDraggable', e);
          }
          try {
            const ratioNumber = (parseFloat(size.width) || 1) / (parseFloat(size.height) || 1);
            makeElementResizable(box, this.editorContainer, ratioNumber, this.mainImage, () => { 
              this.constrainWatermark();
              updateWatermarkPosition();
              updateZIndex();
            });
          } catch (e) {
            console.error('[iMagify] Lỗi makeElementResizable', e);
          }
          } catch (err) {
            console.error('[iMagify] Lỗi khi tạo cropBox index=' + index, err, size);
          }
        });
        console.log('[iMagify] Hoàn tất tạo cropBoxes:', this.cropBoxes.length);
      }    
  
  
      // Tạo thumbnails
      console.log('[iMagify] Bắt đầu tạo thumbnails, count =', (this.options.images||[]).length);
      this.options.images.forEach((imgSrc, index) => {
        var thumb = document.createElement("img");
        thumb.className = "iMagify-thumbnail";
        thumb.src = imgSrc.src ? imgSrc.src : imgSrc;
        Object.assign(thumb.style, {
          width: "60px",
          height: "60px",
          objectFit: "cover",
          marginRight: "10px",
          cursor: "pointer"
        });
        if (index === 0) {
          thumb.style.border = "2px solid #00f";
          this.currentImageIndex = 0;
          this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;;
          console.log('[iMagify] Set ảnh chính ban đầu', this.mainImage.src);
        }
        thumb.addEventListener("click", () => {
          document.querySelectorAll(".iMagify-thumbnail").forEach(el => el.style.border = "none");
          thumb.style.border = "2px solid #00f";
          this.currentImageIndex = index;
          this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;;
          console.log('[iMagify] Chọn thumbnail', { index, src: this.mainImage.src });
          this.mainImage.onload = () => { this.updateCropBoxes(); this.createSaveButton(); };
        });
        this.thumbnailContainer.appendChild(thumb);
      });
      console.log('[iMagify] Hoàn tất tạo thumbnails');
  
      // Khi ảnh chính load, cập nhật crop boxes và đặt watermark nếu chưa được set
      this.mainImage.onload = () => {
        console.log('[iMagify] mainImage onload', { naturalWidth: this.mainImage.naturalWidth, naturalHeight: this.mainImage.naturalHeight });
        const imgRect = this.mainImage.getBoundingClientRect();
        if (imgRect.width === 0 || imgRect.height === 0) {
          setTimeout(() => {
            this.updateCropBoxes();
            this.createSaveButton();
          }, 100);
        } else {
          this.updateCropBoxes();
          this.createSaveButton();
        }
      };
      console.log('[iMagify] init() kết thúc');
    };
  
  
    // Cập nhật crop boxes và đặt watermark mặc định nếu chưa được set
    iMagify.prototype.updateCropBoxes = function () {
      console.log('[iMagify] updateCropBoxes()');
      if (!this.mainImage || !this.editorContainer) return;

      const imgRect = this.mainImage.getBoundingClientRect();
      console.log('[iMagify] mainImage rect', imgRect);
      if (imgRect.width === 0 || imgRect.height === 0) {
        setTimeout(() => {
          this.updateCropBoxes();
          this.createSaveButton();
        }, 100);
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
        console.log('[iMagify] Cập nhật cropBox', {
          ratio: box.dataset.ratio,
          width: boxWidth,
          height: boxHeight,
          left: box.style.left,
          top: box.style.top
        });

        // Điều chỉnh kích thước watermark sau khi cập nhật crop box
        this.adjustWatermarkSize(box);
      });

      // Nếu watermark chưa được khởi tạo và có watermark
      if (!this._watermarkInitialized && this.options.watermark) {
        let pad = this.options.watermark.padding || 10;
        const allowed = this.getAllowedRect();
        if (allowed) {
          let pos = { left: 0, top: 0 };
          switch (this.options.watermark.position) {
            case "top-right":
              pos.left = allowed.right - (this.watermarkContainer?.offsetWidth || 0) - pad;
              pos.top = allowed.top + pad;
              break;
            case "bottom-right":
              pos.left = allowed.right - (this.watermarkContainer?.offsetWidth || 0) - pad;
              pos.top = allowed.bottom - (this.watermarkContainer?.offsetHeight || 0) - pad;
              break;
            case "bottom-left":
              pos.left = allowed.left + pad;
              pos.top = allowed.bottom - (this.watermarkContainer?.offsetHeight || 0) - pad;
              break;
            case "center":
              pos.left = allowed.left + (allowed.right - allowed.left - (this.watermarkContainer?.offsetWidth || 0)) / 2;
              pos.top = allowed.top + (allowed.bottom - allowed.top - (this.watermarkContainer?.offsetHeight || 0)) / 2;
              break;
            default:
              pos.left = allowed.left + pad;
              pos.top = allowed.top + pad;
              break;
          }
          if (this.watermarkContainer) {
            this.watermarkContainer.style.left = pos.left + "px";
            this.watermarkContainer.style.top = pos.top + "px";
            console.log('[iMagify] Đặt watermark mặc định', pos);
          }
        }
        this._watermarkInitialized = true;
      }
    };
  
    // Tính vùng giao của các crop boxes (allowed region)
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
      console.log('[iMagify] allowedRect', allowed);
      return allowed;
    };
  
    // Hàm constrainWatermark: đảm bảo watermarkContainer giữ tỷ lệ ban đầu và không vượt allowed
    iMagify.prototype.constrainWatermark = function () {
      if (!this.options.watermark) return;
      
      this.cropBoxes.forEach(box => {
        if (box.watermarkContainer && box.watermarkContainer.style.display !== "none") {
          const boxRect = box.getBoundingClientRect();
          const wmRect = box.watermarkContainer.getBoundingClientRect();
          
          // Giữ watermark trong phạm vi crop box
          let wmLeft = parseInt(box.watermarkContainer.style.left) || 0;
          let wmTop = parseInt(box.watermarkContainer.style.top) || 0;
          
          if (wmLeft < 0) wmLeft = 0;
          if (wmTop < 0) wmTop = 0;
          if (wmLeft + wmRect.width > boxRect.width) wmLeft = boxRect.width - wmRect.width;
          if (wmTop + wmRect.height > boxRect.height) wmTop = boxRect.height - wmRect.height;
          
          box.watermarkContainer.style.left = wmLeft + "px";
          box.watermarkContainer.style.top = wmTop + "px";
          console.log('[iMagify] constrainWatermark', {
            ratio: box.dataset.ratio,
            left: wmLeft,
            top: wmTop,
            boxWidth: boxRect.width,
            boxHeight: boxRect.height,
            wmWidth: wmRect.width,
            wmHeight: wmRect.height
          });
        }
      });
    };
  
    // Thêm hàm mới để điều chỉnh kích thước watermark
    iMagify.prototype.adjustWatermarkSize = function(box) {
      if (!box.watermarkContainer || box.watermarkContainer.style.display === "none") return;
    
      const boxRect = box.getBoundingClientRect();
      const wmContainer = box.watermarkContainer;
      const wmImage = wmContainer.querySelector('img');
      if (!wmImage) return;
    
      // Tỷ lệ tăng kích thước watermark (20% lớn hơn)
      const scaleFactor = 1;
    
      // Kích thước mới = 20% * scaleFactor của chiều rộng crop box
      const newWidth = boxRect.width * 0.2 * scaleFactor;
      const aspectRatio = wmImage.naturalWidth / wmImage.naturalHeight;
      const newHeight = newWidth / aspectRatio;
    
      // Áp dụng kích thước mới
      wmContainer.style.width  = newWidth + "px";
      wmContainer.style.height = newHeight + "px";
    
      // Đặt luôn xuống góc dưới bên phải, padding 10px
      const padding = 10;
      wmContainer.style.left   = "auto";
      wmContainer.style.top    = "auto";
      wmContainer.style.right  = padding + "px";
      wmContainer.style.bottom = padding + "px";
    
      // Đảm bảo không vượt ngoài crop box
      this.constrainWatermark();
      console.log('[iMagify] adjustWatermarkSize', {
        ratio: box.dataset.ratio,
        width: newWidth,
        height: newHeight
      });
    };
    
  
    // Thêm helper nhường luồng
    function nextFrame() {
      return new Promise(resolve => requestAnimationFrame(resolve));
    }

    // Hàm buildUploadResult: tạo object chứa dữ liệu upload cho ảnh hiện tại
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
      console.log('[iMagify] buildUploadResult()');
    
      // Extract config from options
      const outputs = this.options.output || { jpeg: { name: 'jpeg', q: 90 }, webp: { q: 90 } };
      const webpRequested = !!outputs.webp;
      const primaryFmt = outputs.jpeg ? 'jpeg' : (outputs.png ? 'png' : 'jpeg');
      const primaryQuality = (outputs[primaryFmt] && outputs[primaryFmt].q) ? outputs[primaryFmt].q : 90;
      const primaryExt = (outputs[primaryFmt] && outputs[primaryFmt].name) ? outputs[primaryFmt].name : primaryFmt;
      console.log('[iMagify] output config', { outputs, primaryFmt, primaryQuality, primaryExt, webpRequested });
      
      let currentImage = this.options.images[this.currentImageIndex];
      let currentURL = (typeof currentImage === 'object' && currentImage.src) ? currentImage.src : currentImage;
      let baseName = (typeof currentImage === 'object' && currentImage.name) ? currentImage.name.split('.')[0] : currentURL.split('/').pop().split('.')[0];
      console.log('[iMagify] current image', { index: this.currentImageIndex, currentURL, baseName });
      
      // NEW PAYLOAD FORMAT v2.0
      let result = {
        filename: baseName,
        original: "",
        sizes: {},
        format: primaryExt === 'jpeg' ? 'jpg' : primaryExt,
        quality: primaryQuality,
        webp: webpRequested,
        optimize: true
      };
    
      // Step 2: Create original canvas
      update("Đang tạo canvas gốc...");
      await nextFrame();
      console.log('[iMagify] Tạo base canvas', { width: this.mainImage.naturalWidth, height: this.mainImage.naturalHeight });
    
      let baseCanvas = document.createElement("canvas");
      baseCanvas.width = this.mainImage.naturalWidth;
      baseCanvas.height = this.mainImage.naturalHeight;
      let baseCtx = baseCanvas.getContext("2d");
      baseCtx.drawImage(this.mainImage, 0, 0, baseCanvas.width, baseCanvas.height);
    
      // Step 3: Process each crop box (sizes)
      for (let i = 0; i < this.cropBoxes.length; i++) {
        const box = this.cropBoxes[i];
        update(`Đang xử lý ảnh ${i+1}/${this.cropBoxes.length}...`);
        await nextFrame();
    
        let ratioKey = box.dataset.ratio;
        const boxRect = box.getBoundingClientRect();
        const imageRect = this.mainImage.getBoundingClientRect();
        const scaleX = this.mainImage.naturalWidth / imageRect.width;
        const scaleY = this.mainImage.naturalHeight / imageRect.height;
        let cropX = (boxRect.left - imageRect.left) * scaleX;
        let cropY = (boxRect.top - imageRect.top) * scaleY;
        let cropWidth = boxRect.width * scaleX;
        let cropHeight = boxRect.height * scaleY;
        console.log('[iMagify] Crop params', { index: i, ratioKey, cropX, cropY, cropWidth, cropHeight, scaleX, scaleY });
    
        let cropCanvas = document.createElement("canvas");
        cropCanvas.width = cropWidth;
        cropCanvas.height = cropHeight;
        let cropCtx = cropCanvas.getContext("2d");
        
        cropCtx.drawImage(this.mainImage, cropX, cropY, cropWidth, cropHeight, 0, 0, cropWidth, cropHeight);
    
        // Draw watermark if present
        if (box.watermarkContainer && box.watermarkContainer.style.display !== "none") {
          const wmImage = box.watermarkContainer.querySelector('img');
          if (wmImage) {
            const wmStyle = window.getComputedStyle(box.watermarkContainer);
            const wmLeft = parseFloat(wmStyle.left) || 0;
            const wmTop = parseFloat(wmStyle.top) || 0;
            const wmWidth = parseFloat(wmStyle.width) || wmImage.naturalWidth;
            const wmHeight = parseFloat(wmStyle.height) || wmImage.naturalHeight;
    
            const wmX = wmLeft * scaleX;
            const wmY = wmTop * scaleY;
            const wmWidthScaled = wmWidth * scaleX;
            const wmHeightScaled = wmHeight * scaleY;
            console.log('[iMagify] Vẽ watermark', { index: i, wmLeft, wmTop, wmWidth, wmHeight, wmX, wmY, wmWidthScaled, wmHeightScaled });
    
            try {
              cropCtx.drawImage(wmImage, wmX, wmY, wmWidthScaled, wmHeightScaled);
            } catch (error) {
              console.error('Error drawing watermark:', error);
            }
          }
        }
    
        // Convert to base64 and add to sizes
        const dataURL = cropCanvas.toDataURL("image/" + primaryFmt, primaryQuality / 100);
        
        // NEW FORMAT: Use ratioKey (config size) instead of actual crop size
        // ratioKey = "500x500" from config, not actual cropped size
        result.sizes[ratioKey] = dataURL;
        console.log('[iMagify] Hoàn tất size', { index: i, ratioKey, dataLength: dataURL.length });
      }
    
      // Step 4: Process original if requested
      if (this.options.original) {
        update("Đang xử lý ảnh gốc...");
        await nextFrame();
    
        const dataURLOrig = baseCanvas.toDataURL("image/" + primaryFmt, primaryQuality / 100);
        
        // NEW FORMAT: Direct base64 string
        result.original = dataURLOrig;
        console.log('[iMagify] Đã tạo original', { length: dataURLOrig.length });
      }
    
      return result;
    };
  
  
    // Hàm createSaveButton: tạo nút lưu riêng cho ảnh hiện tại
    iMagify.prototype.createSaveButton = function () {
      // Nếu đã có nút lưu cũ, xóa nó
      if (this.saveBtn) {
        this.saveBtn.remove();
      }

      // Tạo container cho các nút
      const buttonContainer = document.createElement("div");
      Object.assign(buttonContainer.style, {
        position: "fixed",
        bottom: "20px",
        right: "20px",
        display: "flex",
        gap: "10px",
        zIndex: "9999"
      });

      // Tạo nút save mới
      this.saveBtn = document.createElement("button");
      this.saveBtn.className = "iMagify-saveBtn btn btn-primary";
      this.saveBtn.innerText = "Save";
      Object.assign(this.saveBtn.style, {
        padding: "10px 20px",
        fontSize: "16px",
        color: "white",
        border: "none",
        borderRadius: "4px",
        cursor: "pointer",
        boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
        transition: "all 0.3s ease",
        display: "flex",
        alignItems: "center",
        gap: "8px"
      });

      // Thêm hiệu ứng hover cho nút save
      this.saveBtn.addEventListener("mouseenter", () => {
        this.saveBtn.style.backgroundColor = "#45a049";
        this.saveBtn.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
      });
      this.saveBtn.addEventListener("mouseleave", () => {
        this.saveBtn.style.backgroundColor = "#4CAF50";
        this.saveBtn.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
      });

      // Thêm sự kiện click cho nút save
      this.saveBtn.addEventListener("click", () => {
        console.log('[iMagify] Click Save', { currentImageIndex: this.currentImageIndex });
        this.saveBtn.disabled = true;
        this.saveBtn.style.opacity = "0.7";
        this.saveBtn.style.cursor = "not-allowed";
        this.saveBtn.innerHTML = '';
        
        const spinner = document.createElement("div");
        spinner.className = "iMagify-spinner";
        Object.assign(spinner.style, {
          width: "20px",
          height: "20px",
          border: "3px solid #ffffff",
          borderTop: "3px solid transparent",
          borderRadius: "50%",
          animation: "iMagify-spin 1s linear infinite",
          display: "inline-block",
          marginRight: "8px",
          verticalAlign: "middle"
        });

        const text = document.createElement("span");
        text.textContent = "Đang xử lý ảnh...";
        text.style.verticalAlign = "middle";

        this.saveBtn.appendChild(spinner);
        this.saveBtn.appendChild(text);
        
        this.processCurrentUpload();
      });

      // Thêm nút clear và replace cho single file
      if (this.options.images.length === 1) {
        // Nút Clear
        const clearBtn = document.createElement("button");
        clearBtn.className = "iMagify-clearBtn btn btn-danger";
        clearBtn.innerText = "Xóa";
        Object.assign(clearBtn.style, {
          padding: "10px 20px",
          fontSize: "16px",
          color: "white",
          border: "none",
          borderRadius: "4px",
          cursor: "pointer",
          boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
          transition: "all 0.3s ease",
          backgroundColor: "#dc3545"
        });

        // Thêm hiệu ứng hover cho nút clear
        clearBtn.addEventListener("mouseenter", () => {
          clearBtn.style.backgroundColor = "#c82333";
          clearBtn.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
        });
        clearBtn.addEventListener("mouseleave", () => {
          clearBtn.style.backgroundColor = "#dc3545";
          clearBtn.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
        });

        // Thêm sự kiện click cho nút clear
        clearBtn.addEventListener("click", () => {
          if (confirm("Bạn có chắc chắn muốn xóa ảnh này?")) {
            if (typeof this.completeCallback === 'function') {
              // Gửi thông tin file hiện tại với action clear
              const currentImage = this.options.images[0];
              const fileData = {
                action: 'clear',
                id: currentImage.id,
                name: currentImage.name,
                path: currentImage.path
              };
              this.completeCallback([fileData]);
            }
            console.log('[iMagify] Clear ảnh đơn', this.options.images[0]);
            document.body.removeChild(this.modal);
          }
        });

        // Nút Replace
        const replaceBtn = document.createElement("button");
        replaceBtn.className = "iMagify-replaceBtn btn btn-warning";
        replaceBtn.innerText = "Thay thế";
        Object.assign(replaceBtn.style, {
          padding: "10px 20px",
          fontSize: "16px",
          color: "white",
          border: "none",
          borderRadius: "4px",
          cursor: "pointer",
          boxShadow: "0 2px 5px rgba(0,0,0,0.2)",
          transition: "all 0.3s ease",
          backgroundColor: "#ffc107"
        });

        // Thêm hiệu ứng hover cho nút replace
        replaceBtn.addEventListener("mouseenter", () => {
          replaceBtn.style.backgroundColor = "#e0a800";
          replaceBtn.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
        });
        replaceBtn.addEventListener("mouseleave", () => {
          replaceBtn.style.backgroundColor = "#ffc107";
          replaceBtn.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
        });

        // Thêm sự kiện click cho nút replace
        replaceBtn.addEventListener("click", () => {
          const fileInput = document.createElement("input");
          fileInput.type = "file";
          fileInput.accept = "image/*";
          fileInput.style.display = "none";
          document.body.appendChild(fileInput);

          fileInput.addEventListener("change", (e) => {
            if (e.target.files && e.target.files[0]) {
              const file = e.target.files[0];
              console.log('[iMagify] Chọn file thay thế', { name: file.name, size: file.size, type: file.type });
              const reader = new FileReader();
              reader.onload = (e) => {
                // Cập nhật thông tin file mới
                const currentImage = this.options.images[0];
                currentImage.src = e.target.result;
                currentImage.file = file; // Save file object để upload
                currentImage.name = file.name;
                
                // Cập nhật giao diện
                this.mainImage.src = e.target.result;
                this.mainImage.onload = () => {
                  this.updateCropBoxes();
                  this.createSaveButton();
                };
              };
              reader.readAsDataURL(file);
            }
            document.body.removeChild(fileInput);
          });

          fileInput.click();
        });

        // Thêm các nút vào container
        buttonContainer.appendChild(clearBtn);
        buttonContainer.appendChild(replaceBtn);
      }

      // Thêm nút save vào container
      buttonContainer.appendChild(this.saveBtn);

      // Thêm container vào modal
      this.modal.appendChild(buttonContainer);
    };
  
    // Hàm processCurrentUpload: khi bấm "Save" cho ảnh hiện tại
    iMagify.prototype.processCurrentUpload = async function () {
      let thumbnails = document.querySelectorAll(".iMagify-thumbnail");
      try {
        console.log('[iMagify] Bắt đầu upload', { currentImageIndex: this.currentImageIndex });
        // Tạo dữ liệu upload cho ảnh hiện tại
        let uploadData = await this.buildUploadResult();
        console.log('[iMagify] Upload payload xây xong', { filename: uploadData.filename, sizes: Object.keys(uploadData.sizes), hasOriginal: !!uploadData.original });

        // Ẩn thumbnail của ảnh hiện tại
        if (thumbnails[this.currentImageIndex]) {
          thumbnails[this.currentImageIndex].style.display = "none";
        }

        // Chuyển qua ảnh tiếp theo
        var uploadImageIndex = this.currentImageIndex;
        if (this.currentImageIndex < this.options.images.length - 1) {
          this.currentImageIndex++;
          this.mainImage.src = this.options.images[this.currentImageIndex].src ? this.options.images[this.currentImageIndex].src : this.options.images[this.currentImageIndex];
          this.highlightThumbnail(this.currentImageIndex);
          this.createSaveButton();
          console.log('[iMagify] Chuyển sang ảnh tiếp theo', { currentImageIndex: this.currentImageIndex });
        }

        // Cập nhật trạng thái loading
        if (this.saveBtn) {
          this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Đang tải lên server...`;
        }
        await nextFrame();

        // Kiểm tra nếu là file mới từ replace
        const currentImage = this.options.images[uploadImageIndex];
        if (currentImage.file) {
          console.log('[iMagify] Upload kiểu replace (FormData)');
          // Tạo FormData để upload file
          const formData = new FormData();
          formData.append('file', currentImage.file);
          
          // Thêm các thông tin khác nếu cần
          if (currentImage.id) {
            formData.append('id', currentImage.id);
          }
          formData.append('action', 'replace');
          
          // Gửi request upload
          const response = await fetch(this.options.server, {
            method: "POST",
            body: formData
          });

          const data = await response.json();
          console.log('[iMagify] Kết quả server (replace)', data);
          
          if (!data.success || !data || !data.data.files) {
            throw new Error(data?.message || 'Upload thất bại');
          }

          // Xử lý dữ liệu thành công
          if (typeof this.uploadCallback === 'function') {
            this.uploadCallback(data.data.files);
          }
          this.results[uploadImageIndex] = data.data.files;
          this.uploaded[uploadImageIndex] = true;
          
          if (this.uploaded.every(item => item === true)) {
            if (typeof this.completeCallback === 'function') {
              this.completeCallback(this.results);
            }
            setTimeout(() => { document.body.removeChild(this.modal); }, 1000);
          }
        } else {
          // Xử lý upload thông thường
          console.log('[iMagify] Upload JSON thông thường');
          const response = await fetch(this.options.server, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(uploadData)
          });

          const data = await response.json();
          console.log('[iMagify] Kết quả server (json)', data);
          
          if (!data.success || !data || !data.data.files) {
            throw new Error(data?.message || 'Upload thất bại');
          }

          if (typeof this.uploadCallback === 'function') {
            this.uploadCallback(data.data.files);
          }
          this.results[uploadImageIndex] = data.data.files;
          this.uploaded[uploadImageIndex] = true;
          
          if (this.uploaded.every(item => item === true)) {
            if (typeof this.completeCallback === 'function') {
              this.completeCallback(this.results);
            }
            setTimeout(() => { document.body.removeChild(this.modal); }, 1000);
          }
        }
      } catch (err) {
        // Xử lý lỗi
        console.error('[iMagify] Lỗi upload', err);
        if (thumbnails[uploadImageIndex]) {
          thumbnails[uploadImageIndex].style.display = "block";
        }
        if (this.saveBtn) {
          this.saveBtn.disabled = false;
          this.saveBtn.style.opacity = "1";
          this.saveBtn.style.cursor = "pointer";
          this.saveBtn.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="color: #dc3545;">⚠️</span>
              <span>Upload thất bại - Click để thử lại</span>
            </div>
          `;
          
          this.saveBtn.onclick = () => {
            this.saveBtn.innerHTML = `
              <div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>
              Đang xử lý ảnh...
            `;
            this.processCurrentUpload();
          };
        }
        
        // Hiển thị thông báo lỗi
        const errorMsg = document.createElement('div');
        errorMsg.style.cssText = `
          position: fixed;
          top: 20px;
          right: 20px;
          background: #dc3545;
          color: white;
          padding: 10px 20px;
          border-radius: 4px;
          z-index: 10001;
          box-shadow: 0 2px 5px rgba(0,0,0,0.2);
          animation: slideIn 0.3s ease-out;
        `;
        errorMsg.innerHTML = `Upload ảnh ${uploadImageIndex + 1} thất bại: ${err.message}`;
        document.body.appendChild(errorMsg);
        
        setTimeout(() => {
          errorMsg.style.animation = 'slideOut 0.3s ease-out';
          setTimeout(() => errorMsg.remove(), 300);
        }, 5000);
      }
    };
  
    // Hàm highlightThumbnail: đánh dấu thumbnail của ảnh hiện tại
    iMagify.prototype.highlightThumbnail = function (index) {
      document.querySelectorAll(".iMagify-thumbnail").forEach((el, i) => {
        el.style.border = (i === index) ? "2px solid #00f" : "none";
      });
    };
  
    // --- Utility: Draggable ---
    function makeElementDraggable(el, container, boundEl, onDragEnd) {
      let pos = { top: 0, left: 0, x: 0, y: 0 };
      const mouseDownHandler = function (e) {
        // Ngăn chặn sự kiện lan ra các phần tử khác
        e.stopPropagation();
        pos = { left: el.offsetLeft, top: el.offsetTop, x: e.clientX, y: e.clientY };
        console.log('[iMagify][drag] start', { className: el.className, left: pos.left, top: pos.top });
        document.addEventListener("mousemove", mouseMoveHandler);
        document.addEventListener("mouseup", mouseUpHandler);
        e.preventDefault();
      };
      
      const mouseMoveHandler = function (e) {
        const dx = e.clientX - pos.x;
        const dy = e.clientY - pos.y;
        let newLeft = pos.left + dx;
        let newTop = pos.top + dy;
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
        console.log('[iMagify][drag] end', { className: el.className, left: el.style.left, top: el.style.top });
        if (onDragEnd) onDragEnd();
      };
      
      el.addEventListener("mousedown", mouseDownHandler);
    }
  
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
        console.log('[iMagify][resize] end', { className: el.className, width: el.style.width, height: el.style.height });
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