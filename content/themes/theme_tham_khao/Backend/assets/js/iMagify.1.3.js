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
    function iMagify(options) {
      this.options = options;
      this.currentImageIndex = 0;
      this.cropBoxes = [];
      this.selectedCropIndex = 0;
      this.cropStates = {};
      this.isProcessing = false;
      
      // Color strategy configuration
      this.colorStrategy = (this.options && this.options.colorStrategy) || COLOR_STRATEGY;
      this.hasP3 = supportsP3Canvas2D();
      this.wantP3 = (this.colorStrategy === 'p3') || (this.colorStrategy === 'auto' && this.hasP3);
      this.lockPreview = LOCK_PREVIEW_TO_OUTPUT;
      
      // Watermark and upload state
      this._watermarkInitialized = false;
      this.uploaded = new Array(this.options.images.length).fill(false);
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
      
      window.iMagifyInstance = this;
      console.log("window.iMagifyInstance", window.iMagifyInstance);
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
      // Global error handlers
      if (!window.__iMagifyErrorHooked) {
        window.addEventListener('error', function (e) {
          console.error('[iMagify]', e.message, e.filename+':'+e.lineno+':'+e.colno);
        });
        window.addEventListener('unhandledrejection', function (e) {
          console.error('[iMagify]', e.reason);
        });
        window.__iMagifyErrorHooked = true;
      }
      // Create modal dialog
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
  
      // Simple logging helper
      this._log = (label, payload) => {
        if (this.options.debugMode) {
          console.log(`[iMagify] ${label}`, payload || '');
        }
      };


      // Close button
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
  
      // Thumbnail container
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
        overflow: "auto",
        paddingBottom: "100px"
      });
      this.modal.appendChild(this.editorContainer);

      // Sidebar for crop box selection
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
      try { this.editorContainer.style.marginLeft = '180px'; } catch(_) {}
  
      // Main image
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

      // Create thumbnails and load main image
      this.options.images.forEach((imgSrc, index) => {
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
          document.querySelectorAll(".iMagify-thumbnail").forEach(el => el.style.border = "2px solid transparent");
          thumb.style.border = "2px solid #4CAF50";
          this.currentImageIndex = index;
          this.mainImage.src = imgSrc.src ? imgSrc.src : imgSrc;
          
          // Restore crop state for this image
          this.applyCropState();
          
          this.mainImage.onload = () => { 
            this.updateCropBoxes(); 
            this.createSaveButton(); 
            this.buildSidebar();
          };
        });
        
        this.thumbnailContainer.appendChild(thumb);
      });

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
      if(this.options.sizes) {
        this.options.sizes.forEach((size, index) => {
          try {
            const w = parseFloat(size.width);
            const h = parseFloat(size.height);
            if (!isFinite(w) || !isFinite(h) || w <= 0 || h <= 0) {
              return;
            }
            
            const box = document.createElement("div");
          box.className = "iMagify-cropBox";
          box.dataset.ratio = w + "x" + h;
          
            // Get color from array
          const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
          
            // Add label with dimensions
            const label = document.createElement("div");
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
          
            // Create watermark container for each crop box
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

            // Create watermark image
          const wmImage = document.createElement("img");
          
          // Get watermark config from sizes array for this specific crop box
          const sizeConfig = this.options.sizes[index];
          const wmConfig = sizeConfig?.watermark || this.options.watermark;
          const wmSrc = (typeof wmConfig === 'object') ? (wmConfig?.src) : wmConfig;
          
          if (wmSrc) {
            wmImage.src = wmSrc;
          }
          
          // Apply watermark styles with opacity and object-fit
          Object.assign(wmImage.style, {
            width: "100%",
            height: "100%",
            objectFit: "contain", // Prevent distortion
            opacity: wmConfig?.opacity || 1.0
          });
          wmContainer.appendChild(wmImage);

            // Add watermark toggle button
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

            // Hide toggle and watermark if no src
          if (!wmSrc) {
            wmContainer.style.display = "none";
            toggleBtn.style.display = "none";
          }

            // Add watermark container to box
          box.appendChild(wmContainer);
          box.watermarkContainer = wmContainer;

            // Handle watermark toggle
          toggleBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            const isVisible = wmContainer.style.display === "block";
            wmContainer.style.display = isVisible ? "none" : "block";
            toggleBtn.style.opacity = isVisible ? "0.5" : "1";
          });

            // Setup draggable and resizable for watermark
          wmImage.onload = () => {
            const aspect = wmImage.naturalWidth / wmImage.naturalHeight;
            
              // Prevent watermark drag events from propagating to crop box
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

              // Adjust initial watermark size
            this.adjustWatermarkSize(box);
          };

            // Update watermark position when crop box moves
          const updateWatermarkPosition = () => {
            if (wmContainer.style.display !== "none") {
              const boxRect = box.getBoundingClientRect();
              const wmRect = wmContainer.getBoundingClientRect();
              
                // Keep watermark within crop box bounds
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

            // Add move event for crop box
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
          
            // Add hover effects
          box.addEventListener("mouseenter", () => {
            box.style.boxShadow = "0 4px 8px rgba(0,0,0,0.3)";
          });
          box.addEventListener("mouseleave", () => {
            box.style.boxShadow = "0 2px 5px rgba(0,0,0,0.2)";
          });
          
          this.editorContainer.appendChild(box);
          this.cropBoxes.push(box);

            // Click to select box
          box.addEventListener('mousedown', () => {
              this.switchToTab(index);
          });

            // Update z-index when dragging crop box
          const updateZIndex = () => {
            const maxZIndex = Math.max(...this.cropBoxes.map(b => parseInt(b.style.zIndex)));
            box.style.zIndex = (maxZIndex + 1).toString();
          };

          try {
            makeElementDraggable(box, this.editorContainer, this.mainImage, () => { 
              this.constrainWatermark();
              updateWatermarkPosition();
              updateZIndex();
                // Save crop state immediately after drag
                setTimeout(() => {
                  try { 
                    this.saveCropState(this.currentImageIndex); 
                    console.log(`[iMagify] Saved crop state after drag for image ${this.currentImageIndex}`);
                  } catch(e) { 
                    console.error('[iMagify] Error saving crop state after drag:', e);
                  }
                }, 10);
            });
          } catch (e) {
              // Silent fail
          }
            
          try {
            const ratioNumber = (parseFloat(size.width) || 1) / (parseFloat(size.height) || 1);
            makeElementResizable(box, this.editorContainer, ratioNumber, this.mainImage, () => { 
              this.constrainWatermark();
              updateWatermarkPosition();
              updateZIndex();
                // Save crop state immediately after resize
                setTimeout(() => {
                  try { 
                    this.saveCropState(this.currentImageIndex); 
                    console.log(`[iMagify] Saved crop state after resize for image ${this.currentImageIndex}`);
                  } catch(e) { 
                    console.error('[iMagify] Error saving crop state after resize:', e);
                  }
                }, 10);
            });
          } catch (e) {
              // Silent fail
          }
          } catch (err) {
            console.error('[iMagify] Error creating cropBox index=' + index, err, size);
          }
        });
      }    
  
  
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
      
      console.log(`[iMagify] updateCropBoxes - hasSavedState: ${hasSavedState} for image ${this.currentImageIndex}`);

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
        this.adjustWatermarkSize(box);
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
              
              // Set watermark position based on config
          let pos = { left: 0, top: 0 };
              const boxRect = box.getBoundingClientRect();
              const wmWidth = box.watermarkContainer.offsetWidth || 100;
              const wmHeight = box.watermarkContainer.offsetHeight || 50;
              
              switch (position) {
            case "top-right":
                  pos.left = boxRect.width - wmWidth - pad;
                  pos.top = pad;
              break;
            case "bottom-right":
                  pos.left = boxRect.width - wmWidth - pad;
                  pos.top = boxRect.height - wmHeight - pad;
              break;
            case "bottom-left":
                  pos.left = pad;
                  pos.top = boxRect.height - wmHeight - pad;
              break;
            case "center":
                  pos.left = (boxRect.width - wmWidth) / 2;
                  pos.top = (boxRect.height - wmHeight) / 2;
              break;
            default:
                  pos.left = pad;
                  pos.top = pad;
              break;
          }
              
              box.watermarkContainer.style.left = pos.left + "px";
              box.watermarkContainer.style.top = pos.top + "px";
              
              // Apply opacity to watermark image
              const wmImage = box.watermarkContainer.querySelector('img');
              if (wmImage && wmConfig.opacity !== undefined) {
                wmImage.style.opacity = wmConfig.opacity;
              }
              
              console.log(`[iMagify] Initialized watermark for crop box ${index}:`, {
                position: position,
                padding: pad,
                opacity: wmConfig.opacity,
                calculatedPos: pos,
                boxSize: { width: boxRect.width, height: boxRect.height },
                wmSize: { width: wmWidth, height: wmHeight }
              });
            }
          }
        });
        this._watermarkInitialized = true;
      }
    };

    // Save crop state for current image (relative percentages to main image)
    iMagify.prototype.saveCropState = function(imageIndex) {
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
          const wmDisplay = box.watermarkContainer.style.display;
          const wmVisibility = box.watermarkContainer.style.visibility;
          const wmOpacity = box.watermarkContainer.style.opacity;
          
          console.log(`[iMagify] Watermark container state for box ${this.cropBoxes.indexOf(box)}:`, {
            display: wmDisplay,
            visibility: wmVisibility,
            opacity: wmOpacity,
            hasContainer: !!box.watermarkContainer
          });
          
          // Always save watermark state, regardless of visibility (tab system uses visibility: hidden)
          if (wmDisplay !== 'none') {
            const wmRect = box.watermarkContainer.getBoundingClientRect();
            // Calculate position relative to crop box
            const wmLeft = wmRect.left - rect.left;
            const wmTop = wmRect.top - rect.top;
            wm = {
              leftPct: wmLeft / rect.width,
              topPct: wmTop / rect.height,
              widthPct: wmRect.width / rect.width,
              heightPct: wmRect.height / rect.height,
              visible: true
            };
            
            console.log(`[iMagify] Saving watermark state for box ${this.cropBoxes.indexOf(box)}:`, {
              wmRect: { left: wmRect.left, top: wmRect.top, width: wmRect.width, height: wmRect.height },
              boxRect: { left: rect.left, top: rect.top, width: rect.width, height: rect.height },
              relativePos: { left: wmLeft, top: wmTop },
              percentages: { leftPct: wm.leftPct, topPct: wm.topPct, widthPct: wm.widthPct, heightPct: wm.heightPct }
            });
          } else {
            wm = { visible: false };
            console.log(`[iMagify] Watermark hidden for box ${this.cropBoxes.indexOf(box)}`);
          }
        }
        
        // Debug logging
        console.log(`[iMagify] Saving crop state for box:`, {
          leftPct, topPct, widthPct, heightPct,
          actualRect: { left: rect.left, top: rect.top, width: rect.width, height: rect.height },
          imageRect: { left: imageRect.left, top: imageRect.top, width: imageRect.width, height: imageRect.height }
        });
        
        return { leftPct, topPct, widthPct, heightPct, wm };
      });
      this.cropStates[imageIndex] = states;
    };

    // Apply saved crop state
    iMagify.prototype.applyCropState = function(imageIndex) {
      const states = this.cropStates[imageIndex];
      if (!states || !this.mainImage) { this.updateCropBoxes(); return; }
      const imageRect = this.mainImage.getBoundingClientRect();
      const containerRect = this.editorContainer.getBoundingClientRect();
      const imageOffsetLeft = imageRect.left - containerRect.left;
      const imageOffsetTop = imageRect.top - containerRect.top;
      this.cropBoxes.forEach((box, i) => {
        const st = states[i];
        if (!st) return;
        const boxWidth = st.widthPct * imageRect.width;
        const boxHeight = st.heightPct * imageRect.height;
        const left = imageOffsetLeft + st.leftPct * imageRect.width;
        const top = imageOffsetTop + st.topPct * imageRect.height;
        box.style.width = boxWidth + 'px';
        box.style.height = boxHeight + 'px';
        box.style.left = left + 'px';
        box.style.top = top + 'px';
        
        // Debug logging
        console.log(`[iMagify] Applying crop state for box ${i}:`, {
          savedState: st,
          calculatedPos: { left, top, width: boxWidth, height: boxHeight },
          imageRect: { left: imageRect.left, top: imageRect.top, width: imageRect.width, height: imageRect.height },
          containerRect: { left: containerRect.left, top: containerRect.top }
        });
        
        // watermark
        if (box.watermarkContainer && st.wm) {
          const wmLeft = st.wm.leftPct * boxWidth;
          const wmTop = st.wm.topPct * boxHeight;
          const wmWidth = st.wm.widthPct * boxWidth;
          const wmHeight = st.wm.heightPct * boxHeight;
          box.watermarkContainer.style.left = wmLeft + 'px';
          box.watermarkContainer.style.top = wmTop + 'px';
          box.watermarkContainer.style.width = wmWidth + 'px';
          box.watermarkContainer.style.height = wmHeight + 'px';
          
          // Only change display if watermark was actually hidden, not just by tab system
          if (st.wm.visible === false) {
            box.watermarkContainer.style.display = 'none';
          } else {
            box.watermarkContainer.style.display = 'block';
          }
          
          console.log(`[iMagify] Applied watermark state for box ${i}:`, {
            wmState: st.wm,
            calculatedPos: { left: wmLeft, top: wmTop, width: wmWidth, height: wmHeight },
            boxSize: { width: boxWidth, height: boxHeight },
            display: box.watermarkContainer.style.display
          });
        }
      });
      this.constrainWatermark();
    };

    // Create sidebar with tab system for crop boxes
    iMagify.prototype.buildSidebar = function () {
      if (!this.sidebar) return;
      this.sidebar.innerHTML = '';
      
      // Title
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

      // Tab container
      const tabContainer = document.createElement('div');
      Object.assign(tabContainer.style, {
        display: 'flex',
        flexDirection: 'column',
        gap: '4px',
        marginBottom: '12px'
      });

      // Create tabs for each crop box
      this.cropBoxes.forEach((box, index) => {
        const tab = document.createElement('div');
        tab.className = 'iMagify-sidebar-tab';
        tab.dataset.tabIndex = index;
        const ratio = box.dataset.ratio || '';
        const boxColor = this.cropBoxColors[index % this.cropBoxColors.length];
        
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

        tabContainer.appendChild(tab);
      });

      this.sidebar.appendChild(tabContainer);

      // Add tab content area
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

      // Initialize with first tab selected
      this.switchToTab(this.selectedCropIndex);
    };

    // Switch to specific tab and show only that crop box
    iMagify.prototype.switchToTab = function (index) {
      if (index < 0 || index >= this.cropBoxes.length) return;
      
      this.selectedCropIndex = index;
      
      // Apply saved crop state for current image before switching tabs
      if (this.cropStates[this.currentImageIndex]) {
        this.applyCropState(this.currentImageIndex);
        console.log(`[iMagify] Applied saved crop state when switching to tab ${index}`);
      }
      
      // Hide all crop boxes except the selected one (use visibility instead of display)
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
      
      // Update tab styles
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
      
      // Update tab content area
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
      
      // Scroll to selected crop box
      try { 
        this.cropBoxes[index].scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' }); 
      } catch(_) {}
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
      
      this.cropBoxes.forEach(box => {
        if (box.watermarkContainer && box.watermarkContainer.style.display !== "none") {
          const boxRect = box.getBoundingClientRect();
          const wmRect = box.watermarkContainer.getBoundingClientRect();
          
          // Keep watermark within crop box bounds
          let wmLeft = parseInt(box.watermarkContainer.style.left) || 0;
          let wmTop = parseInt(box.watermarkContainer.style.top) || 0;
          
          if (wmLeft < 0) wmLeft = 0;
          if (wmTop < 0) wmTop = 0;
          if (wmLeft + wmRect.width > boxRect.width) wmLeft = boxRect.width - wmRect.width;
          if (wmTop + wmRect.height > boxRect.height) wmTop = boxRect.height - wmRect.height;
          
          box.watermarkContainer.style.left = wmLeft + "px";
          box.watermarkContainer.style.top = wmTop + "px";
        }
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
      const aspectRatio = wmImage.naturalWidth / wmImage.naturalHeight;
      const newHeight = newWidth / aspectRatio;
    
      // Apply new size
      wmContainer.style.width  = newWidth + "px";
      wmContainer.style.height = newHeight + "px";
    
      // Position at bottom right corner with 10px padding
      const padding = 10;
      wmContainer.style.left   = "auto";
      wmContainer.style.top    = "auto";
      wmContainer.style.right  = padding + "px";
      wmContainer.style.bottom = padding + "px";
    
      // Ensure watermark doesn't exceed crop box bounds
      this.constrainWatermark();
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
      
      // Debug logging
      console.log(`[iMagify] Processing crop box ${index}:`, {
        boxRect: { left: boxRect.left, top: boxRect.top, width: boxRect.width, height: boxRect.height },
        imageRect: { left: imageRect.left, top: imageRect.top, width: imageRect.width, height: imageRect.height },
        cropCoords: { x: cropX, y: cropY, width: cropWidth, height: cropHeight },
        scale: { x: scaleX, y: scaleY }
      });
      
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
      
      // Calculate position relative to crop box
      const wmLeft = wmRect.left - boxRect.left;
      const wmTop = wmRect.top - boxRect.top;
      const wmWidth = wmRect.width;
      const wmHeight = wmRect.height;
      
      // Scale to canvas coordinates
      const wmX = wmLeft * scaleX;
      const wmY = wmTop * scaleY;
      const wmWidthScaled = wmWidth * scaleX;
      const wmHeightScaled = wmHeight * scaleY;
      
      console.log(`[iMagify] Drawing watermark for crop box ${index}:`, {
        boxRect: { left: boxRect.left, top: boxRect.top, width: boxRect.width, height: boxRect.height },
        wmRect: { left: wmRect.left, top: wmRect.top, width: wmRect.width, height: wmRect.height },
        relativePos: { left: wmLeft, top: wmTop },
        canvasPos: { x: wmX, y: wmY, width: wmWidthScaled, height: wmHeightScaled },
        opacity: opacity
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
      // Nếu đã có nút lưu cũ, xóa nó
      if (this.saveBtn) {
        this.saveBtn.remove();
      }

      // Tạo container cho các nút
      const buttonContainer = document.createElement("div");
      Object.assign(buttonContainer.style, {
        position: "fixed",
        bottom: "16px",
        right: "16px",
        display: "flex",
        gap: "8px",
        zIndex: "9999"
      });

      // Tạo nút save mới
      this.saveBtn = document.createElement("button");
      this.saveBtn.className = "iMagify-saveBtn btn btn-primary";
      this.saveBtn.innerText = "Xử lý";
      Object.assign(this.saveBtn.style, {
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

      // Thêm hiệu ứng hover cho nút save
      this.saveBtn.addEventListener("mouseenter", () => {
        this.saveBtn.style.filter = "brightness(1.05)";
        this.saveBtn.style.boxShadow = "0 4px 10px rgba(37,99,235,0.45)";
      });
      this.saveBtn.addEventListener("mouseleave", () => {
        this.saveBtn.style.filter = "none";
        this.saveBtn.style.boxShadow = "0 2px 6px rgba(37,99,235,0.35)";
      });

      // Thêm sự kiện click cho nút save
      this.saveBtn.addEventListener("click", () => {
        if (this.isProcessing) {
          console.log('[iMagify] Đang xử lý, bỏ qua click');
          return;
        }
        this.isProcessing = true;
        console.log('[iMagify] Click Save', { currentImageIndex: this.currentImageIndex });
        this.saveBtn.disabled = true;
        this.saveBtn.style.opacity = "0.7";
        this.saveBtn.style.cursor = "not-allowed";
        this.saveBtn.innerHTML = '';
        
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

        const text = document.createElement("span");
        text.textContent = "Đang xử lý...";
        text.style.verticalAlign = "middle";
        text.style.fontSize = "12px";

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

        // Thêm hiệu ứng hover cho nút clear
        clearBtn.addEventListener("mouseenter", () => {
          clearBtn.style.filter = "brightness(1.05)";
          clearBtn.style.boxShadow = "0 4px 10px rgba(153,27,27,0.45)";
        });
        clearBtn.addEventListener("mouseleave", () => {
          clearBtn.style.filter = "none";
          clearBtn.style.boxShadow = "0 2px 6px rgba(153,27,27,0.35)";
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

        // Thêm hiệu ứng hover cho nút replace
        replaceBtn.addEventListener("mouseenter", () => {
          replaceBtn.style.filter = "brightness(1.05)";
          replaceBtn.style.boxShadow = "0 4px 10px rgba(217,119,6,0.45)";
        });
        replaceBtn.addEventListener("mouseleave", () => {
          replaceBtn.style.filter = "none";
          replaceBtn.style.boxShadow = "0 2px 6px rgba(217,119,6,0.35)";
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
                
                // Log input khi replace file
                console.log('DEBUG: mainImage.src =', this.mainImage.src);
                if (this.mainImage.src && (this.mainImage.src.startsWith('data:') || this.mainImage.src.startsWith('blob:'))) {
                  console.log('INPUT:', this.mainImage.src);
                } else {
                  console.log('NOT DATA/BLOB URL:', this.mainImage.src);
                }
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
  
    // Hàm processCurrentUpload (v3 local-only): xử lý ảnh và trả kết quả qua callback, không gọi API
    iMagify.prototype.processCurrentUpload = async function () {
      let thumbnails = document.querySelectorAll(".iMagify-thumbnail");
      try {
        console.log('[iMagify] Local process bắt đầu', { currentImageIndex: this.currentImageIndex });
        // Xây dựng dữ liệu kết quả cho ảnh hiện tại
        let localData = await this.buildUploadResult();
        console.log('[iMagify] Local payload xong', { filename: localData.filename, sizes: Object.keys(localData.sizes), hasOriginal: !!localData.original });

        // Ẩn thumbnail của ảnh hiện tại
        if (thumbnails[this.currentImageIndex]) {
          thumbnails[this.currentImageIndex].style.display = "none";
        }

        // Lưu kết quả local
        const processedIndex = this.currentImageIndex;
        this.results[processedIndex] = localData;
        this.uploaded[processedIndex] = true;

        // Callback per-file
        if (typeof this.uploadCallback === 'function') {
          this.uploadCallback(localData);
        }

        // Chuyển qua ảnh tiếp theo nếu còn
        if (this.currentImageIndex < this.options.images.length - 1) {
          this.currentImageIndex++;
          this.mainImage.src = this.options.images[this.currentImageIndex].src ? this.options.images[this.currentImageIndex].src : this.options.images[this.currentImageIndex];
          
          // Log input khi chuyển sang ảnh tiếp theo
          console.log('DEBUG: mainImage.src =', this.mainImage.src);
          if (this.mainImage.src && (this.mainImage.src.startsWith('data:') || this.mainImage.src.startsWith('blob:'))) {
            console.log('INPUT:', this.mainImage.src);
          } else {
            console.log('NOT DATA/BLOB URL:', this.mainImage.src);
          }
          this.highlightThumbnail(this.currentImageIndex);
          this.createSaveButton();
          this.isProcessing = false;
          if (this.saveBtn) {
            this.saveBtn.innerHTML = `<div class="iMagify-spinner" style="display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;"></div>Đang sẵn sàng xử lý ảnh tiếp theo...`;
          }
          console.log('[iMagify] Chuyển sang ảnh tiếp theo', { currentImageIndex: this.currentImageIndex });
          return; // chờ người dùng bấm tiếp
        }

        // Nếu tất cả đã xử lý xong
        if (this.uploaded.every(Boolean)) {
          if (typeof this.completeCallback === 'function') {
            this.completeCallback(this.results);
          }
          this.isProcessing = false;
          if (this.saveBtn) {
            this.saveBtn.innerHTML = `<div class=\"iMagify-spinner\" style=\"display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;\"></div>Hoàn tất xử lý.`;
          }
          setTimeout(() => { document.body.removeChild(this.modal); }, 800);
        }
      } catch (err) {
        console.error('[iMagify] Lỗi local process', err);
        this.isProcessing = false;
        const failedIndex = this.currentImageIndex;
        if (thumbnails[failedIndex]) {
          thumbnails[failedIndex].style.display = "block";
        }
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
              <div class=\"iMagify-spinner\" style=\"display: inline-block; margin-right: 8px; width: 20px; height: 20px; border: 3px solid #ffffff; border-top: 3px solid transparent; border-radius: 50%; animation: iMagify-spin 1s linear infinite;\"></div>
              Đang xử lý ảnh...
            `;
            this.isProcessing = true;
            this.processCurrentUpload();
          };
        }
      }
    };
  
    // Hàm highlightThumbnail: đánh dấu thumbnail của ảnh hiện tại
    iMagify.prototype.highlightThumbnail = function (index) {
      document.querySelectorAll(".iMagify-thumbnail").forEach((el, i) => {
        el.style.border = (i === index) ? "2px solid #2563eb" : "none";
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