package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// Audit Logs Response
data class AuditLogsResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("data")
    val data: AuditLogsData
)

data class AuditLogsData(
    @SerializedName("logs")
    val logs: List<AuditLog>,
    @SerializedName("pagination")
    val pagination: AuditPagination
)

data class AuditPagination(
    @SerializedName("current_page")
    val currentPage: Int,
    @SerializedName("last_page")
    val lastPage: Int,
    @SerializedName("per_page")
    val perPage: Int,
    @SerializedName("total")
    val total: Int,
    @SerializedName("has_more")
    val hasMore: Boolean
)

// Audit Log Model
data class AuditLog(
    @SerializedName("id")
    val id: Int,
    @SerializedName("user_id")
    val userId: Int? = null,
    @SerializedName("action")
    val action: String,
    @SerializedName("model_type")
    val modelType: String? = null,
    @SerializedName("model_id")
    val modelId: Int? = null,
    @SerializedName("old_values")
    val oldValues: Map<String, Any>? = null,
    @SerializedName("new_values")
    val newValues: Map<String, Any>? = null,
    @SerializedName("ip_address")
    val ipAddress: String? = null,
    @SerializedName("user_agent")
    val userAgent: String? = null,
    @SerializedName("created_at")
    val createdAt: String,
    @SerializedName("user")
    val user: AuditUser? = null
)

data class AuditUser(
    @SerializedName("id")
    val id: Int,
    @SerializedName("username")
    val username: String,
    @SerializedName("email")
    val email: String,
    @SerializedName("role")
    val role: String
)

// Audit Stats Response
data class AuditStatsResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("data")
    val data: AuditStatsData
)

data class AuditStatsData(
    @SerializedName("overview")
    val overview: AuditOverview,
    @SerializedName("login_stats")
    val loginStats: LoginStats,
    @SerializedName("two_factor_stats")
    val twoFactorStats: TwoFactorStats,
    @SerializedName("recent_activity")
    val recentActivity: List<RecentActivity>,
    @SerializedName("daily_activity")
    val dailyActivity: List<DailyActivity>
)

data class AuditOverview(
    @SerializedName("total_logs")
    val totalLogs: Int,
    @SerializedName("today_logs")
    val todayLogs: Int,
    @SerializedName("security_logs")
    val securityLogs: Int
)

data class LoginStats(
    @SerializedName("successful_logins")
    val successfulLogins: Int,
    @SerializedName("failed_logins")
    val failedLogins: Int
)

data class TwoFactorStats(
    @SerializedName("enabled")
    val enabled: Int,
    @SerializedName("disabled")
    val disabled: Int,
    @SerializedName("verified")
    val verified: Int
)

data class RecentActivity(
    @SerializedName("id")
    val id: Int,
    @SerializedName("action")
    val action: String,
    @SerializedName("created_at")
    val createdAt: String,
    @SerializedName("ip_address")
    val ipAddress: String
)

data class DailyActivity(
    @SerializedName("date")
    val date: String,
    @SerializedName("count")
    val count: Int
)

// Audit Log Action Types
object AuditActions {
    const val LOGIN = "login"
    const val LOGIN_FAILED = "login_failed"
    const val LOGOUT = "logout"
    const val PASSWORD_CHANGED = "password_changed"
    const val PROFILE_UPDATED = "profile_updated"
    const val TWO_FA_ENABLED = "2fa_enabled"
    const val TWO_FA_DISABLED = "2fa_disabled"
    const val TWO_FA_VERIFIED = "2fa_verified"
    const val TWO_FA_VERIFICATION_FAILED = "2fa_verification_failed"
    const val BACKUP_CODES_REGENERATED = "backup_codes_regenerated"
    const val BACKUP_CODE_USED = "backup_code_used"
    const val KITAB_CREATED = "kitab_created"
    const val KITAB_UPDATED = "kitab_updated"
    const val KITAB_DELETED = "kitab_deleted"
    const val USER_CREATED = "user_created"
    const val USER_DELETED = "user_deleted"
    const val ROLE_UPDATED = "role_updated"
}

// Audit Log Extensions
fun AuditLog.isSecurityAction(): Boolean {
    return action in listOf(
        AuditActions.LOGIN,
        AuditActions.LOGIN_FAILED,
        AuditActions.LOGOUT,
        AuditActions.PASSWORD_CHANGED,
        AuditActions.TWO_FA_ENABLED,
        AuditActions.TWO_FA_DISABLED,
        AuditActions.TWO_FA_VERIFIED,
        AuditActions.TWO_FA_VERIFICATION_FAILED,
        AuditActions.BACKUP_CODES_REGENERATED,
        AuditActions.BACKUP_CODE_USED
    )
}

fun AuditLog.isAdminAction(): Boolean {
    return action in listOf(
        AuditActions.KITAB_CREATED,
        AuditActions.KITAB_UPDATED,
        AuditActions.KITAB_DELETED,
        AuditActions.USER_CREATED,
        AuditActions.USER_DELETED,
        AuditActions.ROLE_UPDATED
    )
}

fun AuditLog.getActionDisplayName(): String {
    return when (action) {
        AuditActions.LOGIN -> "Login Berhasil"
        AuditActions.LOGIN_FAILED -> "Login Gagal"
        AuditActions.LOGOUT -> "Logout"
        AuditActions.PASSWORD_CHANGED -> "Password Diubah"
        AuditActions.PROFILE_UPDATED -> "Profil Diperbarui"
        AuditActions.TWO_FA_ENABLED -> "2FA Diaktifkan"
        AuditActions.TWO_FA_DISABLED -> "2FA Dinonaktifkan"
        AuditActions.TWO_FA_VERIFIED -> "2FA Terverifikasi"
        AuditActions.TWO_FA_VERIFICATION_FAILED -> "2FA Verifikasi Gagal"
        AuditActions.BACKUP_CODES_REGENERATED -> "Backup Codes Diperbarui"
        AuditActions.BACKUP_CODE_USED -> "Backup Code Digunakan"
        AuditActions.KITAB_CREATED -> "Kitab Dibuat"
        AuditActions.KITAB_UPDATED -> "Kitab Diperbarui"
        AuditActions.KITAB_DELETED -> "Kitab Dihapus"
        AuditActions.USER_CREATED -> "User Dibuat"
        AuditActions.USER_DELETED -> "User Dihapus"
        AuditActions.ROLE_UPDATED -> "Role Diperbarui"
        else -> action
    }
}

fun AuditLog.getActionColor(): String {
    return when {
        isSecurityAction() -> "#dc3545" // Red
        isAdminAction() -> "#ffc107" // Yellow
        else -> "#17a2b8" // Blue
    }
}
