package com.example.al_kutub.model

data class ReadingStats(
    val totalBooksRead: Int = 0,
    val totalPagesRead: Int = 0,
    val averagePagesPerDay: Float = 0f,
    val daysActive: Int = 0,
    val thisMonthBooks: Int = 0,
    val currentStreak: Int = 0,
    val longestStreak: Int = 0,
    val monthlyData: Map<String, Int> = emptyMap(),
    val categoryData: Map<String, Int> = emptyMap(),
    val readingHabits: Map<String, String> = emptyMap(),
    val achievements: List<String> = emptyList()
)
