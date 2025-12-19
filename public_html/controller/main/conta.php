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


    if(is_numeric($URI[1])){
        if(array_key_exists($URI[1],$MS['contas'])){

            require_once Views . '/cliente/conta_header.php';

            if(!$URI[2]){

                $Card = new Cartoes();
                $Card -> conta = $MS['id'];
                $Cartoes = $Card -> MeusCartoes();
                require_once Views . '/cliente/conta_geral.php';

            }

            // Transferir
            if($URI[2] == 'transferir'){
                require_once Views . '/cliente/conta_transferir.php';
            }

            // Pix
            if($URI[2] == 'pix'){
                require_once Views . '/cliente/conta_pix.php';
            }

            // investimento
            if($URI[2] == 'investimento'){
                $Investimentos = new Investimentos($URI[1]);
                $InvestimentoLista = $Investimentos -> Listar();
                require_once Views . '/cliente/conta_investimento.php';
            }

            // Cartoes
            if($URI[2] == 'cartoes'){

                // Exibe todos os cartoes
                if(!$URI[3]){
                    require_once Views . '/cliente/conta_cartoes.php';
                }

                // Solicita novo cartao
                if($URI[3] == 'novo'){
                    require_once Views . '/cliente/conta_cartoes_novo.php';
                }

            }


        }else{
            alert('Está conta não pertence a você!');
            goto Fim;
        }
    goto Fim;}


    Fim: