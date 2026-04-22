package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// ============== Account Response Models ==============

data class AccountResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: AccountData
)

data class AccountData(
    @SerializedName("profile") val profile: ProfileData,
    @SerializedName("statistik") val statistik: StatistikData,
    @SerializedName("aktivitas_terbaru") val aktivitasTerbaru: List<AccountHistoryItem>
)

data class ProfileData(
    @SerializedName("id") val id: Int,
    @SerializedName("username") val username: String,
    @SerializedName("email") val email: String,
    @SerializedName("deskripsi") val deskripsi: String?,
    @SerializedName("role") val role: String,
    @SerializedName("bergabung_sejak") val bergabungSejak: String
)

data class StatistikData(
    @SerializedName("kitab_dibaca") val kitabDibaca: Int,
    @SerializedName("bookmark") val bookmark: Int,
    @SerializedName("komentar") val komentar: Int
)

// ============== Account History Response ==============

data class AccountHistoryResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<AccountHistoryItem>
)

data class AccountHistoryItem(
    @SerializedName("id") val id: Int,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("kitab_id") val kitabId: Int,
    @SerializedName("created_at") val createdAt: String,
    @SerializedName("kitab") val kitab: KitabSimple?
)

data class KitabSimple(
    @SerializedName("id_kitab") val id: Int,
    @SerializedName("judul") val judul: String,
    @SerializedName("cover") val cover: String?,
    @SerializedName("penulis") val penulis: String?,
    @SerializedName("views") val views: Int?
)

// ============== Account Bookmarks Response ==============

data class AccountBookmarksResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<AccountBookmarkItem>
)

data class AccountBookmarkItem(
    @SerializedName("id") val id: Int,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("kitab_id") val kitabId: Int,
    @SerializedName("created_at") val createdAt: String,
    @SerializedName("kitab") val kitab: KitabSimple?
)

// ============== Account Comments Response ==============

data class AccountCommentsResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<AccountCommentItem>
)

data class AccountCommentItem(
    @SerializedName("id") val id: Int,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("kitab_id") val kitabId: Int,
    @SerializedName("comment") val comment: String,
    @SerializedName("created_at") val createdAt: String,
    @SerializedName("kitab") val kitab: KitabComment?
)

data class KitabComment(
    @SerializedName("id_kitab") val id: Int,
    @SerializedName("judul") val judul: String
)

// ============== Update Profile Request/Response ==============

data class UpdateProfileRequest(
    @SerializedName("username") val username: String,
    @SerializedName("email") val email: String,
    @SerializedName("deskripsi") val deskripsi: String? = null,
    @SerializedName("password") val password: String? = null,
    @SerializedName("password_confirmation") val passwordConfirmation: String? = null
)

data class UpdateProfileResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: UpdateProfileData?
)

data class UpdateProfileData(
    @SerializedName("profile") val profile: ProfileData
)

// ============== Logout Response ==============

data class LogoutResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String
)