package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class BookmarkResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String,
    @SerializedName("total") val total: Int? = null,
    @SerializedName("data") val data: List<BookmarkItem>? = null
)

data class BookmarkItem(
    @SerializedName("id_bookmark") val idBookmark: Int,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("id_kitab") val idKitab: Int,
    @SerializedName("created_at") val createdAt: String,
    @SerializedName("updated_at") val updatedAt: String,
    @SerializedName("kitab") val kitab: Kitab?
)

data class BookmarkToggleResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String,
    @SerializedName("action") val action: String?, // "added" or "removed"
    @SerializedName("is_bookmarked") val isBookmarked: Boolean,
    @SerializedName("data") val data: BookmarkData? = null
)

data class BookmarkData(
    @SerializedName("id_bookmark") val idBookmark: Int,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("id_kitab") val idKitab: Int,
    @SerializedName("created_at") val createdAt: String
)


data class BookmarkStatsResponse(
    @SerializedName("status") val status: String,
    @SerializedName("data") val data: BookmarkStatsData
)

data class BookmarkStatsData(
    @SerializedName("total_bookmarks") val totalBookmarks: Int,
    @SerializedName("by_category") val byCategory: List<CategoryStats>,
    @SerializedName("recent_bookmarks") val recentBookmarks: List<RecentBookmark>
)

data class CategoryStats(
    @SerializedName("kategori") val kategori: String,
    @SerializedName("total") val total: Int
)

data class RecentBookmark(
    @SerializedName("id_bookmark") val idBookmark: Int,
    @SerializedName("kitab") val kitab: Kitab?,
    @SerializedName("created_at") val createdAt: String
)

data class BookmarkDeleteResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String,
    @SerializedName("deleted_count") val deletedCount: Int? = null
)