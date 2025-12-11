<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Agrega el Meta Box principal.
 */
function amap_add_meta_boxes() {
	add_meta_box(
		'amap_config_meta',
		__( 'Configuración del Aliado', 'amap-domain' ),
		'amap_render_meta_box',
		'amap_ally',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'amap_add_meta_boxes' );

/**
 * Renderiza el HTML del Meta Box.
 */
function amap_render_meta_box( $post ) {
	wp_nonce_field( 'amap_save_action', 'amap_nonce' );

	// Obtener datos guardados
	$pin_top  = get_post_meta( $post->ID, '_amap_pin_top', true ) ?: '50';
	$pin_left = get_post_meta( $post->ID, '_amap_pin_left', true ) ?: '50';
	$image_id = get_post_meta( $post->ID, '_amap_image_id', true );
	
	$label1 = get_post_meta( $post->ID, '_amap_label_1', true );
	$label2 = get_post_meta( $post->ID, '_amap_label_2', true );
	$label3 = get_post_meta( $post->ID, '_amap_label_3', true );

	// Preparar preview de imagen
	$image_url = '';
	if ( $image_id ) {
		$img_att = wp_get_attachment_image_src( $image_id, 'medium' );
		if ( $img_att ) {
			$image_url = $img_att[0];
		}
	}
	
	$map_image_url = AMAP_PLUGIN_URL . 'assets/images/map-placeholder.jpg';
	?>
	
	<div class="amap-admin-wrapper">
		<div class="amap-admin-controls">
			
			<h4>Logo del Aliado</h4>
			<div class="amap-image-uploader">
				<input type="hidden" name="amap_image_id" id="amap_image_id" value="<?php echo esc_attr( $image_id ); ?>">
				
				<div id="amap_image_preview" style="margin-bottom: 10px; width: 100px; height: 100px; background: #f0f0f1; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; overflow: hidden;">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" style="max-width:100%; max-height:100%;">
					<?php else : ?>
						<span style="color:#aaa; font-size:12px;">Sin Imagen</span>
					<?php endif; ?>
				</div>
				
				<button type="button" class="button" id="amap_upload_btn"><?php _e( 'Elegir Imagen', 'amap-domain' ); ?></button>
				<button type="button" class="button" id="amap_remove_btn" style="<?php echo $image_url ? '' : 'display:none;'; ?> color: #b32d2e;"><?php _e( 'Quitar', 'amap-domain' ); ?></button>
			</div>
			
			<hr>
			
			<h4>Etiquetas Informativas</h4>
			<p class="description">Aparecen en la tarjeta flotante.</p>
			<label>Etiqueta 1: <input type="text" name="amap_label_1" value="<?php echo esc_attr( $label1 ); ?>" placeholder="Ej: País"></label>
			<label>Etiqueta 2: <input type="text" name="amap_label_2" value="<?php echo esc_attr( $label2 ); ?>" placeholder="Ej: Sector"></label>
			<label>Etiqueta 3: <input type="text" name="amap_label_3" value="<?php echo esc_attr( $label3 ); ?>" placeholder="Ej: Año"></label>
			
			<hr>
			
			<h4>Ubicación Geográfica</h4>
			<p class="description">Haz clic en el mapa para ubicar el pin.</p>
			<label>Top (%): <input type="number" step="0.1" id="amap_pin_top" name="amap_pin_top" value="<?php echo esc_attr( $pin_top ); ?>"></label>
			<label>Left (%): <input type="number" step="0.1" id="amap_pin_left" name="amap_pin_left" value="<?php echo esc_attr( $pin_left ); ?>"></label>
		</div>

		<div class="amap-admin-preview">
			<img src="<?php echo esc_url( $map_image_url ); ?>" class="amap-map-img">
			<div class="amap-admin-pin" style="top: <?php echo $pin_top; ?>%; left: <?php echo $pin_left; ?>%;"></div>
		</div>
	</div>
	<?php
}

/**
 * Guarda los datos del Meta Box.
 */
function amap_save_meta( $post_id ) {
	if ( ! isset( $_POST['amap_nonce'] ) || ! wp_verify_nonce( $_POST['amap_nonce'], 'amap_save_action' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array( 'amap_pin_top', 'amap_pin_left', 'amap_image_id', 'amap_label_1', 'amap_label_2', 'amap_label_3' );
	
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
}
add_action( 'save_post', 'amap_save_meta' );