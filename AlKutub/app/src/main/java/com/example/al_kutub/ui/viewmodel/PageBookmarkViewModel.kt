package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.local.entity.PageBookmarkEntity
import com.example.al_kutub.data.repository.AddOrTouchBookmarkResult
import com.example.al_kutub.data.repository.PageBookmarkRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.collect
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

data class PageBookmarkUiModel(
    val id: Long,
    val pageNumber: Int,
    val label: String,
    val createdAt: Long,
    val updatedAt: Long
)

@HiltViewModel
class PageBookmarkViewModel @Inject constructor(
    private val repository: PageBookmarkRepository
) : ViewModel() {

    private val _bookmarks = MutableStateFlow<List<PageBookmarkUiModel>>(emptyList())
    val bookmarks: StateFlow<List<PageBookmarkUiModel>> = _bookmarks.asStateFlow()

    private val _message = MutableStateFlow<String?>(null)
    val message: StateFlow<String?> = _message.asStateFlow()

    private val _isLoggedIn = MutableStateFlow(repository.isLoggedIn())
    val isLoggedIn: StateFlow<Boolean> = _isLoggedIn.asStateFlow()

    private var observeJob: Job? = null
    private var currentKitabId: Int? = null

    fun bindKitab(kitabId: Int) {
        currentKitabId = kitabId
        _isLoggedIn.value = repository.isLoggedIn()

        observeJob?.cancel()
        observeJob = viewModelScope.launch {
            repository.observePageBookmarks(kitabId).collect { entities ->
                _bookmarks.value = entities
                    .sortedBy { it.pageNumber }
                    .map { it.toUiModel() }
            }
        }
    }

    fun addMarker(page: Int) {
        val kitabId = currentKitabId
        if (kitabId == null || kitabId <= 0) {
            pushMessage("Kitab tidak valid.")
            return
        }
        if (page <= 0) {
            pushMessage("Halaman tidak valid.")
            return
        }

        viewModelScope.launch {
            repository.addOrTouchPageBookmark(
                kitabId = kitabId,
                pageNumber = page,
                defaultLabel = "Halaman $page"
            ).fold(
                onSuccess = { result ->
                    when (result) {
                        is AddOrTouchBookmarkResult.Added -> pushMessage("Marker disimpan.")
                        AddOrTouchBookmarkResult.AlreadyExists -> pushMessage("Marker sudah ada di halaman ini.")
                    }
                },
                onFailure = { error ->
                    pushMessage(error.message ?: "Gagal menambah marker.")
                }
            )
        }
    }

    fun renameMarker(id: Long, label: String) {
        if (id <= 0L) {
            pushMessage("Marker tidak valid.")
            return
        }

        val fallback = _bookmarks.value.firstOrNull { it.id == id }?.let { "Halaman ${it.pageNumber}" }
        val normalized = label.trim().ifBlank { fallback ?: "Halaman" }

        viewModelScope.launch {
            repository.renamePageBookmark(id, normalized).fold(
                onSuccess = {
                    pushMessage("Label marker diperbarui.")
                },
                onFailure = { error ->
                    pushMessage(error.message ?: "Gagal memperbarui marker.")
                }
            )
        }
    }

    fun removeMarker(id: Long) {
        if (id <= 0L) {
            pushMessage("Marker tidak valid.")
            return
        }

        viewModelScope.launch {
            repository.deletePageBookmark(id).fold(
                onSuccess = {
                    pushMessage("Marker dihapus.")
                },
                onFailure = { error ->
                    pushMessage(error.message ?: "Gagal menghapus marker.")
                }
            )
        }
    }

    fun clearMessage() {
        _message.value = null
    }

    private fun pushMessage(text: String) {
        _message.update { text }
    }

    private fun PageBookmarkEntity.toUiModel(): PageBookmarkUiModel {
        return PageBookmarkUiModel(
            id = id,
            pageNumber = pageNumber,
            label = label,
            createdAt = createdAt,
            updatedAt = updatedAt
        )
    }
}
