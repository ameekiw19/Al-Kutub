package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class SearchResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String,
    @SerializedName("data") val data: List<SearchResult>,
    @SerializedName("total") val total: Int = 0,
    @SerializedName("count") val count: Int = 0,
    @SerializedName("filters") val filters: SearchFilterOptions = SearchFilterOptions()
)

data class SearchResult(
    @SerializedName(value = "idKitab", alternate = ["id_kitab", "id"])
    val idKitab: Int,
    @SerializedName("judul") val judul: String,
    @SerializedName("penulis") val penulis: String,
    @SerializedName("cover") val cover: String,
    @SerializedName("kategori") val kategori: String,
    @SerializedName("deskripsi") val deskripsi: String = "",
    @SerializedName("bahasa") val bahasa: String = "",
    @SerializedName("filePdf") val filePdf: String? = null,
    @SerializedName("views") val views: Int = 0,
    @SerializedName("downloads") val downloads: Int = 0,
    @SerializedName("averageRating") val averageRating: Double? = null,
    @SerializedName("ratingsCount") val ratingsCount: Int = 0
)

data class SearchFilterOptions(
    @SerializedName("categories") val categories: List<String> = emptyList(),
    @SerializedName("authors") val authors: List<String> = emptyList(),
    @SerializedName("languages") val languages: List<String> = emptyList()
)
