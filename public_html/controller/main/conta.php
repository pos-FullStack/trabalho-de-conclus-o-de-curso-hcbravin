<?php

    if($URI[1] == 'acessar-conta' AND is_numeric($URI[2])){
        if(array_key_exists($URI[2],$MS['contas'])){
            $_SESSION['id'] = $URI[1];
            shdr('home',0);
        }else{
            alert('Acesso negado para está conta.');
            goto Fim;
        }
    goto Fim;}


    Fim: