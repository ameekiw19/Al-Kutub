/**
 * Al-Kutub UI Components
 * Main entry point for all UI components
 * Based on shadcn/ui components, implemented with Alpine.js + Tailwind CSS
 */

// Import Alpine.js
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Import components
import { accordion } from './components/Accordion';
import { alertDialog, alertDialogCustom } from './components/AlertDialog';
import { alert, alertManager } from './components/Alert';
import { avatar, avatarGroup } from './components/Avatar';
import { badge, badgeWithCount, statusBadge } from './components/Badge';
import { breadcrumb, breadcrumbList, breadcrumbItem, breadcrumbLink, breadcrumbPage, breadcrumbSeparator, breadcrumbEllipsis } from './components/Breadcrumb';
import { aspectRatio, ASPECT_RATIOS, responsiveAspectRatio } from './components/AspectRatio';
import { button, buttonWithIcon, buttonGroup } from './components/Button';

/**
 * Register Alpine.js plugins
 */
Alpine.plugin(collapse);

/**
 * Register all Alpine.js components globally
 */
function registerComponents() {
    // Accordion
    Alpine.data('accordion', accordion);
    
    // Alert Dialog
    Alpine.data('alertDialog', alertDialog);
    Alpine.data('alertDialogCustom', alertDialogCustom);
    
    // Alert
    Alpine.data('alert', alert);
    Alpine.data('alertManager', alertManager);
    
    // Avatar
    Alpine.data('avatar', avatar);
    Alpine.data('avatarGroup', avatarGroup);
    
    // Badge
    Alpine.data('badge', badge);
    Alpine.data('badgeWithCount', badgeWithCount);
    Alpine.data('statusBadge', statusBadge);
    
    // Breadcrumb
    Alpine.data('breadcrumb', breadcrumb);
    Alpine.data('breadcrumbList', breadcrumbList);
    Alpine.data('breadcrumbItem', breadcrumbItem);
    Alpine.data('breadcrumbLink', breadcrumbLink);
    Alpine.data('breadcrumbPage', breadcrumbPage);
    Alpine.data('breadcrumbSeparator', breadcrumbSeparator);
    Alpine.data('breadcrumbEllipsis', breadcrumbEllipsis);
    
    // Aspect Ratio
    Alpine.data('aspectRatio', aspectRatio);
    Alpine.data('responsiveAspectRatio', responsiveAspectRatio);
    
    // Button
    Alpine.data('button', button);
    Alpine.data('buttonWithIcon', buttonWithIcon);
    Alpine.data('buttonGroup', buttonGroup);
}

/**
 * Custom Alpine.js directives
 */
function registerDirectives() {
    // Focus directive
    Alpine.directive('focus', (el) => {
        el.focus();
    });
    
    // Click outside directive
    Alpine.directive('click-outside', (el, { expression }, { evaluate }) => {
        el._x_clickOutsideHandler = (event) => {
            if (!el.contains(event.target)) {
                evaluate(expression);
            }
        };
        document.addEventListener('click', el._x_clickOutsideHandler);
    });
    
    // Auto-resize textarea directive
    Alpine.directive('auto-resize', (el) => {
        const resize = () => {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        };
        el.addEventListener('input', resize);
        resize();
    });
}

/**
 * Initialize everything when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Register components and directives
    registerComponents();
    registerDirectives();
    
    // Start Alpine.js
    window.Alpine = Alpine;
    Alpine.start();
    
    console.log('✅ Al-Kutub UI Components loaded successfully!');
});

/**
 * Export components for manual usage
 */
export {
    accordion,
    alertDialog,
    alertDialogCustom,
    alert,
    alertManager,
    avatar,
    avatarGroup,
    badge,
    badgeWithCount,
    statusBadge,
    breadcrumb,
    breadcrumbList,
    breadcrumbItem,
    breadcrumbLink,
    breadcrumbPage,
    breadcrumbSeparator,
    breadcrumbEllipsis,
    aspectRatio,
    ASPECT_RATIOS,
    responsiveAspectRatio,
    button,
    buttonWithIcon,
    buttonGroup
};

/**
 * Export Alpine instance for external usage
 */
export { Alpine };
