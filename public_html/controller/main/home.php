<?php 

    if(!is_numeric($MS['id'])){
        require_once Views . '/main/login_selecionar_conta.php';
    
    }else{
    
        $Card = new Cartoes();
        $Card -> conta = $MS['id'];
        $Cartoes = $Card -> MeusCartoes();
        require_once Views . '/main/inicio.php';

    }
    