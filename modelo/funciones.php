<?php

class Functions {

    private $con;
    public function __construct(){
        $this->con = new Conexion();
    }

    // SISTEMA GENERAL

        public function tabla(){
            // VARIABLES DE CONTROL
            $curso = "210A";
            $alucur = "alucura24";

            // ALUMNOS DEL CURSO
            $codigos_alumnos = [];
            $data_alumnos_cursos = $this->con->select_where_simple(
                $alucur,
                "curso = '" . $curso . "'" 
            );
            foreach ($data_alumnos_cursos as $row_data_alumnos_cursos => $value_data_alumnos_cursos) {
                $codigos_alumnos[] = $value_data_alumnos_cursos['codigo'];
            }

            // NOTAS DEL CURSO
            $notas_curso = [];
            $data_notas_curso = $this->con->select_where_simple(
                "notasa24",
                "curso = '" . $curso . "'" 
            );
            foreach ($data_notas_curso as $row_data_notas_curso => $value_data_notas_curso) {
                $notas_curso[$value_data_notas_curso['id']] = [
                    "fecha" => $value_data_notas_curso['fecha_cracion'],
                    "nombre" => $value_data_notas_curso['nota'],
                    "valor" => ""
                ];
            }

            // ALUMNOS
            $alumnos = [];
            $data_alumnos = $this->con->select_where_simple(
                "alumnos",
                "codigo IN (" . implode(",", $codigos_alumnos) . ") ORDER BY apellidos ASC" 
            );
            foreach ($data_alumnos as $row_data_alumnos => $value_data_alumnos) {

                $alumnos[$value_data_alumnos['codigo']] = [
                    "nombre" => $value_data_alumnos['apellidos'] . " " . $value_data_alumnos['nombres'],
                    "notas" => $notas_curso,
                ];

                foreach ($notas_curso as $key_notas_curso => $value_notas_curso) {
                    // NOTAS DE ALUMNOS INDIVIDUALES
                    $valores_notas = [];
                    $data_valores_notas = $this->con->select_where_simple(
                        "notas_estudiantes",
                        "estudiante = '" . $value_data_alumnos['codigo'] . "' AND nota_id = " . $key_notas_curso 
                    );
                    foreach ($data_valores_notas as $key_data_valores_notas => $value_data_valores_notas) {
                        $alumnos[$value_data_alumnos['codigo']]['notas'][$value_data_valores_notas['nota_id']]['valor'] = $value_data_valores_notas['valor'];
                    }
                }

            }

            $tabla = '
                <table id="tabla1" border="1" class="table custom-table">
                    <tbody class="tb-reporting">
                        <tr class="task">
                            <th>
                                CODIGO
                            </th>
                            <th>
                                ALUMNO
                            </th>
            ';

            foreach ($notas_curso as $key_notas_curso => $value_notas_curso) {
                $tabla .= '
                    <th class="notas_titulo'. $key_notas_curso . '">
                        ' . $value_notas_curso['nombre'] . '
                        <div class="tooltip" id="tooltip'. $key_notas_curso . '">' . $value_notas_curso['fecha'] . '</div>
                        <script>
                            const titulo = document.querySelector(".notas_titulo'. $key_notas_curso . '");
                            const tooltip = document.getElementById("tooltip'. $key_notas_curso . '");

                            titulo.addEventListener("mouseover", (event) => {
                                tooltip.style.display = "block";
                                tooltip.style.top = event.pageY + 10 + "px"; // Posiciona debajo del mouse
                                tooltip.style.left = event.pageX + "px"; // Posiciona al lado del mouse
                            });

                            titulo.addEventListener("mousemove", (event) => {
                                tooltip.style.top = event.pageY + 10 + "px";
                                tooltip.style.left = event.pageX + "px";
                            });

                            titulo.addEventListener("mouseout", () => {
                                tooltip.style.display = "none";
                            });
                        </script>
                    </th>
                ';
            }

            $tabla .= '
                            <th>
                                Promedio
                            </th>
                            <th>
                                Desempeño
                            </th>
                        </tr>'
            ;

            foreach ($alumnos as $key_alumnos => $value_alumnos) {
                $tabla .= '
                    <tr class="task">
                        <td >
                            ' . $key_alumnos . '
                        </td>
                        <td >
                            ' . $value_alumnos['nombre'] . '
                        </td>
                ';

                $notas_calculo = [];    
                foreach ($value_alumnos['notas'] as $key_value_alumnos => $value_value_alumnos) {
                    $notas_calculo[] = $value_value_alumnos['valor'];

                    $tabla .= '
                        <td>
                            ' . $this->form_simple("", "formulario_valores_notas") . ' 
                                <input type="text" name="valor_nota" data-estudiante="' . $key_alumnos . '" data-nota="' . $key_value_alumnos . '" value="' . $value_value_alumnos['valor'] . '" size="3" class="entrada">
                            ' . $this->end_form() . ' 
                        </td>
                    ';
                }

                $valoresFiltrados = array_filter($notas_calculo, function($valor) {
                    return is_numeric($valor) && $valor >= 1.0 && $valor <= 5.0;
                });

                $suma = array_sum($valoresFiltrados);
                $cantidad = count($valoresFiltrados);
                $promedio = $cantidad > 0 ? $suma / $cantidad : 0;

                $desempeno = $this->nivel_promedio($promedio);

                $tabla .= '
                        <td align="center">
                            <div id="promedio' . $key_alumnos . '">' . number_format($promedio, 2) . '</div>
                        </td>
                        <td>
                            <div class="text_center" id="desempeno' . $key_alumnos . '">' . $desempeno . '</div>
                        </td>
                    </tr>
                ';
            }

            $tabla .= "
                    </tbody>
                </table>
                <div class='' id='formulario_notas_content'></div>
                <div class='bnt_nueva_notas' id='bnt_nueva_notas'>Anexar Nueva Nota</div>
                <script>
                    $(document).ready(function () {
                        // Variables de control
                        const tabla = $('#tabla1');
                        const filas = tabla.find('tr');
                        const inputs = tabla.find('input');

                        //Anexar nuevas notas
                        $('#bnt_nueva_notas').on('click', function (e) {
                            " . $this->ajax("crear_notas", "'" . $curso . "'", "formulario_notas_content") . "
                        });


                        // Inicializar inputs como readonly y enfocar el primero
                        inputs.prop('readonly', true);
                        if (inputs.length > 0) inputs.first().focus();

                        // Función para encontrar el siguiente input en una dirección
                        function encontrarSiguienteInput(inputActual, direccion) {
                            const celdaActual = inputActual.closest('td');
                            const filaActual = inputActual.closest('tr');
                            const indiceFila = filas.index(filaActual);
                            const indiceCelda = filaActual.find('td').index(celdaActual);

                            let siguienteInput = null;

                            if (direccion === 'arriba' && indiceFila > 1) { // Saltar la fila del encabezado
                                siguienteInput = filas.eq(indiceFila - 1).find('td').eq(indiceCelda).find('input');
                            } else if (direccion === 'abajo' && indiceFila < filas.length - 1) {
                                siguienteInput = filas.eq(indiceFila + 1).find('td').eq(indiceCelda).find('input');
                            }

                            return siguienteInput && siguienteInput.length > 0 ? siguienteInput : null;
                        }

                        // Mover foco al siguiente input por índice
                        function moverFoco(indice) {
                            if (indice >= 0 && indice < inputs.length) {
                                const siguienteInput = inputs.eq(indice);
                                siguienteInput.prop('readonly', false).focus().select();
                            }
                        }

                        // Mover foco al siguiente input por dirección
                        function moverFocoPorDireccion(inputActual, direccion) {
                            const siguienteInput = encontrarSiguienteInput(inputActual, direccion);
                            if (siguienteInput) {
                                siguienteInput.prop('readonly', false).focus().select();
                            }
                        }

                        // Alternar entre editar y guardar
                        function alternarEdicion(input) {
                            if (input.prop('readonly')) {
                                input.prop('readonly', false).select();
                            } else {
                                const valorAnterior = input.prop('defaultValue');
                                const valorNuevo = input.val();

                                if(input.data('estudiante') != undefined){
                                    if (validarValor(valorNuevo)) {
                                        if (valorAnterior !== valorNuevo) {
                                            guardarCambios(input, valorNuevo, valorAnterior);
                                        } else {
                                            input.prop('readonly', true);
                                        }

                                        moverFoco(inputs.index(input) + 1);
                                        $('#pop').html('').fadeIn(500);
                                    } else {
                                        $('#pop').html('Las notas validas estan entre 1.0 y 5.0').fadeIn(500);
                                        input.val(valorAnterior);
                                    }
                                }
                            }
                        }

                        // Validar que el valor sea un decimal entre 1.0 y 5.0
                        function validarValor(valor) {
                            const numero = parseFloat(valor);
                            return !isNaN(numero) && numero >= 1.0 && numero <= 5.0;
                        }

                        // Cancelar edición
                        function cancelarEdicion(input) {
                            input.prop('readonly', true).blur();
                        }

                        // Desempeño del estudiante
                        function desempeno(nota){
                            if (nota == '') return '';
                            if (nota <= rango[1]) return 'Bajo';
                            if (nota <= rango[2]) return 'Básico';
                            if (nota <= rango[3]) return 'Alto';
                            if (nota <= rango[4]) return 'Superior';
                        }

                        // Guardar cambios con AJAX
                        function guardarCambios(input, valorNuevo, valorAnterior) {

                            let estudianteCodigo = input.data('estudiante');    
                            const inputData = {
                                [input.attr('name')]: valorNuevo,
                                estudiante: estudianteCodigo,
                                nota: input.data('nota'),
                            };

                            let promedioEstudiante = calcularPromedio(estudianteCodigo);
                            $('#promedio' + estudianteCodigo).html(promedioEstudiante).fadeIn(500);
                            $('#desempeno' + estudianteCodigo).html(desempeno(promedioEstudiante)).fadeIn(500);

                            $.ajax({
                                type: 'POST',
                                url: 'controlador/route.php',
                                data: { data: inputData, vista: 'guardar_notas' },
                                success: function (data) {
                                    input.prop('defaultValue', valorNuevo).prop('readonly', true);
                                },
                                error: function () {
                                    $('#Form1').html('Error al guardar');
                                    input.val(valorAnterior);
                                },
                            });
                        }

                        // Manejar eventos de teclado
                        $(document).on('keydown', 'input', function (e) {
                            const activeElement = $(e.target);
                            const accionesTeclado = {
                                ArrowRight: () => moverFoco(inputs.index(activeElement) + 1),
                                ArrowLeft: () => moverFoco(inputs.index(activeElement) - 1),
                                ArrowUp: () => moverFocoPorDireccion(activeElement, 'arriba'),
                                ArrowDown: () => moverFocoPorDireccion(activeElement, 'abajo'),
                                Enter: () => alternarEdicion(activeElement),
                                Escape: () => cancelarEdicion(activeElement),
                            };

                            if (accionesTeclado[e.key]) {
                                e.preventDefault();
                                accionesTeclado[e.key]();
                            }
                        });

                        // Evento blur 
                        $(document).on('blur', 'input', function (e) {
                            const input = $(e.target);
                            const valorAnterior = input.prop('defaultValue');
                            const valorNuevo = input.val();

                            if(input.data('estudiante') != undefined){
                                if (validarValor(valorNuevo)) {
                                    if (valorAnterior !== valorNuevo) {
                                        guardarCambios(input, valorNuevo, valorAnterior);
                                    } else {
                                        input.prop('readonly', true);
                                    }
                                    $('#pop').html('').fadeIn(500);
                                } else {
                                    $('#pop').html('Las notas validas estan entre 1.0 y 5.0').fadeIn(500);
                                    input.val(valorAnterior).prop('readonly', true);
                                }
                            }
                        });

                        // Calcular promedio del estudiante
                        function calcularPromedio(estudianteId) {
                            const inputsEstudiante = $(`input[data-estudiante='\${estudianteId}']`);
                            
                            let suma = 0;
                            let count = 0;

                            inputsEstudiante.each(function () {
                                let valor = parseFloat($(this).val());

                                if (isNaN(valor) || $(this).val().trim() === '') {
                                    valor = 1.0;
                                }
                                
                                suma += valor;
                                count++;
                            });

                            return count > 0 ? (suma / count).toFixed(2) : 0;
                        }
                    });
                </script>
            ";

            return $tabla;
        }

        public function random_id(){
            $permitted_chars = '123456789qwertyuiopasdfghjklzxcvbnmMNBVCXZASDFGHJKLPOIUYTREWQ';
            return substr(str_shuffle(htmlentities($permitted_chars)), 0, 10);
        }

        public function links_resources(){
            echo '
                <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <title>Examen de Prueba</title>
                    <link rel="stylesheet" href="whatever/styles.css">
                    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
                    
                    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
                    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
            ';
        }

        public function split($dato, $dts){
            $nameFile = explode("/", $dato);
            if (isset($dts)) { return $nameFile[$dts]; } 
        }

        public function cortar_string($string, $largo) { 
            $marca = "/";
            if (strlen($string) > $largo) { 
                
                $string = wordwrap($string, $largo, $marca); 
                $string = explode($marca, $string); 
                $string = $string[0]; 
            } 
            return $string; 
        }

        function nivel_promedio($promedio) {
            $rango = [0, 2.9, 3.9, 4.5, 5.0];
            switch (true) {
                case $promedio === '':
                    return '';
                case $promedio <= $rango[1]:
                    return 'Bajo';
                case $promedio <= $rango[2]:
                    return 'Básico';
                case $promedio <= $rango[3]:
                    return 'Alto';
                case $promedio <= $rango[4]:
                    return 'Superior';
                default:
                    return 'Fuera de rango';
            }
        }

    //

    // MISELANEA FECHAS

        public function solo_date(){// Solo Año-Mes-Dia Tipo->DATE
            date_default_timezone_set("America/Bogota");
            return date('Y-m-d');
        }

        public function DateTime(){// Año-Mes-Dia Hora-Minutos-Segundos Tipo->DATESTAMP
            date_default_timezone_set("America/Bogota");
            return date('Y-m-d H:i:s');
        }

        public function solo_time(){// Solo Hora-Minutos-Segundos Tipo->TIME
            date_default_timezone_set("America/Bogota");
            return date('H:i:s');
        }
    //

    // FORMULARIOS
        public function form_simple($id, $class){ //formulario Con id
            return '<form autocomplete="off" class="'.$class.'" role="form" id="'.$id.'"  method="POST" name="form" action="#">';
        }

        public function end_form(){// cerrar formulario
            return '</form>';
        }
    //

    // JAVASCRIPT
        public function ajax($url, $post, $target){

            $id =  $this->random_id();
            return '
                function send_' . $id . '(){
                    var accion = {
                        "data" : ' . $post . ',
                        "vista" : "' . $url . '"
                    };
        
                    if (flag_' . $id . '){
                        flag_' . $id . ' = false;
                        $.ajax({
                            type: "POST",
                            url: "controlador/route.php",
                            data: accion,
                        })
                        .done(function(data) {
                            $("#' . $target . '").fadeOut(300, function() {
                                $(this).html(data).fadeIn(500);
                            });
                        })
                        .fail(function(xhr, textStatus, errorThrown) {
                            $("#' . $target . '")
                                .html("<p>Error al cargar la información.</p>")
                                .append("<button id=\"reintentar_' . $id . '\">Reintentar</button>");
                            
                            $("#reintentar_' . $id . '").click(function() {
                                send_' . $id . '();
                            });
        
                            flag_' . $id . ' = true;
                        });
                    }
                }
                
                var flag_' . $id . ' = true;
                $("#' . $target . '")
                    .fadeIn(1000, function() {
                        setTimeout(send_' . $id . ', 500)
                });   
            ';
        }

        public function ajax_tiempo($url, $post, $target, $tiempo){
            $id = $this->random_id();
            return '
                var flag_' . $id . ' = true;

                function send_' . $id . '(){
                    if (!flag_' . $id . ') return;

                    flag_' . $id . ' = false; // Bloquear hasta completar

                    var accion = {
                        "data": ' . $post . ',
                        "vista": "' . $url . '"
                    };

                    $.ajax({
                        type: "POST",
                        url: "controlador/route.php",
                        data: accion,
                    })
                    .done(function(data) {
                        $("#' . $target . '").fadeOut(300, function() {
                            $(this).html(data).fadeIn(500);
                        });
                    })
                    .fail(function(xhr, textStatus, errorThrown) {
                        $("#' . $target . '")
                            .html("<p>Error al cargar la información.</p>")
                            .append("<button id=\"reintentar_' . $id . '\">Reintentar</button>");
                        
                        $("#reintentar_' . $id . '").click(function() {
                            send_' . $id . '();
                        });
                    })
                    .always(function() {
                        flag_' . $id . ' = true; // Liberar el bloqueo
                    });
                }

                function iniciarCiclo_' . $id . '(){
                    send_' . $id . '();
                    setTimeout(iniciarCiclo_' . $id . ', ' . $tiempo . ');
                }

                // Iniciar el ciclo
                iniciarCiclo_' . $id . '();
            ';
        }

        public function ajax_form_js($url, $form, $target){
            return '
                <script>
                    var flag = true;
                    $("#' . $form . '").submit(function(e) {
                        e.preventDefault();

                        var accion = {
                            "data" : getFormData($(this)),
                            "vista" : "' . $url . '"
                        };

                        $("#' . $form . '").html(\'<div class="spinner-grow" style="width: 3rem; height: 3rem;" role="status"></div>\')

                        if (flag){
                            flag = false;
                            $.ajax({
                                type: "POST",
                            url: "controlador/route.php",
                                data: accion,
                                success: function(data) {
                                    $("#' . $target . '")
                                        .html(data)
                                    $("#' . $form . '").html("")
                                },
                                error: function(xhr, textStatus, errorThrown){
                                    $("#' . $target . '")
                                        .html(errorThrown)
                                } 
                            })
                            .done(function(){
                                flag = true
                            });
                        }
                        
                    });
                </script>
            ';
        }

        public function btn_depende($title, $type_btn, $url, $target, $post) {
            if( $type_btn == "" ) {
                $type_btn = "btn-outline-primary";
            }
            $id_parced = str_replace(" ","", $title);
            return '
                <button style="width:auto" id="'.$id_parced.'_btn" type="button" class="btn '.$type_btn.'">
                    '.$title.
                    '<script>
                        $("#'.$id_parced.'_btn").click(function(){'.
                            $this->ajax($url, $post, $target).
                        '});'.
                    '</script>
                </button>'
            ; 
        }
    //

}

$functions = new Functions;