<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Clase para gestionar la Importación y Exportación de Aliados via CSV.
 */
class AMAP_Import_Export {

	/**
	 * Constructor. Inicia los hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_impexp_submenu' ) );
		add_action( 'admin_init', array( $this, 'handle_export_action' ) );
		add_action( 'admin_init', array( $this, 'handle_import_action' ) );
	}

	/**
	 * Registra el submenú en el panel de administración.
	 */
	public function register_impexp_submenu() {
		add_submenu_page(
			'edit.php?post_type=amap_ally',
			__( 'Importar / Exportar', 'amap-domain' ),
			__( 'Importar / Exportar', 'amap-domain' ),
			'manage_options',
			'amap-impexp',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Procesa la solicitud de exportación (genera descarga de CSV).
	 */
	public function handle_export_action() {
		if ( ! isset( $_POST['amap_action'] ) || 'export_csv' !== $_POST['amap_action'] ) {
			return;
		}

		check_admin_referer( 'amap_export_nonce', 'amap_nonce_field' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filename = 'aliados-export-' . date( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );

		// Encabezados del CSV
		fputcsv( $output, array( 'ID', 'Title', 'Top (%)', 'Left (%)', 'Label 1', 'Label 2', 'Label 3', 'Image URL' ) );

		$args = array(
			'post_type'      => 'amap_ally',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		);

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$id = get_the_ID();
				
				$img_id  = get_post_meta( $id, '_amap_image_id', true );
				$img_url = $img_id ? wp_get_attachment_url( $img_id ) : '';

				$row = array(
					$id,
					get_the_title(),
					get_post_meta( $id, '_amap_pin_top', true ),
					get_post_meta( $id, '_amap_pin_left', true ),
					get_post_meta( $id, '_amap_label_1', true ),
					get_post_meta( $id, '_amap_label_2', true ),
					get_post_meta( $id, '_amap_label_3', true ),
					$img_url
				);

				fputcsv( $output, $row );
			}
		}
		
		fclose( $output );
		exit;
	}

	/**
	 * Procesa la solicitud de importación (lee CSV y crea/actualiza posts).
	 */
	public function handle_import_action() {
		if ( ! isset( $_POST['amap_action'] ) || 'import_csv' !== $_POST['amap_action'] ) {
			return;
		}

		check_admin_referer( 'amap_import_nonce', 'amap_nonce_field' );

		if ( ! isset( $_FILES['amap_import_file'] ) || empty( $_FILES['amap_import_file']['tmp_name'] ) ) {
			add_action( 'admin_notices', function() { echo '<div class="error"><p>Por favor selecciona un archivo.</p></div>'; } );
			return;
		}

		$file = $_FILES['amap_import_file']['tmp_name'];
		$handle = fopen( $file, 'r' );

		if ( $handle !== FALSE ) {
			$row_count = 0;
			// Omitir encabezados
			fgetcsv( $handle, 1000, ',' );

			while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE ) {
				// Mapeo de columnas según el orden del export:
				// 0: ID, 1: Title, 2: Top, 3: Left, 4: L1, 5: L2, 6: L3, 7: ImgURL
				
				$post_id     = isset( $data[0] ) ? intval( $data[0] ) : 0;
				$title       = isset( $data[1] ) ? sanitize_text_field( $data[1] ) : 'Sin Titulo';
				$top         = isset( $data[2] ) ? sanitize_text_field( $data[2] ) : '';
				$left        = isset( $data[3] ) ? sanitize_text_field( $data[3] ) : '';
				$l1          = isset( $data[4] ) ? sanitize_text_field( $data[4] ) : '';
				$l2          = isset( $data[5] ) ? sanitize_text_field( $data[5] ) : '';
				$l3          = isset( $data[6] ) ? sanitize_text_field( $data[6] ) : '';
				$img_url_csv = isset( $data[7] ) ? esc_url_raw( $data[7] ) : '';

				$post_data = array(
					'post_type'   => 'amap_ally',
					'post_title'  => $title,
					'post_status' => 'publish',
				);

				// Si existe ID y el post es válido, actualizamos; si no, creamos.
				if ( $post_id > 0 && get_post( $post_id ) ) {
					$post_data['ID'] = $post_id;
					wp_update_post( $post_data );
				} else {
					$post_id = wp_insert_post( $post_data );
				}

				if ( $post_id ) {
					update_post_meta( $post_id, '_amap_pin_top', $top );
					update_post_meta( $post_id, '_amap_pin_left', $left );
					update_post_meta( $post_id, '_amap_label_1', $l1 );
					update_post_meta( $post_id, '_amap_label_2', $l2 );
					update_post_meta( $post_id, '_amap_label_3', $l3 );

					// Procesamiento de Imagen (Si hay URL y es diferente a la actual)
					if ( ! empty( $img_url_csv ) ) {
						$this->process_image_import( $post_id, $img_url_csv );
					}
					$row_count++;
				}
			}
			fclose( $handle );
			
			add_action( 'admin_notices', function() use ( $row_count ) { 
				echo '<div class="updated"><p>Importación completada. ' . $row_count . ' aliados procesados.</p></div>'; 
			});
		}
	}

