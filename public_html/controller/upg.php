<?php 
// =========================================================================
// Verificações de segurança
if (!Logado()) { goto Fim; }
// if (!Token(@$_POST['form_token'])) { Alert('Token de Segurança expirado!'); goto Fim; }
$P = $_POST; $countErro = 0;
// =========================================================================

// Atualização sobre a account global do sistema
if($URI[1] == 'account'){

    $Usuario = new Usuario();

    // Atualização de convites
    if($URI[2] == 'convites'){
        // Atualiza informação do convite.
        if(!$Usuario -> setConvite($URI[3],1,'subgerentes')){
            $countErro++;
        }else{
            // Remove o item da lista de convites
            $_SESSION['convites'] = (isset($_SESSION['convites']) ? $_SESSION['convites'] - 1 : 0);
        }
        shdr('account/convites');
    goto Status;}


goto Status;}

// Atualizações referente a Agências
if ($URI[1] == 'agencia') {

    if($URI[2]=='encerrar'){
        $Agencia = new Agencia();
        $Agencia -> id = $URI[3];
        if(!$Agencia -> EncerrarAgencia()){
            $countErro++;
        }

        shdr('dashboard');
    goto Status;}

    if($URI[2]=='fechar'){
        $Agencia = new Agencia();
        $Agencia -> id = $URI[3];
        if(!$Agencia -> FecharAgencia()){
            $countErro++;
        }
        shdr("gerencia/{$Agencia->id}");
    goto Status;}

    // Carrega a agência
    $Agencia = new Agencia();
    if (!array_key_exists('agencia', $P) or !is_numeric($P['agencia'])) {
        Alert('Agencia não encontrada!');
        goto Fim;
    } // Verifica se o id da agência foi passado via POST
    $Agencia->id = $P['agencia']; // Atribui o id da agência a classe
    $Config = $Agencia->getConfig(); // Pega as configurações atuais da agência

    // Verifica se a agencia de copia realmente existe
    if(isset($P['agencia']) AND !isset($MS['gerente'][$P['agencia']])){
        Alert('A agência de copia nao foi encontrada!');
        shdr("gerencia/{$Agencia->id}/configuracoes");
        goto Status;
    }

    if($URI[2]=='config'){

        if(!$MS['gerente'][$Agencia->id]['isGerente']){
            alert('Você não tem permissão para executar essa ação!');
            shdr("gerencia/{$Agencia->id}/configuracoes");
            goto Fim;
        }

        // Salva a configuração informada
        if(!$URI[3]){

            $SalarioMinimo = new Taxas() -> getSalarioMinimo();

            // Remove o campo de template das configurações enviadas
            unset($P['debitos']['{id}']); // Remove o campo de template
            unset($P['shop']['{id}']); // Remove o campo de template

            // Atualiza as configurações com os valores enviados via POST
            $Config['ciclo'] = (isset($P['ciclo']) AND is_numeric($P['ciclo']) AND $P['ciclo']>0) ? $P['ciclo'] : $Config['ciclo']; // Ciclo de prorrogação
            $Config['cicloForma'] = (isset($P['cicloForma']) AND is_numeric($P['cicloForma'])) ? $P['cicloForma'] : $Config['cicloForma']; // Forma de prorrogação
            $Config['sorte'] = (isset($P['sorte']) AND is_numeric($P['sorte']) AND $P['sorte'] >= 0) ? $P['sorte'] : $Config['sorte']; // Quantidade de sorteios
            $Config['taxas'] = (isset($P['taxas']) AND in_array($P['taxas'], ['historico','randomico'])) ? $P['taxas'] : $Config['taxas']; // Comportamento das taxas
            $Config['debitos'] = (is_array($P['debitos']) AND count($P['debitos'])) ? $P['debitos'] : $Config['debitos']; // Débitos automáticos
            $Config['transferenciasTipo'] = (isset($P['transferenciasTipo']) AND is_numeric($P['transferenciasTipo'])) ? $P['transferenciasTipo'] : $Config['transferenciasTipo']; // Modo de transferência
            $Config['profissaoMin'] = (isset($P['profissaoMin']) AND is_numeric($P['profissaoMin'])) ? number_format($P['profissaoMin'],2,'.','') : $Config['profissaoMin']; // Profissão mínima
            $Config['profissaoMax'] = (isset($P['profissaoMax']) AND is_numeric($P['profissaoMax'])) ? number_format($P['profissaoMax'],2,'.','') : $Config['profissaoMax']; // Profissão máxima
            $Config['profissaoMinSalario'] = (is_numeric($Config['profissaoMin'])) ? number_format($Config['profissaoMin'] / $SalarioMinimo, 3,'.','') : $Config['profissaoMin'];
            $Config['profissaoMaxSalario'] = (is_numeric($Config['profissaoMax'])) ? number_format($Config['profissaoMax'] / $SalarioMinimo, 3,'.','') : $Config['profissaoMax'];
            $Config['gerenteMaxCash'] = (isset($P['gerenteMaxCash']) ? str_replace(['.',','],['','.'],$P['gerenteMaxCash']) : false);
            $Config['gerenteMaxCash'] = (is_numeric($Config['gerenteMaxCash']) AND $Config['gerenteMaxCash'] > 0) ? number_format($Config['gerenteMaxCash'],2,'.','') : false;
            
            // Verifica os ids das agências com base nos números das agências
            $Config['transferenciasAgencias'] = $Agencia -> getIdByNumber(explode(',',$P['transferenciasAgencias']));
            $Config['transferenciasAgencias'] = ($Config['transferenciasAgencias'] == false) ? [] : $Config['transferenciasAgencias'];

            // Salva as configurações atualizadas no banco de dados
            if(!$Agencia->setConfig(json_encode($Config))){ $countErro++; }

            // Atualiza as configurações do shopping
            if(isset($P['shop']) AND is_array($P['shop'])){
                if (!$Agencia->setShop($P['shop'])) { $countErro++; }
            }

            // Atualiza as profissões
            if(isset($P['profissoes']) AND is_array($P['profissoes'])){
                if (!$Agencia->setProfissoes($P['profissoes'])) { $countErro++; }
            }

            // Redireciona de volta para a página de configurações da agência
            shdr("gerencia/{$Agencia->id}/configuracoes");

        }

        // Copia a configuração de uma agência
        if($URI[3] == 'copiar'){

            // Filtra o array caso a agência de copia esta sendo passada junto para onde irao as copias
            $P['copy'] = array_filter((isset($P['copy']) ? $P['copy'] : []), function($item) use ($P) {
                return $item !== $P['agencia'];
            });

            foreach($P['copy'] as $KeyA => $ViewA){
                if(!isset($MS['gerente'][$ViewA])){
                    unset($P['copy'][$ViewA]);
                }   
            }

            // Se nao for passado nenhuma agencia para copia
            if(!isset($P['copy']) OR count($P['copy']) == 0){
                Alert('Nenhuma agência foi selecionada para copiar!');
                shdr("gerencia/{$Agencia->id}/configuracoes");
                goto Status;
            }

            // Verifica se todas as agencias informadas são de fato do gerente
            if(!$Agencia -> setConfigCopy($P['copy'])){
                Alert('Ocorreu algum erro ao tentar realizar a copia das configurações');
                $countErro++;
            }
        
            shdr("gerencia/{$Agencia->id}/configuracoes");
            goto Status;
        }

    goto Status;}
    

    if ($URI[2] == 'prorrogar') {
        if (!$Agencia->Prorrogar()) { $countErro++; }
        shdr("gerencia/{$Agencia->id}/configuracoes");
    goto Status;}
goto Status;}

// Atualizacoes referentes a conta
if ($URI[1]=='conta'){

    // Atualizacoes referentes a PIX
    if($URI[2]=='pix'){
        // Atualização das Chaves Pix
        if ($URI[3] == 'chaves') {
            
            // As chaves devem ser transmitidas via array, não importando o chave do array.
            $Conta = new Conta();
            $Conta -> contaID = $P['conta'];
            if(!$Conta -> setPixChaves($P['chaves'])){
                $countErro++;
            }
            shdr("conta/{$P['conta']}/pix");
        
        goto Status;}
    }

    // Atualização de investimentos
    if($URI[2]=='investimento'){
        
        if($URI[3]=='detalhes'){ // Detalhes do Investimento
            // Operações com investimentos    

            $Valor = numHTML($P['invModalDetalhesValor']); // formata o valor passado.
            $Inv = new Investimentos($P['conta']); // Chama a classe Investimentos
            $Inv -> Listar(); // Lista os investimentos
            
            // Verifica se o investimento solicitado pertence ao usuário
            if(!is_array($Inv -> listar) OR !array_key_exists($P['invModalDetalhesID'],$Inv -> listar)){
                Alert('O investimento solicitado não pertence a você.');
                $countErro++;
            
            goto investimentosDetalhes; }

            $Inv -> invID = $P['invModalDetalhesID'];
            $InvDetalhes = $Inv -> listar[$P['invModalDetalhesID']]; // Atribui o investimento especifico a variavel

            if($URI[4]=='adicionar'){ // Adicionar fundos.
                // Verifica se o saldo da conta é suficiente
                if(!isset($MS['contas'][$P['conta']]['ct_saldo']) OR $MS['contas'][$P['conta']]['ct_saldo'] < $Valor){
                    alert('Saldo insuficiente.');
                    $countErro++;
                    goto investimentosDetalhes;
                }
            }

            if($URI[4]=='retirar'){ // Retirar fundos.
                // Verifica se o saldo do investimento é suficiente para retirada
                if($Valor > $InvDetalhes['inv_saldo']){
                    alert('Saldo insuficiente.');
                    $countErro++;
                    goto investimentosDetalhes;
                }

                $Valor = (-1) * $Valor;
            }

            // Atualiza o investimento
            if(!$Inv -> Fundos($Valor)){
                $countErro++;
            }

            investimentosDetalhes:
            shdr("conta/".$P['conta']."/investimentos/".$P['invModalDetalhesID']);

        goto Status;}


    }

    // Atualiza conta para ativo ou inativo
    if($URI[2]=='ativo'){
        $Agencia = $URI[3];
        $ContaID = $URI[4];

        // Verifica se os dados estao corretos
        if(!is_numeric($Agencia) OR !is_numeric($ContaID)){
            alert('Dados de conta incorretos.');
            shdr('gerencia/'.$Agencia.'/contas/'.$ContaID);
            $countErro++;
            goto Status;
        }

        // Atualiza a conta
        $Conta = new Conta();
        $Conta -> contaID = $ContaID;
        $Conta -> agenciaID = $Agencia;

        if(!$Conta -> setAtivo()){
            $countErro++;
        }
        
        shdr("gerencia/{$Agencia}/contas/{$ContaID}");
    }

    // Operações com cartão
    if($URI[2]=='cartao'){

        if($URI[3]=='cancelar'){

            $ContaID = $URI[4];
            $CardID = $URI[5];

            // Verifica se os dados estao corretos
            if(!is_numeric($ContaID) OR !is_numeric($CardID)){
                alert('Dados de conta incorretos.');
                shdr('conta/'.$ContaID.'/cartoes/'.$CardID);
                $countErro++;
                goto Status;
            }
            
            // Busca a conta
            $Conta = new Conta();
            $Conta -> contaID = $ContaID;
            $Conta -> findConta();

            // Cancela o cartão
            if(!$Conta -> CartoesCancelar($CardID)){
                $countErro++;
            }
            
            shdr("conta/{$ContaID}/cartoes");

        }
    }

    // Encerramento de conta
    if($URI[2]=='encerrar'){
        $Agencia = new Agencia();
        $Agencia -> id = $URI[3];
        if(!$Agencia -> EncerrarConta($URI[4])){
            $countErro++;
        }

        shdr("gerencia/{$Agencia->id}/contas");
    }

    // Oprações de shopping
    if($URI[2]=='shop'){

        // Atualiza os itens do shop de um cliente
        if($URI[3]=='itens'){

            // Verifica se a agência pertence ao gerente
            if(!array_key_exists($P['agencia'], $MS['gerente'])){
                $countErro++;
                Alert('Agência nao encontrada.');
                shdr('gerencia/'.$P['agencia']);
                goto Status;
            }

            // Busca a conta do cliente e carrega o shopping dele
            $Conta = new Conta();
            $Conta -> contaID = $P['conta'];
            $Conta -> agenciaID = $P['agencia'];
            $Shop = $Conta -> getMyShopItens();

            // Remove itens desnecessários - Usado para debug
            array_walk($Shop, function(&$item){
                unset(
                    $item['cts_item']['description'], 
                    $item['cts_item']['images'],
                    $item['cts_item']['thumbnail']
                );
            });

            // Querys
            $Del = $db -> prepare("DELETE FROM contas_shop WHERE cts_conta = ? AND cts_id = ? LIMIT 1");
            $Upg = $db -> prepare("UPDATE contas_shop 
                SET cts_item = JSON_SET(
                    cts_item, 
                    '$.stock', ?,
                    '$.price', ?
                )
                WHERE cts_conta = ? AND cts_id = ?");

            // Vamos fazer um foreach para verificar se os itens passados são de fato da conta que foi carregada
            foreach($P['item'] as $KeyI => $ViewI){ // Key -> ID, View -> Quantidade
                if(array_key_exists($KeyI, $Shop)){

                    // Se a quantidade for igual a registrada, pulamos
                    if($ViewI == $Shop[$KeyI]['cts_item']['stock']){
                        continue;
                    }

                    // Se a quantidade for zero, já vamos remover ele de cara.
                    if($ViewI == 0){
                        $Del -> bind_param("ii", $P['conta'], $KeyI);
                        if(!$Del -> execute()){
                            $countErro++;
                        }
                        continue;
                    }

                    // Se a quantidade for diferente, mas, não for zero, alteramos.
                    if($ViewI > 0 AND $ViewI != $Shop[$KeyI]['cts_item']['stock']){
                        // Calcula o novo preço baseado no valor unitario original
                        $Price = number_format($Shop[$KeyI]['cts_item']['price_unity'] * $ViewI, 2, '.', '');

                        $Upg -> bind_param("idii", $ViewI, $Price, $P['conta'], $KeyI);
                        if(!$Upg -> execute()){
                            $countErro++;
                        }
                    }
                }
            }

            shdr("gerencia/{$P['agencia']}/contas/{$P['conta']}/shop");

            
        goto Status;}
    }
goto Status;}

Status: 
    require_once Error . '/systemEngineStatus.php'; // Apesar de estar em error, este arquivo exibe o status das operações do sistema
    goto Fim;

Fim:
