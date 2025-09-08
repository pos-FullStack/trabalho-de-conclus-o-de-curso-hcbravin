<?php 

// PARAMETRIZA O POST
$P = $_POST; foreach($_POST as $K=>$V){ $$K = $V; }
$Dados = ['P'=>[], 'B'=>[], 'U'=>[],'E'=>[],'D'=>[], 'PRO'=>['U'=>0,'I'=>0,'D'=>0]]; $C=0; $Map = []; $Nulo = null; 


// ----------------------------------------------- INICIA A VERIFICAÇÃO DE ANO -----------------------------------------------
if($MEUTIPO != 0 AND $ANOBASE != $ANOATUAL){ Alert("Alterações Bloqueadas para o ano de $ANOBASE."); goto Fim; }



if($URI[1]=='agencia'){

    $Agencia = new Agencia();

    if(array_key_exists($agencia,$MS['gerente'])){
        $Agencia -> id = $agencia;
        $Config = $Agencia -> getConfig();

        if($URI[2]=='sorte'){
            $Update = (!isset($Config['sorte']['quantidade']))?false:true;
            $Config['sorte']['quantidade'] = ($Update) ? 'historico' : $P['sorte']['quantidade'];
            $Agencia -> ConfigUpd = json_encode($Config['sorte']);
            if(!$Agencia -> setConfig('sorte', $Update)){$C++;}
        }

        if($URI[2]=='taxas'){
            $Update = (!isset($Config['taxas']['comportamento']))?false:true;
            $P['taxas']['comportamento'] = (!isset($P['taxas']['comportamento']) OR strlen($P['taxas']['comportamento'])) ? 'historico' : $P['taxas']['comportamento'];
            $Config['taxas']['comportamento'] = ($Update) ? 'historico' : $P['taxas']['comportamento'];
            $Agencia -> ConfigUpd = json_encode($Config['taxas']);
            if(!$Agencia -> setConfig('taxas', $Update)){$C++;}
        }

        if($URI[2]=='prorrogar'){
            if(!$Agencia -> Prorrogar()){$C++;}
        }

        if($URI[2]=='debitos'){
            $P['debitos'] = array_filter($P['debitos'], function($item) { return isset($item['nome']) && !empty($item['nome']); });
            // VERIFICA SE TEM ITENS
            if(count($P['debitos'])){
                // VERIFICA SE JA FOI INSERIDO
                $Update = (count(array_filter(array_keys($P['debitos']), 'is_numeric')) > 0)?true:false;
                // ORDENA E PERDE A CHAVE
                usort($P['debitos'], function($a, $b) {return strcmp($a['nome'], $b['nome']);});
                $Agencia -> ConfigUpd = json_encode($P['debitos']);
                if(!$Agencia -> setConfig('debitos', $Update)){$C++;}
            }
        }

        shdr("gerencia/$agencia/configuracoes");
        goto Status;

    }else{ alert('Acesso negado a esta agência.'); shdr('home'); }

goto Fim;}


Status: 
    require_once Views.'/html/system_engine_status.php';
    goto Fim;

Fim:
