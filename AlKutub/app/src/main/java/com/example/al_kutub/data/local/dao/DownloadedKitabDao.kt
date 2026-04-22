package com.example.al_kutub.data.local.dao

import androidx.room.Dao
import androidx.room.Delete
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.example.al_kutub.data.local.entity.DownloadedKitabEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface DownloadedKitabDao {
    @Query("SELECT * FROM downloaded_kitabs ORDER BY downloadedAt DESC")
    fun getAllDownloadedKitabs(): Flow<List<DownloadedKitabEntity>>

    @Query("SELECT * FROM downloaded_kitabs WHERE kitabId = :kitabId")
    suspend fun getDownloadedKitab(kitabId: Int): DownloadedKitabEntity?

    @Query("SELECT EXISTS(SELECT 1 FROM downloaded_kitabs WHERE kitabId = :kitabId)")
    fun isKitabDownloaded(kitabId: Int): Flow<Boolean>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertDownloadedKitab(kitab: DownloadedKitabEntity)

    @Delete
    suspend fun deleteDownloadedKitab(kitab: DownloadedKitabEntity)

    @Query("DELETE FROM downloaded_kitabs WHERE kitabId = :kitabId")
    suspend fun deleteDownloadedKitabById(kitabId: Int)

    @Query("DELETE FROM downloaded_kitabs")
    suspend fun clearAllDownloadedKitabs()
}
