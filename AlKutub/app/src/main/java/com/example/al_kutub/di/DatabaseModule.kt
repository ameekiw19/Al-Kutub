package com.example.al_kutub.di

import android.content.Context
import androidx.room.Room
import com.example.al_kutub.data.local.AppDatabase
import com.example.al_kutub.data.dao.ReadingProgressDao
import com.example.al_kutub.data.local.MIGRATION_8_9
import com.example.al_kutub.data.local.MIGRATION_9_10
import com.example.al_kutub.data.local.dao.CachedBookmarkDao
import com.example.al_kutub.data.local.dao.CachedHistoryDao
import com.example.al_kutub.data.local.dao.CachedReadingNoteDao
import com.example.al_kutub.data.local.dao.DownloadTaskDao
import com.example.al_kutub.data.local.dao.DownloadedKitabDao
import com.example.al_kutub.data.local.dao.PageBookmarkDao
import com.example.al_kutub.data.local.dao.SyncOperationDao
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object DatabaseModule {

    @Provides
    @Singleton
    fun provideAppDatabase(@ApplicationContext context: Context): AppDatabase {
        return Room.databaseBuilder(
            context,
            AppDatabase::class.java,
            "alkutub_database"
        )
            .addMigrations(MIGRATION_8_9, MIGRATION_9_10)
            .build()
    }

    @Provides
    @Singleton
    fun provideDownloadedKitabDao(database: AppDatabase): DownloadedKitabDao {
        return database.downloadedKitabDao()
    }

    @Provides
    @Singleton
    fun provideReadingProgressDao(database: AppDatabase): ReadingProgressDao {
        return database.readingProgressDao()
    }

    @Provides
    @Singleton
    fun provideSyncOperationDao(database: AppDatabase): SyncOperationDao {
        return database.syncOperationDao()
    }

    @Provides
    @Singleton
    fun provideCachedBookmarkDao(database: AppDatabase): CachedBookmarkDao {
        return database.cachedBookmarkDao()
    }

    @Provides
    @Singleton
    fun provideCachedHistoryDao(database: AppDatabase): CachedHistoryDao {
        return database.cachedHistoryDao()
    }

    @Provides
    @Singleton
    fun provideCachedReadingNoteDao(database: AppDatabase): CachedReadingNoteDao {
        return database.cachedReadingNoteDao()
    }

    @Provides
    @Singleton
    fun provideDownloadTaskDao(database: AppDatabase): DownloadTaskDao {
        return database.downloadTaskDao()
    }

    @Provides
    @Singleton
    fun providePageBookmarkDao(database: AppDatabase): PageBookmarkDao {
        return database.pageBookmarkDao()
    }
}
