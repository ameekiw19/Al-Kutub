/**
 * Button Component - Alpine.js version
 * Based on shadcn/ui Button
 * 
 * Usage:
 * <button x-data="button({ variant: 'primary', size: 'md' })">
 *     Click me
 * </button>
 */

export function button(options = {}) {
    return {
        variant: options.variant || 'default', // 'default' | 'secondary' | 'destructive' | 'outline' | 'ghost' | 'link'
        size: options.size || 'default', // 'default' | 'sm' | 'lg' | 'icon'
        disabled: options.disabled || false,
        loading: options.loading || false,
        href: options.href || null,
        
        getVariantClasses() {
            const baseClasses = 'inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background';
            
            switch (this.variant) {
                case 'secondary':
                    return `${baseClasses} bg-secondary text-secondary-foreground hover:bg-secondary/80`;
                case 'destructive':
                    return `${baseClasses} bg-destructive text-destructive-foreground hover:bg-destructive/90`;
                case 'outline':
                    return `${baseClasses} border border-input hover:bg-accent hover:text-accent-foreground`;
                case 'ghost':
                    return `${baseClasses} hover:bg-accent hover:text-accent-foreground`;
                case 'link':
                    return `${baseClasses} text-primary underline-offset-4 hover:underline`;
                default:
                    return `${baseClasses} bg-primary text-primary-foreground hover:bg-primary/90`;
            }
        },
        
        getSizeClasses() {
            switch (this.size) {
                case 'sm':
                    return 'h-9 rounded-md px-3';
                case 'lg':
                    return 'h-11 rounded-md px-8';
                case 'icon':
                    return 'h-10 w-10';
                default:
                    return 'h-10 px-4 py-2';
            }
        },
        
        getClasses() {
            return `${this.getVariantClasses()} ${this.getSizeClasses()}`;
        }
    };
}

/**
 * Button with icon
 */
export function buttonWithIcon(options = {}) {
    return {
        ...button(options),
        icon: options.icon || null,
        iconPosition: options.iconPosition || 'left', // 'left' | 'right'
        
        hasIcon() {
            return this.icon && !this.loading;
        },
        
        isLoading() {
            return this.loading;
        },
        
        getIconClasses() {
            return 'mr-2 h-4 w-4';
        }
    };
}

/**
 * Button Group
 */
export function buttonGroup(options = {}) {
    return {
        orientation: options.orientation || 'horizontal', // 'horizontal' | 'vertical'
        size: options.size || 'default',
        
        getClasses() {
            const baseClasses = 'inline-flex';
            
            if (this.orientation === 'vertical') {
                return `${baseClasses} flex-col`;
            }
            
            return `${baseClasses} flex-row`;
        }
    };
}
