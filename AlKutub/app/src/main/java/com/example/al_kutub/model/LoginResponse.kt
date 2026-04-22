package com.example.al_kutub.model


import com.google.gson.annotations.SerializedName

data class LoginResponse(
    @SerializedName("data")
    val `data`: LoginData,
    @SerializedName("message")
    val message: String,
    @SerializedName("success")
    val success: Boolean
) {
    val username: String
        get() = data.username
    
    val token: String?
        get() = data.token

    val refreshToken: String?
        get() = data.refreshToken
        
    val requires2FA: Boolean
        get() = data.requires2FA
        
    val tempToken: String?
        get() = data.tempToken
        
    val userId: Int?
        get() = data.userId

    val requiresEmailVerification: Boolean
        get() = data.requiresEmailVerification

    val verificationToken: String?
        get() = data.verificationToken
}

data class LoginData(
    @SerializedName("id")
    val id: Int,
    @SerializedName("username")
    val username: String,
    @SerializedName("role")
    val role: String,
    @SerializedName("email")
    val email: String,
    @SerializedName("token")
    val token: String? = null,
    @SerializedName("refresh_token")
    val refreshToken: String? = null,
    @SerializedName("requires_2fa")
    val requires2FA: Boolean = false,
    @SerializedName("temp_token")
    val tempToken: String? = null,
    @SerializedName("user_id")
    val userId: Int? = null,
    @SerializedName("requires_email_verification")
    val requiresEmailVerification: Boolean = false,
    @SerializedName("verification_token")
    val verificationToken: String? = null,
    @SerializedName("requires_admin_approval")
    val requiresAdminApproval: Boolean = false
)
