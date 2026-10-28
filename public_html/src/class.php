<?php

use Hybridauth\Provider\Google;
use PSpell\Config;

class Login
{

	function processarLoginSocial($profile, $provider)
	{
		global $db;

		$social_id = $profile->identifier;
		$email     = $profile->email;
		$nome      = $profile->displayName;
		$foto      = $profile->photoURL;

		// Define qual coluna usar: user_google_id ou user_apple_id
		$coluna_id = "user_{$provider}_id";


		// 1. Busca pelo ID do Provedor (Google ou Apple)
		$stmt = $db->prepare("SELECT user_id FROM user WHERE {$coluna_id} = ?");
		$stmt->bind_param("s", $social_id);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_assoc();

		if ($res) {
			// Opcional: Atualizar a foto caso ela tenha mudado
			$stmtUpd = $db->prepare("UPDATE user SET user_foto = ? WHERE user_id = ?");
			$stmtUpd->bind_param("si", $foto, $res['user_id']);
			$stmtUpd->execute();

			return $res['user_id'];
		}

		// 2. Busca pelo E-mail para vincular a conta
		$stmt = $db->prepare("SELECT user_id FROM user WHERE user_email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$res = $stmt->get_result()->fetch_assoc();

		if ($res) {
			// Vincula o novo provedor à conta existente
			$stmtUpd = $db->prepare("UPDATE user SET {$coluna_id} = ?, user_foto = ? WHERE user_id = ?");
			$stmtUpd->bind_param("ssi", $social_id, $foto, $res['user_id']);
			$stmtUpd->execute();
			return $res['user_id'];
		}

		// 3. Novo Cadastro
		$GoogleID = ($provider == 'google') ? $social_id : NULL;
		$AppleID = ($provider == 'apple') ? $social_id : NULL;

		$stmtIns = $db->prepare("INSERT INTO user (user_nome, user_email, user_foto, user_google_id, user_apple_id) VALUES (?, ?, ?, ?, ?)");
		$stmtIns->bind_param("sssss", $nome, $email, $foto, $GoogleID, $AppleID);
		$stmtIns->execute();
		return $db->insert_id;
	}
}

class Sorte
{
	private $Cards, $Salario;

	public function __construct()
	{
		$this->Salario();
	}

	// Gera os cartões de sorte e revés
	public function getCards()
	{
		global $db;
		$Base = $db->query("SELECT sorte_reves.*, 0.00 as sr_calc FROM sorte_reves")->fetch_all(MYSQLI_ASSOC);
		$this->Cards = ReKey($Base, 'sr_id');
		foreach ($this->Cards as $KeyC => $ViewC) {
			$this->Cards[$KeyC]['sr_calc'] = number_format((is_numeric($ViewC['sr_porcentagem']) ? ($ViewC['sr_porcentagem'] / 100 * $this->Salario) : $ViewC['sr_valor']), 2);
		}
		return $this->Cards;
	}

	// Gera uma quantidade fixa de cartões de forma randomica
	public function RandomCards($Limite = 10)
	{
		global $db;
		// Verifica o tipo de variavel passada e define um valor padrão
		$Limite = (is_numeric($Limite) ? $Limite : 10);
		// Gera os cartões de sorte e revés
		$Base = $db->query("SELECT sorte_reves.*, 0.00 as sr_calc FROM sorte_reves ORDER BY RAND() LIMIT $Limite")->fetch_all(MYSQLI_ASSOC);
		$this->Cards = ReKey($Base, 'sr_id');
		// Calcula o valor com base no salário mínimo
		foreach ($this->Cards as $KeyC => $ViewC) {
			$this->Cards[$KeyC]['sr_calc'] = number_format((is_numeric($ViewC['sr_porcentagem']) ? ($ViewC['sr_porcentagem'] / 100 * $this->Salario) : $ViewC['sr_valor']), 2);
		}
		return $this->Cards;
	}

	// Calcula o valor do sorte ou revés com base no salário mínimo
	private function Salario()
	{
		$this->Salario = new Taxas()->getSalarioMinimo();
	}
}

class Taxas
{
	private $DadosHistoricos;

	public function __construct()
	{
		$this->Update('juros');
		$this->Update('salario');
		$this->Historico();
	}

	// Consulta a API do Banco Central
	private function Api($Tipo)
	{
		switch ($Tipo) {
			case 'juros':
				$Json = @file_get_contents('https://api.bcb.gov.br/dados/serie/bcdata.sgs.4391/dados?formato=json');
				break;

			case 'salario':
				$Json = @file_get_contents('https://api.bcb.gov.br/dados/serie/bcdata.sgs.1619/dados/ultimos/1?formato=json');
				break;

			case 'cdi': // CDI Mensal série histórica
				$Json = @file_get_contents("https://ipeadata.gov.br/api/odata4/ValoresSerie(SERCODIGO='BM12_TJCDI12')");
				break;

			case 'dolar': // Dolar Diario
				$Json = @file_get_contents('https://api.bcb.gov.br/dados/serie/bcdata.sgs.1/dados/ultimos/1?formato=json');
				break;

			default:
				$Json = @file_get_contents('https://api.bcb.gov.br/dados/serie/bcdata.sgs.4391/dados?formato=json');
		}
		return (strlen($Json) == 0) ? false : $Json;
	}

	// Atualiza os arquivos JSON mensalmente
	private function Update($Tipo = 'juros')
	{
		switch ($Tipo) {
			case 'juros':
				$File = PublicHTML . '/files/taxa_juros.json';
				break;

			case 'salario':
				$File = PublicHTML . '/files/salario_minimo.json';
				break;

			case 'cdi':
				$File = PublicHTML . '/files/cdi.json';
				break;
			case 'dolar':
				$File = PublicHTML . '/files/dolar.json';
				break;
			default:
				$File = PublicHTML . '/files/taxa_juros.json';
		}

		if (!file_exists($File)) {
			file_put_contents($File, $this->Api($Tipo));
			return;
		}

		// Obtém a data de modificação do arquivo
		$dataModificacao = filemtime($File);

		// Obtém o mês e ano da modificação do arquivo
		$diaModificacao = date('d', $dataModificacao);
		$mesModificacao = date('n', $dataModificacao);
		$anoModificacao = date('Y', $dataModificacao);

		// Obtém o mês e ano atuais
		$diaAtual = date('d');
		$mesAtual = date('n');
		$anoAtual = date('Y');

		// Caso particular para dolar devido a cotação diaria
		if ($Tipo == 'dolar' and date('Y-m-d') != date('Y-m-d', $dataModificacao)) {
			file_put_contents($File, $this->Api('dolar'));
			return;
		}

		// Verifica se a modificação foi em um mês anterior ao atual
		if ($anoModificacao < $anoAtual || ($anoModificacao == $anoAtual && $mesModificacao < $mesAtual)) {
			// Atualiza o arquivo
			file_put_contents($File, $this->Api($Tipo));
		}
	}

	// Carrega os dados históricos do arquivo JSON
	public function Historico()
	{
		$this->DadosHistoricos = json_decode(file_get_contents(PublicHTML . '/files/taxa_juros.json'), true);
		$this->DadosHistoricos = is_array($this->DadosHistoricos) ? $this->DadosHistoricos : [];
		return $this->DadosHistoricos;
	}

	// Calcula a média anual das taxas de juros
	public function MediaAnual()
	{
		$Media = [];
		foreach ($this->DadosHistoricos as $Valor) {
			$Media[date('Y', strtotime($Valor['data']))][] = floatval($Valor['valor']);
		}

		foreach ($Media as $Ano => $Valor) {
			if (is_array($Valor) and count($Valor)) {
				$Media[$Ano] = number_format(array_sum($Valor) / count($Valor), 3);
			} else {
				unset($Media[$Ano]);
			}
		}

		return $Media;
	}

	// Retorna o valor atual do salário mínimo
	public function getSalarioMinimo()
	{
		$Minimo = json_decode(file_get_contents(PublicHTML . '/files/salario_minimo.json'), true)[0];
		return $Minimo['valor'];
	}

	// Retorna a média mensal do cdi
	public function getCDIMes($Data = null)
	{
		$this->Update('cdi');
		$Dados = json_decode(file_get_contents(PublicHTML . '/files/cdi.json'), true);
		$Data = (is_null($Data)) ? date('Y-m') : date('Y-m', strtotime($Data));

		// Se nao for um array valido
		if (!is_array($Dados['value']) or !count($Dados['value'])) {
			return 100;
		}

		// Inverte o array para diminuir processamento pois o ultimo item será o mês mais recente
		$Dados['value'] = array_reverse($Dados['value']);

		foreach ($Dados['value'] as $Valor) {
			if (date('Y-m', strtotime($Valor['VALDATA'])) == $Data) {
				return $Valor['VALVALOR'];
			}
		}

		return false;
	}

	public function getDolar()
	{
		$this->Update('dolar');
		$Dados = json_decode(file_get_contents(PublicHTML . '/files/dolar.json'), true);
		return (is_array($Dados) AND count($Dados)) ? reset($Dados)['valor'] : 1;
	}
}

class Profissoes
{

	public function getProfissoes($profissaoID = NULL)
	{
		global $db;
		$profissaoID = (is_numeric($profissaoID)) ? $profissaoID : NULL; // Verifica o parâmetro passado
		// Busca a profissão (todas) ou uma profissão especifica
		$Base = $db->prepare("SELECT * FROM profissoes WHERE (? IS NULL OR pf_id = ?) ORDER BY pf_nome");
		$Base->bind_param("ii", $profissaoID, $profissaoID);
		$Base->execute();
		$Base = $Base->get_result()->fetch_all(MYSQLI_ASSOC);

		// Se for uma profissao especifica, retorna apenas ela
		if (is_numeric($profissaoID) and count($Base) == 1) {
			return $Base[0];
		}

		// Se for nulo, retorna todas as profissoes
		return ReKey($Base, 'pf_id');
	}
}

class Investimentos
{

	public $conta, $tipo, $tempo, $valor, $listar, $invID;
	private $user;

	public function __construct($conta)
	{
		global $MS;

		$this->conta = $conta;
		$this->user = (isset($MS['user_id']) ? $MS['user_id'] : NULL);
	}

	// Retorna os tipos de investimentos
	public function Tipos($Tipo = false)
	{
		$Tipos = [
			1 => [
				'nome' => 'Cofrinho', // Nome
				'taxa' => 120, // Taxa de rentabilidade
				'taxaReal' => 120, // Taxa real
				'taxaPeriodo' => 'a.m.', // Em meses
				'cdi'  => 1, // Se usa ou não o cdi como parametro
				'cripto' => 0, // Se usa ou não o cdi como parametro
				'periodo' => 1, // Tempo em meses que irá rentabilizar
				'risco' => 0, // Indice de risco
				'riscoInfo' => 'Baixo', // Informação sobre o risco
				'administracao' => 0, // Taxa de administração
				'fgc' => 1, // Garantia do FGC
				'img' => 'bg_investimentos_pig.png', // Imagem do fundo
			],
			2 => [
				'nome' => 'CDB Mega Rentável',
				'taxa' => 28,
				'taxaReal' => 2,
				'taxaPeriodo' => 'a.a.',
				'cdi'  => 0,
				'cripto' => 0,
				'periodo' => 12,
				'risco' => 6,
				'riscoInfo' => 'Médio',
				'administracao' => 0,
				'fgc' => 0,
				'img' => 'bg_investimentos_cdb.png',
			],
			3 => [
				'nome' => 'LCI Isenta IR',
				'taxa' => 110,
				'taxaReal' => 27,
				5,
				'taxaPeriodo' => 'a.a.',
				'cdi'  => 1,
				'cripto' => 0,
				'periodo' => 3,
				'risco' => 4,
				'riscoInfo' => 'Médio',
				'administracao' => 0,
				'fgc' => 1,
				'img' => 'bg_investimentos_lci.png',
			],
			4 => [
				'nome' => 'Criptomoedas',
				'taxa' => 5000,
				'taxaReal' => 416,
				'taxaPeriodo' => 'a.a.',
				'cdi'  => 0,
				'cripto' => 1,
				'periodo' => 12,
				'risco' => 9,
				'riscoInfo' => 'Alto',
				'administracao' => 10,
				'fgc' => 1,
				'img' => 'bg_investimentos_cripto.png',
			],
			5 => [
				'nome' => 'Fundo Garantido Plus',
				'taxa' => 175,
				'taxaReal' => 35,
				'taxaPeriodo' => 'a.a.',
				'cdi'  => 0,
				'cripto' => 0,
				'periodo' => 60,
				'risco' => 8,
				'riscoInfo' => 'Alto',
				'administracao' => 3,
				'fgc' => 0,
				'img' => 'bg_investimentos_ultra.png',
			]
		];

		if ($Tipo === false) {
			return $Tipos;
		} else {
			if (is_numeric($Tipo) and array_key_exists($Tipo, $Tipos)) {
				return $Tipos[$Tipo];
			}
		}

		return false;
	}

	// Cria um novo investimento
	public function Novo()
	{
		global $db;
		// Se o valor que está tentando investir é maior que o saldo, retorn falso
		if ($this->valor > $_SESSION['contas'][$this->conta]['ct_saldo']) {
			return ['status' => false, 'message' => 'Saldo insuficiente para investimento!'];
		}
		// Busca informações do banco sobre o tipo de investimento pretendido
		$Tipo = $this->Tipos($this->tipo);

		// Tenta criar o investimento
		try {
			$Base = $db->prepare("INSERT INTO investimentos (
				inv_conta, inv_info, inv_taxa, inv_tipo, inv_capital, inv_saldo, inv_periodo
			) VALUES (?, ?, ?, ?, ?, ?, ?)");

			$Base->bind_param(
				"isiiddi",
				$this->conta,
				$Tipo['nome'],
				$Tipo['taxa'],
				$this->tipo,
				$this->valor,
				$this->valor,
				$this->tempo
			);
			if ((bool) $Base->execute()) {
				// Atualiza o saldo da conta e a lista de investimentos
				$Saldo = new Conta($this->conta)->setSaldo((-1) * $this->valor);
				$this->Atualizar();

				return ['status' => true, 'message' => 'Investimento realizado com sucesso!'];
			} else {
				return ['status' => false, 'message' => 'Erro ao realizar investimento!'];
			}
		} catch (Exception $e) {
			return ['status' => false, 'message' => 'Erro ao realizar investimento!'];
		}
	}

	// Lista os investimentos que a conta possui
	public function Listar($Ativos = true)
	{
		global $db;

		$Ativos = (int) $Ativos;

		$Base = $db->prepare("SELECT 
			investimentos.*, 
			(IF(inv_saldo - inv_capital  > 0, inv_saldo - inv_capital, 0)) as inv_saldo_valor  
		
		FROM investimentos 
		WHERE inv_conta = ? AND inv_ativo IN (?,1)
		ORDER BY inv_dref ASC");
		$Base->bind_param("ii", $this->conta, $Ativos);
		$Base->execute();
		$this->listar = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'inv_id');
		$Base->close();

		return $this->listar;
	}

	// Atualiza a lista de investimentos na session
	private function Atualizar()
	{
		$_SESSION['investimentos'] = $this->Listar();
	}

	// Adiciona fundos
	public function Fundos($valor)
	{
		global $db;

		// Atualiza o saldo em conta
		$Saldo = new Conta($this->conta)->setSaldo((-1) * $valor);
		// Atualiza os valores do investimento
		$Base = $db->prepare("UPDATE investimentos SET inv_capital = (inv_capital + IF(? < 0, 0, ?)), inv_saldo = (inv_saldo + ?) WHERE inv_id = ? LIMIT 1");
		$Base->bind_param("dddi", $valor, $valor, $valor, $this->invID);
		$Return = $Base->execute();
		$Base->close();
		// Atualiza informações do investimento
		$this->Atualizar();

		return (bool) $Return;
	}

	// Ativo
	public function Ativo($Ativo = 1){
		global $db;

		try {
			$Upg = $db -> prepare("UPDATE investimentos SET inv_ativo = ? WHERE inv_id = ? LIMIT 1");
			$Upg -> bind_param("ii", $Ativo, $this->invID);
			return (bool) $Upg -> execute();

		} catch (Exception $e) {
			return false;
		}
	}
}

class Usuario
{
	public $cpf, $data, $cep, $nome, $sexo, $tel, $email, $tipo;
	private $id, $findUser, $senha;

