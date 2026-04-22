package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class RateKitabResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: RatingData? = null
)

data class RatingData(
    @SerializedName("rating")
    val rating: Int,
    
    @SerializedName("averageRating")
    val averageRating: Double,
    
    @SerializedName("ratingsCount")
    val ratingsCount: Int
)

data class MyRatingResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("data")
    val data: MyRatingData? = null
)

data class MyRatingData(
    @SerializedName("myRating")
    val myRating: Int
)
