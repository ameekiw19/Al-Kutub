package com.example.al_kutub.model

// ============== Response Models ==============

data class BaseResponse(
    val success: Boolean,
    val message: String
)

data class HistoryData(
    val total: Int,
    val histories: Map<String, List<HistoryItemData>>,
    val raw_histories: List<HistoryItemData>
)

data class HistoryItemData(
    @com.google.gson.annotations.SerializedName(value = "id", alternate = ["id_history"])
    val id: Int,

    @com.google.gson.annotations.SerializedName("kitab_id")
    val kitab_id: Int,

    @com.google.gson.annotations.SerializedName("last_read_at")
    val last_read_at: String = "",

    @com.google.gson.annotations.SerializedName("time_ago")
    val time_ago: String = "",

    @com.google.gson.annotations.SerializedName("current_page")
    val current_page: Int = 0,

    @com.google.gson.annotations.SerializedName("total_pages")
    val total_pages: Int = 0,

    @com.google.gson.annotations.SerializedName("last_position")
    val last_position: String? = null,

    @com.google.gson.annotations.SerializedName("reading_time_minutes")
    val reading_time_minutes: Int = 0,

    @com.google.gson.annotations.SerializedName("kitab")
    val kitab: KitabData? = null
)

data class KitabData(
    @com.google.gson.annotations.SerializedName(value = "id", alternate = ["id_kitab", "idKitab"])
    val id: Int,
    @com.google.gson.annotations.SerializedName("judul")
    val judul: String,
    @com.google.gson.annotations.SerializedName("penulis")
    val penulis: String,
    @com.google.gson.annotations.SerializedName("kategori")
    val kategori: String,
    @com.google.gson.annotations.SerializedName("bahasa")
    val bahasa: String,
    @com.google.gson.annotations.SerializedName("cover")
    val cover: String,
    @com.google.gson.annotations.SerializedName("views")
    val views: Int,
    @com.google.gson.annotations.SerializedName("downloads")
    val downloads: Int
)

data class HistoryDetailResponse(
    val success: Boolean,
    val message: String,
    val data: HistoryDetailData?
)

data class HistoryDetailData(
    val id: Int,
    val kitab_id: Int,
    val last_read_at: String,
    val time_ago: String,
    val created_at: String,
    val current_page: Int?,
    val total_pages: Int?,
    val last_position: String?,
    val reading_time_minutes: Int?,
    val kitab: KitabData
)

data class ClearHistoryResponse(
    val success: Boolean,
    val message: String,
    val data: ClearHistoryData?
)

data class ClearHistoryData(
    val deleted_count: Int
)

data class HistoryStatsResponse(
    val success: Boolean,
    val message: String,
    val data: HistoryStatsData?
)

data class HistoryStatsData(
    val total_kitab: Int,
    val today_count: Int,
    val this_week_count: Int,
    val this_month_count: Int,
    val top_categories: List<TopCategory>,
    val recently_read: List<RecentlyRead>
)

data class TopCategory(
    val category: String,
    val total: Int
)

data class RecentlyRead(
    val kitab_id: Int,
    val judul: String,
    val penulis: String,
    val cover: String,
    val last_read_at: String,
    val time_ago: String
)
