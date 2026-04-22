package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class NotificationPreferences(
    @SerializedName("enable_notifications")
    var enableNotifications: Boolean = true,
    
    @SerializedName("new_book_notifications")
    var newBookNotifications: Boolean = true,
    
    @SerializedName("update_notifications")
    var updateNotifications: Boolean = true,
    
    @SerializedName("reminder_notifications")
    var reminderNotifications: Boolean = true,
    
    @SerializedName("quiet_hours_enabled")
    var quietHoursEnabled: Boolean = false,
    
    @SerializedName("quiet_hours_start")
    var quietHoursStart: String = "22:00", // 10 PM
    
    @SerializedName("quiet_hours_end")
    var quietHoursEnd: String = "08:00", // 8 AM
    
    @SerializedName("sound_enabled")
    var soundEnabled: Boolean = true,
    
    @SerializedName("vibration_enabled")
    var vibrationEnabled: Boolean = true,
    
    @SerializedName("led_enabled")
    var ledEnabled: Boolean = true,
    
    @SerializedName("notification_style")
    var notificationStyle: NotificationStyle = NotificationStyle.BASIC,
    
    @SerializedName("categories")
    var categories: Map<String, Boolean> = mapOf(
        "islamic" to true,
        "education" to true,
        "literature" to true,
        "history" to true,
        "science" to true
    )
) {
    fun isInQuietHours(): Boolean {
        if (!quietHoursEnabled) return false
        
        val calendar = java.util.Calendar.getInstance()
        val currentHour = calendar.get(java.util.Calendar.HOUR_OF_DAY)
        val currentMinute = calendar.get(java.util.Calendar.MINUTE)
        val currentTimeMinutes = currentHour * 60 + currentMinute
        
        val startTimeParts = quietHoursStart.split(":")
        val startMinutes = startTimeParts[0].toInt() * 60 + startTimeParts[1].toInt()
        
        val endTimeParts = quietHoursEnd.split(":")
        val endMinutes = endTimeParts[0].toInt() * 60 + endTimeParts[1].toInt()
        
        return if (startMinutes > endMinutes) {
            // Overnight quiet hours (e.g., 22:00 to 08:00)
            currentTimeMinutes >= startMinutes || currentTimeMinutes <= endMinutes
        } else {
            // Same day quiet hours (e.g., 01:00 to 06:00)
            currentTimeMinutes in startMinutes..endMinutes
        }
    }
    
    fun shouldShowNotification(type: NotificationType): Boolean {
        if (!enableNotifications) return false
        if (isInQuietHours()) return false
        
        return when (type) {
            NotificationType.NEW_BOOK -> newBookNotifications
            NotificationType.UPDATE -> updateNotifications
            NotificationType.REMINDER -> reminderNotifications
        }
    }
    
    fun getNotificationSettingsSummary(): String {
        val enabledCount = listOf(
            newBookNotifications,
            updateNotifications,
            reminderNotifications
        ).count { it }
        
        return if (enableNotifications) {
            "$enabledCount dari 3 notifikasi diaktifkan"
        } else {
            "Semua notifikasi dinonaktifkan"
        }
    }
}

enum class NotificationType {
    NEW_BOOK,
    UPDATE,
    REMINDER
}

enum class NotificationStyle(val label: String) {
    BASIC("Dasar"),
    EXPANDED("Diperluas"),
    SILENT("Hening")
}

data class NotificationSchedule(
    val id: String,
    val title: String,
    val message: String,
    val time: String, // HH:mm format
    val days: List<Int>, // DayOfWeek values (1-7, Monday=1)
    val enabled: Boolean = true,
    val category: String? = null
)
