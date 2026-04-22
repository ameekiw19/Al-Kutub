package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "page_bookmarks",
    indices = [
        Index(value = ["userId", "kitabId", "pageNumber"], unique = true),
        Index(value = ["userId", "kitabId"])
    ]
)
data class PageBookmarkEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Long = 0,
    val userId: Int,
    val kitabId: Int,
    val pageNumber: Int,
    val label: String,
    val createdAt: Long,
    val updatedAt: Long,
    val isPendingSync: Boolean,
    val lastSyncedAt: Long?
)
