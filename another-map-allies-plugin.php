<?php
/*
Plugin Name: Another Map Allies Plugin
Plugin URI: https://tusitio.com
Description: Muestra un mapa de imagen estática con puntos dinámicos de aliados mediante un Widget.
Version: 1.0
Author: Tu Nombre
Author URI: https://tusitio.com
License: GPL2
*/

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Definir rutas constantes
define( 'AMAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * 1. Registrar el Custom Post Type "Aliado"
 */
function amap_register_ally_cpt() {
	$labels = array(
		'name'               => _x( 'Aliados en Mapa', 'post type general name', 'amap-domain' ),
		'singular_name'      => _x( 'Aliado', 'post type singular name', 'amap-domain' ),
		'menu_name'          => _x( 'Aliados Mapa', 'admin menu', 'amap-domain' ),
		'name_admin_bar'     => _x( 'Aliado', 'add new on admin bar', 'amap-domain' ),
		'add_new'            => _x( 'Añadir Nuevo', 'ally', 'amap-domain' ),
		'add_new_item'       => __( 'Añadir Nuevo Aliado', 'amap-domain' ),
		'new_item'           => __( 'Nuevo Aliado', 'amap-domain' ),
		'edit_item'          => __( 'Editar Aliado', 'amap-domain' ),
		'view_item'          => __( 'Ver Aliado', 'amap-domain' ),
		'all_items'          => __( 'Todos los Aliados', 'amap-domain' ),
		'search_items'       => __( 'Buscar Aliados', 'amap-domain' ),
		'not_found'          => __( 'No se encontraron aliados.', 'amap-domain' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false, // No necesitan página propia en el frontend
		'publicly_queryable' => false,
		'show_ui'            => true, // Mostrar en el admin
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'aliado-mapa' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-location', // Icono de pin
		'supports'           => array( 'title' ), // Solo usamos el título para el nombre/ciudad
	);

	register_post_type( 'amap_ally', $args );
}
add_action( 'init', 'amap_register_ally_cpt' );


/**
 * 2. Agregar Meta Boxes para las Coordenadas (Top% y Left%)
 */
function amap_add_position_metaboxes() {
	add_meta_box(
		'amap_position_meta',
		__( 'Posición en el Mapa (Porcentajes)', 'amap-domain' ),
		'amap_position_meta_callback',
		'amap_ally',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'amap_add_position_metaboxes' );

// Callback para mostrar los campos en el editor
function amap_position_meta_callback( $post ) {
	wp_nonce_field( 'amap_save_position_meta', 'amap_position_meta_nonce' );

	$top_pos = get_post_meta( $post->ID, '_amap_top_pos', true );
	$left_pos = get_post_meta( $post->ID, '_amap_left_pos', true );
	?>
	<p>Introduce la posición del punto en porcentajes relativos a la imagen del mapa (0-100%).</p>
	<div style="display: flex; gap: 20px;">
		<div>
			<label for="amap_top_pos"><strong>Posición Superior (Top %):</strong></label><br>
			<input type="number" id="amap_top_pos" name="amap_top_pos" value="<?php echo esc_attr( $top_pos ); ?>" min="0" max="100" step="0.1" style="width: 80px;"> %
			<p class="description">Ej: 20% es cerca de la parte superior.</p>
		</div>
		<div>
			<label for="amap_left_pos"><strong>Posición Izquierda (Left %):</strong></label><br>
			<input type="number" id="amap_left_pos" name="amap_left_pos" value="<?php echo esc_attr( $left_pos ); ?>" min="0" max="100" step="0.1" style="width: 80px;"> %
			<p class="description">Ej: 50% es el centro horizontal.</p>
		</div>
	</div>
	<?php
}

// Guardar los datos de los meta boxes
function amap_save_position_meta( $post_id ) {
	if ( ! isset( $_POST['amap_position_meta_nonce'] ) || ! wp_verify_nonce( $_POST['amap_position_meta_nonce'], 'amap_save_position_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['amap_top_pos'] ) ) {
		update_post_meta( $post_id, '_amap_top_pos', sanitize_text_field( $_POST['amap_top_pos'] ) );
	}
	if ( isset( $_POST['amap_left_pos'] ) ) {
		update_post_meta( $post_id, '_amap_left_pos', sanitize_text_field( $_POST['amap_left_pos'] ) );
	}
}
add_action( 'save_post', 'amap_save_position_meta' );


/**
 * 3. Cargar estilos CSS en el frontend
 */
function amap_enqueue_styles() {
	wp_enqueue_style( 'amap-widget-style', AMAP_PLUGIN_URL . 'assets/css/amap-style.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'amap_enqueue_styles' );


/**
 * 4. Crear el Widget
 */
class AMAP_Allies_Map_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'amap_allies_map_widget', // Base ID
			__( 'Mapa de Aliados (Estático)', 'amap-domain' ), // Name
			array( 'description' => __( 'Muestra una imagen de mapa con puntos de ubicación de aliados.', 'amap-domain' ) ) // Args
		);
	}

	// Front-end display del widget
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Nuestros Aliados', 'amap-domain' );
		// URL de la imagen del mapa. Si quieres que el usuario la suba, esto se complicaría más.
		// Por ahora, asumimos que está en la carpeta assets/images del plugin.
		// PUEDES CAMBIAR ESTA URL SI SUBES LA IMAGEN A MEDIOS DE WP.
		$map_image_url = AMAP_PLUGIN_URL . 'assets/images/map-placeholder.jpg';

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		// Query para obtener los aliados
		$allies_query = new WP_Query( array(
			'post_type'      => 'amap_ally',
			'posts_per_page' => -1, // Mostrar todos
			'post_status'    => 'publish',
		) );

		if ( $allies_query->have_posts() ) : ?>
			
			<div class="amap-map-container" style="background-image: url('<?php echo esc_url($map_image_url); ?>');">
				<img src="<?php echo esc_url($map_image_url); ?>" alt="Mapa base" class="amap-base-image-hidden" style="opacity:0; pointer-events:none; width:100%; height:auto;">

				<?php while ( $allies_query->have_posts() ) : $allies_query->the_post();
					$top = get_post_meta( get_the_ID(), '_amap_top_pos', true );
					$left = get_post_meta( get_the_ID(), '_amap_left_pos', true );
					// Solo mostrar si tenemos coordenadas
					if ( $top !== '' && $left !== '' ) : ?>
						<div class="amap-map-point" style="top: <?php echo esc_attr( $top ); ?>%; left: <?php echo esc_attr( $left ); ?>%;">
							<div class="amap-tooltip"><?php the_title(); ?></div>
						</div>
					<?php endif;
				endwhile; wp_reset_postdata(); ?>
			</div>

		<?php else : ?>
			<p><?php _e( 'No hay aliados configurados aún.', 'amap-domain' ); ?></p>
		<?php endif;

		echo $args['after_widget'];
	}

	// Back-end widget form
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Nuestros Aliados', 'amap-domain' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p style="font-size: 12px; color: #666;">Nota: Asegúrate de subir tu imagen de mapa a la carpeta <code>assets/images/</code> del plugin con el nombre <code>map-placeholder.jpg</code>, o edita el código del plugin para cambiar la URL.</p>
		<?php
	}

	// Sanitize widget form values as they are saved
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		return $instance;
	}

} // class AMAP_Allies_Map_Widget

// Registrar el widget
function amap_register_widget() {
	register_widget( 'AMAP_Allies_Map_Widget' );
}
add_action( 'widgets_init', 'amap_register_widget' );
?>