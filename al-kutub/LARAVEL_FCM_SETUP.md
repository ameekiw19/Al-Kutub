# Laravel FCM Integration Setup

## 📋 Setup Instructions

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Add FCM Server Key to .env
Add this line to your `.env` file:
```
FCM_SERVER_KEY=your_firebase_server_key_here
```

### 3. Get Firebase Server Key
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project
3. Go to Project Settings → Cloud Messaging
4. Copy the **Server Key**
5. Add it to your `.env` file

### 4. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## 🔧 API Endpoints

### Save FCM Token
```
POST /api/fcm/token
Authorization: Bearer {user_token}
Content-Type: application/json

{
  "device_token": "fcm_token_here",
  "device_type": "android",
  "app_version": "1.0"
}
```

### Remove FCM Token (Logout)
```
DELETE /api/fcm/token
Authorization: Bearer {user_token}
Content-Type: application/json

{
  "device_token": "fcm_token_here"
}
```

### Test FCM Notification
```
POST /api/fcm/test
Authorization: Bearer {user_token}
Content-Type: application/json

{
  "title": "Test Notification",
  "message": "This is a test notification",
  "device_token": "optional_specific_token"
}
```

## 📱 Android Integration

The Android app will automatically:
1. Generate FCM token on app start
2. Send token to Laravel backend
3. Receive notifications when admin adds new kitab
4. Display notifications with proper navigation

## 🧪 Testing

### 1. Test Token Registration
```bash
# Check logs for FCM token
adb logcat -s FcmTokenManager

# Should see:
# FcmTokenManager: ✅ FCM token sent to server successfully
```

### 2. Test Notification
```bash
# Use the test endpoint or add a new kitab via admin panel
# Check logs:
adb logcat -s FCMService

# Should see:
# FCMService: 📨 Received FCM message
# FCMService: 🔔 Creating notification...
# FCMService: ✅ Notification displayed
```

### 3. Test Admin → User Flow
1. Login as admin
2. Add new kitab via admin panel
3. Check mobile app for notification
4. Tap notification to open kitab detail

## 🔍 Debugging

### Check Laravel Logs
```bash
tail -f storage/logs/laravel.log | grep FCM
```

### Check Database
```sql
-- Verify FCM tokens are saved
SELECT * FROM fcm_tokens WHERE is_active = 1;

-- Check notification logs
SELECT * FROM app_notifications WHERE type = 'new_kitab' ORDER BY created_at DESC;
```

### Common Issues

#### "FCM_SERVER_KEY not found"
- Add `FCM_SERVER_KEY` to `.env` file
- Run `php artisan config:clear`

#### "User not logged in, skipping token sync"
- User must be logged in to sync FCM token
- Check authentication token in SharedPreferences

#### "No active FCM tokens found"
- FCM token not registered yet
- Check if app has internet connection
- Verify Firebase configuration

## 📊 Monitoring

### FCM Service Logs
```php
// Check FCM notification attempts
Log::info('FCM Notification Sent', [
    'payload' => $payload,
    'response' => $responseData
]);
```

### Success Indicators
- ✅ FCM token saved to database
- ✅ User subscribed to "all_users" topic
- ✅ Admin can send test notifications
- ✅ New kitab triggers FCM notification
- ✅ Mobile app receives and displays notifications

## 🚀 Production Notes

1. **Server Security**: Keep FCM server key secure
2. **Error Handling**: Implement retry logic for failed notifications
3. **Rate Limiting**: Consider rate limiting for FCM API calls
4. **Analytics**: Track notification delivery rates
5. **User Preferences**: Allow users to disable notifications

## 🔄 Backup System

The existing polling notification system (`NotificationWorker`) remains as backup:
- Polls `/api/notifications/latest` every 2 minutes
- Will work even if FCM fails
- Provides redundancy for critical notifications
