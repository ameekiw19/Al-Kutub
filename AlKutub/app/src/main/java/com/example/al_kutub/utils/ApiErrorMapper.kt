package com.example.al_kutub.utils

object ApiErrorMapper {
    fun map(httpCode: Int, fallbackMessage: String? = null): String {
        return when (httpCode) {
            401 -> "AUTH_401: Sesi berakhir, silakan login kembali."
            403 -> "AUTH_403: Anda tidak memiliki akses ke fitur ini."
            404 -> "NOT_FOUND_404: Data yang diminta tidak ditemukan."
            422 -> "VALIDATION_422: Data yang dikirim tidak valid."
            in 500..599 -> "SERVER_5XX: Terjadi gangguan pada server."
            else -> fallbackMessage ?: "UNKNOWN_ERROR: Terjadi kesalahan."
        }
    }
}

