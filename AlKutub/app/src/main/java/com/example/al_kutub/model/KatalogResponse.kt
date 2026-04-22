package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class KatalogResponse(
    @SerializedName("success") val success: Boolean,
    @SerializedName("message") val message: String? = null,
    @SerializedName("data") val data: KatalogData? = null,
    @SerializedName("pagination") val pagination: KatalogPagination? = null
)

data class KatalogData(
    @SerializedName("kategori") val kategori: List<String> = emptyList(),
    @SerializedName("kitab") val kitab: List<Kitab> = emptyList(),
    @SerializedName("bahasa") val bahasa: List<String> = emptyList(),
    @SerializedName("bookmarkedIds") val bookmarkedIds: List<Int> = emptyList()
)

data class KatalogPagination(
    @SerializedName("current_page") val currentPage: Int = 1,
    @SerializedName("last_page") val lastPage: Int = 1,
    @SerializedName("per_page") val perPage: Int = 20,
    @SerializedName("total") val total: Int = 0
)
