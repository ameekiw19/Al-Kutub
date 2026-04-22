/**
 * Accordion Component - Alpine.js version
 * Based on shadcn/ui Accordion
 * 
 * Usage:
 * <div x-data="accordion()" x-ref="accordion">
 *     <div x-accordion-item>
 *         <button x-accordion-trigger>Trigger</button>
 *         <div x-accordion-content>Content</div>
 *     </div>
 * </div>
 */

export function accordion(options = {}) {
    return {
        activeItem: null,
        collapsible: options.collapsible || false,
        type: options.type || 'single', // 'single' | 'multiple'
        multiple: options.multiple || false,
        
        init() {
            // Watch for changes in active items
            this.$watch('activeItem', (value) => {
                this.$dispatch('accordion-change', { value });
            });
        },
        
        toggle(itemId) {
            if (this.type === 'multiple') {
                // Multiple mode - toggle array
                if (!Array.isArray(this.activeItem)) {
                    this.activeItem = [];
                }
                
                const index = this.activeItem.indexOf(itemId);
                if (index > -1) {
                    this.activeItem.splice(index, 1);
                } else {
                    this.activeItem.push(itemId);
                }
            } else {
                // Single mode
                if (this.activeItem === itemId && this.collapsible) {
                    this.activeItem = null;
                } else {
                    this.activeItem = itemId;
                }
            }
        },
        
        isOpen(itemId) {
            if (this.type === 'multiple' && Array.isArray(this.activeItem)) {
                return this.activeItem.includes(itemId);
            }
            return this.activeItem === itemId;
        },
        
        open(itemId) {
            if (this.type === 'multiple') {
                if (!Array.isArray(this.activeItem)) {
                    this.activeItem = [];
                }
                if (!this.activeItem.includes(itemId)) {
                    this.activeItem.push(itemId);
                }
            } else {
                this.activeItem = itemId;
            }
        },
        
        close(itemId) {
            if (this.type === 'multiple' && Array.isArray(this.activeItem)) {
                const index = this.activeItem.indexOf(itemId);
                if (index > -1) {
                    this.activeItem.splice(index, 1);
                }
            } else if (!this.collapsible) {
                this.activeItem = null;
            }
        },
        
        closeAll() {
            this.activeItem = this.type === 'multiple' ? [] : null;
        }
    };
}

/**
 * Alpine.js directive for accordion item
 */
export function registerAccordionDirectives(Alpine) {
    Alpine.directive('accordion', (el, { expression }) => {
        // Parent accordion container
    });
    
    Alpine.directive('accordion-item', (el, { expression }) => {
        // Individual accordion item
        el.setAttribute('data-state', Alpine.evaluate(el, '$data.isOpen(' + expression + ')') ? 'open' : 'closed');
    });
    
    Alpine.directive('accordion-trigger', (el, { expression }) => {
        // Trigger button
        el.addEventListener('click', () => {
            Alpine.evaluate(el, '$data.toggle(' + expression + ')');
        });
    });
    
    Alpine.directive('accordion-content', (el, { expression }) => {
        // Content panel with collapse animation
    });
}
