<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Agrega un filtro para el bloque de consulta de WordPress
// que muestra los posts relacionados en la página de un post y los filtra por categorías
add_filter('render_block_data', function ($parsed_block) {
    if (
        is_single() &&
        isset($parsed_block['blockName']) &&
        $parsed_block['blockName'] === 'core/query' &&
        isset($parsed_block['attrs']['className']) &&
        strpos($parsed_block['attrs']['className'], 'is-style-is-related-posts') !== false
    ) {
        $category_ids = wp_get_post_categories(get_the_ID());

        if (!empty($category_ids)) {
            $parsed_block['attrs']['query']['categoryIds'] = $category_ids;
            $parsed_block['attrs']['query']['exclude'] = [get_the_ID()];
            $parsed_block['attrs']['query']['sticky'] = '';
            $parsed_block['attrs']['query']['perPage'] = 6;
        }
    }

    return $parsed_block;
});

add_action( 'pre_get_posts', 'smn_pre_get_posts' );
function smn_pre_get_posts($query) {
    if (!$query->is_main_query() || is_admin() ) return;

    if (is_search()) {
        $query->set('posts_per_page', 30);
    } elseif( is_tax( 'portfolio_category') ) {
        $query->set('posts_per_page', 24);
    }
}

function cmplz_show_banner_on_click() {
	?>
	<script>
        function addEvent(event, selector, callback, context) {
            document.addEventListener(event, e => {
                if ( e.target.closest(selector) ) {
                    callback(e);
                }
            });
        }
        addEvent('click', 'a.cmplz-show-banner, li.cmplz-show-banner > a', function(){
            event.preventDefault();
            document.querySelectorAll('.cmplz-manage-consent').forEach(obj => {
                obj.click();
            });
        });
	</script>
	<?php
}
add_action( 'wp_footer', 'cmplz_show_banner_on_click' );

// TEMPORAL: mantiene abierta la superposición móvil del menú para maquetarla. Poner a false o borrar al terminar.
define( 'SMN_DEBUG_NAV_OVERLAY', false );

function smn_force_nav_overlay_open( $block_content ) {
    $processor = new WP_HTML_Tag_Processor( $block_content );

    if ( ! $processor->next_tag( 'nav' ) ) {
        return $block_content;
    }

    while ( $processor->next_tag( array( 'class_name' => 'wp-block-navigation__responsive-container' ) ) ) {
        $processor->add_class( 'is-menu-open' );
        $processor->add_class( 'has-modal-open' );
        // Without these directives the Interactivity API can no longer remove the open classes.
        $processor->remove_attribute( 'data-wp-class--is-menu-open' );
        $processor->remove_attribute( 'data-wp-class--has-modal-open' );
    }

    return $processor->get_updated_html();
}

add_filter( 'render_block_core/navigation', function ( $block_content ) {
    if ( ! SMN_DEBUG_NAV_OVERLAY || ! is_user_logged_in() ) {
        return $block_content;
    }

    return smn_force_nav_overlay_open( $block_content );
} );

