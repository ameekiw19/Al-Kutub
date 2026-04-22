package com.example.al_kutub.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "downloaded_kitabs")
data class DownloadedKitabEntity(
    @PrimaryKey
    val kitabId: Int, // Use kitab ID as primary key since one entry per kitab
    val title: String,
    val filePath: String,
    val coverPath: String,
    val downloadedAt: Long = System.currentTimeMillis()
)
