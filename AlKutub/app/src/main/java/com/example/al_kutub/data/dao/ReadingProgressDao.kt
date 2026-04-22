package com.example.al_kutub.data.dao

import androidx.room.*
import com.example.al_kutub.model.ReadingProgress
import kotlinx.coroutines.flow.Flow

@Dao
interface ReadingProgressDao {
    
    @Query("SELECT * FROM reading_progress WHERE userId = :userId AND kitabId = :kitabId")
    suspend fun getProgress(userId: Int, kitabId: Int): ReadingProgress?
    
    @Query("SELECT * FROM reading_progress WHERE userId = :userId ORDER BY lastReadAt DESC")
    fun getAllProgress(userId: Int): Flow<List<ReadingProgress>>
    
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertProgress(progress: ReadingProgress)
    
    @Update
    suspend fun updateProgress(progress: ReadingProgress)
    
    @Delete
    suspend fun deleteProgress(progress: ReadingProgress)
    
    @Query("DELETE FROM reading_progress WHERE userId = :userId AND kitabId = :kitabId")
    suspend fun deleteProgress(userId: Int, kitabId: Int)

    @Query("DELETE FROM reading_progress WHERE userId = :userId")
    suspend fun deleteAllForUser(userId: Int)
}
