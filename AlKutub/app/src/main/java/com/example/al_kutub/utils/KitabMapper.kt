package com.example.al_kutub.utils

import com.example.al_kutub.model.Kitab
import com.example.al_kutub.model.UiKitab

fun Kitab.toUiKitab(): UiKitab {
    return UiKitab(
        idKitab = idKitab,
        judul = judul,
        penulis = penulis,
        cover = com.example.al_kutub.api.ApiConfig.getCoverUrl(cover),
        kategori = kategori,
        deskripsi = deskripsi,
        bahasa = bahasa,
        views = views,
        downloads = downloads
    )
}
