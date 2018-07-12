<?php
/**
 * Child theme functions
 */
function oceanwp_child_enqueue_parent_style() {
	
	$theme   = wp_get_theme( 'OceanWP' );
	$version = $theme->get( 'Version' );

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'oceanwp-style' ), $version );
	
}
add_action( 'wp_enqueue_scripts', 'oceanwp_child_enqueue_parent_style' );

/**
 * Load the parent rtl.css file
 */
function oceanwp_child_enqueue_rtl_style() {
	// Dynamically get version number of the parent stylesheet
	$theme   = wp_get_theme( 'OceanWP' );
	$version = $theme->get( 'Version' );
	// Load the stylesheet
	if ( is_rtl() ) {
		wp_enqueue_style( 'oceanwp-rtl', get_template_directory_uri() . '/rtl.css', array(), $version );
	}
	
}
add_action( 'wp_enqueue_scripts', 'oceanwp_child_enqueue_rtl_style' );

/* Hide administration connexion errors */

add_filter('login_errors', 'wpm_hide_errors');

function wpm_hide_errors() {
	
	return "L'identifiant ou le mot de passe est incorrect";
}