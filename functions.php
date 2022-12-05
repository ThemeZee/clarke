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

	// Register Footer Navigation block style.
	register_block_style( 'core/navigation', array(
		'name'         => 'footer-navigation',
		'label'        => esc_html__( 'Footer Navigation', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );

	// Register Inherit Colors block style.
	register_block_style( 'core/social-links', array(
		'name'         => 'inherit-colors',
		'label'        => esc_html__( 'Inherit Colors', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );

	// Register Primary Hover block style.
	register_block_style( 'core/social-links', array(
		'name'         => 'primary-hover',
		'label'        => esc_html__( 'Primary Hover', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );

	// Register Thin Line block style.
	register_block_style( 'core/separator', array(
		'name'         => 'thin',
		'label'        => esc_html__( 'Thin Line', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	) );

	// Register Underlined Heading block style.
	$underlined_heading_style = array(
		'name'         => 'underlined-heading',
		'label'        => esc_html__( 'Underlined', 'clarke' ),
		'style_handle' => 'clarke-stylesheet',
	);

	register_block_style( 'core/heading', $underlined_heading_style );
	register_block_style( 'core/post-title', $underlined_heading_style );
	register_block_style( 'core/query-title', $underlined_heading_style );
}
add_action( 'init', 'clarke_register_block_styles', 9 );
