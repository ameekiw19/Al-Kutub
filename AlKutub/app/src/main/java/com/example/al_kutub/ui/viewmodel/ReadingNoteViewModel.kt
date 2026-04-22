package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.OfflineSyncRepository
import com.example.al_kutub.data.repository.ReadingNoteRepository
import com.example.al_kutub.model.*
import com.example.al_kutub.utils.SessionManager
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.time.Instant
import java.util.UUID
import javax.inject.Inject

@HiltViewModel
class ReadingNoteViewModel @Inject constructor(
    private val readingNoteRepository: ReadingNoteRepository,
    private val sessionManager: SessionManager,
    private val offlineSyncRepository: OfflineSyncRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<ReadingNoteUiState>(ReadingNoteUiState.Idle)
    val uiState: StateFlow<ReadingNoteUiState> = _uiState.asStateFlow()

    private val _notes = MutableStateFlow<List<ReadingNoteData>>(emptyList())
    val notes: StateFlow<List<ReadingNoteData>> = _notes.asStateFlow()

    private val _stats = MutableStateFlow<ReadingNotesStatsData?>(null)
    val stats: StateFlow<ReadingNotesStatsData?> = _stats.asStateFlow()

    val syncSummary: StateFlow<SyncSummary> = offlineSyncRepository.syncSummary

    /**
     * Load reading notes
     */
    fun loadReadingNotes(kitabId: Int? = null) {
        viewModelScope.launch {
            _uiState.value = ReadingNoteUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val response = readingNoteRepository.getReadingNotes(
                    authorization = "Bearer $token",
                    kitabId = kitabId
                )
                
                if (response.isSuccessful && response.body() != null) {
                    val notesResponse = response.body()!!
                    if (notesResponse.success) {
                        _notes.value = notesResponse.data.notes
                        offlineSyncRepository.cacheReadingNotes(notesResponse.data.notes)
                        _uiState.value = ReadingNoteUiState.Success(notesResponse.message)
                    } else {
                        _uiState.value = ReadingNoteUiState.Error(notesResponse.message)
                    }
                } else {
                    val cached = offlineSyncRepository.getCachedReadingNotes()
                    if (cached.isNotEmpty()) {
                        _notes.value = cached
                        _uiState.value = ReadingNoteUiState.Success("Mode offline: menampilkan cache catatan")
                    } else {
                        _uiState.value = ReadingNoteUiState.Error("Failed to load reading notes")
                    }
                }
            } catch (e: Exception) {
                val cached = offlineSyncRepository.getCachedReadingNotes()
                if (cached.isNotEmpty()) {
                    _notes.value = cached
                    _uiState.value = ReadingNoteUiState.Success("Mode offline: menampilkan cache catatan")
                } else {
                    _uiState.value = ReadingNoteUiState.Error("Error: ${e.message}")
                }
            }
        }
    }

    /**
     * Create a new reading note
     */
    fun createReadingNote(request: CreateReadingNoteRequest) {
        viewModelScope.launch {
            _uiState.value = ReadingNoteUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val normalizedRequest = request.copy(
                    clientRequestId = request.clientRequestId
                        ?: UUID.randomUUID().toString().replace("-", "").take(16),
                    clientUpdatedAt = request.clientUpdatedAt ?: Instant.now().toString()
                )
                val response = readingNoteRepository.createReadingNote(
                    authorization = "Bearer $token",
                    request = normalizedRequest
                )
                
                if (response.isSuccessful && response.body() != null) {
                    val createResponse = response.body()!!
                    if (createResponse.success) {
                        _uiState.value = ReadingNoteUiState.Success(createResponse.message)
                        loadReadingNotes(normalizedRequest.kitabId) // Refresh notes
                    } else {
                        _uiState.value = ReadingNoteUiState.Error(createResponse.message)
                    }
                } else {
                    val clientRequestId = offlineSyncRepository.enqueueReadingNoteCreate(normalizedRequest)
                    offlineSyncRepository.cacheLocalPendingReadingNote(
                        kitabId = normalizedRequest.kitabId,
                        kitabTitle = null,
                        noteContent = normalizedRequest.noteContent,
                        pageNumber = normalizedRequest.pageNumber,
                        highlightedText = normalizedRequest.highlightedText,
                        noteColor = normalizedRequest.noteColor,
                        isPrivate = normalizedRequest.isPrivate,
                        clientRequestId = clientRequestId
                    )
                    _uiState.value = ReadingNoteUiState.Success("Catatan disimpan offline dan akan disinkronkan.")
                }
            } catch (e: Exception) {
                val normalizedRequest = request.copy(
                    clientRequestId = request.clientRequestId
                        ?: UUID.randomUUID().toString().replace("-", "").take(16),
                    clientUpdatedAt = request.clientUpdatedAt ?: Instant.now().toString()
                )
                val clientRequestId = offlineSyncRepository.enqueueReadingNoteCreate(normalizedRequest)
                offlineSyncRepository.cacheLocalPendingReadingNote(
                    kitabId = normalizedRequest.kitabId,
                    kitabTitle = null,
                    noteContent = normalizedRequest.noteContent,
                    pageNumber = normalizedRequest.pageNumber,
                    highlightedText = normalizedRequest.highlightedText,
                    noteColor = normalizedRequest.noteColor,
                    isPrivate = normalizedRequest.isPrivate,
                    clientRequestId = clientRequestId
                )
                _uiState.value = ReadingNoteUiState.Success("Catatan disimpan offline dan akan disinkronkan.")
            }
        }
    }

    /**
     * Update a reading note
     */
    fun updateReadingNote(noteId: Int, request: UpdateReadingNoteRequest) {
        viewModelScope.launch {
            _uiState.value = ReadingNoteUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val normalizedRequest = request.copy(
                    clientUpdatedAt = request.clientUpdatedAt ?: Instant.now().toString()
                )
                val response = readingNoteRepository.updateReadingNote(
                    authorization = "Bearer $token",
                    noteId = noteId,
                    request = normalizedRequest
                )
                
                if (response.isSuccessful && response.body() != null) {
                    val updateResponse = response.body()!!
                    if (updateResponse.success) {
                        _uiState.value = ReadingNoteUiState.Success(updateResponse.message)
                        loadReadingNotes() // Refresh notes
                    } else {
                        _uiState.value = ReadingNoteUiState.Error(updateResponse.message)
                    }
                } else {
                    offlineSyncRepository.enqueueReadingNoteUpdate(noteId, normalizedRequest)
                    _uiState.value = ReadingNoteUiState.Success("Perubahan catatan disimpan offline dan akan disinkronkan.")
                }
            } catch (e: Exception) {
                val normalizedRequest = request.copy(
                    clientUpdatedAt = request.clientUpdatedAt ?: Instant.now().toString()
                )
                offlineSyncRepository.enqueueReadingNoteUpdate(noteId, normalizedRequest)
                _uiState.value = ReadingNoteUiState.Success("Perubahan catatan disimpan offline dan akan disinkronkan.")
            }
        }
    }

    /**
     * Delete a reading note
     */
    fun deleteReadingNote(noteId: Int) {
        viewModelScope.launch {
            _uiState.value = ReadingNoteUiState.Loading
            
            try {
                val token = sessionManager.getToken() ?: return@launch
                val response = readingNoteRepository.deleteReadingNote(
                    authorization = "Bearer $token",
                    noteId = noteId
                )
                
                if (response.isSuccessful && response.body() != null) {
                    val deleteResponse = response.body()!!
                    if (deleteResponse.success) {
                        _uiState.value = ReadingNoteUiState.Success(deleteResponse.message)
                        loadReadingNotes() // Refresh notes
                    } else {
                        _uiState.value = ReadingNoteUiState.Error(deleteResponse.message)
                    }
                } else {
                    offlineSyncRepository.enqueueReadingNoteDelete(noteId)
                    _uiState.value = ReadingNoteUiState.Success("Hapus catatan disimpan offline dan akan disinkronkan.")
                }
            } catch (e: Exception) {
                offlineSyncRepository.enqueueReadingNoteDelete(noteId)
                _uiState.value = ReadingNoteUiState.Success("Hapus catatan disimpan offline dan akan disinkronkan.")
            }
        }
    }

    /**
     * Load reading notes statistics
     */
    fun loadReadingNotesStats() {
        viewModelScope.launch {
            try {
                val token = sessionManager.getToken() ?: return@launch
                val response = readingNoteRepository.getReadingNotesStats(
                    authorization = "Bearer $token"
                )
                
                if (response.isSuccessful && response.body() != null) {
                    val statsResponse = response.body()!!
                    if (statsResponse.success) {
                        _stats.value = statsResponse.data
                    }
                }
            } catch (e: Exception) {
                // Handle stats error silently or show error
            }
        }
    }

    /**
     * Reset UI state
     */
    fun resetState() {
        _uiState.value = ReadingNoteUiState.Idle
    }

    sealed class ReadingNoteUiState {
        object Idle : ReadingNoteUiState()
        object Loading : ReadingNoteUiState()
        data class Success(val message: String) : ReadingNoteUiState()
        data class Error(val message: String) : ReadingNoteUiState()
    }
}
