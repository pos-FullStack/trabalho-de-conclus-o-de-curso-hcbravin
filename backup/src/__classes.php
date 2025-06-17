<?php

use Intervention\Image\ImageManager;

// MANIPULAÇÃO DE PROVAS
class Provas {
	// CHAVE DE CONFIGURAÇÃO
	private $GabaDir = __CONFIG__.'/gabarito';
	private $Gaba = 'gaba_3.3.py';
	// CHAVES
	// IdentificarTipo = 0 -> RA; 1 -> Chamada
	public $id, $File, $turma, $IdentificarTipo;
	// QType = TIPO DE CORREÇÃO
	// Map = PROVA(s)
	// Uplodade = ARQUIVO ENVIADO
	// Extrator = ARQUIVOS EXTRAIDOS EM JPG
	// CORRETOR = MAPA COM A CORREÇÃO DE CADA ARQUIVO EXTRAIDO
	// ETurma = MAPA COM OS ESTUDANTES ATIVOS DAS TURMAS SELECIONADAS
	// Aluno = MAPA SOMENTE COM ESTUDANTES
	private $QType, $Map, $Uploaded, $Extrator, $Corretor, $ETurma, $Aluno;
	

	public function __construct(){
		$this -> QType = false;
		$this -> Corretor = [];
		$this -> Aluno = [];
	}

	public function getCorretor(){
		return $this -> Corretor;
	}

	public function getETurma(){
		return $this -> ETurma;
	}

	public function getAluno(){
		return $this -> Aluno;
	}

	public function Grafico(){
		$Map = ['taxas' => [], 'grupos' =>[]];
		// CALCULA A QUANTIDADE DE ACERTOS E ERROS, EM PORCENTAGEM
		foreach($this->Map['questoes'] as $KeyP=>$ViewP){
			$Map['taxas'][$ViewP['apq_num']]['I'] = $ViewP['apq_num'];
			$Map['taxas'][$ViewP['apq_num']]['A'] = $ViewP['apq_taxa'];
			$Map['taxas'][$ViewP['apq_num']]['E'] = (100 - $ViewP['apq_taxa']);
		}

		// FORMULA OS GRUPOS
		foreach($this->Map['ap_group'] as $KeyG=>$ViewG){
			foreach($ViewG as $KeyI=>$ViewI){
				$Map['grupos'][$KeyG][$ViewI] = $Map['taxas'][$ViewI]['A'];
				unset($Map['taxas'][$ViewI]);
			}
		}
		// REPASSA OS GRUPOS PROMOVENDO O CALCULO, FINALIZANDO OS ERROS E INFOMRANDO O NOME
		foreach($Map['grupos'] as $KeyG=>$ViewG){
			if(count($Map['grupos'][$KeyG])){
				$I = implode(',', array_keys($Map['grupos'][$KeyG]));
				$Map['grupos'][$KeyG] = ['A' => number_format(array_sum($Map['grupos'][$KeyG])/count($Map['grupos'][$KeyG]),2), 'E' => 0, 'I' => $I];
				$Map['grupos'][$KeyG]['E'] = 100 - $Map['grupos'][$KeyG]['A'];
			}else{
				unset($Map['grupos'][$KeyG]);
			}
		}
		// ASSOCIA OS DEMAIS VALORES QUE NÃO TEM GRUPOS
		$Map['grupos'] = $Map['grupos'] + $Map['taxas'];

		return $Map['grupos'];
	}

	public function Processar(){
		global $db;
		if(!is_array($this->Corretor) OR count($this->Corretor) == 0){ return false; Alert('Nenhuma correção encontrada!'); }
		if(!is_array($this->Map) OR count($this->Map['questoes']) == 0){ return false; Alert('Questões não carregadas.'); }
		if($this->IdentificarTipo == 1 AND count($this->turma) > 1){ return false; Alert('O método de correção por número de chamada só pode ser usado para <strong>uma única</strong> turma selecionada.'); }else{ $TurmaID = reset($this->turma); }

		// CONSTROI O MAP DE ACORDO COM A TURMA
		foreach($this->turma as $KeyT=>$ViewT){
			$this->ETurma[$ViewT] = array_merge(findTurma($ViewT),['map'=>TurmaEMap($ViewT)]);

			// REMOVE OS ESTUDANTES QUE NAO ESTAO ATIVOS
			foreach($this->ETurma[$ViewT]['map'] as $KeyE=>$ViewE){
				if($ViewE['vt_sit'] != 0){
					unset($this->ETurma[$ViewT]['map'][$KeyE]);
				}else{
					$this -> Aluno[$ViewE['vt_user']] = [
						'nome' => mb_strtoupper($ViewE['ui_nome'],'UTF-8'),
						'turma' => $ViewE['vt_turma'],
						'num' => $ViewE['vt_num'],
						'matricula' => $ViewE['ui_matricula'],
					];
				}
			}
		}

		// PROCESSA O REGISTRO
		foreach($this -> Corretor as $KeyQ=>$ViewQ){
			$this -> Corretor[$KeyQ]['resultado']['registros'] = implode('',$ViewQ['resultado']['registros']); // IRA COLAPSAR O VALOR EM UMA STRING

			// INFORMA USER_ID NULO
			if(!isset($this -> Corretor[$KeyQ]['user_id'])){ $this -> Corretor[$KeyQ]['user_id'] = false; }

			// PROCESSA OS ERROS DE QUESTOES NAO DETECTADAS REMOVENDO TODAS QUE ESTÃO ACIMA DO MÁXIMO DE QUESTOES PROGRAMADAS
			$MaxQuestoes = count($this->Map['questoes']);
			foreach($ViewQ['resultado']['questoes_erro'] as $KeyQe=>$ViewQe){
				if($ViewQe > $MaxQuestoes){
					unset($this -> Corretor[$KeyQ]['resultado']['questoes_erro'][$KeyQe]);
				}else{
					if(!isset($this -> Corretor[$KeyQ]['resultado']['questoes'][$ViewQe])){
						// SE HOUVE ALGUMA DENTRO DO LIMITE QUE NAO FOI DETECTADA, ATRIBUI NA CORREÇÃO COMO NULA
						$this -> Corretor[$KeyQ]['resultado']['questoes'][$ViewQe] = [];
					}
				}
			}

			// PROCESSA COMPARANDO A QUESTAO COM A PROVA E INFORMADNO SE HOUVE ACERTO OU NÃO
			foreach($this -> Corretor[$KeyQ]['resultado']['questoes'] as $KeyC=>$ViewC){
				// SE NAO FOR FEITO NENHUMA CORREÇÃO AINDA, FAZ ACRESCENTANDO O ID E O SIT
				if(!isset($this -> Corretor[$KeyQ]['resultado']['questoes'][$KeyC]['id'])){
					$this -> Corretor[$KeyQ]['resultado']['questoes'][$KeyC]['id'] = $this->Map['questoes_map'][$KeyC]['id'];
					$this -> Corretor[$KeyQ]['resultado']['questoes'][$KeyC]['sit'] = false;
				}
				// VERIFICA SE A RESPOSTA CORRETA ESTÁ DENTRE AS RESPOSTAS ACEITAVEIS
				// TAMBEM VERIFICA SE JÁ NÃO CONSTA COMO CORRETA A RESPOSTA, SE TIVER CORRETA, NÃO FAZ MAIS VERIFICACOES
				if($this -> Corretor[$KeyQ]['resultado']['questoes'][$KeyC]['sit'] == false){
					foreach($ViewC as $KeyER=>$ViewER){if(is_numeric($KeyER) AND in_array($ViewER,$this->Map['questoes_map'][$KeyC])){
						$this -> Corretor[$KeyQ]['resultado']['questoes'][$KeyC]['sit'] = true;
					}}
				}
			}

			// PROCESSA PARA ENCONTRAR O ESTUDANTE
			$TurmaID = (isset($TurmaID)) ? $TurmaID : false;
			foreach($this -> Aluno as $KeyAl=>$ViewAl){

				if($this->IdentificarTipo == 0){ // RA
					if($this -> Corretor[$KeyQ]['resultado']['registros'] == $ViewAl['matricula']){
						$this -> Corretor[$KeyQ]['user_id'] = $KeyAl;
						break;
					}
				}
				if($this->IdentificarTipo == 1){ // CHAMADA
					if(is_numeric($TurmaID) AND $TurmaID == $ViewAl['turma'] AND $this -> Corretor[$KeyQ]['resultado']['registros'] == $ViewAl['num']){
						$this -> Corretor[$KeyQ]['user_id'] = $KeyAl;
						break;
					}
				}
			}
			ksort($this -> Corretor[$KeyQ]['resultado']['questoes']);
		}


		$Ins = $db -> prepare("INSERT INTO avaliacoes_prova_respostas (apr_questao, apr_user, apr_resposta, apr_correta) VALUES (?,?,?,?)");
		// REGISTRA NO BANCO DE DADOS
		foreach($this -> Corretor as $KeyQ=>$ViewQ){

		
			// PROMOVE A INSERÇÃO DOS DADOS REFERENTE AS CORRECOES
			if(isset($ViewQ['user_id']) AND is_numeric($ViewQ['user_id'])){
				foreach($ViewQ['resultado']['questoes'] as $KeyR=>$ViewR){
					// AUXILIA NO CALCULO DA TAXA
					$this -> Map[$ViewR['id']]['apq_taxa_calc'][] = $ViewR['sit'];
				
					// AUXILIA NO REGISTRO E REGISTRA
					@$Respostas = implode(',',array_filter($ViewR, function ($_, $chave) {
						return ctype_digit((string) $chave);
					}, ARRAY_FILTER_USE_BOTH));
					$Ins -> bind_param("iisi",$ViewR['id'],$ViewQ['user_id'],$Respostas,$ViewR['sit']);
					$Ins -> execute();
				}
			}

			
		}

		// ATUALIZA A TAXA DE ACERTO DAS QUESTOES
		$this -> AtualizarTaxa();

		return true;
	}

