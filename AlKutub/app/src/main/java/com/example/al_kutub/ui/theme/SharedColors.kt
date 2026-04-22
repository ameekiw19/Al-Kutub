package com.example.al_kutub.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * Shared color constants for consistency between Laravel backend and Android app
 * Based on Teal primary colors and Slate neutral colors
 */
object SharedColors {
    
    // Primary Teal Colors (matching Laravel TealMain)
    val TealMain = Color(0xFF44A194)
    val TealLight = Color(0xFF76D3C6)
    val TealDark = Color(0xFF007265)
    val TealBackground = Color(0xFFE0F2F1)
    
    // Slate Neutral Colors (matching Laravel Slate palette)
    val Slate900 = Color(0xFF111111)  // Primary Text (Light)
    val Slate800 = Color(0xFF1E293B)  // Dark Header
    val Slate700 = Color(0xFF334155)  // Secondary Text (Light)
    val Slate600 = Color(0xFF666666)  // Secondary Text (Dark)
    val Slate500 = Color(0xFFBBBBBB)  // Outline
    val Slate400 = Color(0xFF94A3B8)  // Outline Variant
    val Slate300 = Color(0xFFCBD5E1)  // Surface (Light)
    val Slate200 = Color(0xFFE2E8F0)  // Surface (Dark)
    val Slate100 = Color(0xFFF8F9FA)  // Card Background (Light)
    val Slate50 = Color(0xFFF8F9FA)  // App Background (Light)
    
    // Background Colors
    val White = Color(0xFFFFFFFF)  // Text Primary (Dark)
    val Black = Color(0xFF000000)  // Background (Dark)
    
    // Functional Colors
    val ErrorRed = Color(0xFFEF4444)
    val SuccessGreen = Color(0xFF22C55E)
    val WarningAmber = Color(0xFFF59E0B)
    
    // Dark Mode Specifics
    val DarkBackground = Color(0xFF000000)  // Pure Black for Dark Mode
    val DarkSurface = Color(0xFF121212)  // Slightly lighter for cards in Dark Mode
    val DarkSurfaceVariant = Color(0xFF666666)  // Secondary text in Dark Mode
}
