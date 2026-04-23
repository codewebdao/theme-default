/**
 * Spreadsheet Helper Library - Vanilla JS
 * 
 * Generic multi-select editor cho jspreadsheet
 * Hỗ trợ: User, Reference, Terms, Tags, Categories
 */

class SpreadsheetHelper {
    constructor() {
        this.termsData = {};
        this.fieldsTypeMap = {};
        this.fieldsDataMap = {};
        this._currentCallback = null;
        this._dialogData = null;
    }

    /**
     * Get column definition based on field type
     */
    getColumnDefinition(field, fieldData = null) {
        const fieldType = field.type || 'Text';
        const fieldName = field.field_name || '';
        const fieldLabel = field.label || fieldName.charAt(0).toUpperCase() + fieldName.slice(1).replace(/_/g, ' ');
        
        let columnDef = {
            title: fieldLabel,
            width: 200
        };

        switch (fieldType) {
            case 'User':
            case 'Reference':
                columnDef.type = 'text';
                columnDef.width = fieldType === 'User' ? 180 : 250;
                columnDef.placeholder = 'Click to select...';
                break;

            case 'Number':
            case 'Integer':
                columnDef.type = 'numeric';
                columnDef.mask = '#,##';
                columnDef.width = 120;
                break;

            case 'Float':
            case 'Decimal':
                columnDef.type = 'numeric';
                columnDef.mask = '#,##0.00';
                columnDef.width = 150;
                break;

            case 'Boolean':
            case 'Checkbox':
                columnDef.type = 'dropdown';
                columnDef.source = ['0', '1'];
                columnDef.width = 80;
                break;

            case 'Date':
                columnDef.type = 'calendar';
                columnDef.options = { format: 'YYYY-MM-DD' };
                columnDef.width = 140;
                break;

            case 'DateTime':
                columnDef.type = 'calendar';
                columnDef.options = { format: 'YYYY-MM-DD HH:mm:ss', time: true };
                columnDef.width = 180;
                break;

            case 'Select':
            case 'Radio':
                if (field.options && Array.isArray(field.options)) {
                    columnDef.type = 'dropdown';
                    columnDef.source = field.options.map(opt => {
                        return (typeof opt === 'object' && opt.value !== undefined) ? opt.value : opt;
                    });
                    columnDef.autocomplete = true;
                } else {
                    columnDef.type = 'text';
                }
                break;

            default:
                columnDef.type = 'text';
                break;
        }

        return columnDef;
    }

    /**
     * Format cell value for display
     */
    formatCellValue(fieldName, value, fieldType, fieldData = null) {
        if (value === undefined || value === null || value === '') {
            return '';
        }

        switch (fieldType) {
            case 'User':
                if (fieldData && Array.isArray(fieldData)) {
                    const user = fieldData.find(u => u.id == value);
                    if (user) {
                        return user.username || user.email || `#${user.id}`;
                    }
                }
                return value;

            case 'Reference':
                if (fieldData && Array.isArray(fieldData)) {
                    const refPost = fieldData.find(p => p.id == value);
                    if (refPost) {
                        return refPost.title || `#${refPost.id}`;
                    }
                }
                return value;

            case 'Boolean':
            case 'Checkbox':
                return value ? '1' : '0';

            default:
                return value;
        }
    }

    /**
     * Show generic select dialog
     */
    showSelectDialog(options) {
        const {
            fieldType = 'Select',
            fieldLabel = 'Select Items',
            currentValue = '',
            items = [],
            multiSelect = true,
            colIndex = 0,
            rowIndex = 0,
            onSave = null
        } = options;

        this._currentCallback = onSave;

        // Parse current values
        const currentValues = currentValue ? 
            (multiSelect ? currentValue.split(',').map(s => s.trim()).filter(s => s) : [currentValue.toString()]) : 
            [];

        // Determine value/label keys
        const valueKey = (fieldType === 'Terms' || fieldType === 'Tags' || fieldType === 'Category') ? 'slug' : 'id';

        // Store dialog data
        this._dialogData = {
            items: items.map(item => ({
                value: item[valueKey] || item.id || item.slug,
                label: item.name || item.title || item.value || item.label,
                selected: currentValues.includes((item[valueKey] || item.id || item.slug).toString())
            })),
            multiSelect: multiSelect,
            colIndex: colIndex,
            rowIndex: rowIndex
        };

        this.renderDialog(fieldLabel);
    }

    renderDialog(fieldLabel) {
        const { items, multiSelect } = this._dialogData;
        
        const html = `
            <div id="selectDialog" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" 
                style="animation: fadeIn 0.2s ease-out;">
                <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[85vh] flex flex-col"
                    style="animation: slideUp 0.3s ease-out;" onclick="event.stopPropagation()">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">${fieldLabel}</h3>
                        ${multiSelect ? `
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="spreadsheetHelper.selectAll()" 
                                    class="px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                    All
                                </button>
                                <button type="button" onclick="spreadsheetHelper.deselectAll()"
                                    class="px-3 py-1 text-xs font-medium border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                    None
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    
                    <!-- Search -->
                    <div class="p-4 border-b">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" id="dialogSearch" placeholder="Search..."
                                oninput="spreadsheetHelper.filterItems(this.value)"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                        </div>
                    </div>
                    
                    <!-- Items List -->
                    <div id="dialogItems" class="flex-1 overflow-y-auto p-2" style="min-height: 200px; max-height: 400px;">
                        ${this.renderItems(items, multiSelect)}
                    </div>
                    
                    <!-- Selected Preview -->
                    ${multiSelect ? `
                        <div class="px-4 py-3 bg-gray-50 border-t border-b">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-600">Selected</span>
                                <span id="selectedCount" class="text-xs font-semibold text-gray-800">0</span>
                            </div>
                            <div id="selectedPreview" class="text-sm text-gray-900 font-mono break-all" 
                                style="max-height: 60px; overflow-y: auto;">(none)</div>
                        </div>
                    ` : ''}
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-end gap-2 p-4 bg-gray-50">
                        <button type="button" onclick="spreadsheetHelper.closeDialog()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="button" onclick="spreadsheetHelper.saveSelection()"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-900 transition-colors shadow-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        
        // Add event listeners
        this.attachEventListeners();
        
        // Focus search
        setTimeout(() => {
            document.getElementById('dialogSearch')?.focus();
        }, 100);
        
        // Update preview
        this.updatePreview();
    }

    renderItems(items, multiSelect) {
        if (!items || items.length === 0) {
            return `
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm">No items available</p>
                </div>
            `;
        }

        return items.map((item, index) => `
            <label data-item-index="${index}" data-value="${item.value}" data-label="${item.label.toLowerCase()}"
                class="dialog-item flex items-center gap-3 p-3 rounded-md hover:bg-gray-50 cursor-pointer transition-colors border border-transparent ${item.selected ? 'bg-gray-100 border-gray-300' : ''}">
                <input type="${multiSelect ? 'checkbox' : 'radio'}" 
                    ${item.selected ? 'checked' : ''}
                    data-item-index="${index}"
                    class="h-4 w-4 text-gray-800 border-gray-300 rounded focus:ring-gray-500 cursor-pointer">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">${item.label}</div>
                    <div class="text-xs text-gray-500">#${item.value}</div>
                </div>
            </label>
        `).join('');
    }

    attachEventListeners() {
        // ESC key
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                this.closeDialog();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);

        // Click outside
        const dialog = document.getElementById('selectDialog');
        if (dialog) {
            dialog.addEventListener('click', (e) => {
                if (e.target.id === 'selectDialog') {
                    this.closeDialog();
                }
            });
        }

