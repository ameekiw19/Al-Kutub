package com.example.al_kutub.ui.viewmodel

import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.al_kutub.data.repository.KitabTranscriptRepository
import com.example.al_kutub.model.KitabTranscriptPayload
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class KitabTranscriptViewModel @Inject constructor(
    private val repository: KitabTranscriptRepository,
    savedStateHandle: SavedStateHandle
) : ViewModel() {

    private val kitabId: Int = savedStateHandle.get<Int>("kitabId") ?: 0

    private val _transcript = MutableStateFlow<KitabTranscriptPayload?>(null)
    val transcript: StateFlow<KitabTranscriptPayload?> = _transcript.asStateFlow()

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> = _isLoading.asStateFlow()

    init {
        if (kitabId > 0) {
            refresh()
        }
    }

    fun refresh(targetKitabId: Int = kitabId) {
        if (targetKitabId <= 0) return

        viewModelScope.launch {
            _isLoading.value = true
            repository.getTranscript(targetKitabId)
                .onSuccess { payload ->
                    _transcript.value = payload
                }
                .onFailure {
                    if (_transcript.value == null) {
                        _transcript.value = null
                    }
                }
            _isLoading.value = false
        }
    }
}
