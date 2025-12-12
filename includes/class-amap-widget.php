<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Widget del Mapa con Aliados.
 * * Características:
 * 1. Balanceo de Carga: Distribuye aliados priorizando zonas horizontales (2:1).
 * 2. Ordenamiento Geométrico: Evita líneas cruzadas ordenando por coordenadas.
 * 3. Visibilidad: Muestra títulos en burbujas y tooltips en los pines del mapa.
 */
class AMAP_Grid_Map_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'amap_grid_map',
			__( 'Mapa con Aliados (Completo)', 'amap-domain' ),
			array( 'description' => __( 'Distribuye los aliados, ordena conexiones y muestra títulos.', 'amap-domain' ) )
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
		
		// --- TÍTULO DEL WIDGET ---
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
		}

		if ( $allies_query->have_posts() ) : 
			
			$posts = $allies_query->get_posts();
			$valid_posts = array();

			// 1. Filtrar posts válidos
			foreach ( $posts as $post ) {
				$t_val = get_post_meta( $post->ID, '_amap_pin_top', true );
				$l_val = get_post_meta( $post->ID, '_amap_pin_left', true );
				
				if ( $t_val !== '' && $l_val !== '' ) {
					$valid_posts[] = array(
						'post' => $post,
						'top'  => (float) $t_val,
						'left' => (float) $l_val
					);
				}
			}

			$total = count( $valid_posts );

			// 2. Definir Capacidades (Ratio 2:1 a favor de horizontal)
			$share_unit = $total / 6;
			$limit_horizontal = max( 1, ceil( $share_unit * 2 ) ); 
			$limit_vertical   = max( 1, ceil( $share_unit * 1 ) );

			$zones = array(
				'top'    => array(),
				'right'  => array(),
				'bottom' => array(),
				'left'   => array(),
			);

			// 3. Procesar distribución (Balanceo)
			foreach ( $valid_posts as $item ) {
				$top  = $item['top'];
				$left = $item['left'];

				$distances = array(
					'top'    => $top,
					'bottom' => 100 - $top,
					'left'   => $left,
					'right'  => 100 - $left
				);

				asort( $distances );

				$assigned = false;
				foreach ( $distances as $zone_name => $dist ) {
					$current_limit = ( $zone_name === 'top' || $zone_name === 'bottom' ) ? $limit_horizontal : $limit_vertical;

					if ( count( $zones[ $zone_name ] ) < $current_limit ) {
						$zones[ $zone_name ][] = $item; 
						$assigned = true;
						break;
					}
				}

				if ( ! $assigned ) {
					$first_choice = array_key_first( $distances );
					$zones[ $first_choice ][] = $item;
				}
			}

			// 4. ORDENAMIENTO (Evitar cruces)
			// Horizontales -> Ordenar por X (Left)
			usort( $zones['top'], function($a, $b) { return $a['left'] <=> $b['left']; } );
			usort( $zones['bottom'], function($a, $b) { return $a['left'] <=> $b['left']; } );
			// Verticales -> Ordenar por Y (Top)
			usort( $zones['left'], function($a, $b) { return $a['top'] <=> $b['top']; } );
			usort( $zones['right'], function($a, $b) { return $a['top'] <=> $b['top']; } );

			// Limpiar arrays para renderizado
			foreach ( $zones as $key => $items ) {
				$zones[ $key ] = array_column( $items, 'post' );
			}

			?>
			
			<div class="amap-wrapper">
				<svg class="amap-global-svg"></svg>

				<?php 
				$this->render_zone( 'top', $zones['top'] );
				$this->render_zone( 'left', $zones['left'] );
				?>
				
				<div class="amap-map-section">
					<img src="<?php echo esc_url( $map_image_url ); ?>" class="amap-base-image" alt="World Map">
					<?php 
					// Renderizar PINES
					foreach ( $valid_posts as $item ) {
						$p = $item['post'];
						$t = $item['top'];
						$l = $item['left'];
						// AGREGADO: title="..." para mostrar nombre al pasar el mouse por el pin
						echo '<div class="amap-pin" data-id="' . esc_attr( $p->ID ) . '" title="' . esc_attr( $p->post_title ) . '" style="top:' . esc_attr( $t ) . '%; left:' . esc_attr( $l ) . '%;"></div>';
					}
					?>
				</div>

				<?php
				$this->render_zone( 'right', $zones['right'] );
				$this->render_zone( 'bottom', $zones['bottom'] );
				?>

			</div>

		<?php endif; 
		wp_reset_postdata();
		
		echo $args['after_widget'];
	}

	private function render_zone( $zone_name, $posts ) {
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