<?php

use Intervention\Image\ImageManager; 

// API do BCB (SGS) para puxar o CDI histórico.
class Taxas {
	private $DadosHistoricos;

	public function __construct(){
		$this -> Update();
		$this -> Historico();
	}

	private function Api(){
		$Json = @file_get_contents('https://api.bcb.gov.br/dados/serie/bcdata.sgs.4391/dados?formato=json');
		return (strlen($Json) == 0) ? false : $Json;
	}

	private function Update(){
		$File = __ROOT__ . '/files/taxa_juros.json';
		
		if (!file_exists($File)) {
			file_put_contents($File, $this -> Api());
			return;
		}

		// Obtém a data de modificação do arquivo
		$dataModificacao = filemtime($File);

		// Obtém o mês e ano da modificação do arquivo
		$mesModificacao = date('n', $dataModificacao);
		$anoModificacao = date('Y', $dataModificacao);

		// Obtém o mês e ano atuais
		$mesAtual = date('n');
		$anoAtual = date('Y');

		// Verifica se a modificação foi em um mês anterior ao atual
    	if ($anoModificacao < $anoAtual || ($anoModificacao == $anoAtual && $mesModificacao < $mesAtual)) {
			// Atualiza o arquivo
			file_put_contents($File, $this -> Api());
		}

	}

	public function Historico(){
		$this -> DadosHistoricos = json_decode(file_get_contents( __ROOT__ . '/files/taxa_juros.json'),true);
		$this -> DadosHistoricos = is_array($this -> DadosHistoricos)?$this -> DadosHistoricos:[];
		return $this -> DadosHistoricos;
	}

	public function MediaAnual(){
		$Media = [];
		foreach($this->DadosHistoricos as $Valor){
			$Media[date('Y',strtotime($Valor['data']))][] = floatval($Valor['valor']);
		}

		foreach($Media as $Ano => $Valor){
			if(is_array($Valor) AND count($Valor)){
				$Media[$Ano] = number_format(array_sum($Valor)/count($Valor),3);
			}else{ unset($Media[$Ano]); }
		}
		
		return $Media;
	}
}

// AGÊNCIA
class Agencia {
	public $numero;

	public function Criar($cep,$key){
		global $db, $MS;
		$key = ($key) ? BaseEL_encode(rand(999,999999)) : NULL;
		$Ins = $db -> prepare("INSERT INTO agencia (ag_user,ag_num,ag_cep,ag_key) VALUES (?, (
			SELECT MAX(ag_num) + 1 FROM agencia
		), ?, ?)");
		$Ins -> bind_param("iss", $MS['ui_id'], $cep, $key);
		if(!$Ins -> execute()){return false;}
		return $Ins -> insert_id;
	}

	public function Buscar(){
		global $db; 
		$Base = $db -> prepare("SELECT ag_num as numero, ag_cep as cep, LENGTH(ag_key) as chave, ui_nome as gerente FROM agencia
		INNER JOIN userinfo ON (userinfo.ui_id = agencia.ag_user)
		WHERE ag_num = ?");
		$Base -> bind_param("i",$this->numero);
		if(!$Base->execute()){return false;}
		return $Base -> get_result() -> fetch_assoc();
	}
}

// CARTOES
class Cartoes
{
	public $conta, $numero, $validade, $cvv, $tipo, $id;
	private $MeusCartoes;
	
	public function __construct(){
		$this->tipo = 0;
	}

	public function Tipo($Tipo){
		switch($Tipo){
			case 0: return 'Débito'; break;
			case 1: return 'Crédito'; break;
			default: return 'Não Informado';
		}
	}

	public function MeusCartoes(){
		global $db;
		if(!is_numeric($this->conta)){return [];}
		$Base = $db -> prepare("SELECT * FROM cartoes WHERE card_cliente = ? ORDER BY card_tipo ASC");
		$Base -> bind_param("i",$this->conta);
		$Base -> execute();
		$this -> MeusCartoes = ReKey($Base -> get_result() -> fetch_all(MYSQLI_ASSOC),'card_id');
		return $this->MeusCartoes;
	}

