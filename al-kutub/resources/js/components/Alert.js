/**
 * Alert Component - Alpine.js version
 * Based on shadcn/ui Alert
 * 
 * Usage:
 * <div x-data="alert({ variant: 'default' })">
 *     <div x-alert-icon>
 *         <svg>...</svg>
 *     </div>
 *     <div x-alert-title>Title</div>
 *     <div x-alert-description>Description</div>
 * </div>
 */

export function alert(options = {}) {
    return {
        variant: options.variant || 'default', // 'default' | 'destructive'
        visible: options.visible !== undefined ? options.visible : true,
        dismissible: options.dismissible || false,
        
        init() {
            // Auto-dismiss after timeout if provided
            if (options.autoDismiss) {
                setTimeout(() => {
                    this.dismiss();
                }, options.autoDismiss);
            }
        },
        
        dismiss() {
            this.visible = false;
            if (options.onDismiss) {
                options.onDismiss();
            }
        },
        
        getVariantClasses() {
            const baseClasses = 'relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1';
            
            switch (this.variant) {
                case 'destructive':
                    return `${baseClasses} bg-destructive/10 border-destructive/50 text-destructive dark:bg-destructive/20`;
                case 'success':
                    return `${baseClasses} bg-green-50 border-green-200 text-green-900 dark:bg-green-900/20 dark:border-green-800 dark:text-green-100`;
                case 'warning':
                    return `${baseClasses} bg-yellow-50 border-yellow-200 text-yellow-900 dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-100`;
                case 'info':
                    return `${baseClasses} bg-blue-50 border-blue-200 text-blue-900 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-100`;
                default:
                    return `${baseClasses} bg-background text-foreground`;
            }
        },
        
        getIconClasses() {
            const baseClasses = 'size-4 text-current';
            
            switch (this.variant) {
                case 'destructive':
                    return `${baseClasses} text-destructive`;
                case 'success':
                    return `${baseClasses} text-green-600 dark:text-green-400`;
                case 'warning':
                    return `${baseClasses} text-yellow-600 dark:text-yellow-400`;
                case 'info':
                    return `${baseClasses} text-blue-600 dark:text-blue-400`;
                default:
                    return baseClasses;
            }
        }
    };
}

/**
 * Alert Manager for multiple alerts
 */
export function alertManager() {
    return {
        alerts: [],
        
        add(alertData) {
            const id = Date.now().toString();
            const newAlert = {
                id,
                variant: alertData.variant || 'default',
                title: alertData.title,
                description: alertData.description,
                visible: true,
                ...alertData
            };
            
            this.alerts.push(newAlert);
            
            // Auto-dismiss if specified
            if (alertData.autoDismiss) {
                setTimeout(() => {
                    this.remove(id);
                }, alertData.autoDismiss);
            }
            
            return id;
        },
        
        remove(id) {
            const index = this.alerts.findIndex(a => a.id === id);
            if (index > -1) {
                this.alerts.splice(index, 1);
            }
        },
        
        dismiss(id) {
            this.remove(id);
        },
        
        clear() {
            this.alerts = [];
        },
        
        // Convenience methods
        success(title, description, options = {}) {
            return this.add({ variant: 'success', title, description, ...options });
        },
        
        error(title, description, options = {}) {
            return this.add({ variant: 'destructive', title, description, ...options });
        },
        
        warning(title, description, options = {}) {
            return this.add({ variant: 'warning', title, description, ...options });
        },
        
        info(title, description, options = {}) {
            return this.add({ variant: 'info', title, description, ...options });
        }
    };
}
