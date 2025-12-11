jQuery(document).ready(function ($) {
    
    const container = $('.amap-admin-map-wrapper');
    const img = $('.amap-admin-map-image');
    const pin = $('.amap-admin-pin');
    
    // Inputs
    const inputTop = $('#amap_top_pos');
    const inputLeft = $('#amap_left_pos');

    // Al hacer clic en la imagen
    container.on('click', function (e) {
        // Obtener dimensiones actuales de la imagen
        const width = img.width();
        const height = img.height();
        
        // Obtener posición del clic relativa a la imagen
        const offset = $(this).offset();
        const clickX = e.pageX - offset.left;
        const clickY = e.pageY - offset.top;

        // Calcular porcentajes
        let percentX = (clickX / width) * 100;
        let percentY = (clickY / height) * 100;

        // Limitar a 0-100 por seguridad
        percentX = Math.max(0, Math.min(100, percentX));
        percentY = Math.max(0, Math.min(100, percentY));

        // Redondear a 2 decimales
        percentX = percentX.toFixed(2);
        percentY = percentY.toFixed(2);

        // Actualizar Inputs
        inputLeft.val(percentX);
        inputTop.val(percentY);

        // Mover el pin visualmente
        pin.css({
            left: percentX + '%',
            top: percentY + '%'
        });
    });

    // Si los inputs cambian manualmente, mover el pin
    $('#amap_top_pos, #amap_left_pos').on('input', function() {
        pin.css({
            top: inputTop.val() + '%',
            left: inputLeft.val() + '%'
        });
    });
});