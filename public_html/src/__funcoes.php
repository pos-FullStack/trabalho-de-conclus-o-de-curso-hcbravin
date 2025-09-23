<?php
// FUNCOES PRINCIPAIS

use phpDocumentor\Reflection\Types\Null_;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

function Data($data=null,$tipo=null){
	global $ANOATUAL,$ANOBASE,$db, $MYSCT;

	if($data==null AND $tipo==null){return date('Y-m-d H:i:s');} # DATA PADRÃO PHP
	if($data===null){$data = date('Y-m-d H:i:s');} # CASO $DATA NÃO EXISTA
	if($tipo === 0){return date("d/m/Y H:i:s",strtotime($data));}
	if($tipo == 1){return date('d/m/Y',strtotime($data));} # CONVERTENDO DATA BR
	if($tipo == 2){return date('Y-m-d',strtotime(str_replace('/','-',$data)));}
	if($tipo == 3){return date('d/m/Y',strtotime(str_replace('/','-',$data)));}
	if($tipo == 4){return date('H:i:s',strtotime(str_replace('/','-',$data)));}
	if($tipo == 5){return date("d/m", strtotime(str_replace('/','-',$data)));}
	if($tipo == 6){return date("H:i", strtotime(str_replace('/','-',$data)));}
	if($tipo == 7){return date("m", strtotime(str_replace('/','-',$data)));} # EXIBE O MÊS
	if($tipo == 8){return date("d", strtotime(str_replace('/','-',$data)));} # EXIBE O DIA
	if($tipo == 9){return date('N',strtotime(str_replace('/','-',$data)));} # EXIBE  DIA DA SEMANA 
	if($tipo ==10){ $DIAS = [NULL,'SEG','TER','QUA','QUI','SEX','SAB','DOM','SEGUNDA-FEIRA','TERÇA-FEIRA','QUARTA-FEIRA','QUINTA-FEIRA','SEXTA-FEIRA','SÁBADO','DOMINGO'];
      return ((is_numeric($data))? $DIAS[$data] : $DIAS[date('N',strtotime(str_replace('/','-',$data)))]);
    }
	if($tipo ==11){ // RETORNA MES NOME INTEIRO
		$BR = [null,'JANEIRO','FEVEREIRO','MARÇO','ABRIL','MAIO','JUNHO','JULHO','AGOSTO','SETEMBRO','OUTUBRO','NOVEMBRO','DEZEMBRO'];
		return $BR[(is_numeric($data))?$data:date('n',strtotime(str_replace('/','-',$data)))];
	}
	if($tipo ==12){ // RETORNA MES NOME ABREVIADO
		if(!is_numeric($data)){$data = intval(date('m',strtotime(str_replace('/','-',$data))));}else{ $data = intval($data); }
		$BR = [null,'JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];
		return $BR[$data];
	}
	if($tipo == 13){return date('W',strtotime(str_replace('/','-',$data)));} // RETORNA O NUMERO DA SEMANA
	if($tipo == 14 OR $tipo == 'ano'){return date('Y', strtotime(str_replace('/','-',$data)));} // RETORNA O ANO

	if($tipo == 28){
		$DiaExtenso = [
			1   => 'um',
            2   => 'dois',
            3   => 'três',
            4   => 'quatro',
            5   => 'cinco',
            6   => 'seis',
            7   => 'sete',
            8   => 'oito',
            9   => 'nove',
            10  => 'dez',
            11  => 'onze',
            12  => 'doze',
            13  => 'treze',
            14  => 'quatorze',
            15  => 'quinze',
            16  => 'dezesseis',
            17  => 'dezessete',
            18  => 'dezoito',
            19  => 'dezenove',
            20  => 'vinte',
			30  => 'trinta'
		];

		$dia = date('j', strtotime($data));
		$mes = date('M', strtotime($data));
		$ano = date('y', strtotime($data));

		return ($DiaExtenso[$dia]) . " dia".($dia > 1?'s':'')." do mês de " . mb_strtolower(Data($mes,11),'UTF-8');

	}
	if($tipo == 29){$week_start = new DateTime(); $week_start->setISODate($ANOBASE,$data); return $week_start->format('Y-m-d');} # RETORNA A SEGUNDA COM BASE NO NUMERO DA SEMANA
	
	if($tipo == 'feriados'){
		$ano = intval(date('Y',strtotime(str_replace('/','-',$data))));
		$pascoa = easter_date($ano); // Limite de 1970 ou após 2037 da easter_date PHP consulta http://www.php.net/manual/pt_BR/function.easter-date.php

		$dia_pascoa = date('j', $pascoa);
		$mes_pascoa = date('n', $pascoa);
		$ano_pascoa = date('Y', $pascoa);
		
		$feriados = array(
			mktime(0, 0, 0, 1, 1, $ano), // Confraternização Universal - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 4, 21, $ano), // Tiradentes - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 5, 1, $ano), // Dia do Trabalhador - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 9, 7, $ano), // Dia da Independência - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 10, 12, $ano), // N. S. Aparecida - Lei nº 6802, de 30/06/80
			mktime(0, 0, 0, 11, 2, $ano), // Todos os santos - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 11, 15, $ano), // Proclamação da republica - Lei nº 662, de 06/04/49
			mktime(0, 0, 0, 11, 20, $ano), // Dia da consciência negra - Lei 12.519/2011
			mktime(0, 0, 0, 12, 25, $ano), // Natal - Lei nº 662, de 06/04/49
			
	
			// Essas Datas depem diretamente da data de Pascoa
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47, $ano_pascoa), //3ºferia Carnaval
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2, $ano_pascoa), //6ºfeira Santa
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa, $ano_pascoa), //Pascoa
			mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60, $ano_pascoa), //Corpus Cirist
		
		); 
		// PROCURA OS DIAS DE FERIADOS REGISTRADOS NO CALENDARIO ESCOLAR
		$Base = $db -> query("SELECT ce_data FROM agenda_escolar WHERE ce_secretaria = '$MYSCT' AND ce_tipo = 5 AND YEAR(ce_data) = $ANOBASE") -> fetch_all();
		foreach($Base as $V){ $feriados[] = strtotime($V[0]); }

		// REORDENA E FORMATA A DATA
		sort($feriados); foreach($feriados as $K1=>$V1){$feriados[$K1] = date('Y-m-d',$V1);}

		return $feriados;
	}	
	if($tipo == 'agenda'){ // USADO PARA SEMANAS DA AGENDA
		for($c=0;$c<=4;$c++){$Fim[] = date('d/m/Y', strtotime($data." + $c day"));} return $Fim;
    }
}
function ZeroEsquerda($numero) {
    // Converte o número para string para contar os caracteres
    $numeroStr = (string)$numero;
    $quantidadeCaracteres = strlen($numeroStr);
    
    // Verifica se a quantidade de caracteres é menor que 5
    if ($quantidadeCaracteres < 5) {
        // Completa com zeros à esquerda até ter 5 caracteres
        $numeroFormatado = str_pad($numeroStr, 5, '0', STR_PAD_LEFT);
        return $numeroFormatado;
    } else {
        // Retorna o número original se já tiver 5 ou mais caracteres
        return $numeroStr;
    }
}
function BaseEL_encode($num) {
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz'; // Base58 sem 0, O, I, l
    $digits = '123456789'; // Apenas números de 1 a 9 (evitando 0)

    $num1 = ($num % 9) + 1; // Primeiro número (1 a 9)
    $num2 = (($num / 9) % 9) + 1; // Segundo número (1 a 9)

    $letter1 = $letters[$num % strlen($letters)]; // Primeira letra baseada no número
    $letter2 = $letters[($num / 2) % strlen($letters)]; // Segunda letra baseada no número
    $letter3 = $letters[($num / 3) % strlen($letters)]; // Terceira letra baseada no número

    return "{$num1}{$letter1}{$letter2}{$num2}{$letter3}";
}
function BaseEL_decode($str) {
	$num1 = (int)$str[0] - 1;
    $num2 = (int)$str[3] - 1;

    return ($num2 * 9) + $num1;
}
function eSex($data=0){return date('Y-m-d',strtotime(eSeg($data).' +4 days'));}
function eSeg($data=0){
	if(is_date($data)){
		return date("Y-m-d",strtotime(Data($data,2)." monday"));
	}
	if(is_numeric($data)){
		$data_agora = Data(null,2);
		return date("Y-m-d",strtotime("$data_agora ".((date("N",strtotime($data_agora))==1)?null:'last')." monday +$data weeks"));
	}
}
function is_date($date, $format = 'Y-m-d'){
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
function URI(){
    $URL = filter_input(INPUT_SERVER, 'REQUEST_URI');
	$GLOBALS['URLMain'] = $URL;
	$GLOBALS['URI'] = explode('/',substr($URL,1,strlen($URL)));
	// VERIFICA O EAC
	foreach($GLOBALS['URI'] as $K1=>$V1){
		if(strpos($V1,'EAC') !== false){
			$GLOBALS['EAC'] = str_replace('EAC',NULL,$V1);
			unset($GLOBALS['URI'][$K1]);
			
		}else{$GLOBALS['EAC'] = null;}
		if(strpos($V1,'TRI') !== false){
			# REMOVE O TRI E REORGANIZA O ARRAY
			//$_SESSION['TRI'] = str_replace('TRI','',$V1); #REMOVIDO
			unset($GLOBALS['URI'][$K1]);
		}
	}

    // RECRIA O VETOR REMOVENDO OS NULOS
	$ATEMP=[]; $GVc=0; foreach($GLOBALS['URI'] as $Gi=>$GVi){$ATEMP[$GVc] = $GVi; $GVc++;}
	$GLOBALS['URI'] = $ATEMP; unset($ATEMP);
	if(!isset($GLOBALS['URI'][0]) OR $GLOBALS['URI'][0]==null){$GLOBALS['URI'][0]='inicio';}

    // CRIA ANOBASE E ANOATUAL
	$GLOBALS['ANOBASE']=((isset($_SESSION['ano']))?$_SESSION['ano']:date('Y'));$GLOBALS['ANOATUAL']=date('Y');
	
	if(@$_SERVER['REQUEST_SCHEME'] == 'http' AND $_SERVER['SERVER_NAME'] == 'app.minhatesa.com'){
		header('Location: https://'.$_SERVER['SERVER_NAME'].'/'.implode('/',$GLOBALS['URI']));
	}
	return false;
}
function URINull($max,$min=1){global $URI; for($i=$min;$i<=$max;$i++){ if(!isset($URI[$i])){$GLOBALS['URI'][$i] = null;} }}
function UrlOpen(){
	global $URI;
	$URLs = ['login','wiki','descad','atas','qrCode','politicas-de-cookies'];
	return ((in_array($URI[0],$URLs))?true:false);
}
function gCaptcha($Exibir=false){
	global $MS;
	if($Exibir == true OR @$MS['exibir-captcha']){
		$Key = UniqMD5();
		$N1 = rand(0,9);
		$N2 = rand(0,9);
		$_SESSION['captcha'][$Key] = $N1 + $N2;
		return ['codigo'=>"Quanto é <span class=\"badge text-bg-warning mx-1 mb-1\"><i class=\"fa fa-$N1\"></i> + <i class=\"fa fa-$N2\"></i></span>?",'key'=>$Key];
	} return ['codigo'=>NULL,'key'=>NULL];
}
function Logado($Conta=false){
	global $MS;
	$Logado = (isset($MS['ui_id'],$MS['id']))?true:false;
	$Logado = (isset($Conta) OR $Conta == false) ? $Logado : boolval(is_numeric($MS['id']));
	return $Logado;
}
function hdr($local,$codigo=0){
	global $db,$MEUID;

	if(strpos($local,'/') === 0){ $local = substr($local,1); }

	if(is_numeric($codigo)){
		if($codigo == 3306){dbE();}
		print '<script>$(location).attr("href","/'.$local.'/EAC'.$codigo.'");</script>';
	}
	elseif($codigo===null){ print '<script>$(location).attr("href","/'.$local.'");</script>'; }
	else{
			if($codigo == true){ print '<script>$(location).attr("href","/'.$local.'/EACtrue");</script>';}
			else{ print '<script>$(location).attr("href","/'.$local.'/EACfalse");</script>'; }
		}
    return false;
}
function shdr($pagina,$tempo=2){
    if(strlen($pagina) == 0 OR is_numeric($pagina)){ return false; }
	$tempo = $tempo?:1;
	$GLOBALS['SHDR'] = $tempo;
    print "<script>
	$(function(){
		var bar = $('#shdr_bar');
		var b = ".str_replace(',','.',(100 / ceil($tempo/0.250))).";
		var w = 0;
		setInterval(function(){
			w = w + parseInt(b);
			bar.width((w<=100?w:100)+'%');
			if(w >= 100){ return false; }
		}, 250);
		setTimeout(function(){window.location = '/$pagina';}, ".($tempo*1000+250).");
	});
	</script>";
    return true;
}
function dbE(){
	global $db; $DbErro = (mysqli_error($db));
	if(strlen($DbErro)>0){
		if($_SERVER['HTTP_HOST']=='learnpilot.com' OR $_SESSION['tipo']==0){ print $DbErro; }
		
		$File = fopen(__ROOT__."/../dbError.log",'a');
		fwrite($File,$DbErro);
		fclose($File);
		return true;

	} return false;
}
function qTri($data=false){ global $ES,$ANOBASE;
	if(is_date($data)){
		if($data <= Data($ES['1trifim'],2)){return 1;}
		elseif($data < Data($ES['3triini'],2)){return 2;}
		elseif($data >= Data($ES['3triini'],2)){return 3;}
	}else{
		if(isset($ES['1triini'],$ES['2triini'],$ES['3triini'],$ES['1trifim'],$ES['2trifim'],$ES['3trifim'],$ES['1triini'])){
			if(date('Y-m-d') >= $ANOBASE.'-01-01'     AND date('Y-m-d') <= Data($ES['1trifim'],2)){ return 1; }
			elseif(date('Y-m-d') >  Data($ES['1trifim'],2) AND date('Y-m-d') <  Data($ES['3triini'],2)){ return 2; }
			elseif(date('Y-m-d') >= Data($ES['3triini'],2) AND date('Y-m-d') <= $ANOBASE.'12-31'     ){ return 3; }
			else{ return 1; }
		}else{return 1;}
	}
}
function qSem(){global $ES; if(date('Y-m-d') <= Data($ES['feriasini'],2)){return 1;}else{return 2;}}
function Trix($Mod){
	global $TRI, $TRIS;
	return ((TMod($Mod,'eja'))?qSem():$TRI);
}
function DiscAreaNome($Area,$Tipo=0){
	$AreaInfo = [
		0=>['DIVERSIFICADA','btn-fish','DIV','fish'],
		1=>['LINGUAGENS','btn-alinguagens','LNG','verpsc'],
		2=>['MATEMÁTICA','btn-amatematica','MAT','danger'],
		3=>['CIÊNCIAS HUMANAS','btn-ahumanas','CH','primary'],
		4=>['CIÊNCIAS DA NATUREZA E MATEMÁTICA','btn-anaturais','CN|M','warning'],
		5=>['TÉCNICO','btn-dark','TEC','dark']
	];
	
	return ($Tipo==='all')?$AreaInfo:$AreaInfo[$Area][$Tipo];
}
function DiaAula($vp){
    global $db;

    $Base = $db -> prepare("SELECT turmas_aulas.* FROM vinc_prof
    LEFT JOIN turmas_aulas ON (turmas_aulas.aulas_turma = vinc_prof.vp_turma AND turmas_aulas.aulas_disc = vinc_prof.vp_disc AND YEAR(turmas_aulas.aulas_dref) = YEAR(vinc_prof.vp_dref))
    WHERE vp_id = ?
    ORDER BY aulas_dia, aulas_hora");
    $Base -> bind_param("i",$vp);
    $Base -> execute();

    $Map = ['dias' => [], 'map'=>[]];
    foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
        @$Map['dias'][$V['aulas_dia']]++;
        $Map['map'][$V['aulas_id']] = $V;
    }
   # $Map['dias'] = array_filter($Map['dias']); 
   #ppre($Map);
    return $Map;
}

// FUNCOES LOADING
function sCfgJson(){
	global $ES, $MYSCT;
	// CRIA O NOVO ARQUIVO JSON NA PASTA
	$JsonConfig = json_encode($ES);
	$File = fopen(__ROOT__."/src/config_sct/$MYSCT.json",'w');
	$Return = fwrite($File,$JsonConfig);
	fclose($File);
	return is_numeric($Return)?true:false;
}
function sCfg($FromFile=true){
	global $db,$ANOBASE,$ANOATUAL,$MS,$MYSRE,$ES;

	// VERIFICA SE A PREFERENCIA DE CARREGAMENTO É VIA ARQUIVO JSON SALVO
	// SE SIM, PROCURA O ARQUIVO PARA CARREGAR E RETORN TRUE;
	if($FromFile AND isset($MS['sct']) AND is_file(@__ROOT__."/src/config_sct/".$MS['sct'].".json")){
		$ConfigJson = file_get_contents(__ROOT__."/src/config_sct/".$MS['sct'].".json");
		$GLOBALS['ES'] = json_decode($ConfigJson,true);
		return true;
	}

	// INICIA A CONSTRUÇÃO DO $ES
	$ES=['anos'=>[$ANOBASE],'mod'=>MyMod('all'),'cfgi'=>1];
	// PROCURA E CARREGA TODA A CONFIGURAÇÃO BASE
	// 1 - ANUAL; 2 - PERMANETE PARA IMÃ
	$MainConfig = $db->query("SELECT * FROM config_tesa WHERE cfgm_mod IN (1,2) ORDER BY cfgm_ano DESC")->fetch_all(MYSQLI_ASSOC);
	foreach($MainConfig as $K1=>$V1){if(!array_key_exists($V1['cfgm_nome'],$ES)){$ES[$V1['cfgm_nome']] = $V1['cfgm_valor'];}}
	
	// SE O SCT ESTIVER SIDO INFOMADO, CARREGA AS CONFIGURAÇÕES PARTICULARES DO SCT, SOBRESCREVENDO A BASE
	if(isset($MS['sct'])){
		// PROCURA TODOS OS REGISTROS DE ANOS
		// SELECIONA O PRIMEIRO ULTIMO ANO (PRIMEIRO ELEMENTO DO ARRAY)
		$AnoConfig = $db->prepare("SELECT DISTINCT cfg_ano FROM config WHERE cfg_secretaria = ? AND cfg_ano <= ? ORDER BY cfg_ano DESC");
		$AnoConfig -> bind_param("ii",$MS['sct'],$ANOATUAL); $AnoConfig -> execute(); $AnoConfig = $AnoConfig -> get_result();
		$AnoConfig = $AnoConfig -> fetch_all(MYSQLI_ASSOC); dbE();
		foreach($AnoConfig as $K1=>$V1){if(!in_array($V1['cfg_ano'],$ES['anos'])){$ES['anos'][] = $V1['cfg_ano'];}}
		rsort($ES['anos']); 

		// VERIFICA SE O ANO BASE É O MESMO DO ANO LOCALIZADO
		if(in_array($ANOBASE,$ES['anos'])){
			// SE FOR, CARREGA AS CONFIGURAÇÕES
			// SE NÃO FOR, MANTEM A BASE
			$EscConfig = $db->prepare("SELECT * FROM config WHERE cfg_secretaria = ? AND cfg_ano = ? ORDER BY cfg_ano DESC");
			$EscConfig -> bind_param("ii",$MS['sct'],$ANOBASE); $EscConfig -> execute(); $EscConfig = $EscConfig -> get_result();
			$EscConfig = $EscConfig -> fetch_all(MYSQLI_ASSOC);
			// VERIFICA SE AS CONFIGURAÇÕES DO ANO ATUAL EXISTEM
			if(@count($EscConfig) < 10){ $ES['cfgi'] = 0; }
			foreach($EscConfig as $K1=>$V1){$ES[$V1['cfg_nome']] = $V1['cfg_valor'];}
		}
	}
	// CARREGA QUEM IRÁ GERAR AS OCORRENCIAS
	$ES['ocreg'] = (array_key_exists('ocreg',$ES)) ? explode(',',$ES['ocreg']):[];
	$ES['obreg'] = (array_key_exists('obreg',$ES)) ? explode(',',$ES['obreg']):[];
	
	// CARREGA OS MODULOS ATIVOS
	if(array_key_exists('modulos',$ES) AND strlen($ES['modulos'])){ $ES['mod'] = [];
		foreach(explode(',',$ES['modulos']) as $K1=>$V1){ $ES['mod'][$V1] = $V1; }
	}
	
	$GLOBALS['ES']['srenome']=null;
	// REOORDENA OS HORARIOS DE AULAS
	foreach($ES as $K=>$V){

		$ID = explode('-',$K);
		
		// PARAMETRIZA QUEM PODERA ENVIAR OCORRENCIA E OBSERVAÇÕES
        if($ID[0] == 'div'){ $ES[$ID[1]][$ID[0].'-'.$ID[2]] = $V; }

        // ESTUDO ORIENTADO
        if($ID[0] == 'eo'){ $ES[$ID[1]][$ID[0].'-'.$ID[2]] = $V;}
        
        // ELETIVA
        if($ID[0] == 'elt'){ $ES[$ID[1]][$ID[0].'-'.$ID[2]] = $V; }
        
        // HORÁRIO DOS TURNOS
        if($ID[0] == 'horario'){ $ES[$ID[1]][$ID[0]][$ID[2]] = $V; }
	}
	
	$GLOBALS['ES'] = $ES;
	return false;
}
function MEU(){
	global $ES;
	if(isset($_SESSION['id'])){
		$GLOBALS['MEUID'] 	= $_SESSION['id'];
		$GLOBALS['MEULOGIN']= ((isset($_SESSION['lid']))?$_SESSION['lid']:null);
		$GLOBALS['MYSRE'] 	= ((isset($_SESSION['sre']))?$_SESSION['sre']:null);
		$GLOBALS['MYESC'] 	= ((isset($_SESSION['esc']))?$_SESSION['esc']:null);
		$GLOBALS['MYSCT'] 	= ((isset($_SESSION['sct']))?$_SESSION['sct']:null);
		$GLOBALS['TRI']     = ((isset($_SESSION['TRI']))?$_SESSION['TRI']:null);
		$GLOBALS['TRIS'] 	= ((isset($_SESSION['TRIS']))?$_SESSION['TRIS']:null);
		$GLOBALS['MEUTIPO'] = ((isset($_SESSION['tipo']))?$_SESSION['tipo']:null);
		$GLOBALS['MEUTURNO'] = ((isset($_SESSION['turno']))?$_SESSION['turno']:NULL);
		
		// NOME DA SRE
		if(array_key_exists("sre_".$GLOBALS['MYSRE'],$ES)){
			$ES['srenome'] = $ES["sre_".$GLOBALS['MYSRE']];
			foreach($ES as $K1=>$V1){ if(strstr($K1,'sre_')){
				unset($GLOBALS['ES'][$K1]);
			}}
		}

	} return false;
}
function LoadTRI(){
	#ppre($_SESSION);
	if(!isset($_SESSION['TRI'])){
		
		$_SESSION['TRI']  = qTri();
		$_SESSION['TRIS'] = qSem();

		if(!isset($GLOBALS['TRI'])){
			$GLOBALS['TRI']=$_SESSION['TRI'];
			$GLOBALS['TRIS']=$_SESSION['TRIS'];
			$GLOBALS['MS']['TRI']=$_SESSION['TRI'];
			$GLOBALS['MS']['TRIS']=$_SESSION['TRIS'];
		}
	}else{
		if($_SESSION['TRI']==false OR $_SESSION['TRI'] == 404){
			$_SESSION['TRI']  = qTri();
			$_SESSION['TRIS'] = qSem();
		}
		if(isset($GLOBALS['MS']['TRI'])){
			$GLOBALS['TRI']  = $_SESSION['TRI'];
			$GLOBALS['TRIS'] = $_SESSION['TRIS'];
		}
	}
}
function ExibirPainel(){
    $URI = $_SERVER['REQUEST_URI'];
    // Expressões regulares devidamente delimitadas
    $patterns = [
        '#/turmas/avaliacoes#',         // Não precisa de regex elaborada, mas está delimitada
        '#^/turmas/provas/\d+/corrigir$#'   // Permite qualquer número no lugar de "4"
    ];
    foreach($patterns as $pattern){
        if(preg_match($pattern, $URI)){
            return true;
        }
    }
    return false;
}


function Replace($Array,$Html){
	$Saida = [[],[]];
	foreach($Array as $K=>$V){
		if(!is_array($V)){
			$Saida[0][] = '{'.$K.'}';
			$Saida[1][] = $V;
		}
	}
	return str_replace($Saida[0],$Saida[1],$Html);
}


// FUNCOES DE USUÁRIO
function UserTipo($Tipo,$Retorno=false){
	global $MEUTIPO;

	$Perfis = [
		0 => ['admin','ADMIN','ADM','text-bg-dark','dark'],
		10=> ['sre','SRE','SRE','text-bg-info','info'],
		30=> ['gestor','GESTOR(A)','GES','text-bg-danger','danger'],
		31=> ['secretaria','SECRETARIA','SEC','text-bg-success','success'],
		32=> ['professor','PROFESSOR(A)','PRO','text-bg-secondary','secondary'],
		33=> ['aluno','ESTUDANTE','EST','text-bg-verpsc','verpsc'],
		34=> ['coordenador','COORDENADOR','COD','text-bg-wine','wine'],
		35=> ['apoio','APOIO','APO','text-bg-rosapk','rosapk'],
		36=> ['biblioteca','BIBLIOTECÁRIO(A)','BIB','text-bg-wicold border border-secondary','wicold'],
		37=> ['aee','PROFESSOR(A) AEE','AEE','text-bg-purple','purple'],
		
		40=> ['conesc','CONSELHO ESCOLA','CONESC','text-bg-verdlm','verdlm'],
		
		'ativo'=>[0=>['bg-danger text-white','DESATIVADO','D'],1=>['bg-success text-white','ATIVO','A']],
	];
	
	if($Tipo==='all'){ 
		$Map = $Perfis; 
		
		if($Retorno==false){ return $Map; }
		if($Retorno=='filter'){

			if($MEUTIPO > 0){ unset($Map[0]); } // REMOVE ADMIN
			if($MEUTIPO >= 30){ unset($Map[10]); } // REMOVE SRE
			unset($Map[40], $Map['ativo']); // REMOVE STATUS

			$Map[30][1] .= " / PEDAGOGO(A)";
			
		return $Map;}
	
	}elseif(is_numeric($Tipo)){

		switch($Retorno){
			case 'icon': return '<span class="rounded py-0 ft-10 p-1 '.$Perfis[$Tipo][3].'">'.$Perfis[$Tipo][2].'</span>'; break;
			case 'nome': return $Perfis[$Tipo][1]; break;
			case 'mini': return $Perfis[$Tipo][2]; break;
			case 'cor': return $Perfis[$Tipo][3]; break;
			case 'ativo': return '<span class="rounded py-0 px-2 '.$Perfis['ativo'][$Tipo][0].'" data-toggle="tooltip" title="'.$Perfis['ativo'][$Tipo][1].'">'.$Perfis['ativo'][$Tipo][2].'</span>'; break;
			case 'ativofull': return '<span class="rounded py-0 px-2 '.$Perfis['ativo'][$Tipo][0].'">'.$Perfis['ativo'][$Tipo][1].'</span>'; break;
			case false:  return $Perfis[$Tipo][0]; break;
		}

	}else{return false;}
}
function MyMod($Tipo=false){
	global $ES;
	$Modulos = [
		0  => ['DIV','Diversificada','heartbeat'],
		1  => ['TUT','Tutoria','map-signs'],
		2  => ['AGD','Agendas','calendar-week'],
		3  => ['EO', 'Estudo Orientado','book'],
		4  => ['ELT','Eletivas','charging-station'],
		5  => ['CLB','Clubes','cube'],
		6  => ['FQC','Frequência','heart-broken'],
		7  => ['GUI','Guias de Aprendizagem','book-open'],
		8  => ['PLA','Planos de Ação','file-alt'],
		9  => ['PRO','Programas de Ação','file-medical-alt'],
		10 => ['APNP','Atividades Não Presenciais','cloud'],
		11 => ['PRF','Perfil das Turmas','clipboard-check'],
		12 => ['REN','Rendimento','diagnoses'],
		13 => ['SOC','Socioemocional','sun'],
		14 => ['REL','Relatórios','file'],
		15 => ['BIB','Biblioteca','bookmark'],
		16 => ['MER','Merenda','utensils']
	];
	// EXIBE A LISTAGEM DE MODULOS
	if($Tipo == 'ver'){ return $Modulos; }
	// CARREGA TODOS OS MÓDULOS
	if($Tipo == 'all'){ return array_keys($Modulos);}
	// VERIFICA SE ESTA ATIVO
	if(is_numeric($Tipo)){ return (isset($ES['mod']) AND in_array($Tipo,$ES['mod'])) ? true : false; }else{ return false; }	
}


// FUNCOES ADICIONAIS
function gerarQRCodeBase64($url) {
    // Cria o QR Code com a URL fornecida
    $qrCode = QrCode::create($url)
        ->setSize(300) // Tamanho do QR Code
        ->setEncoding(new Encoding('UTF-8')); // Definindo a codificação UTF-8

    // Usa o writer para gerar o PNG do QR Code
    $writer = new PngWriter();
    $qrCodeImage = $writer->write($qrCode);

    // Obtém o conteúdo do QR Code em formato PNG
    $imagemPng = $qrCodeImage->getString();

    // Codifica a imagem em Base64
    $imagemBase64 = base64_encode($imagemPng);

    // Retorna a string Base64 no formato de URL data:image/png
    return 'data:image/png;base64,' . $imagemBase64;
}

function ColorDestak($valor,$Mod=false,$TextBG=true){
	// ELABORA O PREFIXO TEXTBG
	$TextBG = ($TextBG == true) ? 'text-bg-' : NULL;
	// GERAL
	if($Mod===false){
		return $TextBG . (($valor >= 60) ? 'success':'danger');
	}
	// OUTROS MOD
	if($Mod==='boletim'){
		return $TextBG . (($valor >= 60) ? '':'danger');
	}

	// DISTRIBUICAO
	if($Mod==='range'){
		switch(true){
			case ($valor < 25): return $TextBG . 'dark'; break;
			case ($valor < 40): return $TextBG . 'danger'; break;
			case ($valor < 60): return $TextBG . 'warning'; break;
			case ($valor < 75): return $TextBG . 'primary'; break;
			case ($valor >=75): return $TextBG . 'success'; break;
		} return false;
	}


	return false;
}
function SctTipo($Sct,$Mod=0){
	$Tipos = [
		0 => ['PE','PÚBLICA ESTADUAL','success'],
		1 => ['PM','PÚBLICA MUNICIPAL','primary'],
		2 => ['PV','PRIVADA','warning']
	];

	if($Mod==='all'){return $Tipos;}

	if(array_key_exists($Sct,$Tipos)){
		if(array_key_exists($Mod,$Tipos[$Sct])){
			return $Tipos[$Sct][$Mod];
		}
	} return false;
}
function Button($Tipo='save',$Badge=false,$ButtonID=NULL){

	$BadgeButton = '<span id="FNButtonBadge" class="badge-alt text-bg-warning float-start">'.(is_numeric($Badge)?$Badge:0).'</span>';
	$BadgeButton = ($Badge === false) ? NULL : $BadgeButton ;
	$ButtonID = (strlen($ButtonID))?$ButtonID:NULL;

	switch($Tipo){
		case 'save': return '<button type="submit" id="'.$ButtonID.'" class="btn btn-sm btn-success w-px-150">'.$BadgeButton.'<i class="fa fa-save me-1"></i> SALVAR</button>'; break;
		case 'save-b': return '<button type="button" id="'.$ButtonID.'" class="btn btn-sm btn-success w-px-150">'.$BadgeButton.'<i class="fa fa-save me-1"></i> SALVAR</button>'; break;
		case 'upload': return '<button type="submit" id="'.$ButtonID.'" class="btn btn-sm btn-success w-px-150">'.$BadgeButton.'<i class="fa fa-upload me-1"></i> ENVIAR</button>'; break;
		case 'upload-b': return '<button type="button" id="'.$ButtonID.'" class="btn btn-sm btn-success w-px-150">'.$BadgeButton.'<i class="fa fa-upload me-1"></i> ENVIAR</button>'; break;
		default: return false;
	}
}
function ppre($vetor){
	global $MEUTIPO,$MS; if($MEUTIPO != 0 AND @$MS['superuser']==false AND $_SERVER['HTTP_HOST']!='learnpilot.com'){return false; }
	foreach($GLOBALS as $K1=>$V1){
		if(is_array($V1) AND $V1===$vetor){print "[=======( $K1 )=======]"; break;}
	}
	print '<pre>'; print_r($vetor); print '</pre>';
	return false;
}
function EchoTri($Valor){
	return ($Valor<=3?$Valor:($Valor-3)).'º '.($Valor<=3?'TRI':'SEM');
}
function iSelect($valor,$check){ return $valor == $check ? 'selected="selected"':null; }
function iCheck($valor,$check,$precision=false){ if($precision){ return $valor === $check ? 'checked':null; }else{ return $valor == $check ? 'checked':null; } }
function ReKey($Array,$Key){$ATemp = []; if(is_array($Array) AND $Key){foreach($Array as $K=>$V){if(array_key_exists($Key,$V)){$ATemp[$V[$Key]] = $V;}}}else{return false;} unset($Array); return $ATemp;}
function Alert($texto,$color=false){print '<div class="card shadow-md my-2"><div class="card-header text-bg-'.($color?'success':'danger').'"><i class="fa fa-exclamation-triangle"></i> ATENÇÃO!</div><div class="card-body text-center">'.$texto.'</div></div>'; return null;}
function Calendario($MES=false,$Saida=false){
	global $ANOBASE;
	$MES = (is_numeric($MES))?$MES:date('m');
	$MES = ($MES<0)?1:$MES; $MES = ($MES>12)?12:$MES;
	$MAX = date('t',strtotime("$ANOBASE-$MES-$01"));
	$FDay = date("Y-m-d",strtotime("$ANOBASE-$MES-01 ".(1 - intval(date('N',strtotime("$ANOBASE-$MES-01"))))." days"));
	$LDay = date("Y-m-d",strtotime("$ANOBASE-$MES-$MAX ".(5 - intval(date('N',strtotime("$ANOBASE-$MES-$MAX"))))." days"));
	$Cd = []; $DIA = $FDay;
	if($Saida==='fl'){
		// INICIO E FIM DO MES
		$Cd = [$FDay,$LDay];
	}else{
		while($DIA <= $LDay){
			$N = intval(date("N",strtotime($DIA)));
			if($N!=6 AND $N!=7){
				$INS = date('d',strtotime($DIA));
				$Cd[intval(date("W",strtotime($DIA)))][$N] = ($Saida==false) ? (($MES!=date('m',strtotime($DIA))) ? 'false' : $INS) : $INS;
			} $DIA = date("Y-m-d",strtotime("$DIA +1 day"));
		}
	} return $Cd;
}
function is_Admin($tipo=false){
	global $MS,$MEUTIPO;
	if(isset($MS['id']) AND ($MEUTIPO==30 OR $MEUTIPO==0)){return true;}elseif(is_numeric($tipo) AND $tipo == $MEUTIPO){return true;}else{return false;}
}
function is_localhost($Action=false){
	if($Action){
		return ($_SERVER['HTTP_HOST']=='learnpilot.com')?NULL:'d-none';
	}else{
		return ($_SERVER['HTTP_HOST']=='learnpilot.com')?true:false;
	}
}
function is_par($Valor){
	return ($Valor % 2 == 0)?true:false;
}
function isPraticas($id=false){
	global $ES; $Pra = [24,25,26,27,28,90];
	if(isset($ES['altdisc']) AND $ES['altdisc']==0){ $Pra[] = 23; $Pra[] = 9; }
	
	if(is_numeric($id)){ return in_array($id,$Pra); }else{
		if($id=='all'){ return $Pra; }
		elseif($id=='in'){ return "AND disc_id IN (".implode(',',$Pra).")"; }
		else{ return " AND disc_id NOT IN (".implode(',',$Pra).")"; }
	}
}

function CalendarioFull($Meses=[1,12],$NoRepeat=false){
	global $ANOBASE;
	if(is_array($Meses) AND count($Meses)==2){
		$Map=[];
		for($MES=$Meses[0];$MES<=$Meses[1];$MES++){
			$MAX = date('t',strtotime("$ANOBASE-$MES-$01"));
			$FDay = date("Y-m-d",strtotime("$ANOBASE-$MES-01 ".(1 - intval(date('N',strtotime("$ANOBASE-$MES-01"))))." days"));
			$LDay = date("Y-m-d",strtotime("$ANOBASE-$MES-$MAX ".(5 - intval(date('N',strtotime("$ANOBASE-$MES-$MAX"))))." days"));
			$Cd = []; $DIA = $FDay;
			while($DIA <= $LDay){
				$N = intval(date("N",strtotime($DIA)));
				if($N!=6 AND $N!=7){
					#$INS = date('d',strtotime($DIA));
					if(!array_key_exists($MES,$Map)){$Map[$MES]=[];}
					$Map[$MES][intval(date("W",strtotime($DIA)))][$N] = $DIA;
				} $DIA = date("Y-m-d",strtotime("$DIA +1 day"));
			}
		}
		if($NoRepeat){
			foreach($Map as $K1=>$V1){if($K1 > 1){
				foreach($V1 as $K2=>$V2){
					if(array_key_exists($K1-1,$Map) AND array_key_exists($K2,$Map[$K1-1])){
						if(intval(date('m',strtotime($Map[$K1-1][$K2][1])))==($K1-1)){
							unset($Map[$K1][$K2]);
						}else{
							unset($Map[$K1-1][$K2]);
						}
					}
				}
			}}
		}
		return $Map;
	} return false;
}
function CalendarioCor($Tipo,$In=1){
	$Dados = [
		1 => ['PROVAS','danger'],
		2 => ['TRABALHOS','warning'],
		3 => ['APRESENTAÇÕES','success'],
		4 => ['OUTROS','info'],
		5 => ['FERIADOS','rosapk']
	];
	if($In === 'all'){return $Dados; }
	if(is_numeric($In)){
		return $Dados[$Tipo][$In];
	}
}
function Turno($Turno=3,$Mod=0){
	$Turno = (is_array($Turno) AND array_key_exists('turma_turno',$Turno)) ? $Turno['turma_turno'] : $Turno;
	if(is_null($Turno)){return false;}
	
	$Turnos = [
		0 => ['M','MATUTINO','verpsc','white','bg-verpsc text-white'],
		1 => ['V','VESPERTINO','warning','dark','bg-warning  text-dark'],
		2 => ['N','NOTURNO','secondary','white','bg-secondary text-white'],
		3 => ['I','INTEGRAL','danger','white','bg-danger text-white']
	];
	if($Mod===true){ return $Turnos; }
	if($Mod===false){ return $Turnos[$Turno]; }
	if($Mod===null){ return false; }
	if(is_numeric($Mod)){ return $Turnos[$Turno][$Mod]; }
	return false;
}
function NTurma($dados,$mod=0){
	if(!is_array($dados) OR array_key_exists('turma_serie',$dados)==false){ return false; }
	$dados['turma_comp'] = (isset($dados['turma_comp']) AND strlen($dados['turma_comp'])==3)?$dados['turma_comp']:null;
	$isINF = TMod($dados['turma_mod'],'infantil');

	$RangAlf = range('A', 'Z');
	$Serie = ($isINF?'Grupo ':'') . $dados['turma_serie'] . ($isINF?' ':'º ');
	$Numero = ($isINF ? $RangAlf[$dados['turma_num']-1] : ($dados['turma_num']>9?:"0".$dados['turma_num']));

	switch($mod){
		case 0: return $Serie.$Numero.' '.$dados['turma_comp'].' '.TMod($dados['turma_mod']).' - '.Turno($dados['turma_turno']); break;
		case 1: return $Serie.$Numero.' '.$dados['turma_comp'].' '.TMod($dados['turma_mod']).' - '.Turno($dados['turma_turno'],1); break;
		case 2: return $Serie.$Numero.' '.$dados['turma_comp'].' '.TMod($dados['turma_mod']); break;
		case 3: return $Serie.' '.$dados['turma_comp'].' '.TMod($dados['turma_mod']);
		case 4: return $Serie.$Numero.' '.$dados['turma_comp']; break;
		case 5: return $Serie.$Numero.' '.$dados['turma_comp']; break;
		case 6: return $Serie.' '.(TMod($dados,'nomeclatura')); break;
		default: return NSerie($dados).' '.$dados['turma_num'].' '.$dados['turma_comp'];	

	}
}
function TMod($turma,$tipo=1){ 
	$turma = (is_numeric($turma)) ? $turma : ((is_array($turma) AND array_key_exists('turma_mod',$turma)) ? $turma['turma_mod'] : 1);
	$Tipo = [
		0 => ['ENSINO FUNDAMENTAL','EF','text-bg-danger','danger'],
		1 => ['ENSINO MÉDIO','EM','text-bg-info','info'],
		2 => ['ENSINO MÉDIO INTEGRADO','EMI','text-bg-info','info'],
		
		3 => ['ENISNO DE JOVENS E ADULTOS FUNDAMENTAL','EJA EF','text-bg-secondary','secondary'],
		4 => ['ENISNO DE JOVENS E ADULTOS MÉDIO','EJA EM','text-bg-secondary','secondary'],
		5 => ['ENISNO DE JOVENS E ADULTOS MÉDIO PROFISSIONALIZANTE','EJA EMP','text-bg-secondary','secondary'],

		6 => ['BERÇÁRIO','BER','text-bg-verpsc','verpsc'],
		7 => ['EDUCAÇÃO INFANTIL','EI','text-primary','primary']
	];

	$Map = [
		'INFANTIL' => [6,7],
		'REGULAR' => [0,1,2],
		'EJA' => [3,4,5]
	];
	
	if($tipo === true OR $tipo === 'all'){ return $Tipo; }
	if($tipo === 'map'){ return $Map; }
	if($tipo === 'eja' OR $tipo === 'regular' OR $tipo === 'infantil'){
		$Mod = ['regular'=>[0,1,2],'eja'=>[3,4,5],'infantil'=>[6,7]];
		if(array_key_exists($tipo,$Mod)){
			return (in_array($turma,$Mod[$tipo]));
		} return false;
	}
	if($tipo === 'nomeclatura'){
		$Mod = [
			'ano' => [0,3],
			'serie' => [1,2,4,5],
		];
		foreach($Mod as $KeyM=>$ViewM){
			if(in_array($turma,$ViewM)){
				return mb_strtoupper($KeyM,'UTF-8');
				break;
			}
		}
		return false;
	}
	return $Tipo[$turma][$tipo];
}
function is_pautaConceito($Turma,$Serie=2){
	// VERIFICA OS ELEMENTOS PASSADOS PELO VETOR
	if(!is_array($Turma) OR !array_key_exists('turma_mod',$Turma) OR !array_key_exists('turma_serie',$Turma)){ return false; }
	if(TMod($Turma['turma_mod'],'infantil')){return true;}
	return (($Turma['turma_mod'] == 0 OR $Turma['turma_mod'] == 3) AND $Turma['turma_serie'] <= $Serie)?true:false;
}
function NSerie($serie){
	if(is_numeric($serie)){return $serie.'º';}else{
		if(is_array($serie) AND array_key_exists('turma_serie',$serie)){
			return $serie['turma_serie'].'º';
		}
	}
	return false;
}
function SMenu($In = false, $On = 'show', $Out = null){
	global $URI;
	if (!array_key_exists(0, $URI)) {
		return false;
	}
	if ($URI[0] == $In) {
		print $On;
	} else {
		print $Out;
	}
	//return true;
}
function Infracao($i,$mod=0){
	$I = [
		0 => ['OUTRO',NULL,false,'bg-success text-white','success'],
		4 => ['DEVERES DISCENTE',74,false,'bg-secondary text-white','secondary'],
		1 => ['LEVE',81,true,'bg-info text-white','info'],
		2 => ['GRAVE',82,true,'bg-warning','warning'],
		3 => ['INFRACIONAL',83,true,'bg-danger text-white','danger']
	  ];
	
	if($mod==='all'){return $I;}
	return $I[$i][$mod];
}
function Romanos($integer) {
	if(!is_numeric($integer)){return null;}
	$table = array('M'=>1000, 'CM'=>900, 'D'=>500, 'CD'=>400, 'C'=>100, 'XC'=>90, 'L'=>50, 'XL'=>40, 'X'=>10, 'IX'=>9, 'V'=>5, 'IV'=>4, 'I'=>1); $return = '';
	while($integer > 0){foreach($table as $rom=>$arb){if($integer >= $arb){$integer -= $arb;$return .= $rom;break;}}}
	return $return;
}
function Pilar($Num,$mod=0){
	$Pilares = [
		1=>['CONHECER','success','bg-success text-white','rgba(25,135,84,1)'],
		2=>['FAZER','warning','bg-warning text-dark','rgba(255,193,7,1)'],
		3=>['CONVIVER','primary','bg-primary text-white','rgba(13, 110, 253, 1)'],
		4=>['SER','danger','bg-danger text-white','rgba(220, 53, 69, 1)']
	];
	if($mod==='all'){ foreach($Pilares as $K1=>$V1){$Map[$K1] = $V1[0];} return $Map; }
	return $Pilares[$Num][$mod];
}
function Periodo($Number=false,$Mod=false){
	global $TRI,$TRIS;

	$Periodo = (is_numeric($Number) OR $Number=='F')
	? $Number : (
		(TMod($Mod,'eja')?$TRIS:$TRI)
	);

	switch($Periodo){
		 case 0: print 'Entrada'; break;
		 case 1: print '1º trimestre'; break;
		 case 2: print '2º trimestre'; break;
		 case 3: print '3º trimestre'; break;
		 case 4: print '1º semestre'; break;
		 case 5: print '2º semestre'; break;
		 case 'F': print 'Final'; break;
	}
	return false;
}
function PerfilNome($id,$mod='all',$TurmaMod=false){
	$Nomes = [
		1 => ['INSATISFATÓRIO',[1,2],'dark'],
		2 => ['REGULAR',[3,4],'danger'],
		3 => ['BOM',[5,6],'warning'],
		4 => ['MUITO BOM',[7,8],'primary'],
		6 => ['EXCELENTE',[9,10],'success']
	];

	$Periodo = [
		0 => 'Entrada',
		1 => '1º trimestre',
		2 => '2º trimestre',
		3 => '3º trimestre',
		4 => '1º semestre',
		5 => '2º semestre'
	];

	$Order = []; foreach($Nomes as $V){foreach($V[1] as $V1){ $Order[$V1] = $V; }}
	
	$Tri = false; if($mod=='time' AND is_numeric($id) AND is_numeric($TurmaMod)){
		$Tri = ($id == 0 ? $Periodo[0] : (TMod($TurmaMod,'eja') ? $Periodo[$id+3] : $Periodo[$id]));
	}

	switch($mod){
		case 'all': return $Nomes; break;
		case 'order': return $Order; break;
		case 'nome': return @$Order[$id][0]; break;
		case 'cor': return @$Order[$id][2]; break;
		case 'periodo': return @$Periodo[$id]; break;
		case 'periodoall': return $Periodo; break;
		case 'time': return $Tri; break;
	}
}
function LockEO(){
	global $ES,$MEUTURNO;
	$Data = @$ES["eo-$MEUTURNO-data"]; 
	$Hora = @$ES["eo-$MEUTURNO-hora"];
	if(!is_numeric($Data) OR !is_numeric($Hora)){ Alert('Nenhuma configuração de lançamento para o estudo orientado foi encontrada. Informe a gestão!'); return false; }
	return (
		(date('N') <= $Data) ? true : (date('H') < $Hora ? false:true)
	);
}
function EOSit($Sit,$Mod=0){
	if(!is_numeric($Sit) OR $Sit < 0 OR $Sit > 3){return false;}

	$Status = [
		0 => ['A','AGUARDANDO','info'],
		1 => ['F','FALTOU','warning'],
		2 => ['N','NÃO CUMPRIU','danger'],
		3 => ['C','CUMPRIDO','success'],
	];

	if(is_numeric($Mod)){ return $Status[$Sit][$Mod]; }
	if($Mod === 'all'){ return $Status; }
	return false;
}
function VarrerIsNumeric($Array){ // VALIDA SE A LISTA É SÓ DE NÚMEROS
	if(is_array($Array)){foreach($Array as $K1=>$V1){if(!is_numeric($V1)){unset($Array[$K1]);}} return $Array;} return false;
}
function Byte2($size){
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}  
function FileIcon($Ext,$Mod='icon'){
	$TIPO = [
		'pdf'=>['file-pdf','danger'],
		'doc'=>['file-word','primary'],
		'docx'=>['file-word','primary'],
		'png'=>['file-image','verpsc'],
		'jpg'=>['file-image','verpsc'],
		'jpge'=>['file-image','verpsc'],
		'gif'=>['file-image','verpsc'],
		'bug'=>['file','secondary']
	];
	$Ext = array_key_exists($Ext,$TIPO) ? $Ext : 'bug';
	switch($Mod){
		case 'icon': return $TIPO[$Ext][0]; break;
		case 'color': return $TIPO[$Ext][1]; break;
		default: return $TIPO;
	}
}
function Files($id,$SCT=false){
	global $db, $MYSCT; $mID = []; $Map = []; 
	// CASOS
	if(is_numeric($id)){ $mID[] = $id; } // SE FOR UM NÚMERO
	if(is_array($id)){ $mID = VarrerIsNumeric($id); } // SE FOR UM ARRAY
	if(is_string($id)){$mID = VarrerIsNumeric(explode(',',$id)); } // E FOR UMA STRING
	// VERIFICA SE EXISTE ALGUM VALOR DE ID NO VETOR
	if(count($mID) == 0){return [];}
	// PROCURA OS ARQUIVOS NA BASE
	$Base = $db -> query("SELECT *, CONCAT(fl_dir,fl_arquivo) as fl_download FROM files WHERE fl_id IN (".implode(',',$mID).") ORDER BY fl_dref DESC") -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $V1){
		$Map[$V1['fl_id']] = $V1;
		$Map[$V1['fl_id']]['fl_icon'] = FileIcon(pathinfo($V1['fl_nome'],PATHINFO_EXTENSION));
		$Map[$V1['fl_id']]['fl_icon_color'] = FileIcon(pathinfo($V1['fl_nome'],PATHINFO_EXTENSION),'color');
	}
	return ($Map);
}
function Anexos($FId=false,$color='secondary',$aceitos='.doc,.docx,.pdf'){
	global $db; $Files=[];
	if($FId!=false){
		if(is_numeric($FId)){$Files[]=$FId;}
		elseif(is_array($FId)){$Files = VarrerIsNumeric($FId);}
		else{$Files = explode(',',$FId); $Files = VarrerIsNumeric($Files);}
		if(count($Files)>0){
			$Base = $db -> query("SELECT * FROM files WHERE fl_id IN (".implode(',',$Files).")"); dbE();
			$Files = $Base -> fetch_all(MYSQLI_ASSOC);
		}
	}
	$Html = '<fieldset class="main" data-accept="'.$aceitos.'">';
	$Html .='<legend>ANEXOS</legend><div id="MyAnexosFiles">';	
	$Html .='<div class="btn-group ms-1"><button type="button" id="BAnexo" onclick="MyFilesLoad();" class="btn btn-sm btn-'.$color.' mb-1"><i class="fa fa-paperclip me-1"></i> ANEXO</button></div>';
	
	foreach($Files as $K1=>$V1){
		$Html .='<div class="btn-group bFile ms-1 mb-1" data-file="fl-'.$V1['fl_id'].'">';
		$Html .='<button class="btn btn-sm btn-outline-danger">'.$V1['fl_nome'].'</button>';
		$Html .='<input type="hidden" name="fl-'.$V1['fl_id'].'" value="'.$V1['fl_id'].'">';
		$Html .='<button type="button" class="btn btn-sm btn-danger mpoint bTrash"><i class="fa fa-trash"></i></button>';
		$Html .='</div>';
	}

	$Html .='</div></fieldset>';
	return $Html;
}
function TextTag($Texto,$All=false){
	switch($All){
		case true: return strip_tags($Texto); break;
		case false: return strip_tags($Texto,'<img><a><p><font><b><strong><span><br><hr><i><li><ol><ul><table><tr><td><u><em><i>'); break;
		default: return strip_tags($Texto,$All);
	}
}
function GetPhone($User=false,$Turma=false){
	global $db,$MYSCT,$ANOBASE;
	$User = (is_numeric($User))?$User:false;
	$Turma = (is_numeric($Turma))?$Turma:false;
	$Map = []; if(!$User AND !$Turma){return $Map;}
	
	$Base = $db -> query("
	SELECT
		DISTINCT (user_id), ui_nome, 
		(CASE WHEN ui_notificar_mae = 1 THEN ui_telmae ELSE NULL END) as ui_telmae, 
		(CASE WHEN ui_notificar_pai = 1 THEN ui_telpai ELSE NULL END) as ui_telpai
	FROM userinfo
	INNER JOIN user ON (user.user_login = userinfo.ui_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id)
	WHERE vinc_turma.vt_sit = '0'
		".((is_numeric($User))?"AND user.user_id = '$User'":Null)."
		".((is_numeric($Turma))?"AND vinc_turma.vt_turma = '$Turma'":Null)."
	AND user.user_secretaria = '$MYSCT' AND YEAR(vinc_turma.vt_dref) = '$ANOBASE'");dbE();# -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		$Map[$V1['user_id']] = [
			'nome' => $V1['ui_nome'],
			'phone' => array_unique([$V1['ui_telmae'],$V1['ui_telpai']])
		];
	}
	// VERIFICAÇÃO E EXCLUSÃO DE TELEFONE
	foreach($Map as $K1=>$V1){
		foreach($V1['phone'] as $K2=>$V2){if(strlen($V2)==11){if($V2[0] != '0'){$Map[$K1]['phone'][$K2] = "0$V2";}}else{unset($Map[$K1]['phone'][$K2]);}}
		if(count($Map[$K1]['phone']) == 0){ unset($Map[$K1]); }
	}
	return $Map;
}
function UniqMD5(){return md5(date('dmY His').rand(0,99999999).date('Y-m-d H:i:s'));}
function OcorrenciaStatus($Array,$mod=0){
	if(!is_array($Array) AND !isset($Array['oc_sit'],$Array['oc_dev'])){return false;}
	$status = (($Array['oc_sit']==0)?'D':'S').$Array['oc_dev'];
	$St = [
		'S0' => ['C','CRIADO','text-bg-danger border-danger'],
		'D0' => ['A','AGUARDANDO','text-bg-warning text-dark border-dark'],
		'D1' => ['V','VISUALIZADA','text-bg-info border-info'],
		'D2' => ['V','VISUALIZADA','text-bg-info border-info'],
		'S1' => ['F','FECHADO','text-bg-success border-success'],
		'S2' => ['F','FECHADO','text-bg-success border-success'],
	];
	if($mod == 0){return '<span class="badge-alt ft-10 border py-1 px-2 '.$St[$status][2].'" data-toggle="tooltip" title="'.$St[$status][1].'" data-placement="right">'.$St[$status][0].'</span>';}
	return false;
}
function OcorrenciaTipo($Array,$mod=0){
	$St = [
		0 => ['OC','OCORRÊNCIA DISCIPLINAR','text-bg-danger border-danger'],
		1 => ['OP','OBSERVAÇÃO PEDAGÓGICA','text-bg-warning border-dark'],
	];
	// CONDIGURAÇÕES
	if(is_numeric($Array) AND array_key_exists($Array,$St)){$Array['oc_tipo'] = $Array;}
	if(!is_array($Array) AND !isset($Array['oc_tipo'])){return false;}
	
	// MODO DE RETORNO
	if($mod == 0){return '<span class="badge-alt border ft-10 py-1 px-2 '.$St[$Array['oc_tipo']][2].'" data-toggle="tooltip" title="'.$St[$Array['oc_tipo']][1].'" data-placement="right">'.$St[$Array['oc_tipo']][0].'</span>';}
	return false;
}
function DeleteSMS($Key){
	global $db,$MYSCT,$ANOBASE;
	$Rmv = $db -> prepare("DELETE FROM sms WHERE sms_secretaria = ? AND YEAR(sms_dref) = ? AND sms_key = ? AND sms_status != '200'");
	$Rmv -> bind_param("iis",$MYSCT,$ANOBASE,$Key);
	return boolval($Rmv->execute());
}
function CheckRA($Num){
	$Zero = '';
	for($n=1; $n <= (8 - strlen($Num)); $n++){ $Zero .= '0'; } //NUMERAÇÃO MINIMA
	return $Zero . $Num;
}
function CheckMaxRA($Mais=false){
	global $db, $MYSCT;
	NumeroUnico:
	// NUMERO UNICO COM PREFIXO
	$UniqueNumber = $MYSCT . substr(date('Y'), -2) . substr(crc32(microtime()), -5);
	// VERIFICA SE O REGISTRO JÁ EXISTE
	$Verificar = $db -> query("SELECT ui_id FROM userinfo WHERE ui_matricula = '$UniqueNumber' LIMIT 1") -> fetch_assoc();
	if(is_array($Verificar) AND count($Verificar) >= 1){ goto NumeroUnico; }else{ return $UniqueNumber; }
}
function ValidarCPF($cpf){
    // Extrai somente os números
    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {return false;}
    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {return false;}
    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}
