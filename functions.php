<?php
/**
 * Clarke functions and definitions
 *
 * @package Clarke
 */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function clarke_setup() {

	// Enqueue editor styles.
	add_editor_style( 'style.css' );

	// Remove Core block patterns.
	remove_theme_support( 'core-block-patterns' );
}
add_action( 'after_setup_theme', 'clarke_setup' );


/**
 * Enqueue scripts and styles.
 */
function clarke_scripts() {

	// Register and Enqueue Stylesheet.
	wp_enqueue_style( 'clarke-stylesheet', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'clarke_scripts' );


/**
 * Enqueue block styles and scripts for Gutenberg Editor.
 */
function clarke_block_scripts() {

	// Enqueue Editor Styling.
	wp_enqueue_style( 'clarke-editor-styles', get_theme_file_uri( '/assets/css/editor-styles.css' ), array(), wp_get_theme()->get( 'Version' ), 'all' );
}
add_action( 'enqueue_block_editor_assets', 'clarke_block_scripts' );


/**
 * Change excerpt length for default posts
 *
 * @param int $length Length of excerpt in number of words.
 * @return int
 */
function clarke_excerpt_length( $length ) {
	if ( is_admin() ) {
		return $length;
	}

	return apply_filters( 'clarke_excerpt_length', 35 );
}
add_filter( 'excerpt_length', 'clarke_excerpt_length' );


/**
 * Registers block pattern categories.
 *
 * @return void
 */
function clarke_register_block_pattern_categories() {
	$block_pattern_categories = array(
		'clarke_hero'         => array( 'label' => __( 'Clarke: Hero', 'clarke' ) ),
		'clarke_cta'          => array( 'label' => __( 'Clarke: Call to Action', 'clarke' ) ),
		'clarke_features'     => array( 'label' => __( 'Clarke: Features', 'clarke' ) ),
		'clarke_media_text'   => array( 'label' => __( 'Clarke: Media Text', 'clarke' ) ),
		'clarke_portfolio'    => array( 'label' => __( 'Clarke: Portfolio', 'clarke' ) ),
		'clarke_services'     => array( 'label' => __( 'Clarke: Services', 'clarke' ) ),
		'clarke_testimonials' => array( 'label' => __( 'Clarke: Testimonials', 'clarke' ) ),
		'clarke_team'         => array( 'label' => __( 'Clarke: Team', 'clarke' ) ),
		'clarke_blog'         => array( 'label' => __( 'Clarke: Blog Posts', 'clarke' ) ),
	);

	/**
	 * Filters the theme block pattern categories.
	 */
	$block_pattern_categories = apply_filters( 'clarke_block_pattern_categories', $block_pattern_categories );

	foreach ( $block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}
}
add_action( 'init', 'clarke_register_block_pattern_categories', 9 );


/**
 * Registers block styles.
 *
 * @return void
 */
function clarke_register_block_styles() {

	// Register Main Navigation block style.
	register_block_style( 'core/navigation', array(
		'name'         => 'main-navigation',
		'label'        => esc_html__( 'Main Navigation', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );

	// Register Primary Hover block style.
	register_block_style( 'core/social-links', array(
		'name'         => 'primary-hover',
		'label'        => esc_html__( 'Primary Hover', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );
}
add_action( 'init', 'clarke_register_block_styles', 9 );


/**
 * Set up the Plugin Updater Constants.
 */
define( 'CLARKE_THEME_VERSION', '1.0' );
define( 'CLARKE_THEME_NAME', 'Clarke' );
define( 'CLARKE_THEME_ID', 256493 );
define( 'CLARKE_THEME_STORE_URL', 'https://themezee.com' );


/**
 * Include License Settings and Plugin Updater.
 */
include dirname( __FILE__ ) . '/includes/class-clarke-admin-page.php';
include dirname( __FILE__ ) . '/includes/class-clarke-license-settings.php';
include dirname( __FILE__ ) . '/includes/class-clarke-theme-updater.php';


/**
 * Initialize the updater. Hooked into `init` to work with the
 * wp_version_check cron job, which allows auto-updates.
 */
function clarke_run_theme_updater() {

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// Retrieve our license key from the DB.
	$options     = get_option( 'clarke_theme_settings', array() );
	$license_key = ! empty( $options['clarke_license_key'] ) ? trim( $options['clarke_license_key'] ) : false;

	// Setup the updater.
	new Clarke_Theme_Updater(
		array(
			'remote_api_url' => CLARKE_THEME_STORE_URL,
			'item_name'      => CLARKE_THEME_NAME,
			'theme_slug'     => get_template(),
			'version'        => CLARKE_THEME_VERSION,
			'author'         => 'ThemeZee',
			'item_id'        => CLARKE_THEME_ID,
		),
		array(
			'update-available' => esc_html__( 'Version %2$s of %1$s is available. <a href="%3$s">View changelog of %4$s</a> or <a href="%5$s" %6$s>update now</a>.', 'clarke' ),
			'update-notice'    => esc_html__( 'Updating this theme will override all theme files. Click "Cancel" to stop, "OK" to update.', 'clarke' ),
		)
	);
}
add_action( 'init', 'clarke_run_theme_updater' );
