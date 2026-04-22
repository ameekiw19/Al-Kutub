package com.example.al_kutub.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.automirrored.filled.MenuBook
import androidx.compose.material.icons.filled.BookmarkBorder
import androidx.compose.material.icons.filled.CalendarToday
import androidx.compose.material.icons.filled.ChevronRight
import androidx.compose.material.icons.filled.DarkMode
import androidx.compose.material.icons.filled.Edit
import androidx.compose.material.icons.filled.ErrorOutline
import androidx.compose.material.icons.filled.History
import androidx.compose.material.icons.filled.LightMode
import androidx.compose.material.icons.filled.Login
import androidx.compose.material.icons.filled.Mail
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.QuestionAnswer
import androidx.compose.material.icons.filled.WarningAmber
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Switch
import androidx.compose.material3.SwitchDefaults
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import coil.compose.AsyncImage
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.model.AccountData
import com.example.al_kutub.model.AccountHistoryItem
import com.example.al_kutub.model.ThemeMode
import com.example.al_kutub.ui.viewmodel.AccountUiState
import com.example.al_kutub.ui.viewmodel.AccountViewModel
import com.example.al_kutub.utils.ThemeManager
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Locale

private val AccountBackground = Color(0xFFFAFAF5)
private val AccountHeaderStart = Color(0xFF1B5E3B)
private val AccountHeaderEnd = Color(0xFF2D7A52)
private val AccountGold = Color(0xFFC8A951)
private val AccountBorder = Color(0xFFF0EBE0)
private val AccountPrimary = Color(0xFF1A2E1A)
private val AccountSecondary = Color(0xFF6B5E4E)
private val AccountMuted = Color(0xFF8B8070)
private val AccountDanger = Color(0xFFE53935)

@Composable
fun AccountScreen(
    viewModel: AccountViewModel = hiltViewModel(),
    themeManager: ThemeManager,
    onNavigateToLogin: () -> Unit = {},
    onNavigateToRegister: () -> Unit = {},
    onNavigateToEditProfile: () -> Unit = {},
    onNavigateToBookmarks: () -> Unit = {},
    onNavigateToHistory: () -> Unit = {},
    onNavigateToKitabDetail: (Int) -> Unit = {}
) {
    val accountState by viewModel.accountState.collectAsState()
    val logoutState by viewModel.logoutState.collectAsState()
    var showLogoutDialog by remember { mutableStateOf(false) }

    val themeMode by themeManager.themeMode.collectAsState(initial = ThemeMode.LIGHT)
    val isSystemDark = isSystemInDarkTheme()
    val isDarkMode = when (themeMode) {
        ThemeMode.LIGHT -> false
        ThemeMode.DARK -> true
        ThemeMode.AUTO -> isSystemDark
    }
    val scope = rememberCoroutineScope()

    LaunchedEffect(Unit) {
        viewModel.loadAccount()
    }

    LaunchedEffect(logoutState) {
        if (logoutState == true) {
            onNavigateToLogin()
            viewModel.resetLogoutState()
        }
    }

    Scaffold(containerColor = AccountBackground) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            when (val state = accountState) {
                is AccountUiState.Loading -> {
                    Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                        CircularProgressIndicator(color = AccountHeaderStart)
                    }
                }

                is AccountUiState.Success -> {
                    AccountContent(
                        data = state.data,
                        isDarkMode = isDarkMode,
                        onDarkModeToggle = { scope.launch { themeManager.toggleTheme() } },
                        onLogoutClick = { showLogoutDialog = true },
                        onNavigateToEditProfile = onNavigateToEditProfile,
                        onNavigateToBookmarks = onNavigateToBookmarks,
                        onNavigateToHistory = onNavigateToHistory,
                        onNavigateToLatestBook = onNavigateToKitabDetail
                    )
                }

                is AccountUiState.Error -> {
                    val needsLogin = state.message.contains("login", ignoreCase = true)
                    if (needsLogin) {
                        GuestAccountView(
                            onNavigateToLogin = onNavigateToLogin,
                            onNavigateToRegister = onNavigateToRegister
                        )
                    } else {
                        AccountErrorView(
                            message = state.message,
                            onRetry = { viewModel.loadAccount() }
                        )
                    }
                }
            }
        }
    }

    if (showLogoutDialog) {
        LogoutDialog(
            onDismiss = { showLogoutDialog = false },
            onConfirm = {
                showLogoutDialog = false
                viewModel.logout()
            }
        )
    }
}

