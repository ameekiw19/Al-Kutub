package com.example.al_kutub.ui.screens

import android.content.ClipData
import android.content.ClipboardManager
import android.content.Context
import android.widget.Toast
import androidx.compose.animation.*
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.NoteAdd
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.lifecycle.compose.collectAsStateWithLifecycle
import com.example.al_kutub.model.ReadingNoteData
import com.example.al_kutub.model.CreateReadingNoteRequest
import com.example.al_kutub.model.UpdateReadingNoteRequest
import com.example.al_kutub.ui.components.SyncStatusChip
import com.example.al_kutub.ui.theme.SharedColors
import com.example.al_kutub.ui.viewmodel.ReadingNoteViewModel

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ReadingNotesScreen(
    kitabId: Int? = null,
    kitabTitle: String = "Catatan Baca",
    onBack: () -> Unit,
    viewModel: ReadingNoteViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsStateWithLifecycle()
    val notes by viewModel.notes.collectAsStateWithLifecycle()
    val syncSummary by viewModel.syncSummary.collectAsStateWithLifecycle()
    val context = LocalContext.current

    // Load notes when screen is shown
    LaunchedEffect(kitabId) {
        viewModel.loadReadingNotes(kitabId)
    }

    // Handle UI state
    LaunchedEffect(uiState) {
        when (val state = uiState) {
            is ReadingNoteViewModel.ReadingNoteUiState.Success -> {
                Toast.makeText(context, state.message, Toast.LENGTH_SHORT).show()
                viewModel.resetState()
            }
            is ReadingNoteViewModel.ReadingNoteUiState.Error -> {
                Toast.makeText(context, state.message, Toast.LENGTH_LONG).show()
                viewModel.resetState()
            }
            else -> {}
        }
    }

    var showAddNoteDialog by remember { mutableStateOf(false) }
    var noteToEdit by remember { mutableStateOf<ReadingNoteData?>(null) }
    val canCreateNote = kitabId != null

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                Brush.verticalGradient(
                    colors = listOf(
                        Color(0xFF1B5E20),
                        SharedColors.TealMain
                    )
                )
            )
    ) {
        // Header
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            IconButton(
                onClick = onBack,
                modifier = Modifier.background(
                    Color.White.copy(alpha = 0.2f),
                    RoundedCornerShape(12.dp)
                )
            ) {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = "Kembali",
                    tint = Color.White
                )
            }

            Spacer(modifier = Modifier.width(16.dp))

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = "Catatan Baca",
                    color = Color.White,
                    fontSize = 24.sp,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = kitabTitle,
                    color = Color.White.copy(alpha = 0.8f),
                    fontSize = 14.sp
                )
            }

            IconButton(
                onClick = { if (canCreateNote) showAddNoteDialog = true },
                enabled = canCreateNote,
                modifier = Modifier.background(
                    Color.White.copy(alpha = 0.2f),
                    RoundedCornerShape(12.dp)
                )
            ) {
                Icon(
                    Icons.Default.Add,
                    contentDescription = "Tambah Catatan",
                    tint = if (canCreateNote) Color.White else Color.White.copy(alpha = 0.55f)
                )
            }
        }

        if (!canCreateNote) {
            Text(
                text = "Buka dari detail kitab atau reader untuk menambah catatan baru.",
                color = Color.White.copy(alpha = 0.8f),
                fontSize = 12.sp,
                modifier = Modifier
                    .padding(horizontal = 16.dp)
                    .padding(top = 64.dp)
            )
        }

        SyncStatusChip(
            state = syncSummary.notes,
            modifier = Modifier
                .padding(horizontal = 16.dp)
                .padding(top = 72.dp)
        )

        // Notes List
        if (uiState is ReadingNoteViewModel.ReadingNoteUiState.Loading && notes.isEmpty()) {
            Box(
                modifier = Modifier.fillMaxSize(),
                contentAlignment = Alignment.Center
            ) {
                CircularProgressIndicator(
                    color = Color.White,
                    strokeWidth = 3.dp
                )
            }
        } else if (notes.isEmpty()) {
            Box(
                modifier = Modifier.fillMaxSize(),
                contentAlignment = Alignment.Center
            ) {
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Icon(
                        Icons.AutoMirrored.Filled.NoteAdd,
                        contentDescription = null,
                        tint = Color.White.copy(alpha = 0.6f),
                        modifier = Modifier.size(64.dp)
                    )
                    Spacer(modifier = Modifier.height(16.dp))
                    Text(
                        text = "Belum ada catatan",
                        color = Color.White.copy(alpha = 0.8f),
                        fontSize = 18.sp,
                        fontWeight = FontWeight.Medium
                    )
                    Text(
                        text = "Tambahkan catatan pertamamu dari halaman kitab",
                        color = Color.White.copy(alpha = 0.6f),
                        fontSize = 14.sp
                    )
                }
            }
        } else {
            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(horizontal = 16.dp, vertical = 8.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp)
            ) {
                items(notes) { note ->
                    NoteCard(
                        note = note,
                        onEdit = { noteToEdit = note },
                        onDelete = { viewModel.deleteReadingNote(note.id) },
                        onCopy = { copyToClipboard(context, note.noteContent) }
                    )
                }
            }
        }
    }

    // Add Note Dialog
    if (showAddNoteDialog) {
        AddEditNoteDialog(
            kitabId = kitabId,
            onDismiss = { showAddNoteDialog = false },
            onSave = { request ->
                if (noteToEdit == null) {
                    viewModel.createReadingNote(request)
                } else {
                    // For editing, convert to UpdateRequest
                    val updateRequest = UpdateReadingNoteRequest(
                        noteContent = request.noteContent,
                        pageNumber = request.pageNumber,
                        highlightedText = request.highlightedText,
                        noteColor = request.noteColor,
                        isPrivate = request.isPrivate
                    )
                    viewModel.updateReadingNote(noteToEdit!!.id, updateRequest)
                }
                showAddNoteDialog = false
            }
        )
    }

    // Edit Note Dialog
    noteToEdit?.let { note ->
        AddEditNoteDialog(
            kitabId = kitabId,
            note = note,
            onDismiss = { noteToEdit = null },
            onSave = { request ->
                val updateRequest = UpdateReadingNoteRequest(
                    noteContent = request.noteContent,
                    pageNumber = request.pageNumber,
                    highlightedText = request.highlightedText,
                    noteColor = request.noteColor,
                    isPrivate = request.isPrivate
                )
                viewModel.updateReadingNote(note.id, updateRequest)
                noteToEdit = null
            }
        )
    }
}

@Composable
private fun NoteCard(
    note: ReadingNoteData,
    onEdit: () -> Unit,
    onDelete: () -> Unit,
    onCopy: () -> Unit
) {
    var expanded by remember { mutableStateOf(false) }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .clickable { expanded = !expanded },
        colors = CardDefaults.cardColors(
            containerColor = Color.White
        ),
        shape = RoundedCornerShape(16.dp),
        elevation = CardDefaults.cardElevation(4.dp)
    ) {
        Column(
            modifier = Modifier.padding(16.dp)
        ) {
            // Header
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically
            ) {
                // Color indicator
                Box(
                    modifier = Modifier
                        .size(12.dp)
                        .background(
                            Color(android.graphics.Color.parseColor(note.noteColor)),
                            RoundedCornerShape(6.dp)
                        )
                )

                Spacer(modifier = Modifier.width(12.dp))

                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = note.kitab?.judul ?: "Kitab tidak diketahui",
                        color = Color.Black,
                        fontSize = 16.sp,
                        fontWeight = FontWeight.Bold
                    )
                    
                    if (note.pageNumber != null) {
                        Text(
                            text = "Halaman ${note.pageNumber}",
                            color = Color.Gray,
                            fontSize = 12.sp
                        )
                    }
                }

                // Actions
                Row {
                    IconButton(onClick = onCopy) {
                        Icon(
                            Icons.Default.ContentCopy,
                            contentDescription = "Salin",
                            tint = Color.Gray,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                    IconButton(onClick = onEdit) {
                        Icon(
                            Icons.Default.Edit,
                            contentDescription = "Ubah",
                            tint = Color.Gray,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                    IconButton(onClick = onDelete) {
                        Icon(
                            Icons.Default.Delete,
                            contentDescription = "Hapus",
                            tint = Color.Red,
                            modifier = Modifier.size(20.dp)
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(12.dp))

            // Note content
            Text(
                text = note.noteContent,
                color = Color.Black,
                fontSize = 14.sp,
                lineHeight = 20.sp,
                maxLines = if (expanded) Int.MAX_VALUE else 3
            )

            // Highlighted text
            note.highlightedText?.let { highlighted ->
                Spacer(modifier = Modifier.height(8.dp))
                Text(
                    text = "\"$highlighted\"",
                    color = Color(0xFFFF9800),
                    fontSize = 13.sp,
                    fontStyle = androidx.compose.ui.text.font.FontStyle.Italic,
                    modifier = Modifier
                        .fillMaxWidth()
                        .background(
                            Color(0xFFFF9800).copy(alpha = 0.1f),
                            RoundedCornerShape(8.dp)
                        )
                        .padding(8.dp)
                )
            }

            Spacer(modifier = Modifier.height(8.dp))

            // Footer
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = formatDateTime(note.createdAt),
                    color = Color.Gray,
                    fontSize = 11.sp
                )
                
                if (note.isPrivate) {
                    Row(
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            Icons.Default.Lock,
                            contentDescription = "Privat",
                            tint = Color.Gray,
                            modifier = Modifier.size(12.dp)
                        )
                        Spacer(modifier = Modifier.width(4.dp))
                        Text(
                            text = "Privat",
                            color = Color.Gray,
                            fontSize = 11.sp
                        )
                    }
                }
            }
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
private fun AddEditNoteDialog(
    kitabId: Int?,
    note: ReadingNoteData? = null,
    onDismiss: () -> Unit,
    onSave: (CreateReadingNoteRequest) -> Unit
) {
    var noteContent by remember { mutableStateOf(note?.noteContent ?: "") }
    var pageNumber by remember { mutableStateOf(note?.pageNumber?.toString() ?: "") }
    var highlightedText by remember { mutableStateOf(note?.highlightedText ?: "") }
    var noteColor by remember { mutableStateOf(note?.noteColor ?: "#FFFF00") }
    var isPrivate by remember { mutableStateOf(note?.isPrivate ?: true) }

    val colors = listOf("#FFFF00", "#FF9800", "#4CAF50", "#2196F3", "#9C27B0", "#F44336")
    val targetKitabId = kitabId ?: note?.kitabId

    AlertDialog(
        onDismissRequest = onDismiss,
        title = {
            Text(
                text = if (note == null) "Tambah Catatan Baca" else "Ubah Catatan Baca",
                fontWeight = FontWeight.Bold
            )
        },
        text = {
            Column(
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                // Note content
                OutlinedTextField(
                    value = noteContent,
                    onValueChange = { noteContent = it },
                    label = { Text("Isi Catatan*") },
                    modifier = Modifier.fillMaxWidth(),
                    maxLines = 5,
                    singleLine = false
                )

                // Page number
                OutlinedTextField(
                    value = pageNumber,
                    onValueChange = { pageNumber = it.filter { it.isDigit() } },
                    label = { Text("Nomor Halaman") },
                    modifier = Modifier.fillMaxWidth(),
                    keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                    singleLine = true
                )

                // Highlighted text
                OutlinedTextField(
                    value = highlightedText,
                    onValueChange = { highlightedText = it },
                    label = { Text("Teks Sorotan") },
                    modifier = Modifier.fillMaxWidth(),
                    maxLines = 2,
                    singleLine = false
                )

                // Color selection
                Text(
                    text = "Warna Catatan",
                    fontWeight = FontWeight.Medium
                )
                Row(
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
                ) {
                    colors.forEach { color ->
                        Box(
                            modifier = Modifier
                                .size(32.dp)
                                .background(
                                    Color(android.graphics.Color.parseColor(color)),
                                    RoundedCornerShape(16.dp)
                                )
                                .clickable { noteColor = color }
                                .border(
                                    width = if (noteColor == color) 3.dp else 1.dp,
                                    color = if (noteColor == color) Color.Black else Color.Gray,
                                    shape = RoundedCornerShape(16.dp)
                                )
                        )
                    }
                }

                // Private checkbox
                Row(
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Checkbox(
                        checked = isPrivate,
                        onCheckedChange = { isPrivate = it }
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("Catatan privat")
                }
            }
        },
        confirmButton = {
            Button(
                onClick = {
                    if (noteContent.isNotEmpty() && targetKitabId != null) {
                        val request = if (note == null) {
                            CreateReadingNoteRequest(
                                kitabId = targetKitabId,
                                noteContent = noteContent,
                                pageNumber = pageNumber.toIntOrNull(),
                                highlightedText = highlightedText.ifEmpty { null },
                                noteColor = noteColor,
                                isPrivate = isPrivate
                            )
                        } else {
                            // For editing, we need to convert to UpdateRequest
                            CreateReadingNoteRequest(
                                kitabId = targetKitabId,
                                noteContent = noteContent,
                                pageNumber = pageNumber.toIntOrNull(),
                                highlightedText = highlightedText.ifEmpty { null },
                                noteColor = noteColor,
                                isPrivate = isPrivate
                            )
                        }
                        onSave(request)
                    }
                },
                enabled = noteContent.isNotEmpty() && targetKitabId != null
            ) {
                Text(if (note == null) "Tambah" else "Perbarui")
            }
        },
        dismissButton = {
            TextButton(onClick = onDismiss) {
                Text("Batal")
            }
        }
    )
}

private fun copyToClipboard(context: Context, text: String) {
    val clipboard = context.getSystemService(Context.CLIPBOARD_SERVICE) as ClipboardManager
    val clip = ClipData.newPlainText("Catatan", text)
    clipboard.setPrimaryClip(clip)
    Toast.makeText(context, "Catatan disalin ke papan klip", Toast.LENGTH_SHORT).show()
}

private fun formatDateTime(dateString: String): String {
    return try {
        val formatter = java.time.format.DateTimeFormatter.ofPattern("yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'")
        val parsed = java.time.LocalDateTime.parse(dateString, formatter)
        parsed.format(
            java.time.format.DateTimeFormatter.ofPattern(
                "dd MMM yyyy, HH:mm",
                java.util.Locale("id", "ID")
            )
        )
    } catch (e: Exception) {
        dateString
    }
}
