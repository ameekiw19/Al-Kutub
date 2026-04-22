package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class VerificationStatusResponse(
    val success: Boolean,
    val message: String,
    val data: VerificationStatusData? = null
)

data class VerificationStatusData(
    val verified: Boolean = false,
    val email: String? = null,
    @SerializedName("requires_email_verification")
    val requiresEmailVerification: Boolean = false,
    @SerializedName("verification_token")
    val verificationToken: String? = null
)
