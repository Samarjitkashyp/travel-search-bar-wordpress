(function($) {
    'use strict';
    
    // Travel Search Bar Admin JavaScript
    const TravelSearchAdmin = {
        
        // Initialize all admin functionality
        init: function() {
            this.setupTabs();
            this.setupColorPicker();
            this.setupPreview();
            this.setupImportExport();
            this.setupMultiFieldValidation();
            this.setupSettingsValidation();
            this.setupQuickActions();
        },
        
        // Setup tab navigation
        setupTabs: function() {
            $('.travel-search-bar-tab').on('click', function(e) {
                e.preventDefault();
                
                const tabId = $(this).data('tab');
                
                // Update active tab
                $('.travel-search-bar-tab').removeClass('active');
                $(this).addClass('active');
                
                // Show selected tab content
                $('.travel-search-tab-content').hide();
                $('#' + tabId).show();
                
                // Update URL hash
                window.location.hash = tabId;
                
                // Save active tab to localStorage
                localStorage.setItem('travel_search_active_tab', tabId);
            });
            
            // Check for hash on page load
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                $(`.travel-search-bar-tab[data-tab="${hash}"]`).click();
            }
            
            // Check localStorage for saved tab
            const savedTab = localStorage.getItem('travel_search_active_tab');
            if (savedTab && !hash) {
                $(`.travel-search-bar-tab[data-tab="${savedTab}"]`).click();
            }
        },
        
        // Setup color picker with preview
        setupColorPicker: function() {
            $('input[type="color"]').on('change', function() {
                const color = $(this).val();
                const fieldId = $(this).attr('id');
                
                // Update preview
                $(`.color-preview-${fieldId}`).css('background-color', color);
                
                // Update text input if exists
                const textInput = $(`#${fieldId}_text`);
                if (textInput.length) {
                    textInput.val(color);
                }
            });
            
            // Also handle text input changes
            $('input[type="text"][id$="_text"]').on('input', function() {
                const color = $(this).val();
                const fieldId = $(this).attr('id').replace('_text', '');
                
                // Update color picker
                $(`#${fieldId}`).val(color);
                $(`.color-preview-${fieldId}`).css('background-color', color);
            });
        },
        
        // Setup live preview
        setupPreview: function() {
            const previewContainer = $('.travel-search-preview-content');
            if (!previewContainer.length) return;
            
            // Function to update preview
            const updatePreview = function() {
                const formData = $('form').serializeArray();
                const data = {};
                
                // Convert form data to object
                formData.forEach(function(item) {
                    if (item.name.startsWith('travel_search_bar_settings[')) {
                        const key = item.name.replace('travel_search_bar_settings[', '').replace(']', '');
                        data[key] = item.value;
                    }
                });
                
                // Generate preview HTML
                let previewHTML = `
                    <div class="travel-search-preview-live">
                        <h4>Live Preview</h4>
                        <div class="search-bar-preview">
                            <div class="search-input" style="
                                background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('${data.hero_background || ''}');
                                padding: 20px;
                                border-radius: 8px;
                                margin-bottom: 10px;
                            ">
                                <input type="text" 
                                       placeholder="${data.search_placeholder || 'Search...'}" 
                                       style="
                                           width: 100%;
                                           padding: 10px;
                                           border: 1px solid #ddd;
                                           border-radius: 4px;
                                       "
                                       readonly>
                            </div>
                            
                            <div class="filters-preview" style="margin-top: 10px;">
                                <button style="
                                    background: ${data.primary_color || '#3498db'};
                                    color: white;
                                    border: none;
                                    padding: 5px 10px;
                                    border-radius: 4px;
                                    cursor: default;
                                ">
                                    <i class="fas fa-sliders-h"></i> Advanced Filters
                                </button>
                            </div>
                        </div>
                        
                        <div class="preview-stats" style="margin-top: 20px; font-size: 12px; color: #666;">
                            <p>Background: ${data.hero_background ? 'Set ✓' : 'Not set'}</p>
                            <p>Primary Color: <span style="color: ${data.primary_color || '#3498db'}">${data.primary_color || '#3498db'}</span></p>
                            <p>Advanced Filters: ${data.enable_advanced_filters === '1' ? 'Enabled ✓' : 'Disabled'}</p>
                        </div>
                    </div>
                `;
                
                previewContainer.html(previewHTML);
            };
            
            // Update preview on form changes
            $('form').on('change keyup input', 'input, select, textarea', updatePreview);
            
            // Initial preview
            updatePreview();
        },
        
        // Setup import/export functionality
        setupImportExport: function() {
            // Export settings
            $('#export-settings').on('click', function() {
                const settings = $('form').serialize();
                const exportData = btoa(JSON.stringify(settings));
                
                $('#export-data').val(exportData).select();
                
                // Show success message
                TravelSearchAdmin.showMessage('Settings exported to clipboard!', 'success');
                
                // Copy to clipboard
                navigator.clipboard.writeText(exportData).then(function() {
                    TravelSearchAdmin.showMessage('Settings copied to clipboard!', 'success');
                });
            });
            
            // Import settings
            $('#import-settings').on('click', function() {
                const importData = $('#import-data').val();
                
                if (!importData) {
                    TravelSearchAdmin.showMessage('Please paste settings data to import.', 'error');
                    return;
                }
                
                try {
                    const decodedData = atob(importData);
                    const settings = JSON.parse(decodedData);
                    
                    // Parse URL encoded data
                    const params = new URLSearchParams(settings);
                    const settingsObj = {};
                    
                    params.forEach((value, key) => {
                        const match = key.match(/travel_search_bar_settings\[(.*?)\]/);
                        if (match) {
                            settingsObj[match[1]] = value;
                        }
                    });
                    
                    // Populate form fields
                    for (const key in settingsObj) {
                        const input = $(`[name="travel_search_bar_settings[${key}]"]`);
                        
                        if (input.length) {
                            if (input.attr('type') === 'checkbox') {
                                input.prop('checked', settingsObj[key] === '1');
                            } else {
                                input.val(settingsObj[key]);
                            }
                        }
                    }
                    
                    TravelSearchAdmin.showMessage('Settings imported successfully!', 'success');
                    
                    // Trigger preview update
                    $('form').trigger('change');
                    
                } catch (error) {
                    TravelSearchAdmin.showMessage('Invalid import data format.', 'error');
                    console.error('Import error:', error);
                }
            });
        },
        
        // Setup multi-field validation
        setupMultiFieldValidation: function() {
            $('textarea[id$="filter_states"], textarea[id$="filter_categories"]').on('blur', function() {
                const text = $(this).val();
                const lines = text.split('\n');
                const validLines = [];
                
                // Validate each line
                lines.forEach(function(line) {
                    const trimmed = line.trim();
                    if (trimmed !== '') {
                        // Check for duplicates
                        if (!validLines.includes(trimmed)) {
                            validLines.push(trimmed);
                        }
                    }
                });
                
                // Update textarea with valid lines
                $(this).val(validLines.join('\n'));
                
                // Show count
                const countElement = $(`#${$(this).attr('id')}_count`);
                if (!countElement.length) {
                    $(this).after(`<div class="description" id="${$(this).attr('id')}_count">${validLines.length} items</div>`);
                } else {
                    countElement.text(`${validLines.length} items`);
                }
            }).trigger('blur');
        },
        
        // Setup form validation
        setupSettingsValidation: function() {
            $('form').on('submit', function(e) {
                let isValid = true;
                const errors = [];
                
                // Validate URL fields
                $('input[type="url"]').each(function() {
                    const url = $(this).val();
                    if (url && !TravelSearchAdmin.isValidUrl(url)) {
                        isValid = false;
                        errors.push(`Invalid URL in "${$(this).prev('label').text()}"`);
                        $(this).addClass('error-field');
                    } else {
                        $(this).removeClass('error-field');
                    }
                });
                
                // Validate required fields
                $('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).val().trim()) {
                        isValid = false;
                        errors.push(`Required field "${$(this).prev('label').text()}" is empty`);
                        $(this).addClass('error-field');
                    } else {
                        $(this).removeClass('error-field');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    
                    let errorMessage = 'Please fix the following errors:<ul>';
                    errors.forEach(function(error) {
                        errorMessage += `<li>${error}</li>`;
                    });
                    errorMessage += '</ul>';
                    
                    TravelSearchAdmin.showMessage(errorMessage, 'error');
                    
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: $('.error-field').first().offset().top - 100
                    }, 500);
                }
            });
        },
        
        // Setup quick actions
        setupQuickActions: function() {
            // Reset to defaults
            $('#reset-defaults').on('click', function() {
                if (confirm('Are you sure you want to reset all settings to default values?')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'travel_search_reset_defaults',
                            nonce: travelSearchAdmin.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                TravelSearchAdmin.showMessage('Settings reset to defaults!', 'success');
                                location.reload();
                            } else {
                                TravelSearchAdmin.showMessage('Failed to reset settings.', 'error');
                            }
                        }
                    });
                }
            });
            
            // Test search functionality
            $('#test-search').on('click', function() {
                $('#test-results').html('<p>Testing search functionality...</p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'travel_search_test',
                        nonce: travelSearchAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-results').html(`
                                <div class="travel-search-status success">
                                    <strong>✓ Search test successful!</strong>
                                    <p>${response.data.message}</p>
                                </div>
                            `);
                        } else {
                            $('#test-results').html(`
                                <div class="travel-search-status error">
                                    <strong>✗ Search test failed</strong>
                                    <p>${response.data.message}</p>
                                </div>
                            `);
                        }
                    }
                });
            });
            
            // Clear cache
            $('#clear-cache').on('click', function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'travel_search_clear_cache',
                        nonce: travelSearchAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            TravelSearchAdmin.showMessage('Cache cleared successfully!', 'success');
                        } else {
                            TravelSearchAdmin.showMessage('Failed to clear cache.', 'error');
                        }
                    }
                });
            });
        },
        
        // Helper: Check if URL is valid
        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },
        
        // Helper: Show message
        showMessage: function(message, type = 'info') {
            // Remove existing messages
            $('.travel-search-admin-message').remove();
            
            // Create message element
            const messageHtml = `
                <div class="notice notice-${type} travel-search-admin-message is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss"></button>
                </div>
            `;
            
            // Insert message
            $('.wrap h1').after(messageHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('.travel-search-admin-message').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Dismiss button
            $('.notice-dismiss').on('click', function() {
                $(this).closest('.travel-search-admin-message').remove();
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        TravelSearchAdmin.init();
    });
    
})(jQuery);