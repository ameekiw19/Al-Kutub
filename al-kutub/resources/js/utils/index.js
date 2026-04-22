/**
 * Utility function to merge class names with Tailwind CSS
 * Combines clsx for conditional classes and tailwind-merge for conflict resolution
 * 
 * @param  {...string} classes - Class names to merge
 * @returns {string} - Merged class names
 */

import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
