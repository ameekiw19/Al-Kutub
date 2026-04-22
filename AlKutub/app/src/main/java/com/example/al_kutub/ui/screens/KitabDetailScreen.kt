package com.example.al_kutub.ui.screens

import android.content.Intent
import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.automirrored.filled.Send
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.BookmarkBorder
import androidx.compose.material.icons.filled.CalendarToday
import androidx.compose.material.icons.filled.CheckCircle
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material.icons.filled.Download
import androidx.compose.material.icons.filled.FileDownload
import androidx.compose.material.icons.filled.Info
import androidx.compose.material.icons.filled.Language
import androidx.compose.material.icons.filled.Share
import androidx.compose.material.icons.filled.Star
import androidx.compose.material.icons.filled.StarBorder
import androidx.compose.material.icons.filled.Tag
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarDuration
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.DisposableEffect
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.saveable.rememberSaveable
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.Dp
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.model.Comment
import com.example.al_kutub.model.Kitab
import com.example.al_kutub.ui.viewmodel.KitabDetailUiState
import com.example.al_kutub.ui.viewmodel.KitabDetailViewModel
import java.util.Locale

private val DetailBackground = Color(0xFFFAFAF5)
private val DetailPrimary = Color(0xFF1A2E1A)
private val DetailSecondary = Color(0xFF4A3D30)
private val DetailMuted = Color(0xFF8B8070)
private val DetailBorder = Color(0xFFF0EBE0)
private val DetailGreen = Color(0xFF1B5E3B)
private val DetailGreenAlt = Color(0xFF2D7A52)
private val DetailGold = Color(0xFFC8A951)

@Composable
fun KitabDetailScreen(
    kitabId: Int,
    onBack: () -> Unit,
    onNavigateToPdf: (Int, String) -> Unit,
    onNavigateToKitabDetail: (Int) -> Unit = {},
    viewModel: KitabDetailViewModel = hiltViewModel()
) {
    val uiState by viewModel.uiState.collectAsState()
    val isBookmarked by viewModel.isBookmarked.collectAsState()
    val isDownloaded by viewModel.isDownloaded.collectAsState()
    val toastMessage by viewModel.toastMessage.collectAsState()
    val comments by viewModel.comments.collectAsState()
    val navigateToPdf by viewModel.navigateToPdf.collectAsState()
    val myRating by viewModel.myRating.collectAsState()
    val averageRating by viewModel.averageRating.collectAsState()
    val ratingsCount by viewModel.ratingsCount.collectAsState()
    val relatedKitabs by viewModel.relatedKitabs.collectAsState()
    val isSubmittingComment by viewModel.isSubmittingComment.collectAsState()
    var newComment by remember { mutableStateOf("") }
    val snackbarHostState = remember { SnackbarHostState() }
    val context = LocalContext.current

    LaunchedEffect(navigateToPdf) {
        navigateToPdf?.let { data ->
            onNavigateToPdf(kitabId, data)
            viewModel.clearPdfNavigation()
        }
    }

    LaunchedEffect(kitabId) {
        viewModel.loadKitabDetail(kitabId)
    }

    LaunchedEffect(toastMessage) {
        toastMessage?.let { message ->
            snackbarHostState.showSnackbar(message = message, duration = SnackbarDuration.Short)
            viewModel.clearToastMessage()
        }
    }

    Scaffold(
        containerColor = DetailBackground,
        snackbarHost = { SnackbarHost(snackbarHostState) }
    ) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            when (val state = uiState) {
                is KitabDetailUiState.Loading -> {
                    Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(color = DetailGreen)
                    }
                }

                is KitabDetailUiState.Success -> {
                    KitabDetailContent(
                        kitab = state.kitab,
                        isBookmarked = isBookmarked,
                        isDownloaded = isDownloaded,
                        comments = comments,
                        newComment = newComment,
                        myRating = myRating,
                        averageRating = averageRating,
                        ratingsCount = ratingsCount,
                        relatedKitabs = relatedKitabs,
                        isSubmittingComment = isSubmittingComment,
                        currentUserId = viewModel.currentUserId,
                        onNewCommentChange = { newComment = it },
                        onBack = onBack,
                        onBookmarkToggle = { viewModel.toggleBookmark(kitabId) },
                        onOpenPdf = { viewModel.downloadAndOpenPdf(state.kitab.idKitab, state.kitab.judul) },
                        onRate = viewModel::rateKitab,
                        onSubmitComment = {
                            val content = newComment.trim()
                            if (content.isNotEmpty()) {
                                viewModel.submitComment(state.kitab.idKitab, content) {
                                    newComment = ""
                                }
                            }
                        },
                        onDeleteComment = { commentId ->
                            viewModel.deleteComment(commentId, state.kitab.idKitab)
                        },
                        onShare = {
                            val shareUrl = ApiConfig.getKitabShareUrl(state.kitab.idKitab)
                            val sendIntent = Intent().apply {
                                action = Intent.ACTION_SEND
                                putExtra(Intent.EXTRA_TEXT, "$shareUrl\n\n${state.kitab.judul} - ${state.kitab.penulis}")
                                putExtra(Intent.EXTRA_TITLE, state.kitab.judul)
                                type = "text/plain"
                            }
                            context.startActivity(Intent.createChooser(sendIntent, "Bagikan Kitab via"))
                        },
                        onRelatedKitabClick = onNavigateToKitabDetail
                    )
                }

                is KitabDetailUiState.Error -> {
                    ErrorContent(message = state.message, onBack = onBack)
                }
            }
        }
    }
}

@Composable
private fun KitabDetailContent(
    kitab: Kitab,
    isBookmarked: Boolean,
    isDownloaded: Boolean,
    comments: List<Comment>,
    newComment: String,
    myRating: Int,
    averageRating: Double,
    ratingsCount: Int,
    relatedKitabs: List<Kitab>,
    isSubmittingComment: Boolean,
    currentUserId: Int,
    onNewCommentChange: (String) -> Unit,
    onBack: () -> Unit,
    onBookmarkToggle: () -> Unit,
    onOpenPdf: () -> Unit,
    onRate: (Int) -> Unit,
    onSubmitComment: () -> Unit,
    onDeleteComment: (Int) -> Unit,
    onShare: () -> Unit,
    onRelatedKitabClick: (Int) -> Unit
) {
    var descriptionExpanded by rememberSaveable { mutableStateOf(false) }
    val detailRows = remember(kitab, isDownloaded) {
        buildList {
            add(DetailRowData(Icons.Default.Tag, "Kategori", kitab.kategori, Color(0xFFF0F7F3), DetailGreen))
            add(DetailRowData(Icons.Default.Language, "Bahasa", kitab.bahasa, Color(0xFFE3F2FD), Color(0xFF1565C0)))
            add(DetailRowData(Icons.Default.Visibility, "Dilihat", "${formatViews(kitab.views)} kali", Color(0xFFFFF8E1), DetailGold))
            add(DetailRowData(Icons.Default.Download, "Diunduh", "${formatViews(kitab.downloads)} kali", Color(0xFFF3E5F5), Color(0xFF7B1FA2)))
            add(
                DetailRowData(
                    if (isDownloaded) Icons.Default.CheckCircle else Icons.Default.Info,
                    "Status",
                    if (isDownloaded) "Tersedia Offline" else "Perlu download untuk akses offline",
                    Color(0xFFE8F5E9),
                    Color(0xFF2E7D32)
                )
            )
        }
    }
    val tagItems = remember(kitab) {
        listOf(
            "#${kitab.kategori.lowercase(Locale.getDefault())}",
            "#${kitab.bahasa.lowercase(Locale.getDefault())}",
            "#alkutub",
            "#kitab"
        ).distinct()
    }

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(DetailBackground),
        contentPadding = PaddingValues(bottom = 88.dp)
    ) {
        item {
            DetailHeroHeader(
                kitab = kitab,
                onBack = onBack,
                onShare = onShare
            )
        }

        item {
            StatsOverviewCard(
                averageRating = averageRating,
                views = kitab.views,
                downloads = kitab.downloads,
                commentsCount = comments.size
            )
        }

        item {
            ActionButtonsRow(
                isBookmarked = isBookmarked,
                isDownloaded = isDownloaded,
                onRead = onOpenPdf,
                onBookmarkToggle = onBookmarkToggle
            )
        }

        item {
            SectionCard(
                title = "Tentang Kitab",
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)
            ) {
                Text(
                    text = kitab.deskripsi,
                    fontSize = 13.sp,
                    lineHeight = 20.sp,
                    color = DetailSecondary,
                    maxLines = if (descriptionExpanded) Int.MAX_VALUE else 4,
                    overflow = TextOverflow.Ellipsis
                )
                Text(
                    text = if (descriptionExpanded) "Tampilkan lebih sedikit" else "Selengkapnya",
                    color = DetailGreen,
                    fontSize = 13.sp,
                    fontWeight = FontWeight.SemiBold,
                    modifier = Modifier
                        .padding(top = 10.dp)
                        .clickable { descriptionExpanded = !descriptionExpanded }
                )
            }
        }

        item {
            SectionCard(
                title = "Detail Kitab",
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)
            ) {
                detailRows.forEachIndexed { index, row ->
                    DetailInfoRow(row = row)
                    if (index < detailRows.lastIndex) {
                        Spacer(modifier = Modifier.height(10.dp))
                    }
                }
            }
        }

        item {
            SectionCard(
                title = "Tag",
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)
            ) {
                LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                    items(tagItems) { tag ->
                        Box(
                            modifier = Modifier
                                .clip(RoundedCornerShape(12.dp))
                                .background(Color(0xFFF0F7F3))
                                .padding(horizontal = 12.dp, vertical = 8.dp)
                        ) {
                            Text(
                                text = tag,
                                color = DetailGreen,
                                fontSize = 12.sp,
                                fontWeight = FontWeight.SemiBold
                            )
                        }
                    }
                }
            }
        }

        item {
            RatingReviewSection(
                averageRating = averageRating,
                ratingsCount = ratingsCount,
                myRating = myRating,
                newComment = newComment,
                comments = comments,
                isSubmittingComment = isSubmittingComment,
                currentUserId = currentUserId,
                onRate = onRate,
                onNewCommentChange = onNewCommentChange,
                onSubmitComment = onSubmitComment,
                onDeleteComment = onDeleteComment
            )
        }

        if (relatedKitabs.isNotEmpty()) {
            item {
                SectionCard(
                    title = "Kitab Terkait",
                    modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)
                ) {
                    LazyRow(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                        items(relatedKitabs) { related ->
                            RelatedBookCard(
                                kitab = related,
                                onClick = { onRelatedKitabClick(related.idKitab) }
                            )
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun DetailHeroHeader(
    kitab: Kitab,
    onBack: () -> Unit,
    onShare: () -> Unit
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(350.dp)
    ) {
        AsyncImage(
            model = ApiConfig.getCoverUrl(kitab.cover),
            contentDescription = kitab.judul,
            contentScale = ContentScale.Crop,
            modifier = Modifier.fillMaxSize()
        )
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.verticalGradient(
                        colors = listOf(
                            Color.Black.copy(alpha = 0.28f),
                            Color.Black.copy(alpha = 0.36f),
                            Color.Black.copy(alpha = 0.72f)
                        )
                    )
                )
        )

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .statusBarsPadding()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            HeaderIconButton(icon = Icons.AutoMirrored.Filled.ArrowBack, onClick = onBack)
            HeaderIconButton(icon = Icons.Default.Share, onClick = onShare)
        }

        Row(
            modifier = Modifier
                .align(Alignment.BottomStart)
                .fillMaxWidth()
                .padding(horizontal = 20.dp, vertical = 18.dp),
            horizontalArrangement = Arrangement.spacedBy(16.dp),
            verticalAlignment = Alignment.Bottom
        ) {
            HeroCoverCard(
                kitab = kitab,
                modifier = Modifier.size(width = 112.dp, height = 162.dp)
            )
            Column(
                modifier = Modifier
                    .weight(1f)
                    .padding(bottom = 8.dp)
            ) {
                Box(
                    modifier = Modifier
                        .clip(RoundedCornerShape(12.dp))
                        .background(DetailGold)
                        .padding(horizontal = 10.dp, vertical = 6.dp)
                ) {
                    Text(
                        text = kitab.kategori,
                        color = Color.White,
                        fontSize = 11.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
                Text(
                    text = kitab.judul,
                    color = Color.White,
                    fontSize = 22.sp,
                    lineHeight = 28.sp,
                    fontWeight = FontWeight.ExtraBold,
                    maxLines = 3,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 10.dp)
                )
                Text(
                    text = kitab.penulis,
                    color = Color.White.copy(alpha = 0.82f),
                    fontSize = 13.sp,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 4.dp)
                )
            }
        }
    }
}

@Composable
private fun HeroCoverCard(
    kitab: Kitab,
    modifier: Modifier = Modifier
) {
    Card(
        modifier = modifier,
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 10.dp)
    ) {
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    Brush.linearGradient(
                        colors = listOf(
                            Color(0xFFEEF4EF),
                            Color(0xFFDCEBDD)
                        )
                    )
                ),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.AutoMirrored.Filled.MenuBook,
                contentDescription = null,
                tint = DetailGreen.copy(alpha = 0.24f),
                modifier = Modifier.size(40.dp)
            )
            AsyncImage(
                model = ApiConfig.getCoverUrl(kitab.cover),
                contentDescription = kitab.judul,
                contentScale = ContentScale.Crop,
                modifier = Modifier.fillMaxSize()
            )
        }
    }
}

@Composable
private fun HeaderIconButton(
    icon: ImageVector,
    onClick: () -> Unit,
    active: Boolean = false
) {
    IconButton(
        onClick = onClick,
        modifier = Modifier
            .size(40.dp)
            .clip(RoundedCornerShape(14.dp))
            .background(if (active) DetailGold else Color.White.copy(alpha = 0.16f))
    ) {
        Icon(
            imageVector = icon,
            contentDescription = null,
            tint = Color.White,
            modifier = Modifier.size(18.dp)
        )
    }
}

@Composable
private fun StatsOverviewCard(
    averageRating: Double,
    views: Int,
    downloads: Int,
    commentsCount: Int
) {
    Card(
        modifier = Modifier
            .padding(horizontal = 20.dp)
            .offset(y = (-18).dp)
            .fillMaxWidth(),
        shape = RoundedCornerShape(22.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 5.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, DetailBorder, RoundedCornerShape(22.dp))
                .padding(vertical = 18.dp, horizontal = 12.dp),
            horizontalArrangement = Arrangement.SpaceEvenly
        ) {
            StatsItem(icon = Icons.Default.Star, value = String.format(Locale.US, "%.1f", averageRating), label = "Rating")
            StatsItem(icon = Icons.Default.Visibility, value = formatViews(views), label = "Pembaca")
            StatsItem(icon = Icons.Default.FileDownload, value = formatViews(downloads), label = "Unduhan")
            StatsItem(icon = Icons.Default.CalendarToday, value = commentsCount.toString(), label = "Ulasan")
        }
    }
}

@Composable
private fun StatsItem(
    icon: ImageVector,
    value: String,
    label: String
) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Icon(
            imageVector = icon,
            contentDescription = null,
            tint = DetailGold,
            modifier = Modifier.size(16.dp)
        )
        Text(
            text = value,
            color = DetailPrimary,
            fontSize = 16.sp,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 4.dp)
        )
        Text(
            text = label,
            color = DetailMuted,
            fontSize = 11.sp,
            modifier = Modifier.padding(top = 2.dp)
        )
    }
}

@Composable
private fun ActionButtonsRow(
    isBookmarked: Boolean,
    isDownloaded: Boolean,
    onRead: () -> Unit,
    onBookmarkToggle: () -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 4.dp),
        horizontalArrangement = Arrangement.spacedBy(10.dp)
    ) {
        Button(
            onClick = onRead,
            modifier = Modifier
                .weight(1f)
                .height(52.dp),
            colors = ButtonDefaults.buttonColors(containerColor = DetailGreen),
            shape = RoundedCornerShape(18.dp)
        ) {
            Icon(
                imageVector = Icons.AutoMirrored.Filled.MenuBook,
                contentDescription = null,
                modifier = Modifier.size(18.dp)
            )
            Text(
                text = if (isDownloaded) "Baca Kitab" else "Download & Baca",
                fontSize = 14.sp,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(start = 8.dp)
            )
        }

        Surface(
            modifier = Modifier
                .size(52.dp)
                .clip(RoundedCornerShape(18.dp))
                .clickable(onClick = onBookmarkToggle),
            color = if (isBookmarked) Color(0xFFE8F5E9) else Color.White,
            border = BorderStroke(1.5.dp, if (isBookmarked) DetailGreen else DetailBorder)
        ) {
            Box(contentAlignment = Alignment.Center) {
                Icon(
                    imageVector = if (isBookmarked) Icons.Default.Bookmark else Icons.Default.BookmarkBorder,
                    contentDescription = null,
                    tint = if (isBookmarked) DetailGreen else DetailMuted
                )
            }
        }
    }
}

@Composable
private fun SectionCard(
    title: String,
    modifier: Modifier = Modifier,
    content: @Composable ColumnScope.() -> Unit
) {
    Card(
        modifier = modifier.fillMaxWidth(),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, DetailBorder, RoundedCornerShape(20.dp))
                .padding(16.dp),
            content = {
                Text(
                    text = title,
                    color = DetailPrimary,
                    fontSize = 16.sp,
                    fontWeight = FontWeight.ExtraBold
                )
                Spacer(modifier = Modifier.height(12.dp))
                content()
            }
        )
    }
}

private data class DetailRowData(
    val icon: ImageVector,
    val label: String,
    val value: String,
    val background: Color,
    val tint: Color
)

@Composable
private fun DetailInfoRow(row: DetailRowData) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(DetailBackground)
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(38.dp)
                .clip(RoundedCornerShape(12.dp))
                .background(row.background),
            contentAlignment = Alignment.Center
        ) {
            Icon(imageVector = row.icon, contentDescription = null, tint = row.tint, modifier = Modifier.size(18.dp))
        }
        Column(modifier = Modifier.padding(start = 12.dp)) {
            Text(text = row.label, fontSize = 11.sp, color = DetailMuted)
            Text(text = row.value, fontSize = 13.sp, color = DetailPrimary, fontWeight = FontWeight.SemiBold)
        }
    }
}

@Composable
private fun RatingReviewSection(
    averageRating: Double,
    ratingsCount: Int,
    myRating: Int,
    newComment: String,
    comments: List<Comment>,
    isSubmittingComment: Boolean,
    currentUserId: Int,
    onRate: (Int) -> Unit,
    onNewCommentChange: (String) -> Unit,
    onSubmitComment: () -> Unit,
    onDeleteComment: (Int) -> Unit
) {
    Column(modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Rating & Ulasan",
                fontSize = 16.sp,
                color = DetailPrimary,
                fontWeight = FontWeight.ExtraBold
            )
            Row(verticalAlignment = Alignment.CenterVertically) {
                Icon(
                    imageVector = Icons.Default.Star,
                    contentDescription = null,
                    tint = DetailGold,
                    modifier = Modifier.size(16.dp)
                )
                Text(
                    text = String.format(Locale.US, "%.1f", averageRating),
                    color = DetailPrimary,
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.padding(start = 4.dp)
                )
                Text(
                    text = "($ratingsCount)",
                    color = DetailMuted,
                    fontSize = 12.sp,
                    modifier = Modifier.padding(start = 4.dp)
                )
            }
        }

        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 12.dp),
            shape = RoundedCornerShape(20.dp),
            colors = CardDefaults.cardColors(containerColor = Color.White)
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .border(1.dp, DetailBorder, RoundedCornerShape(20.dp))
                    .padding(18.dp),
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                Text(
                    text = String.format(Locale.US, "%.1f", averageRating),
                    color = DetailPrimary,
                    fontSize = 38.sp,
                    fontWeight = FontWeight.ExtraBold
                )
                RatingBar(
                    rating = averageRating.toInt().coerceIn(0, 5),
                    onRatingChanged = {},
                    starSize = 18.dp,
                    clickable = false
                )
                Text(
                    text = "Berdasarkan $ratingsCount penilaian",
                    color = DetailMuted,
                    fontSize = 12.sp,
                    modifier = Modifier.padding(top = 8.dp)
                )

                HorizontalDivider(
                    modifier = Modifier.padding(vertical = 16.dp),
                    color = DetailBorder
                )

                Text(
                    text = "Tulis Ulasan",
                    color = DetailPrimary,
                    fontSize = 16.sp,
                    fontWeight = FontWeight.ExtraBold,
                    modifier = Modifier.align(Alignment.Start)
                )
                RatingBar(
                    rating = myRating,
                    onRatingChanged = onRate,
                    modifier = Modifier
                        .align(Alignment.Start)
                        .padding(top = 10.dp),
                    starSize = 28.dp,
                    clickable = true
                )
                Text(
                    text = if (myRating > 0) "Nilai yang dipilih: $myRating/5" else "Ketuk bintang di atas untuk memberi nilai",
                    color = if (myRating > 0) DetailGreen else DetailMuted,
                    fontSize = 12.sp,
                    modifier = Modifier
                        .align(Alignment.Start)
                        .padding(top = 6.dp)
                )

                OutlinedTextField(
                    value = newComment,
                    onValueChange = onNewCommentChange,
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(top = 14.dp),
                    minLines = 4,
                    maxLines = 6,
                    placeholder = {
                        Text(
                            text = "Tulis ulasan Anda di sini...",
                            color = DetailMuted,
                            fontSize = 13.sp
                        )
                    },
                    textStyle = TextStyle(
                        color = DetailPrimary,
                        fontSize = 13.sp,
                        lineHeight = 19.sp
                    ),
                    shape = RoundedCornerShape(16.dp),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedBorderColor = DetailGreen,
                        unfocusedBorderColor = DetailBorder,
                        focusedContainerColor = DetailBackground,
                        unfocusedContainerColor = DetailBackground,
                        cursorColor = DetailGreen
                    )
                )

                Button(
                    onClick = onSubmitComment,
                    enabled = newComment.trim().isNotEmpty() && !isSubmittingComment,
                    modifier = Modifier
                        .align(Alignment.End)
                        .padding(top = 12.dp)
                        .height(42.dp),
                    colors = ButtonDefaults.buttonColors(
                        containerColor = DetailGreen,
                        disabledContainerColor = Color(0xFFD7D2C8)
                    ),
                    shape = RoundedCornerShape(14.dp)
                ) {
                    if (isSubmittingComment) {
                        CircularProgressIndicator(
                            modifier = Modifier.size(16.dp),
                            color = Color.White,
                            strokeWidth = 2.dp
                        )
                        Text(
                            text = "Mengirim...",
                            fontSize = 13.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.padding(start = 6.dp)
                        )
                    } else {
                        Icon(
                            imageVector = Icons.AutoMirrored.Filled.Send,
                            contentDescription = null,
                            modifier = Modifier.size(16.dp)
                        )
                        Text(
                            text = "Kirim",
                            fontSize = 13.sp,
                            fontWeight = FontWeight.Bold,
                            modifier = Modifier.padding(start = 6.dp)
                        )
                    }
                }
            }
        }

        Column(verticalArrangement = Arrangement.spacedBy(10.dp), modifier = Modifier.padding(top = 12.dp)) {
            if (comments.isEmpty()) {
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(18.dp),
                    colors = CardDefaults.cardColors(containerColor = Color.White)
                ) {
                    Box(
                        modifier = Modifier
                            .fillMaxWidth()
                            .border(1.dp, DetailBorder, RoundedCornerShape(18.dp))
                            .padding(vertical = 28.dp),
                        contentAlignment = Alignment.Center
                    ) {
                        Text(
                            text = "Belum ada ulasan. Jadilah yang pertama.",
                            color = DetailMuted,
                            fontSize = 13.sp
                        )
                    }
                }
            } else {
                comments.forEach { comment ->
                    ReviewCommentCard(
                        comment = comment,
                        canDelete = comment.idUser == currentUserId && currentUserId > 0,
                        onDelete = { onDeleteComment(comment.id) }
                    )
                }
            }
        }
    }
}

@Composable
private fun ReviewCommentCard(
    comment: Comment,
    canDelete: Boolean,
    onDelete: () -> Unit
) {
    val avatarColor = remember(comment.username) {
        val palette = listOf(
            Color(0xFF1B9AAA),
            Color(0xFF2D7A52),
            Color(0xFFF59E0B),
            Color(0xFFE11D48),
            Color(0xFF7C3AED),
            Color(0xFF2563EB)
        )
        palette[kotlin.math.abs(comment.username.hashCode()) % palette.size]
    }

    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(18.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, DetailBorder, RoundedCornerShape(18.dp))
                .padding(14.dp),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            Box(
                modifier = Modifier
                    .size(40.dp)
                    .clip(CircleShape)
                    .background(avatarColor.copy(alpha = 0.15f)),
                contentAlignment = Alignment.Center
            ) {
                Text(
                    text = comment.username.ifBlank { "U" }.first().uppercase(),
                    color = avatarColor,
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Bold
                )
            }

            Column(modifier = Modifier.weight(1f)) {
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween,
                    verticalAlignment = Alignment.Top
                ) {
                    Column {
                        Text(
                            text = comment.username.ifBlank { "User" },
                            color = DetailPrimary,
                            fontSize = 14.sp,
                            fontWeight = FontWeight.Bold
                        )
                        Row(
                            verticalAlignment = Alignment.CenterVertically,
                            horizontalArrangement = Arrangement.spacedBy(6.dp),
                            modifier = Modifier.padding(top = 2.dp)
                        ) {
                            if (comment.rating > 0) {
                                RatingBar(
                                    rating = comment.rating.coerceIn(0, 5),
                                    onRatingChanged = {},
                                    starSize = 12.dp,
                                    clickable = false
                                )
                            }
                            Text(
                                text = comment.getFormattedDate(),
                                color = DetailMuted,
                                fontSize = 11.sp
                            )
                        }
                    }

                    if (canDelete) {
                        IconButton(onClick = onDelete, modifier = Modifier.size(28.dp)) {
                            Icon(
                                imageVector = Icons.Default.Delete,
                                contentDescription = "Hapus komentar",
                                tint = DetailMuted,
                                modifier = Modifier.size(16.dp)
                            )
                        }
                    }
                }

                Text(
                    text = comment.comment,
                    color = DetailSecondary,
                    fontSize = 13.sp,
                    lineHeight = 19.sp,
                    modifier = Modifier.padding(top = 8.dp)
                )
            }
        }
    }
}

@Composable
private fun RelatedBookCard(
    kitab: Kitab,
    onClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .width(138.dp)
            .clip(RoundedCornerShape(16.dp))
            .background(DetailBackground)
            .border(1.dp, DetailBorder, RoundedCornerShape(16.dp))
            .clickable(onClick = onClick)
            .padding(10.dp)
    ) {
        AsyncImage(
            model = ApiConfig.getCoverUrl(kitab.cover),
            contentDescription = kitab.judul,
            contentScale = ContentScale.Crop,
            modifier = Modifier
                .fillMaxWidth()
                .height(168.dp)
                .clip(RoundedCornerShape(12.dp))
        )
        Text(
            text = kitab.judul,
            color = DetailPrimary,
            fontSize = 13.sp,
            lineHeight = 17.sp,
            fontWeight = FontWeight.Bold,
            maxLines = 2,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.padding(top = 8.dp)
        )
        Text(
            text = kitab.penulis,
            color = DetailMuted,
            fontSize = 11.sp,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.padding(top = 2.dp)
        )
    }
}

@Composable
private fun ErrorContent(
    message: String,
    onBack: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(DetailBackground)
            .padding(horizontal = 28.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Icon(
            imageVector = Icons.Default.Info,
            contentDescription = null,
            tint = DetailGold,
            modifier = Modifier.size(56.dp)
        )
        Text(
            text = "Terjadi Kesalahan",
            color = DetailPrimary,
            fontSize = 18.sp,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 12.dp)
        )
        Text(
            text = message,
            color = DetailMuted,
            fontSize = 13.sp,
            lineHeight = 20.sp,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(top = 6.dp)
        )
        Button(
            onClick = onBack,
            modifier = Modifier.padding(top = 18.dp),
            colors = ButtonDefaults.buttonColors(containerColor = DetailGreen),
            shape = RoundedCornerShape(14.dp)
        ) {
            Text("Kembali", color = Color.White, fontWeight = FontWeight.Bold)
        }
    }
}

@Composable
private fun RatingBar(
    rating: Int,
    onRatingChanged: (Int) -> Unit,
    modifier: Modifier = Modifier,
    starSize: Dp = 30.dp,
    clickable: Boolean
) {
    Row(modifier = modifier, horizontalArrangement = Arrangement.spacedBy(2.dp)) {
        for (index in 1..5) {
            Icon(
                imageVector = if (index <= rating) Icons.Default.Star else Icons.Default.StarBorder,
                contentDescription = "Star $index",
                tint = if (index <= rating) Color(0xFFFFC107) else Color(0xFFE0D7C7),
                modifier = Modifier
                    .size(starSize)
                    .then(
                        if (clickable) Modifier.clickable { onRatingChanged(index) } else Modifier
                    )
            )
        }
    }
}

private fun formatViews(count: Int): String {
    return when {
        count >= 1_000_000 -> String.format(Locale.US, "%.1fM", count / 1_000_000.0)
        count >= 1_000 -> String.format(Locale.US, "%.1fK", count / 1_000.0)
        else -> count.toString()
    }
}
