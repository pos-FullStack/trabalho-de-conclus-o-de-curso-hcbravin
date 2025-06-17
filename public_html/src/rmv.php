<?php if($MEUTIPO != 0 AND $ANOBASE != $ANOATUAL){ Alert("Alterações Bloqueadas para o ano de $ANOBASE."); goto Fim; }

// PARAMETRIZA O POST
$P = $_POST; foreach($_POST as $K=>$V){ $$K = $V; }
$Dados = ['P'=>[], 'B'=>[], 'U'=>[],'E'=>[],'D'=>[], 'PRO'=>['U'=>0,'I'=>0,'D'=>0]]; $C=0; $Map = [];
$Map = []; $C=0;

// ----------------------------------------- INICIO FINANCEIRO
if($URI[1]=='cantina-saldo-interno'){
    // BUSCA O USUARIO
    $User = new Usuario();
    $User -> id = $URI[2];
    $findUser = $User -> findUser();
    if(is_array($findUser) AND array_key_exists('user_id',$findUser)){

        $Cantina = New Cantina();
        $Saldo = $Cantina -> Saldo($URI[2]);

        if(is_array($Saldo) AND array_key_exists('map',$Saldo) AND array_key_exists($URI[3],$Saldo['map'])){
            $Rmv = $db -> prepare("DELETE FROM cash_cantina_saldo WHERE css_id = ? AND css_user = ? AND css_saldo = css_valor LIMIT 1");
            $Rmv -> bind_param("ii",$URI[3],$URI[2]);
            if(!$Rmv->execute()){$C++;}

        }else{ Alert('Identificador do saldo inserido não foi encontrado!'); $C++; }

    }else{ Alert('Usuário não encontrado!'); $C++; }
    shdr('financeiro/cantina/saldo-interno');

goto Status;}
// ----------------------------------------- FIM FINANCEIRO

