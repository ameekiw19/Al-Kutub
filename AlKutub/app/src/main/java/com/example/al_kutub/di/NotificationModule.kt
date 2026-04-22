package com.example.al_kutub.di

import com.example.al_kutub.data.repository.NotificationRepository
import com.example.al_kutub.utils.SessionManager
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NotificationModule {
    
    @Provides
    @Singleton
    fun provideNotificationRepository(sessionManager: SessionManager): NotificationRepository {
        return NotificationRepository(sessionManager)
    }
}