@Composable
private fun GuestAccountView(
    onNavigateToLogin: () -> Unit,
    onNavigateToRegister: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(AccountBackground)
            .padding(horizontal = 24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(96.dp)
                .clip(RoundedCornerShape(28.dp))
                .background(Color(0xFFF0F7F3)),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = Icons.Default.Person,
                contentDescription = null,
                tint = AccountMuted,
                modifier = Modifier.size(48.dp)
            )
        }

        Text(
            text = "Belum Masuk",
            fontSize = 20.sp,
            color = AccountPrimary,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 16.dp)
        )
        Text(
            text = "Masuk untuk mengakses profil, simpanan, dan riwayat bacamu",
            fontSize = 13.sp,
            color = AccountMuted,
            lineHeight = 20.sp,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(top = 6.dp)
        )

        Button(
            onClick = onNavigateToLogin,
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 24.dp)
                .height(52.dp),
            colors = ButtonDefaults.buttonColors(containerColor = AccountHeaderStart),
            shape = RoundedCornerShape(18.dp)
        ) {
            Text("Masuk ke Akun", fontSize = 15.sp, fontWeight = FontWeight.Bold)
        }

        TextButton(
            onClick = onNavigateToRegister,
            modifier = Modifier.padding(top = 8.dp)
        ) {
            Text(
                text = "Daftar Akun Baru",
                color = AccountHeaderStart,
                fontSize = 14.sp,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

@Composable
private fun AccountContent(
    data: AccountData,
    isDarkMode: Boolean,
    onDarkModeToggle: () -> Unit,
    onLogoutClick: () -> Unit,
    onNavigateToEditProfile: () -> Unit,
    onNavigateToBookmarks: () -> Unit,
    onNavigateToHistory: () -> Unit,
    onNavigateToLatestBook: (Int) -> Unit
) {
    val latestActivity = remember(data.aktivitasTerbaru) { data.aktivitasTerbaru.firstOrNull() }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(AccountBackground)
            .verticalScroll(rememberScrollState())
    ) {
        AccountHeader(
            data = data,
            onNavigateToEditProfile = onNavigateToEditProfile
        )

        Column(
            modifier = Modifier
                .padding(horizontal = 20.dp)
                .offset(y = (-20).dp)
        ) {
            AccountStatsCard(data = data)

            if (latestActivity?.kitab != null) {
                LatestReadingCard(
                    activity = latestActivity,
                    onOpen = { onNavigateToLatestBook(latestActivity.kitab.id) }
                )
            }

            AccountMenuSection(
                title = "Aktivitas",
                items = listOf(
                    AccountMenuItem(
                        icon = Icons.Default.BookmarkBorder,
                        label = "Kitab Tersimpan",
                        badge = data.statistik.bookmark.toString(),
                        iconTint = AccountHeaderStart,
                        iconBackground = Color(0xFFF0F7F3),
                        onClick = onNavigateToBookmarks
                    ),
                    AccountMenuItem(
                        icon = Icons.Default.History,
                        label = "Riwayat Baca",
                        badge = data.statistik.kitabDibaca.toString(),
                        iconTint = AccountGold,
                        iconBackground = Color(0xFFFFF8E1),
                        onClick = onNavigateToHistory
                    ),
                    AccountMenuItem(
                        icon = Icons.AutoMirrored.Filled.MenuBook,
                        label = "Lanjut Baca",
                        badge = "",
                        iconTint = Color(0xFF1565C0),
                        iconBackground = Color(0xFFE3F2FD),
                        onClick = {
                            latestActivity?.kitab?.id?.let(onNavigateToLatestBook) ?: onNavigateToHistory()
                        }
                    )
                )
            )

            AccountMenuSection(
                title = "Pengaturan",
                items = listOf(
                    AccountMenuItem(
                        icon = if (isDarkMode) Icons.Default.DarkMode else Icons.Default.LightMode,
                        label = "Mode Gelap",
                        badge = if (isDarkMode) "Aktif" else "Nonaktif",
                        iconTint = Color(0xFF7B1FA2),
                        iconBackground = Color(0xFFF3E5F5),
                        onClick = onDarkModeToggle,
                        trailingContent = {
                            Switch(
                                checked = isDarkMode,
                                onCheckedChange = { onDarkModeToggle() },
                                colors = SwitchDefaults.colors(
                                    checkedThumbColor = Color.White,
                                    checkedTrackColor = AccountHeaderStart
                                )
                            )
                        }
                    ),
                    AccountMenuItem(
                        icon = Icons.Default.Edit,
                        label = "Edit Profil",
                        badge = "",
                        iconTint = Color(0xFF00695C),
                        iconBackground = Color(0xFFE0F2F1),
                        onClick = onNavigateToEditProfile
                    )
                )
            )

            AccountInfoCard(data = data)

            Button(
                onClick = onLogoutClick,
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 16.dp)
                    .height(52.dp),
                colors = ButtonDefaults.buttonColors(containerColor = Color(0xFFFFEBEE)),
                shape = RoundedCornerShape(18.dp),
                contentPadding = PaddingValues(horizontal = 18.dp)
            ) {
                Icon(
                    imageVector = Icons.AutoMirrored.Filled.Logout,
                    contentDescription = null,
                    tint = AccountDanger,
                    modifier = Modifier.size(18.dp)
                )
                Text(
                    text = "Keluar dari Akun",
                    color = AccountDanger,
                    fontSize = 15.sp,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier.padding(start = 8.dp)
                )
            }

            AccountFooter()

            Spacer(modifier = Modifier.height(92.dp))
        }
    }
}

