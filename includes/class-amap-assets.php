<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Encola scripts y estilos.
 */
function amap_enqueue_assets( $hook ) {
	
	// 1. Frontend Assets
	if ( ! is_admin() ) {
		wp_enqueue_style( 'amap-style', AMAP_PLUGIN_URL . 'assets/css/amap-style.css', array(), '5.1' );
		wp_enqueue_script( 'amap-frontend-js', AMAP_PLUGIN_URL . 'assets/js/amap-frontend.js', array( 'jquery' ), '5.1', true );
	}
	
	// 2. Admin Assets (Solo en nuestro CPT)
	global $post;
	
	$is_amap_screen = ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset( $post ) && 'amap_ally' === $post->post_type;

	if ( $is_amap_screen ) {
		// Importante: Habilita la librería de medios de WordPress
		wp_enqueue_media(); 
		
		wp_enqueue_style( 'amap-admin-css', AMAP_PLUGIN_URL . 'assets/admin/css/amap-admin.css', array(), '5.1' );
		wp_enqueue_script( 'amap-admin-js', AMAP_PLUGIN_URL . 'assets/admin/js/amap-admin.js', array( 'jquery' ), '5.1', true );
	}
}
add_action( 'admin_enqueue_scripts', 'amap_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'amap_enqueue_assets' );