<?php
/**
 * Unified property gallery slider markup (child theme).
 *
 * Replaces parent's slider-one.php / slider-two.php (FlexSlider based) with
 * markup for the dependency-free slider in assets/js/property-gallery-slider.js.
 * $args['thumb_position'] is 'right' or 'bottom' (mirrors the original
 * "thumb-on-right" / "thumb-on-bottom" admin setting).
 */
$thumb_position = isset( $args['thumb_position'] ) ? $args['thumb_position'] : 'right';

$size              = 'property-detail-slider-image-two';
$properties_images = rwmb_meta( 'REAL_HOMES_property_images', 'type=plupload_image&size=' . $size, get_the_ID() );
$prop_detail_login = inspiry_prop_detail_login();

// Same image-count thresholds as the original slider-one.php ('right') / slider-two.php ('bottom').
$min_images_for_slider = ( 'bottom' === $thumb_position ) ? 1 : 2;
$has_enough_images     = ! empty( $properties_images ) && count( $properties_images ) >= $min_images_for_slider;

if ( $has_enough_images && ( 'yes' != $prop_detail_login || is_user_logged_in() ) ) {
	$title_in_lightbox = 'true' === get_option( 'inspiry_display_title_in_lightbox' );
	$thumb_size         = ( 'bottom' === $thumb_position ) ? 'property-thumb-image' : 'property-detail-slider-thumb';
	$loop               = ( 'right' === $thumb_position ) ? 'true' : 'false'; // matches original loop behaviour per variant.
	$placeholder_url    = get_stylesheet_directory_uri() . '/assets/img/placeholder.jpg';
	$slide_index        = 0;
	?>
	<div class="avillas-property-slider" data-thumb-position="<?php echo esc_attr( $thumb_position ); ?>" data-loop="<?php echo esc_attr( $loop ); ?>">
		<div class="aps-viewport">
			<ul class="aps-slides">
				<?php foreach ( $properties_images as $prop_image_meta ) : ?>
					<li class="aps-slide">
						<img class="aps-loading-bg" src="<?php echo esc_url( $placeholder_url ); ?>" alt="" aria-hidden="true" loading="eager" fetchpriority="low" data-no-lazy="1">
						<a href="<?php echo esc_url( $prop_image_meta['full_url'] ); ?>"
							data-fancybox="gallery-images"
							<?php echo $title_in_lightbox ? 'title="' . esc_attr( $prop_image_meta['title'] ) . '"' : ''; ?>>
							<img src="<?php echo esc_url( $prop_image_meta['url'] ); ?>" alt="<?php echo esc_attr( $prop_image_meta['title'] ); ?>"
								loading="<?php echo 0 === $slide_index ? 'eager' : 'lazy'; ?>"
								fetchpriority="<?php echo 0 === $slide_index ? 'high' : 'low'; ?>"
								data-no-lazy="1">
						</a>
						<span class="aps-loading" aria-hidden="true">
							<span class="aps-spinner"></span>
							<span class="aps-loading-text"><?php esc_html_e( 'Image en cours de chargement', 'realhomes-child' ); ?></span>
						</span>
					</li>
					<?php $slide_index++; ?>
				<?php endforeach; ?>
			</ul>
			<button type="button" class="aps-nav aps-prev" aria-label="<?php esc_attr_e( 'Image précédente', 'realhomes-child' ); ?>"><i class="fas fa-angle-left" aria-hidden="true"></i></button>
			<button type="button" class="aps-nav aps-next" aria-label="<?php esc_attr_e( 'Image suivante', 'realhomes-child' ); ?>"><i class="fas fa-angle-right" aria-hidden="true"></i></button>
		</div>
		<ul class="aps-thumbs">
			<?php
			foreach ( $properties_images as $prop_image_id => $prop_image_meta ) :
				$thumb = wp_get_attachment_image_src( $prop_image_id, $thumb_size );
				?>
				<li class="aps-thumb">
					<button type="button">
						<img src="<?php echo esc_url( $thumb[0] ); ?>" alt="" loading="lazy" fetchpriority="low" data-no-lazy="1">
						<span class="aps-thumb-loading" aria-hidden="true"><span class="aps-spinner"></span></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
} elseif ( has_post_thumbnail() ) {
	?>
	<div id="property-featured-image" class="clearfix">
		<?php
		$image_id  = get_post_thumbnail_id();
		$image_url = wp_get_attachment_url( $image_id );
		echo '<a href="' . esc_url( $image_url ) . '" data-fancybox="gallery-images" class="">';
		echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( the_title_attribute( 'echo=0' ) ) . '" />';
		echo '</a>';
		?>
	</div>
	<?php
}
