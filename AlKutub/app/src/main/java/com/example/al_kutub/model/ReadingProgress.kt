package com.example.al_kutub.model

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(
    tableName = "reading_progress",
    indices = [Index(value = ["userId", "kitabId"], unique = true)]
)
data class ReadingProgress(
    @PrimaryKey(autoGenerate = true)
    var id: Int = 0,
    
    @SerializedName("user_id")
    val userId: Int,
    
    @SerializedName("kitab_id")
    val kitabId: Int,
    
    @SerializedName("last_page_read")
    var lastPageRead: Int = 0,
    
    @SerializedName("total_pages")
    var totalPages: Int = 0,
    
    @SerializedName("progress_percentage")
    var progressPercentage: Float = 0f,
    
    @SerializedName("last_read_position")
    var lastReadPosition: Long = 0L, // Position in PDF for precise resume
    
    @SerializedName("reading_time_minutes")
    var readingTimeMinutes: Int = 0,
    
    @SerializedName("last_read_at")
    var lastReadAt: Long = System.currentTimeMillis(),
    
    @SerializedName("is_completed")
    var isCompleted: Boolean = false,
    
    @SerializedName("completed_at")
    var completedAt: Long? = null,
    
    @SerializedName("bookmark_notes")
    var bookmarkNotes: String? = null
) {
    fun updateProgress(newPage: Int, total: Int) {
        lastPageRead = newPage
        totalPages = total
        progressPercentage = if (total > 0) (newPage.toFloat() / total) * 100 else 0f
        lastReadAt = System.currentTimeMillis()
        
        if (progressPercentage >= 100f && !isCompleted) {
            isCompleted = true
            completedAt = System.currentTimeMillis()
        }
    }
    
    fun getProgressText(): String {
        return "${lastPageRead}/${totalPages} halaman"
    }
    
    fun getTimeAgo(): String {
        val now = System.currentTimeMillis()
        val diff = now - lastReadAt
        
        return when {
            diff < 60_000 -> "Baru saja"
            diff < 3600_000 -> "${diff / 60_000} menit yang lalu"
            diff < 86400_000 -> "${diff / 3600_000} jam yang lalu"
            diff < 604800_000 -> "${diff / 86400_000} hari yang lalu"
            else -> "${diff / 604800_000} minggu yang lalu"
        }
    }
}
