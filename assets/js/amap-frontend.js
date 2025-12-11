jQuery(document).ready(function($) {
    
    function drawLines() {
        const wrapper = $('.amap-wrapper');
        const svg = wrapper.find('.amap-global-svg');
        
        svg.empty();

        $('.amap-pin').each(function() {
            const pin = $(this);
            const id = pin.data('id');
            // Buscamos el item de la grid
            const gridItem = wrapper.find(`.amap-grid-item[data-id="${id}"]`);

            if (gridItem.length) {
                // Buscamos ESPECÍFICAMENTE la caja del logo dentro del item
                // para que la línea salga del círculo y no del texto
                const logoBox = gridItem.find('.amap-logo-box');
                
                const wrapperOffset = wrapper.offset();
                
                // Centro del Pin
                const pinPos = pin.offset();
                const pinX = pinPos.left - wrapperOffset.left + (pin.width() / 2);
                const pinY = pinPos.top - wrapperOffset.top + (pin.height() / 2);

                // Centro inferior del Logo (Círculo)
                const logoPos = logoBox.offset();
                const logoX = logoPos.left - wrapperOffset.left + (logoBox.width() / 2);
                // + logoBox.height() hace que salga de abajo del círculo
                const logoY = logoPos.top - wrapperOffset.top + logoBox.height(); 

                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', logoX);
                line.setAttribute('y1', logoY);
                line.setAttribute('x2', pinX);
                line.setAttribute('y2', pinY);
                line.setAttribute('class', 'amap-connector');
                line.setAttribute('data-id', id);
                
                svg.append(line);
            }
        });
    }

    setTimeout(drawLines, 200); // Un poco más de delay para asegurar carga de imgs
    $(window).on('load resize', drawLines);

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