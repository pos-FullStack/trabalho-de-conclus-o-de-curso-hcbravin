<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);
session_start();
// Carrega as configurações e dependências do aplicativo
require_once __DIR__ . '/src/AppLoading.php';

// Se houver uma solicitação para geração de pdf redireciona o fluxo
if($URI[0] === 'pdf'){
    require_once Controller . '/pdf.php';
    goto CloseAPP;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once PublicHTML . '/src/header.php'; // Carrega o Header da Página 
    ?>
 </head>

<body class="d-flex flex-column min-vh-100">

    <?php
    // Verifica as políticas de uso
    require_once Views . '/main/politicas_load.php';
    if ($PolyLock) {
        goto FimAPP;
    }
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom d-print-none">
        <div class="container-fluid justify-content-between">
            <?php if($Logado AND $Mobile AND $MS['convites']){ ?>
            <!-- Elemento que irá gerar notificações gerais em relação a conta -->
            <a class="navbar-brand d-sm-none float-start ms-2" href="/account/convites">
                <i class="bi bi-envelope-exclamation-fill align-self-center" id="MainNotificacoesAccount"></i>
            </a>
            <?php } ?>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse ms-sm-3 navbar-collapse" id="navbarSupportedContent">
                <div class="edbank text-white d-none d-sm-inline-block me-3">
                    <a href="/" class="text-white"><i class="bi bi-bank2 me-1 <?= Logado() ? 'ms-2' : ''; ?>"></i> <strong>ED Bank</strong></a>
                </div>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($MS['gerente']) and is_array($MS['gerente']) and count($MS['gerente'])) { ?>
                        <li class="nav-item dropdown py-1">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarGerencia" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bank me-1"></i> Gerência
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarGerencia">
                                <?php foreach ($MS['gerente'] as $KeyG => $ViewG) {
                                    if (Data($ViewG['ag_dref'], 'ano') == $AnoAtual or $ViewG['ag_dias'] > 0) { ?>
                                        <li>
                                            <a class="dropdown-item" href="/gerencia/<?= $KeyG; ?>">
                                                <div class="btn-group me-2">
                                                    <span class="btn btn-sm px-1 btn-dark ft-8 py-0"><?= Data($ViewG['ag_dref'], 'ano'); ?></span>
                                                    <span class="btn btn-sm px-1 btn-<?= ($ViewG['ag_dias'] < 10) ? 'danger' : 'info'; ?> ft-8 py-0"><?= ($ViewG['ag_dias'] < 10 ? '0' : NULL) . $ViewG['ag_dias']; ?></span>
                                                </div>
                                                <span class="ft-10"><i class="fa fa-user-tie me-1"></i> <?= ZeroEsquerda($ViewG['ag_num']); ?></span>
                                                <span class="ft-8"><i class="bi bi-folder-fill mx-1"></i> <?= $ViewG['ag_info']; ?></span>
                                            </a>
                                        </li>
                                <?php }
                                } ?>
                                <li>
                                    <hr class="my-1">
                                </li>
                                <li><a class="dropdown-item" href="/gerencia/desativadas"><i class="bi bi-piggy-bank me-1"></i> Agências Desativadas</a></li>

                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (isset($MS['contas']) and is_array($MS['contas']) and count($MS['contas'])) { ?>
                        <li class="nav-item dropdown py-1">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarContas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bank me-1"></i> Contas
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarContas">
                                <?php foreach ($MS['contas'] as $KeyG => $ViewG) {
                                    if (Data($ViewG['ct_dref'], 'ano') == $AnoAtual) { ?>
                                        <li>
                                            <a class="dropdown-item" href="/conta/<?= $KeyG; ?>">
                                                <i class="bi bi-bank me-1"></i> <?= ZeroEsquerda($ViewG['ag_num']); ?> <i class="bi bi-person-square mx-1"></i> <?= ZeroEsquerda($ViewG['ct_conta']) . ' - ' . $ViewG['ct_digito']; ?>
                                            </a>
                                        </li>
                                <?php }
                                } ?>

                            </ul>
                        </li>
                    <?php } ?>
                    <?php if($Logado){ ?>
                    <li class="nav-item dropdown py-1">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarNewAccount" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-square me-1"></i> Abrir
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarNewAccount">
                            <li><a class="dropdown-item" href="/abertura/agencia"><i class="bi bi-bank me-1"></i> Abrir Agência</a></li>
                            <li><a class="dropdown-item" href="/abertura/conta"><i class="bi bi-piggy-bank me-1"></i> Abrir Conta</a></li>
                        </ul>
                    </li>
                    <?php } ?>
                </ul>

                <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                    <?php if (Logado()) { ?>
                        <?php if(isset($MS['rootAcess']) AND $MS['rootAcess']){ ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/usuarios/reset"><span class="btn btn-sm btn-light py-1 ft-10"><i class="bi bi-arrow-counterclockwise"></i> <span class="ft-10 ms-1">Reset</span></span></a></li>
                        <?php } ?>
                        <li class="nav-item">
                            <?php if($Logado AND !$Mobile AND $MS['convites']){ ?>
                            <!-- Elemento que irá gerar notificações gerais em relação a conta -->
                            <a class="nav-link" href="/account/convites">
                                <div class="btn btn-sm btn-warning" id="MainNotificacoesAccount">
                                    <i class="bi bi-envelope-exclamation-fill align-self-center"></i>
                                </div>
                            </a>
                            <?php } ?>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/logout"><span class="btn btn-sm btn-danger ft-10 py-1"><i class="bi bi-door-closed-fill"></i> <span class="ft-10 ms-1">Sair</span></span></a></li>

                    <?php } else { ?>

                        <li class="nav-item"><span class="nav-link px-1"><button type="button" id="UserLogin" class="UserLogin btn btn-sm btn-primary fw-bold" onclick="$('#UserLoginModal').modal('show');"><i class="bi bi-key me-1"></i> Acessar sua Conta</button></span></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="flex-grow-1">
        <div class="container-fluid pt-2 pb-5">
            <?php
            LoadingPage:

            // Se o usuário estiver logado
            if (isset($MS['user_id'], $URI[0])) {
                if (is_file(Controller . "/$URI[0].php")) {
                    // Verifica se o arquivo de controle existe com base no primeiro parâmetro passado na URL.
                    // Se o arquivo existir, carrega o controller deste parametro.
                    require_once Controller . "/$URI[0].php";
                } else {
                    // Se o arquivo não existir, apresenta a página de erro 404.
                    require_once Views . "/main/publicidade.php";
                    // require_once Views . "/error/404.php";
                }
            } else {

                // Se o usuário não tiver logado, verifica se o controller está aberto ao acesso público
                if ($URI[0] != '' and $URI[0] != 'logout' and !UrlOpen()) {
                    // Se não houver parâmetro na URL, carrega a página de login
                    require_once Controller . '/login.php';
                } else {
                    if (UrlOpen()) { // Verifica se a URL é aberta ao público
                        if (is_file(Controller . "/$URI[0].php")) {
                            // Se o controller existir e for publico, o carrega
                            require_once Controller . "/$URI[0].php";
                        } else {
                            // Se o controller não existir, apresenta a página de erro 404.
                            require_once Views . '/error/404.php';
                        }
                    } else {
                        // Se a URL não for pública e o usuário não estiver logado, carrega a página principal
                        require_once Views . '/main/home.php';
                    }
                }
            }



                if (Logado()) {
                    require_once Modal . '/modal_aguarde.php';
                    require_once Modal . '/modal_confirmacao.php';

                } else {
                    require_once Modal . '/UserCadastrar.php';
                }
            ?>


            

        </div>
    </div>

    
    <?php FimAPP: // Marca o fim do aplicativo para casos de bloqueio por políticas de uso  ?>
    <?php require_once Src . '/footer.php'; // Carrega o Footer da Página  ?>
    <script src="/js/LearnJSReady.js?<?= fileatime(PublicHTML . '/js/LearnJSReady.js'); ?>" type="text/javascript"></script>
</body>
</html>
<?php CloseAPP: ?>