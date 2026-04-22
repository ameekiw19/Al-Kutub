package com.example.al_kutub.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * Deprecated legacy design system.
 * Keep this file for compatibility while the app migrates to SharedColors + MaterialTheme.
 */
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Primary: Color = SharedColors.TealMain
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val PrimaryVariant: Color = SharedColors.TealDark
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Secondary: Color = SharedColors.Slate700
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Tertiary: Color = SharedColors.TealLight

@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Background: Color = SharedColors.White
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Surface: Color = SharedColors.Slate100
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val SurfaceVariant: Color = SharedColors.Slate200
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val OnSurface: Color = SharedColors.Slate900
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val OnSurfaceVariant: Color = SharedColors.Slate600
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val Outline: Color = SharedColors.Slate300

@Deprecated("Use SharedColors.SuccessGreen")
val Success: Color = SharedColors.SuccessGreen
@Deprecated("Use SharedColors.SuccessGreen")
val SuccessContainer: Color = SharedColors.SuccessGreen.copy(alpha = 0.12f)
@Deprecated("Use SharedColors.WarningAmber")
val Warning: Color = SharedColors.WarningAmber
@Deprecated("Use SharedColors.WarningAmber")
val WarningContainer: Color = SharedColors.WarningAmber.copy(alpha = 0.12f)
@Deprecated("Use SharedColors.ErrorRed")
val Error: Color = SharedColors.ErrorRed
@Deprecated("Use SharedColors.ErrorRed")
val ErrorContainer: Color = SharedColors.ErrorRed.copy(alpha = 0.12f)

@Deprecated("Use SharedColors.SuccessGreen")
val Online: Color = SharedColors.SuccessGreen
@Deprecated("Use SharedColors.Slate500")
val Offline: Color = SharedColors.Slate500
@Deprecated("Use SharedColors.ErrorRed")
val New: Color = SharedColors.ErrorRed

@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val ProgressBackground: Color = SharedColors.Slate200
@Deprecated("Use SharedColors + MaterialTheme.colorScheme")
val ProgressActive: Color = SharedColors.TealMain

object AppTypography {
    const val FONT_FAMILY_DEFAULT = "Poppins"
    const val DISPLAY_LARGE_SIZE = 32
    const val DISPLAY_MEDIUM_SIZE = 28
    const val DISPLAY_SMALL_SIZE = 24
    const val HEADLINE_LARGE_SIZE = 24
    const val HEADLINE_MEDIUM_SIZE = 20
    const val HEADLINE_SMALL_SIZE = 18
    const val TITLE_LARGE_SIZE = 18
    const val TITLE_MEDIUM_SIZE = 16
    const val TITLE_SMALL_SIZE = 14
    const val BODY_LARGE_SIZE = 16
    const val BODY_MEDIUM_SIZE = 14
    const val BODY_SMALL_SIZE = 12
    const val LABEL_LARGE_SIZE = 14
    const val LABEL_MEDIUM_SIZE = 12
    const val LABEL_SMALL_SIZE = 10
}

object AppSpacing {
    const val EXTRA_SMALL = 4
    const val SMALL = 8
    const val MEDIUM = 16
    const val LARGE = 24
    const val EXTRA_LARGE = 32
    const val HUGE = 48
}

object AppCornerRadius {
    const val SMALL = 8
    const val MEDIUM = 12
    const val LARGE = 16
    const val EXTRA_LARGE = 24
}

object AppElevation {
    const val NONE = 0
    const val SMALL = 2
    const val MEDIUM = 4
    const val LARGE = 8
    const val EXTRA_LARGE = 16
}

object AppIconSizes {
    const val SMALL = 16
    const val MEDIUM = 24
    const val LARGE = 32
    const val EXTRA_LARGE = 48
}

object AppBreakpoints {
    const val COMPACT_MAX_WIDTH = 600
    const val MEDIUM_MAX_WIDTH = 840
    const val EXPANDED_MAX_WIDTH = 1200
}
