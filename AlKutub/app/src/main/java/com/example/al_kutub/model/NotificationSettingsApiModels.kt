package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class NotificationSettingsResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String? = null,
    @SerializedName("data")
    val data: NotificationPreferences? = null
)

data class NotificationSettingsRequest(
    @SerializedName("enable_notifications")
    val enableNotifications: Boolean,
    @SerializedName("new_book_notifications")
    val newBookNotifications: Boolean,
    @SerializedName("update_notifications")
    val updateNotifications: Boolean,
    @SerializedName("reminder_notifications")
    val reminderNotifications: Boolean,
    @SerializedName("quiet_hours_enabled")
    val quietHoursEnabled: Boolean,
    @SerializedName("quiet_hours_start")
    val quietHoursStart: String,
    @SerializedName("quiet_hours_end")
    val quietHoursEnd: String,
    @SerializedName("sound_enabled")
    val soundEnabled: Boolean,
    @SerializedName("vibration_enabled")
    val vibrationEnabled: Boolean,
    @SerializedName("led_enabled")
    val ledEnabled: Boolean,
    @SerializedName("notification_style")
    val notificationStyle: NotificationStyle,
    @SerializedName("categories")
    val categories: Map<String, Boolean>
)

fun NotificationPreferences.toApiRequest(): NotificationSettingsRequest {
    return NotificationSettingsRequest(
        enableNotifications = enableNotifications,
        newBookNotifications = newBookNotifications,
        updateNotifications = updateNotifications,
        reminderNotifications = reminderNotifications,
        quietHoursEnabled = quietHoursEnabled,
        quietHoursStart = quietHoursStart,
        quietHoursEnd = quietHoursEnd,
        soundEnabled = soundEnabled,
        vibrationEnabled = vibrationEnabled,
        ledEnabled = ledEnabled,
        notificationStyle = notificationStyle,
        categories = categories
    )
}
