<?php
/**
 * Advanced Filters Template
 * This file contains the full advanced filters interface
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get settings
$settings = get_option('travel_search_bar_settings');
$states = is_array($settings['filter_states'] ?? '') ? $settings['filter_states'] : explode("\n", $settings['filter_states'] ?? '');
$categories = is_array($settings['filter_categories'] ?? '') ? $settings['filter_categories'] : explode("\n", $settings['filter_categories'] ?? '');
?>

<div class="advanced-search-form-wrapper">
    <!-- Advanced Search Form -->
    <form id="advancedSearchForm" class="travel-advanced-search-form" method="GET" action="<?php echo esc_url(get_permalink($settings['search_results_page'] ?? '')); ?>">
        
        <!-- Search Type Tabs -->
        <div class="search-type-tabs mb-4">
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="search_type" value="destination" checked>
                    <i class="fas fa-map-marker-alt me-2"></i>Destinations
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="search_type" value="hotel">
                    <i class="fas fa-hotel me-2"></i>Hotels
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="search_type" value="package">
                    <i class="fas fa-suitcase-rolling me-2"></i>Packages
                </label>
            </div>
        </div>

        <!-- Main Search Fields -->
        <div class="row g-3 mb-4">
            <!-- Location Search -->
            <div class="col-md-6 col-lg-4">
                <div class="form-group">
                    <label for="advLocation" class="form-label">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <?php esc_html_e('Location / Destination', 'travel-search-bar'); ?>
                    </label>
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="advLocation"
                               name="location"
                               placeholder="<?php esc_attr_e('Enter city, state, or destination', 'travel-search-bar'); ?>"
                               aria-label="Location">
                        <span class="input-group-text">
                            <i class="fas fa-crosshairs"></i>
                        </span>
                    </div>
                    <small class="form-text text-muted">
                        <?php esc_html_e('E.g., Guwahati, Goa, Kerala', 'travel-search-bar'); ?>
                    </small>
                </div>
            </div>

            <!-- Category Selector -->
            <div class="col-md-6 col-lg-3">
                <div class="form-group">
                    <label for="advCategory" class="form-label">
                        <i class="fas fa-tags text-primary me-2"></i>
                        <?php esc_html_e('Category', 'travel-search-bar'); ?>
                    </label>
                    <select class="form-control" 
                            id="advCategory" 
                            name="category"
                            aria-label="Category">
                        <option value=""><?php esc_html_e('All Categories', 'travel-search-bar'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <?php if (!empty(trim($category))): ?>
                                <option value="<?php echo esc_attr(trim($category)); ?>">
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', trim($category)))); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- State Selector -->
            <div class="col-md-6 col-lg-3">
                <div class="form-group">
                    <label for="advState" class="form-label">
                        <i class="fas fa-globe-asia text-primary me-2"></i>
                        <?php esc_html_e('State / Region', 'travel-search-bar'); ?>
                    </label>
                    <select class="form-control" 
                            id="advState" 
                            name="state"
                            aria-label="State">
                        <option value=""><?php esc_html_e('All States', 'travel-search-bar'); ?></option>
                        <?php foreach ($states as $state): ?>
                            <?php if (!empty(trim($state))): ?>
                                <option value="<?php echo esc_attr(trim($state)); ?>">
                                    <?php echo esc_html(ucwords(str_replace('_', ' ', trim($state)))); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Travel Dates -->
            <div class="col-md-6 col-lg-2">
                <div class="form-group">
                    <label for="advCheckIn" class="form-label">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <?php esc_html_e('Check-in Date', 'travel-search-bar'); ?>
                    </label>
                    <input type="date" 
                           class="form-control" 
                           id="advCheckIn"
                           name="check_in"
                           min="<?php echo esc_attr(date('Y-m-d')); ?>"
                           aria-label="Check-in Date">
                </div>
            </div>
        </div>

        <!-- Advanced Filters Section -->
        <div class="advanced-filters-section mb-4">
            <h5 class="mb-3 text-primary">
                <i class="fas fa-sliders-h me-2"></i>
                <?php esc_html_e('Advanced Filters', 'travel-search-bar'); ?>
            </h5>
            
            <div class="row g-3">
                <!-- Price Range -->
                <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-rupee-sign text-primary me-2"></i>
                            <?php esc_html_e('Price Range (₹)', 'travel-search-bar'); ?>
                        </label>
                        <div class="row align-items-center">
                            <div class="col-5">
                                <input type="number" 
                                       class="form-control" 
                                       id="minPrice"
                                       name="min_price"
                                       placeholder="Min"
                                       min="0"
                                       step="500"
                                       aria-label="Minimum price">
                            </div>
                            <div class="col-2 text-center">-</div>
                            <div class="col-5">
                                <input type="number" 
                                       class="form-control" 
                                       id="maxPrice"
                                       name="max_price"
                                       placeholder="Max"
                                       min="0"
                                       step="500"
                                       value="10000"
                                       aria-label="Maximum price">
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="range" 
                                   class="form-range" 
                                   id="priceSlider"
                                   min="0" 
                                   max="50000" 
                                   step="500"
                                   value="10000"
                                   aria-label="Price range slider">
                        </div>
                        <small class="form-text text-muted" id="priceRangeDisplay">
                            <?php esc_html_e('Range: ₹0 - ₹10,000', 'travel-search-bar'); ?>
                        </small>
                    </div>
                </div>

                <!-- Rating Filter -->
                <div class="col-md-6 col-lg-3">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-star text-primary me-2"></i>
                            <?php esc_html_e('Minimum Rating', 'travel-search-bar'); ?>
                        </label>
                        <div class="rating-input mb-2">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" 
                                            class="rating-star-btn" 
                                            data-value="<?php echo $i; ?>"
                                            aria-label="Rate <?php echo $i; ?> stars">
                                        <i class="far fa-star"></i>
                                    </button>
                                <?php endfor; ?>
                                <input type="hidden" name="min_rating" id="selectedRating" value="0">
                            </div>
                        </div>
                        <div class="form-text">
                            <?php esc_html_e('Select minimum star rating', 'travel-search-bar'); ?>
                        </div>
                    </div>
                </div>

                <!-- Sort Options -->
                <div class="col-md-6 col-lg-3">
                    <div class="form-group">
                        <label for="advSortBy" class="form-label">
                            <i class="fas fa-sort-amount-down text-primary me-2"></i>
                            <?php esc_html_e('Sort By', 'travel-search-bar'); ?>
                        </label>
                        <select class="form-control" 
                                id="advSortBy" 
                                name="sort_by"
                                aria-label="Sort by">
                            <option value="relevance"><?php esc_html_e('Relevance', 'travel-search-bar'); ?></option>
                            <option value="price_low"><?php esc_html_e('Price: Low to High', 'travel-search-bar'); ?></option>
                            <option value="price_high"><?php esc_html_e('Price: High to Low', 'travel-search-bar'); ?></option>
                            <option value="rating"><?php esc_html_e('Rating', 'travel-search-bar'); ?></option>
                            <option value="popularity"><?php esc_html_e('Popularity', 'travel-search-bar'); ?></option>
                            <option value="distance"><?php esc_html_e('Distance', 'travel-search-bar'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Guest Count -->
                <div class="col-md-6 col-lg-2">
                    <div class="form-group">
                        <label for="advGuests" class="form-label">
                            <i class="fas fa-users text-primary me-2"></i>
                            <?php esc_html_e('Guests', 'travel-search-bar'); ?>
                        </label>
                        <div class="input-group">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="decreaseGuests"
                                    aria-label="Decrease guest count">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   class="form-control text-center" 
                                   id="advGuests"
                                   name="guests"
                                   value="2"
                                   min="1"
                                   max="20"
                                   readonly
                                   aria-label="Number of guests">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    id="increaseGuests"
                                    aria-label="Increase guest count">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities Section -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label mb-2">
                            <i class="fas fa-concierge-bell text-primary me-2"></i>
                            <?php esc_html_e('Amenities', 'travel-search-bar'); ?>
                        </label>
                        <div class="amenities-grid">
                            <?php 
                            $amenities = [
                                'wifi' => ['icon' => 'wifi', 'label' => __('Free WiFi', 'travel-search-bar')],
                                'pool' => ['icon' => 'swimming-pool', 'label' => __('Swimming Pool', 'travel-search-bar')],
                                'spa' => ['icon' => 'spa', 'label' => __('Spa', 'travel-search-bar')],
                                'parking' => ['icon' => 'parking', 'label' => __('Parking', 'travel-search-bar')],
                                'restaurant' => ['icon' => 'utensils', 'label' => __('Restaurant', 'travel-search-bar')],
                                'gym' => ['icon' => 'dumbbell', 'label' => __('Gym', 'travel-search-bar')],
                                'breakfast' => ['icon' => 'coffee', 'label' => __('Breakfast', 'travel-search-bar')],
                                'ac' => ['icon' => 'snowflake', 'label' => __('Air Conditioning', 'travel-search-bar')]
                            ];
                            
                            foreach ($amenities as $key => $amenity): ?>
                                <div class="amenity-checkbox">
                                    <input type="checkbox" 
                                           id="amenity_<?php echo esc_attr($key); ?>"
                                           name="amenities[]"
                                           value="<?php echo esc_attr($key); ?>"
                                           class="amenity-input">
                                    <label for="amenity_<?php echo esc_attr($key); ?>" 
                                           class="amenity-label">
                                        <i class="fas fa-<?php echo esc_attr($amenity['icon']); ?> me-2"></i>
                                        <?php echo esc_html($amenity['label']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions mt-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                id="resetAllFilters">
                            <i class="fas fa-redo me-2"></i>
                            <?php esc_html_e('Reset All Filters', 'travel-search-bar'); ?>
                        </button>
                        
                        <button type="button" 
                                class="btn btn-outline-primary" 
                                id="saveSearchPrefs">
                            <i class="fas fa-save me-2"></i>
                            <?php esc_html_e('Save Search', 'travel-search-bar'); ?>
                        </button>
                        
                        <span class="badge bg-info ms-2" id="activeFiltersCount">
                            <?php esc_html_e('0 filters active', 'travel-search-bar'); ?>
                        </span>
                    </div>
                </div>
                
                <div class="col-md-4 text-md-end">
                    <button type="submit" 
                            class="btn btn-primary btn-lg px-5" 
                            id="submitAdvancedSearch">
                        <i class="fas fa-search me-2"></i>
                        <?php esc_html_e('Search Now', 'travel-search-bar'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="advanced_search" value="1">
        <?php wp_nonce_field('travel_advanced_search', 'travel_search_nonce'); ?>
    </form>
</div>

<!-- Results Container (Initially Hidden) -->
<div id="searchResultsContainer" class="mt-5" style="display: none;">
    <h3 class="mb-4"><?php esc_html_e('Search Results', 'travel-search-bar'); ?></h3>
    <div id="searchResults" class="row"></div>
    <div id="searchLoading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?php esc_html_e('Loading...', 'travel-search-bar'); ?></span>
        </div>
        <p class="mt-3"><?php esc_html_e('Searching for destinations...', 'travel-search-bar'); ?></p>
    </div>
    <div id="noResults" class="alert alert-info" style="display: none;">
        <i class="fas fa-info-circle me-2"></i>
        <?php esc_html_e('No results found. Try adjusting your search criteria.', 'travel-search-bar'); ?>
    </div>
</div>

<style>
    /* Advanced Filters Styles */
    .travel-advanced-search-form {
        background: #fff;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .search-type-tabs .btn-group {
        width: 100%;
    }
    
    .search-type-tabs .btn {
        flex: 1;
    }
    
    .rating-stars {
        display: flex;
        gap: 5px;
    }
    
    .rating-star-btn {
        background: none;
        border: none;
        padding: 0;
        font-size: 24px;
        color: #ddd;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .rating-star-btn:hover,
    .rating-star-btn.active {
        color: #ffc107;
    }
    
    .rating-star-btn.active ~ .rating-star-btn {
        color: #ffc107;
    }
    
    .amenities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }
    
    .amenity-checkbox {
        display: flex;
        align-items: center;
    }
    
    .amenity-input {
        margin-right: 8px;
    }
    
    .amenity-label {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
    }
    
    /* Price Range Slider */
    #priceSlider::-webkit-slider-thumb {
        background: #3498db;
    }
    
    #priceSlider::-moz-range-thumb {
        background: #3498db;
    }
    
    /* Form Range Customization */
    .form-range::-webkit-slider-thumb {
        background: #3498db;
        border: none;
    }
    
    .form-range::-moz-range-thumb {
        background: #3498db;
        border: none;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .amenities-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .search-type-tabs .btn-group {
            flex-direction: column;
        }
        
        .search-type-tabs .btn {
            margin-bottom: 5px;
        }
    }
    
    @media (max-width: 576px) {
        .travel-advanced-search-form {
            padding: 20px;
        }
        
        .amenities-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions .btn-lg {
            width: 100%;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize advanced filters
    const TravelAdvancedFilters = {
        init: function() {
            this.setupRatingStars();
            this.setupPriceSlider();
            this.setupGuestCounter();
            this.setupFormSubmission();
            this.setupFilterCount();
            this.setupResetButton();
            this.setupSaveSearch();
        },
        
        // Rating stars functionality
        setupRatingStars: function() {
            $('.rating-star-btn').on('click', function() {
                const rating = $(this).data('value');
                
                // Update stars display
                $('.rating-star-btn').each(function() {
                    if ($(this).data('value') <= rating) {
                        $(this).addClass('active').find('i').removeClass('far').addClass('fas');
                    } else {
                        $(this).removeClass('active').find('i').removeClass('fas').addClass('far');
                    }
                });
                
                // Update hidden input
                $('#selectedRating').val(rating);
                
                // Update filter count
                TravelAdvancedFilters.updateFilterCount();
            });
        },
        
        // Price slider functionality
        setupPriceSlider: function() {
            const priceSlider = $('#priceSlider');
            const minPriceInput = $('#minPrice');
            const maxPriceInput = $('#maxPrice');
            const priceDisplay = $('#priceRangeDisplay');
            
            // Update display when slider changes
            priceSlider.on('input', function() {
                const maxPrice = $(this).val();
                const minPrice = minPriceInput.val() || 0;
                maxPriceInput.val(maxPrice);
                
                // Update display
                priceDisplay.text(`Range: ₹${Number(minPrice).toLocaleString()} - ₹${Number(maxPrice).toLocaleString()}`);
                
                // Update filter count
                TravelAdvancedFilters.updateFilterCount();
            });
            
            // Update slider when input changes
            maxPriceInput.on('input', function() {
                const maxPrice = $(this).val() || 10000;
                priceSlider.val(maxPrice);
                priceDisplay.text(`Range: ₹${Number(minPriceInput.val() || 0).toLocaleString()} - ₹${Number(maxPrice).toLocaleString()}`);
                TravelAdvancedFilters.updateFilterCount();
            });
            
            minPriceInput.on('input', function() {
                priceDisplay.text(`Range: ₹${Number($(this).val() || 0).toLocaleString()} - ₹${Number(maxPriceInput.val() || 10000).toLocaleString()}`);
                TravelAdvancedFilters.updateFilterCount();
            });
        },
        
        // Guest counter functionality
        setupGuestCounter: function() {
            $('#decreaseGuests').on('click', function() {
                const guestsInput = $('#advGuests');
                let current = parseInt(guestsInput.val());
                if (current > 1) {
                    guestsInput.val(current - 1);
                    TravelAdvancedFilters.updateFilterCount();
                }
            });
            
            $('#increaseGuests').on('click', function() {
                const guestsInput = $('#advGuests');
                let current = parseInt(guestsInput.val());
                if (current < 20) {
                    guestsInput.val(current + 1);
                    TravelAdvancedFilters.updateFilterCount();
                }
            });
        },
        
        // Form submission with AJAX
        setupFormSubmission: function() {
            $('#advancedSearchForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading
                $('#searchLoading').show();
                $('#searchResultsContainer').show();
                $('#searchResults').empty();
                $('#noResults').hide();
                
                // Collect form data
                const formData = $(this).serialize();
                
                // AJAX request
                $.ajax({
                    url: '<?php echo admin_url("admin-ajax.php"); ?>',
                    type: 'POST',
                    data: formData + '&action=travel_advanced_search',
                    success: function(response) {
                        $('#searchLoading').hide();
                        
                        if (response.success && response.data.results.length > 0) {
                            TravelAdvancedFilters.displayResults(response.data.results);
                        } else {
                            $('#noResults').show();
                        }
                    },
                    error: function() {
                        $('#searchLoading').hide();
                        $('#noResults').show();
                    }
                });
            });
        },
        
        // Display search results
        displayResults: function(results) {
            const resultsContainer = $('#searchResults');
            resultsContainer.empty();
            
            results.forEach(function(result) {
                const resultHtml = `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <img src="${result.image}" class="card-img-top" alt="${result.name}" style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">${result.name}</h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    ${result.location}
                                </p>
                                <p class="card-text">${result.description}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold">₹${result.price.toLocaleString()}</span>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star me-1"></i>${result.rating}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                resultsContainer.append(resultHtml);
            });
        },
        
        // Update active filter count
        setupFilterCount: function() {
            // Update on any form change
            $('#advancedSearchForm').on('change input', function() {
                TravelAdvancedFilters.updateFilterCount();
            });
            
            // Initial update
            TravelAdvancedFilters.updateFilterCount();
        },
        
        updateFilterCount: function() {
            let count = 0;
            const form = $('#advancedSearchForm');
            
            // Check location
            if (form.find('#advLocation').val().trim()) count++;
            
            // Check category
            if (form.find('#advCategory').val()) count++;
            
            // Check state
            if (form.find('#advState').val()) count++;
            
            // Check dates
            if (form.find('#advCheckIn').val()) count++;
            
            // Check price range
            if (form.find('#minPrice').val() || form.find('#maxPrice').val() !== '10000') count++;
            
            // Check rating
            if (form.find('#selectedRating').val() !== '0') count++;
            
            // Check amenities
            count += form.find('input[name="amenities[]"]:checked').length;
            
            // Update display
            $('#activeFiltersCount').text(count + ' filter' + (count !== 1 ? 's' : '') + ' active');
            
            // Update badge color
            const badge = $('#activeFiltersCount');
            badge.removeClass('bg-info bg-success bg-warning bg-danger');
            
            if (count === 0) {
                badge.addClass('bg-secondary');
            } else if (count <= 2) {
                badge.addClass('bg-success');
            } else if (count <= 4) {
                badge.addClass('bg-info');
            } else if (count <= 6) {
                badge.addClass('bg-warning');
            } else {
                badge.addClass('bg-danger');
            }
        },
        
        // Reset all filters
        setupResetButton: function() {
            $('#resetAllFilters').on('click', function() {
                if (confirm('Are you sure you want to reset all filters?')) {
                    // Reset form
                    $('#advancedSearchForm')[0].reset();
                    
                    // Reset rating stars
                    $('.rating-star-btn').removeClass('active').find('i').removeClass('fas').addClass('far');
                    $('#selectedRating').val('0');
                    
                    // Reset price display
                    $('#priceRangeDisplay').text('Range: ₹0 - ₹10,000');
                    $('#maxPrice').val('10000');
                    $('#priceSlider').val('10000');
                    
                    // Reset guest count
                    $('#advGuests').val('2');
                    
                    // Update filter count
                    TravelAdvancedFilters.updateFilterCount();
                    
                    // Hide results
                    $('#searchResultsContainer').hide();
                }
            });
        },
        
        // Save search preferences
        setupSaveSearch: function() {
            $('#saveSearchPrefs').on('click', function() {
                const formData = $('#advancedSearchForm').serializeArray();
                
                // Check if user is logged in
                <?php if (is_user_logged_in()): ?>
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        type: 'POST',
                        data: {
                            action: 'save_travel_search',
                            data: formData,
                            nonce: '<?php echo wp_create_nonce("save_travel_search_nonce"); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Search preferences saved!');
                            } else {
                                alert('Failed to save preferences.');
                            }
                        }
                    });
                <?php else: ?>
                    // Save to localStorage for non-logged-in users
                    try {
                        const searches = JSON.parse(localStorage.getItem('travelSavedSearches') || '[]');
                        searches.push({
                            name: 'Search ' + new Date().toLocaleDateString(),
                            data: formData,
                            timestamp: new Date().toISOString()
                        });
                        
                        localStorage.setItem('travelSavedSearches', JSON.stringify(searches));
                        alert('Search saved locally!');
                    } catch (e) {
                        alert('Please login to save searches permanently.');
                    }
                <?php endif; ?>
            });
        }
    };
    
    // Initialize
    TravelAdvancedFilters.init();
});
</script>