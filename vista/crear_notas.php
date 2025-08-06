<?php

echo
    $functions->form_simple("formulario_notas_crear", "formulario_notas_content").
        '<strong>Titulo</strong>
        <input type="text" name="nombre" value="" class="entrada_form">
        <input type="hidden" name="curso" value="' . $post['data'] . '" class="entrada_form">
        <button  class="btn_text" type="submit" name="formulario_crear_notas" value="' . $post['data'] . '" onclick="var e=this;setTimeout(function(){e.disabled=true;},0);return true;"><span class="icon">Guardar</span></button>'.
    $functions->end_form().
    $functions->ajax_form_js("guardar_nota_curso", "formulario_notas_crear", "Form1")
;