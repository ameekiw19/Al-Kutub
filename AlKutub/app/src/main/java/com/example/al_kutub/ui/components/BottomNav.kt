package com.example.al_kutub.ui.components

import androidx.compose.animation.AnimatedVisibility
import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.Spring
import androidx.compose.animation.core.animateDpAsState
import androidx.compose.animation.core.spring
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.widthIn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.LibraryBooks
import androidx.compose.material.icons.filled.Bookmark
import androidx.compose.material.icons.filled.History
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.HorizontalDivider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import com.example.al_kutub.ui.theme.SharedColors

enum class BottomTab(val id: String, val label: String, val icon: ImageVector) {
    HOME("home", "Beranda", Icons.Default.Home),
    KATALOG("katalog", "Katalog", Icons.AutoMirrored.Filled.LibraryBooks),
    RIWAYAT("riwayat", "Riwayat", Icons.Default.History),
    BOOKMARK("bookmark", "Bookmark", Icons.Default.Bookmark),
    AKUN("akun", "Akun", Icons.Default.Person)
}

@Composable
fun BottomNav(
    activeTab: BottomTab,
    onTabChange: (BottomTab) -> Unit
) {
    Box(
        modifier = Modifier
            .fillMaxWidth()
            .navigationBarsPadding()
            .padding(horizontal = 16.dp, vertical = 10.dp),
        contentAlignment = Alignment.Center
    ) {
        Surface(
            modifier = Modifier
                .fillMaxWidth()
                .shadow(
                    elevation = 18.dp,
                    shape = RoundedCornerShape(26.dp),
                    ambientColor = SharedColors.Slate900.copy(alpha = 0.12f),
                    spotColor = SharedColors.Slate900.copy(alpha = 0.16f)
                ),
            shape = RoundedCornerShape(26.dp),
            color = SharedColors.White,
            tonalElevation = 0.dp
        ) {
            Column(
                modifier = Modifier
                    .border(
                        width = 1.dp,
                        color = SharedColors.Slate200,
                        shape = RoundedCornerShape(26.dp)
                    )
                    .background(SharedColors.White)
                    .padding(horizontal = 10.dp, vertical = 8.dp)
            ) {
                HorizontalDivider(
                    modifier = Modifier
                        .fillMaxWidth()
                        .padding(horizontal = 8.dp),
                    thickness = 1.dp,
                    color = Color.Transparent
                )

                Row(
                    modifier = Modifier
                        .fillMaxWidth()
                        .height(64.dp),
                    horizontalArrangement = Arrangement.spacedBy(6.dp),
                    verticalAlignment = Alignment.CenterVertically
                ) {
                    BottomTab.values().forEach { tab ->
                        BottomNavItem(
                            modifier = Modifier.weight(1f),
                            tab = tab,
                            isActive = activeTab == tab,
                            onClick = { onTabChange(tab) }
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun BottomNavItem(
    modifier: Modifier = Modifier,
    tab: BottomTab,
    isActive: Boolean,
    onClick: () -> Unit
) {
    val interactionSource = remember { MutableInteractionSource() }
    val iconColor by animateColorAsState(
        targetValue = if (isActive) SharedColors.TealDark else SharedColors.Slate600,
        animationSpec = spring(stiffness = Spring.StiffnessLow),
        label = "bottomNavIconColor"
    )
    val labelColor by animateColorAsState(
        targetValue = if (isActive) SharedColors.TealDark else SharedColors.Slate600,
        animationSpec = spring(stiffness = Spring.StiffnessLow),
        label = "bottomNavLabelColor"
    )
    val containerColor by animateColorAsState(
        targetValue = if (isActive) SharedColors.TealBackground else Color.Transparent,
        animationSpec = spring(stiffness = Spring.StiffnessLow),
        label = "bottomNavContainerColor"
    )
    val borderColor by animateColorAsState(
        targetValue = if (isActive) SharedColors.TealLight.copy(alpha = 0.55f) else Color.Transparent,
        animationSpec = spring(stiffness = Spring.StiffnessLow),
        label = "bottomNavBorderColor"
    )
    val verticalPadding by animateDpAsState(
        targetValue = if (isActive) 12.dp else 10.dp,
        animationSpec = spring(stiffness = Spring.StiffnessMediumLow),
        label = "bottomNavVerticalPadding"
    )

    Box(
        modifier = modifier
            .clip(RoundedCornerShape(18.dp))
            .background(containerColor)
            .border(1.dp, borderColor, RoundedCornerShape(18.dp))
            .clickable(
                interactionSource = interactionSource,
                indication = null,
                onClick = onClick
            )
            .padding(horizontal = 8.dp, vertical = verticalPadding),
        contentAlignment = Alignment.Center
    ) {
        Column(
            modifier = Modifier.widthIn(min = 44.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            Icon(
                imageVector = tab.icon,
                contentDescription = tab.label,
                tint = iconColor,
                modifier = Modifier.size(if (isActive) 22.dp else 20.dp)
            )

            AnimatedVisibility(visible = true) {
                Text(
                    text = tab.label,
                    style = MaterialTheme.typography.labelSmall.copy(
                        fontSize = 10.sp,
                        fontWeight = if (isActive) FontWeight.SemiBold else FontWeight.Medium,
                        color = labelColor
                    ),
                    modifier = Modifier.padding(top = 4.dp)
                )
            }
        }
    }
}
