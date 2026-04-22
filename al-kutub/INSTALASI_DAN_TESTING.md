# 🚀 INSTALASI & TESTING GUIDE

## 📋 STEP-BY-STEP INSTALASI

### Prerequisites
Pastikan Anda sudah memiliki:
- ✅ Node.js >= 14.x
- ✅ npm >= 6.x
- ✅ Laravel 8 project running
- ✅ Composer dependencies installed

---

## 🔧 LANGKAH 1: INSTALL DEPENDENCIES

### 1.1 Navigate ke Project Directory
```bash
cd /home/amiir/AndroidStudioProjects/al-kutub
```

### 1.2 Install npm Packages
```bash
npm install
```

**Expected Output:**
```
added 500 packages, and audited 501 packages in 45s
found 0 vulnerabilities
```

**Jika Error:**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules dan package-lock.json
rm -rf node_modules package-lock.json

# Install ulang
npm install
```

---

## 🏗️ LANGKAH 2: BUILD ASSETS

### 2.1 Development Build (First Time)
```bash
npm run dev
```

**Expected Output:**
```
Browserslist: caniuse-lite is outdated
Done in 15.23s.
```

### 2.2 Watch Mode (Development)
Terminal baru:
```bash
npm run watch
```

**Expected Output:**
```
Starting development server...
Compiled successfully.
```

### 2.3 Production Build (Optional - untuk production)
```bash
npm run production
```

**Expected Output:**
```
Browserslist: caniuse-lite is outdated
Asset manifest:
  /public/css/app.css
  /public/js/app.js
Done in 25.45s.
```

---

## 🌐 LANGKAH 3: START LARAVEL SERVER

### 3.1 Start Laravel Server
Terminal baru:
```bash
php artisan serve
```

**Expected Output:**
```
Starting Laravel development server: http://127.0.0.1:8000
```

### 3.2 Access Demo Page
Buka browser:
```
http://localhost:8000/demo-ui-components
```

**Note:** Route ini hanya aktif di environment `local`.

---

## ✅ LANGKAH 4: VERIFY INSTALLATION

### Check Files Created
```bash
# Check Tailwind config
ls -la tailwind.config.js

# Check PostCSS config
ls -la postcss.config.js

# Check components directory
ls -la resources/js/components/

# Check compiled assets
ls -la public/css/app.css
ls -la public/js/app.js
```

### Check Compiled Assets Size
```bash
# Should be reasonable sizes
public/css/app.css      # ~10-50kb (depends on content)
public/js/app.js        # ~20-100kb (depends on content)
```

### Check Browser Console
1. Open browser DevTools (F12)
2. Go to Console tab
3. Should see: `✅ Al-Kutub UI Components loaded successfully!`

---

## 🧪 LANGKAH 5: TEST COMPONENTS

### Test 1: Accordion
1. Scroll ke "Accordion" section
2. Click pada "What is Al-Kutub?"
3. **Expected:** Content expand dengan smooth animation
4. Click lagi
5. **Expected:** Content collapse

### Test 2: Alert Dialog
1. Scroll ke "Alert Dialog" section
2. Click "Delete Account" button
3. **Expected:** Modal dialog muncul dengan overlay
4. Click "Cancel"
5. **Expected:** Dialog menutup
6. Click "Delete Account" lagi
7. Click "Delete"
8. **Expected:** Dialog menutup (check console untuk log)

### Test 3: Alerts
1. Scroll ke "Alerts" section
2. Check semua variant (default, success, warning, destructive)
3. **Expected:** Setiap variant punya warna berbeda

### Test 4: Badges
1. Scroll ke "Badges" section
2. Check semua variants
3. **Expected:** Different colors untuk different variants

### Test 5: Buttons
1. Scroll ke "Buttons" section
2. Hover setiap button
3. **Expected:** Hover effects bekerja
4. Click button
5. **Expected:** Click effects bekerja

### Test 6: Avatars
1. Scroll ke "Avatars" section
2. Check semua sizes
3. **Expected:** Different sizes (sm, md, lg, xl)

### Test 7: Breadcrumb
1. Scroll ke "Breadcrumb" section
2. Check navigation
3. **Expected:** Proper hierarchy dengan separators

### Test 8: Aspect Ratio
1. Scroll ke "Aspect Ratio" section
2. Check semua ratios
3. **Expected:** Different aspect ratios (1:1, 16:9, dll)

---

## 🐛 TROUBLESHOOTING

### Issue 1: Components Not Loading

**Symptoms:**
- Blank page
- Console errors tentang Alpine.js

**Solutions:**
```bash
# 1. Rebuild assets
npm run dev

# 2. Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 3. Hard refresh browser
Ctrl+Shift+R (Windows/Linux)
Cmd+Shift+R (Mac)
```

### Issue 2: Styling Tidak Bekerja

**Symptoms:**
- Components terlihat tanpa style
- Tailwind classes tidak apply

**Solutions:**
```bash
# 1. Check Tailwind config
cat tailwind.config.js

# 2. Rebuild CSS
npm run dev

# 3. Check CSS file loaded
# Browser DevTools → Network → Filter: app.css
# Should be loaded with status 200
```

### Issue 3: Route Not Found

**Symptoms:**
- 404 error saat akses `/demo-ui-components`

**Solutions:**
```bash
# 1. Check environment
# Route hanya aktif di local environment

# 2. Check .env
cat .env | grep APP_ENV
# Should be: APP_ENV=local

# 3. If not local, temporarily change:
# .env: APP_ENV=local
php artisan config:clear
```

### Issue 4: JavaScript Errors

**Symptoms:**
- Console errors tentang components

**Solutions:**
```bash
# 1. Check app.js compiled
ls -la public/js/app.js

# 2. Check for syntax errors
npm run dev
# Should compile without errors

# 3. Clear browser cache
# Browser DevTools → Application → Clear storage
```

### Issue 5: x-collapse Not Working

**Symptoms:**
- Accordion content tidak collapse

**Solutions:**
```javascript
// Add collapse plugin manually di app.js
import collapse from '@alpinejs/collapse';
Alpine.plugin(collapse);
```

Atau gunakan CSS:
```blade
<div x-show="isOpen()" 
     style="display: none;"
     :style="isOpen() ? 'display: block;' : 'display: none;'">
    Content
</div>
```

---

## 📊 PERFORMANCE CHECK

### Check Bundle Size
```bash
# Check compiled CSS size
ls -lh public/css/app.css
# Expected: 10-50kb

# Check compiled JS size
ls -lh public/js/app.js
# Expected: 20-100kb
```

### Check Load Time
1. Open browser DevTools (F12)
2. Go to Network tab
3. Refresh page
4. Check load time untuk:
   - `app.css` - Should be < 100ms (cached)
   - `app.js` - Should be < 200ms (cached)

### Check Lighthouse Score
1. Open browser DevTools (F12)
2. Go to Lighthouse tab
3. Generate report
4. **Expected Scores:**
   - Performance: 90+
   - Accessibility: 90+
   - Best Practices: 90+
   - SEO: 90+

---

## 🎯 INTEGRATION KE EXISTING PAGES

### Update TemplateUser.blade.php

1. Backup file original:
```bash
cp resources/views/TemplateUser.blade.php resources/views/TemplateUser.blade.php.backup
```

2. Edit `TemplateUser.blade.php`:
```blade
<head>
    <!-- Existing styles -->
    
    <!-- Add Tailwind CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Add Alpine.js -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
```

### Update Template.blade.php (Admin)

1. Backup file original:
```bash
cp resources/views/Template.blade.php resources/views/Template.blade.php.backup
```

2. Edit `Template.blade.php`:
```blade
<head>
    <!-- Existing styles -->
    
    <!-- Add Tailwind CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Add Alpine.js -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
```

---

## 📝 TESTING CHECKLIST

### Basic Functionality
- [ ] Accordion expand/collapse works
- [ ] Alert Dialog opens/closes
- [ ] Alerts display correctly
- [ ] Avatar shows image/fallback
- [ ] Badge variants display
- [ ] Breadcrumb navigation works
- [ ] Aspect Ratio containers work
- [ ] Button variants display

### Interactions
- [ ] Button hover effects work
- [ ] Alert dismiss works
- [ ] Dialog confirm/cancel works
- [ ] Accordion toggle works

### Responsive
- [ ] Components work on mobile (< 640px)
- [ ] Components work on tablet (640-1024px)
- [ ] Components work on desktop (> 1024px)

### Accessibility
- [ ] Keyboard navigation works
- [ ] Focus states visible
- [ ] ARIA labels present
- [ ] Screen reader friendly

### Performance
- [ ] Page loads < 2 seconds
- [ ] No console errors
- [ ] No 404 errors
- [ ] Assets cached properly

---

## 🎉 SUCCESS CRITERIA

Installation dianggap sukses jika:

1. ✅ `npm install` completes tanpa error
2. ✅ `npm run dev` builds successfully
3. ✅ `/demo-ui-components` page loads
4. ✅ All 8 components display correctly
5. ✅ Console shows success message
6. ✅ No JavaScript errors in console
7. ✅ No 404 errors in Network tab
8. ✅ Components interactive (click, hover work)

---

## 📞 NEXT STEPS SETELAH INSTALASI

1. **Explore Demo Page**
   - Test semua komponen
   - Check responsiveness
   - Verify accessibility

2. **Read Documentation**
   - `UI_COMPONENTS_README.md` - Quick start
   - `UI_COMPONENTS_DOCUMENTATION.md` - Complete docs

3. **Start Using in Production**
   - Update templates
   - Replace Bootstrap components
   - Add new components as needed

4. **Customize**
   - Change theme colors
   - Add custom components
   - Extend existing components

---

**Happy Coding! 🚀**

Jika ada masalah, check troubleshooting section atau consult documentation.