	public function NovoCartao(){
		global $db;
		if(!is_numeric($this->conta)){return false;}

		$comprimento = 16;

		// VERIFICA O NUMERO DO CARTAO NO BANCO DE DADOS
		$CheckNum = true;
		while($CheckNum){
			
			// Gera os primeiros 15 dígitos aleatórios
			$numero = '';
			for ($i = 0; $i < $comprimento - 1; $i++) {
				$numero .= mt_rand(0, 9);
			}

			$CheckNumRes = $db -> query("SELECT 1 FROM cartoes WHERE card_num = '$numero' LIMIT 1");
			$CheckNum = ($CheckNumRes && $CheckNumRes->num_rows > 0);
		}

		// Aplica o algoritmo de Luhn para o último dígito
		$numero .= $this->DigitoLuhn($numero);

		// Validade entre 1 e 5 anos a partir de agora
		$mes = str_pad(mt_rand(1, 12), 2, '0', STR_PAD_LEFT);
		$ano = date('y') + mt_rand(1, 5);
		//$validade = "$mes/$ano";

		// Código de segurança (CVV) entre 100 e 999
		$cvv = mt_rand(100, 999);
		$limite = mt_rand(20,50) * 100;
		$validade = date("Y-m-t", strtotime("$ano-$mes-01"));

		// INSERE O CARTAO
		$Ins = $db -> prepare("INSERT INTO cartoes (
			card_cliente,
			card_tipo,
			card_num,
			card_validade,
			card_codigo,
			card_limite,
			card_limite_livre
		) VALUES (?,?,?,?,?,?,?)");
		$Ins -> bind_param("iissidd", $this->conta, $this->tipo, $numero, $validade, $cvv, $limite, $limite);
		if($Ins -> execute()){
			$this -> id = $Ins -> insert_id;
			return $this -> id;
		}else{return false;}
	}

	private function DigitoLuhn($numero){
		$soma = 0;
		$invertido = strrev($numero);

		for ($i = 0; $i < strlen($invertido); $i++) {
			$digito = (int)$invertido[$i];
			if ($i % 2 == 0) {
				$digito *= 2;
				if ($digito > 9) $digito -= 9;
			}
			$soma += $digito;
		}

		return (10 - ($soma % 10)) % 10;
	}
}

// USUARIOS
class Usuario
{
	public $cpf, $data, $cep, $nome, $sexo, $tel, $email, $tipo;
	private $id, $findUser, $senha;

	public function setID($id){
		$this->id = $id;
	}
	public function setPass($Pass)
	{
		$this->senha = $Pass;
	}

