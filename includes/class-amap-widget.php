<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Widget del Mapa con Aliados (Distribución Inteligente por Proximidad).
 */
class AMAP_Grid_Map_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'amap_grid_map',
			__( 'Mapa con Aliados (Inteligente)', 'amap-domain' ),
			array( 'description' => __( 'Distribuye los aliados en el borde más cercano a su ubicación geográfica.', 'amap-domain' ) )
		);
	}

	public function widget( $args, $instance ) {
		$map_image_url = AMAP_PLUGIN_URL . 'assets/images/map-placeholder.jpg';
		
		$allies_query = new WP_Query( array(
			'post_type'      => 'amap_ally',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		echo $args['before_widget'];
		
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
		}

		if ( $allies_query->have_posts() ) : 
			
			// Inicializar zonas
			$zones = array(
				'top'    => array(),
				'right'  => array(),
				'bottom' => array(),
				'left'   => array(),
			);
			
			$posts = $allies_query->get_posts();
			
			// --- ALGORITMO DE PROXIMIDAD ---
			foreach ( $posts as $post ) {
				$id = $post->ID;
				// Obtenemos coordenadas (si no existen, saltamos)
				$t_val = get_post_meta( $id, '_amap_pin_top', true );
				$l_val = get_post_meta( $id, '_amap_pin_left', true );

				if ( $t_val === '' || $l_val === '' ) {
					continue; 
				}

				$top  = (float) $t_val;
				$left = (float) $l_val;

				// Calcular distancia a los 4 bordes (0-100%)
				$dist_top    = $top;          // Distancia al borde superior (0)
				$dist_bottom = 100 - $top;    // Distancia al borde inferior (100)
				$dist_left   = $left;         // Distancia al borde izquierdo (0)
				$dist_right  = 100 - $left;   // Distancia al borde derecho (100)

				// Encontrar la distancia mínima
				$min_dist = min( $dist_top, $dist_bottom, $dist_left, $dist_right );

				// Asignar a la zona ganadora
				if ( $min_dist === $dist_top ) {
					$zones['top'][] = $post;
				} elseif ( $min_dist === $dist_bottom ) {
					$zones['bottom'][] = $post;
				} elseif ( $min_dist === $dist_left ) {
					$zones['left'][] = $post;
				} else {
					$zones['right'][] = $post;
				}
			}
			?>
			
			<div class="amap-wrapper">
				<svg class="amap-global-svg"></svg>

				<?php 
				// Renderizar Zona SUPERIOR
				$this->render_zone( 'top', $zones['top'] );
				
				// Renderizar Zona IZQUIERDA
				$this->render_zone( 'left', $zones['left'] );
				
				// --- MAPA CENTRAL ---
				?>
				<div class="amap-map-section">
					<img src="<?php echo esc_url( $map_image_url ); ?>" class="amap-base-image" alt="World Map">
					
					<?php 
					// Renderizar PINES
					foreach ( $posts as $post ) {
						$id = $post->ID;
						$top  = get_post_meta( $id, '_amap_pin_top', true );
						$left = get_post_meta( $id, '_amap_pin_left', true );
						
						if ( $top !== '' && $left !== '' ) {
							echo '<div class="amap-pin" data-id="' . esc_attr($id) . '" style="top:' . esc_attr($top) . '%; left:' . esc_attr($left) . '%;"></div>';
						}
					}
					?>
				</div>
				<?php
				
				// Renderizar Zona DERECHA
				$this->render_zone( 'right', $zones['right'] );
				
				// Renderizar Zona INFERIOR
				$this->render_zone( 'bottom', $zones['bottom'] );
				?>

			</div>

		<?php endif; 
		wp_reset_postdata();
		
		echo $args['after_widget'];
	}

	/**
	 * Renderiza una zona específica con sus aliados.
	 */
	private function render_zone( $zone_name, $posts ) {
		// Agregamos la clase de zona para el CSS Grid
		echo '<div class="amap-zone amap-zone-' . esc_attr( $zone_name ) . '">';
		
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$id = $post->ID;
				$img_id  = get_post_meta( $id, '_amap_image_id', true );
				$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				
				$l1 = get_post_meta( $id, '_amap_label_1', true );
				$l2 = get_post_meta( $id, '_amap_label_2', true );
				$l3 = get_post_meta( $id, '_amap_label_3', true );
				?>
				<div class="amap-grid-item" data-id="<?php echo $id; ?>">
					<div class="amap-logo-box">
						<?php if ( $img_url ) : ?>
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $post->post_title ); ?>">
						<?php else : ?>
							<span>?</span>
						<?php endif; ?>
					</div>
					<div class="amap-ally-name"><?php echo esc_html( $post->post_title ); ?></div>
					
					<div class="amap-tooltip-card">
						<h5><?php echo esc_html( $post->post_title ); ?></h5>
						<?php if ( $l1 ) echo '<span>' . esc_html( $l1 ) . '</span>'; ?>
						<?php if ( $l2 ) echo '<span>' . esc_html( $l2 ) . '</span>'; ?>
						<?php if ( $l3 ) echo '<span>' . esc_html( $l3 ) . '</span>'; ?>
					</div>
				</div>
				<?php
			}
		}
		
		echo '</div>';
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Nuestros Aliados', 'amap-domain' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		return $instance;
	}
}

function amap_register_widget() {
	register_widget( 'AMAP_Grid_Map_Widget' );
}
add_action( 'widgets_init', 'amap_register_widget' );