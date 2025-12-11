jQuery(document).ready(function($) {
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

    // Actualizar al escribir manual
    inputTop.on('input', updatePin);
    inputLeft.on('input', updatePin);

    // Clic en el mapa
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
});