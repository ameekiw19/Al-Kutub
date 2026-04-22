package com.example.al_kutub.model

data class UiKitab(
    val idKitab: Int,
    val judul: String,
    val penulis: String,
    val kategori: String,
    val deskripsi: String,
    val bahasa: String,
    val cover: String,
    val views: Int,
    val downloads: Int,
    )