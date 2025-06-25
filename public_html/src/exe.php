<?php

use PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;

 if($MEUTIPO != 0 AND $ANOBASE != $ANOATUAL){ Alert("Alterações Bloqueadas para o ano de $ANOBASE."); goto Fim; }

// PARAMETRIZA O POST
$P = $_POST; foreach($_POST as $K=>$V){ $$K = $V; }
$Dados = ['P'=>[], 'B'=>[], 'U'=>[],'E'=>[],'D'=>[], 'PRO'=>['U'=>0,'I'=>0,'D'=>0]]; $C=0; $Map = [];
$Map = []; $C=0; $SNull = '';

// --------------------- FUNÇÕES GERAIS
if($URI[1]=='criar-agencia'){
    $Agencia = New Agencia();
    $Criar = $Agencia -> Criar($P['agencia']['cep'], isset($P['agencia']['key']));
    if($Criar){
        shdr("agencia/$Criar");
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
