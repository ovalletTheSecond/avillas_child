<?php
/**
 * Property Images Slider (child theme override).
 *
 * Replaces the parent theme's jQuery FlexSlider gallery with a lightweight
 * dependency-free slider (see assets/js/property-gallery-slider.js).
 * Same conditions/markup wrapper as parent so favorites/compare/print
 * buttons and the overall layout stay identical.
 */
$size              = 'property-detail-slider-image-two';
$properties_images = rwmb_meta( 'REAL_HOMES_property_images', 'type=plupload_image&size=' . $size, get_the_ID() );
$prop_detail_login = inspiry_prop_detail_login();

if ( has_post_thumbnail() || ( ( ! empty( $properties_images ) && count( $properties_images ) ) && ( 'yes' != $prop_detail_login || is_user_logged_in() ) ) ) {
	?>
	<div class="slider-main-wrapper">
		<?php
		$change_gallery_slider_type = get_post_meta( get_the_ID(), 'REAL_HOMES_change_gallery_slider_type', true );
		$gallery_slider_type        = get_post_meta( get_the_ID(), 'REAL_HOMES_gallery_slider_type', true );

		if ( '1' !== $change_gallery_slider_type ) {
			$gallery_slider_type = get_option( 'inspiry_gallery_slider_type', 'thumb-on-right' );
		}

		if ( isset( $_GET['slider-type'] ) ) {
			$gallery_slider_type = $_GET['slider-type']; // to override for demo
		}

		$thumb_position = ( 'thumb-on-bottom' === $gallery_slider_type ) ? 'bottom' : 'right';

		get_template_part(
			'assets/classic/partials/property/single/gallery',
			null,
			array( 'thumb_position' => $thumb_position )
		);
		?>
		<div class="slider-socket <?php echo esc_attr( $gallery_slider_type ); ?>">
			<?php
			$compare_properties_module = get_option( 'theme_compare_properties_module' );
			$inspiry_compare_page      = get_option( 'inspiry_compare_page' );
			if ( ( 'enable' === $compare_properties_module ) && ( $inspiry_compare_page ) ) {
				get_template_part( 'assets/classic/partials/properties/compare/button-compare' );
			}
			get_template_part( 'assets/classic/partials/property/single/add-to-favorites' );    // add to favorites.
			?>
			<span class="printer-icon"><a class="print" href="javascript:window.print()"><i class="fas fa-print"></i></a></span>
		</div>
	</div>
	<?php
}
