package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// Comment entity model - sesuai dengan database MySQL
data class Comment(
    @SerializedName(value = "id_comment", alternate = ["id"])
    val id: Int = 0,

    @SerializedName(value = "id_kitab", alternate = ["kitab_id"])
    val idKitab: Int = 0,

    @SerializedName(value = "user_id", alternate = ["id_user"])
    val idUser: Int = 0,

    @SerializedName(value = "username", alternate = ["user_username", "name"])
    private val usernameRaw: String = "",

    @SerializedName(value = "isi_comment", alternate = ["comment", "isi", "isi_komentar", "komentar", "text"])
    val comment: String = "",

    @SerializedName("created_at")
    val createdAt: String = "",

    @SerializedName("updated_at")
    val updatedAt: String = "",

    @SerializedName(value = "user", alternate = ["user_data", "users"])
    val user: CommentUser? = null,

    @SerializedName(value = "user_rating", alternate = ["rating"])
    val rating: Int = 0
) {
    val username: String
        get() = usernameRaw.ifBlank { user?.username ?: "User #$idUser" }

    // Helper function to get formatted date
    fun getFormattedDate(): String {
        return try {
            when {
                createdAt.isEmpty() -> "Baru saja"
                createdAt.contains("hari yang lalu") -> createdAt
                createdAt.contains("jam yang lalu") -> createdAt
                createdAt.contains("menit yang lalu") -> createdAt
                createdAt.contains("detik yang lalu") -> createdAt
                createdAt.contains("T") -> {
                    // Format ISO 8601 (2024-01-07T10:30:00+00:00)
                    try {
                        val parts = createdAt.split("T")
                        if (parts.isNotEmpty()) {
                            val datePart = parts[0]
                            // Parse the date to check if it's in the future
                            val dateComponents = datePart.split("-")
                            if (dateComponents.size >= 3) {
                                val year = dateComponents[0].toIntOrNull()
                                val month = dateComponents[1].toIntOrNull()
                                val day = dateComponents[2].toIntOrNull()
                                
                                if (year != null && month != null && day != null) {
                                    val currentYear = java.util.Calendar.getInstance().get(java.util.Calendar.YEAR)
                                    // If the year is in the future, it might be a test date
                                    if (year > currentYear) {
                                        return "$day-${month.toString().padStart(2, '0')}-$year"
                                    }
                                }
                            }
                            datePart
                        } else {
                            createdAt
                        }
                    } catch (e: Exception) {
                        createdAt
                    }
                }
                createdAt.contains("-") -> {
                    // Format tanggal ISO (2024-01-07)
                    try {
                        val parts = createdAt.split(" ")
                        parts[0] // Ambil hanya tanggalnya
                    } catch (e: Exception) {
                        createdAt
                    }
                }
                else -> createdAt
            }
        } catch (e: Exception) {
            "Baru saja"
        }
    }
}

data class CommentUser(
    @SerializedName(value = "id", alternate = ["user_id"])
    val id: Int = 0,

    @SerializedName(value = "username", alternate = ["name"])
    val username: String = ""
)

// Request model untuk submit comment - sesuai dengan database field
data class CommentRequest(
    @SerializedName("id_kitab")
    val idKitab: Int,

    @SerializedName("user_id")
    val userId: Int,

    @SerializedName("isi_komentar")
    val isiComment: String
)