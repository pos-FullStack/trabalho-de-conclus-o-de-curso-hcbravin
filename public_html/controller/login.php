<?php

use Hybridauth\Hybridauth;

// 1. Validar se o provedor foi passado na URL
if (!$URI[1] || !in_array($URI[1], ['google', 'apple'])) {
    require_once Views . '/error/loginEntrance.php';
    goto Fim;
}

// 2. Configuração do Hybridauth
if($URI[1] == 'google'){

    try {
        $hybridauth = new Hybridauth($config);
        
        // Tenta autenticar o usuário no Google
        $adapter = $hybridauth->authenticate('Google');
        
        // Se chegou aqui, o usuário já logou!
        $userProfile = $adapter->getUserProfile();
        // var_dump($userProfile->photoURL); die();

        // 3. Processar no Banco de Dados (usando sua função com global $db)
        // Criaremos uma função genérica para aceitar o nome do provedor
        $userId = new Login() -> processarLoginSocial($userProfile, $URI[1]);   

        // 4. Busca informações do usuário e inicia a sessão
        $User  = new Usuario($userId);
        $findUSer = $User -> findUser();
        $User -> getBancoInfo(); // Inicia as informações de contas e gerencias

        // 5. Busca informações sobre convites
        $_SESSION['convites'] = array_sum(array_map('count', array_filter($User -> getConvites(), 'is_array')));

        $_SESSION = array_merge($_SESSION, $findUSer);
        $MS = $_SESSION; // Atualiza a variável global de sessão do sistema

        // Desloga para o próximo teste
        // $adapter->disconnect();

        hdr('dashboard');

    } catch (\Exception $e) {
        echo "Erro: " . $e->getMessage();
    }

}

if($URI[1] == 'apple'){

    // Código para autenticação com Apple (a ser implementado)
    echo "Autenticação com Apple ainda não implementada.";

}




Fim: