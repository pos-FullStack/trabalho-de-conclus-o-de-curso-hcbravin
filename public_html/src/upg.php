<?php 

// PARAMETRIZA O POST
$P = $_POST; foreach($_POST as $K=>$V){ $$K = $V; }
$Dados = ['P'=>[], 'B'=>[], 'U'=>[],'E'=>[],'D'=>[], 'PRO'=>['U'=>0,'I'=>0,'D'=>0]]; $C=0; $Map = []; $Nulo = null; 

// ----------------------------------------------- UNLOCK A VERIFICAÇÃO DE ANO -----------------------------------------------
//  FUNÇÃO FORA DA TRAVA DE ANO 
if($URI[1]=='secretaria'){

    $InsertDate = (($ANOBASE < $ANOATUAL)?"$ANOBASE-12-31":Data(false,2)).' 00:00:00';

    // PROCESSA O POST
    foreach($P as $KeyP=>$ViewP){
        if($ViewP AND $KeyP != 'turma'){
            // TIPO - VP - USER => VALOR
            $Key = explode('-',$KeyP);
            $Dados['P'][$Key[0]][$Key[1]][$Key[2]] = $ViewP;
        }
    }

    if($URI[2]=='notas'){
        if(array_key_exists('nota',$Dados['P']) AND count($Dados['P']['nota'])){
            $Dados['P'] = $Dados['P']['nota'];
            $ActionReg = 37;
            
            // PROCURA AS AVALIAÇÕES DENTRO DA BASE DE DADOS, SE EXISTE ALGUMA DO TIPO 9
            $Base = $db -> prepare("SELECT avi_vp, avi_id, avn_user, avn_id, avn_nota FROM avaliacoes
            INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes.avi_vp)
            LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id)
            WHERE YEAR(vp_dref) = ? AND avi_tipo = '9' AND avi_tri = ? AND avi_vp IN (".implode(',',array_keys($Dados['P'])).")");
            $Base -> bind_param("ii",$ANOBASE,$TRI); $Base -> execute();
            foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
                $Dados['B'][$V1['avi_vp']][$V1['avn_user']] = $V1['avn_nota'];
                $Map[$V1['avi_vp']] = $V1['avi_id'];
            }

            // PROCURA PARA INSERÇÃO OU ATUALIZAÇÃO
            $Upg = $db -> prepare("UPDATE avaliacoes_notas SET avn_nota = ?, avn_dref = NOW() WHERE avn_avi = ? AND avn_user = ?"); dbE();
            $InsU = $db -> prepare("INSERT INTO avaliacoes_notas (avn_avi,avn_user,avn_nota,avn_rp) VALUES (?,?,?,'-1')"); dbE();
            $InsA = $db -> prepare("INSERT INTO avaliacoes (avi_vp,avi_tipo,avi_info,avi_tri,avi_valor) VALUES (?,9,'SECRETARIA',?,0)"); dbE();
            foreach($Dados['P'] as $K1=>$V1){
                if(array_key_exists($K1,$Map)){
                    // SELECIONA A AVI
                    $AVI = $Map[$K1];
                    // FAZ A BUSCA
                    foreach($V1 as $K2=>$V2){
                        if(array_key_exists($K2,$Dados['B'][$K1])){
                            if($Dados['B'][$K1][$K2] != $V2){
                                $V2 = (!is_numeric($V2) OR $V2==0)?-1:$V2;
                                $Upg -> bind_param("dii",$V2,$AVI,$K2);
                                if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
                            }
                        }else{
                            if(is_numeric($V2)){
                                $InsU -> bind_param("iid",$AVI,$K2,$V2);
                                if(!$InsU->execute()){$C++;}else{$Dados['PRO']['I']++;}
                            }
                        }
                    }
                }else{
                    // PROCURA SE EXISTE ALGUM REGISTRO PARA CRIAR A AVI
                    if(array_sum($V1)){
                        $InsA -> bind_param("ii",$K1,$TRI);
                        if($InsA -> execute()){ $AVI = $InsA -> insert_id; $Map[$K1] = $AVI;
                            // INSERE OS ESTUDANTES  E SUAS NOTAS
                            foreach($V1 as $K2=>$V2){
                                if(is_numeric($V2)){
                                    $InsU -> bind_param("iid",$AVI,$K2,$V2);
                                    if(!$InsU->execute()){$C++;}else{$Dados['PRO']['I']++;}
                                }
                            }
                        }
                    }
                }
            }

            
        
        
        }else{$C++;}
    }

    if($URI[2]=='final'){
        if(array_key_exists('final',$Dados['P']) AND count($Dados['P']['final'])){
            $Dados['P'] = $Dados['P']['final'];
            $ActionReg = 38;
        
            // BUSCA AS NOTAS FINAIS
            $Base = $db -> prepare("SELECT avaliacoes_rpt.* FROM avaliacoes_rpt 
            INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes_rpt.rpt_vp)
            INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
            WHERE turmas.turma_id = ? AND turmas.turma_secretaria = ? AND YEAR(turmas.turma_dref) = ? AND avaliacoes_rpt.rpt_tri = '100'");
            $Base -> bind_param("iii",$turma, $MYSCT, $ANOBASE);
            $Base -> execute();
            foreach($Base->get_result()->fetch_all(MYSQLI_ASSOC) as $KeyB=>$ViewB){
                $Dados['B'][$ViewB['rpt_vp']][$ViewB['rpt_user']] = $ViewB['rpt_nota'];
            }
            
            // PREPARA
            $Ins = $db -> prepare("INSERT INTO avaliacoes_rpt (rpt_vp,rpt_user,rpt_nota,rpt_tri,rpt_dref) VALUES (?,?,?,'100','$InsertDate')");
            $Upg = $db -> prepare("UPDATE avaliacoes_rpt SET rpt_nota = ? WHERE rpt_user = ? AND rpt_vp = ? LIMIT 1");
            $Del = $db -> prepare("DELETE FROM avaliacoes_rpt WHERE rpt_user = ? AND rpt_vp = ? LIMIT 1");

            // PROCURA AS NOTAS PARA INSERIR OU ATUALIZAR
            foreach($Dados['P'] as $KeyV=>$ViewV){
                foreach($ViewV as $KeyE=>$ViewE){
                    if(isset($Dados['B'][$KeyV][$KeyE])){
                        if($ViewE != $Dados['B'][$KeyV][$KeyE]){ // FAZ O UPDATE DA NOTA
                            $Upg -> bind_param("dii",$ViewE,$KeyE,$KeyV);
                            if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
                        }
                    }else{
                        $Ins -> bind_param("iid",$KeyV,$KeyE,$ViewE);
                        if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
                    }
                }
            }

            // PROCURA AS NOTAS PARA REMOÇÃO
            foreach($Dados['B'] as $KeyV=>$ViewV){
                foreach($ViewV as $KeyE=>$ViewE){
                    if(!isset($Dados['P'][$KeyV][$KeyE])){
                        $Del -> bind_param("ii",$KeyE,$KeyV);
                        if(!$Del->execute()){$C++;}else{$Dados['PRO']['D']++;}
                    }
                }
            }
        }
    }

    if($URI[2]=='faltas'){
        if(array_key_exists('falta',$Dados['P']) AND count($Dados['P']['falta'])){
            $Dados['P'] = $Dados['P']['falta'];
            $ActionReg = 39;

            // BUSCA
            $Dados['B'] = SecretariaFaltasMap($turma);

            // PREPARA
            $Ins = $db -> prepare("INSERT INTO bncc_secretaria_frequencia (bsf_vp,bsf_user,bsf_valor,bsf_tri,bsf_dref) VALUES (?,?,?,?,'$InsertDate')"); dbE();
            $Upg = $db -> prepare("UPDATE bncc_secretaria_frequencia SET bsf_nota = ? WHERE bsf_user = ? AND bsf_vp = ? AND bsf_tri = ? LIMIT 1");
            $Del = $db -> prepare("DELETE FROM bncc_secretaria_frequencia WHERE bsf_user = ? AND bsf_vp = ? AND bsf_tri = ? LIMIT 1");


            // PROCURA AS NOTAS PARA INSERIR OU ATUALIZAR
            foreach($Dados['P'] as $KeyV=>$ViewV){
                foreach($ViewV as $KeyE=>$ViewE){
                    if(isset($Dados['B'][$KeyV][$KeyE])){
                        if($ViewE != $Dados['B'][$KeyV][$KeyE]){ // FAZ O UPDATE DA NOTA
                            $Upg -> bind_param("diii",$ViewE,$KeyE,$KeyV, $TRI);
                            if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
                        }
                    }else{
                        $Ins -> bind_param("iidi",$KeyV,$KeyE,$ViewE,$TRI); 
                        if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
                    }
                }
            }

            // PROCURA AS NOTAS PARA REMOÇÃO
            foreach($Dados['B'] as $KeyV=>$ViewV){
                foreach($ViewV as $KeyE=>$ViewE){
                    if(!isset($Dados['P'][$KeyV][$KeyE])){
                        $Del -> bind_param("iii",$KeyE,$KeyV,$TRI);
                        if(!$Del->execute()){$C++;}else{$Dados['PRO']['D']++;}
                    }
                }
            }

        }
    }

    if($C==0 AND array_sum($Dados['PRO'])>0){
		$Dados['PRO']['A'] = $MS['nome'];
		$Dados['PRO']['DIA'] = Data(null,3);
		$Dados['PRO']['TRI'] = $TRI;		
		//ActionReg(37,false,$Dados['PRO']);
	}

    shdr("secretaria/notas-faltas/$turma");

goto Status;}


// ----------------------------------------------- INICIA A VERIFICAÇÃO DE ANO -----------------------------------------------
if($MEUTIPO != 0 AND $ANOBASE != $ANOATUAL){ Alert("Alterações Bloqueadas para o ano de $ANOBASE."); goto Fim; }

// ----------------------------------------------- INÍCIO PROVAS
if($URI[1]=='provas'){

    $Prova = New Provas();
    $Prova -> id = $prova;
    $findP = $Prova -> findProva();

    // VERIFICA SE O USUARIO TEM PERMISSAO DE EDICAO
    if(!is_array($findP) OR !in_array($MEUID,$findP['ap_edit_user'])){ 
        $C++; 
        Alert('Acesso Negado!'); 
        shdr('turmas/provas');
        goto Status;
    
    // PROCESSA A REQUISIÇÃO
    }else{
        // ATUALIZA AS QUESTOES DA PROVA
        if($URI[2]=='questoes'){
            // VERIFICA SE NÃO HÁ NENHUMA RESPOSTA
            if(is_numeric($findP['respostas_qt']) AND $findP['respostas_qt'] == 0){
                $P = $P['questao'];
                // ATUALIZAÇÃO
                $Upg = $db -> prepare("UPDATE avaliacoes_prova_questoes SET apq_num = ?, apq_info = ?, apq_alternativas = ?, apq_resposta = ?, apq_dref = NOW() WHERE apq_id = ? AND apq_prova = ? LIMIT 1");

                foreach($P as $KeyP=>$ViewP){
                    if(array_key_exists($KeyP,$findP['questoes'])){
                        sort($ViewP['correta']);
                        $P[$KeyP]['alternativa'] = json_encode($ViewP['alternativa']);
                        $P[$KeyP]['correta'] = json_encode($ViewP['correta']);
                        
                        // ATUALIZA
                        $Upg -> bind_param("isssii",$P[$KeyP]['num'],$P[$KeyP]['info'],$P[$KeyP]['alternativa'],$P[$KeyP]['correta'],$KeyP,$prova);
                        if(!$Upg->execute()){$C++;}
                    }
                }
            }else{ $C++; Alert('As questões não podem ser editadas pois já existem respostas registradas.'); }
        }

        if($URI[2]=='editores'){
            if($findP['ap_user'] == $MEUID){
                $EditCheck = [$MEUID];
                $User = new Usuario();
                $User -> id = $P['edit'];
                foreach($User->findUser() as $KeyU=>$ViewU){
                    $EditCheck[] = $ViewU['user_id'];
                }
                $EditCheck = json_encode(array_filter($EditCheck));

                // ATUALIZA
                $Upg = $db -> prepare("UPDATE avaliacoes_prova SET ap_edit = ?, ap_dref = NOW() WHERE ap_user = ? AND ap_id = ? AND YEAR(ap_dref) = ? LIMIT 1");
                $Upg -> bind_param("siii",$EditCheck,$MEUID,$prova,$ANOBASE);
                if(!$Upg->execute()){$C++;}

            }else{ $C++; Alert('Só quem criou a prova pode alterar os editores.'); }
        }

        if($URI[2]=='grupos'){
            
            // PARAMETRIZA
            sort($P['group']); foreach($P['group'] as $KeyP=>$ViewP){ sort($P['group'][$KeyP]); }
            $P['group'] = json_encode($P['group']);
            
            // ATUALIZA
            $Upg = $db -> prepare("UPDATE avaliacoes_prova SET ap_group = ?, ap_dref = NOW() WHERE ap_id = ? AND YEAR(ap_dref) = ? LIMIT 1");
            $Upg -> bind_param("sii",$P['group'],$prova,$ANOBASE);
            if(!$Upg->execute()){$C++;}
            
        }



    }

   

    #ppre($P);
    #ppre($findP);
    shdr("turmas/provas/$prova/editar");


goto Status;}

// ----------------------------------------------- INÍCIO USUÁRIO
// GERENCIAMENTO DE CARTEIRINHAS
if($URI[1]=='carteirinha'){
    // SOMENTE SECRETARIA PARA CIMA PODERA ALTERAR ALGUMA INFORMAÇÃO DA CARTEIRINHA
    if($MEUTIPO <= 31){

        // ALTERA O STATUS DA CARTEIRINHA
        if($URI[2]=='status'){

            $Base = $db -> prepare("UPDATE user_carteirinha 
            INNER JOIN user ON (user.user_id = user_carteirinha.uc_user) 
            SET uc_sit = ? , uc_dref = NOW()
            WHERE user_secretaria = ? AND uc_id = ?"); dbE();
            $Base -> bind_param("iii", $URI[4], $MYSCT, $URI[3]);
            if(!$Base -> execute()){ $C++; }

        }

        // ALTERA A VALIDADE MANUALMENTE
        if($URI[2]=='validade'){
            $Base = $db -> prepare("UPDATE user_carteirinha 
            INNER JOIN user ON (user.user_id = user_carteirinha.uc_user) 
            SET uc_validade = ?, uc_dref = NOW() 
            WHERE user_secretaria = ? AND uc_id = ?"); dbE();
            $Base -> bind_param("sii", $novavalidade, $MYSCT, $uc);
            if(!$Base -> execute()){ $C++; }
        }




    }else{Alert('Acesso Negado!'); $C++;}
    shdr('secretaria/carteirinha');
goto Status;}
// CONFIGURAÇÃO DO SISTEMA PARA OS GESTORES
if($URI[1]=='configuracoes'){ 

    if($MEUTIPO > 30){ Alert('Acesso Negado!'); goto Fim; } $NES = []; 

    // EXTRAI INFORMAÇÕES DOS TURNOS
    foreach($P as $K=>$V){
        $ID = explode('-',$K);   // PARAMETRIZA A CHAVE
        if($ID[0] == 'ocreg' OR $ID[0] == 'obreg'){ $NES[$ID[0]][] = $V; unset($P[$K]); }   // OCORRENCIAS
    }
    $NES['ocreg'][] = 0; $NES['obreg'][] = 0;
    $P['ocreg'] = implode(',',$NES['ocreg']);
    $P['obreg'] = implode(',',$NES['obreg']);


    // SWITCH - BLOCK TRI
    $P['blocktri'] = (isset($P['blocktri']))?$P['blocktri']:0;

    // BUSCA NA BASE A CONFIGURAÇÃO
    $Base = $db -> prepare("SELECT * FROM config WHERE cfg_secretaria = ? AND cfg_ano = ?"); dbE();
    $Base -> bind_param("ii",$MYSCT,$ANOBASE);
    $Base -> execute();
    foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K=>$V){ $Map[$V['cfg_nome']] = $V; }

    // PARAMETRIZA PARA CRIAÇÃO OU ATUALIZAÇÃO DO VALOR
    $Reg = $db -> prepare("INSERT INTO config (cfg_secretaria,cfg_nome,cfg_valor,cfg_ano) VALUES (?,?,?,?)");
	$Upg = $db -> prepare("UPDATE config SET cfg_valor = ? WHERE cfg_nome = ? AND cfg_secretaria = ? AND cfg_ano = ? AND cfg_id = ? LIMIT 1"); dbE();

    foreach($P as $K1=>$V1){

        if(!array_key_exists($K1,$Map)){
        
            $V1 = addslashes($V1);
            $Reg -> bind_param("isss",$MYSCT,$K1,$V1,$ANOATUAL);
            if(!$Reg->execute()){$C++;}
    
        }elseif($V1 != $Map[$K1]['cfg_valor']){

            $V1 = addslashes($V1);
            $Upg -> bind_param("ssisi",$V1,$K1,$MYSCT,$ANOATUAL,$Map[$K1]['cfg_id']);
            if(!$Upg->execute()){$C++;}
        
        }
    }

    // GERA O NOVO ES COM BASE NO BANCO DE DADOS
    sCfg(false);
    // CRIA O NOVO ARQUIVO JSON
    sCfgJson();
    
	shdr('configuracoes', ($C)?4:2);

goto Status;}
if($URI[1]=='meus-dados'){
    $userCPF = str_replace([',','-'],'',$userCPF);
    $Upg = $db -> prepare("UPDATE userinfo SET ui_doc = ?, ui_tel = ?, ui_city = ?, ui_email = ?, ui_sexo = ?, ui_endereco = ?, ui_nascimento = ?, ui_dref = NOW() WHERE ui_login = ?");
    $Upg -> bind_param("ssisissi",$userCPF,$userFone,$userCidade,$userEmail,$userSexo,$userEndereco,$userNascimento,$MS['lid']);
    if(!$Upg->execute()){$C++;}

    shdr('profile');
goto Status;}
if($URI[1]=='minha-senha'){
    
    // CHECA SE AS SENHAS SAO IGUAIS
    if($userPass1 != $userPass2){ Alert('As senhas informadas não são idênticas.'); $C++; }else{

        // CONFERE A SENHA ATUAL NA BASE
        $Ver = $db -> prepare("SELECT login_id FROM login WHERE login_pass = MD5(?) AND login_id = ? LIMIT 1");
        $Ver -> bind_param('si',$userPass0,$MS['lid']);
        $Ver -> execute();
        
        if($Ver -> get_result() -> num_rows){

            $Upg = $db -> prepare("UPDATE login SET login_pass = MD5(?) WHERE login_id = ? LIMIT 1");
            $Upg -> bind_param('si',$userPass1,$MS['lid']);
            if(!$Upg -> execute()){$C++;}
        
        }else{
            Alert('Senha atual inválida!');
            $C++;
        }
    }

    shdr('profile', ($C?5:2));

goto Status;}
if($URI[1]=='resetar-senha'){
    if($MEUTIPO > 31){ Alert('Você não tem permissão para esta ação.'); goto Fim;}
    
    // PROCURA O USUARIO
    $User = new Usuario();
    $User -> id = $userID;
    $findUser = $User -> findUser();
    if($MEUTIPO > 0 AND (!is_array($findUser) OR !array_key_exists('user_secretaria',$findUser) OR $findUser['user_secretaria'] != $MYSCT)){
        Alert('Usuário não encontrado!'); goto Fim;
    }
    if($userResetType == 2 AND $userPass1 != $userPass2){
        Alert('As senhas digitadas não conferem.'); goto Fim;
    }

    // TIPIFICA A NOVA SENHA
    $UserPass = ($userResetType == 2) ? $userPass1 : str_replace(['-','/'],'',Data($findUser['ui_nascimento'],3));

    // FAZ A ATUALIZAÇÃO
    $Upg = $db -> prepare("UPDATE login SET login_pass = MD5(?), login_dref = NOW() WHERE login_id = ? LIMIT 1");
    $Upg -> bind_param("si",$UserPass,$findUser['user_login']);
    if(!$Upg->execute()){$C++;}
    shdr('secretaria/usuario');

goto Status;}
if($URI[1]=='dados-usuario'){
    $User = New Usuario();

    // VERIFICA SE O USUARIO É DA INSTITUIÇÃO
    $User -> id = $userID;
    $findUser = $User -> findUser();
    if(@$findUser['user_secretaria'] != $MYSCT){
        Alert('Acesso Negado para alteração cadastral deste usuário.'); goto Fim;
    } 
    $userLogin = $findUser['user_login'];
    $userTipo = $findUser['user_tipo'];
    $User -> tipo = $userTipo;

    // CHECA SE FOI PREENCHIDO O CPF E O RA
    if(($userTipo != 33 AND strlen($userCPF) == 0) OR ($userTipo == 33 AND strlen($userRA) == 0)){
        Alert('Existem divergências nos dados de CPF ou Matrícula do usuário.'); goto Fim;
    }

    // VALIDA O CPF E O RA
    $userCPF = str_replace(['.','-'],'',$userCPF);
    $userRA  = ($userTipo == 33)?CheckRA($userRA):'';

    // VALIDA O CPF
    if(strlen($userCPF) AND $userCPF != $findUser['ui_doc']){
        $User -> setCPF($userCPF);
        $CheckCPF = $User -> CheckUser();
        if(is_array($CheckRA) AND is_numeric(@$CheckRA['lid'])){
            Alert('O número de CPF informado pertence a outro usuário.'); goto Fim;
        }else{
            $Upg = $db -> prepare("UPDATE login SET login_user = ?, login_dref = NOW() WHERE login_id = ? LIMIT 1");
            $Upg -> bind_param("si",$userCPF,$userLogin);
            if(!$Upg->execute()){$C++;}
        }
    }

    // VERIFICA O RA INFORMADO
    if(strlen($userRA) AND $userRA != $findUser['ui_matricula']){
        $User -> setRA($userRA);
        $CheckRA = $User -> CheckUser();
        if(is_array($CheckRA) AND is_numeric(@$CheckRA['lid'])){
            Alert('O número de matricula informado pertence a outro usuário.'); goto Fim;
        }else{
            $Upg = $db -> prepare("UPDATE login SET login_user = ?, login_dref = NOW() WHERE login_id = ? LIMIT 1");
            $Upg -> bind_param("si",$userCPF,$userLogin);
            if(!$Upg->execute()){$C++;}
        }
    }

   // ALTERA OS DADOS DO USUARIO
   $Upg = $db -> prepare("UPDATE userinfo SET 
        ui_doc = ?,
        ui_matricula = ?,
        ui_nome = ?,
        ui_nascimento = ?,
        ui_sexo = ?,
        ui_tel = ?,
        ui_email = ?,
        ui_city = ?,
        ui_endereco = ?,
        ui_nomepai = ?,
        ui_telpai = ?,
        ui_notificar_pai = ?,
        ui_nomemae = ?,
        ui_telmae = ?,
        ui_notificar_mae = ?
    WHERE ui_login = ? LIMIT 1"); 
    $Upg -> bind_param("ssssississsissii",
        $userCPF,
        $userRA,
        $userNome,
        $userNascimento,
        $userSexo,
        $userTel,
        $userEmail,
        $userCidade,
        $userEndereco,
        $userNomePai,
        $userTelPai,
        $userNotificarPai,
        $userNomeMae,
        $userTelMae,
        $userNotificarMae,
        $userLogin
    );
    if(!$Upg->execute()){$C++;}
    shdr("secretaria/usuario/cadastro/$userID");

goto Status;}
if($URI[1]=='tutoria-transferencia'){
    $Dados['P']['N'] = $novotutor;
	foreach($P as $K1=>$V1){ 
        if(strstr($K1,'tut-')){
            $Dados['P'][] = $V1; 
            $Dados['U'][$V1] = str_replace('tut-','',$K1); 
        }
    }
	// PROCURA OS USUÁRIOS SE TEM RELAÇÃO COM A INSTITUIÇÃO
    $User = new Usuario();
    $User -> id = $Dados['P'];
	$findUser = $User -> findUser();
	foreach($Dados['U'] as $K1=>$V1){ $Key = true;
		foreach($findUser as $K2=>$V2){ if($V2['user_id'] == $K1){ $Key = false; }}
		if($Key){ unset($Dados['U'][$K1]); }
	}
	// PROMOVE A ALTERAÇÃO DE TUTORIA
	$Upg = $db -> prepare("UPDATE tutoria SET tut_tutor = ? WHERE tut_id = ? LIMIT 1");
	foreach($Dados['U'] as $K1=>$V1){
		$Upg -> bind_param("ii",$novotutor,$V1);
		if(!$Upg->execute()){$C++;}
    }
    shdr('tutoria/gerenciar');
goto Status;}
if($URI[1]=='ocorrencia-devolutiva'){

    $FindOC = findOcorrencia($oc,$est);
    if(!is_array($FindOC) OR $FindOC['oc_id'] != $oc OR $FindOC['oc_estudante'] != $est OR md5($FindOC['oc_dref']) != $checksum){
        Alert('Acesso negado a ocorrência.<br/>Você não tem permissão para realizar a devolutiva.'); $C++;
    
    }else{
        
        $devolutiva = strip_tags($devolutiva);
        $Upg = $db -> prepare("UPDATE ocorrencias SET oc_devolutiva = ?, oc_dref = NOW(), oc_sit = 1, oc_dev = 2 WHERE oc_id = ? AND oc_estudante = ?");
        $Upg -> bind_param("sii",$devolutiva,$oc,$est);
        if(!$Upg->execute()){$C++;}

    }
    shdr("tutoria/ocorrencia/abrir/$est/$oc");

goto Status;}
// ----------------------------------------------- FIM USUÁRIO

// ----------------------------------------------- INÍCIO DIVERSIFICADA
// ENVIO DE DEVOLUTIVA DE ESTUDO ORIENTADO
if($URI[1]=='estudo-orientado-devolutiva'){
    
    // PROMOVE A VERIFICAÇÃO DO VP PARA TER CERTEZA QUE É PROFESSOR DA COMPONENTE E AUTORIZAR AS ALTERAÇÕES
    $findVP = findVP($vp); if(!is_array($findVP) OR !array_key_exists('users_id',$findVP) OR !in_array($MEUID,$findVP['users_id'])){
        Alert('Acesso Negado!'); goto Fim;
    }

    // PARAMETRIZA A BASE
    $findEOList = findEOList($vp,$data); if(!array_key_exists('map',$findEOList) OR count($findEOList['map']) == 0){ 
        // VERICA A AUTENTICIDADE
        $C=1; shdr("estudo-orientado/$vp"); goto Status;
    } foreach($findEOList['map'] as $K0=>$V0){ foreach($V0 as $K1=>$V1){ $Dados['B'][$V1['eol_id']] = $V1['eol_sit']; }}

    // PARAMETRIZA O POST
    foreach($P as $K=>$V){ if(strstr($K,'eol-')){ $Dados['P'][str_replace('eol-',NULL,$K)] = $V; }}

    // AUTUALIZA, CASO NECESSÁRIO
    $Upg = $db -> prepare("UPDATE eo_listagem SET eol_sit = ? WHERE eol_id = ? LIMIT 1");
    foreach($Dados['P'] as $K=>$V){
        if(array_key_exists($K,$Dados['B']) AND $Dados['B'][$K] != $V){
            $Upg -> bind_param("ii",$V,$K);
            if(!$Upg->execute()){$C++;}
        }
    }

    shdr("turmas/estudo-orientado/$vp/abrir/$data");

goto Status;}
// ENVIO DE ESTUDO ORIENTADO
if($URI[1]=='estudo-orientado-enviar'){
    // BUSCA OS DADOS NA BASE
    $Dados['B'] = findEOEdit($vp,$data);
    // PROMOVE A VERIFICAÇÃO DOS DADOS VINDO DA BASE
    if(!is_array($Dados['B']) OR !array_key_exists('eoa_id',$Dados['B'])){ 
        $C=1; shdr("turmas/estudo-orientado/$vp/enviar"); goto Status;
    }
    // PARAMETRIZA O POST
    $Dados['P'] = [
        'info'  => TextTag($info),
        'files' => [],
        'users' => [],
    ];
    // PARAMETRIZA OS DADOS
    foreach($P as $K=>$V){
        // ARQUIVO
        if(strstr($K,'fl-')){ $Dados['P']['files'][] = $V; }
        if(strstr($K,'vt-user')){  $Dados['P']['users'][$V] = @$P["vt-info-$V"]; }
    }
    $Dados['P']['files'] = array_unique($Dados['P']['files']);
    $Dados['P']['files'] = implode(',',$Dados['P']['files']);

    // ATUALIZA AS INFORMAÇÕES DA ATIVIDADE
    $Upg = $db -> prepare("UPDATE eo_atividades SET eoa_info = ?, eoa_files = ?, eoa_dref = NOW() WHERE eoa_vp = ? AND eoa_id = ?");
	$Upg -> bind_param("ssii",$Dados['P']['info'],$Dados['P']['files'],$vp,$Dados['B']['eoa_id']);
	if(!$Upg->execute()){$C++;}
		
    // PARAMETRIZA O SQL
    $Upg = $db -> prepare("UPDATE eo_listagem SET eol_maisinfo = ?, eol_dref = NOW() WHERE eol_vt = ? AND eol_atv = ?");
    $Ins = $db -> prepare("INSERT INTO eo_listagem (eol_vt,eol_atv,eol_maisinfo) VALUES (?,?,?)");
    $Del = $db -> prepare("DELETE FROM eo_listagem WHERE eol_atv = ? AND eol_vt = ? LIMIT 1");
		
    // INICIALIZA A VERIFICAÇÃO COM O BANCO DE DADOS
    foreach($Dados['P']['users'] as $K1=>$V1){
        // VERIFICA SE EXISTE REGISTRO NA BASE DE DADOS
        if(array_key_exists($K1,$Dados['B']['map'])){
            // VERIFICA SE EXISTE ALTERAÇÃO NA INFORMAÇÃO ADICIONAL PASSADA PELO PROFESSOR
            if($V1!=$Dados['B']['map'][$K1]['eol_maisinfo']){
                $Upg -> bind_param("sii",$V1,$K1,$Dados['B']['eoa_id']);
				if(!$Upg->execute()){$C++;}
            }

        // SE NÃO EXISTIR NENHUM REGISTRO NA BASE DE DADOS, ENTÃO TENTA INSERIR O REGISTRO
        }else{
			$Ins -> bind_param("iis",$K1,$Dados['B']['eoa_id'],$V1); 
            if(!$Ins->execute()){$C++;}
        }
    }
    // PROMOVE A VERIFICAÇÃO SE COMPARANDO A BASE COM O POST 
    // E VERIFICANDO SE EXISTE ALGUM REGISTRO NA BASE QUE NÃO VEIO PELO POST
    // SE ISSO OCORRER, REMOVE O REGISTRO
    foreach($Dados['B']['map'] as $K1=>$V1){
        if(!array_key_exists($V1['eol_vt'],$Dados['P']['users'])){
            $Del -> bind_param("ii",$Dados['B']['eoa_id'], $V1['eol_vt']);
            if(!$Del->execute()){$C++;}
        }
    }
    
    shdr("turmas/estudo-orientado/$vp/enviar");
    
goto Status;}
// REMANEJAMENTO DAS ELETIVAS
if($URI[1]=='editar-eletiva'){

    if($MEUTIPO <= 31){

        // PROMOVE A VERIFICAÇÃO DA ELETIVA
        $Elt = findElt($elt); 
        if(!is_array($Elt) OR !array_key_exists('elt_id',$Elt)){
            $C++; Alert('Eletiva não encontrada!'); goto FimEditEletiva;
        }

        // PREPARA OS DADOS
        $Dados['P']['user'] = [];
        $Dados['P']['disc'] = [];
        $Dados['P']['discString'] = '';
        foreach($P as $K=>$V){
            if(strstr($K,'user-')){$Dados['P']['user'][] = $V;}
            if(strstr($K,'disc-')){$Dados['P']['disc'][] = $V;}
        }
        $Dados['P']['user'] = array_unique($Dados['P']['user']);
        $Dados['P']['disc'] = array_unique($Dados['P']['disc']);
        $Dados['P']['discString'] = implode(',',$Dados['P']['disc']);
        $eltNome = strip_tags($eltNome);

        if(count($Dados['P']['user'])==0){ Alert('Nenhum(a) professor(a) selecionado(a).'); goto FimEditEletiva; }
        if(count($Dados['P']['disc'])==0){ Alert('Nenhuma componente curricular selecionada.'); goto FimEditEletiva; }

        // ATUALIZA OS DADOS DOS PROFESSORES NO VINCULO
        $Ins = $db -> prepare("INSERT INTO vinc_prof_user (vpu_user,vpu_vp) VALUES (?,?)");
        $Del = $db -> prepare("DELETE FROM vinc_prof_user WHERE vpu_user = ? AND vpu_vp = ? AND YEAR(vpu_dref) = ?");

        // ATUALIZA A ELETIVA OS DADOS DA ELETIVA
        $Upg = $db -> prepare("UPDATE eletivas SET elt_nome = ?, elt_disc = ?, elt_periodo = ?, elt_dref = NOW() WHERE elt_id = ? AND elt_secretaria = ? AND YEAR(elt_dref) = ?");
        $Upg -> bind_param("ssiiii",$eltNome,$Dados['P']['discString'],$eltPeriodo,$elt,$MYSCT,$ANOBASE);
        if($Upg -> execute()){
            // ATUALIZA OS PROFESSORES
            // VERIFICA SE TODOS OS PROFESSORES INFORMADOS CONSTAM NA LISTAGEM DA ELETIVA
            foreach($Dados['P']['user'] as $KeyP=>$ViewP){
                // SE NÃO EXISTIR, INSERE
                if(!array_key_exists($ViewP,$Elt['prof'])){
                    $Ins -> bind_param("ii",$ViewP,$Elt['vp_id']);
                    if(!$Ins->execute()){$C++;}
                }
            }
            // VERIFICA SE ALGUM PROFESSOR PRECISA SER REMOVIDO
            foreach($Elt['prof'] as $KeyP=>$ViewP){
                if(!in_array($KeyP,$Dados['P']['user'])){
                    $Del -> bind_param("iii",$KeyP,$Elt['vp_id'],$ANOBASE);
                    if(!$Del->execute()){$C++;}
                }
            }

        }else{$C++;}
    }else{$C++;}

    FimEditEletiva:
    shdr(('diversificada/eletiva'));	

goto Status;}
if($URI[1]=='eletiva-remanejar'){
    
    $EltAtual = findElt($atuElt);
    $EltReman = findElt($remElt);
    // VERIFICA SE AS ELETIVAS PERTENCEM AO SCT
    if(@$EltAtual['elt_secretaria'] != $MYSCT OR @$EltReman['elt_secretaria'] != $MYSCT){
        Alert('As eletivas não pertencem a sua instituição!'); goto Fim;
    }
    
    // CRIA O VETOR DO POST
    foreach($P as $K=>$V){
        if(strstr($K,'eltv-')){
            // CRIA O VETOR
            $Key = str_replace('eltv-','',$K);
            $Dados['P'][$Key] = ['user'=>$V,'eltv'=>$Key,'sit'=>0];
            // VERIFICA SE O USUÁRIO É DE FATO DAQUELA ELETIVA
            $Dados['P'][$Key]['sit'] = (array_key_exists($V,$EltAtual['map'])) ? 2 : 0;
            // VERIFICA SE O USUÁRIO NÃO ESTÁ NAQUELA OUTRA ELETIVA
            $Dados['P'][$Key]['sit'] = (array_key_exists($V,$EltReman['map'])) ? 3 : $Dados['P'][$Key]['sit'];
        }
    }
    // PARAMETOS SIT
    // 0 - NORMAL, 2 - DA ELETIVA, 3 - NA NOVA
    $Ins = $db -> prepare("INSERT INTO eletivas_vinc (eltv_elt,eltv_user) VALUES (?,?)"); dbE();
	$Upg = $db -> prepare("UPDATE eletivas_vinc SET eltv_sit = ?, eltv_remanejado = (NOW()), eltv_dref = NOW() WHERE eltv_elt = ? AND eltv_user = ? AND YEAR(eltv_dref) = ? LIMIT 1"); dbE();

    foreach($Dados['P'] as $K=>$V){
        // FAZ PARTE DA VELHA E NÃO FAZ PARTE DA NOVA
        if($V['sit'] == 2){
            // ATUALIZA O SITE DA ELETIVA ATUAL
            $Sit = 1;
            $Upg -> bind_param("iiii",$Sit,$atuElt,$V['user'],$ANOBASE);
            if(!$Upg->execute()){$C++;}
            // CRIA O NOVO VINCULO NA NOVA ELETIVA
            $Ins -> bind_param("ii",$remElt,$V['user']);
            if(!$Ins->execute()){$C++;}
        }
        // FAZ PARTE DA VELHA E FAZ PARTE DA NOVA
        if($V['sit'] == 3){
            $Sit = 1;
            $Upg -> bind_param("iiii",$Sit,$atuElt,$V['user'],$ANOBASE);
            if(!$Upg->execute()){$C++;}
            $Sit = 0;
            $Upg -> bind_param("iiii",$Sit,$remElt,$V['user'],$ANOBASE);
            if(!$Upg->execute()){$C++;}
        }
    }
		
    shdr("diversificada/eletiva/membros/$periodo/$atuElt");
goto Status;}
// ALTERA O PLANO DE AÇÃO DO ESTUDANTE
if($URI[1]=='pv-meu-plano'){
	
    $PVP = findPVPlano();
    
	// MAPEIA O POST
	$Dados['P']['campos']['atualizado'] = Data();
	foreach($P as $K1=>$V1){
		if(strstr($K1,'pvp-')){ $Dados['P']['campos'][str_replace('pvp-','',$K1)] = strip_tags($V1,false);}
		if(strstr($K1,'IRP-')){ $Dados['P']['ind'][0][str_replace('IRP-','',$K1)] = $V1;}
		if(strstr($K1,'IPP-')){ $Dados['P']['ind'][1][str_replace('IPP-','',$K1)] = $V1;}
		if(strstr($K1,'IAA-')){
			$IAA = str_replace('IAA-','',$K1);
			if(strlen($IAA)){
				$Dados['P']['ind'][2][$IAA] = [
					'acao' => $V1,
					'estrategia' => (isset($P["IEA-$IAA"]))?$P["IEA-$IAA"]:null,
					'maxdata' => (isset($P["IED-$IAA"]))?Data($P["IED-$IAA"]."/$ANOBASE",2):null,
					'sit' => (isset($P["IES-$IAA"]))?$P["IES-$IAA"]:null
				];
			}
		}
	}
	// ATUALIZA OS CAMPOS
	$Upg = $db -> prepare("UPDATE pv_plano SET pvp_valor = ?, pvp_dref = NOW() WHERE pvp_user = ? AND pvp_campo = ?"); 
	$Ins = $db -> prepare("INSERT INTO pv_plano (pvp_user,pvp_campo,pvp_valor) VALUES (?,?,?)"); 
	if(isset($Dados['P']['campos'])){ foreach($Dados['P']['campos'] as $K1=>$V1){
		if(array_key_exists($K1,$PVP)){
			if($PVP[$K1] != $V1){
				// ATUALIZA
				$Upg -> bind_param("sis",$V1,$MEUID,$K1); if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
			}
		}else{
			// INSERE
			$Ins -> bind_param("iss",$MEUID,$K1,$V1); if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
		}
	}}

	// ATUALIZA OS ITENS
	if(isset($Dados['P']['ind'])){
		// INSERINDO OU ATUALIZANDO
		$Upg1 = $db -> prepare("UPDATE pv_plano_itens SET pvpi_info_1 = ?, pvpi_dref = NOW() WHERE pvpi_user = ? AND pvpi_id = ?");
		$Ins1 = $db -> prepare("INSERT INTO pv_plano_itens (pvpi_user,pvpi_tipo,pvpi_info_1,pvpi_info_2,pvpi_key) VALUES (?,?,?,?,?)");
		$Upg2 = $db -> prepare("UPDATE pv_plano_itens SET pvpi_sit = ?, pvpi_info_1 = ?, pvpi_info_2 = ?, pvpi_maxdata = ?, pvpi_dref = NOW() WHERE pvpi_user = ? AND pvpi_id = ?");
		$Ins2 = $db -> prepare("INSERT INTO pv_plano_itens (pvpi_user,pvpi_tipo,pvpi_sit,pvpi_info_1,pvpi_info_2,pvpi_maxdata,pvpi_key) VALUES (?,?,?,?,?,?,?)");
		foreach($Dados['P']['ind'] as $K1=>$V1){
			foreach($V1 as $K2=>$V2){
				// ITENS
				if($K1 == 0 OR $K1 == 1){
					if(is_numeric($K2)){
						if(isset($PVP['map'][$K1][$K2]) AND $V2 != $PVP['map'][$K1][$K2]){
							$Upg1 -> bind_param("sii",$V2,$MEUID,$K2);
							if(!$Upg1->execute()){$C++;}else{$Dados['PRO']['U']++;}
						}
					}else{
						$Ins1 -> bind_param("iisss",$MEUID,$K1,$V2,$VZO,$K2);
						if(!$Ins1->execute()){$C++;}else{$Dados['PRO']['I']++;}
					}
				}
				// ACOES
				if($K1 == 2){
					if(is_numeric($K2)){
						$Upg2 -> bind_param("isssii",$V2['sit'],$V2['acao'],$V2['estrategia'],$V2['maxdata'],$MEUID,$K2);
						if(!$Upg2->execute()){$C++;}else{$Dados['PRO']['U']++;}
					}else{
						$Ins2 -> bind_param("iiissss",$MEUID,$K1,$V2['sit'],$V2['acao'],$V2['estrategia'],$V2['maxdata'],$K2);
						if(!$Ins2->execute()){$C++;}else{$Dados['PRO']['I']++;}
					}
				}
			}
		}
	}
	// PROCURA ITENS PARA REMOÇÃO
	$Rmv = $db -> prepare("DELETE FROM pv_plano_itens WHERE pvpi_user = ? AND pvpi_id = ? AND pvpi_tipo = ?");
	foreach($PVP['map'] as $K1=>$V1){
		foreach($V1 as $K2=>$V2){
			if(!isset($Dados['P']['ind'][$K1][$K2])){
				$Rmv -> bind_param("iii",$MEUID,$K2,$K1);
				if(!$Rmv->execute()){$C++;}else{$Dados['PRO']['D']++;}
			}
		}
	}
	shdr('academico/programa-de-acao');

goto Status;}
	
// ----------------------------------------------- FIM DIVERSIFICADA

// ----------------------------------------------- INÍCIO FINANCEIRO
if($URI[1]=='cantina-produtos'){
    if($MEUTIPO <= 31){

        // PARAMETRIZA O POST
        foreach($P as $KeyP=>$ViewP){
            if(strstr($KeyP,'csp-valor-')){
                $Pid = str_replace('csp-valor-','',$KeyP);
                $Dados['P'][$Pid] = [
                    'nome' => ((isset($P["csp-nome-$Pid"]))?$P["csp-nome-$Pid"]:NULL),
                    'info' => ((strlen($P["csp-info-$Pid"]))?$P["csp-info-$Pid"]:NULL),
                    'valor' => ((isset($P["csp-valor-$Pid"]))?number_format($P["csp-valor-$Pid"],2):'0.00'),
                    'ativo' => ((isset($P["csp-ativo-$Pid"]))?1:0)
                ];
            }
        }
        // PARAMETRIZA A BASE
        $Cantina = new Cantina();
        $Dados['B'] = $Cantina -> Produtos();

        // PARAMETRIZA O SQL
        $Ins = $db -> prepare("INSERT INTO cash_cantina_produtos (csp_secretaria,csp_nome,csp_info,csp_valor,csp_sit) VALUES (?,?,?,?,?)");
        $Upg = $db -> prepare("UPDATE cash_cantina_produtos SET csp_info = ?, csp_valor = ?, csp_sit = ?, csp_dref = NOW() WHERE csp_id = ? AND csp_secretaria = ? LIMIT 1");
        $Del = $db -> prepare("UPDATE cash_cantina_produtos SET csp_sit = 2, csp_dref = NOW() WHERE csp_id = ? AND csp_secretaria = ? LIMIT 1");

        // EXECUTA INSERÇÃO E ATUALIZAÇÃO
        foreach($Dados['P'] as $KeyP=>$ViewP){
            // VERIFICA SE A CHAVE EXISTE NO BANCO DE DADOS
            if(array_key_exists($KeyP,$Dados['B'])){
                // COMPARA OS DADOS PARA SABER SE PRECISA ATUALIZAR
                if(
                    $ViewP['info'] != $Dados['B'][$KeyP]['csp_info'] OR 
                    $ViewP['valor'] != $Dados['B'][$KeyP]['csp_valor'] OR 
                    $ViewP['ativo'] != $Dados['B'][$KeyP]['csp_sit']
                ){
                    $Upg -> bind_param("sdiii",$ViewP['info'],$ViewP['valor'],$ViewP['ativo'],$KeyP,$MYSCT);
                    if(!$Upg->execute()){$C++;}
                }
            }else{
                // VERIFICA SE A CHAVE NÃO É UM NUMERO, SE FOR, JA EXISTE NO BANCO
                if(!is_numeric($KeyP)){
                    // INSERE OS DADOS
                    $Ins -> bind_param("issdi",$MYSCT,$ViewP["nome"],$ViewP['info'],$ViewP['valor'],$ViewP['ativo']);
                    if(!$Ins->execute()){$C++;}
                }
            }
        }

        // VERIFICA SE FOI "REMOVIDO" PARA ATUALIZAR O SIT PARA 2 (DELETADO)
        foreach($Dados['B'] as $KeyB=>$ViewB){
            if(!array_key_exists($KeyB,$Dados['P'])){
                $Del -> bind_param("ii",$KeyB,$MYSCT);
                if(!$Del->execute()){$C++;}
            }
        }

    }else{Alert('Acesso Negado'); $C++;}
    shdr('financeiro/cantina/editar-produtos');
goto Status;}
if($URI[1]=='cantina-pagar'){

    $User = new Usuario();
    $User -> id = $URI[3];
    $findUser = $User -> findUser();
    
    if(is_array($findUser) AND array_key_exists('user_id',$findUser)){

        $SMS = new SMS();
        $SMS -> user = $URI[3];
        
        $Cantina = new Cantina();
        $Cantina -> info = "Pagamento Integral Realizado no dia " . Data(null,3) . " às " . Data(null,6) . '. Registro realizado por: ' . $MS["nome"] . '.';

        
        // PAGAMENTO INTEGRAL DA CONTA
        if($URI[2]=='user'){    
            $Cantina -> user = $URI[3];
            if(!$Cantina->PagarVenda()){$C++;}else{
                if(isset($ES['notificar-cantina']) AND $ES['notificar-cantina'] == 1){
                    $SMS -> lac = ['VALOR'=> number_format(str_replace(',','.',$URI[4]),2,',','.')];
                    $SMS -> tipo = 14;
                    $SMS -> Render();	
                }
            }
        }

        if($URI[2]=='nota'){
            $Cantina -> id = $URI[4];
            if(!$Cantina->PagarVenda()){$C++;}else{
                if(isset($ES['notificar-cantina']) AND $ES['notificar-cantina'] == 1){
                    $SMS -> lac = ['VALOR'=> number_format(str_replace(',','.',$URI[5]),2,',','.'),'NOTA'=>$URI[4]];
                    $SMS -> tipo = 15;
                    $SMS -> Render(); exit;
                }
            }
        }

    }else{ Alert('Estudante não localizado!'); $C++; }

    shdr("financeiro/cantina/devedores");
goto Status;}
// ----------------------------------------------- FIM FINANCEIRO



// ----------------------------------------------- INÍCIO AGENDAS
// MINHA AGENDA
if($URI[1]=='minha-agenda'){

    // PROCURA NA BASE
	$sex = eSex($seg);
	$Base = $db -> prepare("SELECT * FROM agenda_servidor WHERE ag_user = ? AND (DATE(ag_data) BETWEEN ? AND ?) AND YEAR(ag_dref) = ?");
	$Base -> bind_param('issi',$MEUID,$seg,$sex,$ANOBASE);
	$Base -> execute();
	foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){ $Dados['B'][$V1['ag_id']] = $V1; }
	// CARREGA O POST
	foreach($P as $K1=>$V1){
		$IID = explode('-',$K1);

        if($mod==1){
            if(strstr($K1, 'i-') AND strlen($V1)) {
                // MODELO TABELA    // H-LINHA      // I-LINHA-DIA-ID
                // PROCURA A HORA (LINHA)
                @$H = $P["h-$IID[1]"];
                // VERIFICA SE A HORÁ NÃO ESTÁ VAZIA
                if(strlen($H)){
                    // PROCURA OS 5 DIAS
                    $Dados['P'][$IID[3]] = ['ag_data' => (date('Y-m-d', strtotime("$seg +" . ($IID[2] - 1) . " days")) . " $H:00"), 'ag_info' => strip_tags($V1)];
                }

            }
        }else{

            if(strstr($K1,'h-')){if(strlen($V1)){
			// NOVO MODELO
			// H-DIA-ID
				$Dados['P'][$IID[2]] = ['ag_data'=> (date('Y-m-d',strtotime("$seg +".($IID[1]-1)." days"))." $V1:00"),'ag_info'=>strip_tags($P["i-".$IID[1]."-".$IID[2]])];
			}}
        }
	}

	// PREPARA
	$Ins = $db -> prepare("INSERT INTO agenda_servidor (ag_user,ag_data,ag_info,ag_key) VALUES (?,?,?,?)");
	$Upg = $db -> prepare("UPDATE agenda_servidor SET ag_info = ?, ag_data = ?, ag_dref = NOW() WHERE ag_user = ? AND ag_id = ? AND YEAR(ag_dref) = ?");
	$Del = $db -> prepare("DELETE FROM agenda_servidor WHERE ag_id = ? AND ag_user = ? AND YEAR(ag_dref) = ? LIMIT 1");
	
    // PROMOVE A VERIFICAÇÃO
	foreach($Dados['P'] as $K1=>$V1){
		// SE FOR NUMERO, TA NA BASE
		if(array_key_exists($K1,$Dados['B'])){
			// VERIFICA SE HOUVE ALTERAÇÃO
			if($V1['ag_info'] != $Dados['B'][$K1]['ag_info'] OR $V1['ag_data'] != $Dados['B'][$K1]['ag_data']){
				$Upg -> bind_param('ssiii',$V1['ag_info'],$V1['ag_data'],$MEUID,$K1,$ANOBASE);
				if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
			}
		}else{
			// PROCURA A CHAVE DE ACESSO PARA VER SE NÃO ESTÁ NA BASE
			$Key = false; foreach($Dados['B'] as $K2=>$V2){ if(is_numeric($K2)  AND $K1 == $V2['ag_key']){$Key = $V2['ag_id']; break; }}
			// SE LOCALIZAR FAZ O UPDATE
			if(is_numeric($Key)){
				if($V1['ag_info'] != $Dados['B'][$Key]['ag_info'] OR $V1['ag_data'] != $Dados['B'][$Key]['ag_data']){
					$Upg -> bind_param('ssiii',$V1['ag_info'],$V1['ag_data'],$MEUID,$Key,$ANOBASE);
					if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
				}
			}else{
				// SE NÃO, FAZ O INSERT
				$Ins -> bind_param("isss",$MEUID,$V1['ag_data'],$V1['ag_info'],$K1);
				if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
			}
		}
	}
	// PROCURA INFORMAÇÕES PARA DELETAR
	foreach($Dados['B'] as $K1=>$V1){ if(is_numeric($K1) AND !array_key_exists($K1,$Dados['P'])){
		$Del -> bind_param('iii',$K1,$MEUID,$ANOBASE);
		#if(!$Del -> execute()){$C++;}else{$Dados['PRO']['D']++;}
	}}

	// REGISTRA NAS AÇÕES
	// if($C==0 AND array_sum($Dados['PRO'])>0){
	// 	$Dados['PRO']['A'] = $MS['nome'];
	// 	$Dados['PRO']['DIA'] = Data($seg,3);
	// 	ActionReg(26,false,$Dados['PRO']);
	// }

	shdr("calendario/agenda/$sem");
goto Status;}
// ----------------------------------------------- FIM AGENDAS

// ----------------------------------------------- INICIO TURMAS
if($URI[1]=='enturmar-professor'){
    foreach($P as $KeyP=>$ViewP){if(strstr($KeyP,'user-')){$Dados['P'][] = $ViewP;}}
    $Dados['B'] = @findVP($P['vp'])['users_id'];

    if(is_array($Dados['B'])){ 
        // VERIFICA QUEM TEM QUE INSERIR
        foreach($Dados['P'] as $KeyD=>$ViewD){if(!in_array($ViewD,$Dados['B'])){$Dados['E'][] = $ViewD;}}
        // VERIFICA QUEM TEM QUE REMOVER
        foreach($Dados['B'] as $KeyD=>$ViewD){if(!in_array($ViewD,$Dados['P'])){$Dados['D'][] = $ViewD;}}

        $Ins = $db -> prepare("INSERT INTO vinc_prof_user (vpu_vp, vpu_user) VALUES (?,?)");
        $Del = $db -> prepare("DELETE FROM vinc_prof_user WHERE vpu_vp = ? AND vpu_user = ? AND YEAR(vpu_dref) = ? LIMIT 1");

        // INSERINDO 
        foreach($Dados['E'] as $KeyD=>$ViewD){
            $Ins -> bind_param("ii",$P['vp'],$ViewD);
            if(!$Ins->execute()){$C++;}
        }

        
        if(count($Dados['D'])){
            $Dados['B'] = @findVP($P['vp'])['users_id'];
            if($C==0 AND (count($Dados['B']) - count($Dados['D'])) > 0){
                foreach($Dados['D'] as $KeyD=>$ViewD){
                    $Del -> bind_param("iii",$P['vp'],$ViewD,$ANOBASE);
                    if(!$Del->execute()){$C++;}
                }
            }else{
                Alert('A quantidade mínima de professor associado deve ser 1.'); $C++;
            }
        }
    }else{ Alert('Nenhum vínculo informado.');  $C++; }
    shdr("secretaria/enturmar/professor/editar/" . $P['vp']);
goto Status;}
if($URI[1]=='perfil-turma'){

    // PERFIL DE TURMA, SALVO PELO PROFESSOR
    if($URI[2]=='avaliar'){

        // VERIFICA SE ESTÁ ABERTO PARA EDIÇÃO
        if(isset($ES["perfil-$MEUTURNO-periodo"]) AND is_numeric($ES["perfil-$MEUTURNO-periodo"])){

            // VERIFICA O VINCULO DO PROFESSOR COM A TURMA
            $KeyTurma = false; foreach(VincProfMap($MEUID) as $Key=>$Val){ if($Val['vp_turma']==$turma){ $KeyTurma=true; break; }}
            if($KeyTurma){

                // MAPEIA O POST 
                foreach($P as $K=>$V){ if(strstr($K,'IP')){ $Dados['P'][str_replace('IP','',$K)] = $V; }}
                $Dados['P']['info'] = strip_tags($info);
                // MAPEIA A BASE
                @$PFTri = $ES["perfil-$MEUTURNO-periodo"];
                @$Dados['B'] = findPFT($turma,$MEUID)[$PFTri];

                // PARAMETRIZA O SQL
                $UpgV = $db -> prepare("UPDATE turmas_perfil_reg SET pftr_valor = ?, pftr_dref = NOW() WHERE pftr_user = ? AND pftr_turma = ? AND pftr_tri = ? AND YEAR(pftr_dref) = ? AND pftr_pergunta = ?");
                $UpgI = $db -> prepare("UPDATE turmas_perfil_info SET pfti_info = ?, pfti_dref = NOW() WHERE pfti_user = ? AND pfti_turma = ? AND pfti_tri = ? AND YEAR(pfti_dref) = ?");
                $InsV = $db -> prepare("INSERT INTO turmas_perfil_reg  (pftr_user,pftr_turma,pftr_pergunta,pftr_valor,pftr_tri) VALUES (?,?,?,?,?)");
                $InsI = $db -> prepare("INSERT INTO turmas_perfil_info (pfti_user, pfti_turma, pfti_info, pfti_tri) VALUES (?,?,?,?)");
                // VERIFICA SE EXISTE NA BASE COM BASE NO POST
                foreach($Dados['P'] as $K=>$V){
                    // SE EXISTIR, VERIFICA SE PRECISA ATUALIZAR
                    if(array_key_exists($K,$Dados['B'])){
                        if($Dados['B'][$K] != $V){
                            if(is_numeric($K)){
                                // SE FOR PERGUNTA
                                $UpgV -> bind_param('iiiiii',$V,$MEUID,$turma,$PFTri,$ANOBASE,$K);
                                if(!$UpgV -> execute()){$C++;}else{$Dados['PRO']['U']++;}
                            }else{
                                // SE FOR INFO
                                $UpgI -> bind_param("siiii",$V,$MEUID,$turma,$PFTri,$ANOBASE);
                                if(!$UpgI -> execute()){$C++;}else{$Dados['PRO']['U']++;}
                            }
                        }
                    }else{
                        // SE NÃO EXISTIR NA BASE, INSERE
                        if(is_numeric($K)){
                            // SE FOR PERGUNTA
                            $InsV -> bind_param("iiiii",$MEUID,$turma,$K,$V,$PFTri);
                            if(!$InsV->execute()){$C++;}else{$Dados['PRO']['I']++;}
                        
                        }else{
                            // SE FOR INFO
                            $InsI -> bind_param("iisi",$MEUID,$turma,$V,$PFTri);
                            if(!$InsI->execute()){$C++;}else{$Dados['PRO']['I']++;}
                        }
                    }
                }

            }else{$C++;}
        }else{$C++;}

        shdr("socioemocional/turma/perfil/$turma" . ($C==0?NULL:'/avaliar'));
    }


    // PERGUNTAS SALVAS
    if($URI[2]=='perguntas'){

        // MAPEIA O POST
        $Dados['P'] = ['perg'=>[], 'altp' => []];
        foreach($P as $K=>$V){
            $ID = explode('-',$K);
            if($ID[0]=='perg'){ $Dados['P']['perg'][$ID[1]][$ID[2]] = strip_tags($V); }
            if($ID[0]=='altp-'){ $Dados['P']['altp'][] = $ID[1]; }
        }
        // MAPEIA A BASE
        $Dados['B'] = PerfilPerguntasMap(); 
        
        // PREPARA O SQL
        $Upg = $db -> prepare("UPDATE turmas_perfil SET pft_texto = ?, pft_dref = NOW() WHERE pft_id = ? AND pft_secretaria = ? AND YEAR(pft_dref) = ?");
        $Ins = $db -> prepare("INSERT INTO turmas_perfil (pft_secretaria,pft_pilar,pft_texto) VALUES (?,?,?)");
        $Del = $db -> prepare("DELETE FROM turmas_perfil WHERE pft_id = ? AND pft_secretaria = ? AND YEAR(pft_dref) = ?");

        // EXECUTA AS ACOES
        foreach($Dados['P']['perg'] as $K=>$V){ // K = PILAR
            foreach($V as $K1=>$V1){
                // VERIFICA SE É NUMERO (SE FOR, PODE EXISTIR NA BASE)
                if(is_numeric($K1)){
                    // VERIFICA SE EXISTE NA BASE E SE É DIFERENTE DO ORIGINAL E SE PODE SER EDITADO
                    if(@$Dados['B']['qtres'][$K1] == 0 AND isset($Dados['B']['perg'][$K][$K1]) AND $Dados['B']['perg'][$K][$K1] != $V1){
                        $Upg -> bind_param("siii",$V1,$K1,$MYSCT,$ANOBASE);
                        if(!$Upg -> execute()){$C++;}
                    }
                }else{
                    // SE NÃO FOR NUMERO, INSERE
                    $Ins -> bind_param("iis",$MYSCT,$K,$V1);
                    if(!$Ins->execute()){$C++;}
                }
            }
        }

        // PROCURA ELEMENTOS PARA REMOÇÃO, CASO POSSIVEL
        foreach($Dados['B']['perg'] as $K=>$V){if(is_numeric($K)){ // K = PILAR
            foreach($V as $K1=>$V1){
                if(@$Dados['B']['qtres'][$K1] == 0 AND !isset($Dados['P']['perg'][$K][$K1])){
                    $Del -> bind_param("iii",$K1,$MYSCT,$ANOBASE);
                    if(!$Del->execute()){$C++;}
                }
            }
        }}

        shdr('socioemocional/turma/perfil/perguntas');
    }


goto Status;}
if($URI[1]=='editar-vinculo'){
    
    #ppre($P); exit;

    $findTurma = findTurma($VincTurma);
    if(is_array($findTurma) AND array_key_exists('turma_secretaria',$findTurma) AND $findTurma['turma_secretaria'] == $MYSCT){

        

        // PROMOVE O REMANEJAMENTO
        if($EditVinc == 1 AND isset($NovaTurma) AND is_numeric($NovaTurma)){
            
            // VERIFICA A NOVA TURMA
            $findNova = findTurma($NovaTurma);

            if(is_array($findNova) AND array_key_exists('turma_secretaria',$findNova) AND $findNova['turma_secretaria'] == $MYSCT){
                // MAPEIA OS ESTUDANTES DAS TURMAS
                $NewTurmaMap = TurmaEMap($NovaTurma); $Max = 0; foreach($NewTurmaMap as $ViewT){if($ViewT['vt_num'] > $Max){$Max=$ViewT['vt_num'];}} $Max++;
                $AtualTurmaMap = TurmaEMap($VincTurma); 

                // VERIFICA O VINCULO NA TURMA ANTIGA
                if(array_key_exists($VincID,$AtualTurmaMap)){
                    // PEGA O ID DO USUARIO
                    $UserID = $AtualTurmaMap[$VincID]['vt_user']; 
                    // VERIFICA O VINCULO NA TURMA NOVA
                    $VincUserID = false; foreach($NewTurmaMap as $KeyN=>$ViewN){if($ViewN['vt_id'] > 0 AND $ViewN['vt_user'] == $UserID){$VincUserID = $ViewN['vt_id']; break;}}

                    // SE EXISTIR O VINCULO DO USUARIO NA NOVA TURMA, ALTERA AS INFORMAÇÕES SOMENTE
                    if(is_numeric($VincUserID)){

                        $Upg = $db -> prepare("UPDATE vinc_turma SET vt_sit = 0, vt_dref = NOW() WHERE vt_id = ? AND vt_turma = ? LIMIT 1");
                        $Upg -> bind_param("ii",$VincUserID,$NovaTurma);
                        $Action1 = boolval($Upg -> execute());
                    
                    // SE NÃO EXISTIR, CRIA UM NOVO ITEM 
                    }else{

                        $Exe = $db -> prepare("INSERT INTO vinc_turma (vt_user,vt_turma,vt_sit,vt_num) VALUES (?,?,0,?)");
                        $Exe -> bind_param("iii",$UserID,$NovaTurma,$Max);
                        $Action1 = boolval($Exe -> execute());

                    }

                    // VERIFICA SE A AÇÃO NA NOVA TURMA FOI EXECUTADA PARA ALTERAR O STATUS DA TURMA ANTIGA
                    if($Action1){
                        
                        $Upg = $db -> prepare("UPDATE vinc_turma SET vt_sit = 1, vt_remanejado = NOW(), vt_dref = NOW() WHERE vt_id = ? AND vt_turma = ? LIMIT 1");
                        $Upg -> bind_param("ii",$VincID,$VincTurma);
                        if(!$Upg->execute()){$C++;}



                        // --------------------------------------------------------- ALTERAÇÕES
                        
                            $Base = $db -> prepare("SELECT 	vp_turma, vp_id, vp_disc, vp_disc FROM vinc_prof 
                            INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
                            INNER JOIN disciplinas ON (disciplinas.disc_id = vinc_prof.vp_disc)
                            WHERE turmas.turma_secretaria = ? AND vp_turma IN (?,?) AND YEAR(vp_dref) = ? AND disc_area > 0");
                            $Base -> bind_param("iiii",$MYSCT,$VincTurma,$NovaTurma,$ANOBASE);
                            $Base -> execute();
                            foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K1=>$V1){
                                $Map[$V1['vp_disc']][($V1['vp_turma'] == $VincTurma)?0:1] = $V1['vp_id'];
                            }
                
                            // PREPARA
                            $Avi9 = $db -> prepare("SELECT avi_vp, avi_tri, avi_id, avn_id FROM avaliacoes LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id AND avaliacoes_notas.avn_user = ?) WHERE avi_tipo = 9 AND avi_vp = ? AND YEAR(avi_dref) = ?"); dbE();
                            $AviN = $db -> prepare("INSERT INTO avaliacoes (avi_vp, avi_tipo, avi_info, avi_tri, avi_valor) VALUES (?,9,'SECRETARIA',?,0)"); dbE();
                            $AvnU = $db -> prepare("UPDATE avaliacoes_notas SET avn_nota = ? WHERE avn_id = ? AND avn_avi = ?"); dbE();
                            $AvnI = $db -> prepare("INSERT INTO avaliacoes_notas (avn_avi,avn_user,avn_nota,avn_rp) VALUES (?,?,?,-1)"); dbE();
                
                            // MAPEIA OS VINCULOS EM BUSCA DAS NOTAS E AVI TIPO 9
                            foreach($Map as $K1=>$V1){if(count($V1) == 2){
                                
                                // BUSCA AS AVALIACOES DO TIPO 9 NO NOVO VP E REFINA POR TRIMESTRE
                                $Avi9 -> bind_param("iii",$UserID,$V1[1],$ANOBASE);
                                $Avi9 -> execute();
                                $Avi9R = ReKey($Avi9 -> get_result() -> fetch_all(MYSQLI_ASSOC),'avi_tri');
                                
                                
                                // PROCURA AS NOTAS DENTRO DOS TRIMESTRES
                                for($i=1; $i<= (($findTurma['turma_mod'] < 3)?3:2) ; $i++){
                                    // BUSCA AS NOTAS
                                    $AviMap = AviMap($V1[0],$MYSCT,$i);
                                    
                                    // VERIFICA SE EXISTE A NOTA DO ESTUDANTE
                                    if(array_key_exists($UserID,$AviMap['tot'])){
                                        $Nota = $AviMap['tot'][$UserID];
                                        // VERIFICA SE EXISTE UMA NOTA TIPO 9 NO TRI PARA O NOVO VP;
                                        // SE NÃO EXISTIR, A CRIA.
                                        if(!array_key_exists($i,$Avi9R)){
                                            $AviN -> bind_param("ii",$V1[1],$i);
                                            if(!$AviN->execute()){$C++;break;}else{ $Avi9R[$i]['avi_id'] = $AviN -> insert_id; }
                                        }
                                        
                                        // VERIFICA SE EXISTE A NOTA NO AVN PARA AQUELE ESTUDANTE NO NOVO VP
                                        // SE EXISTIR, FAZ O UPDATE
                                        // SE NÃO EXISTIR, CRIA
                                        if(array_key_exists('avn_id',$Avi9R[$i]) AND is_numeric($Avi9R[$i]['avn_id'])){
                                            $AvnU -> bind_param("dii",$Nota,$Avi9R[$i]['avn_id'],$Avi9R[$i]['avi_id']);
                                            if(!$AvnU->execute()){$C++;}
                                            
                                        }else{
                                            $AvnI -> bind_param("iid",$Avi9R[$i]['avi_id'],$UserID,$Nota);
                                            if(!$AvnI->execute()){$C++;}
                                            
                                        }
                                    }
                                }
                            }}
                            // REMOVE TODOS OS EO
                            $EOD = $db -> prepare("DELETE FROM eo_listagem WHERE eol_sit = '0' AND eol_vt = ? AND YEAR(eol_dref) = ?"); $EOD -> bind_param("ii",$VincID,$ANOBASE); if(!$EOD->execute()){$C++;}

                        // --------------------------------------------------------- ALTERAÇÕES

                    }else{$C++;}

                }else{ Alert('O Vínculo que está tentando alterar não foi encontrado.'); $C++; }

            }else{ Alert('Acesso Negado!'); $C++; }

        }


        // PROMOVE A TRANSFERÊNCIA OU INFORMAR ENCERRAMENTO
        if($EditVinc == 2 OR $EditVinc == 3){
            $Upg = $db -> prepare("UPDATE vinc_turma SET vt_sit = ?, vt_remanejado = NOW(), vt_dref = NOW() WHERE vt_id = ? AND vt_turma = ? AND YEAR(vt_dref) = YEAR(NOW()) LIMIT 1");
            $Upg -> bind_param("iii",$EditVinc,$VincID,$VincTurma);
            if(!$Upg->execute()){ $C++; }
        }
    
    }else{ Alert('Acesso Negado!'); $C++; }

    shdr("secretaria/turmas/$VincTurma", ($C==0?0.1:4));

goto Status;}
if($URI[1]=='notas-diversificada'){

    // BUSCA O VP PARA CERTIFICAR A AUTORIZAÇÃO
    $findVP = findVP($vpID);

    // VERIFICA SE SOU PROFESSOR DO VP
	if(is_array($findVP) AND count($findVP) > 0 AND in_array($MEUID,$findVP['users_id'])){
        // MAPEIA O POST
		foreach($P as $K1=>$V1){
			if(strstr($K1,'nota1-')){ $uid = str_replace('nota1-','',$K1);
				$Dados['P'][$uid][0] = $V1;
				$Dados['P'][$uid][1] = (array_key_exists("nota2-$uid",$P))?$P["nota2-$uid"]:-1;
			}
		}
		// VERIFICA SE EXISTE UMA AVI PARA O findVP
		$AviID = NULL; $AVI = $db -> query("SELECT avi_id, avaliacoes_notas.* FROM avaliacoes
		LEFT JOIN avaliacoes_notas ON (avaliacoes_notas.avn_avi = avaliacoes.avi_id)
		WHERE avi_vp = '".$findVP['vp_id']."'".(($findVP['vp_disc']!=29)?" AND avi_tri = '$TRI'":null)) -> fetch_all(MYSQLI_ASSOC);
        // VERIFICA SE FOI ENCONTRADO ALGUMA AVI PARA O PERIODO SELECIONADO E PARA O VP SELECIONADO
		if(is_array($AVI) AND count($AVI)>0){
			foreach($AVI as $K1=>$V1){
				if(!is_numeric($AviID)){$AviID = $V1['avi_id'];}
				if(is_numeric($V1['avn_user'])){
					$Dados['B'][$V1['avn_user']][0] = $V1['avn_nota'];
					$Dados['B'][$V1['avn_user']][1] = $V1['avn_rp'];
				}
			}
		}else{
            // SE NÃO EXISTE UMA AVALIAÇÃO PARA O TIPO, TENTA CRIA-LA.
			$InsInfo = "NOTA ".$findVP['disc_nome'];
			$Ins = $db -> prepare("INSERT INTO avaliacoes (avi_vp,avi_tipo,avi_info,avi_tri,avi_valor) VALUES (?,4,?,?,?)");
			$Ins -> bind_param("isis",$findVP['vp_id'],$InsInfo,$TRI,$ES[$TRI.'tridiversificada']);
			if($Ins -> execute()){$AviID = $Ins->insert_id;}
		}
		// CASO NÃO TENHA O ID DA AVALIACAO
		if(!is_numeric($AviID)){ $C++; goto NotasDiversificadaFim; }

		// COMPARA A NOTA COM A BASE PARA SABER SE É NECESSÁRIO CRIAR OU ALTERAR O REGISTRO NO BANCO DE DADOS
		$Upg = $db -> prepare("UPDATE avaliacoes_notas SET avn_nota = ?, avn_rp = ?, avn_dref = NOW() WHERE avn_avi = ? AND avn_user = ? LIMIT 1");
		$Ins = $db -> prepare("INSERT INTO avaliacoes_notas (avn_avi,avn_user,avn_nota,avn_rp) VALUES (?,?,?,?)");
		
        foreach($Dados['P'] as $K1=>$V1){
			if(!is_numeric($V1[0])){$V1[0]=-1;} // INFORMA VALOR -1 CASO NÃO EXISTA VALOR INFORMADO
			if(!is_numeric($V1[1])){$V1[1]=-1;} // INFORMA VALOR -1 CASO NÃO EXISTA VALOR INFORMADO
            
            // VERIFICA SE EXISTE UM VALOR NA BASE E O ALTERA
			if(array_key_exists($K1,$Dados['B'])){
				$Upg -> bind_param("ssii",$V1[0],$V1[1],$AviID,$K1);
				if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
			
            // SE NÃO EXISTIR, CRIA-O
			}else{
            	$Ins -> bind_param("iiss",$AviID,$K1,$V1[0],$V1[1]);
				if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
			}
		}
		
        // if($C==0){
		// 	$Dados['PRO']['A'] = $MS['nome'];
		// 	$Dados['PRO']['AVI'] = "NOTAS ".$VP['disc_nome'];
		// 	$Dados['PRO']['T'] = $VP['disc_nome'];
		// 	ActionReg(19,false,$Dados['PRO']);
		// }

	}else{ $c++; }

    NotasDiversificadaFim:
    shdr("turmas/avaliacoes/$vpID");

goto Status;}
if($URI[1]=='pauta-bncc-conteudo'){

    $findVP = findVP($vpID);
    if(is_array($findVP) AND array_key_exists('users_id',$findVP) AND in_array($MEUID,$findVP['users_id'])){

        // PREPARA O POST
        foreach($P as $K1=>$V1){if(strstr($K1,'conteudo-')){ $Dados['P'][str_replace('conteudo-','',$K1)] = strip_tags($V1); }}
        // PREPARA A BASE
        $Dados['B'] = FrequenciaBaseMap($vpID);
        // EXECUTA AS ATERACOES
        $Upg = $db -> prepare("UPDATE bncc_pauta SET bp_info = ?, bp_dref = NOW() WHERE bp_vp = ? AND bp_id = ? AND YEAR(bp_dref) = ?");
        foreach($Dados['P'] as $K1=>$V1){
            if(array_key_exists($K1,$Dados['B']) AND $Dados['B'][$K1] != $V1){
                $Upg -> bind_param("siii",$V1,$vpID,$K1,$ANOBASE);
                if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
            }
        }

        // if($C==0 AND array_sum($DADOS['PRO'])>0){
        // 	$DADOS['PRO']['A'] = $MS['nome'];
        // 	$DADOS['PRO']['T'] = $TurmaNome;
        // 	ActionReg(40,false,$DADOS['PRO']);
        // }
    
    }else{$C++;}
	shdr("turmas/pautas/$vpID/conteudo");
	
goto Status;}
if($URI[1]=='pauta-bncc'){
   
    // VERIFICA O VP
    $findVP = findVP($vp);  
    if(!is_array($findVP) OR !in_array($MEUID,$findVP['users_id'])){
        Alert('Você não tem permissão para editar esta pauta.'); $C++; goto FimPautaBNCC;
    }

    // MAPEIA OS ESTUDANTES DA TURMA
	$Map = TurmaEMap($turma);
	// CRIA O POST PARA ALUNOS PRESENTES
	foreach($P as $K1=>$V1){if(strstr($K1,'fq-')){$Dados['P'][$V1] = 1;}}
	// COMPLEMENTA O POST PARA ESTUDANTES QUE NÃO ESTÃO PRESENTES
	foreach($Map as $K1=>$V1){ if(!array_key_exists($V1['vt_user'],$Dados['P'])){
		// VERIFICA A RELAÇÃO DE DATA DE FREQUÊNCIA E A DATA DE TRANSFERÊNCIA, SE EXISTIR
		if(ESitStatus($V1,$data)){
			$Dados['P'][$V1['vt_user']] = 0;
		}
	}}

	// PROCURA NA BASE
	if(is_numeric($bpid)){
		$Dados['B'] = FrequenciaBaseMap($vp,$bpid);
		// VERIFICA SE O ID PASSADO COMBINA COM A DATA NA BASE
		if(!$Dados['B']['data'] == $data){
			Alert('A verificação de dados falhou! Tente novamente por favor.');
			goto FimPautaBNCC;
		}
	}
	
	#ppre($DADOS); exit;
	// PREPARA
	$Ins1 = $db -> prepare("INSERT INTO bncc_pauta (bp_vp,bp_info,bp_data) VALUES (?,?,?)");
	$Ins2 = $db -> prepare("INSERT INTO bncc_pauta_frequencia (bpf_bp,bpf_user,bpf_sit) VALUES (?,?,?)");
	$Upg1 = $db -> prepare("UPDATE bncc_pauta SET bp_info = ?, bp_dref = NOW() WHERE bp_id = ? AND DATE(bp_data) = ? AND YEAR(bp_dref) = ?");	
	$Upg2 = $db -> prepare("UPDATE bncc_pauta_frequencia SET bpf_sit = ?, bpf_dref = NOW() WHERE bpf_bp = ? AND bpf_user = ? AND YEAR(bpf_dref) = ?");
	$info = strip_tags($info);
	
	// VERIFICA SE EXISTE REGISTRO
	if(isset($Dados['B']['id']) AND is_numeric($Dados['B']['id']) AND $Dados['B']['id'] == $bpid){
		// ATUALIZA OS CONTEUDOS
		if($info != $Dados['B']['info']){
			$Upg1 -> bind_param("sisi",$info,$Dados['B']['id'],$data,$ANOBASE);
			if(!$Upg1->execute()){!$C++;}else{$Dados['PRO']['U']++;}
		}
		// ATUALIZA OS DADOS DE FREQUENCIA
		foreach($Dados['P'] as $K1=>$V1){
			if(array_key_exists($K1,$Dados['B']['map'])){
				if($Dados['B']['map'][$K1] != $V1){
					$Upg2 -> bind_param("iiii",$V1,$Dados['B']['id'],$K1,$ANOBASE);
					if(!$Upg2->execute()){$C++;}else{$Dados['PRO']['U']++;}
				}
			}else{
				$Ins2 -> bind_param("iii",$Dados['B']['id'],$K1,$V1);
				if(!$Ins2->execute()){$C++;}else{$Dados['PRO']['I']++;}
			}
		}
		
	}else{
		$Ins1 -> bind_param("iss",$vp,$info,$data);
		if($Ins1 -> execute()){
			// ID CRIADO PARA A PAUTA
			$bpid = $Ins1 -> insert_id;
			foreach($Dados['P'] as $K1=>$V1){
				$Ins2 -> bind_param("iii",$bpid,$K1,$V1);
				if(!$Ins2->execute()){$C++;}else{$Dados['PRO']['I']++;}
			}
		}else{$C++;}	
	}
	
    // CORRIGIR
	// if($C==0 AND array_sum($Dados['PRO'])>0){
	// 	$Dados['PRO']['A'] = $MS['nome'];
	// 	$Dados['PRO']['T'] = $TurmaNome;
	// 	$Dados['PRO']['DIA'] = Data($data,3);
	// 	$Dados['PRO']['EST'] = count($Dados['P']);
	// 	$Dados['PRO']['INFO'] = strlen($info) ? 'COM' : 'SEM';
	// 	ActionReg(38,false,$Dados['PRO']);
	// }

    FimPautaBNCC:
    shdr("turmas/pautas/$vp".(($C)?"/editar/$bpid/$aulaNumber":""));

goto Status;}
if($URI[1]=='turma-horario'){
    // DIA - AULA => DISC
    // VERIFICA SE A TURMA PERTENCE A ESCOLA
    $findTurma = findTurma($turma); 
    if(is_array($findTurma) AND $findTurma['turma_secretaria'] == $MYSCT){
        
        // PARAMETRIZA O POST
        foreach($P as $K=>$V){ if($K!='turma' AND is_numeric($V)){ $Dados['P'][$K]=$V; }}
        // PROCURA NA BASE
        $Base = $db -> prepare("SELECT turmas_aulas.* FROM turmas_aulas WHERE aulas_turma = ? AND YEAR(aulas_dref) = ? AND aulas_active = 1"); dbE();
		$Base -> bind_param("ii",$turma,$ANOBASE);
		$Base -> execute();
        foreach($Base -> get_result() -> fetch_all(MYSQLI_ASSOC) as $K=>$V){
            $Dados['B'][$V['aulas_dia'].'-'.$V['aulas_hora']] = ['disc'=>$V['aulas_disc'],'id'=>$V['aulas_id']];
        }


        // PROCESSA A COMPARAÇÃO ENTRE POST E BASE
        $Ins = $db -> prepare("INSERT INTO turmas_aulas (aulas_turma,aulas_dia,aulas_hora,aulas_disc,aulas_sala) VALUES (?,?,?,?,NULL)");
        $Upg = $db -> prepare("UPDATE turmas_aulas SET aulas_disc = ?, aulas_dref = NOW() WHERE aulas_id = ? LIMIT 1");
        $Del = $db -> prepare("UPDATE turmas_aulas SET aulas_active = 0, aulas_dref = NOW() WHERE aulas_id = ? LIMIT 1");
        foreach($Dados['P'] as $K=>$V){
            // QUEBRA A CHAVE EM DIA = 0 - HORA = 1
            $DH = explode('-',$K);
            // SE EXISTIR O VALOR NA BASE
            if(array_key_exists($K,$Dados['B'])){
                // TENTA COMPARAR PARA VER A NECESSIDADE DE ATUALIZAR
                if($V != $Dados['B'][$K]['disc']){
                    $Upg -> bind_param("ii",$V,$Dados['B'][$K]['id']);
                    if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
                }
            }else{
                // TENTA INSERIR
                $Ins -> bind_param("iiii",$turma,$DH[0],$DH[1],$V);
                if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;} #ppre($Ins);
            }
        }
        // PROCESSA A COMPARAÇÃO ENTRE POST E BASE
        foreach($Dados['B'] as $K=>$V){
            if(!array_key_exists($K,$Dados['P'])){
                $Del -> bind_param("i",$V['id']);
                if(!$Del->execute()){$C++;}else{$Dados['PRO']['D']++;}
            }
        }


    }else{$C++;}
    shdr("secretaria/turmas/$turma");
    
goto Status;}
if($URI[1]=='turma-number'){ // ALTERA A NUMERACAO DOS VTs
    
    $Upg = $db -> prepare("UPDATE vinc_turma
    INNER JOIN turmas ON (turmas.turma_id = vinc_turma.vt_turma)
    SET vt_num = ?, vt_dref = NOW() 
    WHERE turma_secretaria = ? AND YEAR(turma_dref) = ? AND vt_turma = ? AND vt_id = ? LIMIT 1");

    foreach($P as $KeyP=>$ViewP){
        if(is_numeric($KeyP)){
            $ViewP = (is_numeric($ViewP)) ? $ViewP : 0;
            $Upg -> bind_param("iiiii", $ViewP, $MYSCT, $ANOBASE, $turma, $KeyP);
            if(!$Upg->execute()){$C++;}
        }
    }
    shdr("secretaria/turmas/$turma");

goto Status;}
// ----------------------------------------------- FIM TURMAS

// ----------------------------------------------- INICIO NOTAS
if($URI[1]=='avi'){
    // PROCURA A AVALIAÇÃO
    $AVI = findAVI($aviIDEdit,$vpIDEdit);
    
    // VERIFICA SE É UM ARRAY (VALIDO)
	if(is_array($AVI)){ 
        
        $VP = findVP($vpIDEdit);    // PROCURA O VP
        $NM = $aviValorEdit * 0.6;  // NOTA MÉDIA AZUL MINIMA # TAXA DE APROVAÇÃO
        $NS = $aviValorEdit * 0.9;  // NOTA MÍNIMA PARA ENVIO DE PARABENIZAÇÃO PELO WHATSAPP
	
		// CONFIGURA A COPIA
		$aviCopiaEdit = (isset($aviCopiaEdit)) ? (($aviCopiaEdit=='on' OR $aviCopiaEdit==1)?1:0) : 0;
		// FAZ UPDATE DAS INFORMAÇÕES DA AVALIAÇÃO
		if($AVI['avi_copy']!=$aviCopiaEdit OR $AVI['avi_tipo']!=$aviTipoEdit OR $AVI['avi_info']!=$aviNomeEdit OR $AVI['avi_valor']!=$aviValorEdit){
			$aviValorEdit = str_replace(',','.',$aviValorEdit);
			$Upg = $db -> prepare("UPDATE avaliacoes SET avi_copy = ?, avi_tipo = ?, avi_valor = ?, avi_info = ?, avi_dref = NOW() WHERE avi_vp = ? AND avi_id = ?");
			$Upg -> bind_param("iidsii",$aviCopiaEdit,$aviTipoEdit,$aviValorEdit,$aviNomeEdit,$AVI['avi_vp'],$AVI['avi_id']);
			if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
		}
		// CRIA O POST
      
		foreach($P as $K1=>$V1){
			if(strstr($K1,'nota-')){ $uid = str_replace('nota-','',$K1);
				$Dados['P'][$uid][0] = $N1 = str_replace(',','.',$V1);
				$Dados['P'][$uid][1] = $N2 = str_replace(',','.',$P["rp-$uid"]);
				
                #=============== --- ENVIA A NOTIFICAÇÃO POR WHATSAPP --- =================
				if(isset($ES['ntfbaixorend']) AND $ES['ntfbaixorend'] == 1){
					$SMS = New SMS();
					if(is_numeric($N1) AND !is_numeric($N2)){
						$SMS -> lac = ['D'=>((isset($disc))?$disc:''),'V'=>"$N1 de $aviValorEdit"];
						$SMS -> user = $uid;
						
						if($N1 < $NM){
							$SMS -> tipo = 3;
							$SMS -> Render();
						}
						if($N1 >= $NS){
							$SMS -> tipo = 9;
							$SMS -> Render();
							
						}
					}
				}
                #=============== --- ENVIA A NOTIFICAÇÃO POR WHATSAPP --- =================
			}
		}
	
		$Ins = $db -> prepare("INSERT INTO avaliacoes_notas (avn_avi,avn_user,avn_nota,avn_rp) VALUES (?,?,?,?)"); dbE();
		$Upg = $db -> prepare("UPDATE avaliacoes_notas SET avn_nota = ?, avn_rp = ?, avn_dref = NOW() WHERE avn_avi = ? AND avn_user = ? LIMIT 1"); dbE();
		
        foreach($Dados['P'] as $K1=>$V1){
			if(array_key_exists($K1,$AVI['map'])){
				if($V1[0]!=$AVI['map'][$K1][0] OR $V1[1]!=$AVI['map'][$K1][1]){
					if(!is_numeric($V1[0])){$V1[0]=-1;}
					if(!is_numeric($V1[1])){$V1[1]=-1;}
					$Upg -> bind_param("ddii",$V1[0],$V1[1],$AVI['avi_id'],$K1);
					if(!$Upg->execute()){$C++;}else{$Dados['PRO']['U']++;}
				}
			}else{
				if(is_numeric($V1[0]) OR is_numeric($V1[1])){
					if(!is_numeric($V1[0])){$V1[0]=-1;}
					if(!is_numeric($V1[1])){$V1[1]=-1;}
					$Ins -> bind_param("iidd",$AVI['avi_id'],$K1,$V1[0],$V1[1]);
					if(!$Ins->execute()){$C++;}else{$Dados['PRO']['I']++;}
				}
			}
		}
		// if($C==0){
		// 	$Dados['PRO']['A'] = $MS['nome'];
		// 	$Dados['PRO']['AVI'] = $AVI['avi_info'];
		// 	$Dados['PRO']['T'] = NTurma($vpIDEdit);
		// 	ActionReg(19,false,$Dados['PRO']);
		// }
		// ATUALIZA O RENDIMENTO DA TURMA
		URendimentoVP($VP['vp_id']);
		
	}else{$C++;}

    shdr("turmas/avaliacoes/$vpIDEdit");

goto Status;}

// ----------------------------------------------- FIM NOTAS


// FUNÇÕES ADIMINISTRATIVAS
if($MEUTIPO == 0){


goto Fim;}


Status: 
    require_once Views.'/html/system_engine_status.php';
    goto Fim;

Fim:
