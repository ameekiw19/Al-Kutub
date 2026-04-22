# FCM Debugging Guide

## 📋 Langkah Debugging Notifikasi FCM

### 1. Build dan Install Aplikasi
```bash
./gradlew clean build
./gradlew installDebug
```

### 2. Periksa Logcat untuk Debug Info
Buka logcat dan filter dengan tag berikut:
- `FcmDebugger` - Untuk debugging Firebase config
- `FCMService` - Untuk debugging FCM service
- `FcmTokenManager` - Untuk debugging token management

```bash
adb logcat -s FcmDebugger,FCMService,FcmTokenManager
```

### 3. Expected Log Output

#### Saat App Start:
```
FcmDebugger: 🔍 Checking Firebase configuration...
FcmDebugger: ✅ Firebase app name: [DEFAULT]
FcmDebugger: ✅ Firebase options: {...}
FcmDebugger: ✅ Project ID: your-project-id
FcmDebugger: ✅ API Key: AIzaSy...
FcmDebugger: 🔥 Starting FCM initialization...
FcmDebugger: ✅ FCM Token obtained: fcm_token_here
FcmDebugger: ✅ Token format looks valid
FcmDebugger: 📱 Token length: 152
```

#### Token Management:
```
FcmTokenManager: 🔑 FCM Token obtained: fcm_token_here
FcmTokenManager: 💾 Token saved to SharedPreferences
FcmTokenManager: 📤 Token sent to server
```

### 4. Test Notifikasi Manual

#### Method 1: Firebase Console
1. Buka Firebase Console
2. Cloud Messaging > Send your first message
3. Masukkan FCM token dari logcat
4. Kirim notifikasi test

#### Method 2: HTTP API
Gunakan `FcmTestHelper` untuk testing:
```kotlin
// Di MainActivity atau tempat lain
fcmTestHelper.sendTestNotification(
    title = "Test Notifikasi",
    body = "Ini adalah test notifikasi FCM",
    data = mapOf("kitab_id" to "123")
)
```

### 5. Troubleshooting Common Issues

#### Issue: "API Key is missing or empty"
**Symptom:** `FcmDebugger: ❌ API Key is missing or empty`
**Solution:** 
- Periksa `google-services.json` sudah benar
- Build ulang aplikasi setelah update config

#### Issue: "No matching client found"
**Symptom:** Error saat build tentang package name mismatch
**Solution:**
- Pastikan `applicationId` di `build.gradle.kts` cocok dengan `package_name` di `google-services.json`
- Build clean: `./gradlew clean`

#### Issue: Token tidak muncul
**Symptom:** Tidak ada log "FCM Token obtained"
**Solution:**
- Pastikan internet connection stabil
- Periksa Firebase project configuration
- Restart aplikasi

#### Issue: Notifikasi tidak muncul
**Symptom:** Token berhasil tapi notifikasi tidak muncul
**Solution:**
- Periksa notification permission: `Settings > Apps > AlKutub > Notifications`
- Pastikan app tidak di-kill/diminish
- Cek log `FCMService` untuk incoming message

### 6. Expected Notification Behavior

#### Saat Notifikasi Diterima:
```
FCMService: 📨 Received FCM message
FCMService: 🔔 Creating notification...
FCMService: ✅ Notification displayed
```

#### Notification Channels:
- **New Kitab Alert**: Channel untuk notifikasi kitab baru
- **General Notifications**: Channel untuk notifikasi umum

### 7. Test Scenarios

#### Test 1: Basic Notification
```bash
# Kirim notifikasi sederhana
fcmTestHelper.sendBasicTest()
```

#### Test 2: Kitab Notification
```bash
# Kirim notifikasi dengan data kitab
fcmTestHelper.sendKitabNotification(
    kitabId = 123,
    title = "Kitab Baru!",
    message = "Al-Qur'an Terjemahan ditambahkan"
)
```

#### Test 3: Background/Foreground Test
1. Buka aplikasi (foreground)
2. Kirim notifikasi → harus muncul sebagai heads-up
3. Tutup aplikasi (background)
4. Kirim notifikasi → harus muncul di notification shade

### 8. Debug Tools

#### Check App Notifications:
```bash
adb shell dumpsys notification | grep AlKutub
```

#### Check Firebase Services:
```bash
adb shell dumpsys activity services | grep firebase
```

#### Force Stop dan Restart:
```bash
adb shell am force-stop com.example.al_kutub
adb shell am start -n com.example.al_kutub/.MainActivity
```

### 9. Backend Integration

Jika backend sudah siap, notifikasi akan dikirim otomatis saat:
- Admin menambahkan kitab baru
- Backend mengirim FCM message ke semua registered tokens
- Client menerima notifikasi real-time

### 10. Monitoring

#### Success Indicators:
- ✅ Firebase configuration valid
- ✅ FCM token obtained successfully  
- ✅ Token saved and sent to server
- ✅ Notification received and displayed
- ✅ Tapping notification opens correct kitab detail

#### Failure Indicators:
- ❌ API Key missing/invalid
- ❌ No FCM token generated
- ❌ Network connection issues
- ❌ Notification permission denied
- ❌ Firebase project misconfigured

---

## 🆘 Jika Masih Bermasalah

1. **Periksa Firebase Console**: Pastikan project aktif dan Cloud Messaging enabled
2. **Verify google-services.json**: Download ulang dari Firebase Console
3. **Check Network**: Pastikan device memiliki internet connection
4. **Restart Device**: Kadang perlu restart untuk refresh Firebase services
5. **Reinstall App**: Uninstall dan install ulang aplikasi

Untuk bantuan lebih lanjut, sertakan logcat lengkap saat reproduksi issue.
