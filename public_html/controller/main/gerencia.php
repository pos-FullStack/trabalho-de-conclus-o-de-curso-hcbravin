<?php

    // VERIFICA SE VOCÊ É GERENTE DA AGÊNCIA
    if(!array_key_exists($URI[1],$MS['gerente'])){ Alert('Você não é gerente desta agência.'); goto Fim; }

    $Taxas = New Taxas();
    require_once Views.'/gerencia/header.php';

    if($URI[2]=='configuracoes'){
        
        $Historico = $Taxas -> MediaAnual();
        require_once Views.'/gerencia/configuracoes.php';
    }

Fim: