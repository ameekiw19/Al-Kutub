package com.example.al_kutub.model

import java.time.LocalDate

data class ReadingGoal(
    val id: String,
    val title: String,
    val targetBooks: Int,
    val completedBooks: Int = 0,
    val deadline: LocalDate,
    val createdAt: LocalDate = LocalDate.now(),
    val isCompleted: Boolean = false,
    val goalType: String = "custom",
    val targetMinutes: Int = 0,
    val completedMinutes: Int = 0
) {
    fun progressPercentage(): Float {
        val pageProgress = if (targetBooks > 0) {
            (completedBooks.toFloat() / targetBooks.toFloat()).coerceAtMost(1f)
        } else {
            0f
        }
        val minuteProgress = if (targetMinutes > 0) {
            (completedMinutes.toFloat() / targetMinutes.toFloat()).coerceAtMost(1f)
        } else {
            0f
        }

        return when {
            targetBooks > 0 && targetMinutes > 0 -> ((pageProgress + minuteProgress) / 2f).coerceAtMost(1f)
            targetBooks > 0 -> pageProgress
            targetMinutes > 0 -> minuteProgress
            else -> 0f
        }
    }

    fun isOverdue(): Boolean {
        return !isCompleted && deadline.isBefore(LocalDate.now())
    }

    fun daysRemaining(): Int {
        return if (isCompleted) 0 else {
            (deadline.toEpochDay() - LocalDate.now().toEpochDay()).toInt().coerceAtLeast(0)
        }
    }
}
