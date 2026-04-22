@php
use App\Http\Constants\SharedColors;

$lightTheme = SharedColors::getThemeConfig('light');
$darkTheme = SharedColors::getThemeConfig('dark');
@endphp
<style>
    :root {
        --ak-font-family-primary: 'Poppins', sans-serif;

        --ak-radius-sm: 8px;
        --ak-radius-md: 12px;
        --ak-radius-lg: 16px;
        --ak-radius-xl: 20px;

        --ak-spacing-xs: 4px;
        --ak-spacing-sm: 8px;
        --ak-spacing-md: 16px;
        --ak-spacing-lg: 24px;
        --ak-spacing-xl: 32px;
    }

    :root,
    [data-theme="light"] {
        --ak-color-primary: {{ $lightTheme['primary'] }};
        --ak-color-on-primary: {{ $lightTheme['on-primary'] }};
        --ak-color-primary-container: {{ $lightTheme['primary-container'] }};
        --ak-color-on-primary-container: {{ $lightTheme['on-primary-container'] }};
        --ak-color-secondary: {{ $lightTheme['secondary'] }};
        --ak-color-on-secondary: {{ $lightTheme['on-secondary'] }};
        --ak-color-secondary-container: {{ $lightTheme['secondary-container'] }};
        --ak-color-on-secondary-container: {{ $lightTheme['on-secondary-container'] }};
        --ak-color-tertiary: {{ $lightTheme['tertiary'] }};
        --ak-color-on-tertiary: {{ $lightTheme['on-tertiary'] }};
        --ak-color-tertiary-container: {{ $lightTheme['tertiary-container'] }};
        --ak-color-background: {{ $lightTheme['background'] }};
        --ak-color-on-background: #111111;
        --ak-color-surface: {{ $lightTheme['surface'] }};
        --ak-color-on-surface: {{ $lightTheme['on-surface'] }};
        --ak-color-surface-variant: {{ $lightTheme['surface-variant'] }};
        --ak-color-on-surface-variant: {{ $lightTheme['on-surface-variant'] }};
        --ak-color-outline: {{ $lightTheme['outline'] }};
        --ak-color-on-outline: {{ $lightTheme['on-outline'] }};
        --ak-color-error: {{ $lightTheme['error'] }};
        --ak-color-on-error: {{ $lightTheme['on-error'] }};
        --ak-color-success: {{ SharedColors::SUCCESS_GREEN }};
        --ak-color-warning: {{ SharedColors::WARNING_AMBER }};
    }

    [data-theme="dark"] {
        --ak-color-primary: {{ $darkTheme['primary'] }};
        --ak-color-on-primary: {{ $darkTheme['on-primary'] }};
        --ak-color-primary-container: {{ $darkTheme['primary-container'] }};
        --ak-color-on-primary-container: {{ $darkTheme['on-primary-container'] }};
        --ak-color-secondary: {{ $darkTheme['secondary'] }};
        --ak-color-on-secondary: {{ $darkTheme['on-secondary'] }};
        --ak-color-secondary-container: {{ $darkTheme['secondary-container'] }};
        --ak-color-on-secondary-container: {{ $darkTheme['on-secondary-container'] }};
        --ak-color-tertiary: {{ $darkTheme['tertiary'] }};
        --ak-color-on-tertiary: {{ $darkTheme['on-tertiary'] }};
        --ak-color-tertiary-container: {{ $darkTheme['tertiary-container'] }};
        --ak-color-background: {{ $darkTheme['background'] }};
        --ak-color-on-background: {{ $darkTheme['on-background'] }};
        --ak-color-surface: {{ $darkTheme['surface'] }};
        --ak-color-on-surface: {{ $darkTheme['on-surface'] }};
        --ak-color-surface-variant: {{ $darkTheme['surface-variant'] }};
        --ak-color-on-surface-variant: #BBBBBB;
        --ak-color-outline: {{ $darkTheme['outline'] }};
        --ak-color-on-outline: {{ $darkTheme['on-outline'] }};
        --ak-color-error: {{ $darkTheme['error'] }};
        --ak-color-on-error: {{ $darkTheme['on-error'] }};
        --ak-color-success: {{ SharedColors::SUCCESS_GREEN }};
        --ak-color-warning: {{ SharedColors::WARNING_AMBER }};
    }

    :root {
        --primary-color: var(--ak-color-primary);
        --primary-dark: var(--ak-color-primary-container);
        --primary-light: var(--ak-color-tertiary);
        --secondary-color: var(--ak-color-surface);
        --accent-color: var(--ak-color-warning);
        --text-color: var(--ak-color-on-surface);
        --background-color: var(--ak-color-background);
        --card-bg: var(--ak-color-surface);
        --border-color: var(--ak-color-outline);
        --light-text: var(--ak-color-on-surface-variant);

        --primary: var(--ak-color-primary);
        --accent: var(--ak-color-warning);
        --text-dark: var(--ak-color-on-surface);
        --text-light: var(--ak-color-on-surface-variant);
        --bg-light: var(--ak-color-background);
        --white: var(--ak-color-on-primary);
        --danger: var(--ak-color-error);
        --border-radius: var(--ak-radius-md);
        --bg-color: var(--ak-color-background);
    }
</style>
<script>
    (function () {
        var savedTheme = localStorage.getItem('theme');
        var theme = savedTheme === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.cookie = 'theme_mode=' + theme + '; path=/';
    })();
</script>
