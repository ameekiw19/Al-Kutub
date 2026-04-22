package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.example.al_kutub.data.local.entity.CachedBookmarkEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface CachedBookmarkDao {
    @Query("SELECT * FROM cached_bookmarks WHERE userId = :userId ORDER BY updatedAt DESC")
    fun observeForUser(userId: Int): Flow<List<CachedBookmarkEntity>>

    @Query("SELECT * FROM cached_bookmarks WHERE userId = :userId ORDER BY updatedAt DESC")
    suspend fun getForUser(userId: Int): List<CachedBookmarkEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsertAll(items: List<CachedBookmarkEntity>)

    @Query("DELETE FROM cached_bookmarks WHERE userId = :userId")
    suspend fun clearForUser(userId: Int)

    @Query("DELETE FROM cached_bookmarks")
    suspend fun clearAll()
}

