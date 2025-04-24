(function($) {
    // Function to update dropdown dimensions
    function updateDropdownDimensions($select) {
        const $dropdown = $select.find('.list');
        const $options = $select.find('.option:visible');
        
        // Ensure minimum height even with few results
        if ($options.length > 0) {
            const totalHeight = Math.max(
                150, // minimum height
                Math.min(
                    300, // maximum height
                    50 + ($options.length * 44) // header + (number of options * option height)
                )
            );
            $dropdown.css('height', totalHeight + 'px');
        } else {
            $dropdown.css('height', '150px'); // minimum height when no results
        }
    }

    // Function to add search functionality to nice-select
    function addSearchToNiceSelect($select) {
        const $dropdown = $select.find('.list');
        
        // Only add search box if it doesn't exist
        if ($dropdown.find('.search-box').length === 0) {
            // First, restructure the dropdown
            const $options = $select.find('.option').detach();
            
            // Add search box
            const $searchBox = $('<div class="search-box">' +
                '<input type="text" placeholder="Rechercher...">' +
                '</div>');
            
            // Create options container
            const $optionsContainer = $('<div class="options-container"></div>');
            
            // Add the detached options to the container
            $optionsContainer.append($options);
            
            // Add both to the dropdown
            $dropdown.empty().append($searchBox).append($optionsContainer);
    
            const $searchInput = $searchBox.find('input');
    
            // Handle search input events
            $searchInput.on('click', function(e) {
                e.stopPropagation();
            });
    
            $searchInput.on('keyup', function(e) {
                const searchTerm = $(this).val().toLowerCase();
                let hasResults = false;
                let visibleCount = 0;
    
                $dropdown.find('.no-results').remove();
                
                $optionsContainer.find('.option').each(function() {
                    const text = $(this).text().toLowerCase();
                    const matches = text.indexOf(searchTerm) > -1;
                    $(this).toggle(matches);
                    if (matches) {
                        hasResults = true;
                        visibleCount++;
                    }
                });
    
                if (!hasResults) {
                    $optionsContainer.append('<div class="no-results">Aucun résultat trouvé</div>');
                }
            });
        }
    }

    // Handle document clicks to close dropdowns
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nice-select').length) {
            $('.nice-select').each(function() {
                const $select = $(this);
                $select.removeClass('open');
                $select.find('.search-box input').val('');
                $select.find('.option').show();
                updateDropdownDimensions($select);
                $select.find('.no-results').remove();
            });
        }
    });

    // Handle option selection
    $(document).on('click', '.nice-select .option', function() {
        const $select = $(this).closest('.nice-select');
        const $searchInput = $select.find('.search-box input');
        $searchInput.val('');
        $select.find('.option').show();
        updateDropdownDimensions($select);
        $select.find('.no-results').remove();
    });

    // Override nice-select initialization
    const originalNiceSelect = $.fn.niceSelect;
    $.fn.niceSelect = function() {
        const result = originalNiceSelect.apply(this, arguments);
        
        this.each(function() {
            const $select = $(this);
            if ($select.next('.nice-select').length) {
                addSearchToNiceSelect($select.next('.nice-select'));
                updateDropdownDimensions($select.next('.nice-select'));
            }
        });
        
        return result;
    };

    // Handle clicks on nice-select
    $(document).on('click', '.nice-select', function(e) {
        if ($(this).hasClass('open')) {
            const $searchInput = $(this).find('.search-box input');
            if ($searchInput.length) {
                setTimeout(() => {
                    $searchInput.focus();
                    updateDropdownDimensions($(this));
                }, 0);
            }
        }
    });

    // Handle clicks on search box
    $(document).on('click', '.nice-select .search-box', function(e) {
        e.stopPropagation();
    });

    // Initialize search on existing nice-selects
    $('.nice-select').each(function() {
        addSearchToNiceSelect($(this));
        updateDropdownDimensions($(this));
    });

})(jQuery);