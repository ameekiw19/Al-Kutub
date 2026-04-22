package com.example.al_kutub.data.local

import androidx.room.Database
import androidx.room.RoomDatabase
import com.example.al_kutub.data.dao.ReadingProgressDao
import com.example.al_kutub.data.local.dao.CachedBookmarkDao
import com.example.al_kutub.data.local.dao.CachedHistoryDao
import com.example.al_kutub.data.local.dao.CachedReadingNoteDao
import com.example.al_kutub.data.local.dao.DownloadTaskDao
import com.example.al_kutub.data.local.dao.DownloadedKitabDao
import com.example.al_kutub.data.local.dao.PageBookmarkDao
import com.example.al_kutub.data.local.dao.SyncOperationDao
import com.example.al_kutub.data.local.entity.CachedBookmarkEntity
import com.example.al_kutub.data.local.entity.CachedHistoryEntity
import com.example.al_kutub.data.local.entity.CachedReadingNoteEntity
import com.example.al_kutub.data.local.entity.DownloadTaskEntity
import com.example.al_kutub.data.local.entity.DownloadedKitabEntity
import com.example.al_kutub.data.local.entity.PageBookmarkEntity
import com.example.al_kutub.data.local.entity.SyncOperationEntity
import com.example.al_kutub.model.ReadingProgress

@Database(
    entities = [
        DownloadedKitabEntity::class,
        ReadingProgress::class,
        SyncOperationEntity::class,
        CachedBookmarkEntity::class,
        CachedHistoryEntity::class,
        CachedReadingNoteEntity::class,
        DownloadTaskEntity::class,
        PageBookmarkEntity::class
    ], 
    version = 10, 
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {
    abstract fun downloadedKitabDao(): DownloadedKitabDao
    abstract fun readingProgressDao(): ReadingProgressDao
    abstract fun syncOperationDao(): SyncOperationDao
    abstract fun cachedBookmarkDao(): CachedBookmarkDao
    abstract fun cachedHistoryDao(): CachedHistoryDao
    abstract fun cachedReadingNoteDao(): CachedReadingNoteDao
    abstract fun downloadTaskDao(): DownloadTaskDao
    abstract fun pageBookmarkDao(): PageBookmarkDao
}
