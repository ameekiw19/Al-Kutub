package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// 2FA Status Response
data class TwoFactorStatusResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("data")
    val data: TwoFactorStatusData
)

data class TwoFactorStatusData(
    @SerializedName("enabled")
    val enabled: Boolean,
    @SerializedName("enabled_at")
    val enabledAt: String? = null,
    @SerializedName("last_used_at")
    val lastUsedAt: String? = null,
    @SerializedName("backup_codes_count")
    val backupCodesCount: Int = 0
)

// 2FA Setup Response
data class TwoFactorSetupResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("data")
    val data: TwoFactorSetupData
)

data class TwoFactorSetupData(
    @SerializedName("secret_key")
    val secretKey: String,
    @SerializedName("qr_code_url")
    val qrCodeUrl: String,
    @SerializedName("backup_codes")
    val backupCodes: List<String>,
    @SerializedName("manual_entry_key")
    val manualEntryKey: String
)

// 2FA Enable Response
data class TwoFactorEnableResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: TwoFactorEnableData? = null
)

data class TwoFactorEnableData(
    @SerializedName("enabled_at")
    val enabledAt: String
)

// Backup Codes Response
data class BackupCodesResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: BackupCodesData? = null
)

data class BackupCodesData(
    @SerializedName("backup_codes")
    val backupCodes: List<String>,
    @SerializedName("remaining_count")
    val remainingCount: Int = 0
)

// 2FA Verification Request
data class TwoFactorVerifyRequest(
    @SerializedName("code")
    val code: String
)

// 2FA Disable Request
data class TwoFactorDisableRequest(
    @SerializedName("password")
    val password: String,
    @SerializedName("code")
    val code: String
)

// Backup Code Verification Request
data class BackupCodeVerifyRequest(
    @SerializedName("code")
    val code: String
)
