package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.Index

@Entity(
    tableName = "cached_reading_notes",
    primaryKeys = ["userId", "noteId"],
    indices = [Index(value = ["userId", "updatedAt"])]
)
data class CachedReadingNoteEntity(
    val userId: Int,
    val noteId: Int,
    val kitabId: Int,
    val kitabTitle: String?,
    val noteContent: String,
    val pageNumber: Int?,
    val highlightedText: String?,
    val noteColor: String,
    val isPrivate: Boolean,
    val createdAt: String,
    val remoteUpdatedAt: String,
    val clientRequestId: String? = null,
    val updatedAt: Long = System.currentTimeMillis()
)

