<?php
/**
 * Travel Search Bar Template (WITHOUT Background Image)
 * 
 * @package TravelSearchBar
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get plugin instance for helper methods
$travel_plugin = TravelSearchBar::get_instance();

// Generate inline styles based on settings - REMOVED BACKGROUND IMAGE
$inline_styles = "
    /* Background image removed - only search bar */
    .travel-search-bar .btn-primary {
        background-color: {$atts['primary_color']};
        border-color: {$atts['primary_color']};
    }
";

// Add hover effect using the correct method
$darkened_color = '';
if (method_exists($travel_plugin, 'adjust_color_brightness')) {
    $darkened_color = $travel_plugin->adjust_color_brightness($atts['primary_color'], -20);
} elseif (function_exists('tsb_adjust_color_brightness')) {
    $darkened_color = tsb_adjust_color_brightness($atts['primary_color'], -20);
} else {
    // Fallback: create a simple darker color
    $darkened_color = $atts['primary_color'];
}

$inline_styles .= "
    .travel-search-bar .btn-primary:hover {
        background-color: {$darkened_color};
        border-color: {$darkened_color};
        opacity: 0.9;
    }
    
    /* Custom notification styles */
    .tsb-custom-notification {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    }
    
    .tsb-notify-close {
        background: transparent !important;
        border: none !important;
        font-size: 24px !important;
        cursor: pointer !important;
        padding: 0 8px !important;
        line-height: 1 !important;
    }
    
    .tsb-notify-close:hover {
        opacity: 1 !important;
    }
    
    /* Smooth transitions */
    #advancedSearchContainer {
        transition: all 0.3s ease !important;
    }
";

// Add CSS animations
$inline_styles .= "
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
";
?>

<style><?php echo $inline_styles; ?></style>

