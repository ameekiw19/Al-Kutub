package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "download_tasks",
    indices = [
        Index(value = ["userId", "kitabId"], unique = true),
        Index(value = ["status", "updatedAt"])
    ]
)
data class DownloadTaskEntity(
    @PrimaryKey(autoGenerate = true)
    val taskId: Long = 0,
    val userId: Int,
    val kitabId: Int,
    val title: String,
    val fileName: String,
    val targetPath: String,
    val tempPath: String,
    val downloadedBytes: Long = 0L,
    val totalBytes: Long = 0L,
    val status: String = STATUS_QUEUED,
    val progressPercent: Int = 0,
    val errorMessage: String? = null,
    val etag: String? = null,
    val lastModified: String? = null,
    val createdAt: Long = System.currentTimeMillis(),
    val updatedAt: Long = System.currentTimeMillis()
) {
    companion object {
        const val STATUS_QUEUED = "queued"
        const val STATUS_DOWNLOADING = "downloading"
        const val STATUS_PAUSED = "paused"
        const val STATUS_COMPLETED = "completed"
        const val STATUS_FAILED = "failed"
        const val STATUS_CANCELED = "canceled"
    }
}

