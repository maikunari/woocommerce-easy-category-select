<?php
/*
 Plugin Name: WooCommerce Easy Category Select
 Description: Adds a search box to the WooCommerce Product Categories meta box for easier category selection.
 Version: 1.0
 Author: Maikunari
 Author URI: https://github.com/maikunari
 Plugin URI: https://github.com/maikunari/woocommerce-easy-category-select
 Requires Plugins: woocommerce
*/

add_action('admin_footer', 'add_category_search_box');
function add_category_search_box() {
    global $post_type;
    if ($post_type !== 'product') return;

    // Get all product categories with hierarchy
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'hierarchical' => true,
    ]);

    // Build a map of category IDs to their full path
    $category_paths = [];
    foreach ($categories as $category) {
        $path = [];
        $current = $category;
        while ($current->parent != 0) {
            $parent = get_term($current->parent, 'product_cat');
            array_unshift($path, $parent->name);
            $current = $parent;
        }
        array_unshift($path, $current->name);
        $category_paths[$category->term_id] = implode(' > ', $path);
    }
    ?>
    <script>
        jQuery(document).ready(function($) {
            // Add search box at the top of the 'All categories' tab content panel so it shows only when this tab is active
            var searchBox = '<div style="margin-bottom: 10px; margin-top: 10px; position: sticky; top: 0; background: #fff; z-index: 10; padding: 5px 0; border-bottom: 1px solid #ddd;">' +
                '<input type="text" id="product_cat_search" placeholder="Search categories..." style="width: 100%; padding: 5px;" />' +
                '<div id="category_path" style="margin-top: 5px; font-style: italic; color: #555;"></div>' +
            '</div>';
            var $target = $('#product_cat-all');
            if ($target.length) {
                $target.prepend(searchBox); // Place it at the top of the 'All categories' tab content
                console.log('Search box added at the top of #product_cat-all with sticky positioning and bottom border');
            } else {
                $('#taxonomy-product_cat .tabs-panel').prepend(searchBox); // Fallback to the top of any tab content panel
                console.log('Fallback: Search box added to .tabs-panel');
            }
            
            // Prevent page reload on Enter key in search field
            $('#product_cat_search').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault(); // Prevent form submission/page reload
                    console.log('Enter key prevented from reloading page');
                }
            });

            // Store category paths
            var categoryPaths = <?php echo json_encode($category_paths); ?>;

            // Filter categories as you type
            $('#product_cat_search').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('#product_catchecklist li').each(function() {
                    var label = $(this).find('label').text().toLowerCase();
                    if (label.indexOf(searchTerm) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });

            // Show category path when a category is checked
            $('#product_catchecklist input[type="checkbox"]').on('change', function() {
                var catId = $(this).val();
                if ($(this).is(':checked') && categoryPaths[catId]) {
                    $('#category_path').text('Path: ' + categoryPaths[catId]);
                } else {
                    $('#category_path').text('');
                }
            });
        });
    </script>
    <?php
} 