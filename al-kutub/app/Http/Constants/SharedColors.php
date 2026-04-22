<?php

namespace App\Http\Constants;

/**
 * Shared color constants for consistency between Laravel backend and Android app.
 */
class SharedColors
{
    // Primary Colors (Dark Green / Forest Green matching the Android reference)
    const TEAL_MAIN = '#1B5E3B';
    const TEAL_LIGHT = '#2D7A52';
    const TEAL_DARK = '#1A4A30';
    const TEAL_BACKGROUND = '#F4FBF7';

    // Slate Neutral Colors (mapping to Beige/Gold/Brown from Android reference)
    const SLATE_900 = '#1A2E1A'; // LoginPrimaryText
    const SLATE_800 = '#3D2C1E'; // LoginLabelText
    const SLATE_700 = '#6B5E4E'; 
    const SLATE_600 = '#8B8070'; // LoginMutedText
    const SLATE_500 = '#C8A951'; // LoginGold (used as mid-accent)
    const SLATE_400 = '#8BAD9A'; // LoginButtonDisabled (green-ish gray)
    const SLATE_300 = '#E8E3D5'; // LoginFieldBorder / LoginDivider
    const SLATE_200 = '#F0EBE0'; // LoginCardBorder
    const SLATE_100 = '#F8F5EF'; // LoginFieldBackground
    const SLATE_50 = '#FAFAF5'; // LoginBackground (Beige)

    // Background Colors
    const WHITE = '#FFFFFF';
    const BLACK = '#000000';

    // Functional Colors
    const ERROR_RED = '#EF4444';
    const SUCCESS_GREEN = '#22C55E';
    const WARNING_AMBER = '#F59E0B';

    // Dark Mode Specifics
    const DARK_BACKGROUND = '#000000';
    const DARK_SURFACE = '#121212';

    /**
     * Get all colors as grouped palette.
     */
    public static function getAllColors(): array
    {
        return [
            'primary' => [
                'main' => self::TEAL_MAIN,
                'light' => self::TEAL_LIGHT,
                'dark' => self::TEAL_DARK,
                'background' => self::TEAL_BACKGROUND,
            ],
            'slate' => [
                '900' => self::SLATE_900,
                '800' => self::SLATE_800,
                '700' => self::SLATE_700,
                '600' => self::SLATE_600,
                '500' => self::SLATE_500,
                '400' => self::SLATE_400,
                '300' => self::SLATE_300,
                '200' => self::SLATE_200,
                '100' => self::SLATE_100,
                '50' => self::SLATE_50,
            ],
            'backgrounds' => [
                'white' => self::WHITE,
                'black' => self::BLACK,
                'dark_background' => self::DARK_BACKGROUND,
                'dark_surface' => self::DARK_SURFACE,
            ],
            'functional' => [
                'error' => self::ERROR_RED,
                'success' => self::SUCCESS_GREEN,
                'warning' => self::WARNING_AMBER,
            ],
        ];
    }

    /**
     * Get color by key from grouped palette.
     */
    public static function getColor(string $colorName): string
    {
        $colors = self::getAllColors();

        foreach ($colors as $colorGroup) {
            if (isset($colorGroup[$colorName])) {
                return $colorGroup[$colorName];
            }
        }

        return self::TEAL_MAIN;
    }

    /**
     * Material-style color roles per theme.
     */
    public static function getThemeConfig(string $theme = 'light'): array
    {
        return match ($theme) {
            'dark' => [
                'primary' => self::TEAL_MAIN,
                'on-primary' => self::WHITE,
                'primary-container' => self::TEAL_DARK,
                'on-primary-container' => self::TEAL_BACKGROUND,
                'secondary' => self::SLATE_600,
                'on-secondary' => self::WHITE,
                'secondary-container' => self::SLATE_700,
                'on-secondary-container' => self::SLATE_300,
                'tertiary' => self::TEAL_LIGHT,
                'on-tertiary' => self::BLACK,
                'tertiary-container' => self::TEAL_BACKGROUND,
                'on-tertiary-container' => self::TEAL_DARK,
                'background' => self::BLACK,
                'on-background' => self::WHITE,
                'surface' => self::DARK_SURFACE,
                'on-surface' => self::WHITE,
                'surface-variant' => self::SLATE_800,
                'on-surface-variant' => self::SLATE_500,
                'outline' => self::SLATE_700,
                'on-outline' => self::SLATE_400,
                'error' => self::ERROR_RED,
                'on-error' => self::WHITE,
                'success' => self::SUCCESS_GREEN,
                'warning' => self::WARNING_AMBER,
            ],
            default => [
                'primary' => self::TEAL_MAIN,
                'on-primary' => self::WHITE,
                'primary-container' => self::TEAL_LIGHT,
                'on-primary-container' => self::TEAL_DARK,
                'secondary' => self::SLATE_700,
                'on-secondary' => self::WHITE,
                'secondary-container' => self::SLATE_300,
                'on-secondary-container' => self::SLATE_600,
                'tertiary' => self::TEAL_LIGHT,
                'on-tertiary' => self::BLACK,
                'tertiary-container' => self::TEAL_BACKGROUND,
                'on-tertiary-container' => self::TEAL_DARK,
                'background' => self::WHITE,
                'on-background' => self::SLATE_900,
                'surface' => self::SLATE_100,
                'on-surface' => self::SLATE_900,
                'surface-variant' => self::SLATE_800,
                'on-surface-variant' => self::SLATE_600,
                'outline' => self::SLATE_200,
                'on-outline' => self::SLATE_400,
                'error' => self::ERROR_RED,
                'on-error' => self::WHITE,
                'success' => self::SUCCESS_GREEN,
                'warning' => self::WARNING_AMBER,
            ],
        };
    }

    /**
     * Design-system aliases for web CSS variables.
     */
    public static function getRoleAliases(string $theme = 'light'): array
    {
        $themeConfig = self::getThemeConfig($theme);

        return [
            'primary-color' => $themeConfig['primary'],
            'primary-dark' => $themeConfig['primary-container'],
            'primary-light' => $themeConfig['tertiary'],
            'secondary-color' => $themeConfig['surface'],
            'accent-color' => $themeConfig['warning'],
            'text-color' => $themeConfig['on-surface'],
            'background-color' => $themeConfig['background'],
            'card-bg' => $themeConfig['surface'],
            'border-color' => $themeConfig['outline'],
            'light-text' => $themeConfig['on-surface-variant'],
        ];
    }
}
