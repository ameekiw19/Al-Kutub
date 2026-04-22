package com.example.al_kutub.di

import android.content.Context
import com.example.al_kutub.api.ApiService
import com.example.al_kutub.api.ApiConfig
import com.example.al_kutub.utils.SessionManager
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @Provides
    @Singleton
    fun provideApiService(sessionManager: SessionManager): ApiService {
        return ApiConfig.getApiService(sessionManager)
    }
    
    @Provides
    @Singleton
    fun provideContext(@ApplicationContext context: Context): Context {
        return context
    }
}
