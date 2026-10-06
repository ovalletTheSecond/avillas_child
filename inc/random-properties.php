<?php

function avillas_random_properties_labels( $language = '' ) {
    if ( ! $language ) {
        $language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
        $language = $language ? $language : substr( get_locale(), 0, 2 );
    }
    $translations = array(
        'fr' => array( 'title' => 'Découvrez aussi ces commerces', 'previous' => 'Commerce précédent', 'next' => 'Commerce suivant', 'view' => 'Voir le commerce', 'photo' => 'Photo indisponible', 'carousel' => 'Carrousel', 'slide' => 'Commerce' ),
        'en' => array( 'title' => 'Discover more businesses', 'previous' => 'Previous business', 'next' => 'Next business', 'view' => 'View business', 'photo' => 'Photo unavailable', 'carousel' => 'Carousel', 'slide' => 'Business' ),
        'es' => array( 'title' => 'Descubre otros negocios', 'previous' => 'Negocio anterior', 'next' => 'Negocio siguiente', 'view' => 'Ver el negocio', 'photo' => 'Foto no disponible', 'carousel' => 'Carrusel', 'slide' => 'Negocio' ),
        'ca' => array( 'title' => 'Descobreix altres negocis', 'previous' => 'Negoci anterior', 'next' => 'Negoci següent', 'view' => 'Veure el negoci', 'photo' => 'Foto no disponible', 'carousel' => 'Carrusel', 'slide' => 'Negoci' ),
    );

    return isset( $translations[ $language ] ) ? $translations[ $language ] : $translations['fr'];
}

function avillas_random_properties_query( $current_id ) {
    return new WP_Query(
        array(
            'post_type'           => 'property',
            'post_status'         => 'publish',
            'posts_per_page'      => 3,
            'post__not_in'        => array( $current_id ),
            'orderby'            => 'rand',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
            'tax_query'           => array(
                array(
                    'taxonomy' => 'property-status',
                    'field'    => 'slug',
                    'terms'    => array( 'services' ),
                    'operator' => 'NOT IN',
                ),
            ),
        )
    );
}

function avillas_enqueue_random_properties() {
    if ( ! is_singular( 'property' ) ) {
        return;
    }

    wp_enqueue_style( 'avillas-random-properties', get_stylesheet_directory_uri() . '/assets/css/random-properties.css', array( 'avillas-shared' ), avillas_child_asset_version( '/assets/css/random-properties.css' ) );
    wp_enqueue_script( 'avillas-random-properties', get_stylesheet_directory_uri() . '/assets/js/random-properties.js', array(), avillas_child_asset_version( '/assets/js/random-properties.js' ), true );
}
add_action( 'wp_enqueue_scripts', 'avillas_enqueue_random_properties', 40 );

function avillas_render_random_properties() {
    if ( ! is_singular( 'property' ) ) {
        return;
    }

    $properties = avillas_random_properties_query( get_queried_object_id() );
    if ( ! $properties->have_posts() ) {
        return;
    }

    $labels = avillas_random_properties_labels();
    $total = $properties->post_count;
    ?>
    <section class="avillas-random-properties" aria-labelledby="avillas-random-properties-title" aria-roledescription="<?php echo esc_attr( $labels['carousel'] ); ?>">
        <div class="avillas-random-properties__heading">
            <h2 id="avillas-random-properties-title"><?php echo esc_html( $labels['title'] ); ?></h2>
            <div class="avillas-random-properties__controls" hidden>
                <button type="button" data-direction="-1" aria-controls="avillas-random-properties-track" aria-label="<?php echo esc_attr( $labels['previous'] ); ?>">&#8592;</button>
                <span class="avillas-random-properties__counter" aria-live="polite">1 / <?php echo esc_html( $total ); ?></span>
                <button type="button" data-direction="1" aria-controls="avillas-random-properties-track" aria-label="<?php echo esc_attr( $labels['next'] ); ?>">&#8594;</button>
            </div>
        </div>
        <div id="avillas-random-properties-track" class="avillas-random-properties__track" tabindex="0" aria-label="<?php echo esc_attr( $labels['title'] ); ?>">
            <?php foreach ( $properties->posts as $index => $property ) : ?>
                <article class="avillas-random-properties__card" role="group" aria-roledescription="<?php echo esc_attr( $labels['slide'] ); ?>" aria-label="<?php echo esc_attr( ( $index + 1 ) . ' / ' . $total ); ?>" data-property-id="<?php echo esc_attr( $property->ID ); ?>">
                    <a class="avillas-random-properties__image" href="<?php echo esc_url( get_permalink( $property ) ); ?>" tabindex="-1" aria-hidden="true">
                        <?php
                        if ( has_post_thumbnail( $property ) ) {
                            echo get_the_post_thumbnail( $property, 'medium_large', array( 'alt' => '', 'loading' => 'eager', 'data-no-lazy' => '1', 'sizes' => '(max-width: 600px) calc(100vw - 106px), (max-width: 979px) 48vw, 30vw' ) );
                        } else {
                            echo '<span>' . esc_html( $labels['photo'] ) . '</span>';
                        }
                        ?>
                    </a>
                    <div class="avillas-random-properties__details">
                        <h3><a href="<?php echo esc_url( get_permalink( $property ) ); ?>"><?php echo esc_html( get_the_title( $property ) ); ?></a></h3>
                        <?php if ( function_exists( 'ere_get_property_price_plain' ) ) : ?>
                            <p class="avillas-random-properties__price"><?php echo esc_html( wp_strip_all_tags( ere_get_property_price_plain( $property->ID ) ) ); ?></p>
                        <?php endif; ?>
                        <a class="avillas-random-properties__link" href="<?php echo esc_url( get_permalink( $property ) ); ?>"><?php echo esc_html( $labels['view'] ); ?> &#8594;</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}
add_action( 'get_template_part_assets/classic/partials/footer/partners', 'avillas_render_random_properties' );