@Composable
private fun AccountHeader(
    data: AccountData,
    onNavigateToEditProfile: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .background(
                Brush.linearGradient(colors = listOf(AccountHeaderStart, AccountHeaderEnd))
            )
            .padding(start = 20.dp, end = 20.dp, top = 26.dp, bottom = 42.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.Top
        ) {
            Row(
                modifier = Modifier.weight(1f),
                horizontalArrangement = Arrangement.spacedBy(14.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Box(
                    modifier = Modifier
                        .size(82.dp)
                        .clip(RoundedCornerShape(24.dp))
                        .background(Brush.linearGradient(colors = listOf(AccountGold, Color(0xFFA67C20)))),
                    contentAlignment = Alignment.Center
                ) {
                    Text(
                        text = data.profile.username.firstOrNull()?.uppercase() ?: "U",
                        color = Color.White,
                        fontSize = 32.sp,
                        fontWeight = FontWeight.ExtraBold
                    )
                }

                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = data.profile.username,
                        color = Color.White,
                        fontSize = 20.sp,
                        fontWeight = FontWeight.ExtraBold
                    )
                    Text(
                        text = data.profile.email,
                        color = Color.White.copy(alpha = 0.78f),
                        fontSize = 13.sp,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis,
                        modifier = Modifier.padding(top = 2.dp)
                    )

                    Row(
                        modifier = Modifier.padding(top = 8.dp),
                        horizontalArrangement = Arrangement.spacedBy(8.dp)
                    ) {
                        HeaderBadge(
                            text = "Santri Digital",
                            background = AccountGold.copy(alpha = 0.24f),
                            color = AccountGold
                        )
                        HeaderBadge(
                            text = "Bergabung ${formatDate(data.profile.bergabungSejak)}",
                            background = Color.White.copy(alpha = 0.12f),
                            color = Color.White.copy(alpha = 0.82f)
                        )
                    }
                }
            }

            IconButton(
                onClick = onNavigateToEditProfile,
                modifier = Modifier
                    .size(38.dp)
                    .clip(RoundedCornerShape(12.dp))
                    .background(Color.White.copy(alpha = 0.15f))
            ) {
                Icon(
                    imageVector = Icons.Default.Edit,
                    contentDescription = "Edit profil",
                    tint = Color.White,
                    modifier = Modifier.size(18.dp)
                )
            }
        }
    }
}

@Composable
private fun HeaderBadge(
    text: String,
    background: Color,
    color: Color
) {
    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(12.dp))
            .background(background)
            .padding(horizontal = 10.dp, vertical = 6.dp)
    ) {
        Text(
            text = text,
            color = color,
            fontSize = 11.sp,
            fontWeight = FontWeight.SemiBold,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis
        )
    }
}

