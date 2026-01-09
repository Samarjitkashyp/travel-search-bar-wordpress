(function($) {
    'use strict';
    
    $(document).ready(function() {
        const travelSearchBar = {
            init: function() {
                this.setupAdvancedSearchToggle();
                this.setupQuickSearch();
                this.setupAdvancedSearch();
                this.setupRatingStars();
                this.setupKeyboardShortcuts();
                this.fixNotificationClose(); // ✅ ADDED THIS LINE
            },
            
            setupAdvancedSearchToggle: function() {
                const toggleBtn = $('#toggleAdvancedFiltersBtn');
                const closeBtn = $('#closeAdvancedSearchBtn');
                const container = $('#advancedSearchContainer');
                
                if (toggleBtn.length && container.length) {
                    toggleBtn.on('click', function() {
                        container.addClass('show').show();
                        
                        // Scroll to advanced search
                        $('html, body').animate({
                            scrollTop: container.offset().top - 100
                        }, 800);
                        
                        // Focus on first input
                        container.find('input, select').first().focus();
                        
                        // Update button
                        toggleBtn.find('span').text('Advanced Filters Active');
                        toggleBtn.find('i').removeClass('fa-sliders-h').addClass('fa-check text-success');
                        
                        // Show notification - USING CUSTOM METHOD
                        travelSearchBar.showCustomNotification('Advanced search filters opened', 'info');
                    });
                    
                    closeBtn.on('click', function() {
                        container.removeClass('show');
                        
                        // Scroll back to hero
                        $('html, body').animate({
                            scrollTop: $('.travel-search-bar').offset().top - 100
                        }, 800);
                        
                        // Update button
                        toggleBtn.find('span').text('Show Advanced Search Filters');
                        toggleBtn.find('i').removeClass('fa-check text-success').addClass('fa-sliders-h');
                        
                        // Show notification - USING CUSTOM METHOD
                        travelSearchBar.showCustomNotification('Advanced filters closed', 'info');
                    });
                }
            },
            
            setupQuickSearch: function() {
                $('#travelQuickSearch').on('submit', function(e) {
                    e.preventDefault();
                    
                    const searchInput = $(this).find('input[name="q"]');
                    const searchQuery = searchInput.val().trim();
                    
                    if (!searchQuery) {
                        travelSearchBar.showCustomNotification('Please enter a search term', 'warning');
                        searchInput.focus();
                        return;
                    }
                    
                    // Save to recent searches
                    travelSearchBar.saveRecentSearch(searchQuery);
                    
                    // Submit form
                    this.submit();
                });
            },
            
            setupAdvancedSearch: function() {
                $('#advancedSearchForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    // Collect form data
                    const formData = {
                        location: $('#searchLocation').val(),
                        category: $('#searchCategory').val(),
                        state: $('#searchState').val(),
                        priceRange: $('#priceRange').val(),
                        rating: $('#minRating').val(),
                        sortBy: $('#sortBy').val()
                    };
                    
                    // Show loading
                    const submitBtn = $('#advancedSearchButton');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Searching...').prop('disabled', true);
                    
                    // Show notification
                    travelSearchBar.showCustomNotification('Searching for destinations...', 'info');
                    
                    // AJAX search
                    $.ajax({
                        url: travelSearchBar.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'travel_search',
                            nonce: travelSearchBar.nonce,
                            ...formData
                        },
                        success: function(response) {
                            if (response.success) {
                                travelSearchBar.showCustomNotification(`Found ${response.data.count} results`, 'success');
                                
                                // Redirect to results page with parameters
                                const resultsPage = travelSearchBar.settings.search_results_page;
                                if (resultsPage) {
                                    const params = new URLSearchParams(formData).toString();
                                    window.location.href = resultsPage + '?' + params;
                                } else {
                                    // Display results inline
                                    travelSearchBar.displayResults(response.data.results);
                                }
                            } else {
                                travelSearchBar.showCustomNotification('Search failed. Please try again.', 'error');
                            }
                        },
                        error: function() {
                            travelSearchBar.showCustomNotification('Network error. Please try again.', 'error');
                        },
                        complete: function() {
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    });
                });
                
                // Reset filters
                $('#resetFilters').on('click', function() {
                    if (confirm('Reset all filters?')) {
                        $('#advancedSearchForm')[0].reset();
                        $('#minRating').val(3);
                        $('.rating-star').removeClass('active');
                        $('.rating-star:nth-child(-n+3)').addClass('active');
                        $('#activeFilterCount').text('0 filters active');
                        travelSearchBar.showCustomNotification('All filters reset', 'success');
                    }
                });
                
                // Update filter count
                $('#advancedSearchForm').on('change', function() {
                    const activeCount = $(this).find('select, input').filter(function() {
                        return $(this).val() !== '' && $(this).val() !== '3';
                    }).length;
                    
                    $('#activeFilterCount').text(activeCount + ' filter' + (activeCount !== 1 ? 's' : '') + ' active');
                });
            },
            
            setupRatingStars: function() {
                $('.rating-star').on('click', function() {
                    const rating = $(this).data('rating');
                    $('#minRating').val(rating);
                    
                    // Update stars display
                    $('.rating-star').removeClass('active');
                    $(`.rating-star:nth-child(-n+${rating})`).addClass('active');
                    
                    // Update filter count
                    $('#advancedSearchForm').trigger('change');
                });
                
                // Initialize with default rating
                $('.rating-star:nth-child(-n+3)').addClass('active');
            },
            
            setupKeyboardShortcuts: function() {
                $(document).on('keydown', function(e) {
                    // ESC to close advanced search
                    if (e.key === 'Escape' && $('#advancedSearchContainer').hasClass('show')) {
                        $('#closeAdvancedSearchBtn').click();
                    }
                    
                    // Ctrl/Cmd + F to focus on quick search
                    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                        e.preventDefault();
                        $('#travelQuickSearch input[name="q"]').focus().select();
                    }
                    
                    // Ctrl/Cmd + / to toggle advanced search
                    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
                        e.preventDefault();
                        if ($('#advancedSearchContainer').hasClass('show')) {
                            $('#closeAdvancedSearchBtn').click();
                        } else {
                            $('#toggleAdvancedFiltersBtn').click();
                        }
                    }
                    
                    // ESC to close notification
                    if (e.key === 'Escape' && $('.tsb-custom-notification').is(':visible')) {
                        $('.tsb-custom-notification').remove();
                    }
                });
            },
            
            saveRecentSearch: function(searchTerm) {
                try {
                    const recentSearches = JSON.parse(localStorage.getItem('travelRecentSearches') || '[]');
                    
                    // Remove if already exists
                    const index = recentSearches.indexOf(searchTerm);
                    if (index !== -1) {
                        recentSearches.splice(index, 1);
                    }
                    
                    // Add to beginning
                    recentSearches.unshift(searchTerm);
                    
                    // Keep only last 5 searches
                    if (recentSearches.length > 5) {
                        recentSearches.pop();
                    }
                    
                    localStorage.setItem('travelRecentSearches', JSON.stringify(recentSearches));
                } catch (e) {
                    console.log('Could not save recent search:', e);
                }
            },
            
            displayResults: function(results) {
                // Create results container if it doesn't exist
                if ($('#travelSearchResults').length === 0) {
                    $('.travel-search-bar').append(`
                        <div class="container py-5" id="travelSearchResults">
                            <h2 class="mb-4">Search Results</h2>
                            <div class="row" id="resultsContainer"></div>
                        </div>
                    `);
                }
                
                const container = $('#resultsContainer');
                container.empty();
                
                if (results.length === 0) {
                    container.html(`
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h4><i class="fas fa-info-circle me-2"></i>No Results Found</h4>
                                <p>Try adjusting your search criteria.</p>
                            </div>
                        </div>
                    `);
                    return;
                }
                
                results.forEach(function(result) {
                    const card = `
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card destination-card">
                                <img src="${result.image}" class="card-img-top" alt="${result.name}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title fw-bold">${result.name}</h5>
                                        <span class="text-primary fw-bold">₹${result.price.toLocaleString()}</span>
                                    </div>
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        ${result.state.charAt(0).toUpperCase() + result.state.slice(1)}
                                    </p>
                                    <p class="card-text mb-4">${result.description}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="#" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                        <div>
                                            <small class="text-muted">
                                                <i class="fas fa-star text-warning me-1"></i>
                                                ${result.rating}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(card);
                });
                
                // Scroll to results
                $('html, body').animate({
                    scrollTop: $('#travelSearchResults').offset().top - 100
                }, 800);
            },
            
            // ✅ FIXED: Custom Notification System (Bootstrap Alert se conflict nahi hoga)
            showCustomNotification: function(message, type = 'info') {
                // Remove existing notifications
                $('.tsb-custom-notification').remove();
                
                // Determine icon and color
                let icon = 'info-circle';
                let color = '#3498db';
                let bgColor = '#d1ecf1';
                let borderColor = '#bee5eb';
                
                if (type === 'warning') {
                    icon = 'exclamation-triangle';
                    color = '#856404';
                    bgColor = '#fff3cd';
                    borderColor = '#ffeaa7';
                } else if (type === 'success') {
                    icon = 'check-circle';
                    color = '#155724';
                    bgColor = '#d4edda';
                    borderColor = '#c3e6cb';
                } else if (type === 'error') {
                    icon = 'times-circle';
                    color = '#721c24';
                    bgColor = '#f8d7da';
                    borderColor = '#f5c6cb';
                }
                
                // Create custom notification (NOT using Bootstrap alert classes)
                const notification = $(`
                    <div class="tsb-custom-notification">
                        <div class="tsb-notify-content">
                            <div class="tsb-notify-icon">
                                <i class="fas fa-${icon}"></i>
                            </div>
                            <div class="tsb-notify-message">
                                <div class="tsb-notify-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
                                <div class="tsb-notify-text">${message}</div>
                            </div>
                            <button class="tsb-notify-close">&times;</button>
                        </div>
                    </div>
                `);
                
                // Add custom styles
                notification.css({
                    position: 'fixed',
                    top: '80px',
                    right: '20px',
                    zIndex: '99999',
                    maxWidth: '350px',
                    borderRadius: '8px',
                    overflow: 'hidden',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                    animation: 'tsbSlideInRight 0.3s ease forwards',
                    backgroundColor: bgColor,
                    border: `1px solid ${borderColor}`,
                    borderLeft: `4px solid ${color}`
                });
                
                // Style the content
                notification.find('.tsb-notify-content').css({
                    display: 'flex',
                    alignItems: 'center',
                    padding: '15px',
                    color: color
                });
                
                notification.find('.tsb-notify-icon').css({
                    fontSize: '20px',
                    marginRight: '12px',
                    flexShrink: '0'
                });
                
                notification.find('.tsb-notify-message').css({
                    flexGrow: '1'
                });
                
                notification.find('.tsb-notify-title').css({
                    fontWeight: 'bold',
                    fontSize: '14px',
                    marginBottom: '4px'
                });
                
                notification.find('.tsb-notify-text').css({
                    fontSize: '13px',
                    lineHeight: '1.4'
                });
                
                notification.find('.tsb-notify-close').css({
                    background: 'none',
                    border: 'none',
                    fontSize: '20px',
                    cursor: 'pointer',
                    padding: '0',
                    marginLeft: '10px',
                    color: color,
                    opacity: '0.7',
                    transition: 'opacity 0.2s',
                    lineHeight: '1'
                });
                
                // Hover effect for close button
                notification.find('.tsb-notify-close').hover(
                    function() { $(this).css('opacity', '1'); },
                    function() { $(this).css('opacity', '0.7'); }
                );
                
                // Add to page
                $('body').append(notification);
                
                // Close button functionality
                notification.find('.tsb-notify-close').on('click', function() {
                    notification.css('animation', 'tsbSlideOutRight 0.3s ease forwards');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                });
                
                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (notification.is(':visible')) {
                        notification.css('animation', 'tsbSlideOutRight 0.3s ease forwards');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }
                }, 3000);
                
                // Add CSS animations if not already added
                if (!$('#tsb-notification-styles').length) {
                    $('<style id="tsb-notification-styles">')
                        .text(`
                            @keyframes tsbSlideInRight {
                                from {
                                    opacity: 0;
                                    transform: translateX(100%);
                                }
                                to {
                                    opacity: 1;
                                    transform: translateX(0);
                                }
                            }
                            
                            @keyframes tsbSlideOutRight {
                                from {
                                    opacity: 1;
                                    transform: translateX(0);
                                }
                                to {
                                    opacity: 0;
                                    transform: translateX(100%);
                                }
                            }
                        `)
                        .appendTo('head');
                }
            },
            
            // ✅ NEW: Fix for notification close buttons
            fixNotificationClose: function() {
                // Remove any old notification system
                $('.travel-notification').remove();
                
                // Ensure our custom notification system works
                $(document).on('click', '.tsb-notify-close', function() {
                    $(this).closest('.tsb-custom-notification').css('animation', 'tsbSlideOutRight 0.3s ease forwards');
                    setTimeout(() => {
                        $(this).closest('.tsb-custom-notification').remove();
                    }, 300);
                });
                
                // Click anywhere on notification to close (optional)
                $(document).on('click', '.tsb-custom-notification', function(e) {
                    if (!$(e.target).closest('.tsb-notify-content').length) {
                        $(this).css('animation', 'tsbSlideOutRight 0.3s ease forwards');
                        setTimeout(() => {
                            $(this).remove();
                        }, 300);
                    }
                });
                
                // Escape key to close notification
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && $('.tsb-custom-notification').length) {
                        $('.tsb-custom-notification').each(function() {
                            $(this).css('animation', 'tsbSlideOutRight 0.3s ease forwards');
                            setTimeout(() => {
                                $(this).remove();
                            }, 300);
                        });
                    }
                });
            }
        };
        
        // Initialize
        travelSearchBar.init();
    });
})(jQuery);