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

	$pin_top  = get_post_meta( $post->ID, '_amap_pin_top', true ) ?: '50';
	$pin_left = get_post_meta( $post->ID, '_amap_pin_left', true ) ?: '50';
	$image_id = get_post_meta( $post->ID, '_amap_image_id', true );
	
	$label1 = get_post_meta( $post->ID, '_amap_label_1', true );
	$label2 = get_post_meta( $post->ID, '_amap_label_2', true );
	$label3 = get_post_meta( $post->ID, '_amap_label_3', true );

	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
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
			<label>Etiqueta 1: <input type="text" name="amap_label_1" value="<?php echo esc_attr( $label1 ); ?>" placeholder="Ej: País"></label>
			<label>Etiqueta 2: <input type="text" name="amap_label_2" value="<?php echo esc_attr( $label2 ); ?>" placeholder="Ej: Sector"></label>
			<label>Etiqueta 3: <input type="text" name="amap_label_3" value="<?php echo esc_attr( $label3 ); ?>" placeholder="Ej: Año"></label>
			
			<hr>
			
			<h4>Ubicación Geográfica</h4>
			<p class="description">Selecciona un país de la lista para ubicarlo automáticamente o haz clic en el mapa.</p>
			
			<div style="margin-bottom: 15px;">
				<label for="amap_country_selector"><strong>Ubicación Rápida:</strong></label>
				<select id="amap_country_selector" style="width: 100%;">
					<option value="">-- Seleccionar País --</option>
					
					<optgroup label="América del Sur">
						<option value="-34,-64">Argentina</option>
						<option value="-16,-68">Bolivia</option>
						<option value="-14,-51">Brasil</option>
						<option value="-33,-70">Chile</option>
						<option value="4,-74">Colombia</option>
						<option value="-1,-78">Ecuador</option>
						<option value="5,-58">Guyana</option>
						<option value="-23,-58">Paraguay</option>
						<option value="-9,-75">Perú</option>
						<option value="3,-56">Surinam</option>
						<option value="-32,-55">Uruguay</option>
						<option value="6,-66">Venezuela</option>
					</optgroup>

					<optgroup label="América del Norte y Central">
						<option value="17,-61">Antigua y Barbuda</option>
						<option value="25,-77">Bahamas</option>
						<option value="13,-59">Barbados</option>
						<option value="17,-88">Belice</option>
						<option value="56,-106">Canadá</option>
						<option value="9,-83">Costa Rica</option>
						<option value="21,-77">Cuba</option>
						<option value="15,-61">Dominica</option>
						<option value="13,-88">El Salvador</option>
						<option value="37,-95">Estados Unidos</option>
						<option value="12,-61">Granada</option>
						<option value="15,-90">Guatemala</option>
						<option value="18,-72">Haití</option>
						<option value="15,-86">Honduras</option>
						<option value="18,-77">Jamaica</option>
						<option value="23,-102">México</option>
						<option value="12,-86">Nicaragua</option>
						<option value="8,-80">Panamá</option>
						<option value="18,-70">República Dominicana</option>
						<option value="17,-62">San Cristóbal y Nieves</option>
						<option value="13,-60">Santa Lucía</option>
						<option value="10,-61">Trinidad y Tobago</option>
					</optgroup>

					<optgroup label="Europa">
						<option value="41,20">Albania</option>
						<option value="51,10">Alemania</option>
						<option value="42,1">Andorra</option>
						<option value="47,14">Austria</option>
						<option value="50,4">Bélgica</option>
						<option value="53,27">Bielorrusia</option>
						<option value="43,17">Bosnia y Herzegovina</option>
						<option value="42,25">Bulgaria</option>
						<option value="45,15">Croacia</option>
						<option value="56,10">Dinamarca</option>
						<option value="48,17">Eslovaquia</option>
						<option value="46,14">Eslovenia</option>
						<option value="40,-3">España</option>
						<option value="58,26">Estonia</option>
						<option value="61,24">Finlandia</option>
						<option value="46,2">Francia</option>
						<option value="39,22">Grecia</option>
						<option value="47,19">Hungría</option>
						<option value="53,-8">Irlanda</option>
						<option value="64,-19">Islandia</option>
						<option value="41,12">Italia</option>
						<option value="56,24">Letonia</option>
						<option value="47,9">Liechtenstein</option>
						<option value="55,23">Lituania</option>
						<option value="49,6">Luxemburgo</option>
						<option value="41,21">Macedonia del Norte</option>
						<option value="35,14">Malta</option>
						<option value="47,28">Moldavia</option>
						<option value="42,19">Montenegro</option>
						<option value="43,12">Mónaco</option>
						<option value="60,8">Noruega</option>
						<option value="52,5">Países Bajos</option>
						<option value="51,19">Polonia</option>
						<option value="39,-8">Portugal</option>
						<option value="55,-3">Reino Unido</option>
						<option value="49,15">República Checa</option>
						<option value="45,24">Rumania</option>
						<option value="61,105">Rusia</option>
						<option value="43,12">San Marino</option>
						<option value="44,20">Serbia</option>
						<option value="60,18">Suecia</option>
						<option value="46,8">Suiza</option>
						<option value="48,31">Ucrania</option>
						<option value="41,12">Vaticano</option>
					</optgroup>

					<optgroup label="Asia">
						<option value="33,65">Afganistán</option>
						<option value="23,46">Arabia Saudita</option>
						<option value="40,47">Armenia</option>
						<option value="40,47">Azerbaiyán</option>
						<option value="23,90">Bangladesh</option>
						<option value="26,50">Baréin</option>
						<option value="27,90">Bután</option>
						<option value="4,114">Brunéi</option>
						<option value="12,104">Camboya</option>
						<option value="25,51">Catar</option>
						<option value="35,104">China</option>
						<option value="38,127">Corea del Norte</option>
						<option value="35,127">Corea del Sur</option>
						<option value="23,53">Emiratos Árabes Unidos</option>
						<option value="12,121">Filipinas</option>
						<option value="42,43">Georgia</option>
						<option value="20,78">India</option>
						<option value="-0,113">Indonesia</option>
						<option value="33,43">Irak</option>
						<option value="32,53">Irán</option>
						<option value="31,34">Israel</option>
						<option value="36,138">Japón</option>
						<option value="30,35">Jordania</option>
						<option value="48,66">Kazajistán</option>
						<option value="41,74">Kirguistán</option>
						<option value="29,47">Kuwait</option>
						<option value="19,102">Laos</option>
						<option value="33,35">Líbano</option>
						<option value="4,101">Malasia</option>
						<option value="3,73">Maldivas</option>
						<option value="46,103">Mongolia</option>
						<option value="21,95">Myanmar (Birmania)</option>
						<option value="28,84">Nepal</option>
						<option value="21,55">Omán</option>
						<option value="30,69">Pakistán</option>
						<option value="31,35">Palestina</option>
						<option value="1,103">Singapur</option>
						<option value="34,38">Siria</option>
						<option value="7,80">Sri Lanka</option>
						<option value="15,100">Tailandia</option>
						<option value="38,71">Tayikistán</option>
						<option value="-8,125">Timor Oriental</option>
						<option value="38,35">Turquía</option>
						<option value="41,64">Uzbekistán</option>
						<option value="14,108">Vietnam</option>
						<option value="15,48">Yemen</option>
					</optgroup>

					<optgroup label="Oceanía">
						<option value="-25,133">Australia</option>
						<option value="-17,178">Fiyi</option>
						<option value="7,134">Islas Marshall</option>
						<option value="-9,159">Islas Salomón</option>
						<option value="1,173">Kiribati</option>
						<option value="7,150">Micronesia</option>
						<option value="-0,166">Nauru</option>
						<option value="-40,174">Nueva Zelanda</option>
						<option value="7,134">Palaos</option>
						<option value="-6,143">Papúa Nueva Guinea</option>
						<option value="-13,-172">Samoa</option>
						<option value="-21,-175">Tonga</option>
						<option value="-8,178">Tuvalu</option>
						<option value="-15,166">Vanuatu</option>
					</optgroup>

					<optgroup label="África">
						<option value="-11,17">Angola</option>
						<option value="28,1">Argelia</option>
						<option value="9,2">Benín</option>
						<option value="-22,24">Botsuana</option>
						<option value="12,-1">Burkina Faso</option>
						<option value="-3,29">Burundi</option>
						<option value="16,-24">Cabo Verde</option>
						<option value="3,11">Camerún</option>
						<option value="15,19">Chad</option>
						<option value="-0,15">Congo</option>
						<option value="7,-5">Costa de Marfil</option>
						<option value="26,30">Egipto</option>
						<option value="15,39">Eritrea</option>
						<option value="9,40">Etiopía</option>
						<option value="-0,9">Gabón</option>
						<option value="13,-15">Gambia</option>
						<option value="7,-1">Ghana</option>
						<option value="9,-9">Guinea</option>
						<option value="11,-15">Guinea-Bisáu</option>
						<option value="1,10">Guinea Ecuatorial</option>
						<option value="0,37">Kenia</option>
						<option value="-29,27">Lesoto</option>
						<option value="6,-9">Liberia</option>
						<option value="26,17">Libia</option>
						<option value="-18,47">Madagascar</option>
						<option value="-13,34">Malaui</option>
						<option value="17,-4">Malí</option>
						<option value="31,-7">Marruecos</option>
						<option value="-20,57">Mauricio</option>
						<option value="21,-11">Mauritania</option>
						<option value="-22,18">Namibia</option>
						<option value="17,8">Níger</option>
						<option value="9,8">Nigeria</option>
						<option value="4,18">República Centroafricana</option>
						<option value="-4,21">República Democrática del Congo</option>
						<option value="-1,29">Ruanda</option>
						<option value="0,6">Santo Tomé y Príncipe</option>
						<option value="14,-14">Senegal</option>
						<option value="-4,55">Seychelles</option>
						<option value="8,-11">Sierra Leona</option>
						<option value="5,46">Somalia</option>
						<option value="-26,31">Suazilandia</option>
						<option value="-30,25">Sudáfrica</option>
						<option value="12,30">Sudán</option>
						<option value="6,31">Sudán del Sur</option>
						<option value="-6,34">Tanzania</option>
						<option value="8,0">Togo</option>
						<option value="33,9">Túnez</option>
						<option value="1,32">Uganda</option>
						<option value="11,42">Yibuti</option>
						<option value="-13,27">Zambia</option>
						<option value="-19,29">Zimbabue</option>
					</optgroup>
				</select>
			</div>

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

function amap_save_meta( $post_id ) {
	if ( ! isset( $_POST['amap_nonce'] ) || ! wp_verify_nonce( $_POST['amap_nonce'], 'amap_save_action' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = array( 'amap_pin_top', 'amap_pin_left', 'amap_image_id', 'amap_label_1', 'amap_label_2', 'amap_label_3' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
	}
}
add_action( 'save_post', 'amap_save_meta' );