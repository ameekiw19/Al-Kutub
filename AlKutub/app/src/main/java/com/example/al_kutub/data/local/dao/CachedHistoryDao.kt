package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.example.al_kutub.data.local.entity.CachedHistoryEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface CachedHistoryDao {
    @Query("SELECT * FROM cached_histories WHERE userId = :userId ORDER BY updatedAt DESC")
    fun observeForUser(userId: Int): Flow<List<CachedHistoryEntity>>

    @Query("SELECT * FROM cached_histories WHERE userId = :userId ORDER BY updatedAt DESC")
    suspend fun getForUser(userId: Int): List<CachedHistoryEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsertAll(items: List<CachedHistoryEntity>)

    @Query("DELETE FROM cached_histories WHERE userId = :userId")
    suspend fun clearForUser(userId: Int)

    @Query("DELETE FROM cached_histories")
    suspend fun clearAll()
}

