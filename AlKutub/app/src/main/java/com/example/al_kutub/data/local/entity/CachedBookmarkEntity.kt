package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.Index

@Entity(
    tableName = "cached_bookmarks",
    primaryKeys = ["userId", "kitabId"],
    indices = [Index(value = ["userId", "updatedAt"])]
)
data class CachedBookmarkEntity(
    val userId: Int,
    val kitabId: Int,
    val bookmarkId: Int?,
    val createdAt: String,
    val judul: String,
    val penulis: String,
    val cover: String?,
    val kategori: String?,
    val updatedAt: Long = System.currentTimeMillis()
)

