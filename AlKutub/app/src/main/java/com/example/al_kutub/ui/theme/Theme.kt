package com.example.al_kutub.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat
import androidx.compose.ui.graphics.luminance
import com.example.al_kutub.ui.theme.SharedColors

/**
 * Enhanced theme system with shared colors for consistency with Laravel backend
 */
private val DarkColorScheme = darkColorScheme(
    primary = SharedColors.TealMain,
    onPrimary = SharedColors.White,
    primaryContainer = SharedColors.TealDark,
    onPrimaryContainer = SharedColors.TealBackground,
    secondary = SharedColors.Slate600,
    onSecondary = SharedColors.White,
    secondaryContainer = SharedColors.Slate700,
    onSecondaryContainer = SharedColors.Slate300,
    tertiary = SharedColors.TealLight,
    onTertiary = SharedColors.Black,
    tertiaryContainer = SharedColors.TealBackground,
    onTertiaryContainer = SharedColors.TealDark,
    background = SharedColors.Black,
    onBackground = SharedColors.White,
    surface = SharedColors.DarkSurface,
    onSurface = SharedColors.White,
    surfaceVariant = SharedColors.Slate800,
    onSurfaceVariant = SharedColors.Slate300,
    outline = SharedColors.Slate700,
    error = SharedColors.ErrorRed,
    onError = SharedColors.White,
    errorContainer = SharedColors.ErrorRed,
    onErrorContainer = SharedColors.White
)

private val LightColorScheme = lightColorScheme(
    primary = SharedColors.TealMain,
    onPrimary = SharedColors.White,
    primaryContainer = SharedColors.TealLight,
    onPrimaryContainer = SharedColors.TealDark,
    secondary = SharedColors.Slate700,
    onSecondary = SharedColors.White,
    secondaryContainer = SharedColors.Slate300,
    onSecondaryContainer = SharedColors.Slate600,
    tertiary = SharedColors.TealLight,
    onTertiary = SharedColors.Black,
    tertiaryContainer = SharedColors.TealBackground,
    onTertiaryContainer = SharedColors.TealDark,
    background = SharedColors.White,
    onBackground = SharedColors.Slate50,
    surface = SharedColors.Slate100,
    onSurface = SharedColors.Slate900,
    surfaceVariant = SharedColors.Slate800,
    onSurfaceVariant = SharedColors.Slate600,
    outline = SharedColors.Slate200,
    error = SharedColors.ErrorRed,
    onError = SharedColors.White,
    errorContainer = SharedColors.ErrorRed,
    onErrorContainer = SharedColors.White
)

@Composable
fun AlKutubTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit
) {
    val colors = if (darkTheme) DarkColorScheme else LightColorScheme

    MaterialTheme(
        colorScheme = colors,
        typography = Typography
    ) {
        content()
    }
}

@Composable
@Suppress("DEPRECATION")
fun SetStatusBarColor(
    statusBarColor: Color,
    darkTheme: Boolean = isSystemInDarkTheme()
) {
    val view = LocalView.current
    val window = (view.context as? Activity)?.window
    window?.let { window ->
        window.statusBarColor = statusBarColor.toArgb()
        WindowCompat.getInsetsController(window, view).isAppearanceLightStatusBars = !darkTheme
    }
}

@Composable
fun GetBackgroundColor(
    darkTheme: Boolean = isSystemInDarkTheme()
): Color {
    return if (darkTheme) SharedColors.DarkBackground else SharedColors.White
}
