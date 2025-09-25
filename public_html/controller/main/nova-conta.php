<?php 

    if($URI[1] == 'abrir'){
        require_once Views . '/main/inicio_agencia_conta_nova.php';
        require_once Views . '/modal/NovaConta.php';
    }

    if((!$URI[1] AND count($MS['contas'])) OR ($URI[1]=='entrar')) {
        require_once Views . '/main/login_selecionar_conta.php';

    }
    