// ----------------------------------------- INICIO AVALIAÇÕES
if($URI[1]=='avaliacao'){

    // PROCURA PELA AVALIAÇÃO
    $Avi = findAVI($URI[3],$URI[2]);
    // VERIFICA A AVALIAÇÃO
    if(is_array($Avi)){
    
        $Del = $db -> prepare("DELETE avaliacoes.* FROM avaliacoes 
        INNER JOIN vinc_prof ON (vinc_prof.vp_id = avaliacoes.avi_vp)
        INNER JOIN vinc_prof_user ON (vinc_prof_user.vpu_vp = vinc_prof.vp_id)
        INNER JOIN turmas ON (turmas.turma_id = vinc_prof.vp_turma)
        WHERE avaliacoes.avi_id = ? AND vinc_prof.vp_id = ? AND YEAR(avaliacoes.avi_dref) = ? AND turmas.turma_secretaria = ? AND vinc_prof_user.vpu_user = ?");
        $Del -> bind_param("iiiii",$URI[3],$URI[2],$ANOBASE,$MYSCT,$MEUID);
        if(!$Del->execute()){$C++;}else{

            // INFORMA DADOS PARA REGISTRO
            $Dados['PRO']['NOME'] = $MS['nome'];
            $Dados['PRO']['DATA'] = Data(null,3).' às '.Data(false,6);
            $Dados['PRO']['AVINOME'] = $Avi['avi_info'];
            $Dados['PRO']['AVIVALOR'] = $Avi['avi_valor'];
        }

    }else{$C++;}
    shdr("turmas/avaliacoes/$URI[2]");

goto Status;}
// ----------------------------------------- FIM AVALIAÇÕES

// ----------------------------------------- INICIO TURMA
if($URI[1]=='pauta'){
    $findVP = findVP($URI[2]);
    if(in_array($MEUID,$findVP['users_id'])){
        $Rmv = $db -> prepare("DELETE FROM bncc_pauta WHERE bp_id = ? AND bp_vp = ? AND YEAR(bp_dref) = ? LIMIT 1"); dbE();
        $Rmv -> bind_param("iii",$URI[3],$URI[2],$ANOBASE);
        if(!$Rmv->execute()){$C++;}
    }else{ $C++; Alert('Você não permissão para esta ação.'); }
    shdr("turmas/pautas/$URI[2]");
goto Status;}
if($URI[1]=='vinculo-turma'){
    // VERIFICA A TURMA
    $findTurma = findTurma($URI[2]);
    if(is_array($findTurma) AND array_key_exists('turma_secretaria',$findTurma) AND $findTurma['turma_secretaria'] == $MYSCT){
        // VERIFICA 
        $TurmaEMap = TurmaEMap($URI[2]);
        if(array_key_exists($URI[3],$TurmaEMap)){

            $Rmv = $db -> prepare("DELETE FROM vinc_turma WHERE vt_id = ? AND vt_turma = ? LIMIT 1"); dbE();
            $Rmv -> bind_param("ii",$URI[3],$URI[2]);
            $Action = $Rmv -> execute();
            if(!$Action OR $Rmv -> affected_rows == 0){
                Alert('O vínculo que você está tentando excluir não pode ser exclúido. Isso pode ocorrer devido a alguma informação relevante associado ao vínculo como, por exemplo, notas.'); $C++; 
            }

        }else{ Alert('O Vinculo que você está tentando excluir não foi encontrado.'); $C++; }

    }else{ Alert('Turma não encontrada.'); $C++; }
    shdr("secretaria/turmas/$URI[2]");

goto Status;}
if($URI[1]=='bncc-estudo-orientado'){
    
    $findTurma = findVP($URI[2]);
    if(is_array($findTurma) AND array_key_exists('users_id',$findTurma) AND in_array($MEUID,$findTurma['users_id'])){
        
        $Rmv = $db -> prepare("DELETE FROM eo_atividades WHERE eoa_vp = ? AND DATE(eoa_data) = ? AND YEAR(eoa_dref) = ? LIMIT 1"); dbE();
        $Rmv -> bind_param("isi",$findTurma['vp_id'],$URI[3],$ANOBASE);
        if(!$Rmv->execute()){$C++;}

    }else{
        Alert('Você não tem permissão para esta ação!'); $C++;
    }

    shdr("turmas/estudo-orientado/$URI[2]", ($C?4:2));

goto Status;}
// VINCULOS
if($URI[1]=='vinculo'){

    // PROFESSOR
    if($URI[2]=='professor'){
        $findVP = findVP($URI[3]);
        if(count($findVP['users_id']) > 1){
            // VERIFICA SE O PROFESSOR FAZ PARTE DO VP
            if(in_array($URI[4],$findVP['users_id'])){
                
                $Rmv = $db -> prepare("DELETE FROM vinc_prof_user WHERE vpu_vp = ? AND vpu_user = ? AND YEAR(vpu_dref) = ? LIMIT 1");
                $Rmv -> bind_param("iii",$URI[3],$URI[4],$ANOBASE);
                if(!$Rmv -> execute()){$C++;}
                # action()

            }else{
                Alert('Não existe vínculo entre o(a) professor(a) informada e a componente/turma.'); $C++;
            }
        }else{
            Alert('Vincule um novo professor a turma antes de remover o antigo.'); $C++;
        }

        shdr("secretaria/enturmar/professor");
    goto Status;}

goto Fim;}
// ----------------------------------------- FIM TURMA

// ----------------------------------------- INICIO CALENDARIO
if($URI[1]=='calendario'){

    // CALENDARIO DA TURMA
    if($URI[2]=='turma'){
        
       $Base = $db -> prepare("SELECT * FROM agenda_turma WHERE ct_id = ? AND ct_turma = ? AND ct_user = ? AND YEAR(ct_dref) = ?");
       $Base -> bind_param("iiii",$URI[5],$URI[3],$MEUID,$ANOBASE);
       if($Base->execute()){
            
            // VERICA SE OS DADOS INFORMADOS SÃO DE FATO DE QUEM ESTÁ TENTANO EXCLUIR
            $Res = $Base->get_result();
            if($Res->num_rows){
                $Res = $Res -> fetch_assoc();
                // VERIFICA SE A ATIVIDADE MARCADA NO CALENDARIO JA PASSOU DA DATA
                $Data = $Res['ct_data'].' '.$Res['ct_hora'];
                if($Data > date("Y-m-d H:i:s")){
                    // BUSCA EXCLUIR A ATIVIDADE E O REGISTRO DE SMS, CASO AINDA SEJA POSSÍVEL
                    $Base = $db -> prepare("DELETE FROM agenda_turma WHERE ct_id = ? LIMIT 1"); dbE();
                    $Base -> bind_param("i",$URI[5]);
                    if(!$Base->execute()){$C++;}else{
                        // TENTA EXCLUIR O SMS
                        $db -> query("DELETE FROM sms WHERE sms_key = '".$Res['ct_key']."' AND sms_status != '200' AND YEAR(sms_dref) = '$ANOBASE'");
                    }
                }else{$C++;}
            }else{$C=false;}

       }else{$C++;}

       shdr("calendario/turma/$URI[3]/$URI[4]");
    goto Status;}

goto Fim;}
// ----------------------------------------- FIM CALENDARIO

// ----------------------------------------- INICIO OCORRENCIAS
if($URI[1]=='ocorrencia'){

    $DevMap = TutorDevolutivasMap(false,$URI[3]);

    if (is_array($DevMap) AND count($DevMap) AND array_key_exists($URI[2], $DevMap)) {
		// PEGA O PRIMEIRO ELEMENTO
		$Ver = $DevMap[$URI[2]];
		// VERIFICA SE VOCE FOI O AUTOR DA OCORRENCIA PARA PERMITIR EXCLUSAO
		if($Ver['oc_por']==$MEUID){
			if($Ver['oc_sit']==0){
				
                $Rmv = $db -> prepare("DELETE FROM ocorrencias WHERE oc_por = ? AND oc_id = ? LIMIT 1"); dbE();
				$Rmv -> bind_param("ii",$MEUID,$URI[3]);
				if($Rmv->execute()){ 
					
					$REG['A'] = $MS['nome'];
					$REG['REF'] = $Ver['oc_id'];
					$REG['DIA'] = Data(null,3);
					//ActionReg(36,false,$REG);
                    
                    // REMOVE O SMS, CASO POSSA
                    DeleteSMS("OCREG." . $Ver['oc_id']);

				}else{$C++;}
			}else{$C++;}
		}else{$C++;}
	}else{$C++;}

    shdr("tutoria/ocorrencia/devolutivas");

goto Status;}


// TUTORIA ATENDIMENTOS
if($URI[1]=='tutoria-atendimento'){

    $Rmv = $db -> prepare("DELETE FROM tutoria_atendimentos WHERE tuta_id = ? AND tuta_tut = ? AND tuta_por = ? AND YEAR(tuta_dref) = ? LIMIT 1");
    $Rmv -> bind_param("iiii",$URI[3],$URI[2],$MEUID,$ANOBASE);
    $Rmv -> execute();
    if($Rmv -> affected_rows == 0){$C++;}
    shdr("tutoria/atendimento/$URI[2]");

goto Status;}
if($URI[1]=='tutoria-metas'){
    
    $findTut = findTutoria($URI[2]);
    if(is_array($findTut) AND array_key_exists('tut_tutor',$findTut) AND $findTut['tut_tutor'] == $MEUID){

        $Rmv = $db -> prepare("DELETE FROM tutoria_meta WHERE tm_user = ? AND YEAR(tm_dref) = ? AND tm_id = ? LIMIT 1");
        $Rmv -> bind_param("iii",$findTut['tut_estudante'],$ANOBASE,$URI[3]);
        if(!$Rmv -> execute()){$C++;}

    }else{
        Alert('Você não tem permissão para esta ação!'); $C++;
    }

    shdr("tutoria/metas/$URI[2]");

goto Status;}
// ----------------------------------------- FIM OCORRENCIAS


// ----------------------------------------- INICIO ELETIVAS
if($URI[1]=='eletiva'){

    // EXCLUI A ELETIVA EM SI
    if(is_numeric($URI[2])){

        $Elt = EltMap($URI[2]);
        if(is_array($Elt) AND array_key_exists('elt_turno',$Elt) AND $Elt['elt_turno'] == $MEUTURNO AND $Elt['elt_secretaria'] == $MYSCT){
            
            // EXCLUI O VINCULO DOS PROFESSORES
            $DelUser = $db -> prepare("DELETE FROM vinc_prof_user WHERE vpu_vp = ?");
            $DelUser -> bind_param("i",$Elt['vp_id']);
            if($DelUser->execute()){

                // EXCLUI O VINCULO
                $DelVP = $db -> prepare("DELETE FROM vinc_prof WHERE vp_id = ?");
                $DelVP -> bind_param("i",$Elt['vp_id']);
                if($DelVP->execute()){

                    // EXCLUI A ELETIVA
                    $DelElt = $db -> prepare("DELETE FROM eletivas WHERE elt_id = ?");
                    $DelElt -> bind_param("i",$Elt['elt_id']);
                    if(!$DelElt){$C++;}

                }else{$C++;}
            }else{$C++;}

        }else{$C++;}
        shdr('diversificada/eletiva');

    goto Status;}

}
// ----------------------------------------- FIM ELETIVAS

// FUNÇÕES ADIMINISTRATIVAS
if($MEUTIPO == 0){


goto Fim;}
    
    
Status: 
    require_once Views.'/html/system_engine_status.php';
    goto Fim;

Fim:
