<?php

	date_default_timezone_set('America/Sao_Paulo');	// DEFINE A TIMEZONE
	setlocale(LC_ALL,'pt_BR.UTF8'); // DEFINE A LINGUAGEM
	mb_internal_encoding('UTF8');  // DEFINE A CODIFICAÇÃO
	mb_regex_encoding('UTF8'); // DEFINE A CODIFICAÇÃO DO REGEX

	// DEFINICOES PRINCIPAIS
	define('__ROOT__',getcwd()); // DEFINE O ENDEREÇO ROOT
	define('Views',__ROOT__.'/views');
	define('Controller',__ROOT__.'/controller');
	define('Src',__ROOT__.'/src');
	define('__CONFIG__',__ROOT__.'/../config');

	// REQUISIÇÕES PRINCIPAIS
	require_once(__CONFIG__.'/database.php');
	require_once(__ROOT__.'/../vendor/autoload.php');
	require_once(__DIR__.'/__funcoes.php');
	require_once(__DIR__.'/__classes.php');
	
	// CARREGAMENTO ...
	if((isset($_SESSION['superuser']) AND $_SESSION['superuser']==true) OR ($_SERVER['HTTP_HOST']=='meubanco.com')){
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
	}

	// DEFINE SE O DISPOSITIVO É MOBILE OU NÃO
	$isMobile = new \Detection\MobileDetect;
	$Mobile = $isMobile->isMobile();
	
	// GERA AS URLs ATRAVÉS DO URI
	URI(); URINull(5);

	// VERIFICA O USUÁRIO LOGADO OU REALIZA O LOGIN
	// require_once __DIR__.'/cookies.php';
	
	$MS = $_SESSION; # CARREGA A SESSÃO
	sCfg();		# CARREGA AS CONFIGURAÇÕES DO SISTEMA E DO SCT E CRIA O $ES
	MEU();		# CARREGA O SESSION
	LoadTRI(); 	# CARREGA O TRIMESTRE CASO NÃO EXISTA
	$ExibirPainel = ExibirPainel(); # VERIFICA SE O PAINEL VAI OU NAO SER EXIBIDO NO CARREGAMENTO
	// UserTime(); # CARREGA O INICIO DA SESSÃO NO BANCO DE DADOS

	PEnd: