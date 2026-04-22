package com.example.al_kutub.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.Button
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.FilterChip
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.ModalBottomSheet
import androidx.compose.material3.RadioButton
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.material3.rememberModalBottomSheetState
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.example.al_kutub.model.SearchFilter
import com.example.al_kutub.model.SortOption
import com.example.al_kutub.model.SortOrder

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AdvancedSearchFilterSheet(
    filter: SearchFilter,
    onFilterChange: (SearchFilter) -> Unit,
    onApply: () -> Unit,
    onReset: () -> Unit,
    onDismiss: () -> Unit,
    availableCategories: List<String> = emptyList(),
    availableAuthors: List<String> = emptyList(),
    availableLanguages: List<String> = emptyList()
) {
    var localFilter by remember(filter) { mutableStateOf(filter) }

    ModalBottomSheet(
        onDismissRequest = onDismiss,
        sheetState = rememberModalBottomSheetState(skipPartiallyExpanded = true)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp)
                .navigationBarsPadding()
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = "Filter Pencarian",
                    style = MaterialTheme.typography.headlineSmall,
                    fontWeight = FontWeight.Bold
                )

                Row(verticalAlignment = Alignment.CenterVertically) {
                    TextButton(
                        onClick = {
                            localFilter = filter.copy(
                                categories = emptyList(),
                                authors = emptyList(),
                                languages = emptyList(),
                                sortBy = SortOption.RELEVANCE,
                                sortOrder = SortOrder.DESC
                            )
                            onReset()
                        }
                    ) {
                        Text("Reset")
                    }

                    Spacer(modifier = Modifier.padding(horizontal = 4.dp))

                    Button(
                        onClick = {
                            onFilterChange(localFilter)
                            onApply()
                            onDismiss()
                        }
                    ) {
                        Text("Terapkan")
                    }
                }
            }

            Spacer(modifier = Modifier.height(16.dp))

            LazyColumn(verticalArrangement = Arrangement.spacedBy(16.dp)) {
                if (availableCategories.isNotEmpty()) {
                    item {
                        FilterSection(title = "Kategori") {
                            FilterChipRow(
                                options = availableCategories,
                                selectedOptions = localFilter.categories,
                                onSelectionChange = { selected ->
                                    localFilter = localFilter.copy(categories = selected)
                                }
                            )
                        }
                    }
                }

                if (availableAuthors.isNotEmpty()) {
                    item {
                        FilterSection(title = "Penulis") {
                            FilterChipRow(
                                options = availableAuthors,
                                selectedOptions = localFilter.authors,
                                onSelectionChange = { selected ->
                                    localFilter = localFilter.copy(authors = selected)
                                }
                            )
                        }
                    }
                }

                if (availableLanguages.isNotEmpty()) {
                    item {
                        FilterSection(title = "Bahasa") {
                            FilterChipRow(
                                options = availableLanguages,
                                selectedOptions = localFilter.languages,
                                onSelectionChange = { selected ->
                                    localFilter = localFilter.copy(languages = selected)
                                }
                            )
                        }
                    }
                }

                item {
                    FilterSection(title = "Urutkan") {
                        LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            items(SortOption.entries.toList()) { option ->
                                FilterChip(
                                    selected = localFilter.sortBy == option,
                                    onClick = { localFilter = localFilter.copy(sortBy = option) },
                                    label = { Text(option.label) }
                                )
                            }
                        }

                        Spacer(modifier = Modifier.height(12.dp))

                        Row(
                            horizontalArrangement = Arrangement.spacedBy(16.dp),
                            verticalAlignment = Alignment.CenterVertically
                        ) {
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                RadioButton(
                                    selected = localFilter.sortOrder == SortOrder.DESC,
                                    onClick = { localFilter = localFilter.copy(sortOrder = SortOrder.DESC) }
                                )
                                Text("Desc")
                            }

                            Row(verticalAlignment = Alignment.CenterVertically) {
                                RadioButton(
                                    selected = localFilter.sortOrder == SortOrder.ASC,
                                    onClick = { localFilter = localFilter.copy(sortOrder = SortOrder.ASC) }
                                )
                                Text("Asc")
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun FilterSection(
    title: String,
    content: @Composable () -> Unit
) {
    Column {
        Text(
            text = title,
            style = MaterialTheme.typography.titleSmall,
            fontWeight = FontWeight.Medium,
            modifier = Modifier.padding(bottom = 8.dp)
        )
        content()
    }
}

@Composable
private fun FilterChipRow(
    options: List<String>,
    selectedOptions: List<String>,
    onSelectionChange: (List<String>) -> Unit
) {
    LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
        items(options) { option ->
            FilterChip(
                selected = selectedOptions.contains(option),
                onClick = {
                    val nextSelection = if (selectedOptions.contains(option)) {
                        selectedOptions - option
                    } else {
                        selectedOptions + option
                    }
                    onSelectionChange(nextSelection)
                },
                label = { Text(option) }
            )
        }
    }
}
