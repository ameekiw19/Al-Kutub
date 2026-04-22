# 🎨 AL-KUTUB DESIGN TOKENS

## Shared Design System - Laravel + Android

Dokumen ini berisi design tokens yang digunakan secara konsisten di:
- **Laravel Backend** (Web Admin + User Interface)
- **Android App** (Native Mobile)

---

## 📐 COLOR SYSTEM

### Primary Colors (Teal)

| Token | Value | Usage | Preview |
|-------|-------|-------|---------|
| `TealMain` | `#44A194` | Primary buttons, links, accents | 🟢 |
| `TealLight` | `#76D3C6` | Hover states, light accents | 🟢 |
| `TealDark` | `#007265` | Active states, dark accents | 🟢 |
| `TealBackground` | `#E0F2F1` | Background highlights | 🟢 |

### Neutral Colors (Slate)

| Token | Value | Usage |
|-------|-------|-------|
| `Slate900` | `#111111` | Primary text (Dark mode) |
| `Slate800` | `#1E293B` | Headings, dark text |
| `Slate700` | `#334155` | Secondary text (Light mode) |
| `Slate600` | `#666666` | Secondary text (Dark mode) |
| `Slate500` | `#BBBBBB` | Outlines, borders |
| `Slate400` | `#94A3B8` | Outline variants |
| `Slate300` | `#CBD5E1` | Surface (Light mode) |
| `Slate200` | `#E2E8F0` | Surface (Dark mode) |
| `Slate100` | `#F8F9FA` | Card background (Light) |
| `Slate50` | `#F8F9FA` | App background (Light) |

### Background Colors

| Token | Value | Usage |
|-------|-------|-------|
| `White` | `#FFFFFF` | Background (Light), Text (Dark) |
| `Black` | `#000000` | Background (Dark) |

### Functional Colors

| Token | Value | Usage |
|-------|-------|-------|
| `ErrorRed` | `#EF4444` | Error states, destructive actions |
| `SuccessGreen` | `#22C55E` | Success states, confirmations |
| `WarningAmber` | `#F59E0B` | Warning states, cautions |

### Dark Mode Specifics

| Token | Value | Usage |
|-------|-------|-------|
| `DarkBackground` | `#000000` | Pure black for dark mode backgrounds |
| `DarkSurface` | `#121212` | Slightly lighter for cards in dark mode |
| `DarkSurfaceVariant` | `#666666` | Secondary text in dark mode |

---

## 📝 TYPOGRAPHY

### Font Family

**Primary Font:** Poppins
- Weights: Regular (400), Medium (500), Bold (700)
- Source: Google Fonts
- Fallback: system-ui, sans-serif

### Typography Scale

| Token | Size | Line Height | Weight | Usage |
|-------|------|-------------|--------|-------|
| `displayLarge` | 57sp | 64sp | Normal | Hero sections |
| `displayMedium` | 45sp | 52sp | Normal | Page titles |
| `displaySmall` | 36sp | 44sp | Normal | Section headers |
| `headlineLarge` | 32sp | 40sp | Normal | Card titles |
| `headlineMedium` | 28sp | 36sp | Normal | Sub-headers |
| `headlineSmall` | 24sp | 32sp | Normal | Item titles |
| `titleLarge` | 22sp | 28sp | Normal | Dialog titles |
| `titleMedium` | 16sp | 24sp | Medium | Button text |
| `titleSmall` | 14sp | 20sp | Medium | Labels |
| `bodyLarge` | 16sp | 24sp | Normal | Paragraph text |
| `bodyMedium` | 14sp | 20sp | Normal | Secondary text |
| `bodySmall` | 12sp | 16sp | Normal | Captions |
| `labelLarge` | 14sp | 20sp | Medium | Form labels |
| `labelMedium` | 12sp | 16sp | Medium | Helper text |
| `labelSmall` | 11sp | 16sp | Medium | Overlines |

---

## 📏 SPACING SYSTEM

Based on **8dp grid** system

| Token | Value | Usage |
|-------|-------|-------|
| `space-1` | 4px (0.25rem) | Micro spacing |
| `space-2` | 8px (0.5rem) | Small gaps |
| `space-4` | 16px (1rem) | Standard padding |
| `space-6` | 24px (1.5rem) | Section padding |
| `space-8` | 32px (2rem) | Large gaps |
| `space-12` | 48px (3rem) | Section margins |
| `space-16` | 64px (4rem) | Page margins |

---

## 🔲 BORDER RADIUS

| Token | Value | Usage |
|-------|-------|-------|
| `sm` | 4px | Small elements (badges, tags) |
| `md` | 8px | Buttons, inputs |
| `lg` | 12px | Cards, modals |
| `xl` | 16px | Large cards, containers |
| `2xl` | 24px | Hero sections |
| `full` | 9999px | Circular elements (avatars) |

---

## 🌑 SHADOW SYSTEM

### Light Mode Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `shadow-sm` | `0 1px 2px 0 rgba(0,0,0,0.05)` | Subtle elevation |
| `shadow` | `0 1px 3px 0 rgba(0,0,0,0.1), 0 1px 2px 0 rgba(0,0,0,0.06)` | Cards |
| `shadow-md` | `0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px 0 rgba(0,0,0,0.06)` | Elevated cards |
| `shadow-lg` | `0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px 0 rgba(0,0,0,0.05)` | Modals, dialogs |
| `shadow-xl` | `0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px 0 rgba(0,0,0,0.04)` | Popovers |

### Dark Mode Shadows