	private function AtualizarTaxa(){
		global $db;

		// ATUALIZA A TAXA DAS QUESTOES
		$Upg = $db -> prepare("UPDATE avaliacoes_prova_questoes q
		JOIN (
			SELECT apr_questao, 
				ROUND((SUM(apr_correta) / COUNT(*)) * 100,2) AS taxa_acertos
			FROM avaliacoes_prova_respostas
			WHERE apr_questao IN (
				SELECT apq_id FROM avaliacoes_prova_questoes WHERE apq_prova = ?
			)
			GROUP BY apr_questao
		) r ON q.apq_id = r.apr_questao
		SET q.apq_taxa = r.taxa_acertos
		WHERE q.apq_prova = ?");
		$Upg -> bind_param("ii",$this->id,$this->id);
		$UpgTaxaQuestao = boolval($Upg->execute());

		// ATUALIZA A TAXA DE ACERTO DAS PROVAS
		// $Upg = $db -> prepare("UPDATE tabela_ap AS ap
		// JOIN tabela_apq AS apq ON ap.ap_id = apq.apq_prova
		// SET ap.ap_taxa = ROUND((
		// 	SELECT AVG(apq.apq_taxa)
		// 	FROM tabela_apq AS apq
		// 	WHERE apq.apq_prova = ap.ap_id
		// ),2)
		// WHERE ap.ap_id = ?"); dbE();
		// $Upg -> bind_param("i",$this->id); ppre($Upg);
		// $UpgTaxaProva = boolval($Upg->execute());
		$UpgTaxaProva=true;
		return ($UpgTaxaQuestao AND $UpgTaxaProva)?true:false;
	}

	public function Corrigir(){
		if(!is_array($this->Uploaded)){ Alert('Erro ao processar o upload.'); return false; }

		// EXTRAI AS PROVAS DO PDF
		$pdf = __ROOT__.'/files/'.($this->Uploaded['fl_dir']).($this->Uploaded['fl_arquivo']); // CAMINHO COMPLETO DO ARQUIVO
        $comando = escapeshellcmd("python3 $this->GabaDir/extrator.py $pdf");
        $retorno_pdf = shell_exec($comando);
        $json_pdf = json_decode($retorno_pdf,true);
		if(array_key_exists('arquivos',$json_pdf)){ $this->Extrator = $json_pdf['arquivos']; }else{ Alert('Erro ao extrair as páginas do arquivo.'); return false; }

		// INICIA O PROCESSO DE CORREÇÃO
		foreach($this->Extrator as $KeyI=>$ViewI){

			// $ViewI -> database/0a8fe9b103b27d222ba57382798b67ce/doc00575420250228162250_1.jpg
			$imagem = basename($ViewI); // NOME DO ARQUIVO
			$dirname = dirname($ViewI); // NOME DO CAMINHO
			$imagem_file = "$dirname/$imagem"; // NOME COMPELTO
			$docname = str_replace('.jpg','',$imagem); // NOME DA IMAGEM SEM EXTENCAO
			//print "Abrindo arquivo para leitura: $imagem".PHP_EOL;
	
			$pythonFile = ($this->GabaDir).'/'.($this->Gaba);
			$comando = escapeshellcmd("python3 $pythonFile $ViewI");
			$retorno = shell_exec($comando);
			$resultado = json_decode($retorno, true);
			
			// ATRIBUI O RESULTADO AO CORRETOR
			$this->Corretor[] = ['extrator' => $ViewI, 'resultado' => $resultado];

			// GERA O ARQUIVO json POR SEGURANÇA
			// $arquivo = fopen("$ViewI.json", "w");
			// fwrite($arquivo, $retorno);
			// fclose($arquivo);
		}

		return (is_array($this->Corretor) AND count($this->Corretor) > 0)?true:false;
	}

	public function CheckFile(){
		
		// VERIFICA O TAMANHO DO ARQUIVO A EXTENÇÃO ESTÁ CORRETA.
		if ($this->File['size'] > 10 * 1024 * 1024) { Alert('O tamanho do arquivo excedeu o limite.'); return false; }
		if (strtolower(pathinfo($this->File['name'], PATHINFO_EXTENSION)) !== 'pdf'){ Alert('A extenção do arquivo não é pdf.'); return false; }
		
		// FAZ O UPLOAD DO ARQUIVO 
		$Upload = New Upload();
		$Upload -> local = 'system';
		$Upload -> reg = false;
		$Upload -> input = $this -> File;
		$this -> Uploaded = $Upload -> Send();
		return (is_array($this->Uploaded));
	}

	public function findProva(){
		global $db, $MEUID, $ANOBASE, $MYSCT;
		$this->QType=true;
		$Base = $db -> prepare("SELECT * FROM avaliacoes_prova WHERE YEAR(ap_dref) = ? AND ap_id = ?");
		$Base -> bind_param("ii",$ANOBASE,$this->id);
		$Base -> execute();
		$this -> Map = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		$Map = $this->Render();
		$this -> Map = (is_array($Map) AND array_key_exists($this->id,$Map)) ? $Map[$this->id] : false;
		return $this -> Map;
	}

	public function Minhas(){
		global $db, $MEUID, $ANOBASE, $MYSCT;
		$Base = $db -> prepare("SELECT * FROM avaliacoes_prova WHERE YEAR(ap_dref) = ? AND (ap_user = ? OR JSON_CONTAINS(ap_edit,?,'$')) ORDER BY ap_id DESC");
		$Base -> bind_param("iii",$ANOBASE,$MEUID,$MEUID);
		$Base -> execute();
		$this -> Map = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
		return $this->Render();
	}

	private function Render(){
		
		$Map = [];
		$User = New Usuario();
		// BUSCA AS PROVAS E AS INFORMAÇÕES DE TURMA
		foreach($this->Map as $KeyA=>$ViewA){
			$Map[$ViewA['ap_id']] = $ViewA;
			$Map[$ViewA['ap_id']]['ap_edit_user'] = json_decode($ViewA['ap_edit'],true);
			$Map[$ViewA['ap_id']]['ap_vp'] = (is_numeric($ViewA['ap_vp'])) ? findVP($ViewA['ap_vp']) : NULL;
			$Map[$ViewA['ap_id']]['questoes'] = $this -> Questoes($ViewA['ap_id']);
			$Map[$ViewA['ap_id']]['questoes_map'] = [];
			$Map[$ViewA['ap_id']]['ap_group_id'] = [];
			$Map[$ViewA['ap_id']]['respostas'] = $this -> Respostas($ViewA['ap_id']);
			$Map[$ViewA['ap_id']]['respostas_qt'] = (is_array($Map[$ViewA['ap_id']]['respostas']))?count($Map[$ViewA['ap_id']]['respostas']):$Map[$ViewA['ap_id']]['respostas'];


			// INFORMA OS IDS QUE CONSTAM COMO AGRUPADOS
			if(is_array($Map[$ViewA['ap_id']]['questoes']) AND count($Map[$ViewA['ap_id']]['questoes'])){
				// CRIA O AGRUPAMENTO DE QUESTOES
				$CountGroup = 1000000001;
				$JsonGroupItem = json_decode($ViewA['ap_group'],true);
				$Map[$ViewA['ap_id']]['ap_group'] = [];
				foreach($JsonGroupItem as $KeyJ=>$ViewJ){
					foreach($ViewJ as $KeyJI=>$ViewJI){
						$Map[$ViewA['ap_id']]['ap_group'][$CountGroup][$ViewJI] = $Map[$ViewA['ap_id']]['questoes'][$ViewJI]['apq_num'];
						$Map[$ViewA['ap_id']]['ap_group_id'][] = $ViewJI;
					}
					$CountGroup++;
				}
				// CRIA O MAP DE QUESTOES
				foreach($Map[$ViewA['ap_id']]['questoes'] as $KeyMQ=>$ViewMQ){
					$Map[$ViewA['ap_id']]['questoes_map'][$ViewMQ['apq_num']] = array_merge(['id' => $KeyMQ],$ViewMQ['apq_resposta']);
				}


			}else{ $Map[$ViewA['ap_id']]['ap_group'] = []; }

			// PROCURA O NOME DOS EDITORES
			$Map[$ViewA['ap_id']]['ap_edit'] = [];
			$User -> id = $Map[$ViewA['ap_id']]['ap_edit_user'];
			foreach($User -> findUser() as $KeyU=>$ViewU){
				$Map[$ViewA['ap_id']]['ap_edit'][$ViewU['user_id']] = $ViewU['ui_nome'];
			}
		}
		return $Map;
	}

	private function Questoes($ap_id){
		global $db;

		if($this->QType){
			// RETORNA AS QUESTOES DA PROVA
			$Questoes = $db -> query("SELECT * FROM avaliacoes_prova_questoes WHERE apq_prova = '$ap_id' ORDER BY apq_num ASC") -> fetch_all(MYSQLI_ASSOC);
			$Map = ReKey($Questoes,'apq_id');
			foreach($Map as $KeyM=>$ViewM){
				$Map[$KeyM]['apq_alternativas'] = json_decode($ViewM['apq_alternativas'],true);
				$Map[$KeyM]['apq_resposta'] = json_decode($ViewM['apq_resposta'],true);
				$Map[$KeyM]['apq_taxa_calc'] = [];
			}
			return $Map;

		}else{
			// RETORNA O TOTAL DE QUESTOES DA PROVA
			return $db -> query("SELECT count(apq_id) as total FROM avaliacoes_prova_questoes WHERE apq_prova = '$ap_id'") -> fetch_assoc()['total'];
		}

		return false;
	}

	private function Respostas($ap_id){
		global $db;

		if($this->QType){
			$Map = [];
			// RETORNA AS QUESTOES DA PROVA
			$Questoes = $db -> query("SELECT avaliacoes_prova_respostas.* FROM avaliacoes_prova_questoes 
			INNER JOIN avaliacoes_prova_respostas ON (avaliacoes_prova_respostas.apr_questao = avaliacoes_prova_questoes.apq_id)
			WHERE apq_prova = '$ap_id' ORDER BY apq_num ASC") -> fetch_all(MYSQLI_ASSOC); 
			foreach($Questoes as $KeyR=>$ViewR){
				$Map[$ViewR['apr_user']][$ViewR['apr_questao']] = array_merge(explode(',',$ViewR['apr_resposta']),['sit' => $ViewR['apr_correta']]);
			}
			return $Map;

		}else{
			// RETORNA O TOTAL DE QUESTOES DA PROVA
			return $db -> query("SELECT count(DISTINCT apr_user) as total FROM avaliacoes_prova_questoes 
			INNER JOIN avaliacoes_prova_respostas ON (avaliacoes_prova_respostas.apr_questao = avaliacoes_prova_questoes.apq_id)
			WHERE apq_prova = '$ap_id'") -> fetch_assoc()['total'];
		}

	}

}

// MANIPULAÇÃO DE PAUTA
class Pauta {
	public $vp, $Periodo, $Map;
	private $isEJA,$findVP,$DIni,$DFim;

	public function __construct($vp,$Periodo=false,$load=true){
		global $TRI,$TRIS,$ES;
		// SE NAO FOR UM NUMERO, REMETE A ERRO
		if(!is_numeric($vp) OR !$load){return false;}

		// CARREGA AS INFORMAÇÕES
		$this -> vp = $vp;
		$this -> findVP = findVP($vp); // LOCALIZA INFORMAÇÃO SOBRE O VP
		$this -> isEJA = TMod($this->findVP,'eja'); // VERIFICA SE É UMA TURMA DA EJA OU NAO
		$this -> Periodo = (is_numeric($Periodo)) ? $Periodo : (($this->isEJA)?$TRIS:$TRI); // INFORMA O PERIODO ESCOLHIDO (1,2,3)
		$this -> DIni = Data(($this->isEJA) ? $ES[$this->Periodo."semini"] : $ES[$this->Periodo."triini"],2); // INFORMA A DATA E INICIO DO PERIODO
		$this -> DFim = Data(($this->isEJA) ? $ES[$this->Periodo."semfim"] : $ES[$this->Periodo."trifim"],2); // INFORMA A DATA DE FIM DO PERIODO
		$this -> Map = ['previstos'=>0, 'calendario' => [], 'datasInfo'=>[],'datas' => []];

		// CARREGA O CALENDARIO
		$this -> Calendario();		// IRA CRIAR O CALENDARIO
		$this -> RegistrosLite();	// IRA BUSCAR OS REGISTROS E INFORMAR AS DATAS QUE HOUVE REGISTROS
		$this -> DiasAulas();		// COMPLETA O CALENDARIO COM BASE NOS DIAS QUE TEM AULA

		#ppre($this->Map['calendario']);
	}

	// CRIA O CALENDARIO COM AS DATAS ENTRE O PERIODO SELECIONADO
	public function Calendario(){
		for($M = intval(Data($this->DIni,7)); $M <= intval(Data($this->DFim,7)); $M++){
			foreach(Calendario($M) as $KeyS => $ViewS){
				foreach($ViewS as $KeyD=>$ViewD){
					$this -> Map['calendario'][$M][$KeyS][$KeyD] = ['dia' => $ViewD, 'cor' => null];
				}
			}
		}
	}

	// BUSCA AS AULAS DA TURMA REGISTRADA
	public function DiasAulas(){
		global $db,$ANOBASE,$ES;

		// CONTROLA A EDIÇÃO FURUTA DA PAUTA
		$PautaFutura = (isset($ES['pauta-registrofuturo']) AND $ES['pauta-registrofuturo'] == 1) ? true : false;

		$Base = $db -> prepare("SELECT turmas_aulas.* FROM vinc_prof
		LEFT JOIN turmas_aulas ON (turmas_aulas.aulas_turma = vinc_prof.vp_turma AND turmas_aulas.aulas_disc = vinc_prof.vp_disc AND YEAR(turmas_aulas.aulas_dref) = YEAR(vinc_prof.vp_dref))
		WHERE vp_id = ? AND aulas_active = 1
		ORDER BY aulas_dia, aulas_hora");
		$Base -> bind_param("i",$this->vp);
		$Base -> execute();
	
		$Map = ['dias' => [], 'map'=>[]];
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
			@$Map['dias'][$V['aulas_dia']]++;
			$Map['map'][$V['aulas_id']] = $V;
		}

		// INFORMA O DIA QUE TEM AULA NAS DATAS
		$Hoje = Data(null,2);
		$Feriados = Data(null,'feriados'); #ppre($Feriados);
		
		foreach($this -> Map['calendario'] as $KeyM => $ViewM){
			foreach($ViewM as $KeyW=>$ViewW){
				foreach($ViewW as $KeyD=>$ViewD){

					if(is_numeric($ViewD['dia'])){
						// MONTA O DIA DE LEITURA
						$Day = Data($ANOBASE."-".$KeyM.'-'.$ViewD['dia'],2);	
						$Color = NULL;				

						// VERIFICA SE É UM FERIADO
						if(in_array($Day,$Feriados)){ $Color = 'rosapk'; }

						// VERIFICA SE É O INICIO OU FIM DO PERIODO
						if($Day == $this -> DIni OR $Day == $this -> DFim){ $Color = 'warning'; /*$this -> Map['previstos']++;*/ }

						// VERIFICA SE É O DIA ATUAL
						if($Day == $Hoje){ $Color = 'verpsc'; }

						// PROMOVE AS VERIFICAÇÕES AFIM DE INFORMAR A COR
						if($Day >= $this -> DIni AND $Day <= $this -> DFim AND !in_array($Day,$Feriados)){ // INICIO DO PERIODO

							// PROMOVE A ASSOCIAÇÃO BASEADO NOS DIAS DE AULA INFORMADOS
							if(is_numeric($ViewD['dia']) AND array_key_exists($KeyD,$Map['dias'])){
								$Color = 'primary';
								$this -> Map['previstos']++; // ADICIONA MAIS UM AO ELEMENTO PREVISTO
							}
							
							// PROMOVE A VERIFICAÇÃO COM BASE NOS DIAS DE AULA REGISTRADOS
							if(isset($this->Map['datas'][$KeyM][$Day])){

								foreach($this->Map['datas'][$KeyM][$Day]['aulas'] as $KeyDT=>$ViewDT){
									// ASSOCIA SOMENTE SUCESSO OU ERRO
									if($ViewDT['color'] == 'success' OR $ViewDT['color'] =='danger'){
										$Color = $ViewDT['color'];
										if($ViewDT['color'] == 'danger'){ break; } // SE FOR INFORMADO ERRO, JA PARA POR AI
									}
								}

								// PERCORRE O CALENDARIO PARA PREENCHER COM AS DATAS
								if(is_numeric($ViewD['dia']) AND array_key_exists($KeyD,$Map['dias'])){
									for($i = count($this->Map['datas'][$KeyM][$Day]['aulas']); $i<$Map['dias'][$KeyD]; $i++){
										$this->Map['datas'][$KeyM][$Day]['aulas'][$i] = [
											'bp_id' => 'novo',
											'color' => 'primary',
											'icon'  => 'file-lines'
										];
									}
								}

							}else{
								$this->Map['datas'][$KeyM][$Day] = [
									'disabled' => ($Day > $Hoje)?($PautaFutura?false:'disabled'):false,
									'aulas' => []
								];
								if(is_numeric($ViewD['dia']) AND array_key_exists($KeyD,$Map['dias'])){
									for($i=0; $i<$Map['dias'][$KeyD]; $i++){
										$this->Map['datas'][$KeyM][$Day]['aulas'][$i] = [
											'bp_id' => 'novo',
											'color' => 'primary',
											'icon'  => 'file-lines'
										];
									}
								}
							}
						}

						// ASSOCIA A COR AO CALENDARIO
						$this -> Map['calendario'][$KeyM][$KeyW][$KeyD]['cor'] = $Color;
					}
				}
			}
			ksort($this->Map['datas'][$KeyM]);
		} krsort($this->Map['datas']);

		// MAPEIA O QUANTITATIVO DE AULAS ASSOCIADAS AO DATAS
		foreach($this->Map['datas'] as $KeyM => $ViewM){
			
			// INFORMA MAIS 1 A INFORMAÇÃO DE DATAS REGISTRADAS PARA AQUELE MES
			if(!array_key_exists($KeyM,$this->Map['datasInfo'])){
				$this->Map['datasInfo'][$KeyM] = 0;
			}

			// CONTABILIZA
			foreach($ViewM as $KeyD=>$ViewD){
				if(count($ViewD['aulas'])){
					foreach($ViewD['aulas'] as $KeyA=>$ViewA){
						$this->Map['datasInfo'][$KeyM]++;
					}
				}
			}
		}

		#ppre($this->Map['datas']);
		//return $Map;
	}

	// BUSCA O REGISTRO COM AS DATAS DE FORMA RESUMIDA
	public function RegistrosLite(){
		global $db;

		$Base = $db -> prepare("SELECT bp_id, bp_vp, LENGTH(bp_info) as bp_info, bp_data, COUNT(bpf_id) as bp_frequencia FROM bncc_pauta
        LEFT JOIN bncc_pauta_frequencia ON (bncc_pauta_frequencia.bpf_bp = bncc_pauta.bp_id)
        WHERE bncc_pauta.bp_vp = ? AND bp_data BETWEEN ? AND ?
		GROUP BY bp_id, bp_vp, bp_info, bp_data
		ORDER BY bp_id ASC");
        $Base -> bind_param('iss',$this->vp,$this->DIni,$this->DFim);
        $Base -> execute();

		$Hoje = Data(null,2);
        foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $K=>$V){
            $VMes = date("n",strtotime($V['bp_data'])); // MES VALOR INTEIRO
            $VDay = Data($V['bp_data'],2); // DIA DA SEMANA INTEIRO
            $VSem = date("W",strtotime($V['bp_data'])); // NUMERO DA SEMANA
            $VDia = date("N",strtotime($V['bp_data'])); // DIA DA SEMANA 1 - 7

            $Cor  = (($V['bp_info'] AND $V['bp_frequencia'])?'success': (($V['bp_info'] OR $V['bp_frequencia'])?'danger':'primary'));
            $Icon = (($V['bp_info'] AND $V['bp_frequencia'])?'check': (($V['bp_info'] OR $V['bp_frequencia'])?'exclamation-triangle':'file-lines'));

			$this -> Map['datas'][$VMes][$VDay]['disabled'] = ($VDay > $Hoje)?'disabled':false;
			$this -> Map['datas'][$VMes][$VDay]['aulas'][] = [
				// 'conteudo' => boolval($V['bp_info']),
				// 'frequencia' => boolval($V['bp_frequencia']),
				'bp_id' => $V['bp_id'],
				'color' => $Cor,
				'icon' => $Icon,
			];
		}
		return false;
	}	

	// INFORMA AS CORES DA PAUTA E SEUS SIGNIFICADOS
	public function Cores($Modo,$Element=1){
		$Tipos = [
            ['rosapk', 'Feriado'],
            ['info', 'Dia Atual'],
            ['warning', 'Início/Fim do Período'],
            ['primary', 'Dia Aula'],
            ['success', 'Registro Preenchido'],
            ['danger', 'Registro Faltando']
        ];
        switch ($Modo) {
            case 'all':
                return $Tipos;
                break;
            case is_numeric($Modo):
                return $Tipos[$Modo][$Element];
                break;
            default:
                return false;
        }
	}
}

// MANIPULAÇÃO DO USUÁRIO
class Usuario {
    public $id, $sct, $tipo;
    private $lid, $matricula, $nome, $cpf, $URIOne;

	public function __construct(){
		global $MYSCT;
		$this->sct = $MYSCT;
	}

    public function Login($User,$Pass){
        global $db;

        $Pass = md5($Pass);
        $Base = $db -> prepare("SELECT * FROM login WHERE login_user = ? AND login_pass = ? LIMIT 1");
        $Base -> bind_param('ss',$User,$Pass);
        $Base -> execute();
        $Res = $Base -> get_result() -> fetch_assoc();

        if(is_array($Res) AND count($Res) > 0){
            // APAGA O LOGIN BLOCK, CASO ESTEJA ATIVO
            if(isset($_SESSION['login_bloq'])){unset($_SESSION['login_bloq']);}

            // ATRIBUI O ID DE LOGIN
			$_SESSION['TRI'] = 404;
            $_SESSION['lid'] = $Res['login_id'];
            $this->lid = $Res['login_id'];

            // VERIFICA OS USUÁRIOS
            $BaseUsers = $db -> prepare("SELECT * FROM user
            INNER JOIN secretarias ON (secretarias.sct_id = user.user_secretaria)
            INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
            WHERE user_login = ? AND user_ativo = '1' AND sct_ativo = '1' AND user_server = '1'");
            $BaseUsers -> bind_param("i", $this->lid);
            $BaseUsers -> execute();
            $MeusUsers = $BaseUsers -> get_result() -> fetch_all(MYSQLI_ASSOC);

            if(is_array($MeusUsers) AND count($MeusUsers) == 1){

                $MeusUsers = reset($MeusUsers);
                $_SESSION['id'] = $MeusUsers['user_id'];
                $_SESSION['tipo'] = $MeusUsers['user_tipo'];
                $_SESSION['sre'] = $MeusUsers['sct_sre'];
                $_SESSION['esc'] = $MeusUsers['sct_esc'];
                $_SESSION['sct'] = $MeusUsers['sct_id'];
                $_SESSION['sct_tipo'] = $MeusUsers['sct_tipo'];
                $_SESSION['sre_nome'] = $MeusUsers['sct_nome'];
                $_SESSION['nome'] = $MeusUsers['ui_nome'];
                $_SESSION['pic'] = $MeusUsers['ui_pic'];

                hdr('/inicio');

            }else{ hdr('/login/users'); return null; }

        }else{ return false; }
    }

    public function MeusUsers(){
        global $db, $MS; 
        if(!isset($MS['lid']) OR !is_numeric($MS['lid'])){
            hdr('login'); return false;
        }

        $LUSER = [];
        $Locais = $db -> prepare("SELECT * FROM user
        INNER JOIN secretarias ON (secretarias.sct_id = user.user_secretaria)
        INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
        WHERE user.user_login = ? AND user_server = '1' ORDER BY secretarias.sct_ativo, user.user_ativo DESC, user.user_tipo ASC");
        $Locais -> bind_param("s",$MS['lid']);
        $Locais -> execute();
        $Locais = $Locais -> get_result();
        while($f = $Locais->fetch_assoc()){ $LUSER[$f['user_tipo']][$f['sct_id']] = ['nome'=>$f['sct_nome'],'uid'=>$f['user_id'], 'ativo'=>(($f['sct_ativo']==1 AND $f['user_ativo']==1)?true:false), 'sct_tipo'=>$f['sct_tipo']]; }
        return is_array($LUSER) ? $LUSER : [];
    }

	public function setNome($nome){
		$this->nome = $nome;
	}

	public function setURIOne($uriOne){
		$this->URIOne = $uriOne;
	}

	public function setRA($matricula){
		$this->matricula = $matricula;
	}

    public function setUser($Sct,$UserID){
        global $db,$MS;

        $Base = $db -> prepare("SELECT * FROM user
        INNER JOIN secretarias ON (secretarias.sct_id = user.user_secretaria)
        INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
        WHERE user.user_login = ? AND user.user_id = ? AND user.user_secretaria = ? AND user_server = '1' LIMIT 1");
        $Base -> bind_param("iii",$MS['lid'],$UserID,$Sct);
        $Base -> execute();
        $User = $Base -> get_result() -> fetch_assoc();
    
        if(is_array($User) AND array_key_exists('user_id',$User)){

            $_SESSION['id'] = $User['user_id'];
            $_SESSION['tipo'] = $User['user_tipo'];
            $_SESSION['sre'] = $User['sct_sre'];
            $_SESSION['esc'] = $User['sct_esc'];
            $_SESSION['sct'] = $User['sct_id'];
            $_SESSION['sct_tipo'] = $User['sct_tipo'];
            $_SESSION['sre_nome'] = $User['sct_nome'];
            $_SESSION['nome'] = $User['ui_nome'];
            $_SESSION['pic'] = $User['ui_pic'];
            
            hdr('inicio');
            return true;

        }else{ hdr('/login/users',false); return false; }
    }

	public function setCPF($cpf){
		$this -> cpf = $cpf;
	}

	public function findUser(){ // PROCURA O USUARIO
		global $ANOBASE,$db,$MYSCT;
		$SCT = $this -> sct;
		$SCT = (is_numeric($SCT))?$SCT:$MYSCT;
		$Usuario = $this->id;

		if(is_numeric($Usuario)){
			$Base = $db -> prepare("SELECT user.*, userinfo.*, login.login_user FROM user
			INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
			INNER JOIN login ON (login.login_id = user.user_login)
			WHERE user_id = ? AND user_secretaria = ? AND user_server = '1' LIMIT 1");
			$Base -> bind_param('ii',$Usuario,$SCT);
		}elseif(is_array($Usuario)){
			if(count($Usuario)>0){

				$Base = $db -> prepare("SELECT user.*, userinfo.* FROM user
				INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
				WHERE user_id IN (".implode(',',$Usuario).") AND user_secretaria = ? AND user_server = '1'");
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

	public function searchUser($userTipo){
		global $db, $MYSCT, $MEUTIPO, $URI;

		$Nome = $this -> nome;
		$Matricula = $this -> matricula;

		if(!is_numeric($MEUTIPO) OR !is_numeric($MYSCT) OR !is_numeric($userTipo)){ return []; }
		if(strlen($Nome) == 0 AND strlen($Matricula) == 0){ return []; }

		$Base = $db -> prepare("SELECT 
			user_id, user_tipo, user_secretaria, ui_nome, ui_pic, ui_nascimento, ui_doc, ui_matricula
		FROM user 
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		WHERE 
			userinfo.ui_nome LIKE CONCAT('%',?,'%') 
			OR (user.user_tipo = 33 AND  userinfo.ui_matricula = ?)
			AND user.user_server = '1' AND user.user_tipo = ?
		".(($MEUTIPO == 0 AND $this->URIOne != 'enturmar')?NULL:" AND user.user_secretaria = '$MYSCT'")."
		ORDER BY user_tipo, ui_nome");
		$Base -> bind_param("ssi",$Nome,$Matricula,$userTipo);
		$Base -> execute();
		
		return  ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'user_id');
	}

	public function CheckUser(){
		global $db;

		if(!is_numeric($this->tipo) OR (strlen($this->cpf) == 0 AND strlen($this->matricula) == 0)){return false;}
		if(strlen($this->cpf) AND !is_numeric($this->cpf)){ return false; }
		if(strlen($this->matricula) AND !is_numeric($this->matricula)){ return false; }

		$Base = $db -> query("SELECT ui_nome, ui_login, user_secretaria, user_tipo FROM userinfo
		LEFT JOIN user ON (user.user_login = userinfo.ui_login)
		WHERE true = true
			".(strlen($this->cpf)?"AND userinfo.ui_doc = '".($this->cpf)."'":'')."
			".(strlen($this->matricula)?"AND userinfo.ui_matricula = '".($this->matricula)."'":'')."
		LIMIT 1");

		$Map = [];
		foreach($Base -> fetch_all(MYSQLI_ASSOC) as $KeyI=>$ViewI){
			if(!array_key_exists('nome',$Map)){
				$Map = [
					'nome' => $ViewI['ui_nome'],
					'lid'  => $ViewI['ui_login'],
					'map'  => []
				];
			}
			$Map['map'][$ViewI['user_secretaria']][$ViewI['user_tipo']] = $ViewI['user_tipo'];
		}
		return (count($Map))?$Map:false;
	}

    public function Logout(){ session_destroy(); hdr('/'); return true; }

	public function Carteirinha(){
		global $db, $ANOBASE, $MYSCT;
		$this->tipo = 33;
	
		if(is_numeric($this->id)){
		
			$Base = $db -> prepare("SELECT * FROM user 
			INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
			LEFT JOIN user_carteirinha ON (user_carteirinha.uc_user = user.user_id) 
			WHERE user.user_id = ? AND user.user_tipo = ? AND user.user_secretaria = ?
			ORDER BY uc_dref DESC LIMIT 1"); dbE();
			$Base -> bind_param("iii",$this->id, $this->tipo, $this->sct);
		
		}else{

			$Base = $db -> prepare("SELECT user_id, ui_nome, ui_doc, ui_pic, user_carteirinha.*, turmas.* FROM user 
			INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
			INNER JOIN user_carteirinha ON (user_carteirinha.uc_user = user.user_id) 
			LEFT JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id AND vinc_turma.vt_sit IN (2,0) AND YEAR(vinc_turma.vt_dref) = ?)
			LEFT JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
			WHERE user.user_tipo = ? AND user.user_secretaria = ? AND (YEAR(uc_dref) = ? OR YEAR(uc_validade) = ?)
			ORDER BY vt_sit , uc_dref DESC"); dbE();
			$Base -> bind_param("iiiii", $ANOBASE, $this->tipo, $this->sct, $ANOBASE, $ANOBASE);
		}

		try {

			$Base -> execute();
			$Map = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'user_id');
			return (!is_numeric($this->id)) ? $Map : ((is_array($Map) AND array_key_exists($this->id,$Map)) ? $Map[$this->id] : false) ; 


		} catch(Exception $e){ return false; }
	}

	public function MinhaTurma(){
		global $db,$ANOBASE;

		try {
			$Base = $db -> prepare("SELECT turmas.*, vinc_turma.* FROM turmas 
			INNER JOIN vinc_turma ON (vinc_turma.vt_turma = turmas.turma_id AND vinc_turma.vt_sit IN (0,2)) 
			INNER JOIN user ON (user.user_id = vinc_turma.vt_user)
			WHERE vt_user = ? AND YEAR(vt_dref) = ? AND user.user_secretaria = ?
			ORDER BY vt_sit ASC"); dbE();
			$Base -> bind_param("iii",$this->id, $ANOBASE, $this->sct);
			$Base -> execute();
			return $Base -> get_result() -> fetch_assoc();

		} catch(Exception $e){ return false; }

	}
}

// MANIPULAÇÃO DO UPLOAD
class Upload {
	// Local deve sempre ser indicado sem / no final.
	public $input; public $local; public $reg;

    public function __construct(){
        $this->local = 'default';
		$this->reg = true;
    }

	public function Send(){
		global $db, $MEUID,$ANOBASE;
		$Dir = ['default','default'];
		$ext  = pathinfo($this->input['name'], PATHINFO_EXTENSION);
		$nome = md5(date('dmY His').rand(0,99999999).date('Y-m-d H:i:s')).'.'.$ext;
		// VERIFICA PASTA
		if(in_array($this->local,['blog','casf','ead','system','eo','club','default'])){ $Dir[0] = $this->local; }
		// VERIFICA SUBPASTA
		if(in_array($ext,['png','jpg','gif','jpge'])){ $Dir[1] = 'image'; }
		elseif(in_array($ext,['doc','docx','xls','xlsx','pdf','ppt','pptx'])){ $Dir[1] = 'documents'; }
		// GERA O DIRETORIO
		$MainDir = "$ANOBASE/$Dir[0]/$Dir[1]/";
		// PROMOVE O UPLOAD
		if(move_uploaded_file($this->input['tmp_name'],__DIR__."/../files/$MainDir".$nome)){
			// SE FOR INFOMRADO PARA REGISTRAR O ARQUIVO
			$dbReg = false;
			if($this->reg){
				$Ins = $db -> prepare("INSERT INTO files (fl_user,fl_dir,fl_nome,fl_arquivo,fl_size) VALUES (?,?,?,?,?)"); dbE();
				$Ins -> bind_param("isssi",$MEUID,$MainDir,$this->input["name"],$nome,$this->input["size"]);
				$Ins -> execute();
				$dbReg = boolval($Ins->affected_rows);
			}
			if($this->reg == false OR $dbReg == true){
				// VERIFICA O REGISTRO
				if($Dir[1]=='image'){
					$Image = New Resize();
					$Image -> File = __DIR__."/../files/$MainDir".$nome;
					@$Image -> Exe();
				}
				return [
					'id'=>(isset($Ins->insert_id)?$Ins->insert_id:false),
					'fl_icon'=> FileIcon(pathinfo($this->input["name"], PATHINFO_EXTENSION)),
                    'fl_icon_color'=> FileIcon(pathinfo($this->input["name"], PATHINFO_EXTENSION),'color'),
					'fl_dir'=>$MainDir,
					'fl_nome'=>$this->input["name"],
					'fl_arquivo'=>$nome,
					'fl_data'=>date('d/m/Y'),
					'fl_size'=>Byte2($this->input['size'])
				];
			// CASO FALHE O REGISTRO NO BANCO DE  DADOS
			}else{unlink(__DIR__."/../files/$MainDir".$nome); return false;}
		}else{return false;}
	}
}

// MANIPULANDO IMAGEM
class Resize {
	public $File, $Max, $NewName, $NewPath;
	public function Exe(){
		require_once(__ROOT__.'/../vendor/autoload.php');
		# CONFIGUAÇÃO
		if(!$this->Max){$this->Max=1280;}
		$Caminho = explode('/',$this->File);
		$Ext = pathinfo(end($Caminho), PATHINFO_EXTENSION);
		if($this->NewName){ $Caminho[count($Caminho)-1] = $this->NewName.'.'.$Ext; }
		if($this->NewPath){ $TempName = $Caminho[count($Caminho)-1]; $Caminho = explode('/',$this->NewPath); $Caminho[] = $TempName; }
		$Imagem = getimagesize($this->File);
		if($Imagem[0] > $Imagem[1] AND $Imagem[0] > $this->Max){ $Imagem['L'] = 'W'; }elseif($Imagem[1] > $this->Max){ $Imagem['L'] = 'H'; }
		# CRIAÇÃO
		if(isset($Imagem['L'])){
			// ABRE E MANIPULA A IMAGEM
			$imagem = new ImageManager(array('driver' => 'imagick'));
			$img = $imagem -> make($this->File);
			$img->resize((($Imagem['L']=='W')?$this->Max:null),(($Imagem['L']=='H')?$this->Max:null), function($constraint){$constraint->aspectRatio();});
			$img->save(implode('/',$Caminho));
			return true;
		}else{return false;}
	}
}

// NOTIFICAÇÕES
class SMS {
	public $id; public $tipo; public $turma; public $user; public $lac; public $status; public $key;
	
	public function Render(){
		global $db, $MYSCT,$MEUID, $ES;
				
		$TextPrefix = "🏫 \*".$ES['esc_nome']."\*\n\n";
		$TextSufix  = "\n\n-----------------------\nQualquer Dúvida entre em contato com a escola no telefone: ".$ES['esc_fixo']." | ".$ES['esc_cel'];
		
		$Lac = $this -> lac;
		$Sta = $this -> status; $Sta = (is_numeric($Sta))?$Sta:0;
		$Key = $this -> key; $Key = ($Key)?$Key:UniqMD5();
		include __DIR__.'/SMS_Config.php';
		
		// VERIFICA O NUMERO
		if(!is_array($Lac)){ return false; } // ARRAY COM AS VARIAVEIS PARA SEREM ALTERADAS, EXCETO NOME E TELEFONE
		
		// VERIFICA SE EXISTE AO MENOS 1 DOS DOIS
		if(!is_numeric($this->turma) AND !is_numeric($this->user)){return false;}
		$GetPhone = GetPhone($this->user,$this->turma); if(!is_array($GetPhone) OR @count($GetPhone) == 0){return false;}

        // VERIFICA E PREENCHA AS LACUNAS DO TEXTO
		if(array_key_exists($this->tipo,$MainText)){
				
			// PREPARANDO O ARRAY
			foreach($GetPhone as $K0=>$V0){
		
                // VERIFICA SE A CONFIGURAÇÃO DE OCULTAR NOME COMPLETO ESTÁ ATIVA
				if(!isset($ES['ntfnome']) OR $ES['ntfnome'] == 0){
					$TempNome = explode(' ',$V0['nome']);
					if(is_array($TempNome) AND count($TempNome)){
						$V0['nome'] = $TempNome[0];
					}
				}
				
				// SUBSTITUI O NOME
				$GetPhone[$K0]['texto'] = str_replace(['{Nome}','{NOME}'],$V0['nome'],$MainText[$this->tipo]);
				// SUBSTITUI O LAC
				foreach($Lac as $KL=>$VL){ $GetPhone[$K0]['texto'] = str_replace("{{$KL}}",$VL,$GetPhone[$K0]['texto']); }
								
				// VERIFICA SE JÁ REGISTROU A NOTIFICAÇÃO DE PROVA E SE SIM EXCLUI
				$Verificar = $db -> query("SELECT sms_id FROM sms WHERE sms_secretaria = '$MYSCT' AND sms_tipo = '".$this->tipo."' AND sms_tx LIKE '%".addslashes($GetPhone[$K0]['texto'])."%' AND TIMESTAMPDIFF(DAY,DATE(sms_dref),NOW()) <= 15") -> num_rows;
				
				if($Verificar > 0){unset($GetPhone[$K0]);}else{
					// INSERE O PREFIX E SUFIX
					$GetPhone[$K0]['texto'] = $TextPrefix . '{'. $GetPhone[$K0]['texto'] . '}' . $TextSufix;
					$GetPhone[$K0]['texto'] = preg_replace("/\r|\n|\r\n/", '', nl2br($GetPhone[$K0]['texto']));
					$GetPhone[$K0]['texto'] = str_replace('<br />','\\\n',$GetPhone[$K0]['texto']);
				}
			}
			if(count($GetPhone)==0){return false;}
			
			$RT = 0;
			foreach($GetPhone as $K1=>$V1){
				foreach($V1['phone'] as $K2=>$V2){

                    $Estudante = (is_numeric($K1)) ? $K1 : 'NULL';
					$RT = $RT + intval(boolval($db -> query("INSERT INTO sms (sms_tipo,sms_status,sms_secretaria,sms_tx,sms_user,sms_estudante,sms_telefone,sms_key) VALUES ('".$this->tipo."','$Sta','$MYSCT','".$V1['texto']."','$MEUID',$Estudante,'$V2','$Key')")));

                }
			}

			return $RT;
		}else{return false;}
	}
}

// OCORRÊNCIAS
class Ocorrencia {
	private $id,$sct;
	public $DIni, $DFim;

	public function __construct(){
		global $MYSCT;
		$this->sct = $MYSCT;
	}

	public function setID($id){
		$this->id = $id;
	}

	public function EstatisticaGeral(){
		global $db, $ANOBASE, $MYSCT, $MEUTURNO;
		$Base = $db -> prepare("SELECT 
			ocorrencias.oc_dev,
			WEEK(ocorrencias.oc_dref, 1) as Sem,
			IF(WEEK(ocorrencias.oc_dref, 1) = WEEK(CURDATE(), 1),1,0) as CurentSem,
			COUNT(ocorrencias.oc_id) as qT
		FROM ocorrencias
		INNER JOIN user ON (user.user_id = ocorrencias.oc_estudante)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id AND vinc_turma.vt_sit != 1)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		WHERE YEAR(ocorrencias.oc_dref) = ? AND user.user_secretaria = ? AND turmas.turma_turno = ?
		GROUP BY oc_dev, Sem, CurentSem;");
		$Base -> bind_param("iii",$ANOBASE,$MYSCT,$MEUTURNO);
		$Base -> execute();
		$Map = [
			'total' => 0,
			'abertas' => 0,
			'semana' => 0
		];
		foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $KeyS=>$ViewS){
			$Map['total'] += $ViewS['qT'];
			$Map['abertas'] += ($ViewS['oc_dev'] == 0)?$ViewS['qT']:0;
			$Map['semana'] += ($ViewS['CurentSem'] == 1)?$ViewS['qT']:0;
		}

		return $Map;
	}

	public function ListarOcorrencias(){
		global $db, $MYSCT, $ANOBASE, $MEUTURNO;

		if(!is_date($this->DIni) OR !is_date($this->DFim)){
			return false;
		}

		$Base = $db -> prepare("SELECT
			ocorrencias.*,
			turmas.*,
			UiEst.ui_nome as EstNome,
			UiPor.ui_nome as PorNome
		FROM ocorrencias
		INNER JOIN user as UsEst ON (UsEst.user_id = ocorrencias.oc_estudante)
		INNER JOIN user as UsPor ON (UsPor.user_id = ocorrencias.oc_por)
		INNER JOIN userinfo as UiEst ON (UiEst.ui_login = UsEst.user_login)
		INNER JOIN userinfo as UiPor ON (UiPor.ui_login = UsPor.user_login)
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = ocorrencias.oc_estudante AND vinc_turma.vt_sit != 1)
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		WHERE UsEst.user_secretaria = ? AND YEAR(ocorrencias.oc_dref) = ? AND (ocorrencias.oc_dref BETWEEN ? AND ?) AND turmas.turma_turno = ?
		ORDER BY ocorrencias.oc_dref DESC");
		$Base -> bind_param("iissi",$MYSCT,$ANOBASE,$this->DIni,$this->DFim,$MEUTURNO);
		$Base -> execute();
		return $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	}

	public function OcorrenciasMapByID(){
		global $db,$ANOBASE,$MYSCT; 
		
		$EST = $this->id;  if(!is_numeric($EST)){return false;}
		$SCT = $this->sct;
		$Map = [];
	
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
		WHERE EstUs.user_secretaria = ? AND EstUs.user_id = ? AND YEAR(oc_data) = ? AND vt_sit IN (0,2) AND YEAR(vt_dref) = YEAR(oc_data)"); dbE();
		
		$Base -> bind_param("iii",$SCT,$EST,$ANOBASE);
		$Base -> execute();
		return $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
	}
}


// CANTINA
class Cantina {

	public $Metodos, $info, $user, $csv, $id;

	public function __construct(){
		$this->Metodos = $this->MetodoPagamento();
	}

	private function MetodoPagamento(){
		$Metodos = [
			1 => ['nome'=>'Dinheiro','info'=>'Pagamento em notas e moedas','icon'=>'far fa-money-bill-1'],
			2 => ['nome'=>'Pix','info'=>'Transferência instantânea entre contas','icon'=>'fab fa-pix'],
			3 => ['nome'=>'Crédito Interno','info'=>'Consumo registrado para pagamento posterior','icon'=>'fa fa-file-invoice-dollar'],
			4 => ['nome'=>'Cartão de Débito','info'=>'Débito imediato na conta bancária','icon'=>'fa fa-credit-card'],
			5 => ['nome'=>'Cartão de Crédito','info'=>'Compra com pagamento posterior ou parcelado','icon'=>'far fa-credit-card'],
			6 => ['nome'=>'Vouncher','info'=>'Vale-refeição ou similar aceito na cantina','icon'=>'fa fa-money-check-dollar'],
			6 => ['nome'=>'Transferência Bancária','info'=>'Envio de fundos entre contas bancárias','icon'=>'fa fa-money-bill-transfer'],
		];

		$Metodos = (is_file(__CONFIG__.'/cash_cantina_metodo.json')) ? json_decode(file_get_contents(__CONFIG__.'/cash_cantina_metodo.json'),true):$Metodos;
		return $Metodos;
	}

	public function Produtos($Todos=false){
		global $db, $ANOBASE, $MYSCT;
		$Todos = ($Todos == false)?2:3;
		$Base = $db -> prepare("SELECT * FROM cash_cantina_produtos WHERE csp_secretaria = ? AND csp_sit < ?");
		$Base -> bind_param("ii",$MYSCT,$Todos);
		$Base -> execute();
		return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC),'csp_id');
	}

	public function Dashboard(){
		global $db, $ANOBASE, $MYSCT;

		$Metodos = $this->MetodoPagamento();
		$Map['debitos'] = [];
		$Map['vendas'] = ['dia' => ['pessoa' => 0, 'valor' => 0], 'semana' => ['pessoa' => 0, 'valor' => 0]];
		$Map['graf'] = ['produto' => [], 'receita' => []];

		// PRODUTODS VENDIDOS AO LONGO DO MES
		$Base = $db -> prepare("SELECT DAY(csi_dref) AS dia, COUNT(csi_id) AS quantidade_vendida FROM  cash_cantina_itens
		INNER JOIN cash_cantina_produtos ON (cash_cantina_produtos.csp_id = cash_cantina_itens.csi_produto)
		WHERE  MONTH(csi_dref) = MONTH(CURRENT_DATE()) AND csp_secretaria = ? AND YEAR(csi_dref) = ?
		GROUP BY DAY(csi_dref)
		ORDER BY dia, quantidade_vendida DESC");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		$Graf = ($Base->get_result()->fetch_all(MYSQLI_ASSOC));
		foreach($Graf as $KeyG=>$ViewG){
			$Map['graf']['produto'][$ViewG['dia']] = $ViewG['quantidade_vendida'];
		}

		// VALORES PAGOS AO LONGO DOS DIAS NO MES ATUAL
		$Base = $db -> prepare("SELECT DAY(csv_dref) AS dia, ROUND(SUM(csv_valor),2) AS vendas,
			IF(csm_id = 3,0,1) as metodo
		FROM  cash_cantina_vendas
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_vendedor)
		INNER JOIN cash_cantina_metodo ON (cash_cantina_metodo.csm_id = cash_cantina_vendas.csv_metodo)
		WHERE  MONTH(csv_dref) = MONTH(CURRENT_DATE()) AND user_secretaria = ? AND YEAR(csv_dref) = ?
		GROUP BY DAY(csv_dref), metodo
		ORDER BY dia, vendas DESC");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		$Graf = ($Base->get_result()->fetch_all(MYSQLI_ASSOC));
		foreach($Graf as $KeyG=>$ViewG){
			if(!array_key_exists($ViewG['dia'], $Map['graf']['receita'])){
				$Map['graf']['receita'][$ViewG['dia']] = [0=>0, 1=>0];
			}
			$Map['graf']['receita'][$ViewG['dia']][$ViewG['metodo']] += $ViewG['vendas'];
		} foreach($Map['graf']['receita'] as $KeyG=>$ViewG){
			$Map['graf']['receita'][$KeyG][0] = number_format($Map['graf']['receita'][$KeyG][0],2,'.','');
			$Map['graf']['receita'][$KeyG][1] = number_format($Map['graf']['receita'][$KeyG][1],2,'.','');
		}


		// // BOX VENDAS
		$Base = $db -> prepare("SELECT 
			DAY(csv_dref) AS dia, 
			WEEK(csv_dref) as semana, 
			ROUND(SUM(csv_valor),2) AS vendas,
			COUNT(DISTINCT csv_user) as pessoas
		FROM  cash_cantina_vendas
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_vendedor)
		INNER JOIN cash_cantina_metodo ON (cash_cantina_metodo.csm_id = cash_cantina_vendas.csv_metodo)
		WHERE WEEK(csv_dref) = WEEK(NOW()) AND user_secretaria = ? AND YEAR(csv_dref) = ?
		GROUP BY dia, semana
		ORDER BY dia, semana, vendas DESC");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyG=>$ViewG){
			$Map['vendas']['semana']['pessoa'] += $ViewG['pessoas'];
			$Map['vendas']['semana']['valor'] += $ViewG['vendas'];

			if($ViewG['dia'] == date('d')){
				$Map['vendas']['dia']['pessoa'] = $ViewG['pessoas'];
				$Map['vendas']['dia']['valor'] = number_format($ViewG['vendas'],2,',','.');
			}
		} $Map['vendas']['semana']['valor'] = number_format($Map['vendas']['semana']['valor'],2,',','.');

		// VALORES EM ABERTO
		$Base = $db -> prepare("SELECT csv_user, SUM(csv_aberto) as valor FROM cash_cantina_vendas
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_user)
		WHERE user_secretaria = ? AND csv_aberto > 0
		GROUP BY csv_user");
		$Base -> bind_param("i",$MYSCT);
		$Base -> execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyG=>$ViewG){
			$Map['debitos'][$ViewG['csv_user']] = number_format($ViewG['valor'],2);
		}

		return $Map;
	}

	public function Devedores(){
		global $db, $MYSCT; $Map = [];

		$Base = $db -> prepare("SELECT 
			ui_nome,
			ui_pic,
			cash_cantina_vendas.*,
			cash_cantina_itens.*,
			csp_nome,
			csm_nome
		FROM cash_cantina_vendas
		INNER JOIN cash_cantina_metodo ON (cash_cantina_metodo.csm_id = cash_cantina_vendas.csv_metodo)
		LEFT JOIN cash_cantina_itens ON (cash_cantina_itens.csi_venda = cash_cantina_vendas.csv_id)
		LEFT JOIN cash_cantina_produtos ON (cash_cantina_produtos.csp_id = cash_cantina_itens.csi_produto)
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_user)
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		WHERE csv_aberto > 0 AND user.user_secretaria = ?
		ORDER BY ui_nome, csv_dref");
		$Base -> bind_param("i",$MYSCT);
		$Base -> execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyD=>$ViewD){
			if(!isset($Map[$ViewD['csv_user']])){
				$Map[$ViewD['csv_user']] = [
					'nome'=>$ViewD['ui_nome'], 
					'pic'=>$ViewD['ui_pic'], 
					'valor'=>0,
					'map'=>[],
					'debitos' => [],
					'key' => []
				];
			}
			
			$Map[$ViewD['csv_user']]['valor'] += (array_key_exists($ViewD['csv_id'],$Map[$ViewD['csv_user']]['key'])) ? 0 : $ViewD['csv_aberto'];
			@$Map[$ViewD['csv_user']]['map'][Data($ViewD['csv_dref'],2)][$ViewD['csi_produto']] += $ViewD['csi_valor'];
			$Map[$ViewD['csv_user']]['debitos'][$ViewD['csv_id']][$ViewD['csi_id']] = $filteredArray = array_filter($ViewD, function($key) {
				return strpos($key, 'csi_') === 0 || strpos($key, 'csp_') === 0;
			}, ARRAY_FILTER_USE_KEY);

			$Map[$ViewD['csv_user']]['debitos'][$ViewD['csv_id']]['total'] = number_format($ViewD['csv_valor'],2,',','.');
			$Map[$ViewD['csv_user']]['debitos'][$ViewD['csv_id']]['aberto'] = number_format($ViewD['csv_aberto'],2,',','.');

			$Map[$ViewD['csv_user']]['key'][$ViewD['csv_id']] = $ViewD['csv_aberto'];
		}

		return $Map;
	}

	public function Receita(){
		global $db, $ANOBASE, $MYSCT; 
		
		$Map = ['receita'=>[], 'metodo' => []];
		for($i=1;$i<=12;$i++){$Map['receita'][$i] = 0;}
		foreach($this->Metodos as $KeyM=>$ViewM){
			for($i=1; $i<=12; $i++){
				$Map['metodo'][$KeyM][$i] = 0;
			}		
		}
		
		// RECEITA AO LONGO DO ANO
		
		$Base = $db -> prepare("SELECT MONTH(csv_dref) as mes, SUM(csv_valor) as valor FROM cash_cantina_vendas
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_vendedor)
		WHERE user_secretaria = ? AND YEAR(csv_dref) = ?
		GROUP BY mes
		ORDER BY mes");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyB=>$ViewB){
			$Map['receita'][$ViewB['mes']] = str_replace(',','',number_format($ViewB['valor'],2));
		}

		// MÉTODOS DE PAGAMENTO AO LONGO DO ANO
		$Base = $db -> prepare("SELECT MONTH(csv_dref) as mes, csv_metodo, count(csv_metodo) as quantidade FROM cash_cantina_vendas
		INNER JOIN user ON (user.user_id = cash_cantina_vendas.csv_vendedor)
		WHERE user_secretaria = ? AND YEAR(csv_dref) = ?
		GROUP BY mes, csv_metodo");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base -> execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyB=>$ViewB){
			$Map['metodo'][$ViewB['csv_metodo']][$ViewB['mes']] = $ViewB['quantidade'];
		}
		return $Map;
	}

	public function PagarVenda(){
		global $db, $MYSCT;
		
		try {
			
			// SE FOR TUDO DO ESTUDANTE
			if(is_numeric($this->user)){
				$UpgUser = $db -> prepare("UPDATE cash_cantina_vendas SET csv_aberto = '0.00', csv_info = CONCAT(csv_info,' ',?) WHERE csv_user = ?");
				$UpgUser -> bind_param("si",$this->info,$this->user);
				return ($UpgUser->execute())?true:false;

			// SE FOR UMA NOTA UNICA
			}elseif(is_numeric($this->id)){

				$UpgVenda = $db -> prepare("UPDATE cash_cantina_vendas SET csv_aberto = '0.00', csv_info = CONCAT(csv_info,' ',?) WHERE csv_id = ? AND csv_aberto > 0 LIMIT 1");
				$UpgVenda -> bind_param("si",$this->info,$this->id);
				return ($UpgVenda->execute())?true:false;
			
			}else{return false;}

		} catch (Exception $e) { return false; }
		return false;
	}

	public function Saldo($User=false){
		global $db, $MYSCT,$ANOBASE;
		$User = (is_numeric($User))?$User:false;

		$Base = $db -> prepare("SELECT ui_nome, ui_pic, cash_cantina_saldo.* FROM cash_cantina_saldo
		INNER JOIN user ON (user.user_id = cash_cantina_saldo.css_user)
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login)
		WHERE user.user_secretaria = ? ".($User?" AND user.user_id = '$User'":'')."
		ORDER BY css_dref DESC");
		$Base->bind_param('i',$MYSCT);
		$Base->execute();

		$Map = [];
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyM=>$ViewM){
			if(!array_key_exists($ViewM['css_user'],$Map)){ $Map[$ViewM['css_user']] = ['nome' => $ViewM['ui_nome'],'pic' => $ViewM['ui_pic'], 'saldo'=>0, 'map' => []]; }
			$Map[$ViewM['css_user']]['map'][$ViewM['css_id']] = array_filter($ViewM, function($key) {
				return str_starts_with($key, "css_");
			}, ARRAY_FILTER_USE_KEY);
			$Map[$ViewM['css_user']]['saldo'] += $ViewM['css_valor'];
		}

		// COLOCA O SALDO NO FORMATO CORRETO
		foreach($Map as $KeyM=>$ViewM){
			$Map[$KeyM]['saldo'] = number_format($ViewM['saldo'],2,'.','');
		}

		// SE HOUVER UM USER INFORMADO
		if($User){
			return (array_key_exists($User,$Map)) ? $Map[$User] : false;
		}

		// MAPEIA TODOS OS DEMAIS ESTUDANTES ATIVOS DA ESCOLA QUE AINDA NÃO NENHUM REGISTRO DE SALDO
		$Base = $db -> prepare("SELECT user_id, ui_nome, ui_pic FROM user
		INNER JOIN userinfo ON (userinfo.ui_login = user.user_login) 
		INNER JOIN vinc_turma ON (vinc_turma.vt_user = user.user_id) 
		INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
		WHERE user.user_secretaria = ? AND YEAR(turmas.turma_dref) = ? AND vinc_turma.vt_sit = 0
		ORDER BY ui_nome");
		$Base -> bind_param("ii",$MYSCT,$ANOBASE);
		$Base->execute();
		foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyM=>$ViewM){
			if(!array_key_exists($ViewM['user_id'],$Map)){
				$Map[$ViewM['user_id']] = ['nome' => $ViewM['ui_nome'],'pic' => $ViewM['ui_pic'], 'saldo'=>'0.00', 'map' => []];
			}
		}

		return $Map;
	}

	public function ExibirSaldo(){
		global $db,$MYSCT;

		try {
			$Base = $db -> prepare("SELECT css_user, SUM(css_valor) as css_valor FROM cash_cantina_saldo
			INNER JOIN user ON (user.user_id = cash_cantina_saldo.css_user)
			WHERE user_secretaria = ?
			GROUP BY css_user");
			$Base -> bind_param("i",$MYSCT);
			$Base -> execute();
			$Map = [];
			foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyM=>$ViewM){
				$Map[$ViewM['css_user']] = number_format($ViewM['css_valor'],2);
			}
			return $Map;
		}  catch (Exception $e) { return false; }
	}
}