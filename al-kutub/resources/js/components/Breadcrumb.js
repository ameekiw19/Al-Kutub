/**
 * Breadcrumb Component - Alpine.js version
 * Based on shadcn/ui Breadcrumb
 * 
 * Usage:
 * <div x-data="breadcrumb()">
 *     <nav x-breadcrumb>
 *         <ol x-breadcrumb-list>
 *             <li x-breadcrumb-item>
 *                 <a x-breadcrumb-link href="/">Home</a>
 *             </li>
 *             <li x-breadcrumb-separator></li>
 *             <li x-breadcrumb-item>
 *                 <span x-breadcrumb-page>Current</span>
 *             </li>
 *         </ol>
 *     </nav>
 * </div>
 */

export function breadcrumb(options = {}) {
    return {
        items: options.items || [],
        separator: options.separator || '/',
        maxItems: options.maxItems || 5,
        showEllipsis: options.showEllipsis !== undefined ? options.showEllipsis : true,
        
        getVisibleItems() {
            if (this.items.length <= this.maxItems) {
                return this.items;
            }
            
            // Show first, last, and current with ellipsis
            const first = this.items[0];
            const last = this.items[this.items.length - 1];
            const middle = this.items.slice(1, -1);
            
            return [first, { ellipsis: true }, ...middle.slice(-1), last];
        },
        
        getSeparatorIcon() {
            return this.separator;
        }
    };
}

/**
 * Breadcrumb List Component
 */
export function breadcrumbList() {
    return {
        classes: 'flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5'
    };
}

/**
 * Breadcrumb Item Component
 */
export function breadcrumbItem() {
    return {
        classes: 'inline-flex items-center gap-1.5'
    };
}

/**
 * Breadcrumb Link Component
 */
export function breadcrumbLink(options = {}) {
    return {
        href: options.href || '#',
        active: options.active || false,
        
        getClasses() {
            return this.active 
                ? 'text-foreground font-normal' 
                : 'hover:text-foreground transition-colors';
        }
    };
}

/**
 * Breadcrumb Page Component (current page, not clickable)
 */
export function breadcrumbPage() {
    return {
        classes: 'text-foreground font-normal',
        attributes: {
            'role': 'link',
            'aria-disabled': 'true',
            'aria-current': 'page'
        }
    };
}

/**
 * Breadcrumb Separator Component
 */
export function breadcrumbSeparator(options = {}) {
    return {
        icon: options.icon || 'chevron',
        
        getIcon() {
            switch (this.icon) {
                case 'slash':
                    return '/';
                case 'backslash':
                    return '\\';
                case 'arrow':
                    return '→';
                case 'chevron':
                default:
                    return 'chevron';
            }
        }
    };
}

/**
 * Breadcrumb Ellipsis Component (for truncated breadcrumbs)
 */
export function breadcrumbEllipsis() {
    return {
        classes: 'flex size-9 items-center justify-center',
        label: 'More'
    };
}
