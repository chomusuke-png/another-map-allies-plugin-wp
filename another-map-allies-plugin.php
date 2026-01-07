<?php
/*
Plugin Name: Another Map Allies Plugin
Plugin URI: https://github.com/chomusuke-png/another-map-allies-plugin-wp
Description: Grid de aliados con carga de imágenes personalizada y mapa de conexiones. (Estructura Modular)
Version: 5.2
Author: Zumito
Author URI: https://github.com/chomusuke-png
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Definir Constantes
define( 'AMAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 2. Cargar Módulos (Includes)
require_once AMAP_PLUGIN_DIR . 'includes/class-amap-cpt.php';
require_once AMAP_PLUGIN_DIR . 'includes/class-amap-metaboxes.php';
require_once AMAP_PLUGIN_DIR . 'includes/class-amap-assets.php';
require_once AMAP_PLUGIN_DIR . 'includes/class-amap-widget.php';
require_once AMAP_PLUGIN_DIR . 'includes/class-amap-import-export.php';