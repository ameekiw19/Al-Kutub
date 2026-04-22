package com.example.al_kutub.model

data class ApiResponse<T>(
    val success: Boolean,
    val message: String,
    val data: T?
)
