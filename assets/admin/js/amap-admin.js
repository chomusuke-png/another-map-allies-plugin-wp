jQuery(document).ready(function($) {
    // --- LÓGICA DEL MAPA (Existente) ---
    const mapImg = $('.amap-map-img');
    const pin = $('.amap-admin-pin');
    const inputTop = $('#amap_pin_top');
    const inputLeft = $('#amap_pin_left');

    function updatePin() {
        pin.css({
            top: inputTop.val() + '%',
            left: inputLeft.val() + '%'
        });
    }

    inputTop.on('input', updatePin);
    inputLeft.on('input', updatePin);

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
    });

    // --- LÓGICA DE CARGA DE IMAGEN (Nueva) ---
    let frame;
    const uploadBtn = $('#amap_upload_btn');
    const removeBtn = $('#amap_remove_btn');
    const imgInput = $('#amap_image_id');
    const imgPreview = $('#amap_image_preview');

    uploadBtn.on('click', function(e) {
        e.preventDefault();

        // Si el frame ya existe, ábrelo
        if (frame) {
            frame.open();
            return;
        }

        // Crear frame de media
        frame = wp.media({
            title: 'Seleccionar Logo del Aliado',
            button: { text: 'Usar esta imagen' },
            multiple: false
        });

        // Al seleccionar una imagen
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            imgInput.val(attachment.id);
            
            // Mostrar preview
            let url = attachment.url;
            if(attachment.sizes && attachment.sizes.thumbnail) {
                url = attachment.sizes.thumbnail.url;
            }
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