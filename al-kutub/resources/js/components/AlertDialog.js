/**
 * Alert Dialog Component - Alpine.js version
 * Based on shadcn/ui Alert Dialog
 * 
 * Usage:
 * <div x-data="alertDialog()">
 *     <button @click="open()">Open Dialog</button>
 *     <div x-show="isOpen" x-cloak>
 *         <div x-dialog-overlay></div>
 *         <div x-dialog-content>
 *             <div x-dialog-header>
 *                 <h2 x-dialog-title>Title</h2>
 *                 <p x-dialog-description>Description</p>
 *             </div>
 *             <div x-dialog-footer>
 *                 <button @click="close()">Cancel</button>
 *                 <button @click="confirm()">Continue</button>
 *             </div>
 *         </div>
 *     </div>
 * </div>
 */

export function alertDialog(options = {}) {
    return {
        isOpen: false,
        isConfirming: false,
        isLoading: false,
        title: options.title || '',
        description: options.description || '',
        confirmText: options.confirmText || 'Continue',
        cancelText: options.cancelText || 'Cancel',
        destructive: options.destructive || false,
        
        // Callbacks
        onConfirm: options.onConfirm || null,
        onCancel: options.onCancel || null,
        onOpenChange: options.onOpenChange || null,
        
        init() {
            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
            
            // Prevent body scroll when dialog is open
            this.$watch('isOpen', (value) => {
                if (value) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
                
                if (this.onOpenChange) {
                    this.onOpenChange(value);
                }
            });
        },
        
        open() {
            this.isOpen = true;
        },
        
        close() {
            this.isOpen = false;
            this.isConfirming = false;
            this.isLoading = false;
            
            if (this.onCancel) {
                this.onCancel();
            }
        },
        
        async confirm() {
            this.isConfirming = true;
            this.isLoading = true;
            
            try {
                if (this.onConfirm) {
                    await Promise.resolve(this.onConfirm());
                }
                this.close();
            } catch (error) {
                console.error('Dialog confirm error:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        setOpen(value) {
            if (value) {
                this.open();
            } else {
                this.close();
            }
        },
        
        toggle() {
            this.isOpen = !this.isOpen;
        }
    };
}

/**
 * Alert Dialog with custom content
 */
export function alertDialogCustom() {
    return {
        ...alertDialog(),
        
        // Additional custom properties
        customContent: null,
        actions: [],
        
        setCustomContent(content) {
            this.customContent = content;
        },
        
        addAction(label, callback, variant = 'default') {
            this.actions.push({ label, callback, variant });
        }
    };
}
