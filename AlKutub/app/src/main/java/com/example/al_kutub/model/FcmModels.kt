package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class FcmNotificationRequest(
    @SerializedName("to") val to: String, // FCM token
    @SerializedName("notification") val notification: FcmNotification,
    @SerializedName("data") val data: Map<String, String>
)

data class FcmNotification(
    @SerializedName("title") val title: String,
    @SerializedName("body") val body: String,
    @SerializedName("sound") val sound: String = "default"
)
