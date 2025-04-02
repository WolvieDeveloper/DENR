<!-- Add this right after the card-header div in the Filter Options card -->
<div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Filter Options</h5>
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="reportFormatDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-file-export me-1"></i> Export As
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportFormatDropdown">
            <li><a class="dropdown-item" href="#" data-format="csv"><i class="fas fa-file-csv me-2"></i> CSV</a></li>
            <li><a class="dropdown-item" href="#" data-format="excel"><i class="fas fa-file-excel me-2"></i> Excel</a></li>
            <li><a class="dropdown-item" href="#" data-format="pdf"><i class="fas fa-file-pdf me-2"></i> PDF</a></li>
        </ul>
    </div>
</div>

<!-- Replace the Export button in the page-header div with this -->
<div>
    <button class="btn btn-outline-primary me-2" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Print
    </button>
    <button class="btn btn-primary" id="generateReportBtn" data-bs-toggle="modal" data-bs-target="#reportModal">
        <i class="fas fa-chart-bar me-1"></i> Generate Report
    </button>
</div>

<!-- Add this modal at the end of the body content but before the closing body tag -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="reportModalLabel"><i class="fas fa-file-alt me-2"></i>Generate Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm" method="GET" action="export-completed.php">
                    <!-- Hidden inputs to carry over current filters -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filterBy); ?>">
                    <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
                    <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
                    <input type="hidden" name="format" value="pdf" id="reportFormat">
                    
                    <div class="mb-3">
                        <label class="form-label">Report Format</label>
                        <div class="d-flex gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format_option" id="formatPDF" value="pdf" checked>
                                <label class="form-check-label" for="formatPDF">
                                    <i class="fas fa-file-pdf text-danger me-1"></i> PDF
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format_option" id="formatExcel" value="excel">
                                <label class="form-check-label" for="formatExcel">
                                    <i class="fas fa-file-excel text-success me-1"></i> Excel
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="format_option" id="formatCSV" value="csv">
                                <label class="form-check-label" for="formatCSV">
                                    <i class="fas fa-file-csv text-primary me-1"></i> CSV
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reportTitle" class="form-label">Report Title (optional)</label>
                        <input type="text" class="form-control" id="reportTitle" name="title" placeholder="DENR Completed Requests Report">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Include in Report</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeSummary" name="include_summary" value="1" checked>
                            <label class="form-check-label" for="includeSummary">
                                Statistical Summary
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeCharts" name="include_charts" value="1" checked>
                            <label class="form-check-label" for="includeCharts">
                                Visual Charts
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includeFilters" name="include_filters" value="1" checked>
                            <label class="form-check-label" for="includeFilters">
                                Applied Filters
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitReportBtn">Generate Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript at the end of the file, right before the closing body tag -->
<script>
    // Update the existing export functionality
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Default to CSV format when using the quick export button
        window.location.href = 'export-completed.php<?php 
            $params = ['format=csv'];
            if (!empty($searchTerm)) $params[] = 'search=' . urlencode($searchTerm);
            if (!empty($filterBy)) $params[] = 'filter=' . urlencode($filterBy);
            if (!empty($dateFrom)) $params[] = 'date_from=' . urlencode($dateFrom);
            if (!empty($dateTo)) $params[] = 'date_to=' . urlencode($dateTo);
            echo '?' . implode('&', $params);
        ?>';
    });
    
    // Format selector in dropdown
    document.querySelectorAll('.dropdown-item[data-format]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const format = this.dataset.format;
            window.location.href = 'export-completed.php<?php 
                $params = [];
                if (!empty($searchTerm)) $params[] = 'search=' . urlencode($searchTerm);
                if (!empty($filterBy)) $params[] = 'filter=' . urlencode($filterBy);
                if (!empty($dateFrom)) $params[] = 'date_from=' . urlencode($dateFrom);
                if (!empty($dateTo)) $params[] = 'date_to=' . urlencode($dateTo);
                echo !empty($params) ? '?' . implode('&', $params) . '&' : '?';
            ?>format=' + format;
        });
    });
    
    // Report modal format selection
    document.querySelectorAll('input[name="format_option"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('reportFormat').value = this.value;
        });
    });
    
    // Submit report form
    document.getElementById('submitReportBtn').addEventListener('click', function() {
        document.getElementById('reportForm').submit();
    });
</script>