function is_engine(){
	global $URI, $URLMain; 
	
	switch($URI[0]){
		case 'exe': return true; break;
		case 'rmv': return true; break;
		case 'upg': return true; break;
		case 'secretaria': break; 		// ANALISA OS DEMAIS PARAMETROS
		default: return true; break;
	}

	// ANALISA O CASO DA SECRETARIA
	if(strstr($URLMain,'/secretaria/boletim/abrir')){return true;}

	return false;
}
function ESitStatus($A,$Data=false){
	// VERIFICA A DATA PASSADA E CONFERE SE O ALUNO FOI TRANSFERIDO OU REMANEJADO ANTES DESTA DATA
	// SE SIM, RETORNA FALSE, SE NÃO, RETORNA TRUE
	// TRUE E FALSE CORRESPONDE AO STATUS DE LIBERADO
	$Data = ($Data)?$Data:Data();
	if($A['vt_remanejado'] == NULL AND $A['vt_transferido'] == NULL){return true;}
	if($A['vt_remanejado'] != NULL AND $A['vt_remanejado'] < $Data){return true;}
	if($A['vt_transferido'] != NULL AND $A['vt_transferido'] < $Data){return true;}
	return false;
}
function ESit($Info,$Tipo=1){
	
	$Situacoes = [
		0=>['ATIVO',@$Info['vt_num'],'bg-lfocus-hover',''],
		1=>['REMANEJADO','R','bg-situ01','warning'],
		2=>['TRANSFERIDO','T','bg-situ02','danger'],
		3=>['ENCERRADO','E','bg-situ03','dark']
	];

	if($Tipo === 'lider'){
		if($Info['vt_lider'] == 1){return '<i class="fa fa-chess-king text-warning" data-toggle="tooltip" title="LIDER"></i>';}
		if($Info['vt_lider'] == 2){return '<i class="fa fa-chess-queen" data-toggle="tooltip" title="VICE LIDER"></i>';}
	}

	if($Tipo === 'miniicon'){
		return ($Info['vt_sit'] == 0) ? $Info['vt_num'] : '<span class="badge-alt text-bg-'.$Situacoes[$Info['vt_sit']][3].'">'.$Situacoes[$Info['vt_sit']][1].'</span>';
	}

	if($Tipo === 'icon'){
		return '<span class="badge-alt ms-1 w-px-100 text-bg-'.$Situacoes[$Info['vt_sit']][3].'">'.$Situacoes[$Info['vt_sit']][0].'</span>';
	}

	if(is_numeric($Tipo) AND array_key_exists($Tipo,$Situacoes)){
		return $Situacoes[$Info['vt_sit']][$Tipo];
	}

	return false;
}
function ESitLock($Info,$LockDate=Null){
	if(!is_array($Info) OR !array_key_exists('vt_sit',$Info) OR !array_key_exists('vt_remanejado',$Info)){ return false; }
	$HOJE = Data($LockDate,2)." 23:59:59";

	if($Info['vt_sit'] != 0){
		if($Info['vt_remanejado'] != null){
			if($HOJE >= $Info['vt_remanejado']){ return true; };
		}
	}
	return false;
}
function AviTipo($avi,$mod=1){
	$Tipo = [2=>['CON','CONCEITUAL','danger','danger'],1=>['PRO','PROCEDIMENTAL','verpsc','verpsc'],3=>['ATI','ATITUDINAL','purple','purple'],4=>['DIV','DIVERSIFICADA','warning text-dark','warning'],9=>['SEC','SECRETARIA','secondary','secondary']];
	switch($mod){
		case is_numeric($mod): return $Tipo[$avi][$mod]; break;
		case 'cor': return $Tipo[$avi][2]; break;
		case 'icon': return '<span class="btn btn-'.$Tipo[$avi][2].' btn-sm pdtb-0">'.$Tipo[$avi][0].'</span>'; break;
		case 'all': return $Tipo; break;
		default: return false;
	}
}
function URendimentoVP($VP,$SCT=false){
	global $ES,$db,$TRI,$ANOBASE,$MYSCT;
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	
	$C = 0; $Rend = 0; $Turma = null; $Map = ['pontos'=>[],'emap'=>[],'rend'=>[0,0]];
	
	// BUSCA AS AVALIAÇÕES E SOMA SEUS VALORES ARREDONDANDO
	$AVI = AVIMap($VP,$SCT);
	$Map['pontos'] = array_sum($AVI['max']);
	$Map['emap'] = $AVI['tot'];
	
	if(($Map['pontos']) > 0){
		
		// FILTRA QUEM NÃO TEM NOTA E QUEM ESTA DESATIVADO
		$findVP = findVP($VP,$SCT);
		if(!isset($findVP['vp_turma'])){ return false; }
	
		$Turma = TurmaEMap($findVP['vp_turma'],$SCT);
		foreach($Turma as $K1=>$V1){
			if(array_key_exists($V1['vt_user'],$Map['emap'])){
				if($V1['vt_sit'] != 0){
					unset($Map['emap'][$V1['vt_user']]);
				}
			}else{
				if($V1['vt_sit'] == 0){
					$Map['emap'][$V1['vt_user']] = 0;
				}
			}
		}
		// PROCESSA AS INFORMAÇÕES
		# TAXA DE APROVAÇÃO
		foreach($Map['emap'] as $K1=>$V1){
			$Map['rend'][($V1 >= 0.6 * $Map['pontos'])?1:0]++;
		}
		// CALCULA O RENDIMENTO
		if(array_sum($Map['rend'])){
			$Rend = number_format( 100 * $Map['rend'][1]/array_sum($Map['rend']),2);
		}
	}
	
	$Base = $db -> prepare("UPDATE vinc_prof SET vp_rend_$TRI = ?, vp_dref = NOW() WHERE vp_id = ? AND YEAR(vp_dref) = ? LIMIT 1");
	$Base -> bind_param('dii',$Rend,$VP,$ANOBASE);
	return boolval($Base -> execute());
}


/* ------------------------------------------------------------------------------------- */
// FUNÇÕES MAP
/* ------------------------------------------------------------------------------------- */
function SCTMap($SRE=false,$Sep=false,$Active=false,$Prefix=true){
	global $db;
	$Base = $db -> query("SELECT * FROM secretarias 
	INNER JOIN cidades ON (cidades.cit_id = secretarias.sct_cidade) 
	ORDER BY sct_id ASC;") -> fetch_all(MYSQLI_ASSOC);
	
	$PrefixMap = ['CEEFMTI ','EEEFM ','EEEF ','EEEM ','CEEMTI ','CEEFTI ','CEEMTI ','CEEFTI '];

	if(is_numeric($SRE)){
		foreach($Base as $K1=>$V1){
			if($V1['sct_sre']!=$SRE){unset($Base[$K1]);}
		}
	}
	
	$Map = ReKey($Base,'sct_id');
	
	// FILTRAR POR ATIVAS
	if($Active){
		foreach($Map as $K=>$V){
			if($V['sct_ativo'] == 0){unset($Map[$K]);}
		}
	}

	// REMOVE O PREFIXO
	if($Prefix==false){
		foreach($Map as $K=>$V){
			if($V['sct_esc']){
				$Map[$K]['sct_nome'] = str_replace($PrefixMap,NULL,$V['sct_nome']);
			}
		}
	}
	
	// SEPARA EM SREs
	if($SRE == false AND $Sep==true){
		$MapAlt = [];
		foreach($Map as $K1=>$V1){
			if(!array_key_exists($V1['sct_sre'],$MapAlt)){
				$MapAlt[$V1['sct_sre']] = array_merge($V1,['map'=>[]]);
			}else{
				$MapAlt[$V1['sct_sre']]['map'][$V1['sct_id']] = $V1;
			}
		}
		$Map = $MapAlt;
	}
	
	return $Map;
}

function CityMap($Cidade=false,$UF=false){ // MAPEIA AS CIDADES
	global $db;
	if(is_numeric($Cidade)){
		$City = $db -> prepare("SELECT *, CASE WHEN cit_uf = 0 THEN 'NÃO INFORMADO' WHEN cit_uf = 1 THEN 'ES' WHEN cit_uf = 2 THEN 'MG' WHEN cit_uf = 3 THEN 'RJ' WHEN cit_uf = 4 THEN 'BA' END as cit_estado FROM cidades WHERE cit_id = ? ORDER BY cit_uf, cit_nome ASC");
		$City -> bind_param("i",$Cidade);
	}else{
		$City = $db -> prepare("SELECT *, CASE WHEN cit_uf = 0 THEN 'NÃO INFORMADO' WHEN cit_uf = 1 THEN 'ES' WHEN cit_uf = 2 THEN 'MG' WHEN cit_uf = 3 THEN 'RJ' WHEN cit_uf = 4 THEN 'BA' END as cit_estado FROM cidades ORDER BY cit_uf, cit_nome ASC");
	}
	$City -> execute();
	$City = $City -> get_result() -> fetch_all(MYSQLI_ASSOC);

	if($UF){
		$TempCity = [];
		foreach($City as $K=>$V){ $TempCity[$V['cit_estado']][$V['cit_id']] = $V; }
		return $TempCity;
	}

	return ReKey($City,'cit_id');
}
function DiscMap($Order = false,$Tecnico=true){ // MAPEIA AS DISCIPLINAS
	global $db,$ES; $Disc=[]; $Stmt = $db -> query("SELECT * FROM disciplinas ORDER BY disc_area, disc_nome ASC");
	$Stmt -> fetch_all(MYSQLI_ASSOC); foreach($Stmt as $K1=>$V1){$Disc[$V1['disc_id']] = $V1;}
	
	if($Tecnico==true){$ES['ofertatecnico']=1;}
	if(isset($ES['ofertatecnico']) AND $ES['ofertatecnico']==0){foreach($Disc as $K1=>$V1){if($V1['disc_area']==5){unset($Disc[$K1]);}}}
	
	if($Order){
		// ORDENA POR AREA DE CONHECIMENTO
		$Temp = $Disc; $Disc = [];
		foreach($Temp as $K1=>$V1){$Disc[$V1['disc_area']][$K1] = $V1;}
	}
	return $Disc;
}
/* ------------------------------------------------------------------------------------- */
// FUNÇÕES FIND
/* ------------------------------------------------------------------------------------- */
function findSCT($SCT){
	global $db;
	$Base = $db -> prepare("SELECT
		main.*,
		cidades.*,
		sre.sct_nome as sre_nome,
		sre.sct_id as sre_id,
		secretarias_info.*
	FROM secretarias as main
	INNER JOIN cidades ON (cidades.cit_id = main.sct_cidade)
	INNER JOIN secretarias as sre ON (sre.sct_sre = main.sct_sre AND sre.sct_esc IS NULL)
	LEFT JOIN secretarias_info ON (main.sct_id = secretarias_info.scti_sct)
	WHERE main.sct_id = ?"); dbE();
	$Base -> bind_param("i",$SCT);
	$Base -> execute();
	return $Base -> get_result() -> fetch_assoc();
}
function findUser($Usuario,$SCT=false){ // PROCURA O USUARIO
	global $ANOBASE,$db,$MYSCT; $SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	if(is_numeric($Usuario)){

		$Base = $db -> prepare("SELECT user.*, userinfo.*, login.login_user FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		INNER JOIN login ON (login.login_id = user.user_login)
		WHERE user_id = ? AND user_secretaria = ? LIMIT 1");
		$Base -> bind_param('ii',$Usuario,$SCT);

	}elseif(is_array($Usuario)){
		if(count($Usuario)>0){
			$Base = $db -> prepare("SELECT user.*, userinfo.*, login.login_user FROM user
			INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
			INNER JOIN login ON (login.login_id = user.user_login)
			WHERE user_id IN (".implode(',',$Usuario).") AND user_secretaria = ? ");
			$Base -> bind_param('i',$SCT);
		}else{return false;}
	}else{return false;}
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);

	if(is_numeric($Usuario)){
		// RETORNA O USUÁRIO LOCALIZADO CASO EXISTA
		return (is_array($Base) AND array_key_exists(0,$Base)) ? $Base[0] : false;

	}else{ 
		// RETORNA O ARRAY COM VARIOS USUARIOS ENCONTRADOS
		return (is_array($Base) AND count($Base) > 0) ? $Base : false; 
	}
	
}
function findPFT($Turma,$User=false,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$MEUID;
	
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$User = (is_numeric($User))?$User:false;
	if(!is_numeric($Turma)){return false;}
	$Map = [0=>[],1=>[],2=>[],3=>[]];
	
	if($User==false){
		$Base = $db -> prepare("SELECT turmas_perfil_reg.* FROM turmas_perfil_reg
		INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma)
		WHERE pftr_turma = ? AND YEAR(pftr_dref) = ? AND turma_secretaria = ?");
		$Base -> bind_param("iii",$Turma,$ANOBASE,$SCT); $Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			$Map[$V1['pftr_tri']][$V1['pftr_pergunta']][] = $V1['pftr_valor'];
		}
		foreach($Map as $K1=>$V1){
			foreach($V1 as $K2=>$V2){
				if(is_array($V2) AND count($V2)>0){
					$Map[$K1][$K2] = number_format(array_sum($V2)/count($V2),0);
					if($Map[$K1][$K2] <= 0){$Map[$K1][$K2] = 1;}
					if($Map[$K1][$K2] > 10){$Map[$K1][$K2] =10;}
				}else{$Map[$K1][$K2] = null;}
			}
		}
	}else{
		// BUSCA AS INDICACOES
		$Base = $db -> prepare("SELECT turmas_perfil_reg.* FROM turmas_perfil_reg
		INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma)
		WHERE pftr_user = ? AND pftr_turma = ? AND YEAR(pftr_dref) = ? AND turma_secretaria = ?");
		$Base -> bind_param("iiii",$User,$Turma,$ANOBASE,$SCT); $Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			$Map[$V1['pftr_tri']][$V1['pftr_pergunta']] = $V1['pftr_valor'];
		}
		// BUSCA A INFORMAÇÃO SOBRE A TURMA
		$Base = $db -> prepare("SELECT * FROM turmas_perfil_info WHERE pfti_turma = ? AND pfti_user = ? AND YEAR(pfti_dref) = ?");
		$Base -> bind_param("iii",$Turma,$User,$ANOBASE);
		$Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			$Map[$V1['pfti_tri']]['info'] = $V1['pfti_info'];
		}
	}
	return $Map;
}