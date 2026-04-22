package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class NotificationResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String?,
    @SerializedName("data") val data: NotificationData?
)

data class NotificationData(
    @SerializedName("id") val id: Int,
    @SerializedName("title") val title: String,
    @SerializedName("message") val message: String,
    @SerializedName("type") val type: String,
    @SerializedName("action_url") val actionUrl: String?,
    @SerializedName("timestamp") val timestamp: String
)
