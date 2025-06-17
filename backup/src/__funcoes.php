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
function Logado(){
	global $MS;
	return (isset($MS['lid'],$MS['id']) AND is_numeric($MS['id']))?true:false;
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
function Alert($texto){print '<div class="card shadow-md my-2"><div class="card-header text-bg-danger"><i class="fa fa-exclamation-triangle"></i> ATENÇÃO!</div><div class="card-body text-center">'.$texto.'</div></div>'; return null;}
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
		default: return false; break;
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
function AgendaEscolarMap($Mes,$SCT=false){ // AGENDA DAS ESCOLAS
	global $db,$ANOBASE,$MYSCT; $SCT = ($SCT)?$SCT:$MYSCT; $Mes = intval($Mes);
	$Calendario = CalendarioFull([$Mes,$Mes])[$Mes];
	$DIni = reset($Calendario)[1];
	$DFim = end($Calendario)[5];
	
	$Base = $db -> prepare("SELECT agenda_escolar.*, ui_nome as nome, CONCAT(MONTH(ce_data),'-',DAY(ce_data)) AS MD FROM agenda_escolar
	INNER JOIN user ON (user.user_id = agenda_escolar.ce_user)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE (ce_data BETWEEN ? AND ?) AND YEAR(ce_data) = ? AND ce_secretaria = ?
	ORDER BY ce_data, ce_mod, ce_tipo ASC"); dbE();
	$Base -> bind_param('ssii',$DIni,$DFim,$ANOBASE,$SCT);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	$Map = []; foreach($Base as $K1=>$V1){ $Map[$V1['MD']][$V1['ce_id']] = $V1; }
	return $Map;
}
function AgendaServidorMap($UId=false,$SCT=false){ // AGENDA DOS SERVIDORES
	global $db,$ANOBASE,$MYSCT; $SCT = ($SCT)?$SCT:$MYSCT; $UId = (is_numeric($UId))?$UId:false; $Map = []; $UTemp = [];
	// PROCURA O USUÁRIO
	if(is_numeric($UId)){
		$User = findUser($UId,$SCT);
		if(array_key_exists(0,$User)){ $User = $User[0]; }
		if(!array_key_exists('ui_nome',$User)){ return false; }
		$Map[$UId] = [
			'nome' => $User['ui_nome'],
			'tipo' => $User['user_tipo'],
			'pic'  => $User['ui_pic'],
			'atual'=> false,
			'map'  => []
		];
		$UTemp[] = $UId;
	}else{
		foreach(ServidorMap('0,10,30,32,34') as $K1=>$V1){ 
			$Map[$V1['user_id']] = [
				'nome' => $V1['ui_nome'],
				'tipo' => $V1['user_tipo'],
				'pic'  => $V1['ui_pic'],
				'atual'=> false,
				'map'  => []
			];
			$UTemp[] = $V1['user_id'];
		}	
	}
	// LOCALIZA AS AGENDAS
	if(count($UTemp) > 0){
		$Semana = Data(null,13);
		$Base = $db -> prepare("SELECT DISTINCT WEEK(ag_data,1) as semana, ag_user FROM agenda_servidor 
		INNER JOIN user ON (user.user_id = agenda_servidor.ag_user) 
		WHERE user.user_secretaria = ? AND YEAR(ag_dref) = ?");
		$Base -> bind_param('ii',$SCT,$ANOBASE); $Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			if(in_array($V1['ag_user'],$UTemp)){
				$Map[$V1['ag_user']]['map'][$V1['semana']] = $V1['semana'];
				if($V1['semana'] == $Semana){
					$Map[$V1['ag_user']]['atual'] = intval($Semana);
				}
			}
		}
	}
	
	return (is_numeric($UId)) ? $Map[$UId] : $Map;
}
function AgendaTurmaMap($Turma,$Mes,$SCT=false){ // AGENDA DOS TURMAS 
	global $db,$ANOBASE,$MYSCT; $SCT = ($SCT)?$SCT:$MYSCT;
	$Turma = (is_numeric($Turma))?$Turma:false; $Mes = (is_numeric($Mes))?$Mes:false;
	$Datas = Calendario($Mes,'fl');
	$Map = ['T'=>[],'E'=>[]];
	#--- TURMA
	$Base = $db -> prepare("
	SELECT agenda_turma.*, turmas.turma_secretaria as TSeg, ui_nome as Unome, user.user_id as Uid, CONCAT(MONTH(ct_data),'-',DAY(ct_data)) AS MD FROM agenda_turma
	INNER JOIN user ON (user.user_id = agenda_turma.ct_user)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	INNER JOIN turmas ON (turmas.turma_id = agenda_turma.ct_turma)
	WHERE turmas.turma_secretaria = ? AND user.user_secretaria = ? AND YEAR(agenda_turma.ct_data) = ?
	AND turmas.turma_id = ? AND agenda_turma.ct_data BETWEEN ? AND ?"); dbE();
	$Base -> bind_param("iiiiss",$SCT,$SCT,$ANOBASE,$Turma,$Datas[0],$Datas[1]);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){ $Map['T'][$V1['MD']][$V1['ct_id']] = array_merge($V1,["info"=>$V1['ct_info']]); }
	#--- ESCOLA
	$Base = $db -> prepare("
	SELECT agenda_escolar.*, CONCAT(MONTH(ce_data),'-',DAY(ce_data)) AS MD FROM agenda_escolar
	WHERE ce_secretaria = ? AND ce_todos = '1' AND ce_data BETWEEN ? AND ?"); dbE();
	$Base -> bind_param("iss",$SCT,$Datas[0],$Datas[1]);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){ $Map['E'][$V1['MD']][$V1['ce_id']] = array_merge($V1,["info"=>$V1['ce_info']]); }
	#--- RETORNO
	return $Map;
}
function AgendaEventosMap($Turma=false,$SCT=false){ // GERA A QUANTIDADE DE EVENTOS QUE ESTAO OCORRENDO NA ESCOLA
	global $ANOBASE,$db,$MYSCT; $Turma = (is_numeric($Turma))?$Turma:false; $SCT = ($SCT)?$SCT:$MYSCT;
	// BASE
	$Base = $db -> prepare("
	SELECT 'T' as R, turma_id as Tid, COUNT(ct_id) as Qt FROM turmas
	INNER JOIN agenda_turma ON (agenda_turma.ct_turma = turmas.turma_id)
	WHERE turmas.turma_secretaria = ? AND ct_data = DATE(NOW()) ".((is_numeric($Turma))?"AND turmas.turma_id = ?":null)."
	GROUP BY R, Tid
	UNION ALL
	SELECT 'E' as R, ce_secretaria as Tid, COUNT(ce_id) as Qt FROM agenda_escolar
	WHERE ce_secretaria = ? AND ce_data = DATE(NOW()) GROUP BY R, Tid"); dbE();
	if(!is_numeric($Turma)){$Base -> bind_param("ii",$SCT,$SCT);}else{$Base -> bind_param("iii",$SCT,$Turma,$SCT);}
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	$Map=['E'=>0]; foreach($Base as $K1=>$V1){$Map[(($V1['R']=='T')?$V1['Tid']:'E')] = $V1['Qt'];}
	return $Map;
}
function TurmasMap($SCT=null,$Turno=false,$ANO=false){ // MAPEAR TURMAS
	global $db,$ANOBASE,$MYSCT; if(!$SCT){$SCT=$MYSCT;}
	$ANO = (is_numeric($ANO)) ? $ANO : $ANOBASE;
	$Turmas = $db -> prepare("
	SELECT
		turmas.*,
		(SELECT COUNT(vt_id) FROM vinc_turma WHERE vt_sit IN (0,3) AND vt_turma = turma_id) as vinculos,
		(SELECT COUNT(aulas_id) FROM turmas_aulas WHERE aulas_turma = turma_id) as aulas
	FROM turmas
		WHERE turma_secretaria = ? AND YEAR(turma_dref) = ?
		ORDER BY turma_turno, turma_mod, turma_serie, turma_num ASC");
	$Turmas -> bind_param("is",$SCT,$ANO);
	$Turmas -> execute();
	$Turmas = $Turmas -> get_result() -> fetch_all(MYSQLI_ASSOC);
	$Retorno = []; foreach($Turmas as $K1=>$V1){ $Retorno[$V1['turma_id']] = $V1; }
	
	if(is_numeric($Turno)){foreach($Retorno as $K1=>$V1){if($V1['turma_turno']!=$Turno){unset($Retorno[$K1]);}}}
	if($Turno===true){$Retorno=[]; foreach($Turmas as $K1=>$V1){$Retorno[$V1['turma_turno']][$K1]=$V1;}}
	
	#ppre($Retorno);
	return $Retorno;
}
function TurmaEMap($Turma,$SCT=false,$LockDate=null){  // MAPEAR ESTUDANTES DA TURMA
	global $ANOBASE,$db,$MYSCT;
	$Alunos = []; $SCT = ($SCT)?$SCT:$MYSCT; $HOJE = Data($LockDate,2)." 23:59:59";
	$Base = $db -> prepare("SELECT vinc_turma.*, ui_nome, ui_pic, ui_matricula FROM vinc_turma
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	INNER JOIN user ON (user.user_id = vinc_turma.vt_user)
	INNER JOIN userinfo ON (user.user_login = userinfo.ui_login)
	WHERE turma_id = ? AND turma_secretaria = ? AND YEAR(turma_dref)= ? ORDER BY vt_num ASC"); dbE();
	$Base -> bind_param("iis",$Turma,$SCT,$ANOBASE);
	$Base -> execute();
	$Base = $Base -> get_result(); 
	$Base = $Base -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){$Alunos[$V1['vt_id']] = $V1; unset($Base[$K1]);}
	// CRIA O LOCK
	foreach($Alunos as $K1=>$V1){
		$Lock = false;
		if($V1['vt_sit'] != 0){
			if($V1['vt_remanejado'] != null){
				if($HOJE >= $V1['vt_remanejado']){ $Lock = true; };
			}
		}
		// NESTA VERSÃO DA LUMOS TODOS AS DATAS REMETEM A COLUNA REMANEJADOS, MESMO OS ENCERRADOS, OU TANSFERIDOS
		// if($V1['vt_sit'] != 0){
		// 	if($V1['vt_sit'] == 1 AND ($V1['vt_remanejado']) != null){
		// 		if($HOJE >= $V1['vt_remanejado']){ $Lock = true; }
			
		// 	}elseif($V1['vt_sit'] == 2 AND ($V1['vt_transferido']) != null){
		// 		if($HOJE >= $V1['vt_transferido']){ $Lock = true; }
			
		// 	}elseif(($V1['vt_transferido']) == null OR ($V1['vt_remanejado']) == null){ $Lock = true; }
		// }
		$Alunos[$K1]['vt_lock'] = $Lock;
	}
	return $Alunos;
}
function TurmaVPSMap($Turma,$SCT=false){ // MAPEIA TODOS OS VPS DA TURMA
	global $ANOBASE,$db,$MYSCT;
	$SCT = ($SCT)?$SCT:$MYSCT;
	$VPF =[]; $Base = $db -> prepare("
	SELECT vinc_prof.*,vinc_prof_user.*,turmas.*,disciplinas.*,ui_nome FROM vinc_prof
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN user ON (vinc_prof_user.vpu_user = user.user_id)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc)
	LEFT JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
	WHERE turmas.turma_id = ? AND  user.user_secretaria = ? AND YEAR(vp_dref)= ? ".isPraticas()." ORDER BY disc_area, disc_valor ASC"); dbE();
	$Base -> bind_param("iis",$Turma,$SCT,$ANOBASE);
	$Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		if(!array_key_exists($V1['vp_id'],$VPF)){$VPF[$V1['vp_id']] = $V1;}
		$VPF[$V1['vp_id']]['users'][$V1['vpu_id']] = ['id'=>$V1['vpu_user'],'nome'=>$V1['ui_nome']];
		$VPF[$V1['vp_id']]['users_id'][] = $V1['vpu_user'];
	}
	return $VPF;
}
function TurmaAulaMap($Turma,$AwayActive=1){
	global $db,$ANOBASE;
	if(!is_numeric($Turma)){return false;}

	$Base = $db -> prepare("SELECT * FROM turmas_aulas WHERE aulas_turma = ? AND YEAR(aulas_dref) = ? AND aulas_active IN (1,?)");
	$Base -> bind_param("iii",$Turma,$ANOBASE,$AwayActive);
	$Base -> execute();
	$Map = [];
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
		$Map[$V['aulas_dia'].'-'.$V['aulas_hora']] = ['id'=>$V['aulas_id'],'disc'=>$V['aulas_disc']];
	}

	return $Map;
}
function ServidorMap($Tipo='30,32,34',$SCT=false,$Order=false){
	global $db, $ANOBASE, $MYSCT; $SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Base = $db -> prepare("SELECT DISTINCT(user_login) as Uid, user.*, userinfo.* FROM user
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE user.user_secretaria = ? AND user_tipo IN ($Tipo) AND user.user_ativo = 1 ORDER BY ui_nome ASC");
	$Base -> bind_param("i",$SCT);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	$Base = ReKey($Base,'user_id');
	// CORRIGE TODOS OS NOMES PARA MAÚSCULO
	foreach($Base as $K1=>$V1){ $Base[$K1]['ui_nome'] = mb_strtoupper($V1['ui_nome'],'UTF-8'); }
	if($Order){ $B = []; foreach($Base as $K1=>$V1){$B[$V1['user_tipo']][$K1] = $V1;} $Base = $B; }
	return $Base;
}
function EspacosMap($SCT=false){
	global $db, $ANOBASE, $MYSCT;
	$SCT = (is_numeric($SCT)) ? $SCT : $MYSCT;
	$Base = $db -> prepare("SELECT
		sala_id, sala_secretaria, sala_nome, sala_num, sala_dref,
		(CASE WHEN NOW() >= ags_data_ini AND NOW() <= ags_data_fim THEN 1 ELSE 0 END) as agenda_now,
		(SUM(CASE WHEN ags_data_ini >= NOW() AND DATE(NOW()) = DATE(ags_data_ini) THEN 1 ELSE 0 END)) as agenda_qt
	FROM salas
	LEFT JOIN agenda_sala ON (agenda_sala.ags_sala = salas.sala_id)
	WHERE sala_secretaria = ?
	GROUP BY sala_id, sala_secretaria, sala_nome, sala_num, sala_dref, agenda_now
	ORDER BY sala_num, sala_nome"); dbE();
	$Base -> bind_param("i",$SCT);
	$Base -> execute();
	$Base = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'sala_id');
	foreach($Base as $K1=>$V1){
		$Base[$K1]['sala_num'] = ($V1['sala_num'] < 10) ? "0".$V1['sala_num'] : $V1['sala_num'];
	}
	return $Base;
}
function EspacosAgendaMap($Esp,$SCT=false){
	global $db, $ANOBASE, $MYSCT;
	$SCT = (is_numeric($SCT)) ? $SCT : $MYSCT;
	$Map = [0=>[],1=>[],2=>[],3=>[]];
	$Base = $db -> prepare("SELECT
		agenda_sala.*,
		user_id, ui_nome,DATE(ags_data_ini) as ags_data_now,
		(CASE
			WHEN DATE(ags_data_ini) < DATE(NOW()) THEN 0
			WHEN DATE(ags_data_ini) = DATE(NOW()) THEN 1
			WHEN DATE(ags_data_ini) > DATE(NOW()) THEN 2
			ELSE 3
		END) as ags_map
	FROM agenda_sala
	INNER JOIN user ON (user.user_id = agenda_sala.ags_user)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE ags_sala = ? AND user.user_ativo = 1 ORDER BY ags_data_ini, ags_data_fim"); dbE();
	$Base -> bind_param("i",$Esp);
	$Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map[$V1['ags_map']][$V1['ags_data_now']][$V1['ags_id']] = $V1;
	}
	
	krsort($Map[0]);
	return (is_array($Map) AND count($Map)>=1) ? $Map : [null];
}
function PVMap(){
	global $db, $ANOBASE, $MYSCT;
	$Base = $db -> prepare("SELECT turmas.*, user_ficha.fc_pv, fc_paiprof, fc_maeprof, userinfo.ui_nome, userinfo.ui_pic FROM vinc_turma
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma) 
	INNER JOIN user_ficha ON (user_ficha.fc_user = vinc_turma.vt_user) 
	INNER JOIN user ON (user.user_id = user_ficha.fc_user) 
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
	WHERE YEAR(vinc_turma.vt_dref) = ? AND LENGTH(user_ficha.fc_pv) > 0 AND turmas.turma_secretaria = ?");
	$Base -> bind_param("ii",$ANOBASE,$MYSCT); $Base -> execute();
	return $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
}
function RegimentoMap($id=false,$order=false){
	global $db;
	$Map = $db -> prepare("SELECT * FROM sedu_regimento ".((is_numeric($id))?"WHERE sreg_id = '$id'":null)." ORDER BY sreg_tipo, sreg_artigo, sreg_inciso ASC");
	$Map -> execute(); $Map = $Map -> get_result() -> fetch_all(MYSQLI_ASSOC);
	$Map = ReKey($Map,'sreg_id');
	
	// ORDENA MELHOR O REGIMENTO
	if($order){
		$KeyMap = []; foreach($Map as $K1=>$V1){$KeyMap[$V1['sreg_tipo']][$V1['sreg_id']] = $V1;}
		return $KeyMap;
	}
	return $Map;
}
function TutorMap($Tutor=false,$SCT=false,$Turno=false){ // MAPEIA TODOS OS TUTORES
	global $ANOBASE,$db,$MYSCT; $Map = [];
	$SCT = ($SCT)?$SCT:$MYSCT;
	$Tutor = (is_numeric($Tutor))?$Tutor:false;
	$Turno = (is_numeric($Turno))?$Turno:false;
	// ASSOCIA TODOS OS ESTUDANTES VINCULADOS
	// COM SEUS RESPECTIVOS TUTORES (VALDIO SO PARA VINCULO COM BASE EM ANOBASE)
	$Base = $db -> prepare("
	SELECT
		tutor_user.user_id as Tid,
		tutor_user.user_ativo as Tativo,
		tutor_user.user_tipo as Ttipo,
		tutor_userinfo.ui_nome as Tnome,
		tutor_userinfo.ui_pic as TPic,
		
		tut_id, tut_atdkey, fc_aledition,
		estudante_user.user_id as Eid,
		estudante_userinfo.ui_nome as Enome,
		estudante_userinfo.ui_pic as Epic,
		estudante_userinfo.ui_nascimento as ENascimento,
		estudante_login.login_user as ELogin,
		turmas.*,
		vt_sit,
		(SELECT COUNT(DISTINCT oc_id) FROM ocorrencias WHERE oc_tipo = '0' AND oc_sit = '1' AND oc_estudante = estudante_user.user_id AND YEAR(oc_data) = YEAR(vt_dref)) as reg1,
		(SELECT COUNT(DISTINCT oc_id) FROM ocorrencias WHERE oc_tipo = '1' AND oc_sit = '1' AND oc_estudante = estudante_user.user_id AND YEAR(oc_data) = YEAR(vt_dref)) as reg2,
		(SELECT COUNT(DISTINCT tuta_id) FROM tutoria_atendimentos WHERE tuta_tut = tutoria.tut_id) as reg3,
		(SELECT COUNT(DISTINCT oc_id) FROM ocorrencias WHERE oc_tipo = '0' AND oc_sit = '0' AND oc_estudante = estudante_user.user_id AND YEAR(oc_data) = YEAR(vt_dref)) as reg4,
		(SELECT COUNT(DISTINCT oc_id) FROM ocorrencias WHERE oc_tipo = '1' AND oc_sit = '0' AND oc_estudante = estudante_user.user_id AND YEAR(oc_data) = YEAR(vt_dref)) as reg5,
		(SELECT COUNT(DISTINCT tm_id) FROM tutoria_meta WHERE tm_sit = 0 AND DATE(tm_prazo) < DATE(NOW()) AND tm_user = tutoria.tut_estudante AND YEAR(tm_dref) = YEAR(NOW())) as metas,
		(SELECT COUNT(DISTINCT tm_id) FROM tutoria_meta WHERE tm_user = tutoria.tut_estudante AND YEAR(tm_dref) = YEAR(NOW())) as metas_total
	
	FROM user as tutor_user
	INNER JOIN userinfo as tutor_userinfo ON (tutor_userinfo.ui_login = tutor_user.user_login)
	INNER JOIN tutoria ON (tutoria.tut_tutor = tutor_user.user_id)	
	INNER JOIN user as estudante_user ON (estudante_user.user_id = tutoria.tut_estudante)
	INNER JOIN userinfo as estudante_userinfo ON (estudante_userinfo.ui_login = estudante_user.user_login)
	INNER JOIN login as estudante_login ON (estudante_login.login_id = estudante_user.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = tutoria.tut_estudante)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma AND turmas.turma_disabled = 0)
	LEFT JOIN user_ficha ON (user_ficha.fc_user = estudante_user.user_id)
	WHERE vinc_turma.vt_sit IN (0,2) AND tutor_user.user_secretaria = ? AND YEAR(vinc_turma.vt_dref) = ? AND tutor_user.user_tipo IN (30,31,32,34,37)
	".((is_numeric($Tutor))?"AND tut_tutor = '$Tutor' ":null)."
	".((is_numeric($Turno))?"AND turma_turno = '$Turno' ":null)."
	ORDER BY turma_mod, turma_serie, turma_num, Tnome ASC, vt_sit DESC"); dbE();
	
	$Base -> bind_param("ii",$SCT,$ANOBASE);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	
	// REG MAP
	// 1 - OCORRENCIAS DISCIPLINASRES FECHADAS
	// 2 - OCORRENCIAS OBSERVAÇÕES FECHADAS
	// 4 - OCORRENCIAS DISCIPLINARES ABERTAS
	// 5 - OCORRENCIAS DISCPLINARES ABERTAS

	foreach($Base as $K1=>$V1){# ppre($V1);
		// CRIA O VETOR DO TUTOR
		if(!array_key_exists($V1['Tid'],$Map)){
			$Map[$V1['Tid']] = [
				'ativo'=>$V1['Tativo'],
				'nome'=>$V1['Tnome'],
				'tipo'=>$V1['Ttipo'],
				'pic'=>$V1['TPic'],
				'qt' => 0,
				'regmap' => ['oc'=>0,'ob'=>0,'rg'=>0,'mt'=>0],
				'alerta' => false,
				'map' => []
			];
		}
		// AGREGA INFORMAÇOES AO MAPA DE TUTORIA
		// if($V1['vt_sit']==0){ $Map[$V1['Tid']]['qt']++; }
		$Map[$V1['Tid']]['map'][$V1['tut_id']] = [
			'id' => $V1['Eid'],
			'pic' => $V1['Epic'],
			'nome'=>$V1['Enome'],
			'nascimento' => $V1['ENascimento'],
			'elogin' => $V1['ELogin'],
			'vt_sit'=>$V1['vt_sit'],
			'turma'=>NTurma($V1,4),
			'turma_id'=>$V1['turma_id'],
			'turma_turno'=>$V1['turma_turno'],
			'atdkey'=>$V1['tut_atdkey'],
			'fc_aledition'=>(($V1['fc_aledition']==1)?1:0),
			'reg'=>[null,$V1['reg1'],$V1['reg2'],$V1['reg3'],$V1['reg4'],$V1['reg5'], 'mt' => $V1['metas']],
			'regsum' => ['oc'=>$V1['reg1']+$V1['reg4'], 'ob'=>$V1['reg2']+$V1['reg5'], 'rg'=>$V1['reg3'], 'mt' => $V1['metas_total']],
			'btn'=>[
				'atd'=>0,
				'frq'=>0,
				'ocr'=>(($V1['reg4']+$V1['reg5'])>0)?true:false,
				'eo' =>0,
				'ead'=>0
			],
			'metas' => $V1['metas']
		];
		// CASO ESTEJA FALTANDO DEVOLUTIVA, ATIVA O ALERTA
		if($V1['reg4'] OR $V1['reg5']){ $Map[$V1['Tid']]['alerta'] = true; }
	}

	// PERCORRE O VETOR NOVAMENTE PARA REGISTRAR O QT | ESSA FUNÇÃO ESTAVA AGREGADA ANTERIORMENTE, PORÉM, ESTAVA CONTABILIZANDO ERRADO
	foreach($Map as $K1=>$V1){
		foreach($V1['map'] as $K2=>$V2){
			if($V2['vt_sit'] == 0){
				$Map[$K1]['qt']++;
			}
		}
	}

	// SE O TUTOR NÃO FOR INFORMADO ... 
	if(!$Tutor){ 
	// REORDENA O VETOR PELO ORDEM ALFABÉTICA
	function TutorMapCompara($a,$b){return $a['nome'] > $b['nome'];}
	uasort($Map,"TutorMapCompara");

	// ASSOCIA TODOS OS POSSIVEIS TUTORES DA ESCOLA QUE NAO ESTAO VINCULADOS A TUTORIA AINDA
	$Base = $db -> prepare("SELECT user_id, 1 as R, user_ativo, ui_nome, user_tipo, 0 as qt FROM user
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE user.user_secretaria = ? AND user.user_ativo = '1' AND user.user_tipo >= 30 AND user.user_tipo != 33
	AND user_id NOT IN (
		SELECT DISTINCT(user_id) as user_id FROM user
		INNER JOIN tutoria ON (tutoria.tut_tutor = user.user_id)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = tutoria.tut_estudante)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		WHERE turma_secretaria = ? AND user_secretaria = ? AND YEAR(vt_dref) = ?
	)
	ORDER BY R, ui_nome ASC"); dbE();
	$Base -> bind_param("iiii",$SCT,$SCT,$SCT,$ANOBASE);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(!array_key_exists($V1['user_id'],$Map)){
			$Map[$V1['user_id']] = [
				'ativo'=>$V1['user_ativo'],
				'nome'=>$V1['ui_nome'],
				'tipo'=>$V1['user_tipo'],
				'map' => []
			];
		}
	}}
	
	// SOMA OS REGISTROS
	foreach($Map as $K1=>$V1){
		foreach($V1['map'] as $K2=>$V2){
			// SOMA OS REGISTROS
			$Map[$K1]['regmap']['oc'] += $V2['regsum']['oc'];
			$Map[$K1]['regmap']['ob'] += $V2['regsum']['ob'];
			$Map[$K1]['regmap']['rg'] += $V2['regsum']['rg'];
			$Map[$K1]['regmap']['mt'] += $V2['regsum']['mt'];
		}
	}
	
	return ($Tutor!= false AND array_key_exists($Tutor,$Map))?$Map[$Tutor]['map']:$Map;
}
function TutorMapLite($Tutor=false,$SCT=false,$Turno=false){ // MAPEIA DE FORMA MAIS SIMPLES OS TUTORES
	global $ANOBASE,$db,$MYSCT; $Map = []; $SCT = ($SCT)?$SCT:$MYSCT;
	$Tutor = (is_numeric($Tutor))?$Tutor:false;
	$Turno = (is_numeric($Turno))?$Turno:false;
	// ASSOCIA TODOS OS POSSIVEIS TUTORES
	if(!$Tutor){ // ASSOCIA TODOS OS POSSIVEIS TUTORES DA ESCOLA QUE NAO ESTAO VINCULADOS A TUTORIA AINDA
	$Base = $db -> prepare("SELECT user_id, 1 as R, user_ativo, ui_nome, user_tipo, 0 as qt FROM user
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE user.user_secretaria = ? AND user.user_ativo = '1' AND user.user_tipo IN (30,32,34,37) AND user.user_server = 1
	ORDER BY R, ui_nome ASC"); dbE();
	$Base -> bind_param("i",$SCT);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(!array_key_exists($V1['user_id'],$Map)){
			$Map[$V1['user_id']] = [
				'ativo'=>$V1['user_ativo'],
				'nome'=>$V1['ui_nome'],
				'tipo'=>$V1['user_tipo'],
				'map' => []
			];
		}
	}}
	// ASSOCIA TODOS OS ESTUDANTES VINCULADOS
	// COM SEUS RESPECTIVOS TUTORES (VALDIO SO PARA VINCULO COM BASE EM ANOBASE)
	$Base = $db -> prepare("
	SELECT
		tutor_user.user_id as Tid,
		tutor_user.user_ativo as Tativo,
		tutor_user.user_tipo as Ttipo,
		tutor_userinfo.ui_nome as Tnome,
		tut_id, tut_atdkey,
		estudante_user.user_id as Eid,
		estudante_userinfo.ui_nome as Enome,
		estudante_userinfo.ui_pic as Epic,
		estudante_userinfo.ui_nascimento as ENascimento,
		turmas.*,
		tutoria.*,
		vt_sit
	FROM user as tutor_user
	INNER JOIN userinfo as tutor_userinfo ON (tutor_userinfo.ui_login = tutor_user.user_login)
	INNER JOIN tutoria ON (tutoria.tut_tutor = tutor_user.user_id)
	INNER JOIN user as estudante_user ON (estudante_user.user_id = tutoria.tut_estudante)
	INNER JOIN userinfo as estudante_userinfo ON (estudante_userinfo.ui_login = estudante_user.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = tutoria.tut_estudante)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma AND turmas.turma_disabled = 0)
	WHERE vinc_turma.vt_sit IN (0,2) AND tutor_user.user_secretaria = ? AND YEAR(vinc_turma.vt_dref) = ? AND tutor_user.user_tipo IN (30,32,34,37) AND tutor_user.user_server = 1
	".((is_numeric($Tutor))?"AND tutor_user.user_id = '$Tutor' ":null)."
	".((is_numeric($Turno))?"AND turmas.turma_turno = '$Turno'":null)."
	ORDER BY Tnome ASC, vt_sit DESC"); dbE();
	
	$Base -> bind_param("ii",$SCT,$ANOBASE);
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	
	foreach($Base as $K1=>$V1){
		if(!array_key_exists($V1['Tid'],$Map)){
			$Map[$V1['Tid']] = [
				'ativo'=>$V1['Tativo'],
				'nome'=>$V1['Tnome'],
				'tipo'=>$V1['Ttipo'],
				'map' => []
			];
		}
			
		if(array_key_exists($V1['Tid'],$Map)){
			$Map[$V1['Tid']]['map'][$V1['tut_id']] = [
				'id'=>$V1['Eid'],
				'pic' => $V1['Epic'],
				'nome'=>$V1['Enome'],
				'nascimento' => $V1['ENascimento'],
				'vt_sit'=>$V1['vt_sit'],
				'turma'=>NTurma($V1,4),
				'turma_id'=>$V1['turma_id'],
				'turma_turno'=>$V1['turma_turno'],
				'atdkey'=>$V1['tut_atdkey'],
			];
		}
	}

	return (is_numeric($Tutor) AND array_key_exists($Tutor,$Map))?$Map[$Tutor]['map']:$Map;
}
function TutoriaDesgarradosMap($SCT=false,$Map=true,$Turno=false){  // MAPEIA OS ESTUDANTES SEM TUTOR
	// SE MAP ESTÁ ATIVO RETORNA LISTA, SENÃO, RETORNA COUNT
	global $ANOBASE,$db,$MYSCT; $SCT = ($SCT)?$SCT:$MYSCT; $Turno = (is_numeric($Turno)) ? $Turno : false;
	
	if(is_bool($Map)){
		$Base = $db -> prepare
		("SELECT ".(($Map)?"DISTINCT(user_id) as user_id, ui_nome, turmas.*":"COUNT(user.user_id) as qt")." FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		WHERE user.user_secretaria = ? AND YEAR(vinc_turma.vt_dref) = ?
		AND user.user_tipo = '33' AND vinc_turma.vt_sit = '0' ".((is_numeric($Turno))?"AND turmas.turma_turno = '$Turno' ":NULL)."
		AND user.user_id NOT IN (
			SELECT user_main.user_id FROM user as user_main
			INNER JOIN tutoria as tutoria_main ON (tutoria_main.tut_estudante = user_main.user_id)
			INNER JOIN vinc_turma as vinc_turma_main ON (vinc_turma_main.vt_user = user_main.user_id) 
			WHERE user_main.user_secretaria = ? AND YEAR(vinc_turma_main.vt_dref) = ? AND user_main.user_tipo = '33'
		)
		ORDER BY turma_mod, turma_serie, turma_num, ui_nome ASC"); dbE();
		$Base -> bind_param("iiii",$SCT,$ANOBASE,$SCT,$ANOBASE);
		$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		return ($Map)?$Base:$Base[0]['qt'];
		
	} return false;
	
}
function TutorDevolutivasMap($OcTut=false,$OcID=false,$SCT=false,$Turno=false){ // MAPEIA TODAS AS OCORRENCIAS CRIADAS POR VOCE OU PELO ID DA OCORRENCIA
	global $db,$ANOBASE,$MYSCT,$MEUID; $SCT = ($SCT)?$SCT:$MYSCT;
	$OcID = (is_numeric($OcID))?$OcID:false; $OcTut = (is_numeric($OcTut))?$OcTut:false;
	$Base = $db -> prepare("
	SELECT ocorrencias.*,
		sedu_regimento.*,
		turmas.*,
		PorUi.ui_nome as PorNome,
		DevUi.ui_nome as DevNome,
		EstUi.ui_nome as EstNome
	FROM ocorrencias
	INNER JOIN user as PorUs ON (PorUs.user_id = ocorrencias.oc_por)
	INNER JOIN userinfo as PorUi ON (PorUi.ui_login = PorUs.user_login)
	LEFT JOIN user as DevUs ON (DevUs.user_id = ocorrencias.oc_devolutiva_por)
	LEFT JOIN userinfo as DevUi ON (DevUi.ui_login = DevUs.user_login)
	INNER JOIN user as EstUs ON (EstUs.user_id = ocorrencias.oc_estudante)
	INNER JOIN userinfo as EstUi ON (EstUi.ui_login = EstUs.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = EstUs.user_id)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	".((is_numeric($OcTut))?"
	   LEFT JOIN tutoria ON (tutoria.tut_estudante = EstUs.user_id)
	":null)."
	LEFT JOIN sedu_regimento ON (sedu_regimento.sreg_id = ocorrencias.oc_regimento)
	WHERE ".((is_numeric($OcTut))?"tutoria.tut_tutor = ?":'PorUs.user_id = ?')." AND PorUs.user_secretaria = ? AND EstUs.user_secretaria = ? AND YEAR(oc_data) = ? AND vt_sit IN (0,2)
	".((is_numeric($OcID))?"AND oc_id = '$OcID'":null)."
	".((is_numeric($OcTut)?"AND tutoria.tut_id = '$OcTut'":null))."
	".((is_numeric($Turno)?"AND turmas.turma_turno = '$Turno'":null))."
	ORDER BY oc_sit, oc_dev, oc_id ASC"); dbE();
	
	$Base -> bind_param("iiii",$MEUID,$SCT,$SCT,$ANOBASE);
	$Base -> execute(); $Map=[];
		
	// MAPEAMENTO PARA A TUTORIA
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		if(!array_key_exists($V1['oc_estudante'],$Map)){ $Map[$V1['oc_estudante']] = array_merge($V1,['map'=>[],'qt' => [0,0]]); }
		
		$Map[$V1['oc_estudante']]['qt'][$V1['oc_tipo']]++;
		$Map[$V1['oc_estudante']]['map'][$V1['oc_id']] = [
			'oc_id' => $V1['oc_id'],
			'DevNome' => $V1['DevNome'],
			'PorNome' => $V1['PorNome'],
			'oc_por' => $V1['oc_por'],
			'oc_devolutiva_por' => $V1['oc_devolutiva_por'],
			'oc_tipo' => $V1['oc_tipo'],
			'oc_regimento' => $V1['oc_regimento'],
			'oc_info' => $V1['oc_info'],
			'oc_devolutiva' => $V1['oc_devolutiva'],
			'oc_sit' => $V1['oc_sit'],
			'oc_dev' => $V1['oc_dev'],
			'oc_data' => $V1['oc_data'],
			'oc_dref' => $V1['oc_dref'],
			'sreg_id' => $V1['sreg_id'],
			'sreg_ativo' => $V1['sreg_ativo'],
			'sreg_tipo' => $V1['sreg_tipo'],
			'sreg_artigo' => $V1['sreg_artigo'],
			'sreg_inciso' => $V1['sreg_inciso'],
			'sreg_info' => $V1['sreg_info']
		];
	}
	return $Map; #ReKey($Base,'oc_id');
}
function TutoriaAtendimentosMap($TutID){
	global $db, $ANOBASE;

	$Base = $db -> prepare("SELECT tutoria_atendimentos.*, ui_nome FROM tutoria_atendimentos 
	INNER JOIN user ON (user.user_id = tutoria_atendimentos.tuta_por)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE tuta_tut = ? AND YEAR(tuta_dref) = ?
	ORDER BY tuta_dref DESC");
	$Base -> bind_param('ii',$TutID,$ANOBASE);
	$Base -> execute();
	return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC),'tuta_id');
}
function TutoriaMetaMap($Est){
	global $db, $ANOBASE, $MYSCT;
	$Base = $db -> prepare("SELECT tutoria_meta.*, ui_nome FROM tutoria_meta
	INNER JOIN user ON (user.user_id = tutoria_meta.tm_por)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE tutoria_meta.tm_user = ? AND YEAR(tutoria_meta.tm_dref) = ? AND user.user_secretaria = ?
	ORDER BY tutoria_meta.tm_sit, tutoria_meta.tm_prazo ASC");

	$Base -> bind_param("iii",$Est,$ANOBASE,$MYSCT);
	$Base -> execute();
	$Map = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC),'tm_id');
	return $Map;
}
function PerfilPerguntasMap($SCT=false){
	global $ANOBASE,$db,$MYSCT; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	// MAPEIA AS PERGUNTAS 
	$Map['perg'] = [1=>[],2=>[],3=>[],4=>[],'N'=>0]; // MAPA DAS PERGUNTAS
	$Map['qtres'] = []; // QUANTIDADE DE RESPOSTAS
	
	$Base = $db -> prepare("SELECT pft_id, pft_pilar, pft_texto, COUNT(pftr_id) as qtres FROM turmas_perfil 
	LEFT JOIN turmas_perfil_reg ON (turmas_perfil_reg.pftr_pergunta = turmas_perfil.pft_id)
	WHERE pft_secretaria = ? AND pft_secretaria IS NOT NULL AND YEAR(pft_dref) = ?
	GROUP BY pft_id, pft_pilar, pft_texto");
	$Base -> bind_param("ii",$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['perg'][$V1['pft_pilar']][$V1['pft_id']] = $V1['pft_texto'];
		$Map['perg']['N']++;
		$Map['qtres'][$V1['pft_id']] = $V1['qtres'];
	} return $Map;
}
function PerfilRegistroMap($SCT=false){
	global $ANOBASE,$db,$MYSCT,$MEUTURNO; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Map = ['turma'=>[], 'map'=>[], 'user' =>[]]; 
	// MAPEIA AS TURMAS DO TURNO
	foreach(TurmasMap(false,$MEUTURNO) as $K=>$V){
		$Map['turma'][$V['turma_id']] = ['info' => NTurma($V), 'user'=>[]];
	}
	// MAPEIA OS VINCULOS DAS TURMAS
	foreach(VincProfMap(false,false,$MEUTURNO) as $K=>$V){
		foreach($V['users'] as $K1=>$V1){
			if(array_key_exists($V['vp_turma'],$Map['turma'])){
				$Map['turma'][$V['turma_id']]['user'][$V1['id']] = $V1['id'];
			}
			$Map['user'][$V1['id']] = $V1['nome'];
		}
	}
	// PROCURA NA BASE QUEM AVALIOU, TURMA - PERIODO
	$Base = $db -> prepare("SELECT  DISTINCT(pftr_turma) as turma, pftr_user as user, pftr_tri as tri FROM turmas_perfil
	INNER JOIN turmas_perfil_reg ON (turmas_perfil_reg.pftr_pergunta = turmas_perfil.pft_id)
	WHERE turmas_perfil.pft_secretaria = ? AND YEAR(turmas_perfil.pft_dref) = ?;");
	$Base -> bind_param("ii",$SCT,$ANOBASE);
	$Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
		$Map['map'][$V['turma']][$V['user']][] = $V['tri'];
	}

	return $Map;
}
function PerfilTurmaMap($Turma,$SCT=false){
	global $ANOBASE,$db,$MYSCT; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;

	// CRIA O VETOR INICIAL
	$Map = [
		'res'=>[],
		'cmm'=>[],
		'graf' => [
			1 => [0=>null,1=>null,2=>null,3=>null],
			2 => [0=>null,1=>null,2=>null,3=>null],
			3 => [0=>null,1=>null,2=>null,3=>null],
			4 => [0=>null,1=>null,2=>null,3=>null]
		]
	];

	// COMPLEMENTA O MAP COM AS PERGUNTAS
	$Map = array_merge(PerfilPerguntasMap(),$Map);
	
	// EXTRAI A MÉDIA DO VALOR DAS RESPOSTAS DOS PROFESSORES COM BASE NA PERGUNTA E NO PERÍODO
	$Base = $db -> prepare("SELECT pftr_tri, ROUND(AVG(pftr_valor)) as pftr_valor, pftr_pergunta FROM turmas_perfil_reg	
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma AND turmas.turma_disabled = 0)
	WHERE turmas.turma_secretaria = ? AND YEAR(pftr_dref) = ? AND turma_id = ?
	GROUP BY pftr_tri, pftr_pergunta 
	ORDER BY pftr_tri ASC"); dbE();
	$Base -> bind_param("iii",$SCT,$ANOBASE,$Turma); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['res'][$V1['pftr_tri']][$V1['pftr_pergunta']] = $V1['pftr_valor'];
	}

	// EXTRAI OS VALORES PARA GERAÇÃO DOS GRÁFICOS POR PILAR E POR PERIODO
	$Base = $db -> prepare("SELECT pftr_tri, ROUND(AVG(pftr_valor)) as pftr_valor, pft_pilar FROM turmas_perfil_reg	
	INNER JOIN turmas_perfil ON (turmas_perfil.pft_id = turmas_perfil_reg.pftr_pergunta)
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma AND turmas.turma_disabled = 0)
	WHERE turmas.turma_secretaria = ? AND YEAR(pftr_dref) = ? AND turma_id = ?
	GROUP BY pftr_tri, pft_pilar 
	ORDER BY pftr_tri ASC"); dbE();
	$Base -> bind_param("iii",$SCT,$ANOBASE,$Turma); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['graf'][$V1['pft_pilar']][$V1['pftr_tri']] = $V1['pftr_valor'];
	}

	// EXTRAI OS PROFESSORES QUE NÃO RESPONDERAM NAQUELE PERÍODO
	$Base = $db -> prepare("SELECT pfti_id, pfti_info, pfti_tri, ui_nome, user_id FROM turmas_perfil_info
	INNER JOIN user ON (user.user_id = turmas_perfil_info.pfti_user) 
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_info.pfti_turma) 
	WHERE turmas.turma_secretaria = ? AND YEAR(pfti_dref) = ? AND turma_id = ?
	ORDER BY ui_nome");
	$Base -> bind_param("iii",$SCT,$ANOBASE,$Turma);  $Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['cmm'][$V1['pfti_tri']][$V1['pfti_id']] = $V1;
	}


	return $Map;
}
function PerfilTurmaMap_alt($Turma=false,$SCT=false){
	global $ANOBASE,$db,$MYSCT; 

	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Turma = (is_numeric($Turma))?$Turma:false;

	$Map=[
		1=>[],2=>[],3=>[],4=>[],'N'=>0,
		'map'=>[],
		'res' => [],
		'esc'=>[
			0=>[1=>null,2=>null,3=>null,4=>null],
			1=>[1=>null,2=>null,3=>null,4=>null],
			2=>[1=>null,2=>null,3=>null,4=>null],
			3=>[1=>null,2=>null,3=>null,4=>null]
		]
	];

	// MAPEIA AS PERGUNTAS 
	$Base = $db -> prepare("SELECT * FROM turmas_perfil WHERE pft_secretaria = ? AND pft_secretaria IS NOT NULL AND YEAR(pft_dref) = ?");
	$Base -> bind_param("ii",$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map[$V1['pft_pilar']][$V1['pft_id']] = $V1['pft_texto'];
		$Map['N']++;
	}
	// MÉDIA DAS RESPOSTAS COM BASE NO PERIODO
	$Base = $db -> prepare("SELECT pftr_turma, pftr_tri, ROUND(AVG(pftr_valor)) as pftr_valor FROM turmas_perfil_reg
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma AND turmas.turma_disabled = 0)
	WHERE turmas.turma_secretaria = ? AND YEAR(pftr_dref) = ? GROUP BY pftr_turma, pftr_tri ORDER BY pftr_tri ASC"); dbE();
	$Base -> bind_param("ii",$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['map'][$V1['pftr_turma']][$V1['pftr_tri']] = $V1['pftr_valor'];
	}
	// MÉDIA DAS RESPOSTAS COM BASE NO PERIODO
	$Base = $db -> prepare("SELECT pftr_turma, pftr_tri, ROUND(AVG(pftr_valor)) as pftr_valor, pftr_pergunta FROM turmas_perfil_reg	
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma AND turmas.turma_disabled = 0)
	WHERE turmas.turma_secretaria = ? AND YEAR(pftr_dref) = ? ".($Turma?" AND turma_id = '$Turma'":NULL)."
	GROUP BY pftr_turma, pftr_tri, pftr_pergunta 
	ORDER BY pftr_tri ASC"); dbE();
	$Base -> bind_param("ii",$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		// POR TURMA E TRI E PERGUNTA
		$Map['res'][$V1['pftr_turma']][$V1['pftr_tri']][$V1['pftr_pergunta']] = $V1['pftr_valor'];
	}
	$Base = $db -> prepare("SELECT pft_pilar, pftr_tri, ROUND(AVG(pftr_valor)) as pftr_valor FROM turmas_perfil_reg
	INNER JOIN turmas_perfil ON (turmas_perfil.pft_id = turmas_perfil_reg.pftr_pergunta)
	INNER JOIN turmas ON (turmas.turma_id = turmas_perfil_reg.pftr_turma AND turmas.turma_disabled = 0)
	WHERE turmas.turma_secretaria = ? AND YEAR(pftr_dref) = ? GROUP BY pft_pilar, pftr_tri"); dbE();
	$Base -> bind_param("ii",$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['esc'][$V1['pftr_tri']][$V1['pft_pilar']] = $V1['pftr_valor'];
	}

	if($Turma){
		$Map['map'] = array_key_exists($Turma,$Map['map']) ? $Map['map'][$Turma] : [];
		$Map['res'] = array_key_exists($Turma,$Map['res']) ? $Map['res'][$Turma] : [];
	}
	
	return $Map;
}
function VincProfMap($Uid=false,$SCT=false,$Turno=false,$Split=false){ // MAPEIA OS VINCULOS DOS PROFESSORES DA ESCOLA
	global $ANOBASE,$db,$MYSCT,$ES;
	$SCT = ($SCT)?$SCT:$MYSCT;
	
	$Dados=[]; $Base = $db -> prepare("
	SELECT vinc_prof.*,vinc_prof_user.*,turmas.*,disciplinas.*, ui_nome  FROM vinc_prof
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN user ON (vinc_prof_user.vpu_user = user.user_id)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc)
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma AND turmas.turma_disabled = 0 AND YEAR(turmas.turma_dref) = YEAR(vinc_prof.vp_dref))
	WHERE turma_secretaria = ? AND YEAR(vp_dref) = ? ".((is_numeric($Uid))?"AND vpu_user = ?":null)." ORDER BY  turma_mod, turma_serie, turma_num, disc_area, disc_valor ASC"); dbE();
	if(is_numeric($Uid)){$Base -> bind_param("isi",$SCT,$ANOBASE,$Uid);}else{$Base -> bind_param("is",$SCT,$ANOBASE);}
	$Base -> execute();
	$Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(!isset($Dados[$V1['vp_id']])){$Dados[$V1['vp_id']] = array_merge($V1,['trash'=>true]);}
		$Dados[$V1['vp_id']]['users'][$V1['vpu_id']] = ['id'=>$V1['vpu_user'],'nome'=>$V1['ui_nome']];
		$Dados[$V1['vp_id']]['users_id'][] = $V1['vpu_user'];
	}
	if(count($Dados) > 0){
		$Keys = implode(',',array_keys($Dados));
		
		// VERIFICA A HORA DAS AULAS
		$Base = $db -> prepare("SELECT vp_id, turma_turno, turmas_aulas.*, salas.* FROM vinc_prof
		INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
		LEFT JOIN turmas_aulas ON (turmas_aulas.aulas_turma = vinc_prof.vp_turma AND turmas_aulas.aulas_disc = vinc_prof.vp_disc)
		LEFT JOIN salas ON (salas.sala_id = turmas_aulas.aulas_sala)
		WHERE sala_secretaria = ? AND YEAR(vp_dref) = ? AND vp_id IN (".$Keys.")
		ORDER BY aulas_dia, aulas_hora");
		$Base -> bind_param("ii",$SCT,$ANOBASE);
		$Base -> execute(); 
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			
			// INSERE AS INFORMAÇÕES NO VETOR
			if(is_numeric($V1['aulas_turma'])){
				$Dados[$V1['vp_id']]['aulas'][$V1['aulas_dia']][$V1['aulas_hora']] = [
					'hora' => @$ES['aulaInicio-'.$V1['turma_turno'].'-'.$V1['aulas_hora']], # CORRIGIR
					'sala_id' => $V1['sala_id'],
					'sala_nome' => $V1['sala_nome'],
					'sala_num' => $V1['sala_num']
				];
				
				@$Dados[$V1['vp_id']]['JsonAulas'][$V1['aulas_dia']][] = $ES['aulaInicio'][$V1['turma_turno']][$V1['aulas_hora']];
			}
		}
		foreach($Dados as $K1=>$V1){
			if(!array_key_exists('aulas',$V1)){
				// CRIA OS ELEMENTOS QUANDO ELES NÃO EXISTEM
				$Dados[$K1]['aulas'] = []; $Dados[$K1]['JsonAulas'] = NULL; 
			}else{
				// COMPACTA EM JSON
				$Dados[$K1]['JsonAulas'] = json_encode($Dados[$K1]['JsonAulas']);
			}
		}
		
		// VERIFICA AS AVALIAÇÕES
		$Base = $db -> prepare("SELECT
			vp_id,
			COUNT(avi_id) as avi,
			COUNT(gnim_id) as niv
		FROM vinc_prof
		INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
		LEFT JOIN avaliacoes ON (avaliacoes.avi_vp = vinc_prof.vp_id)
		LEFT JOIN guias_niv_monitor ON (guias_niv_monitor.gnim_vp = vinc_prof.vp_id)
		WHERE turma_secretaria = ? AND YEAR(vp_dref) = ? AND vp_id IN (".$Keys.")
		GROUP BY vp_id");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			if(array_key_exists($V1['vp_id'],$Dados)){
				if($V1['avi'] > 0 OR $V1['niv'] > 0){
					$Dados[$V1['vp_id']]['trash'] = false;
				}
				if(count($Dados[$V1['vp_id']]['users_id']) > 1){
					$Dados[$V1['vp_id']]['trash'] = true;
				}
			}
		}
	}
	if(is_numeric($Turno)){
		$TempDados = $Dados; $Dados = [];
		foreach($TempDados as $K1=>$V1){if($Turno==$V1['turma_turno']){ $Dados[$K1]=$V1; }}
	}
	if($Split){
		$VincProfMap = [];
		foreach($Dados as $K1=>$V1){ $VincProfMap[$V1['disc_area']][$V1['vp_id']] = $V1; }
		$Dados = $VincProfMap;
	}

	
	
	return $Dados;
}
function EOListMap($VP,$SCT=false,$BNCC=true){
	global $db, $ANOBASE, $MYSCT; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT; 
	$BNCC = (is_bool($BNCC))?$BNCC:false;
	// 0 - Aguardando | 1 - Faltou | 2 - Não Cumpriu | 3 - Cumpriu
	$Map=['meses'=>[],'map'=>[],'aberto'=>0];

	$Base = $db -> prepare("
	SELECT 
		DISTINCT DATE(eo_atividades.eoa_data) as eoa_data, eoa_files,
		COUNT(DISTINCT eol_id) as total,
		SUM(CASE WHEN eol_sit = 3 THEN 1 ELSE 0 END) as cumprido,
		SUM(CASE WHEN eol_sit = 0 THEN 1 ELSE 0 END) as aberto
	FROM eo_atividades
	INNER JOIN vinc_prof as vpeo ON (vpeo.vp_id = eo_atividades.eoa_vp)
	".(($BNCC)?
		"INNER JOIN vinc_prof as vpmain ON (vpmain.vp_id = vpeo.vp_id) ":
		"INNER JOIN vinc_prof as vpmain ON (vpmain.vp_turma = vpeo.vp_turma) "
	)."
	LEFT  JOIN eo_listagem ON (eo_listagem.eol_atv = eo_atividades.eoa_id) 
	WHERE vpmain.vp_id = ? AND YEAR(vpmain.vp_dref) = ? GROUP BY eoa_data DESC, eoa_files;");
	$Base -> bind_param("ii",$VP,$ANOBASE); 	
	$Base -> execute();
	
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){ 
		$Data = $V['eoa_data'];
		// CRIA O VETOR
		if($BNCC){

			$Map['map'][$Data] = $V; // SEPARA POR DATA NO MAPA
			$Map['map'][$Data]['eoa_files'] = count(array_filter(explode(',',$V['eoa_files'])));
			$Map['meses'][intval(Data($Data,7))][] = $Data;
			$Map['aberto'] += $V['aberto'];

		}else{
			// QUANDO FOR O PRIMEIRO, CRIA O MAPA INICIAL DO VETOR
			if(!array_key_exists($Data,$Map['map'])){
				$Map['map'][$Data] = [
					'envios' => 0,
					'eoa_files' => 0,
					'total' => 0,
					'cumprido' => 0,
					'aberto' => 0
				];
			}
			// SOMA OS VALORES AS SUAS DATAS
			$Map['map'][$Data]['envios']++;
			$Map['map'][$Data]['eoa_files'] += intval(count(array_filter(explode(',',$V['eoa_files']))));
			$Map['map'][$Data]['total'] += $V['total'];
			$Map['map'][$Data]['cumprido'] += $V['cumprido'];
			$Map['map'][$Data]['aberto'] += $V['aberto'];
		}
	}

	// MANIPULA AS CHAVES PARA INFORMAR OS MESES
	foreach($Map['map'] as $K1=>$V1){
		$Map['meses'][intval(Data($K1,7))][] = $K1;
		$Map['aberto']+=$V1['aberto'];
	}
	// FILTRA O MAPA DE MESES CASO EXISTA DUPLICAÇÕES
	foreach($Map['meses'] as $K1=>$V1){ $Map['meses'][$K1] = array_unique($V1); }

	return (is_array($Map)) ? $Map : [];
}
function EONextMap($SCT=false){
	global $db, $ANOBASE,$TRI,$MYSCT,$ES,$MEUTURNO;
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT; $Map=[];

	$Seg = eSeg(1);
	
	// PROCURA TODOS OS EO DO TURNO
	$Base = $db -> prepare("SELECT turmas.*, vp_id FROM vinc_prof
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma) 
	WHERE turmas.turma_secretaria = ? AND YEAR(vinc_prof.vp_dref) = ? AND turmas.turma_turno = ? AND vp_disc = '2'
	ORDER BY turma_mod, turma_serie, turma_num, turma_comp"); dbE();
	$Base -> bind_param("iii",$SCT,$ANOBASE,$MEUTURNO);
	$Base -> execute();
	$Map = []; foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
		$Map[$V['turma_id']] = [
			'turma' => NTurma($V,2),
			'vp_id' => $V['vp_id'],
			'map' => [],
			'files' => 0
		];
	}

	// PROCURA AS DEMANDAS
	$Base = $db -> prepare("SELECT eo_atividades.*, disc_nome, user_id, ui_nome FROM eo_atividades
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = eo_atividades.eoa_vp) 
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc) 
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma) 
	LEFT JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	LEFT JOIN user ON (user.user_id = vinc_prof_user.vpu_user)
	LEFT JOIN userinfo ON (userinfo.ui_login = user.user_login)
	WHERE turmas.turma_secretaria = ? AND YEAR(vinc_prof.vp_dref) = ? AND turmas.turma_turno = ? AND disc_area > '0' AND eo_atividades.eoa_data = ?
	ORDER BY disc_area, disc_nome, ui_nome"); dbE();
	$Base -> bind_param("iiis",$SCT,$ANOBASE,$MEUTURNO,$Seg);
	$Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
		if(array_key_exists($V['eoa_turma'],$Map) AND !array_key_exists($V['eoa_vp'],$Map[$V['eoa_turma']])){
			$Map[$V['eoa_turma']]['map'][$V['eoa_vp']] = [
				'disc' => $V['disc_nome'],
				'info' => $V['eoa_info'],
				'files' => $V['eoa_files'],
				'prof' => []
			];
			$Map[$V['eoa_turma']]['files'] += count(explode(',',$V['eoa_files']));
		}
		$Map[$V['eoa_turma']]['map'][$V['eoa_vp']]['prof'][$V['user_id']] = $V['ui_nome'];
	}

	return $Map;
}
function EltMap($Elt=false,$Periodo=false,$Turno=false,$SCT=false){ // $Periodo = false;
	global $db, $ANOBASE,$TRI,$MYSCT,$ES,$MEUTURNO;
	// FILTRO
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT; $Map=[];
	$Elt = (is_numeric($Elt))?$Elt:false;
	$Turno = (is_numeric($Turno))?$Turno:$MEUTURNO;
	$Periodo = (is_numeric($Periodo) AND $Periodo <= 5 AND $Periodo > 0)?$Periodo: (is_bool($Periodo)?$Periodo:false);

	// PREPARA E BUSCA
	$Base = $db -> prepare("SELECT eletivas.*, ui_nome, user_id, vp_id, vp_ead, eletivas_guia.* FROM eletivas
	INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas.elt_id)
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN user ON (user.user_id = vinc_prof_user.vpu_user)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	LEFT JOIN eletivas_guia ON (eletivas_guia.eltg_elt = eletivas.elt_id)
	WHERE YEAR(eletivas.elt_dref) = ? AND eletivas.elt_secretaria = ? ".((is_numeric($Elt))?"AND eletivas.elt_id = ?":null)."
	AND elt_periodo ".((is_numeric($Periodo))?"= '$Periodo'":" <= '5'")."
	AND elt_turno IN (".((is_numeric($Turno)?$Turno:"0,1,2,3")).")
	ORDER BY elt_periodo DESC, elt_nome, ui_nome ASC"); dbE();
	if(is_numeric($Elt)){ $Base -> bind_param("iii",$ANOBASE,$SCT,$Elt);}else{ $Base -> bind_param("ii",$ANOBASE,$SCT); }
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(!array_key_exists($V1['elt_id'],$Map)){
			$Map[$V1['elt_id']] = array_merge($V1,['discs'=>explode(',',$V1['elt_disc']), 'users'=>[$V1['user_id']=>$V1['ui_nome']],'users_id' => [$V1['user_id']],'turno'=>[],'map'=>[],'ativos'=>0,'guia_itens'=>[],'guia_pct'=>[0,0],'mandala'=>[],'notas'=>(@$ES["div-$MEUTURNO-nota-$TRI"]?[0,0]:false)]);
		}else{
			$Map[$V1['elt_id']]['users'][$V1['user_id']] = $V1['ui_nome'];
			$Map[$V1['elt_id']]['users_id'][] = $V1['user_id'];
		}
	}

	$Map = (is_numeric($Elt))?((array_key_exists($Elt,$Map))?$Map[$Elt]:$Map):$Map;	
	$DIni = Data($ES[$TRI.'triini'],2);
	$DFim = Data($ES[$TRI.'trifim'],2);
	
	if(is_numeric($Elt)){
		// VINC USERS ELETIVA
		$Base = $db -> prepare("SELECT user_id, ui_nome, turmas.*, eletivas_vinc.*, vinc_turma.* FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		INNER JOIN eletivas_vinc ON (eletivas_vinc.eltv_user = user.user_id)
		INNER JOIN eletivas ON (eletivas.elt_id = eletivas_vinc.eltv_elt)
		WHERE user_tipo = '33' AND YEAR(vt_data) = ? AND vt_sit IN (0,2) AND turma_secretaria = ? AND elt_id = ?
		ORDER BY vt_sit DESC, turma_mod, turma_serie, turma_num, ui_nome ASC"); dbE();
		$Base -> bind_param("iii",$ANOBASE,$SCT,$Elt); $Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		$Map['map'] = ReKey($Base,'user_id');
		// CRIA O VT_SIT_DATE
		foreach($Map['map'] as $K1=>$V1){
			$Map['map'][$K1]['vt_sit_date'] = (($V1['vt_sit'] == 1) ? $V1['vt_remanejado'] : (($V1['vt_sit']==2) ? $V1['vt_transferido'] : null));
			$Map['turno'][$V1['turma_turno']] = $V1['turma_turno'];
		}
		// HABILIDADES E COMPETENCIAS DA ELETIVA
		$Base = $db -> prepare("SELECT elt_id, eltch_id, mandala.* FROM eletivas
		INNER JOIN eletivas_ch ON (eletivas_ch.eltch_elt = eletivas.elt_id)
		INNER JOIN mandala ON (mandala.ch_id = eletivas_ch.eltch_mandala)
		WHERE elt_secretaria = ? AND YEAR(elt_dref) = ? AND elt_id = ?"); dbE();
		$Base -> bind_param("iii",$SCT,$ANOBASE,$Elt);
		$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		$Map['mandala'] = ReKey($Base,'ch_id');
		// ITENS A SEREM CUMPRIDOS DA ELETIVA
		$Base = $db -> prepare("SELECT elt_id, eletivas_guia_itens.* FROM eletivas
		INNER JOIN eletivas_guia ON (eletivas_guia.eltg_elt = eletivas.elt_id) 
		INNER JOIN eletivas_guia_itens ON (eletivas_guia_itens.eltgi_eltg = eletivas_guia.eltg_id)
		WHERE elt_secretaria = ? AND YEAR(elt_dref) = ? AND elt_id = ?"); dbE();
		$Base -> bind_param("iii",$SCT,$ANOBASE,$Elt);
		$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		$Map['guia_itens'] = ReKey($Base,'eltgi_id');
		foreach($Map['guia_itens'] as $K2=>$V2){$Map['guia_pct'][$V2['eltgi_sit']]++;}
		@$Map['guia_pct'] = (array_sum($Map['guia_pct'])>0)?(number_format(100*$Map['guia_pct'][1]/array_sum($Map['guia_pct']),0)):0;
		// EAD DA ELETIVA
		// $Base = $db -> prepare("SELECT elt_id, COUNT(DISTINCT ead_id) as qt FROM eletivas 
		// INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas.elt_id) 
		// INNER JOIN ead ON (ead.ead_vp = vinc_prof.vp_id) 
		// WHERE (ead.ead_pubdata BETWEEN ? AND ?) AND eletivas.elt_secretaria = ? AND eletivas.elt_id = ?
		// GROUP BY elt_id");
		// $Base -> bind_param("ssii",$DIni,$DFim,$SCT,$Elt); $Base -> execute();
		// $Base = $Base -> get_result() -> fetch_assoc();
		// $Map['ead']['A'] = (is_array($Base) AND array_key_exists('qt',$Base)) ? $Base['qt'] : 0;
		
	}else{
		// VINCULOS DA ELETIVA
		$Base = $db -> prepare("SELECT elt_id, user_id, ui_nome, turmas.*, eletivas_vinc.*, vinc_turma.* FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		INNER JOIN eletivas_vinc ON (eletivas_vinc.eltv_user = user.user_id)
		INNER JOIN eletivas ON (eletivas.elt_id = eletivas_vinc.eltv_elt)
		WHERE user_tipo = '33' AND YEAR(vt_data) = ? AND vt_sit IN (0,2) AND turma_secretaria = ?
		ORDER BY turma_mod, turma_serie, turma_num, ui_nome ASC"); dbE();
		$Base -> bind_param("ii",$ANOBASE,$SCT); $Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		foreach($Base as $K1=>$V1){
			if(array_key_exists($V1['elt_id'],$Map)){
				$Map[$V1['elt_id']]['map'][$V1['user_id']] = $V1;
				$Map[$V1['elt_id']]['turno'][$V1['turma_turno']] = $V1['turma_turno'];
			}
		}
		// HABILIDADES E COMPETENCIAS
		$Base = $db -> prepare("SELECT elt_id, eltch_id, mandala.* FROM eletivas
		INNER JOIN eletivas_ch ON (eletivas_ch.eltch_elt = eletivas.elt_id)
		INNER JOIN mandala ON (mandala.ch_id = eletivas_ch.eltch_mandala)
		WHERE elt_secretaria = ? AND YEAR(elt_dref) = ?");
		$Base -> bind_param("ii",$SCT,$ANOBASE);
		$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		foreach($Base as $K1=>$V1){
			if(array_key_exists($V1['elt_id'],$Map)){
				$Map[$V1['elt_id']]['mandala'][$V1['ch_id']] = $V1;
			}
		}
		// ITENS A SEREM CUMPRIDOS
		$Base = $db -> prepare("SELECT elt_id, eletivas_guia_itens.* FROM eletivas
		INNER JOIN eletivas_guia ON (eletivas_guia.eltg_elt = eletivas.elt_id) 
		INNER JOIN eletivas_guia_itens ON (eletivas_guia_itens.eltgi_eltg = eletivas_guia.eltg_id)
		WHERE elt_secretaria = ? AND YEAR(elt_dref) = ?"); dbE();
		$Base -> bind_param("ii",$SCT,$ANOBASE);
		$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		foreach($Base as $K1=>$V1){
			if(array_key_exists($V1['elt_id'],$Map)){
				$Map[$V1['elt_id']]['guia_itens'][$V1['eltgi_id']] = $V1;
				$Map[$V1['elt_id']]['guia_pct'][$V1['eltgi_sit']]++;
			}
		}
		foreach($Map as $K1=>$V1){
			$Map[$K1]['guia_pct'] = (array_sum($Map[$K1]['guia_pct'])>0)?(number_format(100*$Map[$K1]['guia_pct'][1]/array_sum($Map[$K1]['guia_pct']),0)):0;
			// SOMA OS ATIVOS DA TURMA
			foreach($V1['map'] as $K2=>$V2){ $Map[$K1]['ativos'] += ($V2['eltv_sit']==0 AND $V2['vt_sit'] == 0) ? 1 : 0;}
		}
		// EAD DA ELETIVA
		// $Base = $db -> prepare("SELECT elt_id, COUNT(DISTINCT ead_id) as qt FROM eletivas 
		// INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas.elt_id) 
		// INNER JOIN ead ON (ead.ead_vp = vinc_prof.vp_id) 
		// WHERE (ead.ead_pubdata BETWEEN ? AND ?) AND eletivas.elt_secretaria = ?
		// GROUP BY elt_id");
		// $Base -> bind_param("ssi",$DIni,$DFim,$SCT);
		// $Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		// foreach($Base as $K1=>$V1){
		// 	if(array_key_exists($V1['elt_id'],$Map)){
		// 		$Map[$V1['elt_id']]['ead']['A'] = $V1['qt'];
		// 	}
		// }
		
	}

	if($Periodo === true){
		$MapAlt = [];
		foreach($Map as $KeyM=>$ViewM){
			$MapAlt[$ViewM['elt_periodo']][$KeyM] = $ViewM;
		}
		$Map = $MapAlt;
	}
	
	return $Map;
}
function EltSV($Periodo=false,$TypeMap=false,$SCT=false){ // SEM ELETIVA
	global $ES,$db,$ANOBASE,$TRI,$MYSCT,$MEUTURNO; $SCT = ($SCT)?$SCT:$MYSCT;
	$PeriodoTurno = $ES["elt-$MEUTURNO-periodo"];
	$Map = [];

	if($TypeMap === false){
		// PROCURA SOMENTE O QUANTITATIVO
		$Base = $db -> prepare("SELECT COUNT(user_id) as Qt FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma) 
		WHERE user_tipo = '33' AND YEAR(vt_data) = ? AND vt_sit = '0' AND turma_secretaria = ? AND turmas.turma_turno = ?
		AND user_id NOT IN (
			SELECT eltv_user FROM eletivas_vinc
			INNER JOIN eletivas ON (eletivas.elt_id = eletivas_vinc.eltv_elt)
			WHERE YEAR(elt_dref) = ? AND elt_periodo = ? AND elt_secretaria = ?
		) ORDER BY turma_mod, turma_serie, turma_num, ui_nome ASC"); dbE();

		if($Periodo === true){
			for($i=($PeriodoTurno==0?1:4); $i<=($PeriodoTurno==0?3:5); $i++){
				$Base -> bind_param("iiiiii",$ANOBASE,$SCT,$MEUTURNO,$ANOBASE,$i,$SCT); $Base -> execute(); $Map[$i] = $Base -> get_result() -> fetch_assoc()['Qt'];
			}
		}else{
			if(!is_numeric($Periodo)){ $Periodo = ($PeriodoTurno==0) ? $TRI :  (qSem() + 3); }
			$Base -> bind_param("iiiiii",$ANOBASE,$SCT,$MEUTURNO,$ANOBASE,$Periodo,$SCT); $Base -> execute(); $Map = $Base -> get_result() -> fetch_assoc()['Qt'];
		}
	}else{
		// PROCURA OS ESTUDANTES E SEUS NOMES
		// SE NAO HOUVER O PERIODO, NÃO PROCURA NADA
		if(is_numeric($Periodo)){

			$Map = [
				'turma' => [],
				'map' => [],
				'total' => 0
			];

			$Base = $db -> prepare("SELECT
				turmas.*,
				user_id,
				ui_nome
			FROM vinc_turma
			INNER JOIN user ON (user.user_id = vinc_turma.vt_user)
			INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
			INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
			WHERE vinc_turma.vt_sit = 0 AND user.user_id NOT IN (
				SELECT eltv_user FROM eletivas_vinc 
				INNER JOIN eletivas ON (eletivas.elt_id = eletivas_vinc.eltv_elt)
				WHERE eletivas.elt_secretaria = turmas.turma_secretaria AND YEAR(eletivas.elt_dref) = YEAR(turmas.turma_dref) AND eletivas.elt_periodo = ? AND eletivas.elt_turno = turmas.turma_turno
			) AND turmas.turma_secretaria = ? AND turmas.turma_turno = ? AND YEAR(turmas.turma_dref) = ?
			ORDER BY turma_mod, turma_serie, turma_num, turma_comp");
			$Base -> bind_param("iiii",$Periodo,$SCT,$MEUTURNO,$ANOBASE);
			$Base -> execute();
			foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
				if(!array_key_exists($V['turma_id'],$Map)){
					$Map['turma'][$V['turma_id']] = NTurma($V,2);
				}
				$Map['map'][$V['turma_id']][$V['user_id']] = $V['ui_nome'];
				$Map['total']++;
			}
		}
	}

	return $Map;
}
function DivMaxNotaMap($Turma,$Tri=false,$SCT=false){
	global $db, $ANOBASE, $MYSCT, $ES, $TRI;
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Tri = (is_numeric($Tri))?$Tri:$TRI;
	$Max = $ES[$Tri.'tridiversificada'];
	
	if($ES['notadivcalculo'] == 1){
		$VPs = 0; $VP1 = false;
		// FAZ A SOMA
		// PROCURA A QUANTIDADE DE DISCIPLINAS DA BASE E VERIFICA QUANTOS PONTOS MÁXIMOS PODEM SER ATRIBUIDOS
		foreach(TurmaVPSMap($Turma) as $K1=>$V1){ $VPs += ($V1['disc_area'] == 0)?1:0; if($VP1==false AND $V1['disc_area'] > 0){ $VP1 = $V1['vp_id']; }}
		// PROCURA SE A ELETIVA ESTA ATIVA PARA AO MENOS 1 PESSOA DESTA TURMA
		$Base = $db -> prepare("SELECT IF(COUNT(avn_id) > 0,1,0) as qt
		FROM vinc_turma
		INNER JOIN eletivas_vinc ON (eletivas_vinc.eltv_user = vinc_turma.vt_user) 
		INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas_vinc.eltv_elt)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma AND turmas.turma_mod < 2)
		LEFT JOIN avaliacoes ON (avaliacoes.avi_vp = vinc_prof.vp_id) 
		LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id AND avaliacoes_notas.avn_user = vinc_turma.vt_user)
		WHERE vt_turma = ? AND avi_tri = ? AND YEAR(avi_dref) = ?");
		$Base -> bind_param("iii",$Turma,$Tri,$ANOBASE);
		$Base -> execute();
		$VPs += $Base->get_result()->fetch_assoc()['qt'];
		
		return number_format($Max/$VPs,2);
	
	}else{
		// FAZ A MÉDIA
		return $Max;		
	}
	
}
function DivNotaMap($VP,$Trimestre=false,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$TRI; $SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Map=[]; $Base = $db -> prepare("SELECT * FROM avaliacoes
	LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id)
	WHERE avi_tipo = '4' AND avi_vp = ?".(($Trimestre)?" AND avi_tri = ?":null));
	if($Trimestre){$Base->bind_param("ii",$VP,$TRI);}else{$Base->bind_param("i",$VP);}
	$Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(count($Map)==0){
			$Map = [
				'vp_id' => $VP,
				'avi_id' => $V1['avi_id'],
				'avi_valor' => $V1['avi_valor'],
				'avi_tri' => $V1['avi_tri'],
				'map' => [],
			];
		}
		$Map['map'][$V1['avn_user']] = [
			0 => ($V1['avn_nota']<0)?null:$V1['avn_nota'],
			1 => ($V1['avn_rp']<0)?null:$V1['avn_rp']
		];
	}
	return $Map;
}
function DivNotaTurma($Turma,$Tri=false,$SCT=false){
	global $MYSCT,$ES,$db,$TRI,$ANOBASE,$MEUTIPO; $Elt = 0; $Map = ['vps'=>[],'map'=>[],'elt'=>[]];
	
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	// A TURMA EMI NÃO POSSUEM ELETIVA, POR ISSO O BLOQUEIO LOGO ABAIXO
	$iTurma = findTurma($Turma,$SCT);
	$mTurma = TurmaEMap($Turma);
	
	$Tri = (is_numeric($Tri)) ? $Tri : $TRI;
	// PROCURA AS NOTAS DAS TURMAS
	$Base = $db -> prepare("SELECT vp_id, avn_user, avn_nota, avn_rp FROM avaliacoes
	INNER JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id) 
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes.avi_vp)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc)
	WHERE avaliacoes.avi_tipo = 4 AND vp_turma = ? AND avi_tri = ? AND YEAR(avi_dref) = ? AND ((disc_area = 0 AND disc_id != 29) OR (".str_replace("AND",null,isPraticas('in')).")) ;");
	$Base -> bind_param("iii",$Turma,$Tri,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		// CRIA OS VPS
		if(!in_array($V1['vp_id'],$Map['vps'])){ $Map['vps'][] = $V1['vp_id']; }
		// GERA AS NOTAS
		$Map['map'][$V1['avn_user']][$V1['vp_id']] = ($V1['avn_nota'] > -1) ? $V1['avn_nota'] : null;
	}
	// ELETIVAS (29) 0 - TRI; 1 - SEM
	$Base = $db -> prepare("SELECT 'eletiva' as vp_id, vt_user as avn_user, eltv_elt, 
	CASE WHEN avn_nota > -1 THEN avn_nota ELSE NULL END as avn_nota, 
	CASE WHEN avn_rp > -1 THEN avn_rp ELSE NULL END as avn_rp
	FROM vinc_turma
	INNER JOIN eletivas_vinc ON (eletivas_vinc.eltv_user = vinc_turma.vt_user) 
	INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas_vinc.eltv_elt)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma AND turmas.turma_mod < 2)
	LEFT JOIN avaliacoes ON (avaliacoes.avi_vp = vinc_prof.vp_id) 
	LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id AND avaliacoes_notas.avn_user = vinc_turma.vt_user)
	WHERE vt_turma = ? AND avi_tri = ? AND YEAR(avi_dref) = ?");
	$Base -> bind_param("iii",$Turma,$Tri,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){ 
		
		if(is_array($iTurma) AND array_key_exists('turma_mod',$iTurma) AND $iTurma['turma_mod'] < 2){
			// PARA TURMAS DE MOD > 2, VERIFICA SE O ESTUDANTE ESTA VINCULADO AQUELA TURMA DE fa-exclamation-triangle
			// VERIFICA SE O ESTUDANTE SE ENCONTRA NA LISTAGEM (A KEY DO VETOR É O VP NÃO O USER, POR ISSO O FOREACH)
			$Key = false; foreach($mTurma as $KM => $VM){if($VM['vt_user'] == $V1['avn_user']){$Key = $KM;}}
		    if($Key AND is_numeric($Key) AND $mTurma[$Key]['vt_sit'] == 0){
    			$Map['elt'][$V1['avn_user']] = $V1;
    			if($V1['avn_nota'] OR $V1['avn_rp']){ $Elt++; }
		    }
		}
	}
	if($Elt > 0){
		$Map['vps'][] = 'eletiva';
		foreach($Map['elt'] as $K1=>$V1){
			// SE O USUARIO EXISTIR NA LISTAGEM
			if((is_numeric($V1['avn_nota']) OR is_numeric($V1['avn_rp']))){
				// SE FOR TRIMESTRAL
				if($ES['eltperiodo'] == 0){ $Map['map'][$V1['avn_user']]['eletiva'] = $V1['avn_nota'];
				// SE FOR SEMESTRAL
				}else{
					if($Tri == 1){ $Map['map'][$V1['avn_user']]['eletiva'] = $V1['avn_nota']; }
					if($Tri == 3){ $Map['map'][$V1['avn_user']]['eletiva'] = $V1['avn_rp']; }
					if($Tri == 2){
						// FAZER A MEDIA
						if($ES['eletivacalc'] == 0){
							$Map['map'][$V1['avn_user']]['eletiva'] = @number_format( ($V1['avn_nota'] + $V1['avn_rp'])/2,0); 
						}else{
							$Map['map'][$V1['avn_user']]['eletiva'] = ($ES['eletivacalc'] == 1) ? $V1['avn_nota'] : $V1['avn_rp'];
						}
					}
				}
			}
		}
	}
	// MEDIADIVERSIFICADA
	// DIVISOR
	$Div = count($Map['vps']);
	if($Div > 0){
		foreach($Map['map'] as $K1=>$V1){
			$V1 = array_filter($V1);
			// FAZ A MÉDIA
			if(count($V1) > 0){ $Map['map'][$K1] = number_format(array_sum($V1)/$Div,0);
			// CASO NÃO EXISTA NOTA, COLOCA NULO
			}else{ $Map['map'][$K1] = null; }
		}
	}
	#1($Map);
	
	return $Map['map'];
}
function DivNotaMapPainel(){
	global $db, $MEUTURNO;
	$VPs = [];
	foreach(VincProfMap(false,false,$MEUTURNO) as $K=>$V){if($V['disc_area'] == 0){$VPs[$V['vp_id']] = array_merge($V,['map'=>ReKey(TurmaEMap($V['vp_turma']),'vt_user')]);}}
	foreach(EltMap(false,false,$MEUTURNO) as $K=>$V){if(array_key_exists('vp_id',$V)){ $VPs[$V['vp_id']] = $V; }}
	foreach($VPs as $K=>$V){
		$VPs[$K]['notas'] = DivNotaMap($V['vp_id']);
		$Notas = [0,0];
		if(array_key_exists('avi_id',$VPs[$K]['notas'])){
			if(array_Key_exists('map',$VPs[$K]['notas'])){
				foreach($VPs[$K]['notas']['map'] as $KN=>$VN){
					// O ESTUDANTE EXISTE NA LISTAGEM
					if(array_key_exists($KN,$V['map'])){
						$Notas[($VN[0] >= 0 OR $VN[1] >= 0)] += ($V['map'][$KN][(array_key_exists('vp_turma',$V)?'vt_sit':'eltv_sit')] == 0 OR ($VN[0] >= 0 OR $VN[1] >= 0))?1:0;
					}
				}
			}
			$VPs[$K]['notas'] = ['faltam'=>$Notas[0],'pct'=>(array_sum($Notas)) ? number_format(100*$Notas[1]/array_sum($Notas),0) : 0];
		}else{
			$VPs[$K]['notas'] = ['faltam'=>false, 'pct'=>0];
		}
		unset($VPs[$K]['map']);
	}
	return $VPs;
}
function AVIMap($VP,$SCT=false,$Tri=false){
	global $ANOBASE,$db,$MYSCT,$TRI,$ES,$MEUTURNO; $SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	if(!is_numeric($VP)){return false;}
	$Tri = (is_numeric($Tri)) ? $Tri : $TRI;
	$Map=[
		'max'=>[1=>0,2=>0,3=>0,4=>0,9=>null],
		'maxtri' => 0,
		'secretaria' => false,
		'avi'=>[],
		'col'=>[1=>[],2=>[],3=>[],4=>[],9=>[]],
		'rpt'=>[],'tot'=>[],'emap'=>[],'rmap'=>[],'alert'=>[]
	];
	// DIVERSIFICADA
	$Turma = $db -> query("SELECT vp_turma FROM vinc_prof WHERE vp_id = '$VP' LIMIT 1") -> fetch_assoc()['vp_turma'];
	$TurmaEMap = TurmaEMap($Turma);
	$DivT = DivNotaTurma($Turma,$Tri,$SCT); 
	
	// MAPEIA OS ESTUDANTES
	foreach($TurmaEMap as $K1=>$V1){
		if(!array_key_exists($V1['vt_user'],$Map['emap']) OR $V1['vt_sit']){
			$Map['emap'][$V1['vt_user']] = $V1['vt_sit'];
		}
	}
	
	// BNCC
	$Base = $db -> prepare("SELECT * FROM avaliacoes WHERE avi_vp = ? AND avi_tri = ?"); dbE();
	$Base -> bind_param("ii",$VP,$Tri); $Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(!array_key_exists($V1['avi_id'],$Map['avi'])){
			$Map['avi'][$V1['avi_id']] = array_merge($V1,['map'=>[]]);
			$Map['max'][$V1['avi_tipo']] += $V1['avi_valor'];

			// VERIFICA SE A NOTA É DO TIPO SECRETARIA
			if($Map['secretaria'] == false AND $V1['avi_tipo']==9){ $Map['secretaria'] = true; }
		}
	} $Map['maxtri'] = array_sum($Map['max']);

	// VERIFICA A UNIÃO DE LIBERATURA E REDAÇÃO COM PORTUGUES
	if(array_key_exists('unirportugues',$ES) AND $ES['unirportugues'] == 1){
		$Base = $db -> prepare("SELECT alt.vp_id, avaliacoes.*  FROM vinc_prof as main 
		INNER JOIN vinc_prof as alt ON (alt.vp_turma = main.vp_turma)
		INNER JOIN disciplinas ON (disciplinas.disc_id = alt.vp_disc)
		INNER JOIN avaliacoes ON (avaliacoes.avi_vp = alt.vp_id)
		WHERE main.vp_id = ? AND avi_tri = ? AND alt.vp_id != main.vp_id AND alt.vp_disc IN (8,31,32) AND main.vp_disc IN (8,31,32)");
		$Base -> bind_param('ii',$VP,$Tri); $Base -> execute();  $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		foreach($Base as $K1=>$V1){
			if(!array_key_exists($V1['avi_id'],$Map['avi'])){
				$Map['avi'][$V1['avi_id']] = array_merge($V1,['map'=>[]]);
				$Map['max'][$V1['avi_tipo']] += $V1['avi_valor'];
			}
		}
	}
	if(array_key_exists('avi',$Map) AND is_array($Map['avi']) AND count($Map['avi']) > 0){
	$Base = $db -> query("SELECT avaliacoes_notas.*, avi_tipo FROM avaliacoes
	INNER JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id)
	WHERE avi_id IN (".implode(',',array_keys($Map['avi'])).") AND avi_tri = '$Tri'") -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		// ATRIBUI O USER A AVI COM SUA NOTA
		if(array_key_exists($V1['avn_avi'],$Map['avi'])){
			$Map['avi'][$V1['avn_avi']]['map'][$V1['avn_user']] = [
				0=>($V1['avn_nota']<0)?null:$V1['avn_nota'],
				1=>($V1['avn_rp']<0)?null:$V1['avn_rp']
			];
		}
		// FAZ A SOMA DA COLUNA COM BASE NA ATIVIDADE E NO USER
		if(!array_key_exists($V1['avn_user'],$Map['col'][$V1['avi_tipo']])){
			$Map['col'][$V1['avi_tipo']][$V1['avn_user']] = (($V1['avn_nota'] > $V1['avn_rp']) ? (($V1['avn_nota']>=0)?$V1['avn_nota']:0) : (($V1['avn_rp']>=0)?$V1['avn_rp']:0));
		}else{
			$Map['col'][$V1['avi_tipo']][$V1['avn_user']] += (($V1['avn_nota'] > $V1['avn_rp']) ? (($V1['avn_nota']>=0)?$V1['avn_nota']:0) : (($V1['avn_rp']>=0)?$V1['avn_rp']:0));	
		}
	}}
	// ATRIBUI A NOTA DA PARTE DIVERSIFICADA
	if(is_array($DivT) AND array_sum($DivT) > 0){
		$Map['max'][4] += intval($ES["div-$MEUTURNO-nota-$TRI"]);
		$Map['maxtri'] += intval($ES["div-$MEUTURNO-nota-$TRI"]);
		$Map['avi']['DIV'] = ['avi_id'=>false,'avi_tipo'=>4,'avi_info'=>'M. DIVERSIF.','avi_valor' => $ES["div-$MEUTURNO-nota-$TRI"], 'map' => []];
		foreach($DivT as $K1=>$V1){
			if(array_key_exists($K1,$Map['col'][4])){ $Map['col'][4][$K1] += $V1;}else{ $Map['col'][4][$K1] = $V1;}
			$Map['avi']['DIV']['map'][$K1] = [$V1,null];
		}
	}
	

	// ARREDONDA OS VALORES DAS COLUNAS
	if(!isset($ES['arrednota']) OR $ES['arrednota'] == 1){ // SE O AREDONDAMENTO ESTA ATIVO OU NÃO CONFIGURADO
		foreach($Map['col'] as $K1=>$V1){foreach($V1 as $K2=>$V2){$Map['col'][$K1][$K2] = number_format($V2,0);}}
	}
	// PROCURA NAS COLUNAS E SOMA AS MESMAS EM TOTAL
	foreach($Map['col'] as $K1=>$V1){
		foreach($V1 as $K2=>$V2){ if($K1 != 9 OR $ANOBASE < 2023){
			if(!array_key_exists($K2,$Map['tot'])){$Map['tot'][$K2] = $V2;}else{$Map['tot'][$K2] += $V2;}
		}}
	}
	// VERIFICA SE A NOTA LANÇADA PELA SECRETARIA É MAIOR OU MENOR QUE AS LANÇADAS PELO PROFESSOR
	// SE MAIOR, SUBSTITUI A NOTA FINAL
	foreach($Map['col'][9] as $K1=>$V1){
		if($ANOBASE >= 2023 AND array_key_exists($K1,$Map['tot'])){
			// SE EXISTIR, VERIFICA E ALTERA, CASO NECESSÁRIO
			if($Map['tot'][$K1] < $V1){ $Map['tot'][$K1] = $V1; $Map['alert'][$K1] = 9; }
		}else{
			// SE NÃO EXISTIR NOTA, ATRIBUI A NOTA LANÇADA PELA SECRETARIA
			$Map['tot'][$K1] = $V1;
		}
		
	}
	
	// PROCURA A RPT
	$Base = $db -> prepare("SELECT rpt_user, ROUND(rpt_nota) as rpt_nota FROM avaliacoes_rpt WHERE rpt_vp = ? AND rpt_tri = ? AND YEAR(rpt_dref) = ?");
	$Base -> bind_param("iii",$VP,$Tri,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		// REGISTRA O RPT
		$Map['rpt'][$V1['rpt_user']] = $V1['rpt_nota'];
		// COMPARA O RPT COM O TOT
		if(array_key_exists($V1['rpt_user'],$Map['tot'])){
			if($Map['tot'][$V1['rpt_user']] < $V1['rpt_nota']){ $Map['tot'][$V1['rpt_user']] = $V1['rpt_nota']; }

		}else{ $Map['tot'][$V1['rpt_user']] = $V1['rpt_nota']; }
		
	}
	// PROCURA A RPT NA UNIÃO COM REDAÇÃO E LITERATURA
	if(array_key_exists('unirportugues',$ES) AND $ES['unirportugues'] == 1){
		$Base = $db -> prepare("SELECT alt.vp_id, rpt_user, ROUND(rpt_nota) as rpt_nota  FROM vinc_prof as main 
		INNER JOIN vinc_prof as alt ON (alt.vp_turma = main.vp_turma)
		INNER JOIN disciplinas ON (disciplinas.disc_id = alt.vp_disc)
		INNER JOIN avaliacoes_rpt ON (avaliacoes_rpt.rpt_vp = alt.vp_id)
		WHERE main.vp_id = ? AND avaliacoes_rpt.rpt_tri = ? AND alt.vp_id != main.vp_id AND alt.vp_disc IN (8,31,32) AND main.vp_disc IN (8,31,32)");
		$Base -> bind_param('ii',$VP,$Tri); $Base -> execute();  $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		foreach($Base as $K1=>$V1){
			if(!array_key_exists($V1['rpt_user'],$Map['rpt'])){ $Map['rpt'][$V1['rpt_user']] = $V1['rpt_nota']; }
			if($Map['rpt'][$V1['rpt_user']] > $V1['rpt_nota']){ $Map['rpt'][$V1['rpt_user']] = $V1['rpt_nota']; }
			if(array_key_exists($V1['rpt_user'],$Map['tot']) AND $Map['tot'][$V1['rpt_user']] < $V1['rpt_nota']){ $Map['tot'][$V1['rpt_user']] = $V1['rpt_nota']; }
		}
	}
	// REORGANIZA AS AVIs
	$AVI = []; foreach([2,1,3,4,9] as $K1=>$V1){ foreach($Map['avi'] as $K2=>$V2){ if($V2['avi_tipo'] == $V1){ $AVI[$K2] = $V2; }}}
	if(count($AVI) == count($Map['avi'])){ $Map['avi'] = $AVI;}
	
	// CALCULA O RENDIMENTO DE CADA AVALIAÇÃO
	foreach($Map['avi'] as $K1=>$V1){
		$Map['avi'][$K1]['pct'] = ['nota'=>[], 'rp'=>[]]; $Media = [[],[]];
		foreach($V1['map'] as $K2=>$V2){
			if(is_numeric($V2[0])){ $Media[0][] = ($V2[0] >= intval($V1['avi_valor']) * 0.6) ? 1 : 0; }
			if(is_numeric($V2[1])){ $Media[1][] = ($V2[1] >= intval($V1['avi_valor']) * 0.6) ? 1 : 0; }
		}
		$Map['avi'][$K1]['pct']['nota'] = (count($Media[0])) ? number_format(100*array_sum($Media[0])/count($Media[0]),0) : null;
		$Map['avi'][$K1]['pct']['rp']   = (count($Media[1])) ? number_format(100*array_sum($Media[1])/count($Media[1]),0) : null;
	}
	
	// CRIA O RMAP PARA CALCULO DO RENDIMENTO
	foreach($TurmaEMap as $K1=>$V1){
		if($V1['vt_sit'] == 0 OR array_key_exists($V1['vt_user'],$Map['tot'])){
			$Map['rmap'][$V1['vt_user']] = (isset($Map['tot']['vt_user']))? $Map['tot'][$V1['vt_user']] : 0;
		}
	}

	return $Map;
}
function FinalMap($vp,$SCT=false){
	global $ANOBASE,$db,$MYSCT;
	$SCT = ($SCT)?$SCT:$MYSCT; $Map = [];
	$Base = $db -> prepare("SELECT avaliacoes_rpt.* FROM avaliacoes_rpt
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes_rpt.rpt_vp)
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
	WHERE rpt_vp = ? AND turmas.turma_secretaria = ? AND YEAR(turma_dref) = ? AND rpt_tri = '100'"); dbE();
	$Base -> bind_param("iii",$vp,$SCT,$ANOBASE); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map[$V1['rpt_user']] = $V1['rpt_nota'];
	}
	return (is_array($Map)) ? $Map : [];
}
function FrequenciaBaseMap($vp,$data=false,$turma=false,$anual=false){
	global $db,$ANOBASE,$TRI,$ES; $Map = []; #$Map = ['id'=>NULL,'conteudo' => [1=>null,2=>null],'map'=>[]];
	
	if(is_numeric($data)){ 
		// PROCURA A DATA
		$Base = $db -> prepare("SELECT bp_id, bp_info, bp_data, bpf_user, bpf_sit FROM bncc_pauta
		LEFT JOIN bncc_pauta_frequencia as bpf ON (bpf.bpf_bp = bncc_pauta.bp_id)
		WHERE bp_vp = ? AND (bp_id) = ?"); dbE();
		$Base -> bind_param("ii",$vp,$data);
		$Base -> execute();
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){ 
			// REGISTRA INFORMAÇÃO DA AULA (CONTEUDO);
			if($Map == null){
				$Map['id'] = $V1['bp_id'];
				$Map['info'] = $V1['bp_info'];
				$Map['data'] = Data($V1['bp_data'],2);
			}
			// REGISTRA A PRESENÇA OU FALTA
			$Map['map'][$V1['bpf_user']] = $V1['bpf_sit'];
		} 
		return $Map;
	}else{
		// DEFINE AS DATAS A SE PROCURAR
		$DIni = Data($ES[($anual?1:$TRI)."triini"],2);
		$DFim = Data($ES[$TRI."trifim"],2);
		
		$Map = [];
		$Map['data'] = []; // MAPEIA AS DATAS PARA VER QUAL TERÁ MAIS DE UMA DATA
		$COR = ['secondary','warning','verpsc']; // PARAMETRIZA

		// PROCURA NA BASE
		$Base = $db -> prepare("SELECT
			bp_id, bp_info, DATE(bp_data) as bp_data
		FROM bncc_pauta
		WHERE bp_vp = ? AND YEAR(bp_dref) = ? AND (bp_data BETWEEN ? AND ?)
 		ORDER BY bp_data, bp_id ASC"); dbE();
		$Base -> bind_param("iiss",$vp,$ANOBASE,$DIni,$DFim);
		$Base -> execute();
		$Map['map'] = [];
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			// VERIFICA SE JA FOI ADICIONADO O DIA, SE NÃO, O CRIA.
			if(!array_key_exists($V1['bp_data'],$Map['data'])){ $Map['data'][$V1['bp_data']] = [0,0]; }
			// VERIFICA SE FOI DIGITADO ALGUMA COISA NA AULA
			$Sit = strlen($V1['bp_info']) > 0 ? 2 : 1;
			// CRIA O MAP DO DIA
			$Map['map'][$V1['bp_id']] = [
				'id' => $V1['bp_id'],
				'data' => $V1['bp_data'],
				'sit' => $Sit,
				'cor' => $COR[$Sit],
				'aula' => 0,
				'info' => $V1['bp_info'],
				'map' => []
			];
			$Map['data'][$V1['bp_data']][1]++;
		}
		// ATRIBUI O NUMERO DA AULA
		foreach($Map['data'] as $K1=>$V1){
			if($V1[1] > 1) // VERIFICA SE JA FOI TUDO ATRIBUIDO
			foreach($Map['map'] as $K2=>$V2){
				if($K1 == $V2['data']){
					$Map['map'][$K2]['aula'] = $V1[1] - $Map['data'][$K1][0];
					$Map['data'][$K1][0]++;
				}
			}
		}
		
		// SE A TURMA FOR INFORMADA, MAPEIA A TURMA
		if(is_numeric($turma)){
			// PROCURA NA BASE OS ESTUDANTES QUE TEM REGISTRO DE PRESENÇA OU FALTA E ATRIBUI AO MAP
			$Base = $db -> prepare("SELECT bp_id as id, bpf_user as user, bpf_sit as sit FROM bncc_pauta
			INNER JOIN bncc_pauta_frequencia ON (bncc_pauta_frequencia.bpf_bp = bncc_pauta.bp_id) 
			WHERE bp_vp = ? AND YEAR(bp_dref) = ? AND (bp_data BETWEEN ? AND ?)"); dbE();
			$Base -> bind_param("iiss",$vp,$ANOBASE,$DIni,$DFim);
			$Base -> execute();
			foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
				if(array_key_exists($V1['id'],$Map['map'])){
					$Map['map'][$V1['id']]['map'][$V1['user']] = $V1['sit'];
				}
			}
			// SELECIONA OS ESTUDANTES
			$EMap = TurmaEMap($turma);
			// SELECIONA A BASE
			foreach($EMap as $K1=>$V1){
				foreach($Map['map'] as $K2=>$V2){
					if(!array_Key_exists($V1['vt_user'],$V2['map'])){
						$Map['map'][$K2]['map'][$V1['vt_user']] = (ESitStatus($V1,$V2['data'])) ? 1 : 'E';
					}
				}
			}
		}
		unset($Map['data']);
	}
	return $Map['map'];
}
// SECRETARIA MAP
function SecretariaFaltasMap($Turma){
	global $db, $MYSCT, $ANOBASE, $TRI; $Map = [];
	$Base = $db -> prepare("SELECT bncc_secretaria_frequencia.* FROM bncc_secretaria_frequencia 
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = bncc_secretaria_frequencia.bsf_vp)
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
	WHERE turmas.turma_id = ? AND turmas.turma_secretaria = ? AND YEAR(turmas.turma_dref) = ? AND bsf_tri = ?");
	$Base -> bind_param("iiii",$Turma, $MYSCT, $ANOBASE, $TRI);
	$Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyB=>$ViewB){
		$Map[$ViewB['bsf_vp']][$ViewB['bsf_user']] = $ViewB['bsf_valor'];
	}
	return $Map;
}
// MAPEIA AS FALTAS
function BNCCPautaMap($vp,$User = false){
	global $db, $ANOBASE, $ES;
	
	$Base = $db -> prepare("SELECT 
		bncc_pauta.bp_id, bncc_pauta.bp_data, bncc_pauta_frequencia.bpf_user, bncc_pauta_frequencia.bpf_sit FROM bncc_pauta
	LEFT JOIN bncc_pauta_frequencia ON (bncc_pauta_frequencia.bpf_bp = bncc_pauta.bp_id)
	WHERE bncc_pauta.bp_vp = ? AND YEAR(bncc_pauta.bp_dref) = ?");
	$Base -> bind_param("ii",$vp,$ANOBASE);
	$Base -> execute();
	
	// CRIA O VETOR COM O MAPA
	$Map = [
		1 => ['total' => [],'map' => []],
		2 => ['total' => [],'map' => []],
		3 => ['total' => [],'map' => []],
	];

	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $KeyM=>$ViewM){

		$qTri = qTri(Data($ViewM['bp_data'],2));
		$Map[$qTri]['total'][] = $ViewM['bp_id']; // ACRESCENTA O ID AO BP PARA VERIFICAR O QUANTITATIVO DE AULAS DADAS
		@$Map[$qTri]['map'][$ViewM['bpf_user']] += ($ViewM['bpf_sit'] == 0)?1:0; // VERIFICA SE FOI REGISTRADO FALTA OU NAO, SE FOI, CONTABILIZA

	}

	// REMOVE AS DUPLICAÇÕES DOS IDS
	foreach($Map as $KeyM=>$ViewM){
		$Map[$KeyM]['total'] = count(array_unique($ViewM['total']));
	}	

	return $Map;
}
function PVProgramMap($Turma){
	global $db, $ANOBASE, $MYSCT;
	$Map = ['load'=>['introducao','valores','visao','missao','premissas','objetivos','prioridades','metas']];
	$Base = $db -> prepare("SELECT pv_plano.* FROM pv_plano
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = pv_plano.pvp_user)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	WHERE vt_turma = ? AND YEAR(vt_dref) = ? AND turmas.turma_secretaria = ?");
	$Base -> bind_param("iii",$Turma,$ANOBASE,$MYSCT); $Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map[$V1['pvp_user']]['reg'] = [false,0,[0,0,0]];
		$Map[$V1['pvp_user']]['pct'] = 0;
		$Map[$V1['pvp_user']][$V1['pvp_campo']] = ($V1['pvp_campo']=='atualizado') ? $V1['pvp_valor'] : strlen($V1['pvp_valor']);
	}

	foreach($Map as $K1=>$V1){ if(is_numeric($K1)){
		foreach($Map['load'] as $K2=>$V2){
			if(array_key_exists($V2,$V1)){
				if($V1[$V2] > 0){
					$Map[$K1]['pct']++;
					$Map[$K1]['reg'][0] = $V2;
					#$Map[$K1]['reg'][1] = number_format(100*($K2+1)/count($Map['load']),0);
				}#else{
					#break;
				#}
			}
		}
		$Map[$K1]['reg'][1] = ($Map[$K1]['pct'] == 0) ? 0 : number_format(100*$Map[$K1]['pct']/count($Map['load']),0);
	}}
	// MAPEIA OS ITENS A SEREM CUMPRIDOS
	$Base = $db -> prepare("SELECT pvpi_user, pvpi_sit, SUM(CASE WHEN pvpi_sit > 0 THEN 1 ELSE 0 END) as qt
	FROM pv_plano_itens
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = pv_plano_itens.pvpi_user)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	WHERE vt_turma = ? AND YEAR(vt_dref) = ? AND turmas.turma_secretaria = ?
	GROUP BY pvpi_user, pvpi_sit");
	$Base -> bind_param("iii",$Turma,$ANOBASE,$MYSCT); $Base -> execute();
	foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K1=>$V1){ if(array_key_exists($V1['pvpi_user'],$Map)){
		$Map[$V1['pvpi_user']]['reg'][2][$V1['pvpi_sit']] = $V1['qt'];
	
	}}
	return $Map;
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
function findTurma($Turma,$SCT=false){ // PROCURA A TURMA
	global $ANOBASE,$db,$MYSCT;
	$SCT = ($SCT)?$SCT:$MYSCT;
	$Base = $db -> prepare("SELECT turmas.* FROM turmas
	WHERE turma_id = ? AND turma_secretaria = ? AND YEAR(turma_dref)= ? LIMIT 1"); dbE();
	$Base -> bind_param("iis",$Turma,$SCT,$ANOBASE);
	$Base -> execute();
	$Base = $Base -> get_result() -> fetch_assoc();
	return (is_array($Base))?$Base:false;
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
function findAgendaServidor($User=false,$Sem=false,$Load=true,$Unico=false,$SCT=false){
	global $db, $ANOBASE, $MYSCT, $MEUID;
	$SCT = (is_numeric($SCT)) ? $SCT : $MEUID;
	$Seg = (is_numeric($Sem)) ? Data($Sem,29) : eSeg();
	$User= (is_numeric($User))? $User : $MEUID;
	$Sex = date('Y-m-d',strtotime("$Seg + 4 days"));
	
	$BaseMain  = $db -> prepare("SELECT * FROM agenda_servidor WHERE ag_user = ? AND (DATE(ag_data) BETWEEN ? AND ?) AND YEAR(ag_dref) = ? ORDER BY ag_data ASC"); dbE();
	$BaseMain -> bind_param("issi",$User,$Seg,$Sex,$ANOBASE); $BaseMain -> execute(); 
	
	// CRIA O MAPA INICIAL
	$Map = ['semanal' => Data($Seg,'agenda'), 'sem' => $Sem, 'load' => $Load, 'map'=>[]];
	// ATRIBUI OS RESULTADOS ENCONTRADOS NO MAPA
	foreach($BaseMain -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['load'] = false;
		if($Unico){ $Map['map'][$V1['ag_id']] = $V1; }else{ $Map['map'][Data($V1['ag_data'],9)][$V1['ag_id']] = $V1; }
	}
	
	// CASO NÃO ENCONTRE NENHUM ITEM PARA AGENDA , CARREGA A AGEDA MAIS RECENTE
	if($Map['load']){
		$SexMax = eSex(2);
		$Base = $db -> prepare("SELECT MAX(WEEK(ag_data,1)) as semana FROM agenda_servidor WHERE ag_user = ? AND YEAR(ag_dref) = ? AND DATE(ag_data) <= DATE(?)");
		$Base -> bind_param("iis",$User,$ANOBASE,$SexMax);
		$Base -> execute(); $Week = $Base -> get_result() -> fetch_assoc()['semana'];
		if(!is_numeric($Week)){$Map['load'] = false; return $Map;}
		
		$Seg = Data($Week,29); $Sex = date('Y-m-d',strtotime("$Seg + 4 days"));
		$BaseMain -> bind_param("issi",$User,$Seg,$Sex,$ANOBASE);
		$BaseMain -> execute();
		foreach($BaseMain -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
			if($Unico){ $Map['map'][$V1['ag_id']] = $V1; }else{ $Map['map'][Data($V1['ag_data'],9)][$V1['ag_id']] = $V1; }
		}
	}
	return $Map;
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
function findMinhaEletiva($User=false,$Prof=false,$Turno,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$MEUID;
	
	// PARAMETRIZA
	$Map = [];
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$User = (is_numeric($User))?$User:false;
	$Prof = (is_numeric($Prof))?$Prof:false;
	// VERIFICA SE TEM USUARIO ATIVO NUMERICAMENTE
	if($Prof==false AND $User==false){return false;}

	

	if($Prof){
		$Base = $db -> prepare("SELECT elt_id FROM vinc_prof 
		INNER JOIN eletivas ON (eletivas.elt_id = vinc_prof.vp_eletiva) 
		INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
		WHERE vpu_user = ? AND elt_secretaria = ? AND YEAR(vp_dref) = ? ORDER BY elt_periodo DESC, elt_nome ASC");
		$Base -> bind_param("iii",$Prof,$SCT,$ANOBASE); $Base->execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyElt=>$ViewElt){
			$Map[$ViewElt['elt_id']] = findElt($ViewElt['elt_id']);
		}
		//ppre($Map);
		return $Map;
	}


		
}
function findElt($Elt,$SCT=false){
	global $db, $ANOBASE, $MYSCT;
	// PARAMETRIZA
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;

	$Base = $db -> prepare("SELECT vp_id,eletivas.* FROM eletivas 
	INNER JOIN vinc_prof ON (vinc_prof.vp_eletiva = eletivas.elt_id) 
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id) 
	WHERE elt_secretaria = ? AND YEAR(elt_dref) = ? AND elt_id = ?
	ORDER BY elt_periodo DESC, elt_nome ASC"); dbE();
	$Base -> bind_param("iii",$SCT,$ANOBASE,$Elt); $Base -> execute();
	$Base = $Base -> get_result();

	if($Base -> num_rows){

		$DiscMap = DiscMap();

		$Map = $Base -> fetch_assoc();
		$Map = array_merge($Map,['map'=>[],'prof'=>[]]);

		// DEFINE AS DISCIPLINAS
		$EltDisc = $Map['elt_disc']; $Map['elt_disc'] = [];
		foreach(explode(',',$EltDisc) as $KeyD=>$ViewD){
			if(array_key_exists($ViewD,$DiscMap)){
				$Map['elt_disc'][$ViewD] = $DiscMap[$ViewD]['disc_nome'];
			}
		}
		// PROCURA OS PROFESSORES
		$Prof = $db -> prepare("SELECT user_id, ui_nome FROM user 
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
		INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_user = user.user_id) 
		INNER JOIN vinc_prof ON (vinc_prof.vp_id = vinc_prof_user.vpu_vp) 
		WHERE vinc_prof.vp_eletiva = ? AND user.user_secretaria = ? AND YEAR(vinc_prof.vp_dref) = ?");
		$Prof -> bind_param("iii",$Elt,$SCT,$ANOBASE); $Prof->execute();
		foreach($Prof->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyProf=>$ViewProf){
			$Map['prof'][$ViewProf['user_id']] = $ViewProf['ui_nome'];
		}
		// PROCURA OS ESTUDANTES
		$Base = $db -> prepare("SELECT user_id, ui_nome, vt_sit, vt_remanejado, eletivas_vinc.*, turmas.* FROM eletivas_vinc
		INNER JOIN user ON (user.user_id = eletivas_vinc.eltv_user) 
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id) 
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma) 
		WHERE user.user_tipo = '33' AND eletivas_vinc.eltv_elt = ? AND vinc_turma.vt_sit IN (0,2) AND turmas.turma_secretaria = ? AND YEAR(eletivas_vinc.eltv_dref) = ? 
		ORDER BY vinc_turma.vt_sit DESC, turmas.turma_mod, turmas.turma_serie, turmas.turma_num, turmas.turma_comp, userinfo.ui_nome ASC"); dbE();
		$Base -> bind_param("iii",$Elt,$SCT,$ANOBASE); $Base -> execute();
		$Map['map'] = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'user_id');
		foreach($Map['map'] as $K1=>$V1){
			$Map['map'][$K1]['vt_lock'] = ESitLock($V1);
			$Map['map'][$K1]['vt_sit_date'] = (($V1['vt_sit'] == 1 OR $V1['vt_sit'] == 2) ? $V1['vt_remanejado'] : null);
		}

	}else{return false;}
	return $Map;
}
function findVP($Vp,$SCT=false){ // PROCURA O VP
	global $ANOBASE,$db,$MYSCT;
	$SCT = ($SCT)?$SCT:$MYSCT;
	$VPF =[]; $Base = $db -> prepare("
	SELECT vinc_prof.*,vinc_prof_user.*,turmas.*,disciplinas.*,ui_nome FROM vinc_prof
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN user ON (vinc_prof_user.vpu_user = user.user_id)
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc)
	LEFT JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
	WHERE vp_id = ? AND  user.user_secretaria = ? AND YEAR(vp_dref)= ?"); dbE();
	$Base -> bind_param("iis",$Vp,$SCT,$ANOBASE);
	$Base -> execute();
	$Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		if(count($VPF)==0){$VPF = $V1;}
		$VPF['users'][$V1['vpu_id']] = ['id'=>$V1['vpu_user'],'nome'=>$V1['ui_nome']];
		$VPF['users_id'][] = $V1['vpu_user'];
	}
	return $VPF;
}
function findAVI($Avi,$VP,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$TRI; $SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	if(!is_numeric($VP) OR !is_numeric($Avi)){return false;} $Map=[];
	$Base = $db -> prepare("SELECT avaliacoes.*, vp_turma FROM avaliacoes
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes.avi_vp)
	WHERE avi_id = ? AND avi_vp = ?"); dbE();
	$Base -> bind_param("ii",$Avi,$VP); $Base -> execute(); $Map = $Base -> get_result() -> fetch_assoc(); 
	if(!is_array($Map) OR @count($Map)==0){return false;} $Map['map'] = [];
	$Base = $db -> prepare("SELECT avaliacoes_notas.* FROM avaliacoes
	INNER JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id)
	WHERE avi_vp = ? AND avi_id = ?"); dbE();
	$Base -> bind_param("ii",$VP,$Avi); $Base -> execute(); $Base = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	foreach($Base as $K1=>$V1){
		$Map['map'][$V1['avn_user']] = [
			0=>($V1['avn_nota']<0)?null:$V1['avn_nota'],
			1=>($V1['avn_rp']<0)?null:$V1['avn_rp']
		];
	}
	return $Map;
}
function findEOEdit($vp,$data,$Prof=false,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$MEUID; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$Prof = (is_numeric($Prof))?$Prof:$MEUID;
	$Map = [];
	
	// VERIFICA A DATA
	if(!is_date($data)){ return []; }

	// PROCURA NA BASE DE DADOS O EO PARA O DIA INFORMADO
	$Base = $db -> prepare("SELECT vp_id, turma_id, eo_atividades.* FROM vinc_prof
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
	LEFT JOIN eo_atividades ON (eo_atividades.eoa_vp = vinc_prof.vp_id) 
	WHERE 
		turmas.turma_secretaria = ? AND 
		vinc_prof.vp_id = ? AND 
		vinc_prof_user.vpu_user = ? AND 
		YEAR(vinc_prof.vp_dref) = ? AND 
		DATE(eoa_data) = ?
	LIMIT 1");
	$Base -> bind_param('iiiis',$SCT,$vp,$Prof,$ANOBASE,$data);
	$Base -> execute(); 
	$Map = $Base->get_result()->fetch_assoc();

	if(is_array($Map) AND count($Map) > 0){
		$Map['eoa_files'] = explode(',',$Map['eoa_files']);
		$Base = $db -> prepare("SELECT * FROM eo_listagem WHERE eol_atv = ?");
		$Base -> bind_param("i",$Map['eoa_id']);
		$Base -> execute(); $Base = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'eol_vt');
		$Map['map'] = ((is_array($Base) AND count($Base) > 0))?$Base:[];

		// CALCULA A PORCENTAGEM DE CUMPRIMENTO
		$Map['pct_cumprido'] = 0;
		foreach($Map['map'] as $K=>$V){
			if($V['eol_sit'] == 3){
				$Map['pct_cumprido']++;
			}
		}
		$Map['pct_cumprido'] = (count($Map['map'])) ? number_format(100*$Map['pct_cumprido']/count($Map['map']),1) : 0;
	}
	return (is_array($Map))?$Map:[];
}
function findEOList($vp,$Data,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$MEUID; 
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	// VERIFICA OS PARAMETROS PASSADOS
	if(!is_numeric($vp) OR !is_date($Data)){ return false; }

	// PARAMETRIZA O MAPA
	$Map = [
		'envios' => [], // QUEM ENVIO, QUANTOS, O QUE FAZER
		'map' => [], // QUEM RECEBEU, STATUS, O QUE FAZER
	];
	// PROCURA NA BASE TODOS OS VPs DA BNCC ASSOCIADOS A TURMA
	$Base = $db -> prepare("SELECT vpFind.vp_id, vpFind.vp_disc, ui_nome FROM vinc_prof as vpFind
	INNER JOIN vinc_prof as vpMain ON (vpMain.vp_turma = vpFind.vp_turma)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vpFind.vp_disc AND disciplinas.disc_area > 0)
	INNER JOIN turmas ON (turmas.turma_id = vpFind.vp_turma)
	LEFT JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vpFind.vp_id) 
	LEFT JOIN user ON (user.user_id = vinc_prof_user.vpu_user)
	LEFT JOIN userinfo ON (userinfo.ui_login = user.user_login) 
	WHERE turmas.turma_secretaria = ? AND YEAR(vpFind.vp_dref) = ? AND vpMain.vp_id = ?"); dbE();
	$Base -> bind_param("iii",$SCT,$ANOBASE,$vp);
	$Base -> execute();
	$Ress = $Base -> get_result(); if($Ress->num_rows == 0){return false;} // RETORNA FALSO CASO NÃO ENCONTRE NENHUM VINCULO
 	// NOME DAS COMPONENTES
	$Disc = DiscMap();
	// MAPEIA OS VPs
	foreach($Ress -> fetch_all(MYSQLI_ASSOC) as $K=>$V){
		if(!array_key_exists($V['vp_id'],$Map['envios'])){
			$Map['envios'][$V['vp_id']] = [
				'eoa'   => false,
				'total' => 0,
				'disc'  => $Disc[$V['vp_disc']]['disc_nome'],
				'prof'  => '',
				'info'  => '',
				'files' => '',
			];
			$Map['envios'][$V['vp_id']]['prof'] .= $V['ui_nome'] . '<br />';
		}
	}
	// PROCURA NA BASE ENVIOS PARA A DATA
	$vpKeys = implode(',',array_filter(array_keys($Map['envios'])));
	$Base = $db -> query("SELECT * FROM eo_atividades WHERE eoa_vp IN ($vpKeys) AND DATE(eoa_data) = '$Data'"); dbE();
	if($Base -> num_rows == 0){ return false; } // RETORNA FALSO SE NÃO ENCONTRA DEMANDA
	foreach($Base -> fetch_all(MYSQLI_ASSOC) as $K=>$V){
		if(array_key_exists($V['eoa_vp'],$Map['envios'])){
			$Map['envios'][$V['eoa_vp']]['eoa'] = $V['eoa_id'];
			$Map['envios'][$V['eoa_vp']]['info'] = $V['eoa_info'];
			$Map['envios'][$V['eoa_vp']]['files'] = $V['eoa_files'];
		}
	}
	// LIMPA DO MAPA OS VPS QUE NÃO REALIZARAM ENVIO
	foreach($Map['envios'] as $K=>$V){if($V['eoa']==false){unset($Map['envios'][$K]);}}
	$vpKeys = implode(',',array_filter(array_keys($Map['envios'])));
	if(strlen($vpKeys)){ // SO PROCURA SE EXISTIR AO MENOS 1 VP
		// PROCURA NA BASE AS INDICACOES
		$Base = $db -> query("SELECT eoa_vp, eo_listagem.* FROM eo_atividades 
		INNER JOIN eo_listagem ON (eo_listagem.eol_atv = eo_atividades.eoa_id)
		WHERE eoa_vp IN ($vpKeys) AND DATE(eoa_data) = '$Data'"); dbE();
		foreach($Base->fetch_all(MYSQLI_ASSOC) as $K=>$V){
			$Map['map'][$V['eol_vt']][$V['eoa_vp']] = $V;
			$Map['envios'][$V['eoa_vp']]['total']++;
		}
	}
	return $Map;
}
function findPVPlano($User=false,$SCT=false){
	global $db, $MYSCT, $MEUID;
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	$User = (is_numeric($User))?$User:$MEUID;
	// BUSCA OS CAMPOS
	$Base = $db -> prepare("SELECT pv_plano.* FROM pv_plano
	INNER JOIN user ON (user.user_id = pv_plano.pvp_user)
	WHERE pvp_user = ? AND user.user_secretaria = ?");
	$Base -> bind_param("ii",$User,$SCT); $Base -> execute();
	$Map = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'pvp_campo');
	// BUSCA OS ITENS
	$Map['map'] = [0=>[],1=>[],2=>[]];
	$Base = $db -> prepare("SELECT pv_plano_itens.* FROM pv_plano_itens
	INNER JOIN user ON (user.user_id = pv_plano_itens.pvpi_user)
	WHERE pvpi_user = ? AND user.user_secretaria = ?");
	$Base -> bind_param('ii',$User,$SCT); $Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		$Map['map'][$V1['pvpi_tipo']][$V1['pvpi_id']] = $V1;
	}
	return $Map;
}
function findTutoria($TutID,$SCT=false){
	global $ANOBASE,$db,$MYSCT,$TRI,$MEUID,$ES; $SCT = (is_numeric($SCT))?$SCT:$MYSCT; $Map = [];
	$Base = $db -> prepare("SELECT
		Ti.ui_nome as TNome,
		Ei.ui_nome as ENome,
		tutoria.*
	FROM tutoria
	INNER JOIN user as Tu ON (Tu.user_id = tutoria.tut_tutor)
	INNER JOIN user as Eu ON (Eu.user_id = tutoria.tut_estudante)
	INNER JOIN userinfo as Ti ON (Ti.ui_login = Tu.user_login)
	INNER JOIN userinfo as Ei ON (Ei.ui_login = Eu.user_login)
	WHERE tutoria.tut_id = ? AND Tu.user_secretaria = ? LIMIT 1"); dbE();
	$Base -> bind_param('ii',$TutID,$SCT);
	$Base -> execute();
	$Res = $Base -> get_result() -> fetch_assoc();
	return (is_array($Res) AND array_key_exists('tut_id',$Res)) ? $Res : false;
}
function findOcorrencia($ID,$EST,$SCT=false){
	global $db,$ANOBASE,$MYSCT; $SCT = ($SCT)?$SCT:$MYSCT;
	$Base = $db -> prepare("SELECT ocorrencias.*,
		sedu_regimento.*,
		turmas.*,
		PorUi.ui_nome as PorNome,
		EstUi.ui_nome as EstNome,
		IF(oc_dev IS NULL,'Sem Tutor',(
			SELECT ui_nome FROM userinfo as DevUi
			INNER JOIN user as DevUs ON (DevUs.user_login = DevUi.ui_login)
			WHERE DevUs.user_id = ocorrencias.oc_dev LIMIT 1
		)) as DevNome
	FROM ocorrencias
	INNER JOIN user as PorUs ON (PorUs.user_id = ocorrencias.oc_por)
	INNER JOIN userinfo as PorUi ON (PorUi.ui_login = PorUs.user_login)
	INNER JOIN user as EstUs ON (EstUs.user_id = ocorrencias.oc_estudante)
	INNER JOIN userinfo as EstUi ON (EstUi.ui_login = EstUs.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = EstUs.user_id)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	LEFT JOIN tutoria ON (tutoria.tut_estudante = EstUs.user_id)
	LEFT JOIN sedu_regimento ON (sedu_regimento.sreg_id = ocorrencias.oc_regimento)
	WHERE EstUs.user_secretaria = ? AND EstUs.user_id = ? AND oc_id = ? AND YEAR(oc_data) = ? AND vt_sit IN (0,2) AND YEAR(vt_dref) = YEAR(oc_data)"); dbE();

	/*
	$Base = $db -> prepare("
	SELECT ocorrencias.*,
		sedu_regimento.*,
		turmas.*,
		PorUi.ui_nome as PorNome,
		DevUi.ui_nome as DevNome,
		EstUi.ui_nome as EstNome
	FROM ocorrencias
	INNER JOIN user as PorUs ON (PorUs.user_id = ocorrencias.oc_por)
	INNER JOIN userinfo as PorUi ON (PorUi.ui_login = PorUs.user_login)
	INNER JOIN user as DevUs ON (DevUs.user_id = ocorrencias.oc_devolutiva_por)
	INNER JOIN userinfo as DevUi ON (DevUi.ui_login = DevUs.user_login)
	INNER JOIN user as EstUs ON (EstUs.user_id = ocorrencias.oc_estudante)
	INNER JOIN userinfo as EstUi ON (EstUi.ui_login = EstUs.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_user = EstUs.user_id)
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
	INNER JOIN tutoria ON (tutoria.tut_estudante = EstUs.user_id)
	LEFT JOIN sedu_regimento ON (sedu_regimento.sreg_id = ocorrencias.oc_regimento)
	WHERE EstUs.user_secretaria = ? AND EstUs.user_id = ? AND oc_id = ? AND YEAR(oc_data) = ? AND vt_sit IN (0,2) AND YEAR(vt_dref) = YEAR(oc_data)"); dbE();
	*/
	$Base -> bind_param("iiii",$SCT,$EST,$ID,$ANOBASE);
	$Base -> execute();
	return $Base -> get_result() -> fetch_assoc();
}
function findTutEO($Tut,$SCT=false){
	global $db, $ANOBASE, $MYSCT, $MEUID, $ES, $TRI;
	$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
	if(!is_numeric($Tut)){return false;}
	
	$DIni = Data($ES[$TRI.'triini'],2); $DFim = Data($ES[$TRI.'trifim'],2);
	$Seg = eSeg(0);
	
	$Base = $db -> prepare("SELECT disciplinas.disc_id, disciplinas.disc_nome, disciplinas.disc_mini, eo_listagem.*, userinfo.ui_nome, vinc_prof.vp_id, eo_atividades.eoa_data FROM eo_listagem 
	INNER JOIN eo_atividades ON (eo_atividades.eoa_id = eo_listagem.eol_atv)
	INNER JOIN vinc_prof ON (vinc_prof.vp_id = eo_atividades.eoa_vp) 
	INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
	INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc) 
	INNER JOIN user ON (user.user_id = vinc_prof_user.vpu_user) 
	INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
	INNER JOIN vinc_turma ON (vinc_turma.vt_id = eo_listagem.eol_vt AND YEAR(vinc_turma.vt_dref) = YEAR(vinc_prof.vp_dref))
	INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma AND turmas.turma_id = vinc_prof.vp_turma)
	INNER JOIN tutoria ON (tutoria.tut_estudante = vinc_turma.vt_user) 
	WHERE YEAR(vinc_prof.vp_dref) = ? AND tutoria.tut_id = ? AND turmas.turma_secretaria = ?
	ORDER BY eoa_data DESC"); dbE();
	$Base -> bind_param("iii",$ANOBASE,$Tut,$SCT); $Base -> execute();
	$Map = ['disc' => [], 'map' => [], 'qt' => []];
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
		// CRIA AS INFORMAÇÕES DA DISCIPLINA
		$Map['disc'][$V1['disc_id']] = [
			'nome' => $V1['disc_nome'],
			'mini' => $V1['disc_mini'],
			'prof' => $V1['ui_nome'],
			'vp' => $V1['vp_id']
		];
		// MAPEIA AS ATIVIDADES
		$Map['map'][$V1['eoa_data']][$V1['disc_id']] = $V1['eol_sit'];
		// CRIA O VETOR DE SOMA FINAL
		if(!array_key_exists($V1['disc_id'],$Map['qt'])){ $Map['qt'][$V1['disc_id']] = [0=>0,1=>0,2=>0,3=>0,'pct'=>0]; }
		$Map['qt'][$V1['disc_id']][$V1['eol_sit']]++;
	}
	
	if(count($Map['qt'])){
		foreach($Map['qt'] as $K1=>$V1){
			$Max = array_sum($V1);
			$Map['qt'][$K1]['pct'] = number_format(100 * $V1[3]/$Max,0);
			$Map['qt'][$K1]['pct'] = ($Map['qt'][$K1]['pct'] > 100) ? 100 : $Map['qt'][$K1]['pct'];
		}
	}
	return $Map;
}