	/**
	 * Descarga la imagen desde una URL y la asigna al post como attachment.
	 * * @param int $post_id ID del aliado.
	 * @param string $image_url URL de la imagen.
	 */
	private function process_image_import( $post_id, $image_url ) {
		// Verificar si la URL ya es de este sitio (evitar re-subir lo mismo)
		$upload_dir = wp_upload_dir();
		if ( strpos( $image_url, $upload_dir['baseurl'] ) !== false ) {
			// Es una imagen local, intentamos buscar su ID por URL (costoso pero necesario si solo tenemos URL)
			$attachment_id = attachment_url_to_postid( $image_url );
			if ( $attachment_id ) {
				update_post_meta( $post_id, '_amap_image_id', $attachment_id );
				return;
			}
		}

		// Si es externa, usamos media_sideload_image
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Retorna URL html o WP_Error
		$sideload = media_sideload_image( $image_url, $post_id, null, 'id' );

		if ( ! is_wp_error( $sideload ) ) {
			update_post_meta( $post_id, '_amap_image_id', $sideload );
		}
	}

	/**
	 * Renderiza la vista HTML de la página de administración.
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Importar / Exportar Aliados', 'amap-domain' ); ?></h1>
			<p>Gestiona masivamente tus aliados mediante archivos CSV (Excel).</p>
			
			<div style="display: flex; gap: 40px; margin-top: 20px;">
				<div style="background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 400px; flex: 1;">
					<h2>Exportar Aliados</h2>
					<p>Descarga un archivo CSV con todos los aliados actuales, sus coordenadas y URLs de imágenes.</p>
					<form method="post">
						<?php wp_nonce_field( 'amap_export_nonce', 'amap_nonce_field' ); ?>
						<input type="hidden" name="amap_action" value="export_csv">
						<?php submit_button( 'Descargar CSV', 'primary' ); ?>
					</form>
				</div>

				<div style="background: #fff; padding: 20px; border: 1px solid #ccc; max-width: 400px; flex: 1;">
					<h2>Importar Aliados</h2>
					<p>Sube un archivo CSV para crear o actualizar aliados.</p>
					<ul style="font-size: 12px; color: #666; list-style: disc; margin-left: 15px;">
						<li>Si dejas la columna <strong>ID</strong> vacía, se creará un aliado nuevo.</li>
						<li>Si pones un <strong>ID</strong> existente, se actualizarán sus datos.</li>
						<li>En <strong>Image URL</strong>, puedes poner un enlace directo (ej: https://misitio.com/logo.png). El sistema intentará descargarla.</li>
					</ul>
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'amap_import_nonce', 'amap_nonce_field' ); ?>
						<input type="hidden" name="amap_action" value="import_csv">
						<input type="file" name="amap_import_file" accept=".csv" required style="margin-bottom: 10px;">
						<?php submit_button( 'Subir y Procesar', 'secondary' ); ?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}
}

// Instanciar
new AMAP_Import_Export();