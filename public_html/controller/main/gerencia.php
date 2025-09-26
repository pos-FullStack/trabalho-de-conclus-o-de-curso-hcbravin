<?php


    if($URI[1]=='desativadas'){
        $Map = []; foreach($MS['gerente'] as $KeyG=>$ViewG){
            if(Data($ViewG['ag_dref'],'ano') < $ANOATUAL){
                $Map[] = $KeyG;
            }
        }
        require_once Views.'/gerencia/desativadas.php';
    goto Fim;}

    // VERIFICA SE VOCÊ É GERENTE DA AGÊNCIA
    if(!array_key_exists($URI[1],$MS['gerente'])){ Alert('Você não é gerente desta agência.'); goto Fim; }

    $Agencia = new Agencia();
    $Agencia -> id = $URI[1];

    $Sorte = new Sorte();
    $Cards = $Sorte -> getCards();

    $Taxas = New Taxas();
    require_once Views.'/gerencia/header.php';

    if($URI[2]=='contas'){
        
        if(!$URI[3]){ // Exibe todas as contas
            $Contas = $Agencia -> getContas();
            require_once Views.'/gerencia/contas.php';

        }

        if(is_numeric($URI[3])){ // Abre a conta selecionada
            $Conta = $Agencia -> findConta($URI[3]);
            if($Conta == false){alert('A conta que você quer acessar não foi encontrada!'); goto Fim;}
            require_once Views.'/gerencia/contas_abrir.php';
        }

    goto Fim;}

    if($URI[2]=='configuracoes'){
        
        $Historico = $Taxas -> MediaAnual();
        $Configuracoes = $Agencia -> getConfig();

        // VERIFICA SE EXISTEM DEBITOS CADASTRADOS, SE NAO, CARREGA O PADRAO
        $Configuracoes['debitos'] = (count($Configuracoes['debitos'])) ? $Configuracoes['debitos'] : json_decode(file_get_contents(__ROOT__.'/files/agencia_debitos_main.json'),true);

        // Contas
        $Contas = $Agencia -> getContas();
        $SalarioMinimo = new Taxas() -> getSalarioMinimo();
        $Profissoes = new Profissoes() -> getProfissoes();

        require_once Views.'/gerencia/configuracoes.php';
    
    goto Fim;}

Fim: