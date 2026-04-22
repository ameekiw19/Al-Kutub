package com.example.al_kutub.data.repository

import com.example.al_kutub.api.ApiService
import com.example.al_kutub.model.*
import retrofit2.Response
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class ReadingNoteRepository @Inject constructor(
    private val apiService: ApiService
) {

    /**
     * Get user's reading notes
     */
    suspend fun getReadingNotes(
        authorization: String,
        kitabId: Int? = null
    ): Response<ReadingNotesResponse> {
        return apiService.getReadingNotes(
            kitabId = kitabId,
            authorization = authorization
        )
    }

    /**
     * Create a new reading note
     */
    suspend fun createReadingNote(
        authorization: String,
        request: CreateReadingNoteRequest
    ): Response<ReadingNoteBaseResponse> {
        return apiService.createReadingNote(
            request = request,
            authorization = authorization
        )
    }

    /**
     * Get a specific reading note
     */
    suspend fun getReadingNote(
        authorization: String,
        noteId: Int
    ): Response<ReadingNoteBaseResponse> {
        return apiService.getReadingNote(
            noteId = noteId,
            authorization = authorization
        )
    }

    /**
     * Update a reading note
     */
    suspend fun updateReadingNote(
        authorization: String,
        noteId: Int,
        request: UpdateReadingNoteRequest
    ): Response<ReadingNoteBaseResponse> {
        return apiService.updateReadingNote(
            noteId = noteId,
            request = request,
            authorization = authorization
        )
    }

    /**
     * Delete a reading note
     */
    suspend fun deleteReadingNote(
        authorization: String,
        noteId: Int
    ): Response<ReadingNoteBaseResponse> {
        return apiService.deleteReadingNote(
            noteId = noteId,
            authorization = authorization
        )
    }

    /**
     * Get reading notes statistics
     */
    suspend fun getReadingNotesStats(
        authorization: String
    ): Response<ReadingNotesStatsResponse> {
        return apiService.getReadingNotesStats(
            authorization = authorization
        )
    }
}
