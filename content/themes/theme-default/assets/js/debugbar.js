    function initDebugbarUI() {
        const debugbar = document.getElementById('debugbar');
        if (!debugbar) {
            return;
        }

        const SMALL_SCREEN_WIDTH = 800;

        function defaultExpandedHeight() {
            if (window.innerWidth < SMALL_SCREEN_WIDTH) {
                const target = Math.round(window.innerHeight * 0.6);
                return Math.min(Math.max(target, 260), 480);
            }
            return 400;
        }

        if (!debugbar.dataset.lastHeight) {
            debugbar.dataset.lastHeight = String(defaultExpandedHeight());
        }

        const header = document.getElementById('debugbar-header');
        const toggleBtn = document.getElementById('debugbar-toggle-btn');
        const tabs = debugbar.querySelectorAll('.debugbar-tab');
        const panels = debugbar.querySelectorAll('.debugbar-panel');
        const sections = debugbar.querySelectorAll('.debugbar-section[data-section]');

        function expandDebugbar() {
            const storedHeight = parseInt(debugbar.dataset.lastHeight || '400', 10);
            let targetHeight = Number.isNaN(storedHeight) ? defaultExpandedHeight() : storedHeight;
            if (window.innerWidth < SMALL_SCREEN_WIDTH) {
                targetHeight = Math.min(
                    Math.max(targetHeight, 260),
                    Math.round(window.innerHeight * 0.8)
                );
            }
            debugbar.classList.remove('collapsed');
            debugbar.style.height = targetHeight + 'px';
            debugbar.style.maxHeight = targetHeight + 'px';
            debugbar.dataset.lastHeight = targetHeight;
        }

        function collapseDebugbar() {
            if (!debugbar.classList.contains('collapsed')) {
                const currentHeight = debugbar.offsetHeight;
                if (currentHeight > 0) {
                    debugbar.dataset.lastHeight = currentHeight;
                }
            }
            debugbar.classList.add('collapsed');
            debugbar.style.height = '';
            debugbar.style.maxHeight = '';
        }

        function toggleDebugbar() {
            if (debugbar.classList.contains('collapsed')) {
                expandDebugbar();
            } else {
                collapseDebugbar();
            }
        }

        if (header) {
            header.addEventListener('click', function(event) {
                if (event.target.closest('#debugbar-toggle-btn')) {
                    return;
                }
                toggleDebugbar();
            });
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleDebugbar();
            });
        }

        function activateTab(tabName) {
            if (!tabName) {
                return;
            }
            debugbar.dataset.activeTab = tabName;
            tabs.forEach(function(tabButton) {
                const isActive = tabButton.dataset.tab === tabName;
                tabButton.classList.toggle('active', isActive);
            });
            panels.forEach(function(panel) {
                const isActive = panel.dataset.panel === tabName;
                panel.classList.toggle('active', isActive);
            });
        }

        const initialTab = debugbar.dataset.activeTab || (tabs[0] ? tabs[0].dataset.tab : null);
        activateTab(initialTab);

        tabs.forEach(function(tabButton) {
            tabButton.addEventListener('click', function(event) {
                event.preventDefault();
                activateTab(tabButton.dataset.tab);
            });
        });

        sections.forEach(function(section) {
            const title = section.querySelector('.debugbar-section-title');
            if (!title) {
                return;
            }
            title.addEventListener('click', function() {
                section.classList.toggle('collapsed');
            });
        });

        collapseDebugbar();

        window.addEventListener('resize', function() {
            if (!debugbar.classList.contains('collapsed')) {
                debugbar.dataset.lastHeight = String(defaultExpandedHeight());
                expandDebugbar();
            } else {
                debugbar.dataset.lastHeight = String(defaultExpandedHeight());
            }
        });
    }

    // Copy to clipboard function
    function copyToClipboard(elementId, event) {
        try {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }

            const textarea = document.getElementById(elementId);
            if (!textarea) {
                console.error('Element not found:', elementId);
                return;
            }

            textarea.select();
            textarea.setSelectionRange(0, 99999);

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textarea.value).then(function() {
                    showCopySuccess(event.currentTarget);
                }).catch(function(err) {
                    fallbackCopy(textarea, event.currentTarget);
                });
            } else {
                fallbackCopy(textarea, event.currentTarget);
            }
        } catch (error) {
            console.error('Copy error:', error);
        }
    }

    function fallbackCopy(textarea, button) {
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button);
            } else {
                console.error('Copy command failed');
            }
        } catch (err) {
            console.error('Fallback copy error:', err);
        }
    }

    function showCopySuccess(button) {
        if (!button) return;

        const originalHTML = button.innerHTML;
        button.classList.add('copied');
        button.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';

        setTimeout(function() {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
        }, 2000);
    }

    // Resize functionality
    let isResizing = false;
    let startY = 0;
    let startHeight = 0;

    function initResize() {
        try {
            const resizeHandle = document.getElementById('debugbar-resize-handle');
            const debugbar = document.getElementById('debugbar');

            if (!resizeHandle || !debugbar) {
                return;
            }

            resizeHandle.addEventListener('mousedown', function(e) {
                try {
                    if (debugbar.classList.contains('collapsed')) {
                        return;
                    }
                    isResizing = true;
                    startY = e.clientY;
                    startHeight = parseInt(document.defaultView.getComputedStyle(debugbar).height, 10);

                    // Disable transition during resize for smooth dragging
                    debugbar.style.transition = 'none';

                    document.addEventListener('mousemove', handleResize);
                    document.addEventListener('mouseup', stopResize);

                    e.preventDefault();
                } catch (error) {
                    console.error('Debugbar resize mousedown error:', error);
                }
            });
        } catch (error) {
            console.error('Debugbar initResize error:', error);
        }
    }

    function handleResize(e) {
        try {
            if (!isResizing) return;

            const debugbar = document.getElementById('debugbar');
            if (!debugbar) return;
            if (debugbar.classList.contains('collapsed')) return;

            const newHeight = startHeight - (e.clientY - startY);
            const minHeight = 200;
            const maxHeight = Math.max(window.innerHeight - 100, 400);

            // Ensure height is within bounds
            const clampedHeight = Math.max(minHeight, Math.min(newHeight, maxHeight));
            debugbar.style.height = clampedHeight + 'px';
            debugbar.style.maxHeight = clampedHeight + 'px';
            debugbar.dataset.lastHeight = clampedHeight;
        } catch (error) {
            console.error('Debugbar handleResize error:', error);
        }
    }

    function stopResize() {
        try {
            if (!isResizing) return;

            isResizing = false;

            const debugbar = document.getElementById('debugbar');
            if (debugbar) {
                // Re-enable transition after resize
                debugbar.style.transition = 'max-height 0.2s ease, height 0.2s ease';
                if (!debugbar.classList.contains('collapsed')) {
                    const currentHeight = parseInt(debugbar.style.height, 10);
                    if (!Number.isNaN(currentHeight)) {
                        debugbar.dataset.lastHeight = currentHeight;
                    }
                }
            }

            // Clean up event listeners
            document.removeEventListener('mousemove', handleResize);
            document.removeEventListener('mouseup', stopResize);
        } catch (error) {
            console.error('Debugbar stopResize error:', error);
        }
    }

    function bootDebugbar() {
        try {
            initDebugbarUI();
            initResize();
        } catch (error) {
            console.error('Debugbar initialization error:', error);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootDebugbar);
    } else {
        bootDebugbar();
    }

    // Toggle hook details
    function toggleHookDetails(hookId) {
        try {
            const details = document.getElementById('hook-details-' + hookId);
            const icon = document.getElementById('hook-icon-' + hookId);
            
            if (!details) return;
            
            if (details.style.display === 'none' || details.style.display === '') {
                // Show details
                details.style.display = 'block';
                if (icon) {
                    icon.style.transform = 'rotate(90deg)';
                }
            } else {
                // Hide details
                details.style.display = 'none';
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        } catch (error) {
            console.error('Toggle hook details error:', error);
        }
    }

    // Toggle profile children
    function toggleProfile(profileId) {
        try {
            const children = document.getElementById('profile-children-' + profileId);
            const icon = document.getElementById('profile-icon-' + profileId);
            
            if (!children) return;
            
            if (children.style.display === 'none' || children.style.display === '') {
                // Show children
                children.style.display = 'block';
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            } else {
                // Hide children
                children.style.display = 'none';
                if (icon) {
                    icon.style.transform = 'rotate(-90deg)';
                }
            }
        } catch (error) {
            console.error('Toggle profile error:', error);
        }
    }
