package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// ===== FCM TOKEN REQUEST MODELS =====

data class FcmTokenRequest(
    @SerializedName("device_token")
    val deviceToken: String,
    
    @SerializedName("device_type")
    val deviceType: String = "android",
    
    @SerializedName("app_version")
    val appVersion: String? = null
)

data class FcmTokenRemoveRequest(
    @SerializedName("device_token")
    val deviceToken: String
)

data class FcmTestRequest(
    @SerializedName("title")
    val title: String,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("device_token")
    val deviceToken: String? = null
)

// ===== FCM TOKEN RESPONSE MODELS =====

data class FcmTokenResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: Any? = null
)

data class FcmTestResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: FcmTestData? = null
)

data class FcmTestData(
    @SerializedName("fcm_success")
    val fcmSuccess: Boolean? = null,
    
    @SerializedName("status_code")
    val statusCode: Int? = null,
    
    @SerializedName("response")
    val response: Any? = null
)
