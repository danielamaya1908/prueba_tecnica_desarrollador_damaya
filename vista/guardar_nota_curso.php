<?php

$conexion->insert_simple(
    "notasa24",
    "curso, nota",
    ":curso, :nota",
    [
        ':curso' => $post['data']['curso'],
        ':nota' => $post['data']['nombre']
    ]
);

echo $functions->tabla();