# Firebase Cloud Messaging (FCM) Implementation

## Overview
Project Al-Kutub telah diintegrasikan dengan Firebase Cloud Messaging (FCM) untuk notifikasi real-time ketika admin menambahkan kitab baru.

## Files yang ditambahkan/diubah:

### 1. Dependencies (`app/build.gradle.kts`)
- Menambahkan `firebase.messaging.ktx` untuk FCM

### 2. Firebase Messaging Service (`service/AlKutubFirebaseMessagingService.kt`)
- Handle incoming FCM messages
- Menampilkan notifikasi saat menerima pesan
- Mengelola FCM token

### 3. FCM Models (`model/FcmModels.kt`)
- `FcmNotificationRequest`: Model untuk request FCM
- `FcmNotification`: Model untuk notification payload
- `FcmTokenRequest`: Model untuk menyimpan token ke server

### 4. FCM Token Manager (`utils/FcmTokenManager.kt`)
- Mengelola FCM token lifecycle
- Mengirim token ke server saat user login
- Singleton class untuk token management

### 5. Application Class (`AlKutubApp.kt`)
- Inisialisasi FCM token saat app start
- Dependency injection untuk FcmTokenManager

### 6. AndroidManifest.xml
- Menambahkan Firebase Messaging Service
- Permission untuk notifikasi dan wake lock

## Cara Kerja:

### 1. Token Registration
- Saat app pertama kali dibuka, FCM token akan di-generate
- Token disimpan di SharedPreferences dan dikirim ke server
- Token akan diperbarui otomatis jika berubah

### 2. Receiving Notifications
- Service akan menerima pesan FCM dari server
- Notifikasi akan ditampilkan dengan:
  - Title: "Kitab Baru" atau custom title
  - Message: Deskripsi kitab baru
  - Action: Buka detail kitab
- Support untuk both notification dan data messages

### 3. Notification Flow
1. Admin input kitab baru di backend
2. Backend mengirim FCM notification ke semua user tokens
3. User devices menerima notifikasi real-time
4. Notifikasi ditampilkan dengan opsi untuk buka detail kitab

## Server Integration (Backend):

### Endpoint yang dibutuhkan:
1. **Save FCM Token**
   ```
   POST /api/fcm-token
   Headers: Authorization: Bearer {user_token}
   Body: {
     "fcm_token": "token_string",
     "device_type": "android"
   }
   ```

2. **Send Notification** (dipanggil backend saat admin input kitab)
   ```json
   {
     "to": "fcm_token atau topic",
     "notification": {
       "title": "Kitab Baru: {kitab_title}",
       "body": "{kitab_description}"
     },
     "data": {
       "title": "Kitab Baru",
       "message": "Ada kitab baru yang tersedia!",
       "kitab_id": "{kitab_id}"
     }
   }
   ```

## Testing:
1. Pastikan `google-services.json` sudah ada di `app/`
2. Build dan install app
3. Cek logcat untuk FCM token
4. Test dengan Firebase Console atau API call

## Notes:
- FCM bekerja dengan baik baik di foreground maupun background
- Notification channel akan dibuat otomatis untuk Android O+
- FCM token akan di-refresh otomatis oleh Firebase
- Existing NotificationWorker tetap berjalan sebagai backup system
