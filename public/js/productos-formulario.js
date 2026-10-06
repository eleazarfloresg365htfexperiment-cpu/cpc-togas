/*
 * Formulario de productos (crear y editar).
 *
 * Qué hace:
 *  - Crear: muestra solo los detalles del tipo de producto elegido y calcula
 *    la vista previa del stock inicial.
 *  - Crear y editar: ofrece los colores según el tipo (collarín y borla)
 *    y completa los códigos de color automáticamente (collarín, capa y borla).
 *
 * Los valores (colores, códigos, carreras) llegan desde config/alquiler.php en
 * window.CPC_PRODUCTOS, para que el servidor y la pantalla usen la misma tabla.
 *
 * Regla para no pisar datos: al CAMBIAR un color o carrera se escribe siempre
 * el código que le corresponde; al CARGAR la página solo se rellena un campo
 * si está vacío (así no se cambia un dato ya guardado ni lo que la persona
 * escribió antes de que el formulario volviera con errores).
 */
(function () {
    'use strict';

    // Bloque de detalles de cada tipo de producto.
    var BLOQUES = {
        TOGA: 'campos-toga',
        CAPA: 'campos-capa',
        BIRRETE: 'campos-birrete',
        COLLARIN: 'campos-collarin',
        BORLA: 'campos-borla'
    };

    document.addEventListener('DOMContentLoaded', function () {
        var formulario = document.getElementById('formProducto');

        if (!formulario) {
            return;
        }

        var config = window.CPC_PRODUCTOS || {};

        if (formulario.dataset.modo === 'crear') {
            iniciarSeleccionDeTipo();
            iniciarVistaPreviaDeStock();
        }

        iniciarCollarin(config);
        iniciarCapa(config);
        iniciarBorla(config);
    });

    /**
     * Escribe un valor en un campo. Con `forzar` lo reemplaza siempre; sin
     * `forzar` solo si el campo está vacío.
     */
    function escribir(campo, valor, forzar) {
        if (!campo) {
            return;
        }

        if (forzar || campo.value.trim() === '') {
            campo.value = valor;
        }
    }

    // ------------------------------------------------------------------
    // Crear: mostrar solo los detalles del tipo elegido
    // ------------------------------------------------------------------
    function iniciarSeleccionDeTipo() {
        var selector = document.getElementById('tipo_producto');
        var mensaje = document.getElementById('mensaje-detalles');
        var colorCollarin = document.getElementById('color_collarin');

        if (!selector) {
            return;
        }

        function mostrarSegunTipo() {
            var tipo = selector.value;

            Object.keys(BLOQUES).forEach(function (nombreTipo) {
                var bloque = document.getElementById(BLOQUES[nombreTipo]);

                if (!bloque) {
                    return;
                }

                var activo = nombreTipo === tipo;

                bloque.classList.toggle('d-none', !activo);

                // Los campos de un tipo que no se eligió no se envían.
                bloque.querySelectorAll('input, select, textarea').forEach(function (campo) {
                    campo.disabled = !activo;

                    if (!activo) {
                        campo.removeAttribute('required');
                    }
                });
            });

            if (tipo && mensaje) {
                mensaje.classList.add('d-none');
            }

            if (tipo === 'COLLARIN' && colorCollarin) {
                colorCollarin.setAttribute('required', 'required');
            }

            // Solo las togas tienen precio; los accesorios quedan en 0.
            var grupoPrecio = document.getElementById('grupo-precio');
            var precio = document.getElementById('precio_alquiler');
            var esAccesorio = tipo !== '' && tipo !== 'TOGA';

            if (grupoPrecio) {
                grupoPrecio.classList.toggle('d-none', esAccesorio);
            }

            if (precio && esAccesorio) {
                precio.value = 0;
            }
        }

        selector.addEventListener('change', mostrarSegunTipo);
        mostrarSegunTipo();
    }

    // ------------------------------------------------------------------
    // Crear: vista previa del stock inicial
    // ------------------------------------------------------------------
    function iniciarVistaPreviaDeStock() {
        var entrada = document.getElementById('stock_total');
        var total = document.getElementById('preview_stock_total');
        var disponible = document.getElementById('preview_stock_disponible');
        var alquilado = document.getElementById('preview_stock_alquilado');

        if (!entrada) {
            return;
        }

        function actualizar() {
            var stock = parseInt(entrada.value, 10);

            if (isNaN(stock) || stock < 0) {
                stock = 0;
            }

            if (total) {
                total.textContent = stock;
            }

            if (disponible) {
                disponible.textContent = stock;
            }

            if (alquilado) {
                alquilado.textContent = 0;
            }
        }

        entrada.addEventListener('input', actualizar);
        entrada.addEventListener('change', actualizar);
        actualizar();
    }

    // ------------------------------------------------------------------
    // Collarín y borla: los colores dependen del tipo (normal o
    // universitario) y cada color tiene su código.
    // ------------------------------------------------------------------
    function iniciarCollarin(config) {
        iniciarColorPorTipo({
            nombreTipo: 'tipo_collarin',
            color: document.getElementById('color_collarin'),
            codigo: document.getElementById('codigo_color_collarin'),
            // { NORMAL: { Dorado: 'C-DO', ... }, UNIVERSITARIO: { Azul: 'C-AZ' } }
            porTipo: config.coloresCollarin || {}
        });
    }

    function iniciarBorla(config) {
        iniciarColorPorTipo({
            nombreTipo: 'tipo_borla',
            color: document.getElementById('borla_color'),
            codigo: document.getElementById('borla_codigo_color'),
            // { NORMAL: { Rojo: 'B-RO', ... }, UNIVERSITARIA: { Rojo: 'B-RO-U', ... } }
            porTipo: config.coloresBorla || {}
        });
    }

    function iniciarColorPorTipo(opciones) {
        var color = opciones.color;
        var codigo = opciones.codigo;
        var porTipo = opciones.porTipo;
        var radios = document.querySelectorAll('input[name="' + opciones.nombreTipo + '"]');

        if (!color) {
            return;
        }

        function tipoSeleccionado() {
            var marcado = document.querySelector('input[name="' + opciones.nombreTipo + '"]:checked');

            return marcado ? marcado.value : '';
        }

        // El mismo color puede tener otro código según el tipo
        // (borla roja normal B-RO, universitaria B-RO-U).
        function codigoDeColor(nombre) {
            var delTipo = porTipo[tipoSeleccionado()] || {};

            return delTipo[nombre] || '';
        }

        function actualizarCodigo(cambioDelUsuario) {
            if (!codigo) {
                return;
            }

            var nuevo = codigoDeColor(color.value);

            if (nuevo) {
                escribir(codigo, nuevo, cambioDelUsuario);
            } else if (cambioDelUsuario && color.value === '') {
                codigo.value = '';
            }
        }

        // Al cambiar el tipo se ofrecen solo los colores de ese tipo.
        function reconstruirColores() {
            var previo = color.value;
            var colores = Object.keys(porTipo[tipoSeleccionado()] || {});

            color.innerHTML = '';
            color.add(new Option('Seleccione...', ''));

            colores.forEach(function (nombre) {
                color.add(new Option(nombre, nombre, false, nombre === previo));
            });
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                reconstruirColores();
                actualizarCodigo(true);
            });
        });

        color.addEventListener('change', function () {
            actualizarCodigo(true);
        });

        actualizarCodigo(false);
    }

    // ------------------------------------------------------------------
    // Capa: color y código según la carrera
    // ------------------------------------------------------------------
    function iniciarCapa(config) {
        var carrera = document.getElementById('carrera_capa');
        var color = document.getElementById('color_capa');
        var codigo = document.getElementById('codigo_color_capa');

        if (!carrera) {
            return;
        }

        // { DERECHO: { color: 'Rojo', codigo: 'DER' }, ... }
        var porCarrera = config.capaPorCarrera || {};

        function actualizar(cambioDelUsuario) {
            var datos = porCarrera[carrera.value];

            if (datos) {
                escribir(color, datos.color, cambioDelUsuario);
                escribir(codigo, datos.codigo, cambioDelUsuario);
            } else if (cambioDelUsuario && carrera.value === '') {
                if (color) {
                    color.value = '';
                }

                if (codigo) {
                    codigo.value = '';
                }
            }
        }

        carrera.addEventListener('change', function () {
            actualizar(true);
        });

        actualizar(false);
    }
})();
