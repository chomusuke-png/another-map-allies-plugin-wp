<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Registra el Post Type "Aliado".
 */
function amap_register_ally_cpt() {
	$labels = array(
		'name'          => _x( 'Aliados Mapa', 'post type general name', 'amap-domain' ),
		'singular_name' => _x( 'Aliado', 'post type singular name', 'amap-domain' ),
		'menu_name'     => _x( 'Aliados Mapa', 'admin menu', 'amap-domain' ),
		'name_admin_bar'=> _x( 'Aliado', 'add new on admin bar', 'amap-domain' ),
		'add_new'       => _x( 'Añadir Nuevo', 'ally', 'amap-domain' ),
		'add_new_item'  => __( 'Añadir Nuevo Aliado', 'amap-domain' ),
		'new_item'      => __( 'Nuevo Aliado', 'amap-domain' ),
		'edit_item'     => __( 'Editar Aliado', 'amap-domain' ),
		'view_item'     => __( 'Ver Aliado', 'amap-domain' ),
		'all_items'     => __( 'Todos los Aliados', 'amap-domain' ),
		'search_items'  => __( 'Buscar Aliados', 'amap-domain' ),
		'not_found'     => __( 'No se encontraron aliados.', 'amap-domain' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'aliado-mapa' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-grid-view',
		'supports'           => array( 'title' ), // Usamos campos personalizados para la imagen
	);

	register_post_type( 'amap_ally', $args );
}
add_action( 'init', 'amap_register_ally_cpt' );