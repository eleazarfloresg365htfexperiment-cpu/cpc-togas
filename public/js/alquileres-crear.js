/*
 * Pantalla "Nuevo alquiler" (resources/views/alquileres/create.blade.php).
 *
 * Cada sección de este archivo es independiente: se inicia sola cuando la
 * página termina de cargar y solo toca los elementos de su tema.
 *
 * Datos que llegan desde el servidor (config/alquiler.php):
 *   window.CPC_ALQUILER.coloresPorCarrera  color de borla de cada carrera
 */

// ======================================================================
// 1. Fabricación autorizada y alertas de stock por toga
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {

    const hiddenFabricacionCheckbox =
        document.getElementById('fabricacion_autorizada_hidden');

    const fabricacionMetaPanel =
        document.getElementById('fabricacion_meta_panel');


    /*
    |--------------------------------------------------------------------------
    | Fabricación global
    |--------------------------------------------------------------------------
    |
    | Se activa únicamente si alguna toga tiene autorizado fabricar
    | el excedente.
    |
    */
    function actualizarFabricacionGlobal() {

        const anyFabricacion =
            !!document.querySelector('.producto-fabricacion-checkbox:checked');

        if (hiddenFabricacionCheckbox) {
            hiddenFabricacionCheckbox.value = anyFabricacion ? '1' : '0';
        }

        if (fabricacionMetaPanel) {
            fabricacionMetaPanel.classList.toggle(
                'd-none',
                !anyFabricacion
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actualizar alerta de stock
    |--------------------------------------------------------------------------
    */
    function actualizarStockAlert(productoId) {

        const cantidadInput =
            document.getElementById('cantidad_' + productoId);

        const togaItem =
            document.getElementById('toga_item_' + productoId);

        const stockAlert =
            document.getElementById('stock_alert_' + productoId);

        const fabricacionNotice =
            document.getElementById('fabricacion_notice_' + productoId);

        const cantidad =
            parseInt(cantidadInput?.value || '0', 10) || 0;

        const stock =
            parseInt(togaItem?.dataset.stock || '0', 10) || 0;

        const exceso = cantidad > stock;


        /*
        |--------------------------------------------------------------------------
        | Alerta de exceso
        |--------------------------------------------------------------------------
        */
        if (stockAlert) {
            stockAlert.classList.toggle(
                'd-none',
                !exceso ||
                !cantidadInput ||
                cantidadInput.disabled
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Panel de fabricación
        |--------------------------------------------------------------------------
        */
        if (fabricacionNotice) {

            fabricacionNotice.classList.toggle(
                'd-none',
                !exceso
            );


            /*
            | Si ya no existe exceso, se cancela automáticamente
            | la autorización de fabricación de ese producto.
            |
            | IMPORTANTE:
            | Esto NO toca el checkbox .producto-check.
            */
            if (!exceso) {

                const checkbox =
                    fabricacionNotice.querySelector(
                        '.producto-fabricacion-checkbox'
                    );

                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        }


        actualizarFabricacionGlobal();
    }


    /*
    |--------------------------------------------------------------------------
    | Inicializar controles de stock
    |--------------------------------------------------------------------------
    */
    function inicializarStockAlerts() {

        document
            .querySelectorAll('.cantidad-input')
            .forEach(function (input) {

                const productoId =
                    input.dataset.productoId ||
                    obtenerProductoIdDesdeId(input.id);


                input.addEventListener('input', function () {
                    actualizarStockAlert(productoId);
                });


                input.addEventListener('change', function () {
                    actualizarStockAlert(productoId);
                });


                actualizarStockAlert(productoId);
            });
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener ID del producto
    |--------------------------------------------------------------------------
    */
    function obtenerProductoIdDesdeId(id) {

        if (!id) {
            return null;
        }

        const partes = id.split('_');

        return partes[partes.length - 1];
    }


    /*
    |--------------------------------------------------------------------------
    | OMITIR EXCEDENTE
    |--------------------------------------------------------------------------
    |
    | Reduce la cantidad hasta el stock disponible.
    |
    | MUY IMPORTANTE:
    | NO deselecciona la toga.
    |
    */
    document
        .querySelectorAll('.btn-reset-stock')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const productoId =
                    button.dataset.producto;

                const stock =
                    parseInt(
                        button.dataset.stock || '0',
                        10
                    ) || 0;

                const cantidadInput =
                    document.getElementById(
                        'cantidad_' + productoId
                    );


                if (!cantidadInput) {
                    return;
                }


                /*
                | Si existe stock, usamos todo el disponible.
                | Si no existe stock, dejamos vacío.
                */
                cantidadInput.value =
                    stock > 0 ? stock : '';


                /*
                |--------------------------------------------------------------------------
                | IMPORTANTE
                |--------------------------------------------------------------------------
                | No hacemos:
                |
                | checkbox.checked = false;
                |
                | La toga sigue seleccionada.
                |--------------------------------------------------------------------------
                */


                cantidadInput.dispatchEvent(
                    new Event('input', {
                        bubbles: true
                    })
                );

                cantidadInput.dispatchEvent(
                    new Event('change', {
                        bubbles: true
                    })
                );
            });
        });


    /*
    |--------------------------------------------------------------------------
    | AUTORIZAR FABRICACIÓN
    |--------------------------------------------------------------------------
    */
    document
        .querySelectorAll('.btn-authorize-fabricacion')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const productoId =
                    button.dataset.producto;

                const notice =
                    document.getElementById(
                        'fabricacion_notice_' + productoId
                    );

                const checkbox =
                    document.getElementById(
                        'fabricacion_producto_' + productoId
                    );


                if (!notice || !checkbox) {
                    return;
                }


                notice.classList.remove('d-none');

                checkbox.checked = true;

                actualizarFabricacionGlobal();
            });
        });


    /*
    |--------------------------------------------------------------------------
    | Cambio manual de autorización de fabricación
    |--------------------------------------------------------------------------
    */
    document
        .querySelectorAll('.producto-fabricacion-checkbox')
        .forEach(function (checkbox) {

            checkbox.addEventListener('change', function () {

                actualizarFabricacionGlobal();

            });
        });


    /*
    |--------------------------------------------------------------------------
    | Cambio de selección de toga
    |--------------------------------------------------------------------------
    */
    document
        .querySelectorAll('.producto-check')
        .forEach(function (check) {

            check.addEventListener('change', function () {

                const productoId =
                    check.dataset.producto ||
                    check.dataset.productoId;

                actualizarStockAlert(productoId);

            });
        });


    /*
    |--------------------------------------------------------------------------
    | Inicialización
    |--------------------------------------------------------------------------
    */
    inicializarStockAlerts();

    actualizarFabricacionGlobal();

});

// ======================================================================
// 2. Accesorios de cada toga: collarín, capa, birrete y borla
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {
    const checks = document.querySelectorAll('.producto-check');

    function actualizarResumen(productoId) {
        const resumen = document.getElementById('resumen_' + productoId);
        const cantidad = document.getElementById('cantidad_' + productoId);

        const birreteCheck = document.getElementById('birrete_incluido_' + productoId);
        const birreteSelect = document.getElementById('birrete_' + productoId);

        const birreteExtra = document.getElementById('birrete_extra_' + productoId);
        const birreteExtraCantidad = document.getElementById('birrete_extra_cantidad_' + productoId);

        const borlaExtra = document.getElementById('borla_extra_' + productoId);
        const borlaExtraCantidad = document.getElementById('borla_extra_cantidad_' + productoId);

        if (!resumen) return;

        const cantidadTogas = parseInt(cantidad?.value || 0, 10) || 0;

        let birrete = 'No';

        if (
            birreteCheck &&
            birreteCheck.checked &&
            birreteSelect &&
            birreteSelect.value
        ) {
            birrete = 'Sí';
        }

        let totalExtras = 0;

        if (birreteExtra && birreteExtra.value) {
            totalExtras += parseInt(birreteExtraCantidad?.value || 1, 10) || 1;
        }

        if (borlaExtra && borlaExtra.value) {
            totalExtras += parseInt(borlaExtraCantidad?.value || 1, 10) || 1;
        }

        resumen.textContent = 'Togas: ' + cantidadTogas + ' | Birrete: ' + birrete + ' | Extras: ' + totalExtras;
    }

    function actualizarAccesoriosIncluidos(productoId) {
        const birreteCheck = document.getElementById('birrete_incluido_' + productoId);
        const borlaCheck = document.getElementById('borla_incluida_' + productoId);

        if (birreteCheck) {
            const borlaContainer = document.getElementById('borla_incluida_' + productoId)?.closest('.form-check');

            if (borlaContainer) {
                borlaContainer.style.opacity = birreteCheck.checked ? '' : '0.5';
            }

            if (!birreteCheck.checked) {
                if (borlaCheck) {
                    borlaCheck.checked = false;
                    borlaCheck.disabled = true;
                }
            } else if (borlaCheck) {
                borlaCheck.disabled = false;
            }
        }

        actualizarResumen(productoId);
    }

    function actualizarFila(check) {
        const productoId = check.dataset.producto;

        const activo = check.checked;

        const cantidad = document.getElementById('cantidad_' + productoId);
        const collarin = document.getElementById('collarin_' + productoId);

        const panel = document.getElementById('panel_config_' + productoId);
        const botonConfig = document.getElementById('btn_config_' + productoId);
        const resumen = document.getElementById('resumen_' + productoId);

        const togaItem = document.getElementById('toga_item_' + productoId);

        const tipoToga = togaItem?.dataset.tipo || '';

        const capaContainer = togaItem?.querySelector('.capa-container');
        const capaSelect = document.getElementById('capa_' + productoId);

        const inputsConfiguracion = document.querySelectorAll(
            '#panel_config_' + productoId + ' select, ' +
            '#panel_config_' + productoId + ' input'
        );

        if (cantidad) {
            cantidad.disabled = !activo;
            cantidad.required = activo;

            if (!activo) {
                cantidad.value = '';
            } else if (!cantidad.value || cantidad.value === '0') {
                cantidad.value = 1;
            }
        }

        if (collarin) {
            collarin.disabled = !activo;
            collarin.required = activo;

            if (!activo) {
                collarin.value = '';
            }
        }

        // Mostrar la capa únicamente para togas universitarias.
        if (capaContainer && capaSelect) {

            const esUniversitaria = tipoToga === 'UNIVERSITARIA';

            capaContainer.classList.toggle('d-none', !esUniversitaria);

            capaSelect.disabled = !activo || !esUniversitaria;

            capaSelect.required = activo && esUniversitaria;

            if (!activo || !esUniversitaria) {
                capaSelect.value = '';
            }
        }

        if (collarin) {
            const opciones = Array.from(collarin.options);

            opciones.forEach(function (opcion) {
                if (!opcion.value) {
                    return;
                }

                const tipoCollarin = opcion.dataset.tipo;

                if (tipoToga === 'ESTANDAR') {
                    opcion.hidden = tipoCollarin !== 'NORMAL';
                } else {
                    opcion.hidden = false;
                }
            });

            if (collarin.value) {
                const selectedOption = collarin.selectedOptions[0];
                if (selectedOption && selectedOption.hidden) {
                    collarin.value = '';
                }
            }
        }

        inputsConfiguracion.forEach(function (input) {
            if (input.id === 'collarin_' + productoId) return;

            input.disabled = !activo;

            if (!activo) {
                if (input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            }
        });

        if (botonConfig) {
            botonConfig.classList.toggle('d-none', !activo);
            botonConfig.textContent = 'Mostrar configuración';
        }

        if (resumen) {
            resumen.classList.toggle('d-none', !activo);
        }

        if (panel) {
            panel.classList.add('d-none');
        }

        actualizarBorlaIncluida(productoId);
        actualizarAccesoriosIncluidos(productoId);
        actualizarResumen(productoId);
    }

    function obtenerColorDeCapa(productoId) {
        const capaSelect = document.getElementById('capa_' + productoId);
        const opcion = capaSelect?.selectedOptions?.[0];
        const carrera = opcion?.dataset?.carrera;

        /*
        | Los colores salen de config/alquiler.php (Rojo, Verde, Celeste...).
        | Rojo y Verde también existen como borla normal; se distinguen por
        | el tipo de borla (ver actualizarBorlaIncluida).
        | Si la capa no tiene carrera, se usa el color guardado en la capa.
        */
        const coloresPorCarrera = window.CPC_ALQUILER.coloresPorCarrera || {};

        if (!opcion || !opcion.value) {
            return null;
        }

        return coloresPorCarrera[(carrera || '').toUpperCase()] || opcion.dataset.color || null;
    }

    checks.forEach(function (check) {
        check.addEventListener('change', function () {
            actualizarFila(check);
        });

        actualizarFila(check);
    });

    /*
    |--------------------------------------------------------------------------
    | Cambio de collarín
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('select[id^="collarin_"]')
        .forEach(function (select) {

            select.addEventListener('change', function () {

                const productoId =
                    select.dataset.producto ||
                    select.id.replace('collarin_', '');

                actualizarBorlaIncluida(productoId);
            });

        });


    /*
    |--------------------------------------------------------------------------
    | Cambio de capa
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('select[id^="capa_"]')
        .forEach(function (select) {

            select.addEventListener('change', function () {

                const productoId =
                    select.dataset.producto ||
                    select.id.replace('capa_', '');

                actualizarBorlaIncluida(productoId);
            });

        });


    /*
    |--------------------------------------------------------------------------
    | Cambio de borla incluida
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.accesorio-check[id^="borla_incluida_"]')
        .forEach(function (check) {

            check.addEventListener('change', function () {

                const productoId =
                    check.dataset.producto ||
                    check.id.replace('borla_incluida_', '');

                actualizarBorlaIncluida(productoId);
            });
        });

    function obtenerColorCollarin(productoId) {
        const select = document.getElementById('collarin_' + productoId);

        if (!select || !select.value) {
            return null;
        }

        const opcion = select.options[select.selectedIndex];

        return opcion?.dataset.color || null;
    }

    function actualizarBorlaIncluida(productoId) {

        const togaItem =
            document.getElementById('toga_item_' + productoId);

        const borlaSelect =
            document.getElementById('borla_' + productoId);

        const collarinSelect =
            document.getElementById('collarin_' + productoId);

        const borlaCheck =
            document.getElementById('borla_incluida_' + productoId);

        if (!togaItem || !borlaSelect || !borlaCheck) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | La lógica solamente aplica a la BORLA INCLUIDA
        |--------------------------------------------------------------------------
        */

        if (!borlaCheck.checked) {
            return;
        }


        const tipoToga =
            (togaItem.dataset.tipo || '').toUpperCase();


        /*
        |--------------------------------------------------------------------------
        | Determinar color requerido
        |--------------------------------------------------------------------------
        */

        let colorRequerido = null;


        /*
        |--------------------------------------------------------------------------
        | TOGA UNIVERSITARIA
        |--------------------------------------------------------------------------
        |
        | El color depende de la carrera seleccionada en la capa.
        |
        */

        if (tipoToga === 'UNIVERSITARIA') {

            colorRequerido =
                obtenerColorDeCapa(productoId);

        }


        /*
        |--------------------------------------------------------------------------
        | TOGA NORMAL
        |--------------------------------------------------------------------------
        |
        | El color depende del collarín seleccionado.
        |
        */

        else {

            const collarinOption =
                collarinSelect?.options[
                    collarinSelect.selectedIndex
                ];

            colorRequerido =
                collarinOption?.dataset?.color || null;
        }


        /*
        |--------------------------------------------------------------------------
        | Si todavía no tenemos color, limpiamos la selección.
        |--------------------------------------------------------------------------
        */

        const borlaAviso =
            document.getElementById('borla_aviso_' + productoId);

        if (borlaAviso) {
            borlaAviso.classList.add('d-none');
            borlaAviso.textContent = '';
        }

        if (!colorRequerido) {

            borlaSelect.value = '';

            borlaSelect.querySelectorAll('option').forEach(function (option) {

                option.hidden = false;

            });

            return;
        }


        colorRequerido =
            colorRequerido.toUpperCase();

        /*
        | Rojo y Verde existen como borla normal y universitaria: la toga
        | universitaria solo usa borlas universitarias y la estándar, normales.
        */
        const tipoBorlaRequerido =
            tipoToga === 'UNIVERSITARIA' ? 'UNIVERSITARIA' : 'NORMAL';


        /*
        |--------------------------------------------------------------------------
        | Mostrar únicamente la borla del color correspondiente.
        |--------------------------------------------------------------------------
        */

        borlaSelect.querySelectorAll('option').forEach(function (option) {

            /*
            | La opción vacía siempre permanece disponible.
            */

            if (!option.value) {

                option.hidden = false;

                return;
            }


            const colorBorla =
                (option.dataset.color || '').toUpperCase();

            const tipoBorla =
                (option.dataset.tipoBorla || 'NORMAL').toUpperCase();


            option.hidden =
                colorBorla !== colorRequerido ||
                tipoBorla !== tipoBorlaRequerido;

        });


        /*
        |--------------------------------------------------------------------------
        | Seleccionar automáticamente la borla correspondiente.
        |--------------------------------------------------------------------------
        */

        const opcionCorrecta =
            Array.from(borlaSelect.options).find(function (option) {

                return (
                    option.value &&
                    !option.hidden &&
                    (option.dataset.color || '').toUpperCase() ===
                        colorRequerido
                );

            });


        if (opcionCorrecta) {

            borlaSelect.value =
                opcionCorrecta.value;

        } else {

            borlaSelect.value = '';

            if (borlaAviso) {
                borlaAviso.textContent =
                    'No hay borlas ' + (tipoBorlaRequerido === 'UNIVERSITARIA' ? 'universitarias' : 'normales') +
                    ' color ' + colorRequerido.toLowerCase() +
                    ' con stock disponible. Registra una en Productos o quita la borla incluida.';
                borlaAviso.classList.remove('d-none');
            }

        }

        if (window.actualizarStockAlertToga) {
            window.actualizarStockAlertToga(productoId);
        }
    }

    const botonesConfig = document.querySelectorAll('.btn-toggle-config');

    botonesConfig.forEach(function (boton) {
        boton.addEventListener('click', function () {
            const productoId = boton.dataset.producto;
            const panel = document.getElementById('panel_config_' + productoId);

            if (!panel) return;

            const oculto = panel.classList.contains('d-none');

            if (oculto) {
                panel.classList.remove('d-none');
                boton.textContent = 'Ocultar configuración';
            } else {
                panel.classList.add('d-none');
                boton.textContent = 'Mostrar configuración';
            }

            actualizarResumen(productoId);
        });
    });

    const checksAccesorios =
        document.querySelectorAll('.accesorio-check');

    checksAccesorios.forEach(function (check) {

        check.addEventListener('change', function () {

            const productoId =
                check.dataset.producto;

            actualizarAccesoriosIncluidos(productoId);

            if (check.id.startsWith('borla_incluida_')) {
                actualizarBorlaIncluida(productoId);
            }

            sincronizarIncluido(productoId, 'birrete');
            sincronizarIncluido(productoId, 'borla');

        });

    });

    /*
    |--------------------------------------------------------------------------
    | La casilla "incluido" y su lista van juntas (birrete y borla)
    |--------------------------------------------------------------------------
    |
    | Antes se podía marcar "Birrete incluido" sin elegir cuál birrete; el
    | alquiler se guardaba sin él y sin avisar. Ahora:
    |  - al marcar la casilla se elige solo el birrete que corresponde al tipo
    |    de toga (si hay) y la lista pasa a ser obligatoria;
    |  - al elegir algo en la lista se marca la casilla, y al volver a
    |    "Selecciona..." se desmarca;
    |  - al desmarcar la casilla se vacía la lista.
    */
    function birreteSugerido(productoId, select) {
        const togaItem = document.getElementById('toga_item_' + productoId);
        const tipoToga = (togaItem?.dataset.tipo || '').toUpperCase();
        const tiposValidos = tipoToga === 'UNIVERSITARIA'
            ? ['UNIVERSITARIO']
            : ['NORMAL', 'ESTANDAR'];

        const opciones = Array.from(select.options).filter(function (opcion) {
            return opcion.value &&
                !opcion.hidden &&
                tiposValidos.includes((opcion.dataset.tipo || '').toUpperCase());
        });

        return opciones.find(function (opcion) {
            return Number(opcion.dataset.stock || 0) > 0;
        }) || opciones[0] || null;
    }

    function sincronizarIncluido(productoId, tipo) {
        const check = document.getElementById(
            (tipo === 'birrete' ? 'birrete_incluido_' : 'borla_incluida_') + productoId
        );
        const select = document.getElementById(tipo + '_' + productoId);

        if (!check || !select) {
            return;
        }

        select.required = check.checked && !check.disabled;

        if (!check.checked) {
            select.value = '';
            return;
        }

        if (tipo === 'birrete' && !select.value) {
            const sugerido = birreteSugerido(productoId, select);

            if (sugerido) {
                select.value = sugerido.value;
            }
        }

        actualizarResumen(productoId);
    }

    document.querySelectorAll('select.accesorio-select').forEach(function (select) {
        const coincidencia = (select.id || '').match(/^(birrete|borla)_(\d+)$/);

        if (!coincidencia) {
            return;
        }

        const tipo = coincidencia[1];
        const productoId = coincidencia[2];

        select.addEventListener('change', function () {
            const check = document.getElementById(
                (tipo === 'birrete' ? 'birrete_incluido_' : 'borla_incluida_') + productoId
            );

            // Elegir uno marca la casilla; volver a "Selecciona..." la desmarca.
            if (check && !check.disabled && check.checked !== !!select.value) {
                check.checked = !!select.value;
                actualizarAccesoriosIncluidos(productoId);
            }

            // Quitar el birrete también quita la borla incluida.
            sincronizarIncluido(productoId, 'birrete');
            sincronizarIncluido(productoId, 'borla');
        });

        // Al volver con errores (o al recargar), dejar todo coherente.
        sincronizarIncluido(productoId, tipo);
    });

    const birreteSelects = document.querySelectorAll('.accesorio-select[id^="birrete_"]');

    birreteSelects.forEach(function (select) {
        select.addEventListener('change', function () {
            const productoId = select.dataset.producto || select.id.replace('birrete_', '');
            actualizarAccesoriosIncluidos(productoId);
        });
    });

    const camposResumen = document.querySelectorAll('.cantidad-input, .extra-input, .extra-cantidad, .accesorio-select');

    camposResumen.forEach(function (campo) {
        campo.addEventListener('input', function () {
            const productoId = campo.dataset.producto || obtenerProductoIdDesdeId(campo.id);
            actualizarResumen(productoId);
        });

        campo.addEventListener('change', function () {
            const productoId = campo.dataset.producto || obtenerProductoIdDesdeId(campo.id);
            actualizarResumen(productoId);
        });
    });

    function obtenerProductoIdDesdeId(id) {
        if (!id) return null;

        const partes = id.split('_');

        return partes[partes.length - 1];
    }
});

// ======================================================================
// 3. Validación de los accesorios adicionales al enviar
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.confirm-action-form');

    function validarCampoExtra(productoId, tipo) {
        const select = document.getElementById(tipo + '_extra_' + productoId);
        const cantidad = document.getElementById(tipo + '_extra_cantidad_' + productoId);

        if (!select || !cantidad) return true;

        const tieneProducto = select.value !== '';
        const tieneCantidad = cantidad.value !== '';

        select.setCustomValidity('');
        cantidad.setCustomValidity('');

        if (!tieneProducto && tieneCantidad) {
            const mensaje = tipo === 'birrete'
                ? 'Selecciona qué birrete extra será cobrado.'
                : 'Selecciona qué borla extra será cobrada.';

            select.setCustomValidity(mensaje);
            return false;
        }

        if (tieneProducto && !tieneCantidad) {
            const mensaje = tipo === 'birrete'
                ? 'Coloca la cantidad de birretes extra.'
                : 'Coloca la cantidad de borlas extra.';

            cantidad.setCustomValidity(mensaje);
            return false;
        }

        return true;
    }

    function validarExtras() {
        let valido = true;

        document.querySelectorAll('.producto-check:checked').forEach(function (check) {
            const productoId = check.dataset.producto || check.dataset.productoId;

            if (!productoId) return;

            if (!validarCampoExtra(productoId, 'birrete')) {
                valido = false;
            }

            if (!validarCampoExtra(productoId, 'borla')) {
                valido = false;
            }
        });

        return valido;
    }

    document.querySelectorAll('.extra-input, .extra-cantidad').forEach(function (campo) {
        campo.addEventListener('input', validarExtras);
        campo.addEventListener('change', validarExtras);
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!validarExtras()) {
                event.preventDefault();
                event.stopImmediatePropagation();

                const primerInvalido = form.querySelector(':invalid');

                if (primerInvalido) {
                    primerInvalido.reportValidity();
                }
            }
        }, true);
    }
});

// ======================================================================
// 4. Stock insuficiente: pedir la autorización de fabricación al enviar
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {
    const hiddenFabricacionCheckbox = document.getElementById('fabricacion_autorizada_hidden');
    const fabricacionMetaPanel = document.getElementById('fabricacion_meta_panel');

    function actualizarFabricacionGlobal() {
        const anyFabricacion = !!document.querySelector('.producto-fabricacion-checkbox:checked');

        if (hiddenFabricacionCheckbox) {
            hiddenFabricacionCheckbox.value = anyFabricacion ? '1' : '0';
        }

        if (fabricacionMetaPanel) {
            fabricacionMetaPanel.classList.toggle('d-none', !anyFabricacion);
        }
    }

    /*
    | Revisa la toga y sus accesorios incluidos (collarín, capa, birrete,
    | borla). Si alguno no alcanza para la cantidad pedida, se pide
    | autorizar fabricación; el servidor ya no recorta cantidades.
    */
    function faltantesDeToga(productoId, cantidad) {
        const togaItem = document.getElementById('toga_item_' + productoId);
        const faltantes = [];

        if (cantidad <= 0) {
            return faltantes;
        }

        const stockToga = parseInt(togaItem?.dataset.stock || '0', 10) || 0;

        if (cantidad > stockToga) {
            faltantes.push({ nombre: 'Toga', stock: stockToga });
        }

        const accesorios = [
            { tipo: 'collarin', nombre: 'Collarín', check: null },
            { tipo: 'capa', nombre: 'Capa', check: null },
            { tipo: 'birrete', nombre: 'Birrete', check: 'birrete_incluido_' },
            { tipo: 'borla', nombre: 'Borla', check: 'borla_incluida_' },
        ];

        accesorios.forEach(function (accesorio) {
            const select = document.getElementById(accesorio.tipo + '_' + productoId);

            if (!select || select.disabled || !select.value) {
                return;
            }

            // La capa solo aplica a togas universitarias (su contenedor se oculta en las estándar).
            if (accesorio.tipo === 'capa' && select.closest('.capa-container')?.classList.contains('d-none')) {
                return;
            }

            if (accesorio.check) {
                const check = document.getElementById(accesorio.check + productoId);

                if (!check || !check.checked) {
                    return;
                }
            }

            const opcion = select.options[select.selectedIndex];
            const stock = parseInt(opcion?.dataset?.stock || '0', 10) || 0;

            if (cantidad > stock) {
                faltantes.push({ nombre: accesorio.nombre, stock: stock });
            }
        });

        return faltantes;
    }

    function actualizarStockAlert(productoId) {
        const cantidadInput = document.getElementById('cantidad_' + productoId);
        const stockAlert = document.getElementById('stock_alert_' + productoId);
        const stockDetalle = document.getElementById('stock_detalle_' + productoId);
        const fabricacionNotice = document.getElementById('fabricacion_notice_' + productoId);
        const cantidad = parseInt(cantidadInput?.value || '0', 10) || 0;
        const activo = !!cantidadInput && !cantidadInput.disabled;
        const faltantes = activo ? faltantesDeToga(productoId, cantidad) : [];
        const exceso = faltantes.length > 0;

        if (stockDetalle) {
            stockDetalle.innerHTML = faltantes.map(function (f) {
                return '<li>' + f.nombre + ': pides ' + cantidad + ', hay ' + f.stock +
                    ' (faltan ' + (cantidad - f.stock) + ').</li>';
            }).join('');
        }

        if (stockAlert) {
            stockAlert.classList.toggle('d-none', !exceso);
        }

        if (fabricacionNotice) {
            fabricacionNotice.classList.toggle('d-none', !exceso);
        }

        if (!exceso && fabricacionNotice) {
            const checkbox = fabricacionNotice.querySelector('.producto-fabricacion-checkbox');
            if (checkbox) {
                checkbox.checked = false;
            }
        }

        actualizarFabricacionGlobal();
    }

    function inicializarStockAlerts() {
        document.querySelectorAll('.cantidad-input').forEach(function (input) {
            const productoId = input.dataset.productoId || obtenerProductoIdDesdeId(input.id);

            input.addEventListener('input', function () {
                actualizarStockAlert(productoId);
            });

            input.addEventListener('change', function () {
                actualizarStockAlert(productoId);
            });

            actualizarStockAlert(productoId);
        });
    }

    function obtenerProductoIdDesdeId(id) {
        if (!id) return null;
        const partes = id.split('_');
        return partes[partes.length - 1];
    }

    document.querySelectorAll('.btn-reset-stock').forEach(function (button) {
        button.addEventListener('click', function () {
            const productoId = button.dataset.producto;
            const stock = parseInt(button.dataset.stock || '0', 10) || 0;
            const cantidadInput = document.getElementById('cantidad_' + productoId);

            if (cantidadInput) {
                cantidadInput.value = stock > 0 ? stock : '';
                cantidadInput.dispatchEvent(new Event('input'));
                cantidadInput.dispatchEvent(new Event('change'));
            }
        });
    });

    document.querySelectorAll('.btn-authorize-fabricacion').forEach(function (button) {
        button.addEventListener('click', function () {
            const productoId = button.dataset.producto;
            const notice = document.getElementById('fabricacion_notice_' + productoId);
            const checkbox = document.getElementById('fabricacion_producto_' + productoId);

            if (notice && checkbox) {
                notice.classList.remove('d-none');
                checkbox.checked = true;
                actualizarFabricacionGlobal();
            }
        });
    });

    document.querySelectorAll('.producto-fabricacion-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            actualizarFabricacionGlobal();
        });
    });

    document.querySelectorAll('.producto-check').forEach(function (check) {
        check.addEventListener('change', function () {
            const productoId = check.dataset.producto || check.dataset.productoId;
            actualizarStockAlert(productoId);
        });
    });

    window.actualizarStockAlertToga = actualizarStockAlert;

    document.querySelectorAll('select, .accesorio-check').forEach(function (campo) {
        const coincidencia = (campo.id || '').match(/^(collarin|capa|birrete|borla|birrete_incluido|borla_incluida)_(\d+)$/);

        if (!coincidencia) {
            return;
        }

        campo.addEventListener('change', function () {
            // Espera a que los demás scripts terminen de habilitar/rellenar campos.
            setTimeout(function () {
                actualizarStockAlert(coincidencia[2]);
            }, 0);
        });
    });

    inicializarStockAlerts();
    actualizarFabricacionGlobal();
});

// ======================================================================
// 5. Institución del cliente
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {
    const clienteSelect = document.getElementById('cliente_id');
    const institucionInput = document.getElementById('institucion_representada');

    if (!clienteSelect || !institucionInput) {
        return;
    }

    function completarInstitucionDesdeCliente() {
        const selectedOption = clienteSelect.options[clienteSelect.selectedIndex];

        if (!selectedOption) {
            return;
        }

        const institucion = selectedOption.dataset.institucion || '';

        institucionInput.value = institucion;
    }

    clienteSelect.addEventListener('change', completarInstitucionDesdeCliente);

    if (clienteSelect.value && !institucionInput.value) {
        completarInstitucionDesdeCliente();
    }
});

// ======================================================================
// 6. Selección de togas, descuentos y resumen del alquiler
// ======================================================================
document.addEventListener('DOMContentLoaded', function () {

    const descuentoInput = document.getElementById('descuento');

    const subtotalElement = document.getElementById('resumen_subtotal');
    const descuentoElement = document.getElementById('resumen_descuento');
    const totalElement = document.getElementById('resumen_total');
    const saldoElement = document.getElementById('resumen_saldo');


    function numero(valor) {
        const numero = parseFloat(valor);

        return Number.isFinite(numero) ? numero : 0;
    }


    function dinero(valor) {
        return numero(valor).toFixed(2);
    }


    function calcularResumen() {

        let subtotal = 0;


        /*
        |--------------------------------------------------------------------------
        | Productos principales
        |--------------------------------------------------------------------------
        */

        document.querySelectorAll('.toga-item').forEach(function (item) {

            const productoId = item.id.replace('toga_item_', '');

            const checkbox = document.getElementById('producto_' + productoId);
            const cantidadInput = document.getElementById('cantidad_' + productoId);

            if (!checkbox || !checkbox.checked || !cantidadInput) {
                return;
            }

            const cantidad = numero(cantidadInput.value);

            if (cantidad <= 0) {
                return;
            }

            const precio = numero(item.dataset.precio);

            subtotal += precio * cantidad;


            /*
            |--------------------------------------------------------------------------
            | Birrete extra
            |--------------------------------------------------------------------------
            */

            const birreteExtra = document.getElementById(
                'birrete_extra_' + productoId
            );

            const birreteExtraCantidad = document.getElementById(
                'birrete_extra_cantidad_' + productoId
            );

            if (
                birreteExtra &&
                birreteExtra.value &&
                birreteExtraCantidad
            ) {
                const cantidadExtra = numero(
                    birreteExtraCantidad.value
                );

                if (cantidadExtra > 0) {

                    const opcion = birreteExtra.options[
                        birreteExtra.selectedIndex
                    ];

                    const tipoBirrete = opcion?.dataset.tipo || 'ESTANDAR';

                    const precioBirrete =
                        tipoBirrete === 'UNIVERSITARIO'
                            ? 50
                            : 25;

                    subtotal += precioBirrete * cantidadExtra;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Borla extra
            |--------------------------------------------------------------------------
            */

            const borlaExtra = document.getElementById(
                'borla_extra_' + productoId
            );

            const borlaExtraCantidad = document.getElementById(
                'borla_extra_cantidad_' + productoId
            );

            if (
                borlaExtra &&
                borlaExtra.value &&
                borlaExtraCantidad
            ) {
                const cantidadExtra = numero(
                    borlaExtraCantidad.value
                );

                if (cantidadExtra > 0) {
                    subtotal += 5 * cantidadExtra;
                }
            }

        });


        /*
        |--------------------------------------------------------------------------
        | Descuentos
        |--------------------------------------------------------------------------
        */

        let descuentoManual = numero(
            descuentoInput?.value
        );

        if (descuentoManual < 0) {
            descuentoManual = 0;
        }

        if (descuentoManual > subtotal) {
            descuentoManual = subtotal;
        }


        /*
        |--------------------------------------------------------------------------
        | Descuento por toga
        |--------------------------------------------------------------------------
        */

        const descuentoPorTogaInput =
            document.getElementById('descuento_por_toga');

        const descuentoTogaPreview =
            document.getElementById('descuento_toga_preview');

        let descuentoPorToga =
            numero(descuentoPorTogaInput?.value);

        if (descuentoPorToga < 0) {
            descuentoPorToga = 0;
        }

        let cantidadTogas = 0;

        document.querySelectorAll('.toga-item').forEach(function (item) {

            const productoId =
                item.id.replace('toga_item_', '');

            const checkbox =
                document.getElementById('producto_' + productoId);

            const cantidadInput =
                document.getElementById('cantidad_' + productoId);

            if (!checkbox || !checkbox.checked || !cantidadInput) {
                return;
            }

            const cantidad =
                numero(cantidadInput.value);

            if (cantidad > 0) {
                cantidadTogas += cantidad;
            }
        });


        /*
        |--------------------------------------------------------------------------
        | Descuento calculado
        |--------------------------------------------------------------------------
        */

        let descuentoToga =
            descuentoPorToga * cantidadTogas;

        if (descuentoToga > subtotal) {
            descuentoToga = subtotal;
        }


        if (descuentoTogaPreview) {
            descuentoTogaPreview.value =
                'Q ' + dinero(descuentoToga);
        }


        /*
        |--------------------------------------------------------------------------
        | Descuento total
        |--------------------------------------------------------------------------
        */

        const descuentoTotal =
            Math.min(
                descuentoManual + descuentoToga,
                subtotal
            );


        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        const total = Math.max(
            subtotal - descuentoTotal,
            0
        );


        /*
        |--------------------------------------------------------------------------
        | Saldo pendiente
        |--------------------------------------------------------------------------
        |
        | Al crear el alquiler todavía no existen pagos.
        |
        */

        const saldoPendiente = total;


        /*
        |--------------------------------------------------------------------------
        | Actualizar interfaz
        |--------------------------------------------------------------------------
        */

        if (subtotalElement) {
            subtotalElement.textContent = dinero(subtotal);
        }

        if (descuentoElement) {
            descuentoElement.textContent = dinero(descuentoTotal);
        }

        if (totalElement) {
            totalElement.textContent = dinero(total);
        }

        if (saldoElement) {
            saldoElement.textContent = dinero(saldoPendiente);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Descuento
    |--------------------------------------------------------------------------
    */

    if (descuentoInput) {

        descuentoInput.addEventListener(
            'input',
            calcularResumen
        );

        descuentoInput.addEventListener(
            'change',
            calcularResumen
        );
    }

    const descuentoPorTogaInput =
        document.getElementById('descuento_por_toga');

    if (descuentoPorTogaInput) {

        descuentoPorTogaInput.addEventListener(
            'input',
            calcularResumen
        );

        descuentoPorTogaInput.addEventListener(
            'change',
            calcularResumen
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Productos y cantidades
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll(
        '.producto-check, .cantidad-input, .accesorio-check, .accesorio-select, .extra-input, .extra-cantidad'
    ).forEach(function (elemento) {

        elemento.addEventListener(
            'change',
            calcularResumen
        );

        elemento.addEventListener(
            'input',
            calcularResumen
        );
    });


    /*
    |--------------------------------------------------------------------------
    | Inicialización
    |--------------------------------------------------------------------------
    */

    calcularResumen();

});
