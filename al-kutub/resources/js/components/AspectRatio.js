/**
 * Aspect Ratio Component - Alpine.js version
 * Based on shadcn/ui Aspect Ratio
 * 
 * Usage:
 * <div x-data="aspectRatio({ ratio: '16/9' })">
 *     <div x-aspect-ratio class="relative">
 *         <img src="image.jpg" class="absolute inset-0 size-full object-cover" />
 *     </div>
 * </div>
 */

export function aspectRatio(options = {}) {
    return {
        ratio: options.ratio || '1/1', // '1/1' | '16/9' | '4/3' | '3/2' | '3/4' | 'auto'
        
        getPaddingBottom() {
            if (this.ratio === 'auto') {
                return 'auto';
            }
            
            const [width, height] = this.ratio.split('/').map(Number);
            if (!width || !height) {
                return '100%'; // Default to 1/1
            }
            
            return `${(height / width) * 100}%`;
        },
        
        getRatioClasses() {
            switch (this.ratio) {
                case '1/1':
                    return 'aspect-square';
                case '16/9':
                    return 'aspect-video';
                case '4/3':
                    return 'aspect-[4/3]';
                case '3/2':
                    return 'aspect-[3/2]';
                case '3/4':
                    return 'aspect-[3/4]';
                case 'auto':
                    return '';
                default:
                    return 'aspect-square';
            }
        }
    };
}

/**
 * Preset aspect ratios for common use cases
 */
export const ASPECT_RATIOS = {
    SQUARE: '1/1',
    VIDEO: '16/9',
    PHOTO_LANDSCAPE: '4/3',
    PHOTO_PORTRAIT: '3/4',
    CARD: '3/2',
    AUTO: 'auto'
};

/**
 * Responsive Aspect Ratio
 */
export function responsiveAspectRatio(options = {}) {
    return {
        mobile: options.mobile || '1/1',
        tablet: options.tablet || options.mobile || '1/1',
        desktop: options.desktop || options.tablet || options.mobile || '1/1',
        
        getRatioForBreakpoint(breakpoint) {
            switch (breakpoint) {
                case 'mobile':
                    return this.mobile;
                case 'tablet':
                    return this.tablet;
                case 'desktop':
                    return this.desktop;
                default:
                    return this.mobile;
            }
        }
    };
}
