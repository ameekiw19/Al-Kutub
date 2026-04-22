package com.example.al_kutub.model

import com.google.gson.annotations.SerializedName

data class ReadingGoalsResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: ReadingGoalsData
)

data class ReadingGoalsData(
    @SerializedName("daily_goal")
    val dailyGoal: ReadingGoalApiItem,
    @SerializedName("weekly_goal")
    val weeklyGoal: ReadingGoalApiItem,
    @SerializedName("statistics")
    val statistics: ReadingGoalSummary
)

data class ReadingGoalApiItem(
    @SerializedName("id")
    val id: Int,
    @SerializedName("type")
    val type: String,
    @SerializedName("target_minutes")
    val targetMinutes: Int,
    @SerializedName("target_pages")
    val targetPages: Int,
    @SerializedName("current_minutes")
    val currentMinutes: Int,
    @SerializedName("current_pages")
    val currentPages: Int,
    @SerializedName("minutes_progress")
    val minutesProgress: Float,
    @SerializedName("pages_progress")
    val pagesProgress: Float,
    @SerializedName("overall_progress")
    val overallProgress: Float,
    @SerializedName("is_completed")
    val isCompleted: Boolean,
    @SerializedName("start_date")
    val startDate: String,
    @SerializedName("end_date")
    val endDate: String? = null
)

data class ReadingGoalSummary(
    @SerializedName("total_goals")
    val totalGoals: Int,
    @SerializedName("completed_goals")
    val completedGoals: Int,
    @SerializedName("completion_rate")
    val completionRate: Float
)

data class ReadingStreakResponse(
    @SerializedName("success")
    val success: Boolean,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: ReadingStreakData
)

data class ReadingStreakData(
    @SerializedName("current_streak")
    val currentStreak: Int,
    @SerializedName("longest_streak")
    val longestStreak: Int,
    @SerializedName("total_days")
    val totalDays: Int,
    @SerializedName("has_read_today")
    val hasReadToday: Boolean,
    @SerializedName("status_message")
    val statusMessage: String
)
