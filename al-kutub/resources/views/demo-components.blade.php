<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>UI Components Demo - Al-Kutub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-background text-foreground">
    <!-- Header -->
    <header class="border-b bg-muted/50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Al-Kutub UI Components</h1>
                    <p class="text-muted-foreground text-sm">shadcn/ui inspired components with Alpine.js + Tailwind CSS</p>
                </div>
                <nav class="flex gap-2">
                    <a href="#" class="text-sm font-medium hover:underline">Documentation</a>
                    <a href="#" class="text-sm font-medium hover:underline">GitHub</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 space-y-12">
        
        <!-- SECTION: Breadcrumb -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Breadcrumb</h2>
            
            <nav x-data="breadcrumb()" aria-label="breadcrumb" class="bg-card rounded-lg border p-4">
                <ol class="flex flex-wrap items-center gap-1.5 text-sm">
                    <li>
                        <a href="/" class="hover:text-foreground transition-colors text-muted-foreground">
                            Home
                        </a>
                    </li>
                    <li aria-hidden="true">
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li>
                        <a href="/components" class="hover:text-foreground transition-colors text-muted-foreground">
                            Components
                        </a>
                    </li>
                    <li aria-hidden="true">
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li>
                        <span class="text-foreground font-normal" aria-current="page">Demo</span>
                    </li>
                </ol>
            </nav>
        </section>

        <!-- SECTION: Alerts -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Alerts</h2>
            
            <div class="grid gap-4">
                <!-- Default Alert -->
                <div x-data="alert()" class="bg-card rounded-lg border">
                    <div :class="getVariantClasses()" class="relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="font-medium">Heads up!</div>
                                <div class="text-muted-foreground">This is a default alert message.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Success Alert -->
                <div x-data="alert({ variant: 'success' })">
                    <div :class="getVariantClasses()" class="relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="font-medium">Success!</div>
                                <div class="text-muted-foreground">Your changes have been saved successfully.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div x-data="alert({ variant: 'warning' })">
                    <div :class="getVariantClasses()" class="relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <div class="font-medium">Warning</div>
                                <div class="text-muted-foreground">Please review before continuing.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Destructive Alert -->
                <div x-data="alert({ variant: 'destructive' })">
                    <div :class="getVariantClasses()" class="relative w-full rounded-lg border px-4 py-3 text-sm grid gap-1">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-destructive" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="font-medium">Error</div>
                                <div class="text-muted-foreground">Something went wrong. Please try again.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: Badges -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Badges</h2>
            
            <div class="flex flex-wrap gap-2 bg-card rounded-lg border p-4">
                <span x-data="badge()" :class="getVariantClasses()">Default</span>
                <span x-data="badge({ variant: 'secondary' })" :class="getVariantClasses()">Secondary</span>
                <span x-data="badge({ variant: 'destructive' })" :class="getVariantClasses()">Destructive</span>
                <span x-data="badge({ variant: 'outline' })" :class="getVariantClasses()">Outline</span>
                <span x-data="badge({ variant: 'success' })" :class="getVariantClasses()">Success</span>
                <span x-data="badge({ variant: 'warning' })" :class="getVariantClasses()">Warning</span>
                <span x-data="badge({ variant: 'info' })" :class="getVariantClasses()">Info</span>
            </div>

            <h3 class="font-semibold mt-4">Status Badges</h3>
            <div class="flex flex-wrap gap-2 bg-card rounded-lg border p-4">
                <span x-data="statusBadge({ status: 'active' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    Active
                </span>
                <span x-data="statusBadge({ status: 'pending' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    Pending
                </span>
                <span x-data="statusBadge({ status: 'review' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    In Review
                </span>
                <span x-data="statusBadge({ status: 'published' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    Published
                </span>
                <span x-data="statusBadge({ status: 'draft' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    Draft
                </span>
                <span x-data="statusBadge({ status: 'rejected' })" :class="getStatusClasses()">
                    <span :class="getDotColor()" class="size-2 rounded-full"></span>
                    Rejected
                </span>
            </div>
        </section>

        <!-- SECTION: Buttons -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Buttons</h2>
            
            <h3 class="font-semibold">Variants</h3>
            <div class="flex flex-wrap gap-2 bg-card rounded-lg border p-4">
                <button x-data="button()" :class="getClasses()">Default</button>
                <button x-data="button({ variant: 'secondary' })" :class="getClasses()">Secondary</button>
                <button x-data="button({ variant: 'destructive' })" :class="getClasses()">Destructive</button>
                <button x-data="button({ variant: 'outline' })" :class="getClasses()">Outline</button>
                <button x-data="button({ variant: 'ghost' })" :class="getClasses()">Ghost</button>
                <button x-data="button({ variant: 'link' })" :class="getClasses()">Link</button>
            </div>

            <h3 class="font-semibold mt-4">Sizes</h3>
            <div class="flex flex-wrap items-center gap-2 bg-card rounded-lg border p-4">
                <button x-data="button({ size: 'sm' })" :class="getClasses()">Small</button>
                <button x-data="button()" :class="getClasses()">Default</button>
                <button x-data="button({ size: 'lg' })" :class="getClasses()">Large</button>
                <button x-data="button({ size: 'icon' })" :class="getClasses()">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </button>
            </div>

            <h3 class="font-semibold mt-4">Loading State</h3>
            <div class="flex flex-wrap gap-2 bg-card rounded-lg border p-4">
                <button 
                    x-data="button({ variant: 'primary', loading: true })" 
                    :class="getClasses()"
                    disabled
                >
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </button>
            </div>
        </section>

        <!-- SECTION: Avatars -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Avatars</h2>
            
            <h3 class="font-semibold">Sizes</h3>
            <div class="flex items-center gap-4 bg-card rounded-lg border p-4">
                <div x-data="avatar({ size: 'sm' })">
                    <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
                        <div class="bg-muted flex size-full items-center justify-center rounded-full">
                            <span class="text-muted-foreground font-medium text-xs">JD</span>
                        </div>
                    </div>
                </div>
                
                <div x-data="avatar({ size: 'md' })">
                    <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
                        <div class="bg-muted flex size-full items-center justify-center rounded-full">
                            <span class="text-muted-foreground font-medium text-sm">JD</span>
                        </div>
                    </div>
                </div>
                
                <div x-data="avatar({ size: 'lg' })">
                    <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
                        <div class="bg-muted flex size-full items-center justify-center rounded-full">
                            <span class="text-muted-foreground font-medium text-base">JD</span>
                        </div>
                    </div>
                </div>
                
                <div x-data="avatar({ size: 'xl' })">
                    <div :class="getSizeClasses()" class="relative flex shrink-0 overflow-hidden rounded-full">
                        <div class="bg-muted flex size-full items-center justify-center rounded-full">
                            <span class="text-muted-foreground font-medium text-lg">JD</span>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="font-semibold mt-4">Avatar Group</h3>
            <div class="flex bg-card rounded-lg border p-4">
                <div x-data="avatarGroup({ avatars: [1,2,3,4,5], size: 'md' })">
                    <div class="flex">
                        <template x-for="i in 4" :key="i">
                            <div 
                                :class="getSizeClasses()"
                                class="relative flex shrink-0 overflow-hidden rounded-full border-2 border-background -ml-2 first:ml-0"
                            >
                                <div class="bg-muted flex size-full items-center justify-center rounded-full">
                                    <span class="text-muted-foreground font-medium text-xs">U<span x-text="i"></span></span>
                                </div>
                            </div>
                        </template>
                        <div class="size-10 rounded-full bg-muted flex items-center justify-center text-xs font-medium -ml-2">
                            +1
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: Accordion -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Accordion</h2>
            
            <div x-data="accordion()" class="space-y-2 bg-card rounded-lg border p-4">
                <!-- Item 1 -->
                <div class="border-b last:border-b-0">
                    <button 
                        @click="toggle('item1')"
                        class="flex w-full items-center justify-between py-4 text-left font-medium hover:underline"
                    >
                        <span>What is Al-Kutub?</span>
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
                        Al-Kutub is a digital Islamic library platform that provides easy access to various classical Islamic books (kitab). 
                        The platform allows users to read, bookmark, and manage their reading progress digitally.
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="border-b last:border-b-0">
                    <button 
                        @click="toggle('item2')"
                        class="flex w-full items-center justify-between py-4 text-left font-medium hover:underline"
                    >
                        <span>How to register?</span>
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
                        Simply click the "Register" button on the homepage, fill in your details, and verify your email. 
                        After verification, you can start reading and bookmarking books.
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="border-b last:border-b-0">
                    <button 
                        @click="toggle('item3')"
                        class="flex w-full items-center justify-between py-4 text-left font-medium hover:underline"
                    >
                        <span>Is it free to use?</span>
                        <svg 
                            :class="{'rotate-180': isOpen('item3')}"
                            class="h-4 w-4 transition-transform duration-200"
                            fill="none" 
                            viewBox="0 0 24 24" 
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="isOpen('item3')" x-collapse class="pb-4 text-muted-foreground">
                        Yes! Al-Kutub is completely free to use for all users. We believe in making Islamic knowledge accessible to everyone.
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: Alert Dialog -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Alert Dialog</h2>
            
            <div class="bg-card rounded-lg border p-4">
                <div x-data="alertDialog({
                    title: 'Delete Account',
                    description: 'Are you sure you want to delete your account? This action cannot be undone.',
                    confirmText: 'Delete',
                    destructive: true
                })">
                    <!-- Trigger -->
                    <button 
                        @click="open()"
                        class="bg-destructive text-destructive-foreground px-4 py-2 rounded-md hover:bg-destructive/90 text-sm font-medium"
                    >
                        Delete Account
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
                                    class="px-4 py-2 border rounded-md hover:bg-accent text-sm"
                                    x-text="cancelText"
                                ></button>
                                <button 
                                    @click="confirm()"
                                    class="px-4 py-2 bg-destructive text-destructive-foreground rounded-md hover:bg-destructive/90 text-sm"
                                    x-text="confirmText"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION: Aspect Ratio -->
        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Aspect Ratio</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-card rounded-lg border p-4">
                <div>
                    <h4 class="text-sm font-medium mb-2">Square (1:1)</h4>
                    <div x-data="aspectRatio({ ratio: '1/1' })">
                        <div :class="getRatioClasses()" class="relative w-full overflow-hidden rounded-lg bg-muted">
                            <div class="absolute inset-0 flex items-center justify-center text-muted-foreground">
                                1:1
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-2">Video (16:9)</h4>
                    <div x-data="aspectRatio({ ratio: '16/9' })">
                        <div :class="getRatioClasses()" class="relative w-full overflow-hidden rounded-lg bg-muted">
                            <div class="absolute inset-0 flex items-center justify-center text-muted-foreground">
                                16:9
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-2">Photo (4:3)</h4>
                    <div x-data="aspectRatio({ ratio: '4/3' })">
                        <div :class="getRatioClasses()" class="relative w-full overflow-hidden rounded-lg bg-muted">
                            <div class="absolute inset-0 flex items-center justify-center text-muted-foreground">
                                4:3
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium mb-2">Portrait (3:4)</h4>
                    <div x-data="aspectRatio({ ratio: '3/4' })">
                        <div :class="getRatioClasses()" class="relative w-full overflow-hidden rounded-lg bg-muted">
                            <div class="absolute inset-0 flex items-center justify-center text-muted-foreground">
                                3:4
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t bg-muted/50 mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    © 2026 Al-Kutub. Built with Alpine.js + Tailwind CSS
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-sm hover:underline">Documentation</a>
                    <a href="#" class="text-sm hover:underline">GitHub</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
