<?php

// REQERIDOS
include_once ("controlador/requeridos.php");
$functions->links_resources();

// VISTA PRINCIPAL
echo "
		<body>
			<div id='principal'>
				<br/>

				<h1>COLEGIO DE PRUEBA SYSCOLEGIOS</h1>

				<marquee id='mensaje_motivacion'></marquee>

				<h2>PLANILLA DE INGRESO DE CALIFICACIONES</h2>
				<hr/>

				<div id='pop' title='Mensaje syscolegios'></div>

				<div id='Form1' style='overflow-x: scroll;max-width: max-content;'>
				</div>
				<hr/>
			</div>
		</body>
		<script>
			var rango = new Array(0, 2.9, 3.9, 4.5, 5.0);
		".
			$functions->ajax("tabla_alumnos", "''", "Form1").
		"
			function getFormData(form) {
				var unindexed_array = form.serializeArray();
				var indexed_array = {};

				$.map(unindexed_array, function(n, i) {
					indexed_array[n['name']] = n['value'];
				});

				return indexed_array;
			}

			" . $functions->ajax_tiempo("mensaje_motivacion", "'ds'", "mensaje_motivacion", "10000") . "
		</script>
	</html>
";
