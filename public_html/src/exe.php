<?php

use PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;

 if($MEUTIPO != 0 AND $ANOBASE != $ANOATUAL){ Alert("Alterações Bloqueadas para o ano de $ANOBASE."); goto Fim; }

// PARAMETRIZA O POST
$P = $_POST; foreach($_POST as $K=>$V){ $$K = $V; }
$Dados = ['P'=>[], 'B'=>[], 'U'=>[],'E'=>[],'D'=>[], 'PRO'=>['U'=>0,'I'=>0,'D'=>0]]; $C=0; $Map = [];
$Map = []; $C=0; $SNull = '';

// --------------------- FUNÇÕES GERAIS
if($URI[1]=='abrir-conta'){
    $Agencia = new Agencia();
    $Agencia -> numero = $P['agencia']['numero'];
    $Agencia -> key = $P['agencia']['key'];
    $AgenciaInfo = $Agencia -> Buscar();
    if(is_array($AgenciaInfo) AND array_key_exists('ag_dias',$AgenciaInfo) AND $AgenciaInfo['ag_dias'] > 0){
        // VERIFICA SE EXISTE UMA CONTA ATIVA
        $Busca = $Agencia -> BuscarConta();
        if(!$Busca){
            // case 0: return 'Poupança'; break;
			// case 1: return 'Corrente'; break;
			// case 3: return 'Jurídica'; break;
			// default: return 'Não Informado';
            $Criar = $Agencia -> CriarConta(); 
            if(!is_numeric($Criar)){
                $C++;
               # shdr('nova-conta');   
               print 'erro';
                goto Fim;
            }
            shdr("conta/".$Criar);

        }else{
            alert('Já existe uma conta ativa nesta agência.');
            shdr("conta/".$Busca['cl_id']);
            goto Fim;
        }
    }else{
        alert('Está agência não está mais aberta.');
        shdr('home');
        goto Fim;
    }

goto Status;}
if($URI[1]=='criar-agencia'){
    $Agencia = New Agencia();
    $Criar = $Agencia -> Criar($P['agencia']['cep'], isset($P['agencia']['key']));
    if($Criar){
        $Agencia -> AtualizarSession();
        shdr("gerencia/$Criar");
        
    }else{
        alert('Houve um erro e sua agência não pode ser criada.');
        shdr('home');
    }
goto Status;}

// --------------------- FUNÇÕES ADIMINISTRATIVAS
if($MEUTIPO == 0){


goto Fim;}


Status: 
    require_once Views.'/html/system_engine_status.php';
    goto Fim;

Fim:
