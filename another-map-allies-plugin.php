<?php
/*
Plugin Name: Another Map Allies Plugin
Plugin URI: https://tusitio.com
Description: Grid de aliados con carga de imágenes personalizada y mapa de conexiones.
Version: 5.0
Author: Tu Nombre
Author URI: https://tusitio.com
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'AMAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * 1. CPT
 */
function amap_register_ally_cpt() {
	$args = array(
		'labels' => array(
			'name'          => __( 'Aliados Mapa', 'amap-domain' ),
			'singular_name' => __( 'Aliado', 'amap-domain' ),
			'add_new_item'  => __( 'Añadir Nuevo Aliado', 'amap-domain' ),
			'edit_item'     => __( 'Editar Aliado', 'amap-domain' ),
		),
		'public'      => false,
		'show_ui'     => true,
		'menu_icon'   => 'dashicons-grid-view',
		'supports'    => array( 'title' ), // Quitamos 'thumbnail' para usar nuestro propio campo
	);
	register_post_type( 'amap_ally', $args );
}
add_action( 'init', 'amap_register_ally_cpt' );

/**
 * 2. Meta Boxes
 */
function amap_add_meta_boxes() {
	add_meta_box('amap_config_meta', __('Configuración del Aliado', 'amap-domain'), 'amap_render_meta_box', 'amap_ally', 'normal', 'high');
}
add_action( 'add_meta_boxes', 'amap_add_meta_boxes' );

function amap_render_meta_box( $post ) {
	wp_nonce_field( 'amap_save_action', 'amap_nonce' );

	// Obtener datos
	$pin_t  = get_post_meta( $post->ID, '_amap_pin_top', true ) ?: '50';
	$pin_l  = get_post_meta( $post->ID, '_amap_pin_left', true ) ?: '50';
	$img_id = get_post_meta( $post->ID, '_amap_image_id', true );
	
	$l1 = get_post_meta( $post->ID, '_amap_label_1', true );
	$l2 = get_post_meta( $post->ID, '_amap_label_2', true );
	$l3 = get_post_meta( $post->ID, '_amap_label_3', true );

	// Preparar preview de imagen
	$img_url = '';
	if ( $img_id ) {
		$img_att = wp_get_attachment_image_src( $img_id, 'medium' );
		if ( $img_att ) $img_url = $img_att[0];
	}
	$map_url = AMAP_PLUGIN_URL . 'assets/images/map-placeholder.jpg';
	?>
	<div class="amap-admin-wrapper">
		<div class="amap-admin-controls">
			
			<h4>Logo del Aliado</h4>
			<div class="amap-image-uploader">
				<input type="hidden" name="amap_image_id" id="amap_image_id" value="<?php echo esc_attr($img_id); ?>">
				<div id="amap_image_preview" style="margin-bottom: 10px; width: 100px; height: 100px; background: #eee; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
					<?php if ( $img_url ) : ?>
						<img src="<?php echo esc_url($img_url); ?>" style="max-width:100%; max-height:100%;">
					<?php else : ?>
						<span style="color:#aaa;">Sin Imagen</span>
					<?php endif; ?>
				</div>
				<button type="button" class="button" id="amap_upload_btn"><?php _e('Elegir Imagen', 'amap-domain'); ?></button>
				<button type="button" class="button" id="amap_remove_btn" style="<?php echo $img_url ? '' : 'display:none;'; ?> color: #a00;"><?php _e('Quitar', 'amap-domain'); ?></button>
			</div>
			
			<hr>
			
			<h4>Etiquetas Informativas</h4>
			<label>Etiqueta 1: <input type="text" name="amap_label_1" value="<?php echo esc_attr($l1); ?>"></label>
			<label>Etiqueta 2: <input type="text" name="amap_label_2" value="<?php echo esc_attr($l2); ?>"></label>
			<label>Etiqueta 3: <input type="text" name="amap_label_3" value="<?php echo esc_attr($l3); ?>"></label>
			
			<hr>
			
			<h4>Ubicación Geográfica</h4>
			<p>Haz clic en el mapa para situar el punto.</p>
			<label>Top (%): <input type="number" step="0.1" id="amap_pin_top" name="amap_pin_top" value="<?php echo esc_attr($pin_t); ?>"></label>
			<label>Left (%): <input type="number" step="0.1" id="amap_pin_left" name="amap_pin_left" value="<?php echo esc_attr($pin_l); ?>"></label>
		</div>

		<div class="amap-admin-preview">
			<img src="<?php echo esc_url($map_url); ?>" class="amap-map-img">
			<div class="amap-admin-pin" style="top:<?php echo $pin_t; ?>%; left:<?php echo $pin_l; ?>%;"></div>
		</div>
	</div>
	<?php
}

function amap_save_meta( $post_id ) {
	if ( ! isset( $_POST['amap_nonce'] ) || ! wp_verify_nonce( $_POST['amap_nonce'], 'amap_save_action' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$keys = ['amap_pin_top', 'amap_pin_left', 'amap_image_id', 'amap_label_1', 'amap_label_2', 'amap_label_3'];
	foreach ( $keys as $key ) {
		// Guardamos incluso si está vacío para permitir borrado
		if ( isset( $_POST[$key] ) ) update_post_meta( $post_id, '_' . $key, sanitize_text_field( $_POST[$key] ) );
	}
}
add_action( 'save_post', 'amap_save_meta' );

/**
 * 3. Assets
 */
function amap_enqueue_assets( $hook ) {
	// Frontend
	if ( ! is_admin() ) {
		wp_enqueue_style( 'amap-style', AMAP_PLUGIN_URL . 'assets/css/amap-style.css', array(), '5.0' );
		wp_enqueue_script( 'amap-frontend-js', AMAP_PLUGIN_URL . 'assets/js/amap-frontend.js', array( 'jquery' ), '5.0', true );
	}
	
	// Backend
	global $post;
	if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && isset($post) && 'amap_ally' === $post->post_type ) {
		wp_enqueue_media(); // IMPORTANTE: Habilita el cargador de medios de WP
		wp_enqueue_style( 'amap-admin-css', AMAP_PLUGIN_URL . 'assets/admin/css/amap-admin.css', array(), '5.0' );
		wp_enqueue_script( 'amap-admin-js', AMAP_PLUGIN_URL . 'assets/admin/js/amap-admin.js', array( 'jquery' ), '5.0', true );
	}
}
add_action( 'admin_enqueue_scripts', 'amap_enqueue_assets' );
add_action( 'wp_enqueue_scripts', 'amap_enqueue_assets' );

/**
 * 4. Widget
 */
class AMAP_Grid_Map_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct( 'amap_grid_map', __( 'Mapa con Grid de Aliados', 'amap-domain' ) );
	}

	public function widget( $args, $instance ) {
		$map_url = AMAP_PLUGIN_URL . 'assets/images/map-placeholder.jpg';
		$allies = new WP_Query( array( 'post_type' => 'amap_ally', 'posts_per_page' => -1 ) );

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		if ( $allies->have_posts() ) : ?>
			
			<div class="amap-wrapper">
				<svg class="amap-global-svg"></svg>

				<div class="amap-grid-section">
					<?php while ( $allies->have_posts() ) : $allies->the_post(); 
						$id = get_the_ID();
						// Recuperar imagen personalizada
						$img_id = get_post_meta($id, '_amap_image_id', true);
						$img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';
						
						$l1 = get_post_meta($id, '_amap_label_1', true);
						$l2 = get_post_meta($id, '_amap_label_2', true);
						$l3 = get_post_meta($id, '_amap_label_3', true);
						?>
						<div class="amap-grid-item" data-id="<?php echo $id; ?>">
							<div class="amap-logo-box">
								<?php if($img_url): ?><img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title(); ?>"><?php else: ?><span>?</span><?php endif; ?>
							</div>
							
							<div class="amap-ally-name"><?php the_title(); ?></div>

							<div class="amap-tooltip-card">
								<h5><?php the_title(); ?></h5>
								<?php if($l1) echo "<span>$l1</span>"; ?>
								<?php if($l2) echo "<span>$l2</span>"; ?>
								<?php if($l3) echo "<span>$l3</span>"; ?>
							</div>
						</div>
					<?php endwhile; ?>
				</div>

				<div class="amap-map-section">
					<img src="<?php echo esc_url($map_url); ?>" class="amap-base-image">
					
					<?php while ( $allies->have_posts() ) : $allies->the_post(); 
						$id = get_the_ID();
						$pt = get_post_meta($id, '_amap_pin_top', true);
						$pl = get_post_meta($id, '_amap_pin_left', true);
						if($pt && $pl): ?>
							<div class="amap-pin" data-id="<?php echo $id; ?>" style="top:<?php echo $pt; ?>%; left:<?php echo $pl; ?>%;"></div>
						<?php endif;
					endwhile; ?>
				</div>

			</div>

		<?php endif; wp_reset_postdata();
		echo $args['after_widget'];
	}
	public function form( $instance ) {} 
	public function update( $new, $old ) { return $new; }
}
function amap_reg_widget() { register_widget( 'AMAP_Grid_Map_Widget' ); }
add_action( 'widgets_init', 'amap_reg_widget' );
?>