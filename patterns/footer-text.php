<?php
/**
 * Title: Footer Text
 * Slug: clarke/footer-text
 * Inserter: no
*/
?>

<!-- wp:paragraph -->
<p>&copy; <?php echo wp_kses_post( date_i18n( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|gray"}}}},"className":"flip-link-hover"} -->
<p class="flip-link-hover has-link-color"><a href="#"><?php _e( 'Privacy Policy', 'clarke' ); ?></a> | <a href="#"><?php _e( 'Imprint', 'clarke' ); ?></a></p>
<!-- /wp:paragraph -->
