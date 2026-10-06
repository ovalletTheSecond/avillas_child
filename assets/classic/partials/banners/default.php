<?php
/**
 * Banner: Default (Child Theme Override)
 *
 * Remplace l'image de bannière de la page d'accueil par une vidéo de fond
 * avec effet parallax au scroll.
 *
 * @package    realhomes-child
 * @subpackage classic
 */

$post_id = get_the_ID();

// Revolution Slider if alias is provided and plugin is installed.
$rev_slider_alias = get_post_meta( $post_id, 'REAL_HOMES_rev_slider_alias', true );
if ( ! empty( $rev_slider_alias ) && realhomes_is_revslider_enabled() ) {
	realhomes_render_revslider( $rev_slider_alias );
} else {
	// Banner Image.
	$banner_image_path = '';

	if ( is_page_template( 'templates/home.php' ) ) {
		$banner_image_id = get_post_meta( $post_id, 'REAL_HOMES_home_banner_image', true );
	} else {
		$banner_image_id = get_post_meta( $post_id, 'REAL_HOMES_page_banner_image', true );
	}

	if ( $banner_image_id ) {
		$banner_image_path = wp_get_attachment_url( $banner_image_id );
	} else {
		$banner_image_path = get_default_banner();
	}

	$is_home = ( is_page_template( 'templates/home.php' ) || is_front_page() );
	?>
	<?php if ( $is_home ) : ?>
		<div class="page-head page-head--video">
			<video class="page-head__video-bg" autoplay muted loop playsinline preload="auto">
				<source src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/video/bg.mp4' ); ?>" type="video/mp4">
			</video>
			<div class="page-head__video-overlay"></div>
	<?php else : ?>
		<div class="page-head" style="background-image: url('<?php echo esc_url( $banner_image_path ); ?>');">
	<?php endif; ?>
		<?php
		if ( 'true' === get_option( 'theme_banner_titles', 'false' ) ) {
			$page_title_display = true;
		} else {
			$page_title_display = false;
		}

		$banner_title_display = get_post_meta( $post_id, 'REAL_HOMES_banner_title_display', true );
		if ( ! empty( $banner_title_display ) && 'show' === $banner_title_display ) {
			$page_title_display = true;
		} else {
			$page_title_display = false;
		}

		if ( $page_title_display ) {
			// Banner title
			$banner_title = get_post_meta( $post_id, 'REAL_HOMES_banner_title', true );
			if ( empty( $banner_title ) ) {
				$banner_title = get_the_title( $post_id );
			}

			if ( realhomes_is_woocommerce_activated() ) {
				if ( is_shop() ) {
					$banner_title = woocommerce_page_title( false );
				}
			}
			?>
			<div class="container">
				<div class="wrap clearfix">
					<h1 class="page-title"><span><?php echo esc_html( $banner_title ); ?></span></h1>
					<?php
					// Banner Sub Title.
					$banner_sub_title = get_post_meta( $post_id, 'REAL_HOMES_banner_sub_title', true );

					if ( $banner_sub_title ) {
						?><p><?php echo esc_html( $banner_sub_title ); ?></p><?php
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div><!-- End Page Head -->
	<?php
}
