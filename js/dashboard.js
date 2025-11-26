/**
 * Admin Dashboard JavaScript
 * Fetches and displays real-time statistics
 */

$(document).ready(function() {
    // Load statistics on page load
    loadDashboardStats();
    
    // Refresh stats every 30 seconds
    setInterval(loadDashboardStats, 30000);
    
    /**
     * Load all dashboard statistics
     */
    function loadDashboardStats() {
        $.ajax({
            url: '../actions/get_dashboard_stats.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    updateStats(response.data);
                } else {
                    console.error('Failed to load stats:', response.message);
                    // Show placeholders on error
                    $('#totalCategories').text('N/A');
                    $('#totalBrands').text('N/A');
                    $('#totalProducts').text('N/A');
                    $('#totalArtisans').text('N/A');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error loading stats:', error);
                // Show placeholders on error
                $('#totalCategories').text('N/A');
                $('#totalBrands').text('N/A');
                $('#totalProducts').text('N/A');
                $('#totalArtisans').text('N/A');
            }
        });
    }
    
    /**
     * Update statistics display
     */
    function updateStats(stats) {
        // Animate number changes
        animateValue('totalCategories', parseInt($('#totalCategories').text()) || 0, stats.total_categories);
        animateValue('totalBrands', parseInt($('#totalBrands').text()) || 0, stats.total_brands);
        animateValue('totalProducts', parseInt($('#totalProducts').text()) || 0, stats.total_products);
        animateValue('totalArtisans', parseInt($('#totalArtisans').text()) || 0, stats.total_artisans);
    }
    
    /**
     * Animate number counter
     */
    function animateValue(id, start, end) {
        const element = $('#' + id);
        const duration = 1000; // 1 second animation
        const range = end - start;
        const increment = range / (duration / 16); // ~60fps
        let current = start;
        
        const timer = setInterval(function() {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.text(Math.floor(current));
        }, 16);
    }
});