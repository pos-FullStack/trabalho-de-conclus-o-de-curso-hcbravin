<?php

    $BloqLogin = false; $C = 0; $P = $_POST;
    // INSTANCIA USUARIO
    $User = new Usuario;

    // SE A URI FOR DE LOGIN
    if($URI[0]=='login'){

        if($URI[1] == 'criar-conta'){

            // PARAMETRIZA
            $User -> nome = $_POST['UserNome'];
            $User -> cpf = $_POST['UserCPF'];
            $User -> data = $_POST['UserNascimento'];
            $User -> sexo = $_POST['UserSexo'];
            $User -> cep = $_POST['UserCEP'];
            $User -> tel = $_POST['UserTel'];
            $User -> email = $_POST['UserEmail'];
            $Criar = $User -> Cadastrar();

        goto Fim;}

        if($URI[1]=='esqueci-minha-senha'){
            
            if(isset($_POST['UserCPF'])){
                // VALIDA O CAPTCHA
                if(@reset($P['UserCaptcha']) != @$MS['captcha'][array_key_first($P['UserCaptcha'])]){
                    alert('Código Captcha inválido.<br>Tente novamente.');
                    goto Fim;
                }
                // VERIFICA O USUÁRIO
                $User -> cpf = $P['UserCPF'];
                $findUser = $User -> findUser();
                if(!is_array($findUser) OR !array_key_exists('ui_id',$findUser)){
                    Alert('Usuário não encontrado!');
                    goto Fim;
                }
                // VALIDA A DATA DE NASCIMENTO
                if($P['UserNascimento'] != $findUser['ui_nascimento']){
                    Alert('Os dados fornecidos não conferem com os do usuário encontrado!');
                    goto Fim;
                }

                if($User -> ResetPass()){
                    Alert('Senha alterada com sucesso.<br>Sua nova senha será seu CPF (<b>somente números</b>).',true);
                    goto Fim;
                }else{
                    Alert('Sua senha não pode ser alterada.<br>Tente novamente.');
                    goto Fim;
                }
            }

            $gCaptcha = gCaptcha(true);
            require_once Views . '/main/login_recuperar_senha.php';
        goto Fim;}

        if($URI[1] == 'entrar'){
            if($User -> CheckCaptcha($P['UserCaptcha'])){

                $Login = $User -> Login($P['UserCPF'],$P['UserSenha']);
                if($Login){
                    Alert('Login realizado com sucesso!',true);
                    
                }else{
                    Alert('Login inválido. Verifique as informações e tente novamente.');
                }

            }else{ Alert('Captcha Inválido.'); }
        goto Fim;}

        if($URI[1] == 'acessar-conta'){

            $User -> setID($MS['ui_id']);
            $Contas = $User -> getContas();
            if(array_key_exists($URI[2],$Contas)){
                $_SESSION['id'] = $MS['id'] = $URI[2];
                alert('Acesso a conta concedido. Aguarde para ser redirecionado.', true);
                shdr('home');

            }else{
                alert('Acesso negado a está conta.<br>Você será desconectado!');
                $C=1; shdr('logout');
            }

        goto Fim;}

        
    }

Status:
    require_once Views.'/html/system_engine_status.php';
    goto Fim;

Fim: