package com.example.al_kutub.ui.screens
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.BasicTextField
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.automirrored.filled.TrendingUp
import androidx.compose.material.icons.filled.Book
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Star
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.model.UiKitab
import com.example.al_kutub.ui.navigation.AppScreen
import com.example.al_kutub.ui.viewmodel.HomeViewModel
import com.example.al_kutub.utils.SessionManager
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

private val HomeBackground = Color(0xFFFAFAF5)
private val HomeHeaderStart = Color(0xFF1B5E3B)
private val HomeHeaderMid = Color(0xFF2D7A52)
private val HomeHeaderEnd = Color(0xFF1A4A30)
private val HomeGold = Color(0xFFC8A951)
private val HomePrimary = Color(0xFF1A2E1A)
private val HomeSecondary = Color(0xFF6B5E4E)
private val HomeMuted = Color(0xFF8B8070)
private val HomeBorder = Color(0xFFE8E3D5)

@Composable
fun HomeScreen(
    navController: NavController,
    viewModel: HomeViewModel = hiltViewModel()
) {
    val kitabList by viewModel.kitabList.collectAsState()
    val recommendations by viewModel.recommendations.collectAsState()
    val loading by viewModel.loading.collectAsState()
    val errorMessage by viewModel.errorMessage.collectAsState()

    val context = LocalContext.current
    val sessionManager = remember(context) { SessionManager.getInstance(context) }
    val username = remember { sessionManager.getUsername().orEmpty().ifBlank { "Sahabat" } }

    LaunchedEffect(Unit) {
        viewModel.loadKitab()
    }

    val books = remember(kitabList) { kitabList }
    val popularBooks = remember(books) { books.sortedByDescending { it.views }.take(8) }
    val categories = remember(books) { books.map { it.kategori }.filter { it.isNotBlank() }.distinct().take(8) }
    val featuredBook = remember(popularBooks, books) { popularBooks.firstOrNull() ?: books.firstOrNull() }
    val listPreview = remember(books) { books.take(6) }

    val dateText = remember {
        SimpleDateFormat("EEEE, d MMMM", Locale("id", "ID")).format(Date())
    }

    LazyColumn(
        modifier = Modifier
            .fillMaxSize()
            .background(HomeBackground),
        contentPadding = PaddingValues(bottom = 100.dp)
    ) {
        item {
            HomeHeader(
                username = username,
                dateText = dateText,
                totalBooks = books.size,
                totalCategories = categories.size,
                totalReaders = books.sumOf { it.views },
                onOpenSearch = { navController.navigate(AppScreen.Search.createRoute()) },
                onNotificationsClick = { navController.navigate(AppScreen.Notifications.route) },
                onProfileClick = { navController.navigate(AppScreen.Account.route) }
            )
        }

        item {
            if (loading) {
                Box(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(28.dp),
                    contentAlignment = Alignment.Center
                ) {
                    CircularProgressIndicator(color = HomeHeaderStart)
                }
            } else if (!errorMessage.isNullOrBlank() && books.isEmpty()) {
                ErrorBanner(
                    message = errorMessage ?: "Gagal memuat kitab",
                    onRetry = { viewModel.loadKitab() }
                )
            }
        }

        featuredBook?.let { featured ->
            item {
                FeaturedBanner(
                    book = featured,
                    onReadNow = { navController.navigate("${AppScreen.KitabDetail.route}/${featured.idKitab}") }
                )
            }
        }

        if (recommendations.isNotEmpty()) {
            item {
                SectionHeader(
                    icon = Icons.Default.Star,
                    title = "Rekomendasi Untukmu",
                    actionLabel = "",
                    onActionClick = { }
                )
            }

            item {
                LazyRow(
                    modifier = Modifier.fillMaxWidth(),
                    contentPadding = PaddingValues(horizontal = 20.dp),
                    horizontalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    items(recommendations, key = { it.idKitab }) { book ->
                        HomeGridBookCard(
                            book = book,
                            onClick = { navController.navigate("${AppScreen.KitabDetail.route}/${book.idKitab}") }
                        )
                    }
                }
            }
        }

        item {
            SectionHeader(
                icon = Icons.AutoMirrored.Filled.TrendingUp,
                title = "Kitab Populer",
                actionLabel = "Lihat Semua",
                onActionClick = { navController.navigate(AppScreen.Katalog.route) }
            )
        }

        item {
            LazyRow(
                modifier = Modifier.fillMaxWidth(),
                contentPadding = PaddingValues(horizontal = 20.dp),
                horizontalArrangement = Arrangement.spacedBy(10.dp)
            ) {
                items(popularBooks, key = { it.idKitab }) { book ->
                    HomeGridBookCard(
                        book = book,
                        onClick = { navController.navigate("${AppScreen.KitabDetail.route}/${book.idKitab}") }
                    )
                }
            }
        }

        item {
            SectionHeader(
                icon = Icons.AutoMirrored.Filled.MenuBook,
                title = "Semua Kitab",
                actionLabel = "",
                onActionClick = {}
            )
        }

        items(listPreview, key = { it.idKitab }) { book ->
            HomeListBookCard(
                book = book,
                modifier = Modifier.padding(horizontal = 20.dp, vertical = 6.dp),
                onClick = { navController.navigate("${AppScreen.KitabDetail.route}/${book.idKitab}") }
            )
        }

        item {
            Surface(
                modifier = Modifier
                    .padding(horizontal = 20.dp, vertical = 12.dp)
                    .fillMaxWidth()
                    .clip(RoundedCornerShape(16.dp))
                    .clickable { navController.navigate(AppScreen.Katalog.route) },
                color = Color.Transparent,
                shape = RoundedCornerShape(16.dp)
            ) {
                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .border(1.5.dp, HomeHeaderStart, RoundedCornerShape(16.dp))
                        .padding(vertical = 14.dp),
                    horizontalArrangement = Arrangement.Center,
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    Text(
                        text = "Lihat Semua Kitab",
                        color = HomeHeaderStart,
                        fontSize = 14.sp,
                        fontWeight = FontWeight.Bold
                    )
                    Icon(
                        imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                        contentDescription = null,
                        tint = HomeHeaderStart,
                        modifier = Modifier
                            .padding(start = 4.dp)
                            .size(16.dp)
                    )
                }
            }
        }
    }
}

@Composable
private fun HomeHeader(
    username: String,
    dateText: String,
    totalBooks: Int,
    totalCategories: Int,
    totalReaders: Int,
    onOpenSearch: () -> Unit,
    onNotificationsClick: () -> Unit,
    onProfileClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                Brush.linearGradient(
                    colors = listOf(HomeHeaderStart, HomeHeaderMid, HomeHeaderEnd)
                )
            )
            .padding(horizontal = 20.dp, vertical = 18.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Column {
                Text(
                    text = dateText,
                    fontSize = 12.sp,
                    color = Color.White.copy(alpha = 0.72f)
                )
                Text(
                    text = "Ahlan, ${username.substringBefore(" ")}!",
                    fontSize = 20.sp,
                    color = Color.White,
                    fontWeight = FontWeight.ExtraBold,
                    modifier = Modifier.padding(top = 2.dp)
                )
                Text(
                    text = "بِسْمِ اللَّهِ — Mari mulai belajar hari ini",
                    fontSize = 12.sp,
                    color = Color.White.copy(alpha = 0.65f),
                    modifier = Modifier.padding(top = 2.dp)
                )
            }

            Row(
                horizontalArrangement = Arrangement.spacedBy(10.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(RoundedCornerShape(16.dp))
                        .background(Color.White.copy(alpha = 0.14f))
                        .clickable(onClick = onNotificationsClick),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Default.Notifications,
                        contentDescription = "Notifikasi",
                        tint = Color.White,
                        modifier = Modifier.size(20.dp)
                    )
                }

                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(RoundedCornerShape(16.dp))
                        .background(HomeGold.copy(alpha = 0.24f))
                        .clickable(onClick = onProfileClick),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = username.firstOrNull()?.uppercase() ?: "U",
                        color = HomeGold,
                        fontWeight = FontWeight.ExtraBold,
                        fontSize = 18.sp
                    )
                }
            }
        }

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 16.dp)
                .clip(RoundedCornerShape(16.dp))
                .background(Color.White.copy(alpha = 0.12f))
                .border(1.dp, Color.White.copy(alpha = 0.16f), RoundedCornerShape(16.dp))
                .clickable(onClick = onOpenSearch)
                .padding(horizontal = 14.dp, vertical = 12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                imageVector = Icons.Default.Search,
                contentDescription = null,
                tint = Color.White.copy(alpha = 0.62f),
                modifier = Modifier.size(18.dp)
            )
            Text(
                text = "Cari kitab, ulama, kategori...",
                color = Color.White.copy(alpha = 0.62f),
                fontSize = 14.sp,
                modifier = Modifier
                    .weight(1f)
                    .padding(start = 8.dp)
            )
        }

        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 12.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            StatCard(emoji = "📚", label = "Total Kitab", value = "$totalBooks+")
            StatCard(emoji = "🗂️", label = "Kategori", value = totalCategories.toString())
            StatCard(emoji = "👥", label = "Pembaca", value = formatCompact(totalReaders))
        }
    }
}

@Composable
private fun RowScope.StatCard(
    emoji: String,
    label: String,
    value: String
) {
    Column(
        modifier = Modifier
            .weight(1f)
            .clip(RoundedCornerShape(14.dp))
            .background(Color.White.copy(alpha = 0.11f))
            .padding(vertical = 10.dp, horizontal = 8.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = emoji, fontSize = 18.sp)
        Text(
            text = value,
            color = HomeGold,
            fontWeight = FontWeight.ExtraBold,
            fontSize = 14.sp,
            modifier = Modifier.padding(top = 2.dp)
        )
        Text(
            text = label,
            color = Color.White.copy(alpha = 0.65f),
            fontSize = 10.sp,
            modifier = Modifier.padding(top = 2.dp)
        )
    }
}

@Composable
private fun SearchResultHeader(count: Int, query: String) {
    Text(
        text = "$count kitab ditemukan untuk \"$query\"",
        fontSize = 13.sp,
        color = HomeMuted,
        modifier = Modifier.padding(horizontal = 20.dp, vertical = 16.dp)
    )
}

@Composable
private fun EmptySearchView() {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 36.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "📖", fontSize = 44.sp)
        Text(
            text = "Kitab tidak ditemukan",
            fontSize = 15.sp,
            color = HomeSecondary,
            fontWeight = FontWeight.Bold,
            modifier = Modifier.padding(top = 8.dp)
        )
        Text(
            text = "Coba kata kunci lain",
            fontSize = 13.sp,
            color = HomeMuted,
            modifier = Modifier.padding(top = 2.dp)
        )
    }
}

@Composable
private fun FeaturedBanner(
    book: UiKitab,
    onReadNow: () -> Unit
) {
    Box(
        modifier = Modifier
            .padding(horizontal = 20.dp, vertical = 18.dp)
            .fillMaxWidth()
            .height(152.dp)
            .clip(RoundedCornerShape(24.dp))
            .clickable(onClick = onReadNow)
            .background(Brush.linearGradient(colors = listOf(HomeGold, Color(0xFFA67C20))))
    ) {
        Row(modifier = Modifier.fillMaxSize()) {
            Column(
                modifier = Modifier
                    .weight(1f)
                    .padding(18.dp),
                verticalArrangement = Arrangement.Center
            ) {
                Text(
                    text = "UNGGULAN HARI INI",
                    fontSize = 10.sp,
                    color = Color.White.copy(alpha = 0.82f),
                    fontWeight = FontWeight.SemiBold
                )
                Text(
                    text = book.judul,
                    fontSize = 16.sp,
                    lineHeight = 20.sp,
                    color = Color.White,
                    fontWeight = FontWeight.ExtraBold,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 4.dp)
                )
                Text(
                    text = book.penulis,
                    fontSize = 11.sp,
                    color = Color.White.copy(alpha = 0.82f),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 2.dp)
                )
                Surface(
                    modifier = Modifier
                        .padding(top = 10.dp)
                        .clip(RoundedCornerShape(12.dp))
                        .clickable(onClick = onReadNow),
                    color = Color.White.copy(alpha = 0.18f),
                    shape = RoundedCornerShape(12.dp)
                ) {
                    Text(
                        text = "Baca Sekarang →",
                        fontSize = 12.sp,
                        color = Color.White,
                        fontWeight = FontWeight.SemiBold,
                        modifier = Modifier.padding(horizontal = 12.dp, vertical = 7.dp)
                    )
                }
            }

            Box(
                modifier = Modifier
                    .width(128.dp)
                    .fillMaxSize()
                    .background(Color.Black.copy(alpha = 0.12f)),
                contentAlignment = Alignment.Center
            ) {
                Box(
                    modifier = Modifier
                        .padding(horizontal = 14.dp, vertical = 12.dp)
                        .fillMaxHeight()
                        .aspectRatio(0.72f)
                        .clip(RoundedCornerShape(18.dp))
                        .background(
                            Brush.linearGradient(
                                colors = listOf(
                                    Color.White.copy(alpha = 0.28f),
                                    Color.White.copy(alpha = 0.12f)
                                )
                            )
                        )
                ) {
                    Text(
                        text = "📖",
                        fontSize = 34.sp,
                        modifier = Modifier.align(Alignment.Center)
                    )
                    AsyncImage(
                        model = ApiConfig.getCoverUrl(book.cover),
                        contentDescription = book.judul,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier.fillMaxSize()
                    )
                }
            }
        }
    }
}

@Composable
private fun SectionHeader(
    icon: androidx.compose.ui.graphics.vector.ImageVector,
    title: String,
    actionLabel: String,
    onActionClick: () -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 12.dp),
        horizontalArrangement = Arrangement.SpaceBetween,
        verticalAlignment = Alignment.CenterVertically
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = HomeGold,
                modifier = Modifier.size(18.dp)
            )
            Text(
                text = title,
                fontSize = 17.sp,
                color = HomePrimary,
                fontWeight = FontWeight.ExtraBold,
                modifier = Modifier.padding(start = 6.dp)
            )
        }

        if (actionLabel.isNotBlank()) {
            Row(
                modifier = Modifier.clickable(onClick = onActionClick),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = actionLabel,
                    fontSize = 13.sp,
                    color = HomeHeaderStart,
                    fontWeight = FontWeight.SemiBold
                )
                Icon(
                    imageVector = Icons.AutoMirrored.Filled.ArrowForward,
                    contentDescription = null,
                    tint = HomeHeaderStart,
                    modifier = Modifier.size(14.dp)
                )
            }
        }
    }
}