	public function __construct($id = false)
	{
		global $MS;
		$this->setID((is_numeric($id)) ? $id : ((isset($MS['user_id']) ? $MS['user_id'] : false)));
	}
	public function setID($id)
	{
		$this->id = $id;
	}
	public function setPass($Pass)
	{
		$this->senha = $Pass;
	}
	public function findUser()
	{
		global $db;

		// Promove a procura do usuario com base no id
		$Base = $db->prepare("SELECT * FROM user WHERE user_id = ? LIMIT 1");
		$Base->bind_param('i', $this->id);
		if (!$Base->execute()) {
			return false;
		}

		$Map = $Base->get_result()->fetch_assoc();
		$this->findUser = (is_array($Map) and array_key_exists('user_id', $Map)) ? $Map : false;
		return $this->findUser;
	}
	public function findEmail(): array
	{
		global $db;

		// 1) Normaliza entrada (string ou array) para array
		$raw = is_array($this->email)
			? $this->email
			: explode(',', (string) $this->email);

		// 2) Trim + validação + remove vazios/inválidos
		$Emails = array_map('trim', $raw);
		$Emails = filter_var_array($Emails, FILTER_VALIDATE_EMAIL) ?: [];
		$Emails = array_values(array_filter($Emails, fn($e) => $e !== false && $e !== null && $e !== ''));

		if (count($Emails) === 0) {
			$this->findUser = [];
			return [];
		}

		// 3) Monta placeholders dinâmicos: ?,?,?
		$placeholders = implode(',', array_fill(0, count($Emails), '?'));
		$types        = str_repeat('s', count($Emails));

		$sql = "SELECT * FROM user WHERE user_email IN ($placeholders)";

		$Base = $db->prepare($sql);
		if (!$Base) {
			// Em produção, logue em vez de mostrar
			error_log('prepare falhou: ' . $db->error);
			return [];
		}

		// 4) bind_param com spread
		$Base->bind_param($types, ...$Emails);

		if (!$Base->execute()) {
			error_log('execute falhou: ' . $Base->error);
			return [];
		}

		$Map = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
		$Base->close();

		// 5) Decide retorno
		if (count($Emails) === 1) {
			// Só 1 e-mail: retorna o único registro (ou vazio)
			$this->findUser = count($Map) === 1 ? $Map[0] : [];
			return $this->findUser;
		}

		// Vários e-mails: reindexa por user_id
		$Map = ReKey($Map, 'user_id');

		$this->findUser = $Map;
		return $this->findUser;
	}
	public function getBancoInfo()
	{
		$_SESSION['contas'] = $this->Contas();
		$_SESSION['gerente'] = $this->Gerente();
	}
	private function Contas()
	{
		global $db;
		if (!is_numeric($this->id)) {
			return [];
		}

		$SalarioMinimo = new Taxas()->getSalarioMinimo();
		$SalarioMinimo = is_numeric($SalarioMinimo) ? $SalarioMinimo : 0;

		$Base = $db->prepare("SELECT 
			contas.*, 
			profissoes.*, 
			ROUND(pf_salario * $SalarioMinimo, 2) as pf_salario_valor,
			agencia.ag_num, agencia.ag_cep, agencia.ag_dref,
			user_nome 
		FROM contas
		INNER JOIN agencia ON (agencia.ag_id = contas.ct_agencia)
		INNER JOIN user ON (user.user_id = agencia.ag_user)
		LEFT JOIN profissoes ON (profissoes.pf_id = contas.ct_profissao)
		WHERE ct_user = ? ORDER BY ct_tipo ASC, ct_dref DESC");
		$Base->bind_param("i", $this->id);
		if ($Base->execute()) {
			return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ct_id');
		}
		return [];
	}
	private function Gerente()
	{
		global $db;
		if (!is_numeric($this->id)) {
			return [];
		}
		$Base = $db->prepare("SELECT 
			ag_id,
			ag_num,
			ag_user,
			ag_info,
			ag_cep,
			ag_key,
			ag_fim,
			ag_dref,
			IF(ag_user = ?, 1, 0) as isGerente,
			GREATEST(0, DATEDIFF(ag_fim, NOW())) as ag_dias,
			COUNT(ct_id) AS total_contas
		FROM  agencia
		LEFT JOIN contas ON (agencia.ag_id = contas.ct_agencia)
		LEFT JOIN agencia_gerentes ON  (agencia_gerentes.agg_agencia = agencia.ag_id)
		WHERE  ag_user = ? OR agg_user = ?
		GROUP BY  ag_id, ag_num, ag_user, ag_cep, ag_key, ag_dref, ag_dias, ag_fim
		ORDER BY ag_num ASC");
		$Base->bind_param("iii", $this->id, $this->id, $this->id);
		if ($Base->execute()) {
			return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ag_id');
		}
		return [];
	}
	public function getGerente()
	{
		return $this->Gerente();
	}

	public function CheckCaptcha($Captcha)
	{
		global $MS;
		$Key = array_key_first($Captcha);
		$Codigo = reset($Captcha);
		if (array_key_exists($Key, $MS['captcha']) and $MS['captcha'][$Key] == $Codigo) {
			return true;
		}
		return false;
	}

	public function ContaTipo($Conta)
	{
		switch ($Conta) {
			case 0:
				return 'Poupança';
				break;
			case 1:
				return 'Corrente';
				break;
			case 3:
				return 'Jurídica';
				break;
			default:
				return 'Não Informado';
		}
	}

	public function getContas()
	{
		return $this->Contas();
	}

	public function getConvites(): array {
		global $db;
		
		if(!is_numeric($this -> id)) return [];

		$Map = [
			'subgerentes' => []
		];

		try {
			// Convite de subgerente;
			$Base = $db->prepare("SELECT 
				agencia_gerentes.*, 
				agencia.*,
				user_nome
				
			FROM agencia_gerentes 
			INNER JOIN agencia ON (agencia.ag_id = agencia_gerentes.agg_agencia)
			INNER JOIN user ON (user.user_id = agencia.ag_user)
			WHERE agg_user = ? AND agg_ativo = 2");
			$Base -> bind_param("i", $this -> id);
			$Base -> execute();
			$Map['subgerentes'] = $Base -> get_result() -> fetch_all(MYSQLI_ASSOC);
				
			return $Map;
			
		} catch (Exception $e) {
			return $Map;
		}
	}
	public function setConvite(int $id, int $status, string $local): bool {
		global $db;

		switch ($local) {
			case 'subgerentes':
				try {
					$Base = $db->prepare("UPDATE agencia_gerentes SET agg_ativo = ? WHERE agg_id = ? AND agg_user = ? LIMIT 1");
					$Base -> bind_param("iii", $status, $id, $this -> id);
					$Base -> execute();
					return true;

				} catch (Exception $e) {
					return false;	
				}
				break;
		}

		return false;
	}

	public function updateSession() {}
}
class Agencia
{
	public $numero, $id, $ConfigUpd, $key, $getContas;
	private $Agencia, $Debitos, $Config;

	public function __construct($id = false)
	{
		global $db;

		// Se um ID for fornecido, carrega a agência correspondente
		if ($id !== false) {
			$this->id = $id;
			$Base = $db->prepare("SELECT * FROM agencia WHERE ag_id = ? LIMIT 1");
			$Base->bind_param('i', $this->id);
			if ($Base->execute()) {
				$this->Agencia = $Base->get_result()->fetch_assoc();
			}
		}

		$this->Debitos = false;
	}
	// Funções relacionadas a agencia
	public function Criar($cep, $key, $info = '')
	{
		global $db, $MS;
		$key = ($key) ? BaseEL_encode(rand(999, 999999)) : NULL;
		$info = mb_strtoupper($info, 'UTF-8');

		// Primeiro pega o próximo número
		$query = $db->query("SELECT MAX(ag_num) + 1 as prox_num FROM agencia");
		$row = $query->fetch_assoc();
		$prox_num = $row['prox_num'] ?? 1; // Se for NULL (tabela vazia), começa com 1

		// Depois faz o INSERT
		$Ins = $db->prepare("INSERT INTO agencia (ag_user, ag_num, ag_cep, ag_key, ag_fim, ag_info) 
							VALUES (?, ?, ?, ?, (SELECT CURDATE() + INTERVAL 30 DAY), ?)");
		$Ins->bind_param("iisss", $MS['user_id'], $prox_num, $cep, $key, $info);

		if (!$Ins->execute()) {
			return false;
		}

		return $Ins->insert_id;
	}
	public function EncerrarAgencia()
	{
		global $db, $MS;
		$Base = $db->prepare("DELETE FROM agencia WHERE ag_id = ? AND ag_user = ? LIMIT 1");
		$Return = boolval($Base->execute([$this->id, $MS['user_id']]));
		$this->AtualizarSession();
		return $Return;
	}
	public function FecharAgencia(){
		global $db, $MS;
		$Base = $db->prepare("UPDATE agencia SET ag_fim = DATE(CURDATE()-1) WHERE ag_id = ? AND ag_user = ? LIMIT 1");
		$Return = boolval($Base->execute([$this->id, $MS['user_id']]));
		$this->AtualizarSession();
		return $Return;
	}
	public function Buscar()
	{ // Busca uma agência com base no número da agência
		global $db;
		$Base = $db->prepare("SELECT 
			ag_id,
			ag_num as numero, 
			ag_cep as cep, 
			ag_fim as fim,
			LENGTH(ag_key) as chave, 
			user_nome as gerente,
			GREATEST(0, DATEDIFF(agencia.ag_fim, NOW())) as ag_dias
		FROM agencia
		INNER JOIN user ON (user.user_id = agencia.ag_user)
		WHERE ag_num = ?");
		dbE();
		$Base->bind_param("i", $this->numero);
		if (!$Base->execute()) {
			return false;
		}
		$this->Agencia = $Base->get_result()->fetch_assoc();
		return $this->Agencia;
	}
	public function getAgencia()
	{
		return $this->Agencia;
	}
	public function checkConfigTransferencia(int $Out, int $In, bool $PorAgencia = true): bool {
		global $db;
		// Out -> Quem está pagando, In -> Quem está recebendo
		// Irá fazer uma verificação das configurações das duas agências se é permitido a transferência de valores entre elas.
		// Se PorAgencia for false, a verificação ocorre pelo número das contas.
		// Se PorAgencia for true, a verificação ocorre pelo ID das agências.

		if($PorAgencia){
			$ConfigOut = new Agencia($Out) -> getConfig();
			$ConfigIn  = new Agencia($In) -> getConfig();


		}


		return true;
	}
	public function getConfig()
	{
		global $db;
		$Config = [
			'taxas' => 'randomico',
			'sorte' => 0,
			'ciclo' => 7,
			'cicloForma' => 1,
			'profissaoMin' => false,
			'profissaoMax' => false,
			'profissaoMinSalario' => false,
			'profissaoMaxSalario' => false,
			'transferenciasTipo' => 0,
			'gerenteMaxCash' => false,
			'transferenciasAgencias' => [],
			'debitos' => [],
		];

		try {
			$Base = $db->prepare("SELECT * FROM agencia_config WHERE agc_agencia = ?");
			$Base->bind_param("i", $this->id);
			$Base->execute();
			$BaseConfig = @$Base->get_result()->fetch_assoc()['agc_config'];
			$Config = !is_null($BaseConfig) ? json_decode($BaseConfig, true) : $Config;
		} catch (Exception $e) {
		}
		return $Config;
	}
	public function setConfig($Config)
	{
		global $db;

		try {
			$Config = is_array($Config) ? json_encode($Config) : $Config;

			// Tenta fazer o update
			$Base = $db->prepare("UPDATE agencia_config SET agc_config = ?, agc_dref = NOW() WHERE agc_agencia = ? LIMIT 1");
			$Base->bind_param("si", $Config, $this->id);
			$Return = boolval($Base->execute());

			// Caso não tenha atualizado nenhuma linha, insere
			if ($db->affected_rows == 0) {
				$Base = $db->prepare("INSERT INTO agencia_config (agc_config, agc_agencia) VALUES (?,?)");
				$Base->bind_param("si", $Config, $this->id);
				$Return = boolval($Base->execute());
			}

			return $Return;
		} catch (Exception $e) {
			return false;
		}
	}
	public function setConfigCopy(array $AgenciasCopia): bool
	{
		global $db, $MS;

		$countError = 0;

		try {

			// Busca as configs das tabelas
			$Base = $db->prepare("SELECT agc_id, agc_agencia, agc_config FROM agencia_config 
			INNER JOIN agencia ON (agencia.ag_id = agencia_config.agc_agencia)
			WHERE ag_user = ?");
			$Base->bind_param("i", $MS['user_id']);
			$Base->execute();
			$Configs = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'agc_agencia');
			$Base->close();


			// Verifica se a agência que queremos copiar tem configs
			if (!array_key_exists($this->id, $Configs)) {
				return false;
			}

			// Vamos realizar a copia OU inserção
			$Ins  = $db->prepare("INSERT INTO agencia_config (agc_config, agc_agencia) VALUES (?,?)");
			$Upg  = $db->prepare("UPDATE agencia_config SET agc_config = ?, agc_dref = NOW() WHERE agc_id = ? LIMIT 1");
			$Shop = $db->prepare("INSERT INTO agencia_shop (ags_agencia, ags_key, ags_item)
				SELECT 
					? AS ags_agencia,
					CONCAT(origem.ags_key, '_', ?) AS ags_key,
					origem.ags_item
				FROM agencia_shop origem
				WHERE origem.ags_agencia = ?");


			foreach ($AgenciasCopia as $KeyA => $ViewA) {

				// Verifica se nas configuracoes obtidas no banco tem a config da agência que queremos copiar
				// Como cada agência tem apenas 1 configuracao, então basta verificar se ela existe e altera-la
				if (array_key_exists($ViewA, $Configs)) {
					// Copia
					$Upg->bind_param("si", $Configs[$this->id]['agc_config'], $ViewA);
					if (!$Upg->execute()) {
						$countError++;
					}
				} else {
					// Insere
					$Ins->bind_param("si", $Configs[$this->id]['agc_config'], $ViewA);
					if (!$Ins->execute()) {
						$countError++;
					}
				}

				// Remove todo Shopping da agência de destino da copia
				$this->closeShop($ViewA);

				// Copia Shopping
				$Shop->bind_param("iii", $ViewA, $ViewA, $this->id);
				if (!$Shop->execute()) {
					$countError++;
				}
			}

			return boolval(($countError == 0));
		} catch (Exception $e) {
			print $e;
			return false;
		}
	}
	public function Prorrogar()
	{
		global $db;
		$Base = $db->prepare("UPDATE agencia SET ag_fim = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE ag_id = ? LIMIT 1");
		$Base->bind_param('i', $this->id);
		if ($Base->execute()) {
			$_SESSION['gerente'][$this->id]['ag_dias'] += 30;
			return true;
		} else {
			return false;
		}
	}
	public function Debitos($Rand = true)
	{ // Busca os débitos configurados para a agência e, se quiser, randomiza os valores.
		$SalarioMinimo = new Taxas()->getSalarioMinimo(); // Busca o valor do salário mínimo atual
		$Config = $this->getConfig(); // Busca as configurações da agência
		foreach ($Config['debitos'] as $KeyD => $ViewD) {
			if (!is_numeric($ViewD['valor'])) {
				$Config['debitos'][$KeyD]['valor'] = number_format($SalarioMinimo * ($ViewD['porcentagem'] + (is_numeric($ViewD['variacao']) ? mt_rand(0, $ViewD['variacao']) : 0)) / 100, 2);
			}
		}
		// Se o Rand estiver ativo OU se for a primeira execução, ele carrega na variável
		if ($Rand or $this->Debitos === false) {
			$this->Debitos = $Config['debitos'];
		}

		return $this->Debitos;
	}
	// Funções relacionadas a conta
	public function CriarConta()
	{
		global $db, $MS;
		$SalarioMinimo = new Taxas()->getSalarioMinimo();

		// Verifica se a agência existe
		if (!is_numeric($this->Agencia['ag_id']) or !is_numeric($MS['user_id'])) {
			return false;
		}

		// Verifica se você já não tem uma conta na agência
		$Base = $db->query("SELECT ct_id FROM contas WHERE ct_user = '" . $MS['user_id'] . "' AND ct_agencia = '" . $this->Agencia['ag_id'] . "' LIMIT 1");
		if ($Base->num_rows > 0) {
			return $Base->fetch_assoc()['ct_id'];
		}
		$Base->close();

		// Busca o número da conta (baseado nos números que já existem)
		$ContaNumber = $db->query("SELECT (MAX(ct_conta) + 1) as conta from contas WHERE ct_agencia = '" . $this->Agencia['ag_id'] . "'")->fetch_assoc()['conta'];
		if (is_null($ContaNumber)) {
			$ContaNumber = rand(10000, 98765);
		}

		// Busca o parâmetro de valor de salario minimo e máximo configurado para agência distribuir as profissoes
		$this->Config = $this->getConfig();
		$profissaoMinSalario = is_numeric($this->Config['profissaoMinSalario']) ? $this->Config['profissaoMinSalario'] : false;
		$profissaoMaxSalario = is_numeric($this->Config['profissaoMaxSalario']) ? $this->Config['profissaoMaxSalario'] : false;

		// Sorteia uma profissão
		$ProfissaoSorteada = $db->query("SELECT pf_id FROM profissoes 
		" . (($profissaoMinSalario and $profissaoMaxSalario) ? "WHERE pf_salario BETWEEN $profissaoMinSalario AND $profissaoMaxSalario" : '') . "
		ORDER BY RAND() LIMIT 1")->fetch_assoc()['pf_id'];

		// Insere a conta nova no banco de dados
		$Base = $db->prepare("INSERT INTO contas (ct_agencia,ct_conta,ct_digito,ct_user,ct_profissao) VALUES (?,?,1,?,
			" . ((is_numeric($ProfissaoSorteada)) ? $ProfissaoSorteada : // Verifica se a profissão selecionada é um número válido
			"(SELECT pf_id FROM profissoes ORDER BY RAND() LIMIT 1)") . // Se não for, insere o sistema de sorteio dentro da propria subquery
			")");
		dbE();
		$Base->bind_param('iii', $this->Agencia['ag_id'], $ContaNumber, $MS['user_id']);
		if ($Base->execute()) {
			// Atualiza as contas
			$User = new Usuario();
			$User->setID($MS['user_id']);
			$_SESSION['contas'] = $User->getContas();
			$ContaID = $Base->insert_id;
			$Base->close();

			// Busca a profissão e o salário com base na conta criada
			$Base = $db->prepare("SELECT pf_nome, pf_salario FROM contas 
			INNER JOIN profissoes ON (profissoes.pf_id = contas.ct_profissao) 
			WHERE ct_id = ? LIMIT 1");
			$Base->execute([$ContaID]);
			$Profissao = $Base->get_result()->fetch_assoc();
			$Base->close();

			// Cria a notificação informando a profissão e o salário
			$Notificacao = new Notificacao();
			$Notificacao->Conta = $ContaID;
			$Notificacao->Info = "Profissão: {$Profissao['pf_nome']} - Salário: R$ " . (number_format($Profissao['pf_salario'] * $SalarioMinimo, 2, ',', '.'));
			$Notificacao->Nova();

			return $ContaID;
		} else {
			return false;
		}
	}
	public function EncerrarConta($contaID)
	{
		global $db;
		if (!is_numeric($contaID)) {
			return false;
		}
		$Base = $db->prepare("DELETE FROM contas WHERE ct_id = ? AND ct_agencia = ? LIMIT 1");
		return boolval($Base->execute([$contaID, $this->id]));
	}
	public function BuscarConta()
	{ // Busca uma conta;
		global $db, $MS;
		$Base = $db->prepare("SELECT contas.* FROM contas
		INNER JOIN agencia ON (agencia.ag_id = contas.ct_agencia)
		WHERE ag_num = ? AND ag_key = ? AND ct_user = ? LIMIT 1");
		$Base->bind_param("iii", $this->numero, $this->key, $MS['user_id']);
		$Base->execute();
		$Map = $Base->get_result()->fetch_assoc();
		return (is_array($Map) and array_key_exists('ct_id', $Map)) ? $Map : false;
	}
	public function getContas()
	{
		global $db;
		$Minimo = new Taxas()->getSalarioMinimo();
		$Base = $db->prepare("SELECT 
			user.user_nome,
			user.user_foto,
			user.user_email,
			contas.*,
			profissoes.*,
			ROUND(pf_salario * ?,2) as ct_salario

		FROM contas 
		INNER JOIN user ON (user.user_id = contas.ct_user)
		LEFT JOIN profissoes ON (profissoes.pf_id = contas.ct_profissao)
		WHERE ct_agencia = ?
		ORDER BY user.user_nome ASC");
		$Base->bind_param('di', $Minimo, $this->id);
		$Base->execute();
		$this->getContas = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ct_user');
		return $this->getContas;
	}
	public function findConta($Conta)
	{
		$Contas = $this->getContas();
		$Key = false;
		foreach ($Contas as $KeyC => $ViewC) {
			if (is_array($ViewC) and array_key_exists('ct_id', $ViewC) and $ViewC['ct_id'] == $Conta) {
				$Key = $KeyC;
				break;
			}
		}
		if ($Key !== false) {
			return $Contas[$Key];
		} else {
			return false;
		}
	}
	public function setProfissoes(array $Contas): bool
	{ // Atualiza as profissoes das contas
		global $db;

		try {
			// Busca a relação cliente - Profissao da agência
			$Base = $db->prepare("SELECT ct_id, ct_profissao FROM contas WHERE ct_agencia = ?");
			$Base->execute([$this->id]);
			$AgenciaContas = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ct_id');
			$Base->close();

			// Promove a verificação em relação ao banco de dados com o que foi passado via array e se for diferente altera.
			foreach ($AgenciaContas as $ViewB) {
				if (array_key_exists($ViewB['ct_id'], $Contas) and $ViewB['ct_profissao'] == $Contas[$ViewB['ct_id']]) {
					// Se a profissão for igual, remove do array sinalizando que não precisa de alteração
					unset($Contas[$ViewB['ct_id']]);
				}
			}
			// Com base no array, veremos se os itens enviados são contas válidas da agẽncia
			foreach ($Contas as $KeyB => $ViewB) {
				if (!array_key_exists($KeyB, $AgenciaContas)) {
					unset($Contas[$KeyB]);
				}
			}

			// Se ainda existir contas a serem atualizadas, atualiza
			if (count($Contas) > 0) {
				// Atualiza as contas
				$Base = $db->prepare("UPDATE contas SET ct_profissao = ? WHERE ct_id = ? AND ct_agencia = ?");
				foreach ($Contas as $KeyB => $ViewB) {
					$Base->bind_param("iii", $ViewB, $KeyB, $this->id);
					$Base->execute();
				}
				$Base->close();
			}

			return true;
		} catch (Exception $e) {
			error_log("Erro ao realizar a atualização das profissões no config da agencia: {$e->getMessage()}");
			return false;
		}
	}

	// Subgerentes
	public function getSubgerentes(){
		global $db;
		$Base = $db -> prepare("SELECT agencia_gerentes.*, user.user_nome, user.user_email FROM agencia_gerentes 
		INNER JOIN user ON (user.user_id = agencia_gerentes.agg_user)
		WHERE agg_agencia = ?");
		$Base -> bind_param("i", $this->id);
		$Base -> execute();
		$this -> Agencia['subgerentes'] = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC), 'agg_id');
		return $this -> Agencia['subgerentes'];
	}
	public function setSubgerentes(array $Subgerentes): bool {
		global $db;

		try {
			$Ins = $db -> prepare("INSERT INTO agencia_gerentes (agg_agencia, agg_user) VALUES (?, ?)");
			$Upg = $db -> prepare("UPDATE agencia_gerentes SET agg_ativo = ? WHERE agg_agencia = ? AND agg_user = ? LIMIT 1");
			$Del = $db -> prepare("DELETE FROM agencia_gerentes WHERE agg_agencia = ? AND agg_user = ? LIMIT 1");

			foreach($Subgerentes as $KeyS => $ViewS){
				
				// Inserir elemento
				if($ViewS == 'insert'){
					$Ins -> bind_param("ii", $this->id, $KeyS);
					$Ins -> execute();
				}

				// Atualizar elemento
				if(in_array($ViewS, [0,1])){
					$Upg -> bind_param("iii", $ViewS, $this->id, $KeyS);
					$Upg -> execute();
				}

				// Deletar elemento
				if($ViewS == 'delete'){
					$Del -> bind_param("ii", $this->id, $KeyS);
					$Del -> execute();
				}
			}

			return true;
		
		}catch (Exception $e) {
			return false;
		}
	}

	// Outras funções
	public function AtualizarSession()
	{ // Atualiza a sessão informando novamente contas e gerencias
		global $MS;

		$User = new Usuario();
		$User->setID($MS['user_id']);
		$Agencias = $User->getGerente();

		if (is_array($Agencias)) {
			$_SESSION['gerente'] = $Agencias;
			return true;
		}
		return true;
	}
	// Funções relacionadas ao ciclo
	public function Ciclos()
	{ // Busca as agências elegíveis para o ciclo até um máximo de 200;
		global $db;

		// Busca as agências com ciclo
		$Base = $db->prepare("SELECT * FROM agencia 
		INNER JOIN agencia_config as agc ON (agc.agc_agencia = agencia.ag_id)
		WHERE DATE(ag_fim) >= DATE(NOW()) AND DATE(agc_ciclo_data) < DATE(NOW())
		LIMIT 200");
		$Base->execute();
		// Atualiza as agências passando o id da agencia como chave do array
		$Map = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ag_id');
		// Percorre as agências para buscar as contas
		foreach ($Map as $KeyA => &$ViewA) {
			// Transforma o json de configuração em array
			$ViewA['agc_config'] = json_decode($ViewA['agc_config'], true);

			// Verifica a necessidade de executar o ciclo
			/* Aqui, vamos verificar o tempo do ciclo configurado para esta agência, normalmente diário, 
			semanal, quinzenal ou mensal (o número do item ciclo corresponde a quantidade de dias para 
			ocorrência de cada ciclo) Comparamos com o registro agc_ciclo no banco de dados e se for igual 
			ao mesmo valor, executamos o ciclo, se não, incrementamos +1 ao registro */
			$ViewA['agc_ciclo']++; // Informa o número do ciclo atual
			if ($ViewA['agc_ciclo'] % $ViewA['agc_config']['ciclo'] != 0) {
				$this->id = $KeyA; // Parametriza o ID da Agência
				$this->CicloUpdate();
				continue; // Interrompe o item atual e pula para o próximo
			}

			// Busca as contas da agência
			$this->id = $KeyA; // Parametriza o ID da Agência
			$Map[$KeyA]['contas'] = $this->getContas();
		}
		return $Map;
	}
	public function CicloUpdate()
	{ // Atualiza o ciclo da agência
		global $db;
		$Base = $db->prepare("UPDATE agencia_config SET agc_ciclo = agc_ciclo + 1, agc_ciclo_data = NOW() WHERE agc_agencia = ? LIMIT 1");
		$Base->bind_param("i", $this->id);
		return (bool) $Base->execute();
	}
	public function getIdByNumber($Number)
	{
		global $db;

		$Map = [];

		// Se for um array, filtra os numeros e associa ao array;
		if (is_array($Number)) {
			$Number = array_filter($Number, 'is_numeric');
		}

		// Se for um numero, associa ao array
		if (is_numeric($Number)) {
			$Number[] = $Number;
		}

		// Se o array estiver vazio, retorna falso
		if (!is_array($Number) or count($Number) == 0) {
			return false;
		}

		// Busca as agencias com os numeros
		$Base = $db->query("SELECT ag_id, ag_num FROM agencia WHERE ag_num IN (" . implode(',', $Number) . ")");
		foreach ($Base->fetch_all(MYSQLI_ASSOC) as $View) {
			$Map[$View['ag_id']] = $View['ag_num'];
		};
		return $Map;
	}

	// Shopping
	public function setShop(array $data): bool
	{
		$Shop = new Shop();
		$Shop->agenciaID = $this->id;
		return $Shop->setShop($data);
	}
	private function closeShop($Agencia = false): bool
	{
		global $db, $MS;

		$Agencia = $Agencia ? $Agencia : $this->id;

		if (
			!is_numeric($Agencia) or // Se nao for passado um valor valido
			!isset($MS['gerente'][$Agencia]) // Se não for dono da agência relacionada
		) {
			return false;
		}

		$Upg = $db->prepare("UPDATE agencia_shop SET ags_ativo = 0 WHERE ags_agencia = ? AND ags_ativo = 1");
		$Upg->bind_param("i", $Agencia);
		return boolval($Upg->execute());
	}

	// Graficos
	public function Chart($Chart)
	{
		global $db;
		$Map = [];

		switch ($Chart) {
			case 'saldo':

				$Map = ['contas' => $this -> getContas()];

				// Extrai apenas os saldos
				$saldos = array_column($Map['contas'], 'ct_saldo');
				// $Map['grafico']['saldos'] = $saldos;
				$minSaldo = min($saldos);
				$maxSaldo = max($saldos);
				$numeroFaixas = 10;

				$ranger = ceil(($maxSaldo - $minSaldo) / $numeroFaixas);
				$potencia = ($ranger == 0) ? 0 : (floor(log10(abs($ranger))));
				$rangerFaixa = 5 * 10**$potencia;

				// Encontra o múltiplo de 5000 mais próximo para baixo (negativos) e para cima (positivos)
				$minFaixa = floor($minSaldo / $rangerFaixa) * $rangerFaixa;
				$maxFaixa = ceil($maxSaldo / $rangerFaixa) * $rangerFaixa;

				// Garante que o zero esteja incluso
				if ($minFaixa > 0) $minFaixa = 0;
				if ($maxFaixa < 0) $maxFaixa = 0;
				$numeroFaixas = (($maxFaixa - $minFaixa) / $rangerFaixa);

				for ($i = 0; $i < $numeroFaixas; $i++) {
					$inicio = $minFaixa + ($i * $rangerFaixa);
					$fim = $inicio + $rangerFaixa;
					
					$inicioFormatado = number_format($inicio, 0, ',', '.');
					$fimFormatado = number_format($fim, 0, ',', '.');
					
					$Map['grafico']['label'][] = "{$inicioFormatado} a {$fimFormatado}";
				}

				// Conta os saldos em cada faixa
				foreach ($saldos as $saldoV) {
					// Calcula o índice baseado no intervalo a partir do mínimo
					$indice = floor(($saldoV - $minFaixa) / $rangerFaixa);
					// Garante que o índice não ultrapasse os limites
					$indice = min(max($indice, 0), $numeroFaixas - 1);

					if(!isset($Map['grafico']['values'][$indice])){ $Map['grafico']['values'][$indice] = 0; }
					$Map['grafico']['values'][$indice]++;
				}

				break;

			case 'transacoes':
				// Busca as o ranking de transacoes de clientes contabilizando o total de transações por cliente
				$Base = $db->prepare("SELECT user_id, user_nome, 
					COUNT(*) as transacoes,
					SUM(tr_valor) as valor_total
				
				FROM contas
				INNER JOIN agencia ON (agencia.ag_id = contas.ct_agencia)
				INNER JOIN user ON (user.user_id = contas.ct_user)
				INNER JOIN transferencias ON (transferencias.tr_origem = contas.ct_id)
				WHERE ag_id = ?
				GROUP BY user_id, user_nome
				ORDER BY transacoes DESC");
				$Base->execute([$this->id]);
				$Map['contas'] = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
				$Base->close();

				// Busca as o ranking de transacoes de agencias contabilizanod o total de transações ao longo dos tempos
				$Base = $db->prepare("SELECT 
					YEAR(ext_dref) AS ano,
					WEEK(ext_dref) AS semana,
					COUNT(*) AS transacoes
				FROM extrato
				INNER JOIN contas ON (contas.ct_id = extrato.ext_conta)
				WHERE contas.ct_agencia = ?
				GROUP BY ano, semana
				ORDER BY ano DESC, semana ASC;");
				$Base->execute([$this->id]);
				$Map['agencia'] = $Base->get_result()->fetch_all(MYSQLI_ASSOC);

				break;

			case 'shop':

				$Base = $db->prepare("SELECT 
					contas_shop.cts_conta,
					user.user_nome,
					SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(contas_shop.cts_item, '$.stock')) AS UNSIGNED)) AS total_stock,
					SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(contas_shop.cts_item, '$.stock')) AS UNSIGNED) * 
						CAST(JSON_UNQUOTE(JSON_EXTRACT(contas_shop.cts_item, '$.price_unity')) AS DECIMAL(10,2))) AS valor_total
				FROM contas_shop
				INNER JOIN contas ON (contas.ct_id = contas_shop.cts_conta)
				INNER JOIN user ON (user.user_id = contas.ct_user)
				WHERE contas.ct_agencia = ?
				GROUP BY contas_shop.cts_conta, user.user_nome
				ORDER BY total_stock DESC;");
				$Base->bind_param("i", $this->id);
				$Base->execute();
				$Map['contas'] = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
				$Base->close();
				break;

			case 'cartao':

				// Busca todos os cartões
				$Base = $db -> prepare("SELECT 
					contas.ct_id,
					user.user_nome,
					cartoes.card_id
				FROM contas
				INNER JOIN cartoes ON (cartoes.card_conta = contas.ct_id)
				INNER JOIN user ON (user.user_id = contas.ct_user)
				WHERE contas.ct_agencia = ?");
				$Base -> bind_param("i", $this->id);
				$Base -> execute();

				foreach($Base -> get_result()->fetch_all(MYSQLI_ASSOC) as $View){
					// Cria o elemento de vetor
					if(!isset($Map['cartao'][$View['ct_id']])){
						$Map['cartao'][$View['ct_id']] = [
							'nome' => $View['user_nome'],
							'card' => [],
							'compras' => 0,
							'parcelas' => 0,
							'total' => 0
						];
					}

					// Processa o resultado
					$Map['cartao'][$View['ct_id']]['card'][] = $View['card_id'];
				}
				$Base -> close();

				$Base = $db -> prepare("SELECT contas.ct_id, cartoes_fatura.* FROM cartoes_fatura  
				INNER JOIN cartoes ON (cartoes.card_id = cartoes_fatura.ctf_cartao)
				INNER JOIN contas ON (contas.ct_id = cartoes.card_conta)
				WHERE contas.ct_agencia = ?");
				$Base -> bind_param("i", $this->id);
				$Base -> execute();

				foreach($Base -> get_result()->fetch_all(MYSQLI_ASSOC) as $View){

					$View['ctf_fatura'] = json_decode($View['ctf_fatura'], true); // Transofmra em array

					if(array_key_exists($View['ct_id'], $Map['cartao'])){ // Verifica se o dono do cartão foi encontrado
						$Map['cartao'][$View['ct_id']]['compras'] += (isset($View['ctf_fatura']['itens']) ? count($View['ctf_fatura']['itens']) : 0);  // Incrementa a quantidade de compras
						$Map['cartao'][$View['ct_id']]['total'] += $View['ctf_valor']; // Incrementa o total
						$Map['cartao'][$View['ct_id']]['parcelas'] += (isset($View['ctf_fatura']['parcelas']) ? count(array_filter($View['ctf_fatura']['parcelas'], function($item) { return $item['parcelaAtual'] == 1; })) : 0); // Incrementa a quantidade de parcelas
					}
				}

				$Base -> close();
				break;

			case 'investimento':
				$Map['investimento'] = [];
				$Map['investimentoChart'] = [];


				$Base = $db -> prepare("SELECT
					contas.ct_id,
					user_nome,
					investimentos.*
				FROM contas
				INNER JOIN user ON (user.user_id = contas.ct_user)
				INNER JOIN investimentos  ON (investimentos.inv_conta = contas.ct_id)
				WHERE contas.ct_agencia = ?;");
				$Base -> bind_param("i", $this->id);
				$Base -> execute();

				foreach($Base -> get_result()->fetch_all(MYSQLI_ASSOC) as $View){

					if(!isset($Map['investimento'][$View['ct_id']])){
						$Map['investimento'][$View['ct_id']] = [
							'nome' => $View['user_nome'],
							'quantidade' => 0,
							'total' => 0
						];
					}

					// Processa o resultado
					$Map['investimento'][$View['ct_id']]['quantidade']++; // Incrementa a quantidade de investimentos
					$Map['investimento'][$View['ct_id']]['total'] += $View['inv_capital']; // Incrementa o total

					// Grafico
					if(!isset($Map['investimentoChart'][$View['inv_tipo']])){ 
						$Map['investimentoChart'][$View['inv_tipo']] = [
							'nome' => $View['inv_info'],
							'quantidade' => 0
						];
					}
					$Map['investimentoChart'][$View['inv_tipo']]['quantidade']++;
				}
				$Base -> close();
				break;


			default:
				return [];
		}

		return $Map;
	}

	// Cartoes
	public function getCartaoByNumero(string $Numero): array {
		global $db;

		try {
			$Base = $db -> prepare("SELECT ct_user as user_id, cartoes.* FROM cartoes 
			INNER JOIN contas ON (contas.ct_id = cartoes.card_conta)
			INNER JOIN agencia ON (agencia.ag_id = contas.ct_agencia)
			WHERE card_numero = ? AND ag_id = ? LIMIT 1");

			$Base -> bind_param("si", $Numero, $this->id);
			$Base -> execute();
			
			return $Base -> get_result()->fetch_assoc();

		}catch (Exception $e) {
			return [];
		}
	}
}

class Conta
{
	public $contaID, $cardID, $agenciaID, $numero, $agencia, $user;
	private $Conta, $Pagamentos;

	public function __construct($contaID = null)
	{
		$this->contaID = $contaID;
		$this->Conta = [
			'ui_nome' => NULL,
			'cartoes' => [],
			'investimentos' => [],
			'notificacoes' => [],
		];
	}

	// Funções relacionadas a contas
	public function findConta()
	{
		global $db;

		$Minimo = new Taxas()->getSalarioMinimo();

		try {
			$Base = $db->prepare("SELECT 
				user_nome, 
				user_foto, 
				user_email, 
				pf_nome, 
				pf_salario, 
				contas.*, 
				agencia.* 
			FROM contas
            INNER JOIN agencia ON (agencia.ag_id = contas.ct_agencia)
            INNER JOIN user ON (user.user_id = contas.ct_user)
			INNER JOIN profissoes ON (profissoes.pf_id = contas.ct_profissao)
            WHERE ct_id = ? OR (ct_agencia = ? AND ct_conta = ?) OR (ag_num = ? AND ct_conta = ?) LIMIT 1");
			$Base->bind_param("iiiii", $this->contaID, $this->agenciaID, $this->numero, $this->agencia, $this->numero);
			$Base->execute();
			$this->Conta = array_merge($this->Conta, $Base->get_result()->fetch_assoc());

			// Associa o salario minimo a profissao
			$this->Conta['pf_salario'] = number_format($this->Conta['pf_salario'] * $Minimo, 2, '.', '');

			// Busca os cartoes da Conta
			$this->Conta['cartoes'] = $this->Cartoes();
			// Busca os investimentos 
			$this->Conta['investimentos'] = new Investimentos($this->contaID)->Listar();
			// Busca as informações de shop
			$this->Conta['shop']['itens'] = $this->getMyShopItens();
			$this->Conta['shop']['total'] = count($this->Conta['shop']['itens']);
			$this->Conta['shop']['valor'] = array_reduce($this->Conta['shop']['itens'], function ($carry, $item) {
				return $carry + ($item['cts_item']['stock'] * $item['cts_item']['price_unity']);
			}, 0);

			$Base->close();
			return $this->Conta;
		} catch (Throwable $e) {
			return false;
		}
	}
	private function TransferenciaTipo($Tipo, $Default = 'numerico')
	{
		// As modalidades de transferências são:
		// 0 -> Transferência
		// 1 -> PIX

		// Retorna o tipo de transferência em formato numérico para registro no banco de dados
		if ($Default == 'numerico') {
			switch ($Tipo) {
				case 0:
					return 0;
				case 'transferencia':
					return 0;
				case 1:
					return 1;
				case 'pix':
					return 1;
				default:
					return 0;
			}
		}

		// Retorna o tipo de transferência em formato textual
		if ($Default == 'texto') {
			switch ($Tipo) {
				case 0:
					return 'transferencia';
				case 'transferencia':
					return 'transferencia';
				case 1:
					return 'pix';
				case 'pix':
					return 'pix';
				default:
					return 'transferencia';
			}
		}

		// Se nenhuma das opções acima for verdadeira, retorna falso
		return false;
	}
	public function setExtrato(string $Info, float $Valor, string $Chave = ''): bool
	{
		global $db;

		$Valor = numHTML($Valor);
		$Info = strip_tags($Info);

		try {
			// Insere o elemento no extrato da conta.
			$Base = $db->prepare("INSERT INTO extrato (ext_conta, ext_info, ext_valor, ext_chave) VALUES (?, ?, ?, ?)");
			$Base->bind_param("isds", $this->contaID, $Info, $Valor, $Chave);
			$Base->execute();
			$Base->close();

			$this->setSaldo($Valor); // Atualiza o valor do saldo da conta
			return true;
		} catch (Throwable $e) {
			error_log("Erro ao registrar extrato: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	public function getExtrato($Maximo = 5)
	{
		global $db;
		try {

			// Verifica o parametro passado
			$Maximo = (is_numeric($Maximo)) ? $Maximo : ($Maximo === true ? PHP_INT_MAX : 5);

			// Busca o extrato
			$Base = $db->prepare("SELECT * FROM extrato WHERE ext_conta = ? ORDER BY ext_id DESC LIMIT ?");
			$Base->bind_param("ii", $this->contaID, $Maximo);
			$Base->execute();
			$Extrato = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
			$Base->close();

			// Busca transferências
			$Transferencias = $this->getTransferencia();
			foreach ($Transferencias as $KeyT => $ViewT) {
				$Extrato["T$KeyT"] = [
					'ext_id' => "T$KeyT",
					'ext_conta' => $ViewT['tr_origem'],
					'ext_info' => ucfirst($ViewT['tr_tipo_texto']) . " " . (($ViewT['tr_origem'] == $this->contaID) ? "para {$ViewT['nomeDestino']}" : "de {$ViewT['nomeOrigem']}"),
					'ext_valor' => (($ViewT['tr_origem'] == $this->contaID) ? '-' . $ViewT['tr_valor'] : $ViewT['tr_valor']),
					'ext_dref' => $ViewT['tr_dref']
				];
			}

			// Ordena o extrato baseado em valores de tempo descrescentes
			uasort($Extrato, function ($a, $b) {
				return ($a['ext_dref'] <= $b['ext_dref']) ? 1 : -1;
			});

			return array_slice($Extrato, 0, $Maximo);
		} catch (Throwable $e) {
			error_log("Erro ao buscar extrato: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	public function getExtratoChave(string $Chave): bool {
		global $db;

		try {
			$Base = $db -> prepare("SELECT ext_id FROM extrato WHERE ext_conta = ? AND ext_chave = ? LIMIT 1");
			$Base -> bind_param("is", $this->contaID, $Chave);
			$Base -> execute();
			return boolval($Base -> get_result() -> num_rows);
			
		} catch (Throwable $e) {
			error_log("Erro ao buscar extrato: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	public function getExtratoCiclo()
	{

		$Map = [];

		$Extrato = $this->getExtrato(true); // Busca o extrato completo
		if (!is_array($Extrato) or count($Extrato) == 0) return [];

		$Agencia = new Agencia(); // Busca as configurações da agência
		$Agencia->id = $this->Conta['ct_agencia']; // Associa a agência
		$Ciclo  = $Agencia->getConfig(); // Busca as configurações
		$Ciclo  = (is_array($Ciclo) and array_key_exists('ciclo', $Ciclo)) ? $Ciclo['ciclo'] : 30; // Associa o ciclo
		$CicloInt = 1;

		$MenorData = date('Y-m-d', strtotime(min(array_column($Extrato, 'ext_dref')))); // Busca a menor data do extrato

		foreach ($Extrato as $ViewE) {
			if (Data($ViewE['ext_dref'], 2) <= $MenorData) {
				$Map[$CicloInt][] = $ViewE;
			} else {
				$CicloInt++;
				$MenorData = date('Y-m-d', strtotime("$MenorData +$Ciclo days"));
			}
		}

		$Map = array_values($Map);
		return $Map;
	}
	public function setAtivo()
	{
		global $db;
		try {
			$Base = $db->prepare("UPDATE contas SET ct_ativo = !ct_ativo WHERE ct_id = ? AND ct_agencia = ? LIMIT 1");
			$Base->bind_param("ii", $this->contaID, $this->agenciaID);
			$Return = boolval($Base->execute());
			$Base->close();
			return $Return;
		} catch (Throwable $e) {
			return false;
		}
	}

	// Operacoes com saldo
	public function updSaldo()
	{
		global $db;
		try {
			// Procura o saldo na conta e se encontrar, atualiza a session com o novo saldo.
			$Base = $db->prepare("SELECT ct_saldo FROM contas WHERE ct_id = ?");
			$Base->bind_param("i", $this->Conta['ct_id']);
			$Base->execute();
			$Saldo = $Base->get_result()->fetch_assoc();
			$Base->close();
			if (isset($Saldo['ct_saldo'], $_SESSION['contas'][$this->contaID]['ct_saldo'])) {
				$_SESSION['contas'][$this->contaID]['ct_saldo'] = $Saldo['ct_saldo'];
				return true;
			}
			return false;
		} catch (Throwable $e) {
			return false;
		}
	}
	public function setSaldo($valor)
	{
		global $db;
		$valor = floatval($valor);

		try {
			// Procura o saldo na conta e se encontrar, atualiza a session com o novo saldo.
			$Base = $db->prepare("UPDATE contas SET ct_saldo = (ct_saldo + ?) WHERE ct_id = ?");
			$Base->bind_param("di", $valor, $this->contaID);
			$Base->execute();
			$Base->close();
			$this->updSaldo(); // Atualiza o saldo na session
			return true;
		} catch (Throwable $e) {
			return false;
		}
	}
	public function getSaldo(): float {
		
		if(!isset($this->Conta['ct_saldo'])){
			$this -> findConta();
		}
		
		return $this->Conta['ct_saldo'];
	}

	// Realiza uma transferência de valor entre contas
	public function Transferencia($contaDestinoID, $valor, $tipo = 0)
	{
		global $db, $MS;

		// Verifica se a conta de origem foi informada.
		if (!is_numeric($this->contaID) and $this->contaID > 0) {
			return false;
		}

		// Verifica se a conta de origem é diferente da conta de destino;
		if ($this->contaID == $contaDestinoID) {
			return false;
		}

		// Define o tipo de transferência
		$tipo = $this->TransferenciaTipo($tipo);

		$valor = numHTML($valor); // Formata o valor
		if ($valor <= 0) {
			return false;
		} // Se o valor for negativo ou zero, retorna falso

		// 1. Inicia transação para garantir atomicidade
		$db->begin_transaction();

		try {

			// 2. Retira da conta de origem (this->contaID)
			$menosSaldo = $db->prepare("UPDATE contas SET ct_saldo = (ct_saldo - ?) WHERE ct_id = ? LIMIT 1");
			$menosSaldo->bind_param('di', $valor, $this->contaID);

			if (!$menosSaldo->execute()) {
				throw new Exception("Erro ao debitar conta origem");
			}

			$menosSaldo->close();

			// 3. Adiciona na conta de destino
			$maisSaldo = $db->prepare("UPDATE contas SET ct_saldo = (ct_saldo + ?) WHERE ct_id = ? LIMIT 1");
			$maisSaldo->bind_param('di', $valor, $contaDestinoID);

			if (!$maisSaldo->execute()) {
				throw new Exception("Erro ao creditar conta destino");
			}

			$maisSaldo->close();

			// Commit da transação
			$db->commit();

			// Realiza o registro da transferência
			if (!$this->TransferenciaRegistrar($tipo, $contaDestinoID, $valor)) {
				throw new Exception("Erro ao registrar transferência");
			}

			// 4. Registra a notificação para o recebedor
			$Notificacao = new Notificacao();
			$Notificacao->Conta = $contaDestinoID;
			$Notificacao->Info = $MS['user_nome'] . " lhe enviou R$ " . numBR($valor) . ".";
			$Notificacao->Nova();

			return true;
		} catch (Exception $e) {
			// Rollback em caso de erro
			$db->rollback();
			return false;
		}
	}
	public function getTransferencia($tipo = false, $limite = 5)
	{
		global $db;
		try {

			// Busca informação de Transferências, informando origem, destino e tipo nonimal
			$Transferencias = $db->prepare("SELECT
				transferencias.*,
				userOrigem.user_nome as nomeOrigem,
				userDestino.user_nome as nomeDestino
			FROM transferencias 
			INNER JOIN contas as contaOrigem ON (contaOrigem.ct_id = transferencias.tr_origem)
			INNER JOIN contas as contaDestino ON (contaDestino.ct_id = transferencias.tr_destino)
			INNER JOIN user as userOrigem ON (userOrigem.user_id = contaOrigem.ct_user)
			INNER JOIN user as userDestino ON (userDestino.user_id = contaDestino.ct_user)
			WHERE tr_origem = ? OR tr_destino = ? ORDER BY tr_id DESC LIMIT ?");
			$Transferencias->bind_param('iii', $this->contaID, $this->contaID, $limite);
			$Transferencias->execute();
			$Return = ReKey($Transferencias->get_result()->fetch_all(MYSQLI_ASSOC), 'tr_id');
			foreach ($Return as $KeyR => $ViewR) {
				$Return[$KeyR]['tr_tipo_texto'] = $this->TransferenciaTipo($ViewR['tr_tipo'], 'texto');
			}
			return $Return;
		} catch (Exception $e) {
			return false;
		}
	}
	public function TransferenciaRegistrar($Tipo, $contaDestinoID, $Valor)
	{ // Registra a informação da transferência no banco de dados.
		global $db;
		$regTransferencia = $db->prepare("INSERT INTO transferencias (tr_origem, tr_destino, tr_valor, tr_tipo) VALUES (?, ?, ?, ?)");
		$regTransferencia->bind_param("iiid", $this->contaID, $contaDestinoID, $Valor, $Tipo);
		return (bool) $regTransferencia->execute();
	}

	// Funções relacionadas aos cartões
	public function CartoesTipo($Tipo = false)
	{
		$Cartoes = [
			[ // Debito
				'id' => 0,
				'tipo' => 'Débito',
				'color' => 'purple',
				'anuidade' => 0, // Reais
				'anuidadeDesconto' => 0, // Meses
				'anuidadeIsento' => 0,
				'jurosRotativo' => 0, // % ao período
				'jurosSaque' => 0, // % ao periodo
				'beneficios' => [
					'Sem limite de saques.',
					'Cartão virtual',
				],
				'limite' => 0
			],
			[ // Classic
				'id' => 1,
				'tipo' => 'Classic',
				'color' => 'primary',
				'anuidade' => 120, // Reais
				'anuidadeDesconto' => 12, // Meses
				'anuidadeIsento' => 0,
				'jurosRotativo' => 12.5, // % ao período
				'jurosSaque' => 6.5, // % ao periodo
				'beneficios' => [
					'Seguro de proteção de preço',
					'App para controle de gastos',
					'Atendimento 24h'
				],
				'limite' => 1
			],
			[ // Gold
				'id' => 2,
				'tipo' => 'Gold',
				'color' => 'warning',
				'anuidade' => 240, // Reais por ano
				'anuidadeDesconto' => 0, // Meses
				'anuidadeIsento' => 2000, // Isento de anuidade (mensal) se gastar o valor
				'jurosRotativo' => 11.2, // % ao período
				'jurosSaque' => 5.8, // % ao periodo
				'beneficios' => [
					'Seguro de viagem nacional',
					'Acesso a salas VIP em aeroportos',
					'Programa de milhas 1:1',
					'Assistência residencial'
				],
				'limite' => 2.5
			],
			[ // Platinum
				'id' => 3,
				'tipo' => 'Platinum',
				'color' => 'dark',
				'anuidade' => 480, // Reais por ano
				'anuidadeDesconto' => 0, // Meses
				'anuidadeIsento' => 5000, // Isento de anuidade (mensal) se gastar o valor
				'jurosRotativo' => 9.8, // % ao período
				'jurosSaque' => 4.5, // % ao periodo
				'beneficios' => [
					'Seguro de viagem internacional',
					'Acesso ilimitado a salas VIP worldwide',
					'Programa de milhas 1:1,5',
					'Concierge personalizado 24/7',
					'Seguro de celular e eletrônicos'
				],
				'limite' => 4
			]
		];

		// Caso procure por um ID
		if (is_numeric($Tipo)) {
			$Cartoes = ReKey($Cartoes, 'id');
		}

		return $Tipo === false ? $Cartoes : $Cartoes[$Tipo];
	}
	private function CartoesGerarNumero(): array
	{
		$bin   = '4539'; // Visa Like
		$corpo = '';

		// Gera os primeiros 11 dígitos aleatórios (BIN + 11 = 15 dígitos)
		for ($i = 0; $i < 11; $i++) {
			$corpo .= random_int(0, 9);
		}

		$numeroParcial = $bin . $corpo;

		// Calcula o dígito verificador (Luhn)
		$digito = $this->calcularLuhn($numeroParcial);

		$numero = $numeroParcial . $digito;
		$last4  = substr($numero, -4);

		return [
			'token'  => bin2hex(random_bytes(16)),
			'numero' => $numero,
			'last4'  => $last4,
		];
	}
	private function calcularLuhn(string $numero): int
	{
		$soma   = 0;
		$digitos = str_split(strrev($numero)); // inverte para processar da direita

		foreach ($digitos as $i => $digito) {
			$valor = (int) $digito;

			// Multiplica por 2 os dígitos em posições ímpares (0-indexado)
			if ($i % 2 === 0) {
				$valor *= 2;
				if ($valor > 9) {
					$valor -= 9; // equivalente a somar os dois dígitos
				}
			}

			$soma += $valor;
		}

		return (10 - ($soma % 10)) % 10;
	}
	public function Cartoes()
	{ // Busca os cartoes da Conta
		global $db;
		$Base = $db->prepare("SELECT cartoes.*, (
			SELECT COALESCE(SUM(ctf_valor - ctf_pagamento), 0) as limite_usado FROM cartoes_fatura WHERE ctf_cartao = cartoes.card_id
		) as card_limite_usado FROM cartoes WHERE card_conta = ?");
		$Base->bind_param("i", $this->contaID);
		$Base->execute();
		$Cartoes = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'card_id');
		foreach ($Cartoes as $KeyC => &$ViewC) {
			$ViewC['card_limite_livre'] = $ViewC['card_limite'] - $ViewC['card_limite_usado']; // Associa o valor do limite livre
			$ViewC['card_tipo_nome'] = $this->CartoesTipo($ViewC['card_tipo'])['tipo']; // Associa o nome do cartão
			$ViewC['card_tipo_color'] = $this->CartoesTipo($ViewC['card_tipo'])['color']; // Associa a cor do cartão
			$ViewC['card_tipo_rotativo'] = $this->CartoesTipo($ViewC['card_tipo'])['jurosRotativo']; // Associa o juros rotativo
			$ViewC['card_fatura_vencimento'] = '15/' . ((date('d') >= 1 and date('d') <= 15) ? date('m') : date('m', strtotime('+1 month'))); // Associa o vencimento da fatura
			$ViewC['card_fatura_valor'] = number_format($ViewC['card_limite_usado'], 2, '.', ''); // Associa o valor da fatura

			// Remove o cartão que não tem fatura e não está ativo
			if ($Cartoes[$KeyC]['card_ativo'] == 0 and $Cartoes[$KeyC]['card_fatura_valor'] == 0) {
				unset($Cartoes[$KeyC]);
			}
		}
		return $Cartoes;
	}
	public function CartaoRender(array $Cartao): string {

		// Altera a forma de exibição dos números e do nome
		$Cartao['card_numero'] = chunk_split($Cartao['card_numero'], 4, ' ');
		$Cartao['card_titular'] = AbreviarNome(mb_strtoupper($Cartao['card_titular'], 'UTF-8'));
		$Cartao['card_validade'] = date('m/y', strtotime($Cartao['card_validade']));
		$Cartao['card_tipo_color'] = (!isset($Cartao['card_tipo_color']) or $Cartao['card_tipo_color'] == '') ? 'dark' : $Cartao['card_tipo_color'];

		$Html = file_get_contents(Views . '/html/cartao_frente.html');
		$Html = str_replace(
			array_map(fn($key) => '{' . $key . '}', array_keys($Cartao)),
			array_values($Cartao),
			$Html
		);

		return $Html;
	}
	public function getCartao()
	{
		$Cartoes = $this->Cartoes();
		if (!isset($Cartoes[$this->cardID])) return false;
		return $Cartoes[$this->cardID];
	}
	public function getCartaoDebito(bool $Criar = false): array {
		// Criar caso não exista: false;

		$Cartoes =  array_filter(
            $this -> Cartoes(),
            function($filterCard){
                return $filterCard['card_tipo'] == 0;
            }
        );

		if (count($Cartoes)) {
			return reset($Cartoes);
		}

		if($Criar) {
			if($this->CartoesNovo(0)){
				return $this->getCartaoDebito(false);

			}
		}

		return [];
	}
	public function CartoesNovo($Tipo)
	{ // Associa um novo cartão a conta
		global $db, $MS;

		$salario = (isset($MS['contas'][$this->contaID]['pf_salario'])) ? $MS['contas'][$this->contaID]['pf_salario'] : 0; // Busca informação do salário associado a conta. Essa informação ajudará no limite.
		$salario = floatval(str_replace('.', '', $salario)); // Retira o ponto

		$CartoesTipoInfo = $this->CartoesTipo($Tipo); // Busca informações referente ao tipo de cartão escolhido
		if (!$CartoesTipoInfo) return false; // Se o tipo de cartão for inválido, retorna falso

		// Verifica se o cartão de débito ja existe
		if($Tipo == 0){
			$CartaoDebito = $this -> getCartaoDebito(false);
			if($CartaoDebito){
				return false;
			}
		}

		$cartao = $this->CartoesGerarNumero(); // Gera um novo cartão
		$validade = date('Y-m-d', strtotime('+5 years')); // Gera a data de validade do cartão
		$limite = ($salario ? $salario * $CartoesTipoInfo['limite'] : 1000); // Busca o limite do cartão
		$limite = ($Tipo == 0 ? 0 : $limite); // Se for um cartão de débito, zera o limite.

		$Base = $db->prepare("INSERT INTO cartoes (card_conta,card_tipo,card_numero,card_validade,card_limite,card_limite_livre,card_token) VALUES (?,?,?,?,?,?,?)");
		$Base->bind_param("iissdds", $this->contaID, $Tipo, $cartao['numero'], $validade, $limite, $limite, $cartao['token']);
		return (bool) $Base->execute();
	}
	// Certifique-se que este método existe (ele já está no seu código, mas vamos garantir)
	private function CartoesFaturaMap($Livre = 0)
	{
		return [
			'limite_livre' => $Livre,
			'limite_livre_novo' => $Livre,
			'fatura' => 0,
			'fatura_paga' => 0,
			'itens' => [],
			'parcelas' => []
		];
	}
	public function CartoesFatura($DecodeJson = false)
	{ // Busca a fatura
		global $db;
		$Base = $db->prepare("SELECT * FROM cartoes_fatura WHERE ctf_cartao = ? ORDER BY ctf_id DESC");
		$Base->bind_param("i", $this->cardID);
		$Base->execute();

		if ($DecodeJson === 'open') {
			$Open = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
			// procura e remove os elementos que forem pagos totalmente
			foreach ($Open as $KeyF => &$ViewF) {
				if ($ViewF['ctf_valor'] == $ViewF['ctf_pagamento']) {
					unset($Open[$KeyF]);
				} else {
					// Decodifica o json
					$ViewF['ctf_fatura'] = json_decode($ViewF['ctf_fatura'], true);
				}
			}
			return (count($Open) == 1) ? reset($Open) : $Open;
		}

		// Se não precisar decodificar o json
		if ($DecodeJson === false) return $Base->get_result()->fetch_all(MYSQLI_ASSOC);

		// Se pedir para decodificar o json de uma vez
		$Map = $Base->get_result()->fetch_all(MYSQLI_ASSOC);
		foreach ($Map as &$ViewF) {
			$ViewF['ctf_fatura'] = json_decode($ViewF['ctf_fatura'], true);
		}
		return $Map;
	}
	public function CartoesCompra($Item, $Valor, $Parcelas = false){
		global $db;

		// Procura a fatura em aberto.
		$Fatura = $this -> CartoesFatura('open');

		// Calcula o valor da parcela
		$ValorParcela = number_format(($Parcelas > 1 ? $Valor/$Parcelas : $Valor), 2, '.', '');

		// Existe uma fatura em aberto
		if(isset($Fatura['ctf_id'])){
			// Adiciona o elemento na fatura
			$Fatura['ctf_fatura']['itens'][] = [
				'item' => $Item . ($Parcelas ? ' (1 de ' . $Parcelas . ')' : ''),
				'valor' => $ValorParcela
			];
			$Fatura['ctf_fatura']['fatura'] += $ValorParcela;
			$Fatura['ctf_fatura']['limite_livre_novo'] -= $Valor;
			// Informa caso tenha pagamentos parcelados
			if($Parcelas){
				$Fatura['ctf_fatura']['parcelas'][] = [
					'item' => $Item,
					'valor' => $ValorParcela,
					'parcelaAtual' => 1,
					'pacelaTotal' => $Parcelas
				];
			}

			$Fatura['ctf_fatura'] = json_encode($Fatura['ctf_fatura']);

			// Atualiza a fatura
			$Base = $db -> prepare("UPDATE cartoes_fatura SET ctf_fatura = ?, ctf_valor = ctf_valor + ? WHERE ctf_id = ?");
			$Base -> bind_param("sdi", $Fatura['ctf_fatura'], $Valor, $Fatura['ctf_id']);
			return (bool) $Base -> execute();
		}

		// Se não existe uma fatura em aberto
		$Fatura = $this -> CartoesFaturaMap();
		$Fatura['ctf_fatura']['itens'][] = [
			'item' => $Item . ($Parcelas ? ' (1 de ' . $Parcelas . ')' : ''),
			'valor' => $ValorParcela
		];
		$Fatura['ctf_fatura']['fatura'] = $ValorParcela;
		$Fatura['ctf_fatura']['limite_livre_novo'] = -$Valor;
		$Fatura['ctf_valor'] = $ValorParcela;
		// Informa caso tenha pagamentos parcelados
			if($Parcelas){
				$Fatura['ctf_fatura']['parcelas'][] = [
					'item' => $Item,
					'valor' => $ValorParcela,
					'parcelaAtual' => 1,
					'pacelaTotal' => $Parcelas
				];
			}

		$Fatura['ctf_fatura'] = json_encode($Fatura['ctf_fatura']);

		// Insere a fatura
		$Base = $db -> prepare("INSERT INTO cartoes_fatura (ctf_cartao, ctf_fatura, ctf_valor) VALUES (?,?,?)");
		$Base -> bind_param("isd", $this -> cardID, $Fatura['ctf_fatura'], $Fatura['ctf_valor']);
		return (bool) $Base -> execute();

	}
	public function CartoesFaturaGerar()
	{ // Gera a fatura com base nos itens aleatórios
		global $db;

		if (!isset($this->Conta['cartoes'])) {
			$this->findConta();
		}

		try {
			// Procura nas faturas anteriores se houve pagamento em aberto
			$cardBase = $db->prepare("SELECT * FROM cartoes_fatura WHERE ctf_cartao = ? ORDER BY ctf_id DESC LIMIT 1");

			// Prepara a funcoes para buscar na base elementos de gastos aleatorios
			$Base = $db->prepare("SELECT ctg_info as item, ctg_valor as valor FROM cartoes_gastos ORDER BY RAND() LIMIT 200");
			$SalarioMinimo = new Taxas()->getSalarioMinimo(); // Busca o salario mínimo atual

			// Percorre os itens buscados na base
			foreach ($this->Conta['cartoes'] as $KeyC => $ViewC) {

				// Calcula o percentual que será usado do limite disponivel para este mês
				$MaxValor = $ViewC['card_limite_livre'] * rand(0, 100) / 100;

				// Prepara o map de gastos
				$MapGastos = $this->CartoesFaturaMap($ViewC['card_limite_livre']);

				// Verifica se existe débito pendente na fatura anterior;
				$cardBase->bind_param("i", $ViewC['card_id']);
				$cardBase->execute();
				$CartaoBackFature = $cardBase->get_result()->fetch_assoc();
				$CartaoBackID = false; // ID da fatura anterior

				$CartaoBackFature['ctf_fatura'] = json_decode($CartaoBackFature['ctf_fatura'], true);

				if (isset($CartaoBackFature['ctf_id']) && $CartaoBackFature['ctf_id'] > 0) { // Verifica se achou a fatura anterior

					// Associa as parcelas da fatura anterior a nova fatura que será criada
					$MapGastos['parcelas'] = $CartaoBackFature['ctf_fatura']['parcelas'];

					// Verifica se houve pagamento em aberto
					if ($CartaoBackFature['ctf_valor'] > $CartaoBackFature['ctf_pagamento']) { // Verifica se houve pagamento total
						$CartaoBackID = $CartaoBackFature['ctf_id'];

						$ValorEmAberto = number_format($CartaoBackFature['ctf_valor'] - $CartaoBackFature['ctf_pagamento'], 2, '.', '');
						$ValorRotativo = number_format($ValorEmAberto * ($ViewC['card_tipo_rotativo'] / 100), 2, '.', '');

						$MapGastos['fatura'] += ($ValorRotativo + $ValorEmAberto); // Associa o valor total a soma
						$MapGastos['itens'][] = [ // Adiciona o item ao fatura em aberto da fatura anterior
							'item' => 'Fatura Anterior - Aberta',
							'valor' => $ValorEmAberto
						];
						$MapGastos['itens'][] = [ // Adiciona o item a fatura atual referente ao juros do rotativo da fatura anterior
							'item' => 'Juros do Rotativo',
							'valor' => ($ValorRotativo)
						];
					}
				}

				// Procura randomicamente os gastos;
				$Base->execute();
				$CartoesGastos = $Base->get_result()->fetch_all(MYSQLI_ASSOC);

				// Percorre os elementos de gastos encontrados aleatórios
				foreach ($CartoesGastos as $ViewG) {
					$ViewG['valor'] = number_format($ViewG['valor'] * $SalarioMinimo, 2, '.', '');

					// Verifica se não estoura o limite disponivel
					if ($MapGastos['fatura'] + $ViewG['valor'] < $MaxValor) {

						// Adiciona o item ao fatura
						$MapGastos['fatura'] += $ViewG['valor'];
						$MapGastos['itens'][] = $ViewG;

					} else {
						break; /* Se atingir o limite, encerra o foreach */
					}
				}

				//  Verifica se houve pagamento na fatura anterior
				$MapGastos['limite_livre_novo'] -= number_format($MapGastos['fatura'], 2, '.', '');

				// Fechar a fatura anterior
				if ($CartaoBackID) {
					$cardUpg = $db->prepare("UPDATE cartoes_fatura SET ctf_pagamento = ctf_valor WHERE ctf_id = ?");
					$cardUpg->bind_param("i", $CartaoBackID);
					$cardUpg->execute();
				}

				// Gera as próximas parcelas extraindo em 1 a parcela, associando esse valor a fatura atual.
				if(isset($MapGastos['parcelas'])){
					foreach($MapGastos['parcelas'] as $KeyP => &$ViewP){

						$ViewP['parcelaAtual']++; // Incrementa em 1 a quantidade de parcelas.
						if($ViewP['parcelaAtual'] > $ViewP['pacelaTotal']){ // Verifica se chegou na ultima parcela
							unset($MapGastos['parcelas'][$KeyP]); // Remove o item pois ele já foi completamente pago
							continue;
						}

						// Se ele ainda não foi quitado, continua o processo;
						$MapGastos['fatura'] += $ViewP['valor']; // adiciona o valor da parcela na fatura do cartão
						$MapGastos['itens'][] = [ // cria o item que será exibido na fatura do cartão
							'item' => $ViewP['item'] . '(' . $ViewP['parcelaAtual'] . ' de ' . $ViewP['pacelaTotal'] . ')',
							'valor' => $ViewP['valor']
						];

					}
				}
				
				// Criar a nova fatura
				$cardInsFatura = $MapGastos['fatura'];
				$MapGastos = json_encode($MapGastos);
				$cardIns = $db->prepare("INSERT INTO cartoes_fatura (ctf_cartao, ctf_fatura, ctf_valor) VALUES (?,?,?)");
				$cardIns->bind_param("isd", $ViewC['card_id'], $MapGastos, $cardInsFatura);
				$cardIns->execute();
			}
			
			return true;

		} catch (Exception $e) {
			error_log("Erro ao processar fatura: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
		}
	}
	public function CartoesPagar($valor)
	{ // Pagamento do cartão pelo cliente
		global $db;
		try {

			// Atualiza a informação de pagamento do cartão
			$cardUpg = $db->prepare("UPDATE cartoes_fatura SET ctf_pagamento = ctf_pagamento + ? WHERE ctf_cartao = ? ORDER BY ctf_id DESC LIMIT 1");
			$cardUpg->bind_param("di", $valor, $this->cardID);
			$cardUpg->execute();
			// Lança no Extrato
			$this->setExtrato('Pagamento do cartão', (-1) * $valor);

			return true;
		} catch (Exception $e) {
			error_log("Erro ao processar pagamento do cartão pelo usuário: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	private function CartoesAnteciparParcela($faturaID, $parcelaID, $parcelaAtual){ // Antecipa o pagamento de uma determinada parcela.
		global $db;

		if(
			!is_numeric($this -> contaID) OR
			!is_numeric($this -> cardID) OR 
			!is_numeric($faturaID) OR
			!is_numeric($parcelaID) OR 
			!is_numeric($parcelaAtual)
		){return false;}

		try {
			$CaminhoJson = "$.parcelas[{$parcelaID}].parcelaAtual";
			$Upg = $db -> prepare("UPDATE cartoes_fatura SET ctf_fatura = JSON_SET(
				ctf_fatura, ?, ?
			) WHERE ctf_id = ? AND ctf_cartao = ? LIMIT 1");
			$Upg -> bind_param("siii", $CaminhoJson, $parcelaAtual, $faturaID, $this -> cardID);
			return boolval($Upg -> execute());

		} catch (Exception $e) {
			error_log("Erro ao processar pagamento do cartão pelo usuário: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	public function CartoesCancelar($CardID)
	{ // Cancelamento do cartão de crédito
		global $db, $MS;
		try {

			// Verifica se o cartão existe e é do usuário logado
			if (!isset($this->Conta['cartoes'][$CardID])) {
				return false;
			}

			// Verifica se existe fatura em aberto. Se sim, lança no extrato
			if ($this->Conta['cartoes'][$CardID]['card_fatura_valor'] > 0) {
				$this->CartoesPagar($this->Conta['cartoes'][$CardID]['card_fatura_valor']);
			}

			// Verifica se existe anuidade no cartão. Se sim, lança no extrato
			$Anuidade = $this->CartoesTipo($this->Conta['cartoes'][$CardID]['card_tipo'])['anuidade'];
			if ($Anuidade > 0) {
				$this->setExtrato('Taxa de cancelamento do cartão.', (-1) * $Anuidade * 0.3);
			}

			// Atualiza a informação de pagamento do cartão
			$cardUpg = $db->prepare("UPDATE cartoes SET card_ativo = 0 WHERE card_id = ?");
			$cardUpg->bind_param("i", $CardID);
			$cardUpg->execute();

			return true;
		} catch (Exception $e) {
			error_log("Erro ao processar cancelamento do cartão pelo usuário: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}

	// Funções relacionadas a PIX
	public function PixMinhasChaves()
	{
		global $db, $MS;

		$Base = $db->prepare("SELECT pk_id, pk_tipo, pk_chave, pk_ativo  FROM pix_chaves  WHERE pk_conta = ?");
		$Base->bind_param('i', $this->contaID);
		$Base->execute();

		$Keys = [];

		foreach ($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $ViewP) {
			$Keys[$ViewP['pk_tipo']] = [
				'pkId' => $ViewP['pk_id'],
				'chave' => $ViewP['pk_chave'],
				'ativo' => (int)$ViewP['pk_ativo']
			];
		}

		// Se não existir chave aleatória, sugere uma
		if (array_key_exists(0, $Keys) === false) {
			$Keys[0] = [
				'pkId' => false,
				'chave' => $this->PixGerarChaveAleatoria(),
				'ativo' => 0
			];
		}

		// Sempre sugere o email do usuário, se ainda não estiver listado
		if (array_key_exists(1, $Keys) === false) {
			$Keys[1] = [
				'pkId' => false,
				'chave' => $MS['user_email'],
				'ativo' => 0
			];
		}

		return $Keys;
	}
	public function setPixChaves($Chaves)
	{ // Insere ou atualiza a chave pix associada a uma conta
		global $db, $MS;

		$db->begin_transaction();

		try {

			// Chaves ativas atuais da conta
			$stmtAtivas = $db->prepare("SELECT pk_id, pk_chave FROM pix_chaves WHERE pk_conta = ? AND pk_ativo = 1");
			$stmtAtivas->bind_param("i", $this->contaID);
			$stmtAtivas->execute();
			$resultAtivas = $stmtAtivas->get_result();

			$chavesAtuais = [];
			while ($row = $resultAtivas->fetch_assoc()) {
				$chavesAtuais[$row['pk_chave']] = $row['pk_id'];
			}

			// Normaliza chaves recebidas
			$chavesRecebidas = [];

			foreach ($Chaves as $Key => $View) {

				if (filter_var($View, FILTER_VALIDATE_EMAIL)) {

					if ($View !== $MS['user_email']) {
						continue;
					}

					$tipo = 1; // email

				} else {
					$tipo = 0; // aleatória
				}

				$chavesRecebidas[$View] = $tipo;
			}

			// Desativa chaves que não vieram do frontend
			$stmtDesativar = $db->prepare("UPDATE pix_chaves SET pk_ativo = 0 WHERE pk_id = ?");

			foreach ($chavesAtuais as $chave => $pk_id) {
				if (!array_key_exists($chave, $chavesRecebidas)) {
					$stmtDesativar->bind_param("i", $pk_id);
					$stmtDesativar->execute();
				}
			}

			// Ativa / insere chaves recebidas
			$stmtVerificar = $db->prepare("SELECT pk_id FROM pix_chaves WHERE pk_chave = ?");
			$stmtAtualizar = $db->prepare("UPDATE pix_chaves SET pk_conta = ?, pk_ativo = 1 WHERE pk_id = ?");
			$stmtInserir = $db->prepare("INSERT INTO pix_chaves (pk_conta, pk_chave, pk_ativo, pk_tipo) VALUES (?, ?, 1, ?)");

			foreach ($chavesRecebidas as $chave => $tipo) {

				// Verifica se a chave ja foi cadastrada
				$stmtVerificar->bind_param("s", $chave);
				$stmtVerificar->execute();
				$res = $stmtVerificar->get_result();

				if ($res->num_rows > 0) {

					$row = $res->fetch_assoc();

					// UPDATE permitido (pela regra didática, ou seja, no pix real esse update não seria válido)
					$stmtAtualizar->bind_param("ii", $this->contaID, $row['pk_id']);
					$stmtAtualizar->execute();
				} else {

					// Se não foi, tenta inserir
					$stmtInserir->bind_param("isi", $this->contaID, $chave, $tipo);
					$stmtInserir->execute();
				}
			}

			$db->commit();
			return true; // Se tudo ocorreu bem, retorna verdadeiro

		} catch (Throwable $e) {
			$db->rollback();
			// throw $e;
			return false; // Se algo deu errado, retorna falso
		}
	}
	private function PixGerarChaveAleatoria()
	{ // Gera uma chave pix aleatoria
		$Pix = new Pix();
		return $Pix->PixGerarChaveAleatoria();
	}
	public function PixBuscarChave(string $chave): array
	{ // Busca uma chave pix
		global $db;
		try {
			$Buscar = $db->prepare("SELECT 
				pk_id as id, 
				pk_conta as conta, 
				pk_chave as chave, 
				user_nome as nome 
			FROM pix_chaves 
			INNER JOIN contas ON (contas.ct_id = pk_conta)
			INNER JOIN user ON (user.user_id = contas.ct_user)
			WHERE pk_chave = ? AND pk_ativo = 1 LIMIT 1");
			$Buscar->bind_param('s', $chave);
			$Buscar->execute();
			return array_merge([
				'status' => 'success',
				'message' => 'Chave pix encontrada!'
			], $Buscar->get_result()->fetch_assoc());
		} catch (Throwable $e) {
			return [
				'status' => 'error',
				'message' => 'Chave pix não encontrada!'
			];
		}
	}

	// Funções relacionadas a investimentos
	public function InvestimentoRentabilizar()
	{
		global $db;

		try {
			// Atualiza os investimentos
			$InvUpg = $db->prepare("UPDATE investimentos SET inv_meses = ?, inv_saldo = ?, inv_ativo = ? WHERE inv_id = ? LIMIT 1");

			// Pega os investimentos
			$Inv = $this->Conta['investimentos'];
			$InvTipo = new Investimentos($this->contaID)->Tipos(); // Busca os tipos de investimentos
			$CDI = (new Taxas()->getCDIMes()) / 100; // Busca o CDI do mês atual em % e retorna o decimal
			$RiscoAleatorio = (mt_rand(-50, 50) / 100); // entre -0.5 e 0.5

			foreach ($Inv as $KeyI => &$ViewI) {
				if ($ViewI['inv_ativo'] == 1) {

					$TaxaReal = $InvTipo[$ViewI['inv_tipo']]['taxaReal'] / 100;
					$Risco = $InvTipo[$ViewI['inv_tipo']]['risco'];

					// Irá pegar a taxa real do invTipo e calcular a taxa do investimento, baseado nela
					// Ex Cofrinho: 120% = 1.2 * CDI
					// Se não for CDI, calcula um valor com base no risco.
					$Inv[$KeyI]['inv_tipo_periodo'] = $InvTipo[$ViewI['inv_tipo']]['periodo'];
					$Inv[$KeyI]['inv_tipo_taxareal'] = 1 + (
						($InvTipo[$ViewI['inv_tipo']]['cdi'] ? // Se rentabilizar com base no CDI
							(is_numeric($CDI) ? $CDI : 0) // Se o CDI não for um número válido ele altera para 1 retornando o CDI sendo 100%
							: ($TaxaReal - ($TaxaReal * $Risco / 20) + ($Risco / 50) * $RiscoAleatorio)) //* $TaxaReal // Calcula o risco real baseado em um número aleatório e na tabela de risco do investimento
					);

					$Inv[$KeyI]['inv_meses']++;

					// Se der o período, rentabiliza.
					if ($Inv[$KeyI]['inv_meses'] % $Inv[$KeyI]['inv_tipo_periodo'] == 0) {
						$Inv[$KeyI]['inv_saldo'] = $Inv[$KeyI]['inv_saldo'] * ($Inv[$KeyI]['inv_tipo_taxareal']);
					}

					// Verifica se atingiu o prazo final do investimento, exceto o tipo Cofrinho
					if ($Inv[$KeyI]['inv_meses'] == $Inv[$KeyI]['inv_periodo']) {
						$Inv[$KeyI]['inv_ativo'] = ($Inv[$KeyI]['inv_tipo'] != 1 or $Inv[$KeyI]['inv_saldo'] <= 0) ? 0 : 1;
					}

					// Atualiza as informações do investimento
					$InvUpg->bind_param(
						'idii',
						$Inv[$KeyI]['inv_meses'],
						$Inv[$KeyI]['inv_saldo'],
						$Inv[$KeyI]['inv_ativo'],
						$KeyI
					);

					$InvUpg->execute();
				}
			}

			return true;
		} catch (Throwable $e) {
			error_log("Erro ao rentabilizar investimentos: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}

	// Funções relacionadas a Pagamentos
	public function updPagamentos($Item)
	{
		// Recebo o json ou array contendo as informacoes para atualizar as mesmas.
		// O json ou array recebido é o mesmo que contem toda a linha do processamento do pagamento

		global $db;
		// Promove verificacoes para validar o item de pagamento passado.
		if (
			!array_key_exists('ctp_id', $Item) and
			!array_key_exists('ctp_aberto', $Item) and
			!array_key_exists('ctp_contas', $Item) and
			$Item['ctp_conta'] != $this->contaID

		) {
			return false;
		}

		$Item['ctp_contas'] = is_array($Item['ctp_contas']) ? json_encode($Item['ctp_contas']) : $Item['ctp_contas'];

		$Base = $db->prepare("UPDATE contas_pagamentos SET ctp_aberto = ?, ctp_contas = ? WHERE ctp_id = ? AND ctp_conta = ? LIMIT 1");
		$Base->bind_param("isii", $Item['ctp_aberto'], $Item['ctp_contas'], $Item['ctp_id'], $Item['ctp_conta']);
		return (bool) $Base->execute();
	}
	public function getPagamentos()
	{
		global $db;

		// Busca os pagamentos em aberto no banco de dados
		$Base = $db->prepare("SELECT * FROM contas_pagamentos WHERE ctp_conta = ? AND ctp_aberto = 1 ORDER BY ctp_id ASC");
		$Base->bind_param("i", $this->contaID);
		$Base->execute();
		$Map = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ctp_id');
		$Base->close();
		foreach ($Map as &$View) {
			$View['ctp_contas'] = json_decode($View['ctp_contas'], true);
		}
		return $Map;
	}
	public function PagamentosGerar()
	{
		global $db;
		// Zera a variavel pagamento
		$this->Pagamentos = [];

		// Verifica se o número da agência está carregado
		if (!isset($this->Conta['ct_agencia']) and !is_numeric($this->agenciaID)) return false;
		$this->agenciaID = (is_numeric($this->agenciaID)) ? $this->agenciaID : $this->Conta['ct_agencia'];

		$Config = new Agencia($this->agenciaID)->getConfig(); // Busca a configuração de débitos
		$SalarioMinimo = new Taxas()->getSalarioMinimo(); // Busca o salario mínimo atual

		// Verifica se a configuração de debitos foi encontrada
		if (!isset($Config['debitos']) or !is_array($Config['debitos']) or count($Config['debitos']) == 0) return false;

		foreach ($Config['debitos'] as $KeyD => $ViewD) {
			// Calcula o valor do pagamento + Variação (se houver);
			$Valor = number_format(((is_numeric($ViewD['valor'])) ? $ViewD['valor'] : ($ViewD['porcentagem'] / 100 * $SalarioMinimo)) * (is_numeric($ViewD['variacao']) ? (1 + rand(0, $ViewD['variacao']) / 100) : 1), 2, '.', '');

			$this->Pagamentos[] = [
				'nome' => $ViewD['nome'],
				'valor_inicial' => $Valor,
				'valor' => $Valor,
				'juros' => (isset($ViewD['juros']) ? $ViewD['juros'] : 0),
				'pago' => false,
				'ciclo' => 0
			];
		}
		// Transforma o array criado em json
		$this->Pagamentos = json_encode($this->Pagamentos);
		// Insere os pagamentos no banco de dados
		$Ins = $db->prepare("INSERT INTO contas_pagamentos (ctp_conta, ctp_contas) VALUES (?, ?)");
		$Ins->bind_param("is", $this->contaID, $this->Pagamentos);
		return (bool) $Ins->execute();
	}
	public function LiquidarPagamentos(){

		$LiquidaError = 0;

		/* 
			Etapa de pagamento das contas ainda pendentes:
		*/
		$Pagamentos = ($this -> getPagamentos());
		foreach($Pagamentos as &$ViewPagamento){
			foreach($ViewPagamento['ctp_contas'] as &$ViewContas){
				if($this -> setExtrato(
					"Execução bancária: " . $ViewContas['nome'],
					(-1) * $ViewContas['valor']
				)){ $ViewContas['pago'] = 1; }else{ $LiquidaError++; };
			}

			// Verifica se todos os pagamentos foram realizados para informar ao banco de dados
			$Pagos = array_column($ViewPagamento['ctp_contas'], 'pago');
            $ViewPagamento['ctp_aberto'] = (in_array(false, $Pagos, true) || in_array(0, $Pagos, true) || in_array('', $Pagos, true));
            $ViewPagamento['ctp_contas'] = json_encode($ViewPagamento['ctp_contas']);

			// Atualiza as informações
			$LiquidaError += $this -> updPagamentos($ViewPagamento) ? 0 : 1;
		}

		/*
			Etapa de pagamentos dos cartões:
		*/
		$Cartoes = $this -> Cartoes();
		foreach($Cartoes as $ViewCartao){
			
			$this -> cardID = $ViewCartao['card_id']; // Informa qual cartão estamos trabalhando no momento
			$cardFatura = $this -> CartoesFatura('open'); // Busca a fatura aberta do cartão

			if(is_array($cardFatura) AND array_key_exists('ctf_id', $cardFatura)){
				// Processa fatura do cartão
				$cartaoPagar = $cardFatura['ctf_valor'] - $cardFatura['ctf_pagamento'];
				$cartaoPagar = $cartaoPagar > 0 ? $cartaoPagar : 0;
				$LiquidaError += $this -> CartoesPagar($cartaoPagar) ? 0 : 1;
				
				// Processa parcelas do cartão
				$cardFatura['ctf_fatura'] = is_array($cardFatura['ctf_fatura']) ? $cardFatura['ctf_fatura'] : json_decode($cardFatura['ctf_fatura'], true); // Verifica se está no formato correto
				// Parcelas
				if(isset($cardFatura['ctf_fatura']['parcelas'])){ // Existem parcelas?
					foreach($cardFatura['ctf_fatura']['parcelas'] as $KeyParcela => $ViewParcela){ // Vamos percorrer todos os itens parcelados

						// Vamos verificar se existem parcelas em aberto
						if($ViewParcela['parcelaAtual'] < $ViewParcela['pacelaTotal']){

							// Calcula quantas parcelas faltam para pagar
							$ParcelasRestantes = $ViewParcela['pacelaTotal'] - $ViewParcela['parcelaAtual'];
							// Paga a parcela
							$LiquidaError += $this -> setExtrato(
								$ViewParcela['item'],
								((-1) * $ViewParcela['valor'] * $ParcelasRestantes),
							) ? 0 : 1;
							// Informa que a parcela foi paga
							$LiquidaError += $this -> CartoesAnteciparParcela( // faturaID, parcelaID, parcelaAtual
								$cardFatura['ctf_id'],
								$KeyParcela,
								$ViewParcela['pacelaTotal']
							) ? 0 : 1;	
						}
					}
				}
			}
		}

		/*
			Etapa de investimentos
		*/
		$Investimentos = new Investimentos($this -> contaID);
		$InvestimentosLista = $Investimentos -> Listar();
		foreach($InvestimentosLista as $ViewInvestimento){
			// Vamos retirar os fundos do investimento
			$Investimentos -> invID = $ViewInvestimento['inv_id'];
			$LiquidaError += $Investimentos -> Fundos((-1) * $ViewInvestimento['inv_saldo']) ? 0 : 1;
			$Investimentos -> Ativo(0);
		}

		return boolval(!$LiquidaError);
	}

	// Shop Compras
	public function getMyShopItens()
	{
		global $db;
		$Base = $db->prepare("SELECT * FROM contas_shop WHERE cts_conta = ? ORDER BY cts_dref DESC");
		$Base->bind_param("i", $this->contaID);
		$Base->execute();
		$Itens = ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'cts_id');
		foreach ($Itens as &$View) {
			$View['cts_item'] = json_decode($View['cts_item'], true);
		}
		return $Itens;
	}
	public function setMyShopItem($Item, $Quantidade)
	{
		global $db;

		// Verifica o shopItem do usuário
		$ShopItens = $this->getMyShopItens();
		if (!is_array($ShopItens) or count($ShopItens) == 0 or !isset($ShopItens[$Item])) return false;

		$getItem = $ShopItens[$Item]; // O cts_item já está no formato Array

		// Verifica se o item tem stock suficiente
		if ($getItem['cts_item']['stock'] < $Quantidade) return false;

		// Atualiza o item
		// Se for passado false, ele remove todos os itens.
		$getItem['cts_item']['stock'] -= ($Quantidade === false or $Quantidade == 0) ? $getItem['cts_item']['stock'] : $Quantidade;

		// Recalcula o valor total do item
		$getItem['cts_item']['price'] = number_format($getItem['cts_item']['stock'] * $getItem['cts_item']['price_unity'], 2);

		// Verifica se a quantidade final não é zero
		if ($getItem['cts_item']['stock'] == 0) {
			$Base = $db->prepare("DELETE FROM contas_shop WHERE cts_conta = ? AND cts_id = ? LIMIT 1");
			$Base->bind_param("ii", $this->contaID, $Item);
			return $Base->execute();
		}

		// Atualiza o item
		$getItem['cts_item'] = json_encode($getItem['cts_item']);
		$Base = $db->prepare("UPDATE contas_shop SET cts_item = ? WHERE cts_conta = ? AND cts_id = ? LIMIT 1");
		$Base->bind_param("sii", $getItem['cts_item'], $this->contaID, $Item);
		return $Base->execute();
	}

	// Outras funcoes
	public function SorteReves(int $Maximo = 0)
	{
		global $db;
		try {
			// Busca os cartões de sorte de forma aleatória
			$Sorte = (new Sorte()->RandomCards($Maximo));
			// Verifica se encontrou algum cartão
			if (is_array($Sorte) and count($Sorte) > 0) {
				foreach ($Sorte as $ViewS) {
					// Cria o extrato e altera o valor do saldo
					$this->setExtrato($ViewS['sr_nome'], ($ViewS['sr_tipo'] == 0 ? (-1) : 1) * $ViewS['sr_calc']);
				}
			}
			return true;
		} catch (Throwable $e) {
			error_log("Erro ao sortear cartões de sorte: " . $e->getMessage() . " em " . $e->getFile() . " na linha " . $e->getLine());
			return false;
		}
	}
	public function Pendencias()
	{
		$Map = $this->getPagamentos();
		$Map['total'] = 0;
		$Map['valor'] = 0;

		foreach ($Map as $KeyP => $ViewP) {
			if (is_numeric($KeyP)) {
				if ($ViewP['ctp_aberto'] == 1) {
					foreach ($ViewP['ctp_contas'] as $KeyI => $ViewI) {
						if (!$ViewI['pago']) {
							$Map['total']++;
							$Map['valor'] += $ViewI['valor'];
						}
					}
				}
			}
		}

		return $Map;
	}
}

class Notificacao
{

	public $Info, $Conta;

	public function __construct($conta = null)
	{
		$this->Conta = $conta;
	}

	public function Listar(bool $Todas = false): array
	{ // Lista todas as notificações
		global $db;

		// Verifica se o ID da conta foi passado
		if ($this->Conta === null) {
			return [];
		}

		if ($Todas === false) {
			// Listar apenas as não lidas		
			$Listar = $db->prepare("SELECT * FROM notificacoes WHERE nt_conta = ? AND nt_lido = 0 ORDER BY nt_id DESC");
			$Listar->bind_param("i", $this->Conta);
			$Listar->execute();
			return ReKey($Listar->get_result()->fetch_all(MYSQLI_ASSOC), 'nt_id');
		} else {
			// Listar todas
			$Listar = $db->prepare("SELECT * FROM notificacoes WHERE nt_conta = ? ORDER BY nt_id DESC");
			$Listar->bind_param("i", $this->Conta);
			$Listar->execute();
			return ReKey($Listar->get_result()->fetch_all(MYSQLI_ASSOC), 'nt_id');
		}
	}

	public function Nova()
	{ // Cria uma nova notificação
		global $db;

		// Cria a notificação passando o ID da conta e a informação
		$Criar = $db->prepare("INSERT INTO notificacoes (nt_conta, nt_info) VALUES (?, ?)");
		$Criar->bind_param("is", $this->Conta, $this->Info);
		if ($Criar->execute()) { // Se a notificação foi criada encerra o statement e retorna o ID
			$Criar->close();
			return $db->insert_id;
		} else { // Se a notificação não foi criada, encerra o statement e retorna falso
			$Criar->close();
			return false;
		}
	}

	public function Lido($notificacaoID)
	{ // Atualiza uma notificação específica para lida
		global $db;
		$Atualizar = $db->prepare("UPDATE notificacoes SET nt_lido = 1 WHERE nt_id = ? LIMIT 1");
		$Atualizar->bind_param("i", $notificacaoID);
		if ($Atualizar->execute()) { // Se a notificação foi atualizada encerra o statement e retorna verdadeiro
			$Atualizar->close();
			return true;
		} else { // Se a notificação nao foi atualizada encerra o statement e retorna falso
			$Atualizar->close();
			return false;
		}
	}

	public function LidoTodos()
	{ // Atualiza todas as notificações para lidas que ainda constam como não lidas
		global $db;
		$Atualizar = $db->prepare("UPDATE notificacoes SET nt_lido = 1 WHERE nt_conta = ? AND nt_lido = 0");
		$Atualizar->bind_param("i", $this->Conta);
		if ($Atualizar->execute()) { // Se a notificação foi atualizada encerra o statement e retorna verdadeiro
			$Atualizar->close();
			return true;
		} else { // Se a notificação nao foi atualizada encerra o statement e retorna falso
			$Atualizar->close();
			return false;
		}
	}
}

class Pix
{

	// Gera uma chave pix aleatoria
	public function PixGerarChaveAleatoria()
	{
		$dados = random_bytes(16);
		// Ajusta os bits conforme RFC 4122 (UUID v4)
		$dados[6] = chr((ord($dados[6]) & 0x0f) | 0x40); // versão 4
		$dados[8] = chr((ord($dados[8]) & 0x3f) | 0x80); // variante RFC
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($dados), 4));
	}

	// Auxilia na decodificação do PIX
	// TLV = Tag · Length · Value
	// Ele irá compactar a informação (ou descompactar neste caso) num formato descrito
	// Exemplo: 59 08 HENRIQUE -> Campo 59, 08 Caracteres seguido da informação -> Henrique
	private function parseTLV(string $data): array
	{
		$result = [];
		$i = 0;
		$len = strlen($data);

		while ($i + 4 <= $len) {
			$id = substr($data, $i, 2);
			$size = (int) substr($data, $i + 2, 2);
			$value = substr($data, $i + 4, $size);

			// Validação estrutural
			if (strlen($value) !== $size) {
				throw new Exception("TLV inválido no campo {$id}");
			}

			$result[$id] = $value;
			$i += 4 + $size;
		}

		return $result;
	}
	// Função auxiliar para calcular o tamanho do TLV
	private function tlv(string $id, string $value): string
	{
		return $id . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
	}

	// gera um código de 16 bits baseado no conteúdo da mensagem, permitindo que o receptor verifique se houve corrupção durante a transmissão
	private function crc16(string $payload): string
	{
		$crc = 0xFFFF;
		for ($i = 0; $i < strlen($payload); $i++) {
			$crc ^= ord($payload[$i]) << 8;
			for ($j = 0; $j < 8; $j++) {
				$crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : ($crc << 1);
			}
		}
		return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
	}
	private function validarCRC(string $payload): bool
	{
		if (!str_contains($payload, '6304')) {
			return false;
		}
		$base = substr($payload, 0, -4);
		$crcInformado = substr($payload, -4);

		return $this->crc16($base) === $crcInformado;
	}

	// Codifica o Payload das informações Pix
	public function encodePixPayload(string $chave, string $nome, ?float $valor = null): string
	{
		$cidade = 'IUNA';

		// Campo 26 (PIX)
		$pix =
			$this->tlv('00', 'BR.GOV.BCB.PIX') .
			$this->tlv('01', $chave);

		$payload =
			$this->tlv('00', '01') .
			$this->tlv('01', $valor !== null ? '12' : '11') .
			$this->tlv('26', $pix) .
			$this->tlv('52', '0000') .
			$this->tlv('53', '986');

		if ($valor !== null) {
			$payload .= $this->tlv('54', number_format($valor, 2, '.', ''));
		}

		$payload .=
			$this->tlv('58', 'BR') .
			$this->tlv('59', strtoupper($nome)) .
			$this->tlv('60', strtoupper($cidade)) .
			'6304';

		// CRC
		$payload .= $this->crc16($payload);

		return $payload;
	}


	// Decodifica o Payload do codigo Pix enviado
	public function decodePixPayload(string $payload): array
	{
		try {
			if (!$this->validarCRC($payload)) {
				throw new Exception('CRC inválido');
			}

			$root = $this->parseTLV($payload);

			if (!isset($root['26'])) {
				throw new Exception('PIX sem campo 26');
			}

			$pix = $this->parseTLV($root['26']);

			$chave = $pix['01'] ?? null;
			if (!$chave) {
				throw new Exception('Chave PIX inexistente');
			}

			return [
				'status' => 'success',
				'chave'   => $chave,
				'tipo'    => str_contains($chave, '@') ? 'email' : 'aleatoria',
				'valor'   => isset($root['54']) ? (float)$root['54'] : null,
				'nome'    => $root['59'] ?? null,
				'cidade'  => $root['60'] ?? null
			];
		} catch (Exception $e) {
			return [
				'status' => 'error',
				'message' => $e->getMessage()
			];
		}
	}
}

class Email
{
	public $email, $assunto, $body;
	private $Config, $ValidConfig, $Mail;

	public function __construct()
	{
		// Carrega e valida as configurações do smtp
		require_once __DIR__ . '/../../config/Config.php';
		$this->Config = (isset($SMTPConfig) && is_array($SMTPConfig)) ? $SMTPConfig : false;
		$this->ValidConfig = $this->validarConfigSMTP();

		// $this -> Mail = new PHPMailer(true);
	}

	private function validarConfigSMTP()
	{
		// Verifica se todos os campos existem
		$camposObrigatorios = ['host', 'port', 'encryption', 'username', 'password'];

		foreach ($camposObrigatorios as $campo) {
			if (!isset($this->Config[$campo]) || $this->Config[$campo] === '' || $this->Config[$campo] === null) {
				return false;
			}
		}

		// Validação específica para porta (deve ser número)
		if (!is_numeric($this->Config['port']) || $this->Config['port'] < 1 || $this->Config['port'] > 65535) {
			return false;
		}

		// Validação de encryption (deve ser um dos tipos aceitos)
		$encryptionsValidas = ['tls', 'ssl', 'starttls', ''];
		if (!in_array($this->Config['encryption'], $encryptionsValidas)) {
			return false;
		}

		// Validação de email para username (opcional)
		if (!filter_var($this->Config['username'], FILTER_VALIDATE_EMAIL)) {
			// Pode não ser um email, então apenas verifica se não está vazio
			if (empty($this->Config['username'])) {
				return false;
			}
		}

		return true;
	}
}

class Shop
{

	public $Categorias, $categoriaID, $Dolar, $produtoID, $agenciaID, $contaID, $apiProduct, $apiCategory;
	private $Endpoint, $EndpointProduto, $Shopping;

	public function __construct($agenciaID = false)
	{

		$this->Categorias = $this->getCategorias();
		$this->categoriaID = false;
		$this->Endpoint = 'https://dummyjson.com/products/category/';
		$this->EndpointProduto = 'https://dummyjson.com/products/';
		$this->Dolar = new Taxas()->getDolar();
		$this->produtoID = false;
		$this->agenciaID = $agenciaID;
	}

	public function getCategorias()
	{
		// O item category das categorias foram adicionados manualmente de acordo com o retorno de
		// https://dummyjson.com/products/categories
		return [
			// Fixas baseado no enriquecimento do jogo ou avanços no trimestre
			1 => ['nome' => 'Imóveis', 'icon' => 'house-fill', 'category' => ''],
			2 => ['nome' => 'Automóveis', 'icon' => 'car-front-fill', 'category' => 'vehicle,motorcycle'],
			3 => ['nome' => 'Educação', 'icon' => 'mortarboard-fill', 'category' => ''],
			// Variáveis baseado no acumulo de itens.
			4 => ['nome' => 'Smartphones', 'icon' => 'phone-fill', 'category' => 'smartphones'],
			5 => ['nome' => 'Eletrodomésticos', 'icon' => 'tv', 'category' => 'kitchen-accessories'],
			6 => ['nome' => 'Eletrônicos', 'icon' => 'headphones', 'category' => 'laptops,tablets'],
			7 => ['nome' => 'Moda e Acessórios', 'icon' => 'universal-access', 'category' => 'mens-shirts,womens-dresses,mens-watches,womens-jewellery'],
			8 => ['nome' => 'Casa e Decoração', 'icon' => 'lamp-fill', 'category' => 'home-decoration'],
			9 => ['nome' => 'Saúde e Cosméticos', 'icon' => 'arrow-through-heart-fill', 'category' => 'beauty,skin-care'],
			10 => ['nome' => 'Alimentos e Bebidas', 'icon' => 'fork-knife', 'category' => 'groceries'],
		];
	}
	public function getAllProdutos()
	{ // Busca todos os produtos da categoria
		if ($this->categoriaID === false) { // Se nenhuma categoria foi escolhida, escolhe uma aleatória
			$this->categoriaID = rand(1, 10);
		}

		// Se a categoria for do tipo Educação, busca os itens criado na configuração da agência.		
		if ($this->categoriaID == 3) {
			$this->apiCategory = $this->getShop();
			return $this->apiCategory;
		}

		// Verifica se o item (ingles) da categoria existe
		if ($this->Categorias[$this->categoriaID]['category'] === '') {
			return [];
		}

		$this->apiCategory = [];
		$this->apiProduct = false;

		// Verifica se existe um arquivo para a categoria criado com base no json passado
		// Se existir, retorna o json em local storage
		if ($this->checkJsonCategory()) {

			$this->apiCategory = json_decode(file_get_contents(PublicHTML . '/files/shop_category_' . $this->categoriaID . '.json'), true);
			// Verifica se algum item nao foi traduzido
			$countTranslateFalse =  count(array_filter(array_column($this->apiCategory, 'translate'), fn($value) => $value == true));
			if ($countTranslateFalse == 0 or $countTranslateFalse < count($this->apiCategory)) {
				$this->translateFile(); // Tenta traduzir o arquivo
				$this->setFile(); // Grava o json em local storage
			}

			return $this->apiCategory;
		}

		// Pega todos os produtos da categoria
		foreach (explode(',', $this->Categorias[$this->categoriaID]['category']) as $categoria) {
			// Pega todos os produtos da categoria via api
			$Json = json_decode(file_get_contents($this->Endpoint . $categoria), true)['products'];
			if (is_array($Json)) {
				$this->apiCategory = array_merge($this->apiCategory, $Json);
			}
		}

		$this->translateFile(); // Tenta traduzir o arquivo
		$this->setFile(); // Grava o json em local storage

		return $this->apiCategory;
	}
	public function getProduto($produtoID)
	{

		if ($this->produtoID === false) {
			$this->produtoID = $produtoID;
		}

		$this->apiProduct = [];
		$this->apiCategory = false;

		// Se o produto passado foi um codigo com ., separa os elementos sendo
		// categoria . idProduto
		if (strstr($produtoID, '.')) {
			$Item = explode('.', $produtoID);
			if (!is_numeric($Item[0]) or !is_numeric($Item[1])) return [];
			$this->produtoID = $Item[1];
			$this->categoriaID = $Item[0];

			// Se a categoria for do tipo Educação, busca os itens criado na configuração da agência.
			if ($this->categoriaID == 3) {
				$agenciaShop = $this->getShop();
				if (!array_key_exists($this->produtoID, $agenciaShop)) return [];

				$this->apiProduct = $agenciaShop[$this->produtoID];
				$this->apiProduct['price_real'] = number_format($this->apiProduct['price'] * $this->Dolar, 2, '.', '');
				$this->apiProduct['price_real_discount'] = number_format($this->apiProduct['price_real'] * (1 - $this->apiProduct['discountPercentage'] / 100), 2, '.', '');
				return $this->apiProduct;
			}
		}

		// Checa se o arquivo do produto ja existe ou se atingiu o tempo limite de modificacao
		if ($this->checkJsonProduct()) {
			$this->apiProduct = json_decode(file_get_contents(PublicHTML . '/files/shop_product_' . $this->produtoID . '.json'), true);

			if (!isset($this->apiProduct['translate']) or $this->apiProduct['translate'] == false) {
				$this->translateFile(); // Tenta traduzir o arquivo
				if ($this->apiProduct['translate'] == true) $this->setFile(); // Salva em json em localstore
			}

			if (!is_array($this->apiProduct)) return false;

			$this->apiProduct['price_real'] = number_format($this->apiProduct['price'] * $this->Dolar, 2, '.', '');
			$this->apiProduct['price_real_discount'] = number_format($this->apiProduct['price_real'] * (1 - $this->apiProduct['discountPercentage'] / 100), 2, '.', '');
			return $this->apiProduct;
		}

		// Se não existir, pega o produto da API
		$this->apiProduct = json_decode(file_get_contents($this->EndpointProduto . $this->produtoID), true);
		$this->apiProduct['price_real'] = number_format($this->apiProduct['price'] * $this->Dolar, 2, '.', '');
		$this->apiProduct['price_real_discount'] = number_format($this->apiProduct['price_real'] * (1 - $this->apiProduct['discountPercentage'] / 100), 2, '.', '');

		$this->translateFile(); // Tenta traduzir o arquivo
		$this->setFile(); // Salva em json em localstore

		return $this->apiProduct;
	}
	public function ComprarProduto($Valor, $Quantidade): bool
	{ // Compra o produto e adiciona a sua conta
		global $db;

		$this->apiCategory = false;

		// Verifica se o produto existe
		if (!is_array($this->apiProduct)) return false;

		// Gera o vetor do produto que será comprado
		$Produto = json_encode([
			"title" => $this->apiProduct['title'],
			"description" => $this->apiProduct['description'],
			"price" => $Valor,
			"stock" => $Quantidade,
			"price_unity" => $this->apiProduct['price_real'],
			"images" => $this->apiProduct['images'][0],
			"thumbnail" => $this->apiProduct['thumbnail']
		], JSON_UNESCAPED_UNICODE);

		if ($this->categoriaID == 3) {
			// Verifica se tem estoque disponivel
			if ($Quantidade > $this->apiProduct['stock']) return false;

			// Atualiza o stock
			$this->apiProduct['stock'] -= $Quantidade;

			// Salva o novo stock no banco de dados
			$Base = $db->prepare("UPDATE agencia_shop SET ags_item = JSON_SET(ags_item, '$.stock', ?) WHERE ags_agencia = ? AND ags_id = ?");
			$Base->bind_param("iii", $this->apiProduct['stock'], $this->agenciaID, $this->produtoID);
			$Base->execute();
			$Base->close();
		}

		// Adiciona o item comprado na conta do usuário
		$Base = $db->prepare("INSERT INTO contas_shop (cts_conta, cts_item) VALUES (?,?)");
		$Base->bind_param("is", $this->contaID, $Produto);
		return boolval($Base->execute());
	}

	// Shopping da agênica
	public function getShop()
	{
		global $db;
		if (!is_numeric($this->agenciaID)) {
			return [];
		}
		$Map = [];

		try {
			$Base = $db->prepare("SELECT * FROM agencia_shop WHERE ags_agencia = ? AND ags_ativo = 1 ORDER BY ags_id DESC");
			$Base->bind_param("i", $this->agenciaID);
			$Base->execute();

			foreach ($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $View) {
				$Map[$View['ags_id']] = array_merge(
					[
						'id' => $View['ags_id'],
						'chave' => $View['ags_key']
					],
					(array)json_decode($View['ags_item'], true)
				);
			}

			$this->Shopping = $Map;
			foreach ($this->Shopping as $KeyS => &$ViewS) {
				$ViewS['price_real'] = number_format($ViewS['price'] * $this->Dolar, 2, '.', '');
				$ViewS['price_real_discount'] = number_format($ViewS['price_real'] * (1 - $ViewS['discountPercentage'] / 100), 2, '.', '');
			}

			return $this->Shopping;
		} catch (Exception $e) {
			error_log($e->getMessage());
			return [];
		}
	}
	public function setShop(array $Item): bool
	{
		global $db, $MS;

		try {

			// Verifica se a agência pertence de fato a você;
			if (!isset($MS['gerente'][$this->agenciaID])) {
				return false;
			}

			// Busca os itens do shop da sua agência no banco de dados
			$this->getShop();

			// Verifica se o item é para inserção
			$Ins = $db->prepare("INSERT INTO agencia_shop (ags_agencia, ags_key, ags_item) VALUES (?,?,?)");

			foreach ($Item as $KeyI => $ViewI) {
				if (!is_numeric($KeyI)) {
					$Ins->bind_param("iss", $this->agenciaID, $KeyI, $ViewI);
					$Ins->execute();
				}
			}

			$Ins->close();

			// Verifica se foi excluida para poder desativar
			$Upd = $db->prepare("UPDATE agencia_shop SET ags_ativo = 0 WHERE ags_agencia = ? AND ags_id = ? AND ags_key = ? LIMIT 1");

			foreach ($this->Shopping as $KeyS => $ViewS) {
				if (!array_key_exists($KeyS, $Item)) {
					$Upd->bind_param("iis", $this->agenciaID, $KeyS, $ViewS['chave']);
					$Upd->execute();
				}
			}

			$Upd->close();

			return true;
		} catch (Exception $e) {
			error_log($e->getMessage());
			return false;
		}
	}

	// Busca e retorna os produtos de uma categoria com base em arquivo local
	// Tambem verifica se o arquivo existe a mais de um periodo minimo e atualiza caso necessario
	private function checkJsonCategory($MaxDias = 365)
	{
		$File = PublicHTML . '/files/shop_category_' . $this->categoriaID . '.json';
		if (!is_file($File)) {
			return false;
		} // Se o arquivo não existir, retorna falso

		// Dias decorridos desde a ultima alteracao
		$Dias = (time() - filemtime($File)) / 86400;

		// Verifica se ultrapassou o prazo de atualizacao
		if ($Dias >= $MaxDias) {
			return false;
		}

		// Se o arquivo existir e não ultrapassar a data maxima, retorna verdadeiro
		return true;
	}
	private function checkJsonProduct($MaxDias = 365)
	{
		$File = PublicHTML . '/files/shop_product_' . $this->produtoID . '.json';
		if (!is_file($File)) {
			return false;
		} // Se o arquivo não existir, retorna falso

		// Dias decorridos desde a ultima alteracao
		$Dias = (time() - filemtime($File)) / 86400;

		// Verifica se ultrapassou o prazo de atualizacao
		if ($Dias >= $MaxDias) {
			return false;
		}

		// Se tudo ocorreu bem, retorna verdadeiro
		return true;
	}

	// Cria o arquivo do Json
	private function setFile()
	{
		// Pega o Json retornado e tenta traduzir se for uma categoria diferente de educação
		// Pois esta categoria a inserção é por parte do professor/gerente
		if ($this->categoriaID == 3) {
			return false;
		}

		// Cria o arquivo
		if (is_array($this->apiProduct) and count($this->apiProduct) > 0) {
			file_put_contents(
				PublicHTML . '/files/shop_product_' . $this->produtoID . '.json',
				json_encode($this->apiProduct, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
			);
		}

		// Cria o arquivo de categoria
		if (is_array($this->apiCategory) and count($this->apiCategory) > 0) {
			file_put_contents(
				PublicHTML . '/files/shop_category_' . $this->categoriaID . '.json',
				json_encode($this->apiCategory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
			);
		}

		return true;
	}
	private function getFile()
	{

		// Exclui a categoria 3 pois estas categorias a inserção é por parte do professor/gerente
		if ($this->categoriaID == 3) {
			return false;
		}

		// Busca o arquivo de produto
		if (is_numeric($this->produtoID)) {
			return file_get_contents(
				PublicHTML . '/files/shop_product_' . $this->produtoID . '.json',
				true
			);
		}

		// Busca o arquivo de categoria
		if (is_numeric($this->categoriaID)) {
			return file_get_contents(
				PublicHTML . '/files/shop_category_' . $this->categoriaID . '.json',
				true
			);
		}

		return false;
	}
	private function translateFile()
	{

		// Tenta traduzir o produto
		if ($this->apiProduct != false) {
			// Tenta traduzir o produto
			$Translate = [
				'title' => GoogleTranslateAPI($this->apiProduct['title']),
				'description' => GoogleTranslateAPI($this->apiProduct['description'])
			];
			// Substitui o produto
			$this->apiProduct['title'] = ($Translate['title'] ?: $this->apiProduct['title']);
			$this->apiProduct['description'] = ($Translate['description'] ?: $this->apiProduct['description']);
			$this->apiProduct['translate'] = boolval(($Translate['title']));

			return;
		}

		// Tenta traduzir a categoria
		if ($this->apiCategory != false) {
			// Pega todos os produtos da categoria
			foreach ($this->apiCategory as &$ViewI) {
				// Tenta traduzir o produto
				$Translate = [
					'title' => GoogleTranslateAPI($ViewI['title']),
					'description' => GoogleTranslateAPI($ViewI['description'])
				];
				// Substitui o produto
				$ViewI['title'] = ($Translate['title'] ?: $ViewI['title']);
				$ViewI['description'] = ($Translate['description'] ?: $ViewI['description']);
				$ViewI['translate'] = boolval(($Translate['title']));
			}

			return;
		}
	}
}


class Admin
{
	// =========================================================================
	// Classe Admin
	// Busca promover uma série de funções do sistema, inclusive para auxiliar 
	// em depuração de problemas usando a função root()
	// =========================================================================
	private $EmailsAutorizados;

	public function __construct()
	{
		$this->EmailsAutorizados = json_decode(file_get_contents(ROOT . '/config/admin_auth.json'), true)['admins'];
	}
	public function Logado()
	{
		global $MS;
		return (
			(is_array($this->EmailsAutorizados) and in_array($MS['user_email'], $this->EmailsAutorizados)) or ($_SESSION['rootAcess'])
		) ? true : false;
	}


	// Usuarios
	public function getAllUser()
	{
		global $db;
		return $db->query("SELECT * FROM user")->fetch_all(MYSQLI_ASSOC);
	}

	// RootAcess
	function rootAcess($User)
	{
		if (isset($_SESSION['rootUser'])) return false;
		if (isset($_SESSION['rootAcess'])) unset($_SESSION['rootAcess']);

		$UserSearch = new Usuario();
		$UserSearch->setID($User);
		$Userfind = $UserSearch->findUser();

		if (!array_key_exists('user_id', $Userfind)) return false;

		$temp = [];
		foreach ($_SESSION as $key => $value) {
			if ($key !== 'HYBRIDAUTH::STORAGE' and $key !== 'form_token') {
				$temp[$key] = $value;
				unset($_SESSION[$key]);
			}
		}

		$_SESSION['rootUser'] = $temp;
		$_SESSION['rootAcess'] = true;
		$_SESSION = array_merge(
			$_SESSION,
			$Userfind,
			[
				'contas' => $UserSearch->getContas(),
				'gerente' => $UserSearch->getGerente()
			]
		);
		$GLOBALS['MS'] = $_SESSION;
		return true;
	}

	// RootReset
	public function rootReset()
	{
		if (isset($_SESSION['rootAcess'])) unset($_SESSION['rootAcess']);
		foreach ($_SESSION as $KeyS => $ViweS) {
			if (!in_array($KeyS, ['HYBRIDAUTH::STORAGE', 'rootUser', 'form_token'])) {
				unset($_SESSION[$KeyS]);
			}
		}
		$_SESSION = array_merge(
			$_SESSION,
			$_SESSION['rootUser']
		);
		unset($_SESSION['rootUser']);
		$MS = $_SESSION;
		return true;
	}

	public function Estatistica()
	{
		global $db;
		return [
			'users' => $db->query("SELECT DISTINCT user_id FROM user")->num_rows,
			'agencias' => $db->query("SELECT DISTINCT ag_id FROM agencia")->num_rows,
			'contas' => $db->query("SELECT DISTINCT ct_id FROM contas")->num_rows
		];
	}
}
