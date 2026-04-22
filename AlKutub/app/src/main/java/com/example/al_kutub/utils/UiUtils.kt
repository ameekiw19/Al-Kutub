package com.example.al_kutub.utils

fun formatViews(views: Int): String {
    return if (views >= 1000) {
        "${(views / 1000.0).let { "%.1f".format(it) }}k"
    } else {
        views.toString()
    }
}