@Composable
private fun AccountStatsCard(data: AccountData) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        shape = RoundedCornerShape(22.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, AccountBorder, RoundedCornerShape(22.dp))
                .padding(vertical = 18.dp, horizontal = 10.dp),
            horizontalArrangement = Arrangement.SpaceEvenly
        ) {
            MiniStat(emoji = "📚", value = data.statistik.kitabDibaca.toString(), label = "Kitab Dibaca")
            MiniStat(emoji = "🔖", value = data.statistik.bookmark.toString(), label = "Disimpan")
            MiniStat(emoji = "💬", value = data.statistik.komentar.toString(), label = "Komentar")
        }
    }
}

@Composable
private fun MiniStat(
    emoji: String,
    value: String,
    label: String
) {
    Column(horizontalAlignment = Alignment.CenterHorizontally) {
        Text(text = emoji, fontSize = 18.sp)
        Text(
            text = value,
            fontSize = 20.sp,
            color = AccountPrimary,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 2.dp)
        )
        Text(
            text = label,
            fontSize = 11.sp,
            color = AccountMuted
        )
    }
}

@Composable
private fun LatestReadingCard(
    activity: AccountHistoryItem,
    onOpen: () -> Unit
) {
    val kitab = activity.kitab ?: return

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(top = 16.dp),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, AccountBorder, RoundedCornerShape(20.dp))
                .padding(16.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Sedang Dibaca",
                    fontSize = 14.sp,
                    color = AccountPrimary,
                    fontWeight = FontWeight.ExtraBold
                )
                Text(
                    text = "Aktivitas terbaru",
                    fontSize = 12.sp,
                    color = AccountHeaderStart,
                    fontWeight = FontWeight.SemiBold
                )
            }

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(top = 12.dp)
                    .clickable(onClick = onOpen),
                verticalAlignment = Alignment.CenterVertically
            ) {
                AsyncImage(
                    model = ApiConfig.getCoverUrl(kitab.cover ?: ""),
                    contentDescription = kitab.judul,
                    contentScale = ContentScale.Crop,
                    modifier = Modifier
                        .width(54.dp)
                        .height(74.dp)
                        .clip(RoundedCornerShape(12.dp))
                )

                Column(
                    modifier = Modifier
                        .weight(1f)
                        .padding(start = 12.dp)
                ) {
                    Text(
                        text = kitab.judul,
                        fontSize = 14.sp,
                        color = AccountPrimary,
                        fontWeight = FontWeight.Bold,
                        maxLines = 2,
                        overflow = TextOverflow.Ellipsis
                    )
                    Text(
                        text = kitab.penulis.orEmpty().ifBlank { "Penulis tidak tersedia" },
                        fontSize = 12.sp,
                        color = AccountSecondary,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis,
                        modifier = Modifier.padding(top = 2.dp)
                    )
                    Text(
                        text = "Terakhir dibuka ${relativeDateLabel(activity.createdAt)}",
                        fontSize = 11.sp,
                        color = AccountMuted,
                        modifier = Modifier.padding(top = 6.dp)
                    )
                }

                Icon(
                    imageVector = Icons.Default.ChevronRight,
                    contentDescription = null,
                    tint = AccountMuted
                )
            }
        }
    }
}

private data class AccountMenuItem(
    val icon: ImageVector,
    val label: String,
    val badge: String,
    val iconTint: Color,
    val iconBackground: Color,
    val onClick: () -> Unit,
    val trailingContent: (@Composable () -> Unit)? = null
)

@Composable
private fun AccountMenuSection(
    title: String,
    items: List<AccountMenuItem>
) {
    Column(modifier = Modifier.padding(top = 18.dp)) {
        Text(
            text = title.uppercase(Locale.getDefault()),
            fontSize = 12.sp,
            color = AccountMuted,
            fontWeight = FontWeight.Bold
        )

        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(top = 8.dp),
            shape = RoundedCornerShape(20.dp),
            colors = CardDefaults.cardColors(containerColor = Color.White)
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .border(1.dp, AccountBorder, RoundedCornerShape(20.dp))
            ) {
                items.forEachIndexed { index, item ->
                    Row(
                        modifier = Modifier
                            .fillMaxWidth()
                            .clickable(onClick = item.onClick)
                            .padding(horizontal = 16.dp, vertical = 14.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Box(
                            modifier = Modifier
                                .size(38.dp)
                                .clip(RoundedCornerShape(12.dp))
                                .background(item.iconBackground),
                            contentAlignment = Alignment.Center
                        ) {
                            Icon(
                                imageVector = item.icon,
                                contentDescription = null,
                                tint = item.iconTint,
                                modifier = Modifier.size(18.dp)
                            )
                        }

                        Text(
                            text = item.label,
                            fontSize = 14.sp,
                            color = AccountPrimary,
                            modifier = Modifier
                                .weight(1f)
                                .padding(start = 12.dp)
                        )

                        when {
                            item.trailingContent != null -> item.trailingContent.invoke()
                            item.badge.isNotBlank() -> {
                                Box(
                                    modifier = Modifier
                                        .clip(RoundedCornerShape(10.dp))
                                        .background(Color(0xFFF0F7F3))
                                        .padding(horizontal = 8.dp, vertical = 4.dp)
                                ) {
                                    Text(
                                        text = item.badge,
                                        fontSize = 11.sp,
                                        color = AccountHeaderStart,
                                        fontWeight = FontWeight.Bold
                                    )
                                }
                                Icon(
                                    imageVector = Icons.Default.ChevronRight,
                                    contentDescription = null,
                                    tint = Color(0xFFC8C0B0),
                                    modifier = Modifier.padding(start = 8.dp)
                                )
                            }
                            else -> {
                                Icon(
                                    imageVector = Icons.Default.ChevronRight,
                                    contentDescription = null,
                                    tint = Color(0xFFC8C0B0)
                                )
                            }
                        }
                    }

                    if (index < items.lastIndex) {
                        HorizontalDivider(color = Color(0xFFF5F0E8))
                    }
                }
            }
        }
    }
}

@Composable
private fun AccountInfoCard(data: AccountData) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(top = 18.dp),
        shape = RoundedCornerShape(20.dp),
        colors = CardDefaults.cardColors(containerColor = Color.White)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .border(1.dp, AccountBorder, RoundedCornerShape(20.dp))
                .padding(16.dp)
        ) {
            Text(
                text = "Informasi Akun",
                fontSize = 15.sp,
                color = AccountPrimary,
                fontWeight = FontWeight.ExtraBold
            )

            Spacer(modifier = Modifier.height(12.dp))

            InfoRow(
                icon = Icons.Default.Person,
                label = "Username",
                value = data.profile.username,
                iconBackground = Color(0xFFF0F7F3),
                iconTint = AccountHeaderStart
            )
            Spacer(modifier = Modifier.height(10.dp))
            InfoRow(
                icon = Icons.Default.Mail,
                label = "Email",
                value = data.profile.email,
                iconBackground = Color(0xFFE3F2FD),
                iconTint = Color(0xFF1565C0)
            )
            Spacer(modifier = Modifier.height(10.dp))
            InfoRow(
                icon = Icons.Default.CalendarToday,
                label = "Bergabung Sejak",
                value = formatDate(data.profile.bergabungSejak),
                iconBackground = Color(0xFFFFF8E1),
                iconTint = AccountGold
            )

            if (!data.profile.deskripsi.isNullOrBlank()) {
                Spacer(modifier = Modifier.height(12.dp))
                Text(
                    text = "Tentang",
                    fontSize = 13.sp,
                    color = AccountMuted,
                    fontWeight = FontWeight.SemiBold
                )
                Text(
                    text = data.profile.deskripsi.orEmpty(),
                    fontSize = 13.sp,
                    color = AccountSecondary,
                    lineHeight = 20.sp,
                    modifier = Modifier.padding(top = 4.dp)
                )
            }
        }
    }
}

@Composable
private fun InfoRow(
    icon: ImageVector,
    label: String,
    value: String,
    iconBackground: Color,
    iconTint: Color
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(16.dp))
            .background(AccountBackground)
            .padding(12.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            modifier = Modifier
                .size(38.dp)
                .clip(RoundedCornerShape(12.dp))
                .background(iconBackground),
            contentAlignment = Alignment.Center
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = iconTint,
                modifier = Modifier.size(18.dp)
            )
        }

        Column(modifier = Modifier.padding(start = 12.dp)) {
            Text(
                text = label,
                fontSize = 11.sp,
                color = AccountMuted
            )
            Text(
                text = value,
                fontSize = 14.sp,
                color = AccountPrimary,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

@Composable
private fun LogoutDialog(
    onDismiss: () -> Unit,
    onConfirm: () -> Unit
) {
    AlertDialog(
        onDismissRequest = onDismiss,
        confirmButton = {
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                TextButton(onClick = onDismiss) {
                    Text("Batal", color = AccountMuted)
                }
                Button(
                    onClick = onConfirm,
                    colors = ButtonDefaults.buttonColors(containerColor = AccountDanger),
                    shape = RoundedCornerShape(14.dp)
                ) {
                    Text("Ya, Keluar", color = Color.White)
                }
            }
        },
        title = {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Box(
                    modifier = Modifier
                        .size(44.dp)
                        .clip(RoundedCornerShape(14.dp))
                        .background(Color(0xFFFFEBEE)),
                    contentAlignment = Alignment.Center
                ) {
                    Icon(
                        imageVector = Icons.Default.WarningAmber,
                        contentDescription = null,
                        tint = AccountDanger
                    )
                }
                Column(modifier = Modifier.padding(start = 12.dp)) {
                    Text("Keluar dari Akun?", fontWeight = FontWeight.ExtraBold, color = AccountPrimary)
                    Text("Kamu perlu masuk kembali nanti", fontSize = 13.sp, color = AccountMuted)
                }
            }
        },
        text = {},
        containerColor = Color.White,
        shape = RoundedCornerShape(24.dp)
    )
}

@Composable
private fun AccountFooter() {
    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(top = 20.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(
            text = "طَلَبُ الْعِلْمِ فَرِيضَةٌ عَلَى كُلِّ مُسْلِمٍ",
            fontSize = 16.sp,
            color = AccountGold
        )
        Text(
            text = "\"Menuntut ilmu adalah kewajiban bagi setiap Muslim\"",
            fontSize = 11.sp,
            color = AccountMuted,
            modifier = Modifier.padding(top = 4.dp)
        )
        Text(
            text = "Al-Kutub Digital Library",
            fontSize = 11.sp,
            color = AccountMuted,
            modifier = Modifier.padding(top = 2.dp)
        )
    }
}

@Composable
private fun AccountErrorView(
    message: String,
    onRetry: () -> Unit
) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal = 28.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Icon(
            imageVector = Icons.Default.ErrorOutline,
            contentDescription = null,
            tint = AccountDanger,
            modifier = Modifier.size(56.dp)
        )
        Text(
            text = "Gagal Memuat Profil",
            fontSize = 18.sp,
            color = AccountPrimary,
            fontWeight = FontWeight.ExtraBold,
            modifier = Modifier.padding(top = 12.dp)
        )
        Text(
            text = message,
            fontSize = 13.sp,
            color = AccountMuted,
            lineHeight = 20.sp,
            textAlign = TextAlign.Center,
            modifier = Modifier.padding(top = 6.dp)
        )
        Button(
            onClick = onRetry,
            modifier = Modifier.padding(top = 20.dp),
            colors = ButtonDefaults.buttonColors(containerColor = AccountHeaderStart),
            shape = RoundedCornerShape(14.dp)
        ) {
            Text("Coba Lagi", color = Color.White, fontWeight = FontWeight.Bold)
        }
    }
}

private fun formatDate(dateString: String): String {
    return try {
        val inputFormat = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
        val outputFormat = SimpleDateFormat("d MMM yyyy", Locale("id", "ID"))
        val date = inputFormat.parse(dateString.take(10))
        date?.let { outputFormat.format(it) } ?: dateString
    } catch (_: Exception) {
        dateString
    }
}

private fun relativeDateLabel(raw: String): String {
    return try {
        val parser = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault())
        val parsed = parser.parse(raw.take(19))
        if (parsed != null) {
            val diffMillis = System.currentTimeMillis() - parsed.time
            val diffHours = diffMillis / (1000 * 60 * 60)
            val diffDays = diffHours / 24
            when {
                diffHours <= 0 -> "baru saja"
                diffHours < 24 -> "$diffHours jam lalu"
                else -> "$diffDays hari lalu"
            }
        } else {
            raw
        }
    } catch (_: Exception) {
        raw
    }
}
