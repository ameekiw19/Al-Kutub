package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

// Base Response for Reading Notes operations
data class ReadingNoteBaseResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String
)

// Reading Note Data
data class ReadingNoteData(
    @SerializedName("id")
    val id: Int,
    
    @SerializedName("user_id")
    val userId: Int,
    
    @SerializedName("kitab_id")
    val kitabId: Int,
    
    @SerializedName("note_content")
    val noteContent: String,
    
    @SerializedName("page_number")
    val pageNumber: Int?,
    
    @SerializedName("highlighted_text")
    val highlightedText: String?,
    
    @SerializedName("note_color")
    val noteColor: String,
    
    @SerializedName("is_private")
    val isPrivate: Boolean,
    
    @SerializedName("created_at")
    val createdAt: String,
    
    @SerializedName("updated_at")
    val updatedAt: String,

    @SerializedName("client_request_id")
    val clientRequestId: String? = null,
    
    // Relations
    @SerializedName("kitab")
    val kitab: KitabNoteData? = null,
    
    @SerializedName("user")
    val user: UserNoteData? = null
)

// Kitab data for notes (simplified)
data class KitabNoteData(
    @SerializedName("id")
    val id: Int,
    
    @SerializedName("judul")
    val judul: String
)

// User data for notes (simplified)
data class UserNoteData(
    @SerializedName("id")
    val id: Int,
    
    @SerializedName("username")
    val username: String
)

// Reading Notes List Response
data class ReadingNotesResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: ReadingNotesData
)

data class ReadingNotesData(
    @SerializedName("data")
    val notes: List<ReadingNoteData>,
    
    @SerializedName("current_page")
    val currentPage: Int,
    
    @SerializedName("last_page")
    val lastPage: Int,
    
    @SerializedName("per_page")
    val perPage: Int,
    
    @SerializedName("total")
    val total: Int
)

// Create Reading Note Request
data class CreateReadingNoteRequest(
    @SerializedName("kitab_id")
    val kitabId: Int,
    
    @SerializedName("note_content")
    val noteContent: String,
    
    @SerializedName("page_number")
    val pageNumber: Int? = null,
    
    @SerializedName("highlighted_text")
    val highlightedText: String? = null,
    
    @SerializedName("note_color")
    val noteColor: String = "#FFFF00",
    
    @SerializedName("is_private")
    val isPrivate: Boolean = true,

    @SerializedName("client_request_id")
    val clientRequestId: String? = null,

    @SerializedName("client_updated_at")
    val clientUpdatedAt: String? = null
)

// Update Reading Note Request
data class UpdateReadingNoteRequest(
    @SerializedName("note_content")
    val noteContent: String,
    
    @SerializedName("page_number")
    val pageNumber: Int? = null,
    
    @SerializedName("highlighted_text")
    val highlightedText: String? = null,
    
    @SerializedName("note_color")
    val noteColor: String = "#FFFF00",
    
    @SerializedName("is_private")
    val isPrivate: Boolean = true,

    @SerializedName("client_updated_at")
    val clientUpdatedAt: String? = null
)

// Reading Notes Statistics Response
data class ReadingNotesStatsResponse(
    @SerializedName("success")
    val success: Boolean,
    
    @SerializedName("message")
    val message: String,
    
    @SerializedName("data")
    val data: ReadingNotesStatsData
)

data class ReadingNotesStatsData(
    @SerializedName("total_notes")
    val totalNotes: Int,
    
    @SerializedName("public_notes")
    val publicNotes: Int,
    
    @SerializedName("private_notes")
    val privateNotes: Int,
    
    @SerializedName("notes_per_kitab")
    val notesPerKitab: List<NotesPerKitabData>
)

data class NotesPerKitabData(
    @SerializedName("judul")
    val judul: String,
    
    @SerializedName("note_count")
    val noteCount: Int
)
