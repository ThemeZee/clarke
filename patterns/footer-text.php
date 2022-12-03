<?php
/**
 * Title: Footer Text
 * Slug: clarke/footer-text
 * Inserter: no
*/
?>

<!-- wp:paragraph {"className":"flip-link-hover"} -->
<p class="flip-link-hover">&copy; <?php echo wp_kses_post( date_i18n( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?> | <a href="#"><?php _e( 'Privacy Policy', 'clarke' ); ?></a> | <a href="#"><?php _e( 'Imprint', 'clarke' ); ?></a></p>
<!-- /wp:paragraph -->
