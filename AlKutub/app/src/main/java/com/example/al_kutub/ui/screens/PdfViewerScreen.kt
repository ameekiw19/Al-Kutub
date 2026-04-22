package com.example.al_kutub.ui.screens

import android.graphics.Bitmap
import android.graphics.pdf.PdfRenderer
import android.os.ParcelFileDescriptor
import android.util.LruCache
import android.util.Log
import androidx.compose.foundation.Image
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.lazy.rememberLazyListState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.FormatListBulleted
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Brightness4
import androidx.compose.material.icons.filled.Brightness7
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExtendedFloatingActionButton
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.rememberUpdatedState
import androidx.compose.runtime.setValue
import androidx.compose.runtime.snapshotFlow
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import com.example.al_kutub.model.KitabTranscriptSegment
import com.example.al_kutub.ui.viewmodel.PageBookmarkUiModel
import com.example.al_kutub.ui.viewmodel.PageBookmarkViewModel
import com.example.al_kutub.ui.viewmodel.KitabTranscriptViewModel
import com.example.al_kutub.ui.viewmodel.ReadingProgressViewModel
import com.example.al_kutub.utils.arabicText
import com.example.al_kutub.utils.displayTitle
import com.example.al_kutub.utils.resolvePageSegments
import com.example.al_kutub.utils.translationText
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.CancellationException
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.distinctUntilChanged
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import kotlinx.coroutines.withContext
import kotlinx.coroutines.withTimeoutOrNull
import java.io.File
import kotlin.math.roundToInt

private sealed interface PdfOpenState {
    data object Preparing : PdfOpenState
    data object Ready : PdfOpenState
    data class Error(val message: String) : PdfOpenState
}

private class PdfDocumentRenderer(
    private val filePath: String
) {
    private var fileDescriptor: ParcelFileDescriptor? = null
    private var renderer: PdfRenderer? = null
    private val renderMutex = Mutex()
    private val bitmapCache = object : LruCache<Int, Bitmap>(24 * 1024) {
        override fun sizeOf(key: Int, value: Bitmap): Int = value.byteCount / 1024
    }

    suspend fun openDocument(): Result<Int> = withContext(Dispatchers.IO) {
        runCatching {
            val file = File(filePath)
            require(file.isFile) { "Lokasi PDF tidak valid." }
            require(file.exists()) { "File PDF tidak ditemukan." }
            require(file.canRead()) { "File PDF tidak dapat dibaca." }
            require(file.length() > 0) { "File PDF kosong atau rusak." }

            closeInternal()
            bitmapCache.evictAll()

            fileDescriptor = ParcelFileDescriptor.open(file, ParcelFileDescriptor.MODE_READ_ONLY)
            renderer = PdfRenderer(fileDescriptor!!)
            renderer?.pageCount ?: 0
        }
    }

    suspend fun renderPage(pageIndex: Int): Bitmap? = withContext(Dispatchers.IO) {
        bitmapCache.get(pageIndex)?.let { return@withContext it }

        renderMutex.withLock {
            bitmapCache.get(pageIndex)?.let { return@withLock it }

            val doc = renderer ?: return@withLock null
            if (pageIndex !in 0 until doc.pageCount) return@withLock null

            val page = doc.openPage(pageIndex)
            val scale = 1.4f
            val width = (page.width * scale).toInt().coerceAtLeast(1)
            val height = (page.height * scale).toInt().coerceAtLeast(1)
            val bitmap = Bitmap.createBitmap(width, height, Bitmap.Config.ARGB_8888)
            page.render(bitmap, null, null, PdfRenderer.Page.RENDER_MODE_FOR_DISPLAY)
            page.close()

            bitmapCache.put(pageIndex, bitmap)
            bitmap
        }
    }

    fun close() {
        closeInternal()
        bitmapCache.evictAll()
    }

    private fun closeInternal() {
        try {
            renderer?.close()
        } catch (_: Exception) {
        } finally {
            renderer = null
        }

        try {
            fileDescriptor?.close()
        } catch (_: Exception) {
        } finally {
            fileDescriptor = null
        }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun PdfViewerScreen(
    kitabId: Int,
    filePath: String,
    initialPage: Int = 1,
    onBack: () -> Unit,
    readingProgressViewModel: ReadingProgressViewModel = hiltViewModel(),
    pageBookmarkViewModel: PageBookmarkViewModel = hiltViewModel(),
    transcriptViewModel: KitabTranscriptViewModel = hiltViewModel()
) {
    var isNightMode by remember { mutableStateOf(false) }
    var pageCount by remember { mutableIntStateOf(0) }
    var currentPage by remember { mutableIntStateOf(1) }
    var showContinueDialog by remember { mutableStateOf(false) }
    var pdfOpenState by remember { mutableStateOf<PdfOpenState>(PdfOpenState.Preparing) }
    var resumeTargetPage by remember { mutableIntStateOf(1) }
    var resumeDecisionPending by remember { mutableStateOf(false) }
    var reloadKey by remember { mutableIntStateOf(0) }
    var showMarkerSheet by rememberSaveable { mutableStateOf(false) }
    var showTranscriptSheet by rememberSaveable { mutableStateOf(false) }
    var markerToEdit by remember { mutableStateOf<PageBookmarkUiModel?>(null) }
    var markerEditValue by rememberSaveable { mutableStateOf("") }
    var showJumpDialog by rememberSaveable { mutableStateOf(false) }
    var jumpPageInput by rememberSaveable { mutableStateOf("") }

    val context = LocalContext.current
    val listState = rememberLazyListState()
    val scope = rememberCoroutineScope()
    val snackbarHostState = remember { SnackbarHostState() }
    val latestPage by rememberUpdatedState(currentPage)
    val latestPageCount by rememberUpdatedState(pageCount)
    val latestResumeDecisionPending by rememberUpdatedState(resumeDecisionPending)
    val pageBookmarks by pageBookmarkViewModel.bookmarks.collectAsState()
    val markerMessage by pageBookmarkViewModel.message.collectAsState()
    val markerActionsEnabled by pageBookmarkViewModel.isLoggedIn.collectAsState()
    val transcript by transcriptViewModel.transcript.collectAsState()

    val documentRenderer = remember(filePath) { PdfDocumentRenderer(filePath) }
    val markerSheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)
    val transcriptSheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)
    val currentPageSegments = remember(transcript, currentPage) {
        transcript?.resolvePageSegments(currentPage).orEmpty()
    }
    val currentChapterInfo = remember(transcript, currentPage) {
        transcript?.chapterMap?.get(currentPage.toString())
    }

    LaunchedEffect(kitabId) {
        pageBookmarkViewModel.bindKitab(kitabId)
        transcriptViewModel.refresh(kitabId)
    }

    LaunchedEffect(markerMessage) {
        markerMessage?.let { message ->
            snackbarHostState.showSnackbar(message)
            pageBookmarkViewModel.clearMessage()
        }
    }

    val openJumpDialog: () -> Unit = {
        if (resumeDecisionPending) {
            scope.launch {
                snackbarHostState.showSnackbar("Tentukan pilihan lanjut baca terlebih dahulu.")
            }
        } else if (pdfOpenState is PdfOpenState.Ready && pageCount > 0) {
            jumpPageInput = currentPage.toString()
            showJumpDialog = true
        }
        Unit
    }

    DisposableEffect(documentRenderer) {
        onDispose {
            documentRenderer.close()
        }
    }

    LaunchedEffect(filePath, reloadKey) {
        pdfOpenState = PdfOpenState.Preparing
        pageCount = 0
        showContinueDialog = false
        resumeDecisionPending = false
        resumeTargetPage = 1

        try {
            val openStartMillis = System.currentTimeMillis()
            val openResult = withTimeoutOrNull(6_000) {
                documentRenderer.openDocument()
            }

            if (openResult == null) {
                Log.w("PdfViewerScreen", "Open PDF timeout > 6000ms for path=$filePath")
                pdfOpenState = PdfOpenState.Error("Membuka PDF terlalu lama. Coba lagi.")
                return@LaunchedEffect
            }

            openResult
                .onSuccess { total ->
                    val duration = System.currentTimeMillis() - openStartMillis
                    Log.d("PdfViewerScreen", "Open PDF success in ${duration}ms, totalPages=$total")
                    if (total <= 0) {
                        pdfOpenState = PdfOpenState.Error("PDF tidak memiliki halaman yang bisa dibaca.")
                        return@onSuccess
                    }

                    pageCount = total
                    val resumeState = PdfResumeCoordinator.prepare(
                        initialPage = initialPage,
                        pageCount = total
                    )
                    currentPage = resumeState.currentPage
                    resumeTargetPage = resumeState.resumeTargetPage
                    resumeDecisionPending = resumeState.resumeDecisionPending
                    showContinueDialog = resumeState.showContinueDialog
                    pdfOpenState = PdfOpenState.Ready

                    if (!resumeState.resumeDecisionPending) {
                        // Posisi default LazyColumn sudah di halaman pertama.
                        readingProgressViewModel.updateReadingProgress(kitabId, 1, total)
                    }
                }
                .onFailure { error ->
                    Log.e("PdfViewerScreen", "Open PDF failure for path=$filePath", error)
                    pdfOpenState = PdfOpenState.Error(error.message ?: "Gagal membuka PDF.")
                }
        } catch (e: Exception) {
            Log.e("PdfViewerScreen", "Unexpected open PDF failure for path=$filePath", e)
            pdfOpenState = PdfOpenState.Error(e.message ?: "Gagal membuka PDF.")
        }
    }

    LaunchedEffect(listState, pageCount) {
        if (pageCount <= 0) return@LaunchedEffect
        snapshotFlow { listState.firstVisibleItemIndex }
            .map { index -> (index + 1).coerceIn(1, pageCount) }
            .distinctUntilChanged()
            .collect { visiblePage ->
                if (!PdfResumeCoordinator.shouldTrackVisiblePage(resumeDecisionPending)) return@collect
                currentPage = visiblePage
                readingProgressViewModel.updateReadingProgress(
                    kitabId = kitabId,
                    currentPage = visiblePage,
                    totalPages = pageCount
                )
            }
    }

    LaunchedEffect(kitabId, pageCount) {
        if (pageCount <= 0) return@LaunchedEffect
        while (true) {
            delay(20_000)
            if (latestResumeDecisionPending) continue
            readingProgressViewModel.updateReadingProgress(
                kitabId = kitabId,
                currentPage = latestPage,
                totalPages = latestPageCount
            )
        }
    }

    DisposableEffect(kitabId) {
        onDispose {
            if (latestPageCount > 0 && !latestResumeDecisionPending) {
                readingProgressViewModel.flushProgress(
                    kitabId = kitabId,
                    currentPage = latestPage,
                    totalPages = latestPageCount
                )
            }
        }
    }

    if (showContinueDialog && pageCount > 0) {
        AlertDialog(
            onDismissRequest = {
                showContinueDialog = false
                resumeDecisionPending = false
            },
            title = { Text("Lanjutkan Membaca?") },
            text = {
                Text("Terakhir dibuka di halaman $resumeTargetPage dari $pageCount halaman.")
            },
            confirmButton = {
                TextButton(
                    onClick = {
                        showContinueDialog = false
                        resumeDecisionPending = false
                        currentPage = resumeTargetPage
                        scope.launch {
                            listState.scrollToItem((resumeTargetPage - 1).coerceAtLeast(0))
                        }
                        readingProgressViewModel.updateReadingProgress(
                            kitabId = kitabId,
                            currentPage = resumeTargetPage,
                            totalPages = pageCount
                        )
                    }
                ) { Text("Lanjutkan") }
            },
            dismissButton = {
                TextButton(
                    onClick = {
                        showContinueDialog = false
                        resumeDecisionPending = false
                        currentPage = 1
                        scope.launch {
                            listState.scrollToItem(0)
                        }
                        readingProgressViewModel.flushProgress(
                            kitabId = kitabId,
                            currentPage = 1,
                            totalPages = pageCount
                        )
                    }
                ) { Text("Mulai dari Awal") }
            }
        )
    }

    val progressPercent = if (pageCount > 0) {
        ((currentPage.toFloat() / pageCount.toFloat()) * 100f).roundToInt().coerceIn(0, 100)
    } else 0

    Scaffold(
        snackbarHost = { SnackbarHost(snackbarHostState) },
        topBar = {
            TopAppBar(
                title = {
                    Text(
                        text = "Membaca Kitab",
                        fontSize = 18.sp,
                        fontWeight = FontWeight.SemiBold
                    )
                },
                navigationIcon = {
                    IconButton(
                        onClick = {
                            if (pageCount > 0 && !resumeDecisionPending) {
                                readingProgressViewModel.flushProgress(
                                    kitabId = kitabId,
                                    currentPage = currentPage,
                                    totalPages = pageCount
                                )
                            }
                            onBack()
                        }
                    ) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Kembali")
                    }
                },
                actions = {
                    TextButton(
                        onClick = { showTranscriptSheet = true },
                        enabled = pdfOpenState is PdfOpenState.Ready && pageCount > 0
                    ) {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.MenuBook,
                            contentDescription = null
                        )
                        Spacer(modifier = Modifier.width(4.dp))
                        Text(
                            text = "Teks",
                            fontWeight = FontWeight.SemiBold
                        )
                    }
                    TextButton(
                        onClick = openJumpDialog,
                        enabled = pdfOpenState is PdfOpenState.Ready && pageCount > 0
                    ) {
                        Text(
                            text = "Hal.",
                            fontWeight = FontWeight.SemiBold
                        )
                    }
                    IconButton(onClick = { isNightMode = !isNightMode }) {
                        Icon(
                            if (isNightMode) Icons.Default.Brightness7 else Icons.Default.Brightness4,
                            contentDescription = "Mode baca"
                        )
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = if (isNightMode) Color(0xFF121212) else Color.White,
                    titleContentColor = if (isNightMode) Color.White else Color(0xFF1A2E1A),
                    navigationIconContentColor = if (isNightMode) Color.White else Color(0xFF1A2E1A),
                    actionIconContentColor = if (isNightMode) Color.White else Color(0xFF1A2E1A)
                )
            )
        }
    ) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .background(if (isNightMode) Color.Black else Color(0xFFF1F1F1))
        ) {
            when {
                pdfOpenState is PdfOpenState.Preparing -> {
                    Box(
                        modifier = Modifier.fillMaxSize(),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator()
                    }
                }

                pdfOpenState is PdfOpenState.Error -> {
                    val errorMessage = (pdfOpenState as PdfOpenState.Error).message
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(horizontal = 24.dp),
                        horizontalAlignment = Alignment.CenterHorizontally,
                        verticalArrangement = Arrangement.Center
                    ) {
                        Text(
                            text = errorMessage,
                            color = if (isNightMode) Color.White else Color(0xFF6B5E4E),
                            textAlign = TextAlign.Center
                        )
                        Row(
                            modifier = Modifier.padding(top = 12.dp),
                            horizontalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            Button(
                                onClick = { reloadKey += 1 },
                                shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp),
                                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF1B5E3B))
                            ) {
                                Text("Coba Lagi", color = Color.White)
                            }
                            Button(
                                onClick = onBack,
                                shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp),
                                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFF6B5E4E))
                            ) {
                                Text("Kembali", color = Color.White)
                            }
                        }
                    }
                }

                pdfOpenState is PdfOpenState.Ready && pageCount > 0 -> {
                    LazyColumn(
                        state = listState,
                        modifier = Modifier.fillMaxSize(),
                        contentPadding = PaddingValues(8.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        items(count = pageCount, key = { it }) { index ->
                            PdfPageItem(
                                documentRenderer = documentRenderer,
                                pageIndex = index,
                                isNightMode = isNightMode
                            )
                        }
                    }

                    Surface(
                        modifier = Modifier
                            .align(Alignment.BottomCenter)
                            .padding(bottom = 16.dp)
                            .clickable(onClick = openJumpDialog),
                        color = Color.Black.copy(alpha = 0.6f),
                        shape = androidx.compose.foundation.shape.RoundedCornerShape(16.dp)
                    ) {
                        Text(
                            text = "$currentPage / $pageCount  •  $progressPercent%",
                            color = Color.White,
                            fontSize = 12.sp,
                            modifier = Modifier.padding(horizontal = 12.dp, vertical = 6.dp)
                        )
                    }

                    Column(
                        modifier = Modifier
                            .align(Alignment.BottomEnd)
                            .navigationBarsPadding()
                            .padding(end = 16.dp, bottom = 64.dp),
                        verticalArrangement = Arrangement.spacedBy(10.dp),
                        horizontalAlignment = Alignment.End
                    ) {
                        FloatingActionButton(
                            onClick = { showMarkerSheet = true },
                            containerColor = Color(0xFF1A2E1A),
                            contentColor = Color.White
                        ) {
                            Row(
                                modifier = Modifier.padding(horizontal = 12.dp),
                                verticalAlignment = Alignment.CenterVertically,
                                horizontalArrangement = Arrangement.spacedBy(6.dp)
                            ) {
                                Icon(
                                    imageVector = Icons.AutoMirrored.Filled.FormatListBulleted,
                                    contentDescription = "Daftar Marker"
                                )
                                Text(
                                    text = "${pageBookmarks.size}",
                                    fontWeight = FontWeight.Bold
                                )
                            }
                        }

                        ExtendedFloatingActionButton(
                            onClick = {
                                if (!markerActionsEnabled) {
                                    scope.launch {
                                        snackbarHostState.showSnackbar(
                                            "Silakan login untuk menambah marker."
                                        )
                                    }
                                    return@ExtendedFloatingActionButton
                                }
                                if (currentPage !in 1..pageCount) {
                                    scope.launch {
                                        snackbarHostState.showSnackbar("Halaman marker tidak valid.")
                                    }
                                } else {
                                    pageBookmarkViewModel.addMarker(currentPage)
                                }
                            },
                            icon = {
                                Icon(
                                    imageVector = Icons.Default.Add,
                                    contentDescription = null
                                )
                            },
                            text = { Text("Tambah Marker") },
                            containerColor = if (markerActionsEnabled) Color(0xFF1B5E3B) else Color(0xFF9E9E9E),
                            contentColor = Color.White,
                            modifier = Modifier
                        )
                    }

                    Column(
                        modifier = Modifier
                            .align(Alignment.TopCenter)
                            .padding(top = 8.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        if (!markerActionsEnabled) {
                            Surface(
                                color = Color(0xFFFFF8E1),
                                shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp),
                                tonalElevation = 0.dp
                            ) {
                                Text(
                                    text = "Login untuk menambah, edit, dan hapus marker.",
                                    color = Color(0xFF6B5E4E),
                                    fontSize = 12.sp,
                                    modifier = Modifier.padding(horizontal = 12.dp, vertical = 8.dp)
                                )
                            }
                        }
                    }
                }
            }
        }
    }

    if (showMarkerSheet) {
        ModalBottomSheet(
            onDismissRequest = { showMarkerSheet = false },
            sheetState = markerSheetState
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .navigationBarsPadding()
                    .padding(horizontal = 16.dp, vertical = 12.dp)
            ) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = "Daftar Marker",
                        fontSize = 18.sp,
                        fontWeight = FontWeight.Bold
                    )
                    Surface(
                        shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp),
                        color = Color(0xFFF0F7F3)
                    ) {
                        Text(
                            text = "Marker: ${pageBookmarks.size}",
                            color = Color(0xFF1B5E3B),
                            fontSize = 12.sp,
                            fontWeight = FontWeight.SemiBold,
                            modifier = Modifier.padding(horizontal = 10.dp, vertical = 6.dp)
                        )
                    }
                }

                if (!markerActionsEnabled) {
                    Text(
                        text = "Mode tamu: login untuk mengelola marker.",
                        color = Color(0xFF8B8070),
                        fontSize = 12.sp,
                        modifier = Modifier.padding(top = 6.dp, bottom = 8.dp)
                    )
                } else {
                    Spacer(modifier = Modifier.height(8.dp))
                }

                if (pageBookmarks.isEmpty()) {
                    Text(
                        text = "Belum ada marker untuk kitab ini.",
                        color = Color(0xFF6B5E4E),
                        fontSize = 14.sp,
                        modifier = Modifier.padding(vertical = 12.dp)
                    )
                } else {
                    LazyColumn(
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(340.dp),
                        verticalArrangement = Arrangement.spacedBy(8.dp),
                        contentPadding = PaddingValues(bottom = 8.dp)
                    ) {
                        items(items = pageBookmarks, key = { it.id }) { marker ->
                            MarkerListItem(
                                marker = marker,
                                isNightMode = isNightMode,
                                canManage = markerActionsEnabled,
                                onJump = {
                                    if (marker.pageNumber in 1..pageCount) {
                                        scope.launch {
                                            listState.animateScrollToItem(marker.pageNumber - 1)
                                            showMarkerSheet = false
                                        }
                                    } else {
                                        scope.launch {
                                            snackbarHostState.showSnackbar(
                                                "Halaman ${marker.pageNumber} tidak tersedia."
                                            )
                                        }
                                    }
                                },
                                onEdit = {
                                    markerToEdit = marker
                                    markerEditValue = marker.label
                                },
                                onDelete = {
                                    pageBookmarkViewModel.removeMarker(marker.id)
                                }
                            )
                        }
                    }
                }
            }
        }
    }

    if (showTranscriptSheet) {
        ModalBottomSheet(
            onDismissRequest = { showTranscriptSheet = false },
            sheetState = transcriptSheetState
        ) {
            ReaderTranscriptSheet(
                currentPage = currentPage,
                chapterTitle = currentChapterInfo?.title,
                segments = currentPageSegments
            )
        }
    }

    if (showJumpDialog) {
        AlertDialog(
            onDismissRequest = { showJumpDialog = false },
            title = { Text("Lompat ke Halaman") },
            text = {
                Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    Text(
                        text = "Masukkan nomor halaman dari 1 sampai $pageCount.",
                        fontSize = 12.sp,
                        color = Color(0xFF8B8070)
                    )
                    OutlinedTextField(
                        value = jumpPageInput,
                        onValueChange = { input ->
                            jumpPageInput = input.filter { it.isDigit() }.take(6)
                        },
                        singleLine = true,
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Number),
                        placeholder = { Text("Contoh: 25") }
                    )
                }
            },
            confirmButton = {
                val isValidTarget = jumpPageInput.toIntOrNull()?.let { it in 1..pageCount } == true
                TextButton(
                    onClick = {
                        val jumpTarget = jumpPageInput.toIntOrNull()
                        if (jumpTarget == null || jumpTarget !in 1..pageCount) {
                            scope.launch {
                                snackbarHostState.showSnackbar("Nomor halaman tidak valid.")
                            }
                            return@TextButton
                        }
                        showJumpDialog = false
                        currentPage = jumpTarget
                        scope.launch {
                            listState.animateScrollToItem(jumpTarget - 1)
                        }
                        readingProgressViewModel.updateReadingProgress(
                            kitabId = kitabId,
                            currentPage = jumpTarget,
                            totalPages = pageCount
                        )
                    },
                    enabled = isValidTarget
                ) {
                    Text("Lompat")
                }
            },
            dismissButton = {
                TextButton(onClick = { showJumpDialog = false }) {
                    Text("Batal")
                }
            }
        )
    }

    if (markerToEdit != null) {
        AlertDialog(
            onDismissRequest = { markerToEdit = null },
            title = { Text("Ubah Label Marker") },
            text = {
                Column(verticalArrangement = Arrangement.spacedBy(8.dp)) {
                    Text(
                        text = "Label kosong akan dikembalikan ke default.",
                        fontSize = 12.sp,
                        color = Color(0xFF8B8070)
                    )
                    OutlinedTextField(
                        value = markerEditValue,
                        onValueChange = { markerEditValue = it },
                        singleLine = true,
                        placeholder = { Text("Masukkan label marker") }
                    )
                }
            },
            confirmButton = {
                TextButton(
                    onClick = {
                        markerToEdit?.let { marker ->
                            pageBookmarkViewModel.renameMarker(marker.id, markerEditValue)
                        }
                        markerToEdit = null
                    },
                    enabled = markerActionsEnabled
                ) {
                    Text("Simpan")
                }
            },
            dismissButton = {
                TextButton(onClick = { markerToEdit = null }) {
                    Text("Batal")
                }
            }
        )
    }
}

@Composable
private fun ReaderTranscriptSheet(
    currentPage: Int,
    chapterTitle: String?,
    segments: List<KitabTranscriptSegment>
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .navigationBarsPadding()
            .padding(horizontal = 16.dp, vertical = 12.dp)
    ) {
        Text(
            text = "Teks Halaman $currentPage",
            fontSize = 18.sp,
            fontWeight = FontWeight.Bold
        )
        Spacer(modifier = Modifier.height(6.dp))
        Text(
            text = chapterTitle?.takeIf { it.isNotBlank() }
                ?: "Teks utama memakai terjemahan Indonesia, dan teks Arab ditampilkan bila tersedia.",
            fontSize = 12.sp,
            color = Color(0xFF6B5E4E)
        )
        Spacer(modifier = Modifier.height(12.dp))

        if (segments.isEmpty()) {
            Surface(
                color = Color(0xFFF7F4EC),
                shape = androidx.compose.foundation.shape.RoundedCornerShape(14.dp)
            ) {
                Text(
                    text = "Belum ada transcript terstruktur untuk halaman ini. Generate transcript dari panel admin agar bagian per halaman tersusun otomatis.",
                    color = Color(0xFF6B5E4E),
                    fontSize = 13.sp,
                    modifier = Modifier.padding(14.dp)
                )
            }
            return
        }

        LazyColumn(
            modifier = Modifier
                .fillMaxWidth()
                .height(520.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp),
            contentPadding = PaddingValues(bottom = 8.dp)
        ) {
            items(items = segments, key = { segment -> segment.key ?: segment.id }) { segment ->
                ReaderTranscriptSegmentCard(
                    segment = segment
                )
            }
        }
    }
}

@Composable
private fun ReaderTranscriptSegmentCard(
    segment: KitabTranscriptSegment
) {
    val translationText = segment.translationText()
    val arabicText = segment.arabicText()
    val meta = buildList {
        if (segment.type.isNotBlank()) {
            add(segment.type.uppercase())
        }
        segment.pageStart?.let { start ->
            val end = segment.pageEnd
            add(if (end != null && end != start) "halaman $start-$end" else "halaman $start")
        }
    }.joinToString(" • ")

    Card(
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 1.dp),
        border = BorderStroke(1.dp, Color(0xFFE8E3D5))
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(14.dp),
            verticalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            Column(verticalArrangement = Arrangement.spacedBy(4.dp)) {
                Text(
                    text = segment.displayTitle(segment.pageStart),
                    fontWeight = FontWeight.Bold,
                    color = Color(0xFF1A2E1A),
                    fontSize = 15.sp
                )
                if (meta.isNotBlank()) {
                    Text(
                        text = meta,
                        color = Color(0xFF8B8070),
                        fontSize = 12.sp
                    )
                }
            }

            if (translationText.isNotBlank()) {
                Text(
                    text = translationText,
                    color = Color(0xFF1A2E1A),
                    fontSize = 14.sp,
                    lineHeight = 22.sp
                )
            } else {
                Text(
                    text = "Terjemahan belum tersedia untuk bagian ini.",
                    color = Color(0xFF8B8070),
                    fontSize = 13.sp
                )
            }

            if (arabicText.isNotBlank()) {
                Surface(
                    color = Color(0xFFF7F4EC),
                    shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp)
                ) {
                    Column(
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(12.dp),
                        verticalArrangement = Arrangement.spacedBy(6.dp)
                    ) {
                        Text(
                            text = "Teks Arab",
                            fontSize = 12.sp,
                            fontWeight = FontWeight.SemiBold,
                            color = Color(0xFF6B5E4E)
                        )
                        Text(
                            text = arabicText,
                            color = Color(0xFF1A2E1A),
                            textAlign = TextAlign.End,
                            fontSize = 18.sp,
                            lineHeight = 30.sp,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            }
        }
    }
}

@Composable
private fun MarkerListItem(
    marker: PageBookmarkUiModel,
    isNightMode: Boolean,
    canManage: Boolean,
    onJump: () -> Unit,
    onEdit: () -> Unit,
    onDelete: () -> Unit
) {
    val cardColor = if (isNightMode) Color(0xFF1C1C1C) else Color.White
    val borderColor = if (isNightMode) Color(0xFF2F2F2F) else Color(0xFFE8E3D5)
    val titleColor = if (isNightMode) Color(0xFFECECEC) else Color(0xFF1A2E1A)
    val subtitleColor = if (isNightMode) Color(0xFFB0B0B0) else Color(0xFF6B5E4E)

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .background(cardColor, shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp))
            .border(1.dp, borderColor, shape = androidx.compose.foundation.shape.RoundedCornerShape(12.dp))
            .clickable(onClick = onJump)
            .padding(horizontal = 12.dp, vertical = 10.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = "Halaman ${marker.pageNumber}",
                color = titleColor,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold
            )
            Spacer(modifier = Modifier.height(2.dp))
            Text(
                text = marker.label,
                color = subtitleColor,
                fontSize = 12.sp
            )
        }

        Row(verticalAlignment = Alignment.CenterVertically) {
            IconButton(
                onClick = onEdit,
                enabled = canManage
            ) {
                Icon(
                    imageVector = Icons.Default.Edit,
                    contentDescription = "Ubah marker",
                    tint = if (canManage) Color(0xFF1B5E3B) else Color(0xFFBDBDBD)
                )
            }
            Spacer(modifier = Modifier.width(2.dp))
            IconButton(
                onClick = onDelete,
                enabled = canManage
            ) {
                Icon(
                    imageVector = Icons.Default.Delete,
                    contentDescription = "Hapus marker",
                    tint = if (canManage) Color(0xFFE53935) else Color(0xFFBDBDBD)
                )
            }
        }
    }
}

@Composable
private fun PdfPageItem(
    documentRenderer: PdfDocumentRenderer,
    pageIndex: Int,
    isNightMode: Boolean
) {
    var bitmap by remember(documentRenderer, pageIndex) { mutableStateOf<Bitmap?>(null) }
    var hasError by remember(documentRenderer, pageIndex) { mutableStateOf(false) }

    LaunchedEffect(documentRenderer, pageIndex) {
        hasError = false
        try {
            val renderStartMillis = System.currentTimeMillis()
            val rendered = withTimeoutOrNull(4_000) {
                documentRenderer.renderPage(pageIndex)
            }
            val renderDuration = System.currentTimeMillis() - renderStartMillis
            if (rendered != null) {
                bitmap = rendered
                Log.d("PdfViewerScreen", "Rendered page=${pageIndex + 1} in ${renderDuration}ms")
            } else {
                bitmap = null
                hasError = true
                Log.w("PdfViewerScreen", "Render timeout page=${pageIndex + 1} (>4000ms)")
            }
        } catch (e: CancellationException) {
            // Expected when item leaves composition during fast scroll/navigation.
            bitmap = null
            hasError = false
            Log.d("PdfViewerScreen", "Render cancelled page=${pageIndex + 1}")
        } catch (e: Exception) {
            bitmap = null
            hasError = true
            Log.e("PdfViewerScreen", "Render failure page=${pageIndex + 1}", e)
        }
    }

    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        when {
            bitmap != null -> {
                Image(
                    bitmap = bitmap!!.asImageBitmap(),
                    contentDescription = "Halaman ${pageIndex + 1}",
                    modifier = Modifier.fillMaxWidth(),
                    contentScale = ContentScale.FillWidth,
                    colorFilter = if (isNightMode) {
                        androidx.compose.ui.graphics.ColorFilter.colorMatrix(
                            androidx.compose.ui.graphics.ColorMatrix(
                                floatArrayOf(
                                    -1f, 0f, 0f, 0f, 255f,
                                    0f, -1f, 0f, 0f, 255f,
                                    0f, 0f, -1f, 0f, 255f,
                                    0f, 0f, 0f, 1f, 0f
                                )
                            )
                        )
                    } else {
                        null
                    }
                )
            }

            hasError -> {
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(420.dp)
                        .background(Color.White),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = "Gagal memuat halaman ${pageIndex + 1}",
                        color = Color(0xFF6B5E4E)
                    )
                }
            }

            else -> {
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(420.dp)
                        .background(Color.White),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(modifier = Modifier.size(24.dp))
                }
            }
        }
    }
}