	public function ValidarCPF()
	{
		// Extrai somente os números
		$cpf = preg_replace('/[^0-9]/is', '', $this->cpf);
		// Verifica se foi informado todos os digitos corretamente
		if (strlen($cpf) != 11) {
			return false;
		}
		// Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
		if (preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}
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

	public function VerificarCPF()
	{
		global $db;
		$Base = $db->prepare("SELECT * FROM userinfo WHERE ui_cpf = ? LIMIT 1");
		$Base->bind_param("s", $this->cpf);
		$Base->execute();
		return $Base->get_result()->fetch_assoc();
	}

	public function Cadastrar()
	{
		global $db;
		if ( // VALIDAÇÃO DOS DADOS
			strlen($this->cpf) == 0 or
			!is_date($this->data) or
			strlen($this->cep) != 9 or
			strlen($this->nome) < 10 or
			!is_numeric($this->sexo) or
			strlen($this->tel) == 0 or
			strlen($this->email) == 0
		) {
			return false;
		}
		// VERIFICA SE O USUARIO JA EXISTE
		$Verificar = $db->prepare("SELECT * FROM userinfo WHERE ui_cpf = ?");
		$Verificar->bind_param("s", $this->cpf);
		$Verificar->execute();
		if ($Verificar->get_result()->num_rows != 0) {
			Alert('Um cadastro com este CPF já existe.<br>Tente recuperar sua senha.');
			return false;
		}

		// CADASTRA		
		$Senha = str_replace(['-', '.'], '', $this->cpf);
		$this->email = strtolower($this->email);
		$Exe = $db->prepare("INSERT INTO userinfo (ui_cpf,ui_nome,ui_email,ui_sexo,ui_telefone,ui_cep,ui_senha,ui_nascimento) VALUES (?,?,?,?,?,?,md5(?),?)");
		dbE();
		$Exe->bind_param("sssissss", $this->cpf, $this->nome, $this->email, $this->sexo, $this->tel, $this->cep, $Senha, $this->data);
		if ($Exe->execute()) {
			$this->id = $Exe->insert_id;
			$this->Login();
			return true;
		}
		return false;
	}

	public function findUser()
	{
		global $db;

		// BUSCA PELO ID
		if ($this->id) {
			$Base = $db->prepare("SELECT * FROM userinfo WHERE ui_id = ? LIMIT 1");
			dbE();
			$Base->bind_param('i', $this->id);

			print "SELECT * FROM userinfo WHERE ui_id = '$this->id' LIMIT 1";

			// BUSCA PELO CPF
		} elseif ($this->cpf) {
			$Base = $db->prepare("SELECT * FROM userinfo WHERE ui_cpf = ? LIMIT 1");
			dbE();
			$Base->bind_param('s', $this->cpf);
		}
		if (!isset($Base) or !$Base->execute()) {
			return false;
		}
		$Map = $Base->get_result()->fetch_assoc();
		$this->findUser = (is_array($Map) and array_key_exists('ui_id', $Map)) ? $Map : false;
		return $this->findUser;
	}

	public function Login($cpf = false, $senha = false)
	{
		global $db;
		if (is_numeric($this->id) or strlen($this->cpf)) {
			$findUser = $this->findUser();
		} else {
			$Base = $db->prepare("SELECT * FROM userinfo WHERE ui_cpf = ? AND ui_senha = md5(?) LIMIT 1");
			$Base->bind_param("ss", $cpf, $senha);
			if ($Base->execute()) {
				$findUser = $Base->get_result()->fetch_assoc();
				if (!array_key_exists('ui_id', $findUser)) {
					return false;
				}
			} else {
				return false;
			}
		}

		// INFOMRA O ID CASO NAO TENHA INFORMADO ANTEIRORMENTE
		if (!is_numeric($this->id)) {
			$this->id = $findUser['ui_id'];
		}

		// ATRIBUI OS VALORES AO SESSION
		foreach ($findUser as $KeyU => $ViewU) {
			$_SESSION[$KeyU] = $ViewU;
		}
		

		$_SESSION['contas'] = $this->Contas();
		$_SESSION['gerente'] = $this->Gerente();
		$_SESSION['id'] = (count($_SESSION['contas']) == 1) ? array_key_first($_SESSION['contas']) : false; // ATRIBUI A CONTA

		shdr('home', 0); // REDIRECIONA PARA O HOME PAGE DO USUARIO
		return true;
	}

	private function Contas()
	{
		global $db;
		if (!is_numeric($this->id)) {
			return [];
		}
		$Base = $db->prepare("SELECT clientes.*, ui_nome FROM clientes 
		INNER JOIN agencia ON (agencia.ag_id = clientes.cl_agencia)
		INNER JOIN userinfo ON (userinfo.ui_id = agencia.ag_user)
		WHERE cl_user = ? ORDER BY cl_tipo ASC, cl_dref DESC");
		$Base->bind_param("i", $this->id);
		if ($Base->execute()) {
			return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'cl_id');
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
			a.ag_id,
			a.ag_num,
			a.ag_user,
			a.ag_cep,
			a.ag_key,
			a.ag_dref,
			COUNT(c.cl_id) AS total_clientes
		FROM  agencia a
		LEFT JOIN clientes c ON a.ag_id = c.cl_agencia
		WHERE  a.ag_user = ?
		GROUP BY  a.ag_id, a.ag_num, a.ag_user, a.ag_cep, a.ag_key, a.ag_dref
		ORDER BY a.ag_num ASC");
		$Base->bind_param("i", $this->id);
		if ($Base->execute()) {
			return ReKey($Base->get_result()->fetch_all(MYSQLI_ASSOC), 'ag_id');
		}
		return [];
	}

	public function ResetPass()
	{
		global $db;
		$Senha = $this->ClearCPF();
		$Base = $db->prepare("UPDATE userinfo SET ui_senha = MD5(?) WHERE ui_id = ? AND ui_cpf = ? LIMIT 1");
		$Base->bind_param('sis', $Senha, $this->findUser()['ui_id'], $this->cpf);
		if (!$Base->execute()) {
			alert('Ocorreu um erro ao tentar resetar sua senha. Por favor, tente novamente.');
			return false;
		}
		return true;
	}

	private function ClearCPF()
	{
		return str_replace(['-', '.', ' '], '', $this->cpf);
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

	public function ContaTipo($Conta){
		switch($Conta){
			case 0: return 'Poupança'; break;
			case 1: return 'Corrente'; break;
			case 3: return 'Jurídica'; break;
			default: return 'Não Informado';
		}
	}

	public function getContas(){
		return $this -> Contas();
	}
}

// MANIPULAÇÃO DO UPLOAD
class Upload
{
	// Local deve sempre ser indicado sem / no final.
	public $input;
	public $local;
	public $reg;

	public function __construct()
	{
		$this->local = 'default';
		$this->reg = true;
	}

	public function Send()
	{
		global $db, $MEUID, $ANOBASE;
		$Dir = ['default', 'default'];
		$ext  = pathinfo($this->input['name'], PATHINFO_EXTENSION);
		$nome = md5(date('dmY His') . rand(0, 99999999) . date('Y-m-d H:i:s')) . '.' . $ext;
		// VERIFICA PASTA
		if (in_array($this->local, ['blog', 'casf', 'ead', 'system', 'eo', 'club', 'default'])) {
			$Dir[0] = $this->local;
		}
		// VERIFICA SUBPASTA
		if (in_array($ext, ['png', 'jpg', 'gif', 'jpge'])) {
			$Dir[1] = 'image';
		} elseif (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx'])) {
			$Dir[1] = 'documents';
		}
		// GERA O DIRETORIO
		$MainDir = "$ANOBASE/$Dir[0]/$Dir[1]/";
		// PROMOVE O UPLOAD
		if (move_uploaded_file($this->input['tmp_name'], __DIR__ . "/../files/$MainDir" . $nome)) {
			// SE FOR INFOMRADO PARA REGISTRAR O ARQUIVO
			$dbReg = false;
			if ($this->reg) {
				$Ins = $db->prepare("INSERT INTO files (fl_user,fl_dir,fl_nome,fl_arquivo,fl_size) VALUES (?,?,?,?,?)");
				dbE();
				$Ins->bind_param("isssi", $MEUID, $MainDir, $this->input["name"], $nome, $this->input["size"]);
				$Ins->execute();
				$dbReg = boolval($Ins->affected_rows);
			}
			if ($this->reg == false or $dbReg == true) {
				// VERIFICA O REGISTRO
				if ($Dir[1] == 'image') {
					$Image = new Resize();
					$Image->File = __DIR__ . "/../files/$MainDir" . $nome;
					@$Image->Exe();
				}
				return [
					'id' => (isset($Ins->insert_id) ? $Ins->insert_id : false),
					'fl_icon' => FileIcon(pathinfo($this->input["name"], PATHINFO_EXTENSION)),
					'fl_icon_color' => FileIcon(pathinfo($this->input["name"], PATHINFO_EXTENSION), 'color'),
					'fl_dir' => $MainDir,
					'fl_nome' => $this->input["name"],
					'fl_arquivo' => $nome,
					'fl_data' => date('d/m/Y'),
					'fl_size' => Byte2($this->input['size'])
				];
				// CASO FALHE O REGISTRO NO BANCO DE  DADOS
			} else {
				unlink(__DIR__ . "/../files/$MainDir" . $nome);
				return false;
			}
		} else {
			return false;
		}
	}
}

// MANIPULANDO IMAGEM
class Resize
{
	public $File, $Max, $NewName, $NewPath;
	public function Exe()
	{
		require_once(__ROOT__ . '/../vendor/autoload.php');
		# CONFIGUAÇÃO
		if (!$this->Max) {
			$this->Max = 1280;
		}
		$Caminho = explode('/', $this->File);
		$Ext = pathinfo(end($Caminho), PATHINFO_EXTENSION);
		if ($this->NewName) {
			$Caminho[count($Caminho) - 1] = $this->NewName . '.' . $Ext;
		}
		if ($this->NewPath) {
			$TempName = $Caminho[count($Caminho) - 1];
			$Caminho = explode('/', $this->NewPath);
			$Caminho[] = $TempName;
		}
		$Imagem = getimagesize($this->File);
		if ($Imagem[0] > $Imagem[1] and $Imagem[0] > $this->Max) {
			$Imagem['L'] = 'W';
		} elseif ($Imagem[1] > $this->Max) {
			$Imagem['L'] = 'H';
		}
		# CRIAÇÃO
		if (isset($Imagem['L'])) {
			// ABRE E MANIPULA A IMAGEM
			$imagem = new ImageManager(array('driver' => 'imagick'));
			$img = $imagem->make($this->File);
			$img->resize((($Imagem['L'] == 'W') ? $this->Max : null), (($Imagem['L'] == 'H') ? $this->Max : null), function ($constraint) {
				$constraint->aspectRatio();
			});
			$img->save(implode('/', $Caminho));
			return true;
		} else {
			return false;
		}
	}
}
