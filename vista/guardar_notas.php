<?php

echo '<pre>';
print_r($post);
echo '</pre>';

$data_notas = $conexion->select_where_simple(
	"notas_estudiantes",
	"nota_id = '" . $post['data']['nota'] . "' AND estudiante = " . $post['data']['estudiante']
);
if(count($data_notas) === 0){
    $conexion->insert_simple(
        "notas_estudiantes",
        "nota_id, valor, estudiante",
        ":nota_id, :valor, :estudiante",
        [
            ':nota_id' => $post['data']['nota'],
            ':estudiante' => $post['data']['estudiante'],
            ':valor' => $post['data']['valor_nota']
        ]
    );
}else{
    $conexion->udpdate_simple(
        "notas_estudiantes",
        "valor = :valor",
        array(
            'valor' => $post['data']['valor_nota'],
            'id' => $data_notas[0]['id']
        )
    );
}