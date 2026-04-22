package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class AppNotification(
    @SerializedName("id")
    val id: Int,
    
    @SerializedName("title")
    val title: String,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("type")
    val type: String,
    
    @SerializedName("action_url")
    val actionUrl: String? = null,
    
    @SerializedName("data")
    val data: String? = null,
    
    @SerializedName("read_at")
    val readAt: String? = null,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String? = null
)
