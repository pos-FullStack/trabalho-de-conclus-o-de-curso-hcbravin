<?php 
    if(isset($MS['contas'],$MS['gerente']) AND count($MS['contas']) == 1 AND count($MS['gerente']) > 0){
        shdr("conta/" . reset($MS['contas'])['cl_id'],0);
    }
    require_once Views . '/main/inicio.php';
    