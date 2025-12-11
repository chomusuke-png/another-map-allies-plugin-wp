<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Widget del Mapa con Grid de Aliados.
 */
class AMAP_Grid_Map_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'amap_grid_map',
			__( 'Mapa con Grid de Aliados', 'amap-domain' ),
			array( 'description' => __( 'Muestra una cuadrícula de logos conectada a un mapa.', 'amap-domain' ) )
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

		if ( $allies_query->have_posts() ) : ?>
			
			<div class="amap-wrapper">
				<svg class="amap-global-svg"></svg>

				<div class="amap-grid-section">
					<?php while ( $allies_query->have_posts() ) : $allies_query->the_post(); 
						$id = get_the_ID();
						
						// Obtener imagen custom o placeholder
						$img_id  = get_post_meta( $id, '_amap_image_id', true );
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
						
						// Etiquetas
						$l1 = get_post_meta( $id, '_amap_label_1', true );
						$l2 = get_post_meta( $id, '_amap_label_2', true );
						$l3 = get_post_meta( $id, '_amap_label_3', true );
						?>
						
						<div class="amap-grid-item" data-id="<?php echo $id; ?>">
							
							<div class="amap-logo-box">
								<?php if ( $img_url ) : ?>
									<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title(); ?>">
								<?php else : ?>
									<span>?</span>
								<?php endif; ?>
							</div>
							
							<div class="amap-ally-name"><?php the_title(); ?></div>

							<div class="amap-tooltip-card">
								<h5><?php the_title(); ?></h5>
								<?php if ( $l1 ) echo '<span>' . esc_html( $l1 ) . '</span>'; ?>
								<?php if ( $l2 ) echo '<span>' . esc_html( $l2 ) . '</span>'; ?>
								<?php if ( $l3 ) echo '<span>' . esc_html( $l3 ) . '</span>'; ?>
							</div>
						</div>

					<?php endwhile; ?>
				</div>

				<div class="amap-map-section">
					<img src="<?php echo esc_url( $map_image_url ); ?>" class="amap-base-image" alt="World Map">
					
					<?php while ( $allies_query->have_posts() ) : $allies_query->the_post(); 
						$id = get_the_ID();
						$top  = get_post_meta( $id, '_amap_pin_top', true );
						$left = get_post_meta( $id, '_amap_pin_left', true );
						
						if ( $top !== '' && $left !== '' ) : ?>
							<div class="amap-pin" data-id="<?php echo $id; ?>" style="top: <?php echo esc_attr( $top ); ?>%; left: <?php echo esc_attr( $left ); ?>%;"></div>
						<?php endif;
					endwhile; ?>
				</div>

			</div>

		<?php endif; 
		wp_reset_postdata();
		
		echo $args['after_widget'];
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

// Registrar Widget
function amap_register_widget() {
	register_widget( 'AMAP_Grid_Map_Widget' );
}
add_action( 'widgets_init', 'amap_register_widget' );