@Composable
private fun HomeGridBookCard(
    book: UiKitab,
    onClick: () -> Unit
) {
    Column(
        modifier = Modifier
            .width(146.dp)
            .clip(RoundedCornerShape(14.dp))
            .background(Color.White)
            .border(1.dp, HomeBorder, RoundedCornerShape(14.dp))
            .clickable(onClick = onClick)
            .padding(10.dp)
    ) {
        AsyncImage(
            model = ApiConfig.getCoverUrl(book.cover),
            contentDescription = book.judul,
            contentScale = ContentScale.Crop,
            modifier = Modifier
                .fillMaxWidth()
                .height(162.dp)
                .clip(RoundedCornerShape(10.dp))
        )

        Text(
            text = book.judul,
            fontSize = 13.sp,
            lineHeight = 17.sp,
            color = HomePrimary,
            fontWeight = FontWeight.Bold,
            maxLines = 2,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.padding(top = 8.dp)
        )
        Text(
            text = book.penulis,
            fontSize = 11.sp,
            color = HomeMuted,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.padding(top = 2.dp)
        )
        Text(
            text = "${formatCompact(book.views)} pembaca",
            fontSize = 10.sp,
            color = HomeHeaderStart,
            fontWeight = FontWeight.SemiBold,
            modifier = Modifier.padding(top = 6.dp)
        )
    }
}

@Composable
private fun HomeListBookCard(
    book: UiKitab,
    modifier: Modifier = Modifier,
    onClick: () -> Unit
) {
    Row(
        modifier = modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(14.dp))
            .background(Color.White)
            .border(1.dp, HomeBorder, RoundedCornerShape(14.dp))
            .clickable(onClick = onClick)
            .padding(10.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        AsyncImage(
            model = ApiConfig.getCoverUrl(book.cover),
            contentDescription = book.judul,
            contentScale = ContentScale.Crop,
            modifier = Modifier
                .width(64.dp)
                .height(88.dp)
                .clip(RoundedCornerShape(10.dp))
        )

        Column(modifier = Modifier.padding(start = 10.dp).weight(1f)) {
            Text(
                text = book.judul,
                fontSize = 14.sp,
                lineHeight = 18.sp,
                color = HomePrimary,
                fontWeight = FontWeight.Bold,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            Text(
                text = book.penulis,
                fontSize = 12.sp,
                color = HomeMuted,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 2.dp)
            )
            Text(
                text = "${book.kategori} • ${book.bahasa}",
                fontSize = 11.sp,
                color = HomeHeaderStart,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(top = 6.dp)
            )
        }

        Icon(
            imageVector = Icons.AutoMirrored.Filled.ArrowForward,
            contentDescription = null,
            tint = HomeMuted,
            modifier = Modifier.size(16.dp)
        )
    }
}

@Composable
private fun ErrorBanner(
    message: String,
    onRetry: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 20.dp, vertical = 18.dp)
            .clip(RoundedCornerShape(14.dp))
            .background(Color(0xFFFFF5F5))
            .border(1.dp, Color(0xFFFED7D7), RoundedCornerShape(14.dp))
            .padding(14.dp)
    ) {
        Text(
            text = "Tidak dapat memuat data",
            color = Color(0xFFC53030),
            fontWeight = FontWeight.Bold,
            fontSize = 13.sp
        )
        Text(
            text = message,
            color = Color(0xFF9B2C2C),
            fontSize = 12.sp,
            modifier = Modifier.padding(top = 4.dp)
        )
        HorizontalDivider(
            modifier = Modifier.padding(vertical = 10.dp),
            color = Color(0xFFFED7D7)
        )
        Row(
            modifier = Modifier
                .clip(RoundedCornerShape(10.dp))
                .background(HomeHeaderStart)
                .clickable(onClick = onRetry)
                .padding(horizontal = 14.dp, vertical = 8.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                imageVector = Icons.Default.Book,
                contentDescription = null,
                tint = Color.White,
                modifier = Modifier.size(14.dp)
            )
            Text(
                text = "Coba lagi",
                color = Color.White,
                fontSize = 12.sp,
                fontWeight = FontWeight.SemiBold,
                modifier = Modifier.padding(start = 6.dp)
            )
        }
    }
}

private fun formatCompact(value: Int): String {
    return when {
        value >= 1_000_000 -> String.format(Locale.US, "%.1fM", value / 1_000_000f)
        value >= 1_000 -> String.format(Locale.US, "%.1fK", value / 1_000f)
        else -> value.toString()
    }
}
