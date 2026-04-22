/**
 * Avatar Component - Alpine.js version
 * Based on shadcn/ui Avatar
 * 
 * Usage:
 * <div x-data="avatar({ src: 'image.jpg', alt: 'User Name' })">
 *     <div x-avatar>
 *         <img x-avatar-image :src="src" :alt="alt" />
 *         <span x-avatar-fallback>UN</span>
 *     </div>
 * </div>
 */

export function avatar(options = {}) {
    return {
        src: options.src || null,
        alt: options.alt || '',
        fallback: options.fallback || '',
        isLoading: true,
        hasError: false,
        size: options.size || 'md', // 'sm' | 'md' | 'lg'
        
        init() {
            // Generate fallback from alt if not provided
            if (!this.fallback && this.alt) {
                this.fallback = this.getInitials(this.alt);
            }
        },
        
        getInitials(name) {
            if (!name) return '?';
            const names = name.trim().split(' ');
            const initials = names.slice(0, 2).map(n => n[0]).join('').toUpperCase();
            return initials || '?';
        },
        
        onLoad() {
            this.isLoading = false;
            this.hasError = false;
        },
        
        onError() {
            this.isLoading = false;
            this.hasError = true;
        },
        
        showFallback() {
            return this.hasError || !this.src;
        },
        
        getSizeClasses() {
            switch (this.size) {
                case 'sm':
                    return 'size-8';
                case 'md':
                    return 'size-10';
                case 'lg':
                    return 'size-12';
                case 'xl':
                    return 'size-16';
                default:
                    return 'size-10';
            }
        }
    };
}

/**
 * Avatar Group Component
 */
export function avatarGroup(options = {}) {
    return {
        avatars: options.avatars || [],
        max: options.max || 5,
        size: options.size || 'md',
        
        getVisibleAvatars() {
            return this.avatars.slice(0, this.max);
        },
        
        getRemainingCount() {
            return Math.max(0, this.avatars.length - this.max);
        },
        
        getSizeClasses() {
            switch (this.size) {
                case 'sm':
                    return 'size-8 -ml-2 first:ml-0';
                case 'md':
                    return 'size-10 -ml-2 first:ml-0';
                case 'lg':
                    return 'size-12 -ml-3 first:ml-0';
                default:
                    return 'size-10 -ml-2 first:ml-0';
            }
        }
    };
}