In dark mode, use **lighter shadows** with lower opacity:
```css
shadow-dark-sm: 0 1px 2px rgba(0, 0, 0, 0.3)
shadow-dark: 0 2px 4px rgba(0, 0, 0, 0.4)
shadow-dark-md: 0 4px 8px rgba(0, 0, 0, 0.5)
```

---

## 🎨 COMPONENT STYLES

### Buttons

#### Primary Button
```css
.btn-primary {
  @apply bg-primary text-white;
  @apply h-10 px-4 py-2;
  @apply rounded-full;
  @apply font-medium text-sm;
  @apply hover:bg-primary/90;
  @apply transition-colors duration-200;
}
```

**Android Equivalent:**
```kotlin
Button(
    modifier = Modifier
        .height(40.dp)
        .padding(horizontal = 16.dp),
    colors = ButtonDefaults.buttonColors(
        containerColor = MaterialTheme.colorScheme.primary
    ),
    shape = RoundedCornerShape(20.dp)
)
```

#### Secondary Button
```css
.btn-secondary {
  @apply bg-secondary text-secondary-foreground;
  @apply h-10 px-4 py-2;
  @apply rounded-full;
  @apply font-medium text-sm;
  @apply hover:bg-secondary/80;
}
```

#### Outline Button
```css
.btn-outline {
  @apply border border-input;
  @apply bg-transparent;
  @apply h-10 px-4 py-2;
  @apply rounded-full;
  @apply font-medium text-sm;
  @apply hover:bg-accent hover:text-accent-foreground;
}
```

---

### Cards

#### Standard Card
```css
.card {
  @apply bg-card text-card-foreground;
  @apply rounded-xl;
  @apply border;
  @apply shadow-sm;
  @apply p-6;
}
```

**Android Equivalent:**
```kotlin
Surface(
    modifier = Modifier
        .fillMaxWidth()
        .padding(16.dp),
    shape = RoundedCornerShape(12.dp),
    color = MaterialTheme.colorScheme.surface,
    tonalElevation = 1.dp
) {
    Column(modifier = Modifier.padding(16.dp)) {
        // Content
    }
}
```

---

### Input Fields

#### Text Input
```css
.input {
  @apply h-14 px-4;
  @apply rounded-lg;
  @apply border border-input;
  @apply bg-background;
  @apply text-sm;
  @apply focus:outline-none focus:ring-2 focus:ring-ring;
  @apply placeholder:text-muted-foreground;
}
```

**Android Equivalent:**
```kotlin
OutlinedTextField(
    value = text,
    onValueChange = { text = it },
    modifier = Modifier
        .fillMaxWidth()
        .height(56.dp),
    shape = RoundedCornerShape(8.dp),
    colors = OutlinedTextFieldDefaults.colors(
        focusedBorderColor = MaterialTheme.colorScheme.primary
    )
)
```

---

### Badges

#### Default Badge
```css
.badge {
  @apply inline-flex items-center;
  @apply rounded-md;
  @apply border;
  @apply px-2 py-0.5;
  @apply text-xs font-medium;
}
```

#### Status Badge
```css
.badge-success {
  @apply bg-green-100 text-green-800;
  @apply border-transparent;
}

.badge-warning {
  @apply bg-yellow-100 text-yellow-800;
  @apply border-transparent;
}

.badge-error {
  @apply bg-red-100 text-red-800;
  @apply border-transparent;
}
```

---

## 🌗 DARK MODE MAPPING

| Token | Light Mode | Dark Mode |
|-------|-----------|-----------|
| `background` | `#FFFFFF` | `#000000` |
| `surface` | `#F8F9FA` | `#121212` |
| `card` | `#FFFFFF` | `#1E1E1E` |
| `primary` | `#44A194` | `#44A194` |
| `onPrimary` | `#FFFFFF` | `#FFFFFF` |
| `secondary` | `#F1F5F9` | `#334155` |
| `muted` | `#F1F5F9` | `#334155` |
| `muted-foreground` | `#64748B` | `#94A3B8` |
| `border` | `#E2E8F0` | `#334155` |
| `input` | `#E2E8F0` | `#334155` |

---

## 📱 BREAKPOINTS

| Name | Min Width | Usage |
|------|-----------|-------|
| `sm` | 640px | Mobile landscape |
| `md` | 768px | Tablets |
| `lg` | 1024px | Laptops |
| `xl` | 1280px | Desktops |
| `2xl` | 1400px | Large screens |

---

## 🎯 IMPLEMENTATION GUIDELINES

### 1. **Consistency is Key**
- Selalu gunakan design tokens, bukan hard-coded values
- Refer ke dokumen ini saat membuat komponen baru

### 2. **Platform-Specific Adjustments**
- **Laravel:** Gunakan Tailwind CSS classes
- **Android:** Gunakan Material 3 components
- Maintain visual consistency despite different implementations

### 3. **Accessibility**
- Minimum contrast ratio: 4.5:1 untuk text
- Focus states harus visible
- Support keyboard navigation

### 4. **Responsive Design**
- Mobile-first approach
- Test di semua breakpoints
- Ensure touch targets min 44x44px

---

## 📚 REFERENCES

### Laravel Implementation
- Tailwind CSS: `tailwind.config.js`
- Custom CSS: `resources/css/app.css`
- Components: Blade templates

### Android Implementation
- Colors: `SharedColors.kt`
- Theme: `Theme.kt`
- Typography: `Type.kt`
- Components: Jetpack Compose

---

**Last Updated:** Februari 2026
**Version:** 1.0.0
**Status:** ✅ Active
