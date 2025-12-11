jQuery(document).ready(function($) {
    
    function drawLines() {
        const wrapper = $('.amap-wrapper');
        const svg = wrapper.find('.amap-global-svg');
        
        // Limpiar líneas anteriores
        svg.empty();

        // Iterar sobre cada Pin en el mapa
        $('.amap-pin').each(function() {
            const pin = $(this);
            const id = pin.data('id');
            const logo = wrapper.find(`.amap-grid-item[data-id="${id}"]`);

            if (logo.length) {
                // Obtener posiciones relativas al Wrapper
                const wrapperOffset = wrapper.offset();
                
                // Centro del Pin
                const pinPos = pin.offset();
                const pinX = pinPos.left - wrapperOffset.left + (pin.width() / 2);
                const pinY = pinPos.top - wrapperOffset.top + (pin.height() / 2);

                // Centro del Logo (Parte inferior para conectar)
                const logoPos = logo.offset();
                const logoX = logoPos.left - wrapperOffset.left + (logo.width() / 2);
                const logoY = logoPos.top - wrapperOffset.top + logo.height(); // Conectar desde abajo del logo

                // Crear línea SVG
                // Usamos clases para controlar la visibilidad con CSS
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', logoX);
                line.setAttribute('y1', logoY);
                line.setAttribute('x2', pinX);
                line.setAttribute('y2', pinY);
                line.setAttribute('class', 'amap-connector');
                line.setAttribute('data-id', id); // Para hover
                
                svg.append(line);
            }
        });
    }

    // Dibujar al cargar
    // Pequeño delay para asegurar carga de imágenes
    setTimeout(drawLines, 100);
    $(window).on('load', drawLines);
    
    // Redibujar al cambiar tamaño de ventana
    $(window).on('resize', function(){
        drawLines();
    });

    // Efectos Hover (Sincronizar Logo <-> Pin <-> Línea)
    // Cuando pasas el mouse por el Item de la Grid
    $('.amap-grid-item').hover(
        function() {
            const id = $(this).data('id');
            $(`.amap-pin[data-id="${id}"]`).addClass('active');
            $(`.amap-connector[data-id="${id}"]`).addClass('active');
        },
        function() {
            const id = $(this).data('id');
            $(`.amap-pin[data-id="${id}"]`).removeClass('active');
            $(`.amap-connector[data-id="${id}"]`).removeClass('active');
        }
    );
});