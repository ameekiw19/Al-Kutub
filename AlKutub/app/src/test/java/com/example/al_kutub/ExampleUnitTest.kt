package com.example.al_kutub
import com.example.al_kutub.utils.ApiErrorMapper

import org.junit.Test

import org.junit.Assert.*

/**
 * Example local unit test, which will execute on the development machine (host).
 *
 * See [testing documentation](http://d.android.com/tools/testing).
 */
class ExampleUnitTest {
    @Test
    fun apiErrorMapper_maps_common_codes() {
        assertTrue(ApiErrorMapper.map(401).contains("AUTH_401"))
        assertTrue(ApiErrorMapper.map(404).contains("NOT_FOUND_404"))
        assertTrue(ApiErrorMapper.map(422).contains("VALIDATION_422"))
        assertTrue(ApiErrorMapper.map(500).contains("SERVER_5XX"))
    }
}
