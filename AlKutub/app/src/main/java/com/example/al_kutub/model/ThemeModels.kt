package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// Theme Response
data class ThemeResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: ThemeData
)

data class ThemeData(
    @SerializedName("theme")
    val theme: String
)

// Theme Request
data class ThemeRequest(
    @SerializedName("theme")
    val theme: String
)

// Theme enum
enum class ThemeMode(val value: String) {
    LIGHT("light"),
    DARK("dark"),
    AUTO("auto");
    
    companion object {
        fun fromString(value: String): ThemeMode {
            return values().find { it.value == value } ?: LIGHT
        }
    }
}
