package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.Index

@Entity(
    tableName = "cached_histories",
    primaryKeys = ["userId", "historyId"],
    indices = [Index(value = ["userId", "updatedAt"])]
)
data class CachedHistoryEntity(
    val userId: Int,
    val historyId: Int,
    val kitabId: Int,
    val judul: String,
    val penulis: String,
    val cover: String?,
    val kategori: String?,
    val timeAgo: String,
    val lastReadAt: String,
    val currentPage: Int,
    val totalPages: Int,
    val readingTimeMinutes: Int,
    val updatedAt: Long = System.currentTimeMillis()
)

