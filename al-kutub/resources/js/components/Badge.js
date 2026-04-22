/**
 * Badge Component - Alpine.js version
 * Based on shadcn/ui Badge
 * 
 * Usage:
 * <div x-data="badge({ variant: 'default' })">
 *     <span x-badge>Badge</span>
 * </div>
 */

export function badge(options = {}) {
    return {
        variant: options.variant || 'default', // 'default' | 'secondary' | 'destructive' | 'outline'
        content: options.content || '',
        clickable: options.clickable || false,
        href: options.href || null,
        
        getVariantClasses() {
            const baseClasses = 'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium whitespace-nowrap shrink-0 gap-1';
            
            switch (this.variant) {
                case 'secondary':
                    return `${baseClasses} border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80`;
                case 'destructive':
                    return `${baseClasses} border-transparent bg-destructive text-white hover:bg-destructive/90`;
                case 'outline':
                    return `${baseClasses} text-foreground border-border hover:bg-accent hover:text-accent-foreground`;
                case 'success':
                    return `${baseClasses} border-transparent bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100`;
                case 'warning':
                    return `${baseClasses} border-transparent bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100`;
                case 'info':
                    return `${baseClasses} border-transparent bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100`;
                default:
                    return `${baseClasses} border-transparent bg-primary text-primary-foreground hover:bg-primary/90`;
            }
        },
        
        getAsLink() {
            return this.href && this.clickable;
        }
    };
}

/**
 * Badge with count/indicator
 */
export function badgeWithCount(options = {}) {
    return {
        ...badge(options),
        count: options.count || 0,
        maxCount: options.maxCount || 99,
        showCount: options.showCount !== undefined ? options.showCount : true,
        
        getDisplayCount() {
            if (this.count > this.maxCount) {
                return `${this.maxCount}+`;
            }
            return this.count.toString();
        },
        
        hasCount() {
            return this.showCount && this.count > 0;
        }
    };
}

/**
 * Status Badge Component
 */
export function statusBadge(options = {}) {
    return {
        status: options.status || 'pending', // 'pending' | 'active' | 'inactive' | 'archived'
        showDot: options.showDot !== undefined ? options.showDot : true,
        
        getStatusClasses() {
            const baseClasses = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium';
            
            switch (this.status) {
                case 'active':
                case 'success':
                case 'published':
                    return `${baseClasses} bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100`;
                case 'inactive':
                case 'draft':
                    return `${baseClasses} bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100`;
                case 'pending':
                case 'review':
                    return `${baseClasses} bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100`;
                case 'error':
                case 'rejected':
                    return `${baseClasses} bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100`;
                default:
                    return `${baseClasses} bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100`;
            }
        },
        
        getDotColor() {
            switch (this.status) {
                case 'active':
                case 'success':
                case 'published':
                    return 'bg-green-500';
                case 'inactive':
                case 'draft':
                    return 'bg-gray-500';
                case 'pending':
                case 'review':
                    return 'bg-yellow-500';
                case 'error':
                case 'rejected':
                    return 'bg-red-500';
                default:
                    return 'bg-blue-500';
            }
        }
    };
}
