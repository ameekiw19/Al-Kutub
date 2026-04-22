package com.example.al_kutub.model

data class LeaderboardResponse(
    val success: Boolean,
    val message: String,
    val data: LeaderboardData
)

data class LeaderboardData(
    val leaderboard: List<LeaderboardEntry>,
    val user_rank: UserRankInfo
)

data class LeaderboardEntry(
    val user_id: Int,
    val username: String,
    val current_streak: Int,
    val longest_streak: Int
)

data class UserRankInfo(
    val rank: Int?,
    val current_streak: Int,
    val username: String
)

data class AchievementResponse(
    val success: Boolean,
    val message: String,
    val data: AchievementData
)

data class AchievementData(
    val achievements: List<AchievementEntry>,
    val unlocked_count: Int,
    val total_count: Int,
    val completion_percentage: Float
)

data class AchievementEntry(
    val id: String,
    val name: String,
    val description: String,
    val icon: String,
    val unlocked: Boolean,
    val progress: Float
)
