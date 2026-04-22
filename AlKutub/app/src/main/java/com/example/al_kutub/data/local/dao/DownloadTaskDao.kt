package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.example.al_kutub.data.local.entity.DownloadTaskEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface DownloadTaskDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun upsert(task: DownloadTaskEntity): Long

    @Update
    suspend fun update(task: DownloadTaskEntity)

    @Query("SELECT * FROM download_tasks WHERE userId = :userId ORDER BY updatedAt DESC")
    fun observeForUser(userId: Int): Flow<List<DownloadTaskEntity>>

    @Query("SELECT * FROM download_tasks WHERE taskId = :taskId LIMIT 1")
    suspend fun getById(taskId: Long): DownloadTaskEntity?

    @Query("SELECT * FROM download_tasks WHERE userId = :userId AND kitabId = :kitabId LIMIT 1")
    suspend fun getByUserAndKitab(userId: Int, kitabId: Int): DownloadTaskEntity?

    @Query("SELECT * FROM download_tasks WHERE userId = :userId AND status IN ('queued','paused','failed') ORDER BY updatedAt ASC")
    suspend fun getPendingTasks(userId: Int): List<DownloadTaskEntity>

    @Query("SELECT * FROM download_tasks WHERE userId = :userId")
    suspend fun getAllForUser(userId: Int): List<DownloadTaskEntity>

    @Query("DELETE FROM download_tasks WHERE taskId = :taskId")
    suspend fun deleteById(taskId: Long)

    @Query("DELETE FROM download_tasks WHERE userId = :userId")
    suspend fun clearForUser(userId: Int)

    @Query("DELETE FROM download_tasks")
    suspend fun clearAll()
}
