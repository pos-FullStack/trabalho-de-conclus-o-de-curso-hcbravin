<?php 
// INICIA A SESSÃO
session_start(); 
// CARREGAMENTO INICIAL
require_once __DIR__.'/ConfigSystem.php';
if(!isset($MS['id'])){ print json_encode([false]); goto Fim; }

// MODELA AS VARIAVEIS 
$P = $_POST; foreach($P as $k => $v){if(!strstr($k,'-')){ $$k = ($v); }}
foreach($_GET as $k => $v){if(!strstr($k,'-')){ $$k = ($v); }}
// FIM CARREGANDO ------------------------------

// BUSCAR AGENCIA
if($URI[1]=='buscar-agencia'){

	$Agencia = new Agencia();
	$Agencia -> numero = $URI[2];
	$Buscar = $Agencia -> Buscar();
	if(is_array($Buscar) AND array_key_exists('numero',$Buscar)) { 
		print json_encode($Buscar);
		goto Fim;
	}

goto JsonErro;}

// BUSCA O CPF
if($URI[1]=='search-doc'){

	if(Logado()){ 
		
		$Base = $db -> prepare("SELECT ui_nome as nome, user_login as id, user_secretaria as sct, user_tipo as tipo FROM userinfo
		LEFT JOIN user ON (user.user_login = userinfo.ui_login) WHERE ui_doc = ?");
		$Base -> bind_param("s",$doc);
		$Base -> execute();
		$User = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC); $Map = [];
		foreach($User as $K=>$V){
			$Map = ['nome'=>$V['nome'],'id'=>$V['id']];
			if($V['sct'] == $MYSCT AND $V['tipo'] == $tipo){
				$Map = ['error']; break;
			}
		} print json_encode($Map); goto Fim;
	}
	
goto JsonErro;}

// CARREGA ARQUIVOS DO SUMMERFILES
if($URI[1]=='SummerFiles'){
	if ($_FILES['file']['name']) {
		if (!$_FILES['file']['error']) {
			$Upload = New Upload();
			$Upload -> local = 'ead';
			$Upload -> input = $_FILES['file'];
			$Uploading = $Upload -> Send();
			echo "/files/".$Uploading['fl_dir'].$Uploading['fl_arquivo'];
		}
	}
goto PFim;}

// CARREGA MEUS ARQUIVOS
if($URI[1]=='LoadMyFiles'){

	$Base = $db -> prepare("SELECT DISTINCT(fl_id), files.* FROM user as sUser
	LEFT JOIN user as fUser ON (fUser.user_login = sUser.user_login)
	LEFT JOIN files ON (files.fl_user = fUser.user_id) 
	WHERE sUser.user_id = ? ORDER BY fl_dref DESC"); dbE();
	$Base -> bind_param("i",$MEUID); dbE();
	$Base -> execute();
	$Map = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC),'fl_id');

	foreach($Map as $K1=>$V1){
		$Map[$K1]['fl_icon'] = FileIcon(pathinfo($V1['fl_nome'], PATHINFO_EXTENSION));
		$Map[$K1]['fl_icon_color'] = FileIcon(pathinfo($V1['fl_nome'], PATHINFO_EXTENSION),'color');
		$Map[$K1]['fl_data'] = Data($V1['fl_data'],3);
		$Map[$K1]['fl_size'] = Byte2($V1['fl_size']);
		$Map[$K1]['fl_download'] = $V1['fl_dir'].$V1['fl_arquivo'];
		$Map[$K1]['fl_data'] = Data($V1['fl_dref'],3);
		unset($Map[$K1]['fl_user'],$Map[$K1]['fl_dref'],$Map[$K1]['fl_server'],$Map[$K1]['fl_arquivo'],$Map[$K1]['fl_dir']);
	}
	
	print json_encode($Map);

goto PFim;}

// UPLOAD POR AJAX
if($URI[1]=='dropzone'){

    if(!isset($_FILES['file'])){ goto JsonErro; }

	$Upload = New Upload();
	$Upload -> local = @$local;
	$Upload -> input = $_FILES['file'];
	$Uploading = $Upload -> Send();
	print json_encode((is_array($Uploading))?$Uploading:[false]);

goto PFim;}


// ERRO DE JSON
JsonErro: print json_encode([false]);

PFim:
Fim: