<?php
/*-----------------------------------------------------------------------------------*/
/*	Enqueue Styles in Child Theme
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'avillas_child_asset_version' ) ) {
    function avillas_child_asset_version( $relative_path ) {
        $asset_path = get_stylesheet_directory() . $relative_path;
        return file_exists( $asset_path ) ? filemtime( $asset_path ) : '1.0.0';
    }
}

if ( ! function_exists( 'avillas_child_force_recaptcha_https' ) ) {
    function avillas_child_force_recaptcha_https( $src, $handle ) {
        if ( in_array( $handle, array( 'rh-google-recaptcha', 'inspiry-google-recaptcha' ), true ) ) {
            return set_url_scheme( $src, 'https' );
        }

        return $src;
    }
    add_filter( 'script_loader_src', 'avillas_child_force_recaptcha_https', 10, 2 );
}

if (!function_exists('inspiry_enqueue_child_styles')) {
    function inspiry_enqueue_child_styles(){
        if ( !is_admin() ) {
            // dequeue and deregister parent default css
            wp_dequeue_style( 'parent-default' );
            wp_deregister_style( 'parent-default' );

            // dequeue parent custom css
            wp_dequeue_style( 'parent-custom' );

            // parent default css
            wp_enqueue_style( 'parent-default', get_template_directory_uri().'/style.css' );

            // parent custom css
            wp_enqueue_style( 'parent-custom' );

            // child default css
            wp_enqueue_style('child-default', get_stylesheet_uri(), array('parent-default'), avillas_child_asset_version( '/style.css' ), 'all' );

            // child custom css
            wp_enqueue_style('child-custom',  get_stylesheet_directory_uri() . '/child-custom.css', array('child-default'), avillas_child_asset_version( '/child-custom.css' ), 'all' );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'inspiry_enqueue_child_styles', PHP_INT_MAX );


if ( !function_exists( 'inspiry_load_translation_from_child' ) ) {
    /**
     * Load translation files from child theme
     */
    function inspiry_load_translation_from_child() {
        load_child_theme_textdomain ( 'framework', get_stylesheet_directory () . '/languages' );
    }
    add_action ( 'after_setup_theme', 'inspiry_load_translation_from_child' );
}

/**
 * Load the global header design everywhere, with page-specific AVILLAS styling
 * only on the homepage, property pages, and telephone consultation page.
 */
if ( ! function_exists( 'avillas_child_enqueue_design_styles' ) ) {
    function avillas_child_enqueue_design_styles() {
        if ( is_admin() ) {
            return;
        }

        $styles_uri = get_stylesheet_directory_uri() . '/assets/css/';
        wp_enqueue_style(
            'avillas-header-global',
            $styles_uri . 'avillas-header-global.css',
            array( 'child-custom' ),
            avillas_child_asset_version( '/assets/css/avillas-header-global.css' )
        );
        $images_uri = get_stylesheet_directory_uri() . '/assets/images/';
        wp_add_inline_style(
            'avillas-header-global',
            sprintf(
                ':root { --avillas-header-logo-image: url("%1$s"); --avillas-new-banner-image: url("%2$s"); --avillas-consultation-hero-image: url("%3$s"); }',
                esc_url( $images_uri . 'header_logo.png' ),
                esc_url( $images_uri . 'newbanner.png' ),
                esc_url( $images_uri . 'telephone-consultation-banner.png' )
            )
        );

        if ( is_front_page() || is_singular( 'property' ) ) {
            wp_enqueue_style(
                'avillas-shared',
                $styles_uri . 'avillas-shared.css',
                array( 'avillas-header-global' ),
                avillas_child_asset_version( '/assets/css/avillas-shared.css' )
            );
        }

        if ( is_page( array( 14510, 7951 ) ) ) {
            wp_enqueue_style(
                'avillas-rendez-vous-telephonique',
                $styles_uri . 'rendez-vous-telephonique.css',
                array( 'avillas-header-global' ),
                avillas_child_asset_version( '/assets/css/rendez-vous-telephonique.css' )
            );
            if ( is_page( 7951 ) ) {
                wp_enqueue_style(
                    'avillas-visites-personnalisees',
                    $styles_uri . 'visites-personnalisees.css',
                    array( 'avillas-rendez-vous-telephonique' ),
                    avillas_child_asset_version( '/assets/css/visites-personnalisees.css' )
                );
                wp_add_inline_style(
                    'avillas-visites-personnalisees',
                    'body.page-id-7951 { --avillas-consultation-hero-image: url("' . esc_url( $images_uri . 'screen.png' ) . '"); }'
                );
            }
        }
    }
    add_action( 'wp_enqueue_scripts', 'avillas_child_enqueue_design_styles', 30 );
}

function avillas_child_consultation_body_class( $classes ) {
    if ( is_page( array( 14510, 7951 ) ) ) {
        $classes[] = 'avillas-consultation-page';
    }
    return $classes;
}
add_filter( 'body_class', 'avillas_child_consultation_body_class' );

require_once get_stylesheet_directory() . '/inc/random-properties.php';

/**
 * Keep partner thumbnails out of LiteSpeed's transparent-placeholder lazyload.
 */
if ( ! function_exists( 'avillas_child_preserve_partner_logo_src' ) ) {
    function avillas_child_preserve_partner_logo_src( $attr, $attachment, $size ) {
        $image_class = isset( $attr['class'] ) ? $attr['class'] : '';
        $is_priority_image = false !== strpos( $image_class, 'attachment-partners-logo' )
            || false !== strpos( $image_class, 'attachment-agent-image' )
            || false !== strpos( $image_class, 'attachment-property-thumb-image' );

        if ( 'partners-logo' !== $size && ! $is_priority_image ) {
            return $attr;
        }

        $attr['data-no-lazy'] = '1';
        return $attr;
    }
    add_filter( 'wp_get_attachment_image_attributes', 'avillas_child_preserve_partner_logo_src', 10, 3 );
}

/**
 * Keep visible image-widget artwork on the homepage and property pages out of
 * LiteSpeed's transparent-placeholder lazyload.
 */
if ( ! function_exists( 'avillas_child_preserve_image_widget_src' ) ) {
    function avillas_child_preserve_image_widget_src( $attr, $instance ) {
        if ( is_admin() || ( ! is_front_page() && ! is_singular( 'property' ) ) ) {
            return $attr;
        }

        $attr['data-no-lazy'] = '1';
        return $attr;
    }
    add_filter( 'image_widget_image_attributes', 'avillas_child_preserve_image_widget_src', 10, 2 );
}

/**
 * Exclude agent photos, property thumbnails, and visible sidebar artwork from
 * LiteSpeed's HTML-level lazyload rewrite.
 */
if ( ! function_exists( 'avillas_child_exclude_priority_image_classes_from_litespeed' ) ) {
    function avillas_child_exclude_priority_image_classes_from_litespeed( $excludes ) {
        if ( is_admin() || ( ! is_front_page() && ! is_singular( 'property' ) ) ) {
            return $excludes;
        }

        $excludes[] = 'attachment-agent-image';
        $excludes[] = 'attachment-property-thumb-image';

        return array_unique( $excludes );
    }
    add_filter( 'litespeed_media_lazy_img_cls_excludes', 'avillas_child_exclude_priority_image_classes_from_litespeed', 10, 1 );

    function avillas_child_exclude_priority_image_sources_from_litespeed( $excludes ) {
        if ( is_admin() || ( ! is_front_page() && ! is_singular( 'property' ) ) ) {
            return $excludes;
        }

        $excludes[] = 'contactez-vous-par-telephone.jpg';
        $excludes[] = 'reservez-vos-visites.jpg';
        $excludes[] = 'Hotesse_AVILLAS_abonnez-vous-gratuitement.png';
        $excludes[] = 'AVILLAS-garantie-revente.png';

        return array_unique( $excludes );
    }
    add_filter( 'litespeed_media_lazy_img_excludes', 'avillas_child_exclude_priority_image_sources_from_litespeed', 10, 1 );
}

/**
 * Property gallery slider (replaces parent's FlexSlider gallery, single property page only).
 * See assets/classic/partials/property/single/{slider,gallery}.php for the markup override.
 */
if ( ! function_exists( 'avillas_child_enqueue_property_gallery_slider' ) ) {
    function avillas_child_enqueue_property_gallery_slider() {
        if ( is_admin() || ! is_singular( 'property' ) ) {
            return;
        }

        wp_enqueue_style(
            'avillas-property-gallery-slider',
            get_stylesheet_directory_uri() . '/assets/css/property-gallery-slider.css',
            array(),
            avillas_child_asset_version( '/assets/css/property-gallery-slider.css' )
        );

        wp_enqueue_script(
            'avillas-property-gallery-slider',
            get_stylesheet_directory_uri() . '/assets/js/property-gallery-slider.js',
            array(),
            avillas_child_asset_version( '/assets/js/property-gallery-slider.js' ),
            true
        );
    }
    add_action( 'wp_enqueue_scripts', 'avillas_child_enqueue_property_gallery_slider', 20 );
}

/**
 * Prevents a Layered Popups popup from reopening for 15 minutes after it was
 * last closed (per-popup cookie). See assets/js/layered-popup-cooldown.js.
 */
if ( ! function_exists( 'avillas_child_enqueue_layered_popup_cooldown' ) ) {
    function avillas_child_enqueue_layered_popup_cooldown() {
        if ( is_admin() || ! wp_script_is( 'ulp', 'registered' ) ) {
            return;
        }

        wp_enqueue_script(
            'avillas-layered-popup-cooldown',
            get_stylesheet_directory_uri() . '/assets/js/layered-popup-cooldown.js',
            array( 'ulp' ),
            avillas_child_asset_version( '/assets/js/layered-popup-cooldown.js' ),
            true
        );

        if ( is_front_page() || is_singular( 'property' ) ) {
            wp_enqueue_script(
                'avillas-contact-popup-widget',
                get_stylesheet_directory_uri() . '/assets/js/contact-popup-widget.js',
                array( 'avillas-layered-popup-cooldown' ),
                avillas_child_asset_version( '/assets/js/contact-popup-widget.js' ),
                true
            );
        }
    }
    add_action( 'wp_enqueue_scripts', 'avillas_child_enqueue_layered_popup_cooldown', 100 );
}

/**
 * Render the manual contact-popup control on the homepage and property pages.
 */
if ( ! function_exists( 'avillas_child_render_contact_popup_widget' ) ) {
    function avillas_child_render_contact_popup_widget() {
        if ( is_admin() || ( ! is_front_page() && ! is_singular( 'property' ) ) || ! wp_script_is( 'ulp', 'registered' ) ) {
            return;
        }
        ?>
        <button id="avillas-contact-widget" type="button" aria-label="<?php esc_attr_e( 'Besoin d’informations ?', 'framework' ); ?>" aria-controls="ulp-zvq5f6w2pb38Bufq" aria-expanded="false">
            <svg class="avillas-contact-widget__phone" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M5 3.5h3l1.5 4-2 1.6a15 15 0 0 0 7.4 7.4l1.6-2 4 1.5v3a2 2 0 0 1-2.2 2A17.4 17.4 0 0 1 3 5.7 2 2 0 0 1 5 3.5Z"></path>
            </svg>
            <span class="avillas-contact-widget__question" aria-hidden="true">?</span>
            <span class="avillas-contact-widget__badge" aria-label="<?php esc_attr_e( '1 notification', 'framework' ); ?>">1</span>
        </button>
        <?php
    }
    add_action( 'wp_footer', 'avillas_child_render_contact_popup_widget', 5 );
}

/**
 * Enqueue Avillas Parallax assets from child theme.
 */
if ( ! function_exists( 'avillas_child_enqueue_parallax' ) ) {
    function avillas_child_enqueue_parallax() {
        if ( ! is_admin() ) {
            wp_enqueue_script(
                'avillas-parallax-js',
                get_stylesheet_directory_uri() . '/assets/js/parallax.js',
                array(),
                avillas_child_asset_version( '/assets/js/parallax.js' ),
                true
            );

            $video_banner_js = get_stylesheet_directory() . '/assets/js/video-banner.js';
            wp_enqueue_script(
                'avillas-video-banner-js',
                get_stylesheet_directory_uri() . '/assets/js/video-banner.js',
                array(),
                file_exists( $video_banner_js ) ? filemtime( $video_banner_js ) : '1.0.0',
                true
            );
        }
    }
    add_action( 'wp_enqueue_scripts', 'avillas_child_enqueue_parallax', 20 );
}

/**
 * Shortcode [avillas_parallax]
 */
if ( ! function_exists( 'avillas_child_parallax_shortcode' ) ) {
    function avillas_child_parallax_shortcode( $atts, $content = null ) {
        $atts = shortcode_atts(
            array(
                'image'  => '',
                'height' => '400px',
                'speed'  => '0.5',
                'class'  => '',
            ),
            $atts,
            'avillas_parallax'
        );

        $image  = esc_url( $atts['image'] );
        $height = esc_attr( $atts['height'] );
        $speed  = esc_attr( $atts['speed'] );
        $class  = esc_attr( $atts['class'] );

        if ( empty( $image ) ) {
            return do_shortcode( $content );
        }

        $style = sprintf(
            'background-image:url(%s);min-height:%s;--parallax-speed:%s;',
            $image,
            $height,
            $speed
        );

        return sprintf(
            '<div class="avillas-parallax-section %s" data-parallax="true" data-parallax-speed="%s" style="%s">%s</div>',
            $class,
            $speed,
            $style,
            do_shortcode( $content )
        );
    }
    add_shortcode( 'avillas_parallax', 'avillas_child_parallax_shortcode' );
}