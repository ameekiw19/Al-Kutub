/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      // Font Family - Match Android (Poppins)
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
      },
      
      // Font Size - Match Android Typography Scale
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.4sp' }],      // 12sp - bodySmall
        'sm': ['0.875rem', { lineHeight: '1.25rem', letterSpacing: '0.25sp' }], // 14sp - bodyMedium
        'base': ['1rem', { lineHeight: '1.5rem', letterSpacing: '0.5sp' }],     // 16sp - bodyLarge
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],                          // 18sp
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],                           // 20sp
        '2xl': ['1.5rem', { lineHeight: '2rem' }],                              // 24sp - headlineSmall
        '3xl': ['1.75rem', { lineHeight: '2.25rem' }],                          // 28sp - headlineMedium
        '4xl': ['2rem', { lineHeight: '2.5rem' }],                              // 32sp - headlineLarge
        '5xl': ['2.25rem', { lineHeight: '2.75rem' }],                          // 36sp - displaySmall
      },
      
      // Font Weight - Match Android
      fontWeight: {
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
      },
      
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
          light: '#76D3C6',    // TealLight - Android match
          dark: '#007265',     // TealDark - Android match
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
        
        // Al-Kutub custom colors - Match Android SharedColors.kt
        'kutub': {
          primary: '#44A194',    // TealMain - Android: 0xFF44A194
          secondary: '#2D7A6E',  // TealDark variant
          accent: '#5FB8A9',     // TealLight variant
          light: '#E0F2F1',      // TealBackground - Android: 0xFFE0F2F1 (FIXED for consistency)
          dark: '#1A4D42',       // TealDark deeper
        },
        
        // Functional colors - Match Android
        error: '#EF4444',        // ErrorRed
        success: '#22C55E',      // SuccessGreen
        warning: '#F59E0B',      // WarningAmber
        
        // Slate neutrals - Match Android
        slate: {
          50: '#F8F9FA',
          100: '#F8F9FA',
          200: '#E2E8F0',
          300: '#CBD5E1',
          400: '#94A3B8',
          500: '#BBBBBB',
          600: '#666666',
          700: '#334155',
          800: '#1E293B',
          900: '#111111',
        },
      },
      
      // Border Radius - Match Android Material 3
      borderRadius: {
        'none': '0',
        'sm': '4px',    // Small badges, tags
        'md': '8px',    // Buttons, inputs
        'lg': '12px',   // Cards
        'xl': '16px',   // Large cards
        '2xl': '24px',  // Hero sections
        'full': '9999px', // Circular
        DEFAULT: "var(--radius)",
      },
      
      // Shadows - Match Android Elevation
      boxShadow: {
        'sm': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        'DEFAULT': '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        'md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
        'lg': '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px 0 rgba(0, 0, 0, 0.05)',
        'xl': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px 0 rgba(0, 0, 0, 0.04)',
        '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        'inner': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
        
        // Dark mode shadows
        'dark-sm': '0 1px 2px rgba(0, 0, 0, 0.3)',
        'dark': '0 2px 4px rgba(0, 0, 0, 0.4)',
        'dark-md': '0 4px 8px rgba(0, 0, 0, 0.5)',
      },
      
      // Spacing - Match Android 8dp grid
      spacing: {
        'px': '1px',
        '0': '0',
        '0.5': '0.125rem',  // 2px
        '1': '0.25rem',     // 4px
        '1.5': '0.375rem',  // 6px
        '2': '0.5rem',      // 8px
        '2.5': '0.625rem',  // 10px
        '3': '0.75rem',     // 12px
        '3.5': '0.875rem',  // 14px
        '4': '1rem',        // 16px
        '5': '1.25rem',     // 20px
        '6': '1.5rem',      // 24px
        '7': '1.75rem',     // 28px
        '8': '2rem',        // 32px
        '9': '2.25rem',     // 36px
        '10': '2.5rem',     // 40px
        '11': '2.75rem',    // 44px
        '12': '3rem',       // 48px
        '14': '3.5rem',     // 56px
        '16': '4rem',       // 64px
        '20': '5rem',       // 80px
        '24': '6rem',       // 96px
        '28': '7rem',       // 112px
        '32': '8rem',       // 128px
        '36': '9rem',       // 144px
        '40': '10rem',      // 160px
        '44': '11rem',      // 176px
        '48': '12rem',      // 192px
        '52': '13rem',      // 208px
        '56': '14rem',      // 224px
        '60': '15rem',      // 240px
        '64': '16rem',      // 256px
        '72': '18rem',      // 288px
        '80': '20rem',      // 320px
        '96': '24rem',      // 384px
      },
      
      // Height - Match Android components
      height: {
        '10': '2.5rem',     // 40px - Button height
        '14': '3.5rem',     // 56px - Input height
      },
      
      // Width - Match Android components
      width: {
        '11': '2.75rem',    // 44px - Min touch target
        '44': '11rem',      // 176px
      },
      
      // Min height/width - Match Android touch targets
      minHeight: {
        '11': '2.75rem',    // 44px - Min touch target
      },
      minWidth: {
        '11': '2.75rem',    // 44px - Min touch target
      },
      
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
        "alert-dialog-enter": {
          from: { opacity: "0", transform: "translate(-50%, -48%) scale(0.96)" },
          to: { opacity: "1", transform: "translate(-50%, -50%) scale(1)" },
        },
        "alert-dialog-exit": {
          from: { opacity: "1", transform: "translate(-50%, -50%) scale(1)" },
          to: { opacity: "0", transform: "translate(-50%, -48%) scale(0.96)" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
      },
    },
  },
  plugins: [
    require("tailwindcss-animate"),
    require("@tailwindcss/forms"),
    require("@tailwindcss/typography"),
  ],
};