<div class="travel-search-bar">
    <!-- Simple Search Box WITHOUT Hero Background -->
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Simple Search Box -->
                <div class="search-box-container">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4">
                            <form id="travelQuickSearch" action="<?php echo esc_url(get_permalink($atts['results_page'])); ?>" method="GET">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-primary text-white border-0">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-0" 
                                           name="q"
                                           placeholder="<?php echo esc_attr($atts['placeholder']); ?>"
                                           required>
                                    <button class="btn btn-primary border-0" type="submit">
                                        Search
                                    </button>
                                </div>
                                
                                <!-- Toggle Advanced Search Button -->
                                <?php if ($atts['show_filters']): ?>
                                <div class="text-center mt-3">
                                    <button type="button" 
                                            class="btn btn-link text-decoration-none" 
                                            id="toggleAdvancedFiltersBtn">
                                        <i class="fas fa-sliders-h me-2"></i>
                                        <span>Show Advanced Search Filters</span>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Search Container (Initially Hidden) -->
    <?php if ($atts['show_filters']): ?>
    <div class="container-fluid bg-light py-4" id="advancedSearchContainer" style="display: none;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Advanced Search Component -->
                    <div class="advanced-search-form">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white py-3">
                                <h4 class="mb-0">
                                    <i class="fas fa-search-plus me-2"></i>Advanced Search
                                </h4>
                            </div>
                            
                            <div class="card-body p-4">
                                <form id="advancedSearchForm">
                                    <!-- Main Search Row -->
                                    <div class="row g-3 mb-4">
                                        <!-- Location Search -->
                                        <div class="col-md-6 col-lg-4">
                                            <label class="form-label">
                                                <i class="fas fa-map-marker-alt text-primary me-1"></i>Location / Destination
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="searchLocation"
                                                       name="location"
                                                       placeholder="Enter city, state, or destination">
                                                <span class="input-group-text">
                                                    <i class="fas fa-crosshairs"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Category Selector -->
                                        <div class="col-md-6 col-lg-3">
                                            <label class="form-label">
                                                <i class="fas fa-tags text-primary me-1"></i>Category
                                            </label>
                                            <select class="form-select" id="searchCategory">
                                                <option value="">All Categories</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <?php if (!empty(trim($category))): ?>
                                                        <option value="<?php echo esc_attr(trim($category)); ?>">
                                                            <?php echo esc_html(ucfirst(trim($category))); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- State Selector -->
                                        <div class="col-md-6 col-lg-3">
                                            <label class="form-label">
                                                <i class="fas fa-globe text-primary me-1"></i>State/Region
                                            </label>
                                            <select class="form-select" id="searchState">
                                                <option value="">All States</option>
                                                <?php foreach ($states as $state): ?>
                                                    <?php if (!empty(trim($state))): ?>
                                                        <option value="<?php echo esc_attr(trim($state)); ?>">
                                                            <?php echo esc_html(ucfirst(trim($state))); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Price Range -->
                                        <div class="col-md-6 col-lg-2">
                                            <label class="form-label">
                                                <i class="fas fa-rupee-sign text-primary me-1"></i>Price Range
                                            </label>
                                            <select class="form-select" id="priceRange">
                                                <option value="">Any Price</option>
                                                <option value="0-5000">Under ₹5,000</option>
                                                <option value="5000-10000">₹5,000 - ₹10,000</option>
                                                <option value="10000-20000">₹10,000 - ₹20,000</option>
                                                <option value="20000-50000">₹20,000 - ₹50,000</option>
                                                <option value="50000-100000">Over ₹50,000</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Additional Filters -->
                                    <div class="row g-3 mb-4">
                                        <!-- Rating Filter -->
                                        <div class="col-md-6 col-lg-3">
                                            <label class="form-label">
                                                <i class="fas fa-star text-primary me-1"></i>Minimum Rating
                                            </label>
                                            <div class="rating-stars mb-3">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star rating-star" data-rating="<?php echo $i; ?>"></i>
                                                <?php endfor; ?>
                                                <input type="hidden" name="min_rating" id="minRating" value="3">
                                            </div>
                                        </div>

                                        <!-- Sort Options -->
                                        <div class="col-md-6 col-lg-3">
                                            <label class="form-label">
                                                <i class="fas fa-sort-amount-down text-primary me-1"></i>Sort By
                                            </label>
                                            <select class="form-select" id="sortBy">
                                                <option value="relevance">Relevance</option>
                                                <option value="price_low">Price: Low to High</option>
                                                <option value="price_high">Price: High to Low</option>
                                                <option value="rating">Rating</option>
                                                <option value="popularity">Popularity</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="row mt-4">
                                        <div class="col-md-8">
                                            <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                                <i class="fas fa-redo me-1"></i>Reset All Filters
                                            </button>
                                            <span class="badge bg-info ms-2" id="activeFilterCount">0 filters active</span>
                                        </div>
                                        <div class="col-md-4 text-md-end">
                                            <button type="submit" class="btn btn-primary btn-lg px-5" id="advancedSearchButton">
                                                <i class="fas fa-search me-2"></i>Search Now
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Close Button -->
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-outline-secondary" id="closeAdvancedSearchBtn">
                            <i class="fas fa-times me-2"></i>Close Advanced Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// ✅ REMOVED: Inline JavaScript - All JavaScript is now in frontend.js
// This prevents conflicts and ensures proper notification closing
?>

<!-- Minimal inline script for basic functionality -->
<script>
// This minimal script ensures basic functionality if JS fails to load
document.addEventListener('DOMContentLoaded', function() {
    // Basic toggle functionality as fallback
    var toggleBtn = document.getElementById('toggleAdvancedFiltersBtn');
    var closeBtn = document.getElementById('closeAdvancedSearchBtn');
    var container = document.getElementById('advancedSearchContainer');
    
    if (toggleBtn && container) {
        toggleBtn.addEventListener('click', function() {
            container.style.display = 'block';
        });
    }
    
    if (closeBtn && container) {
        closeBtn.addEventListener('click', function() {
            container.style.display = 'none';
        });
    }
    
    // Basic form validation
    var quickSearch = document.getElementById('travelQuickSearch');
    if (quickSearch) {
        quickSearch.addEventListener('submit', function(e) {
            var searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
                alert('Please enter a search term');
                return false;
            }
            return true;
        });
    }
});
</script>