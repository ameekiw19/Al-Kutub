# Testing Firebase Cloud Messaging

## Cara Test Notifikasi FCM

### 1. Dapatkan FCM Token
Install app dan buka Logcat. Filter dengan tag "FCMService" untuk melihat token:
```
D/FCMService: New FCM token generated: fcm_token_here
```

### 2. Test dengan Firebase Console
1. Buka [Firebase Console](https://console.firebase.google.com/)
2. Pilih project Al-Kutub
3. Cloud Messaging → Kirim pesan pertama
4. Masukkan FCM token di "Target" → "Token registrasi FCM"
5. Compose message:
   - Title: "Kitab Baru Test"
   - Body: "Ini adalah test notifikasi"
   - Data (optional):
     - key: `kitab_id`, value: `1`
     - key: `action_url`, value: `/kitab/1`

### 3. Test dengan API (Manual)
Gunakan `FcmTestHelper` atau curl:

```bash
curl -X POST https://fcm.googleapis.com/fcm/send \
  -H "Authorization: key=YOUR_SERVER_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "FCM_TOKEN_HERE",
    "notification": {
      "title": "Kitab Baru Test",
      "body": "Test notifikasi dari API"
    },
    "data": {
      "title": "Kitab Baru",
      "message": "Ada kitab baru!",
      "kitab_id": "1"
    }
  }'
```

### 4. Debugging Checklist

#### ✅ Permissions
- POST_NOTIFICATIONS (Android 13+)
- INTERNET
- WAKE_LOCK
- VIBRATE

#### ✅ Firebase Setup
- `google-services.json` ada di `app/`
- Firebase project sudah dibuat
- Cloud Messaging enabled

#### ✅ Service Registration
- `AlKutubFirebaseMessagingService` terdaftar di AndroidManifest
- Service menggunakan `@AndroidEntryPoint`

#### ✅ Dependencies
- `firebase-messaging-ktx` sudah ditambahkan
- Hilt modules untuk SharedPreferences

### 5. Logcat Filtering
Filter tags untuk debugging:
- `FCMService` - Firebase messaging logs
- `FirebaseMessaging` - Firebase SDK logs
- `AlKutubApp` - App initialization logs

### 6. Common Issues & Solutions

#### Token tidak muncul
- Cek internet connection
- Pastikan `google-services.json` valid
- Reinstall app

#### Notifikasi tidak muncul
- Cek notification permissions
- Test di foreground vs background
- Cek notification channel settings

#### Service tidak berjalan
- Cek AndroidManifest registration
- Pastikan `@AndroidEntryPoint` annotation
- Restart app

### 7. Production Setup
Backend perlu:
1. Simpan FCM tokens dari user
2. Kirim notifikasi saat admin input kitab
3. Handle token refresh

### 8. Test Scenarios
- ✅ App foreground
- ✅ App background  
- ✅ App killed
- ✅ Multiple devices
- ✅ Data vs notification payload
