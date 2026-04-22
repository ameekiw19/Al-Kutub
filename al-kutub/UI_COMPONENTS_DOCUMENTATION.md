# 🎨 AL-KUTUB UI COMPONENTS DOCUMENTATION

## 📋 DAFTAR ISI

1. [Installation](#installation)
2. [Setup](#setup)
3. [Components](#components)
   - [Accordion](#accordion)
   - [Alert Dialog](#alert-dialog)
   - [Alert](#alert)
   - [Avatar](#avatar)
   - [Badge](#badge)
   - [Breadcrumb](#breadcrumb)
   - [Aspect Ratio](#aspect-ratio)
   - [Button](#button)
4. [Usage Examples](#usage-examples)
5. [Customization](#customization)

---

## 🔧 INSTALLATION

### 1. Install Dependencies

```bash
cd /home/amiir/AndroidStudioProjects/al-kutub
npm install
```

### 2. Build Assets

```bash
# Development
npm run dev

# Watch mode (auto-rebuild on changes)
npm run watch

# Production
npm run production
```

---

## 📦 SETUP

### Include in Blade Templates

Add this to your `<head>` section in `Template.blade.php` or `TemplateUser.blade.php`:

```blade
<!-- Tailwind CSS -->
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

<!-- Alpine.js Components -->
<script src="{{ asset('js/app.js') }}" defer></script>
```

---

## 🎯 COMPONENTS

### 1. ACCORDION

Collapsible content sections with smooth animations.

#### Basic Usage
```blade
<div x-data="accordion()" class="space-y-2">
    <!-- Item 1 -->
    <div class="border-b">
        <button 
            @click="toggle('item1')"
            class="flex w-full items-center justify-between py-4 text-left font-medium hover:underline"
        >
            What is Al-Kutub?
            <svg 
                :class="{'rotate-180': isOpen('item1')}"
                class="h-4 w-4 transition-transform duration-200"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="isOpen('item1')" x-collapse class="pb-4 text-muted-foreground">
            Al-Kutub is a digital Islamic library platform...
        </div>
    </div>
    
    <!-- Item 2 -->
    <div class="border-b">
        <button 
            @click="toggle('item2')"
            class="flex w-full items-center justify-between py-4 text-left font-medium hover:underline"
        >
            How to use this platform?
            <svg 
                :class="{'rotate-180': isOpen('item2')}"
                class="h-4 w-4 transition-transform duration-200"
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="isOpen('item2')" x-collapse class="pb-4 text-muted-foreground">
            Simply register, verify your email, and start reading...
        </div>
    </div>
</div>
```

#### Multiple Mode (Open Multiple Items)
```blade
<div x-data="accordion({ type: 'multiple' })">
    <!-- Same structure, but multiple items can be open -->
</div>
```

#### Options
```javascript
accordion({
    type: 'single',        // 'single' | 'multiple'
    collapsible: false,    // Allow closing all items
    multiple: false        // Enable multiple mode
})
```

---

### 2. ALERT DIALOG

Confirmation dialogs for important actions.

#### Basic Usage
```blade
<div x-data="alertDialog()">
    <!-- Trigger Button -->
    <button 
        @click="open()"
        class="bg-destructive text-destructive-foreground px-4 py-2 rounded-md hover:bg-destructive/90"
    >
        Delete Account
    </button>
    
    <!-- Dialog Overlay -->
    <div 
        x-show="isOpen" 
        x-cloak
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
        @click="close()"
    >
        <!-- Dialog Content -->
        <div 
            @click.stop
            class="bg-background rounded-lg border p-6 shadow-lg max-w-md w-full mx-4"
        >
            <!-- Header -->
            <div class="space-y-2">
                <h3 class="text-lg font-semibold">Are you sure?</h3>
                <p class="text-muted-foreground text-sm">
                    This action cannot be undone. This will permanently delete your account.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="flex gap-2 justify-end mt-4">
                <button 
                    @click="close()"
                    class="px-4 py-2 border rounded-md hover:bg-accent"
                >
                    Cancel
                </button>
                <button 
                    @click="confirm()"
                    class="px-4 py-2 bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90"
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
```

#### With Callbacks
```blade
<div x-data="alertDialog({
    title: 'Delete Kitab',
    description: 'Are you sure you want to delete this kitab? This action cannot be undone.',
    confirmText: 'Delete',
    cancelText: 'Cancel',
    destructive: true,
    onConfirm: () => {
        // Perform delete action
        console.log('Deleting...');
        // Submit form or make API call
    },
    onCancel: () => {
        console.log('Cancelled');
    }
})">
    <!-- Trigger -->
    <button @click="open()">Delete Kitab</button>
    
    <!-- Dialog (same structure as above) -->
</div>
```

#### Options
```javascript
alertDialog({
    title: '',
    description: '',
    confirmText: 'Continue',
    cancelText: 'Cancel',
    destructive: false,
    onConfirm: () => {},
    onCancel: () => {},
    onOpenChange: (isOpen) => {}
})
```

---

### 3. ALERT

Notification banners for user feedback.

#### Default Alert
```blade
<div x-data="alert()" class="my-4">
    <div 
        x-show="visible"
        :class="getVariantClasses()"
        class="relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1"
    >
        <!-- Icon -->
        <div class="flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            
            <!-- Content -->
            <div>
                <div class="font-medium">Heads up!</div>
                <div class="text-muted-foreground">
                    This is a default alert message.
                </div>
            </div>
            
            <!-- Dismiss Button -->
            <button 
                @click="dismiss()"
                class="absolute right-2 top-2 hover:opacity-70"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
```

#### Alert Variants

```blade
<!-- Success Alert -->
<div x-data="alert({ variant: 'success' })">
    <div :class="getVariantClasses()">
        <div class="flex items-center gap-2">
            <svg class="text-green-600">✓</svg>
            <div>
                <div class="font-medium">Success!</div>
                <div class="text-muted-foreground">Your changes have been saved.</div>
            </div>
        </div>
    </div>
</div>

<!-- Error Alert -->
<div x-data="alert({ variant: 'destructive' })">
    <div :class="getVariantClasses()">
        <div class="flex items-center gap-2">
            <svg class="text-destructive">✕</svg>
            <div>
                <div class="font-medium">Error</div>
                <div class="text-muted-foreground">Something went wrong.</div>
            </div>
        </div>
    </div>
</div>

<!-- Warning Alert -->
<div x-data="alert({ variant: 'warning' })">
    <div :class="getVariantClasses()">
        <div class="flex items-center gap-2">
            <svg class="text-yellow-600">⚠</svg>
            <div>
                <div class="font-medium">Warning</div>
                <div class="text-muted-foreground">Please review before continuing.</div>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div x-data="alert({ variant: 'info' })">
    <div :class="getVariantClasses()">
        <div class="flex items-center gap-2">
            <svg class="text-blue-600">ℹ</svg>
            <div>
                <div class="font-medium">Did you know?</div>
                <div class="text-muted-foreground">You can bookmark pages for later.</div>
            </div>
        </div>
    </div>
</div>
```

#### Alert Manager (Multiple Alerts)
```blade
<div x-data="alertManager()">
    <!-- Add alerts programmatically -->
    <button @click="success('Success!', 'Operation completed successfully.')">
        Show Success
    </button>
    
    <button @click="error('Error!', 'Something went wrong.')">
        Show Error
    </button>
    
    <!-- Alert Container -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="alertItem in alerts" :key="alertItem.id">
            <div x-show="alertItem.visible" class="w-80">
                <!-- Alert content -->
            </div>
        </template>
    </div>
</div>
```

---

### 4. AVATAR

User profile images with fallback.

#### Basic Avatar
```blade
<div x-data="avatar({ 
    src: '/path/to/image.jpg', 
    alt: 'John Doe',
    size: 'md'
})">
    <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
        <!-- Image -->
        <img 
            x-show="!showFallback()"
            @load="onLoad()"
            @error="onError()"
            :src="src" 
            :alt="alt"
            class="aspect-square size-full"
        />
        
        <!-- Fallback -->
        <div 
            x-show="showFallback()"
            class="bg-muted flex size-full items-center justify-center rounded-full"
        >
            <span class="text-muted-foreground font-medium" x-text="fallback"></span>
        </div>
    </div>
</div>
```

#### Avatar Sizes
```blade
<!-- Small -->
<div x-data="avatar({ size: 'sm' })">...</div>  <!-- 32px -->

<!-- Medium (default) -->
<div x-data="avatar({ size: 'md' })">...</div>  <!-- 40px -->

<!-- Large -->
<div x-data="avatar({ size: 'lg' })">...</div>  <!-- 48px -->

<!-- Extra Large -->
<div x-data="avatar({ size: 'xl' })">...</div>  <!-- 64px -->
```

#### Avatar Group
```blade
<div x-data="avatarGroup({ 
    avatars: [
        { src: '/user1.jpg', alt: 'User 1' },
        { src: '/user2.jpg', alt: 'User 2' },
        { src: '/user3.jpg', alt: 'User 3' }
    ],
    max: 3,
    size: 'md'
})">
    <div class="flex">
        <template x-for="(user, index) in getVisibleAvatars()" :key="index">
            <div 
                :class="getSizeClasses()"
                class="relative flex shrink-0 overflow-hidden rounded-full border-2 border-background -ml-2 first:ml-0"
            >
                <img :src="user.src" :alt="user.alt" class="aspect-square size-full" />
            </div>
        </template>
        
        <!-- Remaining Count -->
        <div x-show="getRemainingCount() > 0" class="size-10 rounded-full bg-muted flex items-center justify-center text-xs font-medium -ml-2">
            +<span x-text="getRemainingCount()"></span>
        </div>
    </div>
</div>
```

---

### 5. BADGE

Status indicators and tags.

#### Basic Badge
```blade
<span x-data="badge()" :class="getVariantClasses()">
    Badge
</span>
```

#### Badge Variants
```blade
<!-- Default -->
<span x-data="badge()" :class="getVariantClasses()">Default</span>

<!-- Secondary -->
<span x-data="badge({ variant: 'secondary' })" :class="getVariantClasses()">Secondary</span>

<!-- Destructive -->
<span x-data="badge({ variant: 'destructive' })" :class="getVariantClasses()">Destructive</span>

<!-- Outline -->
<span x-data="badge({ variant: 'outline' })" :class="getVariantClasses()">Outline</span>

<!-- Success -->
<span x-data="badge({ variant: 'success' })" :class="getVariantClasses()">Success</span>

<!-- Warning -->
<span x-data="badge({ variant: 'warning' })" :class="getVariantClasses()">Warning</span>

<!-- Info -->
<span x-data="badge({ variant: 'info' })" :class="getVariantClasses()">Info</span>
```

#### Status Badge
```blade
<span x-data="statusBadge({ status: 'active' })" :class="getStatusClasses()">
    <span x-show="showDot" :class="getDotColor()" class="size-2 rounded-full"></span>
    Active
</span>
```

#### Status Options
- `pending` - Yellow
- `active` - Green
- `inactive` - Gray
- `error` - Red
- `review` - Yellow
- `published` - Green
- `draft` - Gray
- `rejected` - Red

#### Badge with Count
```blade
<button x-data="badgeWithCount({ 
    variant: 'secondary',
    count: 99,
    showCount: true
})">
    Notifications
    <span x-show="hasCount()" class="ml-1" x-text="getDisplayCount()"></span>
</button>
```

---

### 6. BREADCRUMB

Navigation breadcrumbs.

#### Basic Breadcrumb
```blade
<nav x-data="breadcrumb()" aria-label="breadcrumb">
    <ol :class="breadcrumbList().classes" class="flex flex-wrap items-center gap-1.5 text-sm">
        <!-- Home -->
        <li :class="breadcrumbItem().classes">
            <a :class="breadcrumbLink().getClasses()" href="/" class="hover:text-foreground transition-colors">
                Home
            </a>
        </li>
        
        <!-- Separator -->
        <li :class="breadcrumbSeparator().classes" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </li>
        
        <!-- Category -->
        <li :class="breadcrumbItem().classes">
            <a :class="breadcrumbLink().getClasses()" href="/kategori" class="hover:text-foreground transition-colors">
                Kategori
            </a>
        </li>
        
        <!-- Separator -->
        <li aria-hidden="true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </li>
        
        <!-- Current Page -->
        <li :class="breadcrumbItem().classes">
            <span 
                :class="breadcrumbPage().classes" 
                class="text-foreground font-normal"
                aria-current="page"
            >
                Kitab Detail
            </span>
        </li>
    </ol>
</nav>
```

#### Dynamic Breadcrumb (from array)
```blade
<nav x-data="breadcrumb({
    items: [
        { label: 'Home', href: '/' },
        { label: 'Kitab', href: '/kitab' },
        { label: 'Tauhid', href: '/kategori/tauhid' },
        { label: 'Kitab Al-Muwatta', href: null, current: true }
    ]
})">
    <ol :class="breadcrumbList().classes">
        <template x-for="(item, index) in items" :key="index">
            <li :class="breadcrumbItem().classes">
                <template x-if="!item.current">
                    <a :class="breadcrumbLink().getClasses()" :href="item.href" class="hover:text-foreground">
                        <span x-text="item.label"></span>
                    </a>
                </template>
                <template x-if="item.current">
                    <span 
                        :class="breadcrumbPage().classes"
                        class="text-foreground font-normal"
                        x-text="item.label"
                    ></span>
                </template>
                
                <!-- Separator (not for last item) -->
                <template x-if="index < items.length - 1">
                    <svg class="h-4 w-4 inline mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </template>
            </li>
        </template>
    </ol>
</nav>
```

---

### 7. ASPECT RATIO

Maintain consistent aspect ratios for images/videos.

#### Basic Usage
```blade
<div x-data="aspectRatio({ ratio: '16/9' })">
    <div :class="getRatioClasses()" class="relative w-full overflow-hidden rounded-lg">
        <img 
            src="/path/to/image.jpg" 
            alt="Cover"
            class="absolute inset-0 size-full object-cover"
        />
    </div>
</div>
```

#### Preset Ratios
```blade
<!-- Square (1:1) -->
<div x-data="aspectRatio({ ratio: '1/1' })">...</div>

<!-- Video (16:9) -->
<div x-data="aspectRatio({ ratio: '16/9' })">...</div>

<!-- Photo Landscape (4:3) -->
<div x-data="aspectRatio({ ratio: '4/3' })">...</div>

<!-- Photo Portrait (3:4) -->
<div x-data="aspectRatio({ ratio: '3/4' })">...</div>

<!-- Card (3:2) -->
<div x-data="aspectRatio({ ratio: '3/2' })">...</div>

<!-- Auto (no constraint) -->
<div x-data="aspectRatio({ ratio: 'auto' })">...</div>
```

#### Using Constants
```blade
<div x-data="aspectRatio({ ratio: ASPECT_RATIOS.VIDEO })">
    <!-- 16:9 video container -->
</div>
```

---

### 8. BUTTON

Interactive buttons with variants and sizes.

#### Basic Button
```blade
<button x-data="button()" :class="getClasses()">
    Button
</button>
```

#### Button Variants
```blade
<!-- Default (Primary) -->
<button x-data="button()" :class="getClasses()">Default</button>

<!-- Secondary -->
<button x-data="button({ variant: 'secondary' })" :class="getClasses()">Secondary</button>

<!-- Destructive -->
<button x-data="button({ variant: 'destructive' })" :class="getClasses()">Destructive</button>

<!-- Outline -->
<button x-data="button({ variant: 'outline' })" :class="getClasses()">Outline</button>

<!-- Ghost -->
<button x-data="button({ variant: 'ghost' })" :class="getClasses()">Ghost</button>

<!-- Link -->
<button x-data="button({ variant: 'link' })" :class="getClasses()">Link</button>
```

#### Button Sizes
```blade
<!-- Small -->
<button x-data="button({ size: 'sm' })" :class="getClasses()">Small</button>

<!-- Default -->
<button x-data="button()" :class="getClasses()">Default</button>

<!-- Large -->
<button x-data="button({ size: 'lg' })" :class="getClasses()">Large</button>

<!-- Icon -->
<button x-data="button({ size: 'icon' })" :class="getClasses()">
    <svg class="h-5 w-5">...</svg>
</button>
```

#### Button with Icon
```blade
<button x-data="buttonWithIcon({ 
    variant: 'default',
    icon: 'search',
    iconPosition: 'left'
})" :class="getClasses()">
    <!-- Icon -->
    <svg x-show="hasIcon()" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>
    
    <!-- Loading Spinner -->
    <svg x-show="isLoading()" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    
    <span>Search</span>
</button>
```

#### Loading State
```blade
<button 
    x-data="button({ 
        variant: 'primary',
        loading: true,
        disabled: true
    })" 
    :class="getClasses()"
    disabled
>
    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    Loading...
</button>
```

---

## 🎨 USAGE EXAMPLES

### Example 1: FAQ Section with Accordion
```blade
<section class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-8">Frequently Asked Questions</h2>
        
        <div class="max-w-3xl mx-auto">
            <div x-data="accordion()" class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="border rounded-lg">
                    <button 
                        @click="toggle('faq1')"
                        class="flex w-full items-center justify-between p-4 text-left font-medium hover:bg-muted/50 rounded-lg"
                    >
                        <span>Apa itu Al-Kutub?</span>
                        <svg 
                            :class="{'rotate-180': isOpen('faq1')}"
                            class="h-5 w-5 transition-transform"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="isOpen('faq1')" x-collapse class="px-4 pb-4 text-muted-foreground">
                        Al-Kutub adalah platform perpustakaan digital Islam yang menyediakan akses mudah ke berbagai kitab klasik...
                    </div>
                </div>
                
                <!-- More FAQ items... -->
            </div>
        </div>
    </div>
</section>
```

### Example 2: Delete Confirmation Dialog
```blade
<!-- Delete Button -->
<button 
    x-data="alertDialog({
        title: 'Delete Kitab',
        description: 'Are you sure you want to delete this kitab? This action cannot be undone.',
        confirmText: 'Delete',
        destructive: true,
        onConfirm: () => {
            // Submit delete form
            document.getElementById('delete-form').submit();
        }
    })"
    @click="open()"
    class="text-red-600 hover:text-red-800"
>
    Delete
</button>

<!-- Dialog -->
<div 
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
    @click="close()"
>
    <div 
        @click.stop
        class="bg-background rounded-lg border p-6 shadow-lg max-w-md w-full"
    >
        <div class="space-y-2">
            <h3 class="text-lg font-semibold" x-text="title"></h3>
            <p class="text-muted-foreground text-sm" x-text="description"></p>
        </div>
        
        <div class="flex gap-2 justify-end mt-4">
            <button 
                @click="close()"
                class="px-4 py-2 border rounded-md hover:bg-accent"
                x-text="cancelText"
            ></button>
            <button 
                @click="confirm()"
                class="px-4 py-2 bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90"
                x-text="confirmText"
            ></button>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" action="/admin/delete-kitab/123">
    @csrf
    @method('DELETE')
</form>
```

### Example 3: User Profile Card
```blade
<div class="bg-card rounded-lg border p-6 shadow-sm">
    <div class="flex items-center gap-4">
        <!-- Avatar -->
        <div x-data="avatar({ 
            src: '{{ auth()->user()->avatar ?? null }}',
            alt: '{{ auth()->user()->username }}',
            size: 'lg'
        })">
            <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
                <template x-if="src">
                    <img 
                        @load="onLoad()"
                        @error="onError()"
                        :src="src" 
                        :alt="alt"
                        class="aspect-square size-full"
                    />
                </template>
                <template x-if="showFallback()">
                    <div class="bg-muted flex size-full items-center justify-center rounded-full">
                        <span class="text-muted-foreground font-medium" x-text="fallback"></span>
                    </div>
                </template>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="flex-1">
            <h3 class="font-semibold text-lg">{{ auth()->user()->username }}</h3>
            <p class="text-muted-foreground text-sm">{{ auth()->user()->email }}</p>
        </div>
        
        <!-- Status Badge -->
        <span x-data="statusBadge({ status: 'active' })" :class="getStatusClasses()">
            <span :class="getDotColor()" class="size-2 rounded-full"></span>
            Active
        </span>
    </div>
</div>
```

### Example 4: Page Header with Breadcrumb
```blade
<div class="border-b bg-muted/50">
    <div class="container mx-auto px-4 py-4">
        <!-- Breadcrumb -->
        <nav x-data="breadcrumb()" class="mb-4" aria-label="breadcrumb">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm">
                <li>
                    <a href="/" class="hover:text-foreground transition-colors">Home</a>
                </li>
                <li aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>
                    <a href="/kitab" class="hover:text-foreground transition-colors">Kitab</a>
                </li>
                <li aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li>
                    <span class="text-foreground font-normal" aria-current="page">Detail Kitab</span>
                </li>
            </ol>
        </nav>
        
        <!-- Page Title -->
        <h1 class="text-3xl font-bold">Kitab Al-Muwatta</h1>
        
        <!-- Actions -->
        <div class="flex gap-2 mt-4">
            <button x-data="button({ variant: 'outline', size: 'sm' })" :class="getClasses()">
                Edit
            </button>
            <button x-data="button({ variant: 'destructive', size: 'sm' })" :class="getClasses()">
                Delete
            </button>
        </div>
    </div>
</div>
```

---

## 🎨 CUSTOMIZATION

### Theme Colors

Edit `tailwind.config.js` to customize colors:

```javascript
theme: {
  extend: {
    colors: {
      // Al-Kutub primary color
      primary: {
        DEFAULT: '#44A194',  // Teal
        foreground: '#FFFFFF',
      },
      // Custom variants
      kutub: {
        primary: '#44A194',
        secondary: '#2D7A6E',
        accent: '#5FB8A9',
        light: '#E8F5F3',
        dark: '#1A4D42',
      },
    },
  },
},
```

### Border Radius

```javascript
theme: {
  extend: {
    borderRadius: {
      lg: 'var(--radius)',
      md: 'calc(var(--radius) - 2px)',
      sm: 'calc(var(--radius) - 4px)',
    },
  },
},
```

### Animations

```javascript
theme: {
  extend: {
    keyframes: {
      'accordion-down': {
        from: { height: '0' },
        to: { height: 'var(--radix-accordion-content-height)' },
      },
      'accordion-up': {
        from: { height: 'var(--radix-accordion-content-height)' },
        to: { height: '0' },
      },
    },
    animation: {
      'accordion-down': 'accordion-down 0.2s ease-out',
      'accordion-up': 'accordion-up 0.2s ease-out',
    },
  },
},
```

---

## 📝 NOTES

### Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Alpine.js requires ES6+ support

### Performance Tips

1. Use `x-cloak` to prevent FOUC (Flash of Uncompiled Content)
2. Lazy load components when possible
3. Use `x-show` instead of `x-if` for frequently toggled elements
4. Minimize Alpine.js reactivity scope

### Best Practices

1. Keep component data close to where it's used
2. Use meaningful variable names in `x-data`
3. Extract complex logic to external functions
4. Use events for cross-component communication

---

## 🆘 TROUBLESHOOTING

### Components Not Working?

1. Check if `app.js` is loaded: `<script src="{{ asset('js/app.js') }}" defer></script>`
2. Check browser console for errors
3. Ensure Tailwind CSS is compiled: `npm run watch`
4. Clear browser cache

### Styling Issues?

1. Verify Tailwind config is correct
2. Check if CSS file is loaded
3. Run `npm run dev` to rebuild assets
4. Check for CSS conflicts with existing styles

---

**Last Updated:** Februari 2026
**Version:** 1.0.0
**Status:** ✅ Production Ready
