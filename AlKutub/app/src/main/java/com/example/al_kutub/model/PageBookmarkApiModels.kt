package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class PageBookmarkRemoteData(
    @SerializedName("id")
    val id: Long = 0L,
    @SerializedName("user_id")
    val userId: Int = 0,
    @SerializedName("kitab_id")
    val kitabId: Int = 0,
    @SerializedName("page_number")
    val pageNumber: Int = 0,
    @SerializedName("label")
    val label: String = "",
    @SerializedName("created_at")
    val createdAt: String? = null,
    @SerializedName("updated_at")
    val updatedAt: String? = null
)
