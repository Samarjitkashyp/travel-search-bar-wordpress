(function($) {
    'use strict';
    
    // Travel Search Widget Admin JavaScript
    $(document).ready(function() {
        
        // Initialize when widget is added or updated
        $(document).on('widget-added widget-updated', function(event, widget) {
            if (widget.find('.travel-search-widget-form').length) {
                initWidgetForm(widget);
            }
        });
        
        // Initialize existing widgets
        $('.travel-search-widget-form').each(function() {
            initWidgetForm($(this).closest('.widget'));
        });
        
        /**
         * Initialize widget form
         */
        function initWidgetForm(widgetContainer) {
            const form = widgetContainer.find('.travel-search-widget-form');
            
            // Initialize color pickers
            form.find('.color-picker').wpColorPicker();
            
            // Initialize image upload
            form.find('.upload-image-button').on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const container = button.closest('.image-upload-container');
                const imageUrlField = container.find('.image-url');
                const imagePreview = container.find('.image-preview');
                const removeButton = container.find('.remove-image-button');
                
                // Create media frame
                const frame = wp.media({
                    title: travelSearchWidget.i18n.select_image,
                    button: {
                        text: travelSearchWidget.i18n.select_image
                    },
                    multiple: false
                });
                
                // Handle image selection
                frame.on('select', function() {
                    const attachment = frame.state().get('selection').first().toJSON();
                    
                    // Update field and preview
                    imageUrlField.val(attachment.url);
                    imagePreview.html('<img src="' + attachment.url + '" style="max-width: 100%; height: auto;">');
                    removeButton.show();
                });
                
                // Open media frame
                frame.open();
            });
            
            // Initialize image removal
            form.find('.remove-image-button').on('click', function(e) {
                e.preventDefault();
                
                const button = $(this);
                const container = button.closest('.image-upload-container');
                const imageUrlField = container.find('.image-url');
                const imagePreview = container.find('.image-preview');
                
                // Clear field and preview
                imageUrlField.val('');
                imagePreview.empty();
                button.hide();
            });
            
            // Toggle hero section based on compact mode
            form.find('#compact_mode').on('change', function() {
                const isCompact = $(this).is(':checked');
                const heroCheckbox = form.find('#show_hero');
                
                if (isCompact) {
                    heroCheckbox.prop('checked', false).prop('disabled', true);
                } else {
                    heroCheckbox.prop('disabled', false);
                }
            }).trigger('change');
            
            // Preview functionality
            form.on('change input', 'input, select, textarea', function() {
                updateWidgetPreview(widgetContainer);
            });
            
            // Initial preview update
            updateWidgetPreview(widgetContainer);
        }
        
        /**
         * Update widget preview
         */
        function updateWidgetPreview(widgetContainer) {
            const form = widgetContainer.find('.travel-search-widget-form');
            const previewId = 'preview-' + widgetContainer.find('input.widget-id').val();
            let preview = $('#' + previewId);
            
            // Create preview container if it doesn't exist
            if (!preview.length) {
                preview = $('<div class="travel-search-widget-preview" id="' + previewId + '"></div>');
                form.after(preview);
            }
            
            // Get form values
            const title = form.find('#title').val() || 'Search Destinations';
            const description = form.find('#description').val();
            const placeholder = form.find('#placeholder').val() || 'Search destinations...';
            const showAdvanced = form.find('#show_advanced').is(':checked');
            const showHero = form.find('#show_hero').is(':checked') && !form.find('#compact_mode').is(':checked');
            const compactMode = form.find('#compact_mode').is(':checked');
            const bgColor = form.find('#bg_color').val();
            const textColor = form.find('#text_color').val();
            const bgImage = form.find('#bg_image').val();
            
            // Generate preview HTML
            let previewHTML = '<h4>Preview</h4>';
            previewHTML += '<div class="preview-content" style="padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #ddd;">';
            
            // Title
            previewHTML += '<h5 style="margin-top: 0; color: #2c3e50;">' + title + '</h5>';
            
            // Description
            if (description) {
                previewHTML += '<p style="color: #666; font-size: 14px;">' + description + '</p>';
            }
            
            // Search bar preview
            previewHTML += '<div style="margin: 15px 0;">';
            
            if (showHero && !compactMode) {
                previewHTML += '<div style="';
                if (bgImage) {
                    previewHTML += 'background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(\'' + bgImage + '\'); background-size: cover;';
                } else if (bgColor) {
                    previewHTML += 'background-color: ' + bgColor + ';';
                } else {
                    previewHTML += 'background-color: #3498db;';
                }
                previewHTML += 'color: ' + (textColor || '#fff') + '; padding: 20px; border-radius: 8px; margin-bottom: 10px;">';
                previewHTML += '<input type="text" placeholder="' + placeholder + '" style="width: 100%; padding: 10px; border: none; border-radius: 4px;" readonly>';
                previewHTML += '</div>';
            } else {
                previewHTML += '<input type="text" placeholder="' + placeholder + '" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;" readonly>';
            }
            
            // Advanced filters button
            if (showAdvanced) {
                previewHTML += '<button style="margin-top: 10px; background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: default;">';
                previewHTML += '<i class="fas fa-sliders-h"></i> Advanced Filters';
                previewHTML += '</button>';
            }
            
            previewHTML += '</div>';
            
            // Stats
            previewHTML += '<div style="font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">';
            previewHTML += '<p style="margin: 5px 0;">';
            previewHTML += '<strong>Mode:</strong> ' + (compactMode ? 'Compact' : (showHero ? 'Full' : 'Simple'));
            previewHTML += '</p>';
            if (bgColor) {
                previewHTML += '<p style="margin: 5px 0;">';
                previewHTML += '<strong>BG Color:</strong> <span style="display: inline-block; width: 15px; height: 15px; background: ' + bgColor + '; border: 1px solid #ddd; vertical-align: middle; margin-right: 5px;"></span>' + bgColor;
                previewHTML += '</p>';
            }
            previewHTML += '</div>';
            
            previewHTML += '</div>';
            
            // Update preview
            preview.html(previewHTML);
        }
        
        /**
         * Save widget with AJAX for preview
         */
        $(document).on('click', '.widget-control-save', function() {
            const widget = $(this).closest('.widget');
            if (widget.find('.travel-search-widget-form').length) {
                // Trigger preview update
                setTimeout(function() {
                    updateWidgetPreview(widget);
                }, 500);
            }
        });
        
    });
    
})(jQuery);