jQuery(document).ready(function($) {
    // --- LÓGICA DEL MAPA ---
    const pin = $('.amap-admin-pin');
    const inputTop = $('#amap_pin_top');
    const inputLeft = $('#amap_pin_left');
    const countrySelect = $('#amap_country_selector');

    function updatePin() {
        pin.css({
            top: inputTop.val() + '%',
            left: inputLeft.val() + '%'
        });
    }

    // 1. Inputs Manuales
    inputTop.on('input', updatePin);
    inputLeft.on('input', updatePin);

    // 2. Clic en el Mapa
    $('.amap-admin-preview').on('click', function(e) {
        const offset = $(this).offset();
        const width = $(this).width();
        const height = $(this).height();
        const x = e.pageX - offset.left;
        const y = e.pageY - offset.top;

        const pX = ((x / width) * 100).toFixed(1);
        const pY = ((y / height) * 100).toFixed(1);

        inputLeft.val(pX);
        inputTop.val(pY);
        updatePin();
        
        // Resetear select si se mueve manualmente
        countrySelect.val('');
    });

    // 3. SELECTOR DE PAÍS (NUEVO)
    countrySelect.on('change', function() {
        const val = $(this).val();
        if(!val) return;

        const coords = val.split(',');
        const lat = parseFloat(coords[0]);
        const lon = parseFloat(coords[1]);

        // FÓRMULA PROYECCIÓN EQUIRECTANGULAR
        // Convertir Lat/Lon a Porcentajes de imagen
        // Top: 90 (Norte) a -90 (Sur). 90 = 0%, -90 = 100%
        // Left: -180 (Oeste) a 180 (Este). -180 = 0%, 180 = 100%
        
        let pTop = (90 - lat) / 180 * 100;
        let pLeft = (lon + 180) / 360 * 100;

        // Limitar
        pTop = Math.max(0, Math.min(100, pTop)).toFixed(1);
        pLeft = Math.max(0, Math.min(100, pLeft)).toFixed(1);

        inputTop.val(pTop);
        inputLeft.val(pLeft);
        updatePin();
    });

    // --- LÓGICA DE CARGA DE IMAGEN ---
    let frame;
    const uploadBtn = $('#amap_upload_btn');
    const removeBtn = $('#amap_remove_btn');
    const imgInput = $('#amap_image_id');
    const imgPreview = $('#amap_image_preview');

    uploadBtn.on('click', function(e) {
        e.preventDefault();
        if (frame) { frame.open(); return; }
        frame = wp.media({
            title: 'Seleccionar Logo del Aliado',
            button: { text: 'Usar esta imagen' },
            multiple: false
        });
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            imgInput.val(attachment.id);
            let url = attachment.url;
            if(attachment.sizes && attachment.sizes.thumbnail) url = attachment.sizes.thumbnail.url;
            imgPreview.html('<img src="'+url+'" style="max-width:100%; max-height:100%;">');
            removeBtn.show();
        });
        frame.open();
    });

    removeBtn.on('click', function() {
        imgInput.val('');
        imgPreview.html('<span style="color:#aaa;">Sin Imagen</span>');
        $(this).hide();
    });
});