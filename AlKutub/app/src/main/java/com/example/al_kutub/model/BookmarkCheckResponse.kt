package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class BookmarkCheckResponse(
    @SerializedName("status")
    val status: String,

    @SerializedName("is_bookmarked")
    val isBookmarked: Boolean,

    @SerializedName("bookmark_id")
    val bookmarkId: Int?
)