        // Event delegation for label/checkbox clicks
        const itemsContainer = document.getElementById('dialogItems');
        if (itemsContainer) {
            itemsContainer.addEventListener('click', (e) => {
                const label = e.target.closest('.dialog-item');
                if (!label) return;

                const index = parseInt(label.getAttribute('data-item-index'));
                if (isNaN(index)) return;

                // If clicking directly on checkbox/radio, prevent default and handle manually
                if (e.target.type === 'checkbox' || e.target.type === 'radio') {
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleItem(index);
                } else {
                    // Clicking on label text - toggle the item
                    e.preventDefault();
                    e.stopPropagation();
                    this.toggleItem(index);
                }
            });
        }
    }

    toggleItem(index) {
        if (!this._dialogData) return;
        
        const item = this._dialogData.items[index];
        if (!item) return;

        if (this._dialogData.multiSelect) {
            item.selected = !item.selected;
        } else {
            // Single select
            this._dialogData.items.forEach((i, idx) => i.selected = idx === index);
        }

        // Update checkbox/radio state directly without full re-render for better UX
        const label = document.querySelector(`.dialog-item[data-item-index="${index}"]`);
        if (label) {
            const input = label.querySelector('input[type="checkbox"], input[type="radio"]');
            if (input) {
                input.checked = item.selected;
            }
            
            // Update label styling
            if (item.selected) {
                label.classList.add('bg-gray-100', 'border-gray-300');
                label.classList.remove('border-transparent');
            } else {
                label.classList.remove('bg-gray-100', 'border-gray-300');
                label.classList.add('border-transparent');
            }
        }

        this.updatePreview();
    }

    selectAll() {
        if (!this._dialogData) return;
        
        // Get visible items
        const visibleItems = document.querySelectorAll('.dialog-item:not([style*="display: none"])');
        visibleItems.forEach(el => {
            const index = parseInt(el.getAttribute('data-item-index'));
            this._dialogData.items[index].selected = true;
        });

        // Re-render
        const itemsContainer = document.getElementById('dialogItems');
        if (itemsContainer) {
            itemsContainer.innerHTML = this.renderItems(this._dialogData.items, this._dialogData.multiSelect);
        }

        this.updatePreview();
    }

    deselectAll() {
        if (!this._dialogData) return;
        
        this._dialogData.items.forEach(item => item.selected = false);

        // Re-render
        const itemsContainer = document.getElementById('dialogItems');
        if (itemsContainer) {
            itemsContainer.innerHTML = this.renderItems(this._dialogData.items, this._dialogData.multiSelect);
        }

        this.updatePreview();
    }

    filterItems(query) {
        if (!query) query = '';
        const lowerQuery = query.toLowerCase();
        
        const items = document.querySelectorAll('.dialog-item');
        items.forEach(item => {
            const label = item.getAttribute('data-label') || '';
            const value = item.getAttribute('data-value') || '';
            
            if (label.includes(lowerQuery) || value.includes(lowerQuery)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    updatePreview() {
        if (!this._dialogData || !this._dialogData.multiSelect) return;

        const selectedItems = this._dialogData.items.filter(item => item.selected);
        const count = selectedItems.length;
        const values = selectedItems.map(item => item.value).join(', ');

        const countEl = document.getElementById('selectedCount');
        const previewEl = document.getElementById('selectedPreview');

        if (countEl) countEl.textContent = count;
        if (previewEl) previewEl.textContent = values || '(none)';
    }

    saveSelection() {
        if (!this._dialogData) return;

        const selectedItems = this._dialogData.items.filter(item => item.selected);
        const values = selectedItems.map(item => item.value);
        const result = this._dialogData.multiSelect ? values.join(',') : (values[0] || '');

        if (this._currentCallback && typeof this._currentCallback === 'function') {
            this._currentCallback(this._dialogData.colIndex, this._dialogData.rowIndex, result);
        }

        this.closeDialog();
    }

    closeDialog() {
        const dialog = document.getElementById('selectDialog');
        if (dialog) {
            dialog.remove();
        }
        this._dialogData = null;
        this._currentCallback = null;
    }

    /**
     * Show dialog for Terms (multi-select)
     */
    showTermsDialog(colIndex, rowIndex, termType, termsAvailable, currentValue, onSave) {
        this.showSelectDialog({
            fieldType: 'Terms',
            fieldLabel: `Select ${termType.charAt(0).toUpperCase() + termType.slice(1)}`,
            currentValue: currentValue,
            items: termsAvailable.map(t => ({ id: t.id, name: t.name, slug: t.slug })),
            multiSelect: true,
            colIndex: colIndex,
            rowIndex: rowIndex,
            onSave: onSave
        });
    }

    /**
     * Show dialog for User (single-select)
     */
    showUserDialog(colIndex, rowIndex, currentValue, usersData, onSave) {
        this.showSelectDialog({
            fieldType: 'User',
            fieldLabel: 'Select User',
            currentValue: currentValue,
            items: usersData.map(u => ({ id: u.id, name: u.username || u.email || `User #${u.id}` })),
            multiSelect: false,
            colIndex: colIndex,
            rowIndex: rowIndex,
            onSave: onSave
        });
    }

    /**
     * Show dialog for Reference (single-select)
     */
    showReferenceDialog(colIndex, rowIndex, currentValue, referencedPosts, fieldLabel, onSave) {
        this.showSelectDialog({
            fieldType: 'Reference',
            fieldLabel: fieldLabel || 'Select Post',
            currentValue: currentValue,
            items: referencedPosts.map(p => ({ id: p.id, name: p.title || `Post #${p.id}` })),
            multiSelect: false,
            colIndex: colIndex,
            rowIndex: rowIndex,
            onSave: onSave
        });
    }
}

// CSS Animations
const styleEl = document.createElement('style');
styleEl.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(styleEl);

// Global instance
const spreadsheetHelper = new SpreadsheetHelper();
