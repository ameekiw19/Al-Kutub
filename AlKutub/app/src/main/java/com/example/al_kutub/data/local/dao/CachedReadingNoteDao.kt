package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.example.al_kutub.data.local.entity.CachedReadingNoteEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface CachedReadingNoteDao {
    @Query("SELECT * FROM cached_reading_notes WHERE userId = :userId ORDER BY updatedAt DESC")
    fun observeForUser(userId: Int): Flow<List<CachedReadingNoteEntity>>

    @Query("SELECT * FROM cached_reading_notes WHERE userId = :userId ORDER BY updatedAt DESC")
    suspend fun getForUser(userId: Int): List<CachedReadingNoteEntity>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsertAll(items: List<CachedReadingNoteEntity>)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsert(item: CachedReadingNoteEntity)

    @Query("DELETE FROM cached_reading_notes WHERE userId = :userId AND noteId = :noteId")
    suspend fun deleteById(userId: Int, noteId: Int)

    @Query("DELETE FROM cached_reading_notes WHERE userId = :userId")
    suspend fun clearForUser(userId: Int)

    @Query("DELETE FROM cached_reading_notes")
    suspend fun clearAll()
}

