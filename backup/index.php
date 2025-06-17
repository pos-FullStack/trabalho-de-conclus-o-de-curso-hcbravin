<?php
session_start(); // INICIA A SESSÕES
//	session_set_cookie_params(['secure' => ($_SERVER['HTTP_HOST'] == 'tesa24.com') ? true : false,'httponly'=>true]);
require_once __DIR__ . '/src/ConfigSystem.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once __DIR__ . '/src/header.php'; ?>

<body class="<?= ($ExibirPainel ? 'sb-sidenav-toggled' : ''); ?>">
    <div class="d-flex" id="wrapper">

        <?php
        // VERIFICA AS POLITICAS DE PRIVACIDADE E SE ESTÃO ACEITAS
        #require_once Views . '/main/politicas_load.php';
        // if ($PolyLock) {
        //     goto FimAPP;
        // }

        // VERIFICA SE ESTA LOGADO E EXIBE A PAGINA DE LOGIN, SE NECESSÁRIO
        // if ((!isset($MS['lid']) AND $URI[0] != 'logout') AND !UrlOpen()) {
        //     // EXIBE A PAGINA DE LOGIN PARA ESCOLHA DO USUARIO
        //     // $URI[1] = 'user';
        //   #  require_once Controller . '/main/login.php';
        //   #  goto FimAPP;
        // }

        ?>
        <!-- Sidebar-->
        <div class="border-end bg-gray-700 MainMenu d-print-none <?= (Logado() and is_Engine() == false) ? NULL : 'd-none'; ?>" id="sidebar-wrapper">
            <div class="py-md-2 py-1 sidebar-heading border-bottom bg-light mpoint" onclick="window.location='/';"><i class="fa fa-home me-1"></i> Início</div>
            <div class="list-group list-group-flush">
                <?php if (Logado()) {
                    require_once __DIR__ . '/src/menu.php';
                } ?>
            </div>
        </div>
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom d-print-none <?= Logado() ? '' : 'd-nonea'; ?>">
                <div class="container-fluid">
                    <button class="btn btn-light py-0 ft-12 <?= (Logado() and !is_engine()) ? NULL : 'd-none'; ?>" id="sidebarToggle"><i class="fa fa-<?= ($Mobile or $ExibirPainel) ? 'bars' : 'square-caret-left'; ?>"></i></button>
                    <button class="navbar-toggler py-0 border-0 ft-12" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fa-bars"></i></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <li class="nav-item"><a class="nav-link px-1" href="/" data-toggle="tooltip" title="Início"><span class="badge text-bg-dark"><i class="fa fa-home"></i> <span class="d-md-none">Home</span></span></a></li>
                            <li class="nav-item"><a class="nav-link px-1" href="/logout"><span class="" data-toggle="tooltip" title="Sair"><i class="fa fa-right-from-bracket"></i> <span class="d-md-none">Sair</span></span></a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Page content-->
            <div class="container-fluid pt-2 pb-5">

                <?php
                // ESTÁ LOGADO?
                LoadingPage:
                if (isset($MS['lid'], $URI[0])) {
                    if (is_file(Controller . "/main/$URI[0].php")) {
                        // É UM ARQUIVO MAIN ?
                        // ARQUIVOS MAIN SÃO AQUELES QUE SE SOBREPÕE AOS ARQUIVOS LOCAIS DOS TIPOS DE USUÁRIO
                        // UM ARQUIVO MAIN COM NOME meus-dados POR EXEMPLO, É SUPERIOR A UM ARQUIVO LOCAL meus-dados E SERÁ CARREGADO PRIMEIRO
                        require_once Controller . "/main/$URI[0].php";
                    } else { // PROCURA NA PASTA DO TIPO
                        if (isset($MEUTIPO) and is_file(Controller . "/" . UserTipo($MEUTIPO) . "/$URI[0].php")) {
                            // SE FOR UM ARQUIVO DO TIPO DE USUÁRIO LOCAL, CARREGA-0
                            // NÃO ESTÁ IMPLEMENTADO A DIFERENCIAÇÃO DE ENG PARA MOB
                            require_once Controller . "/" . UserTipo($MEUTIPO) . "/$URI[0].php";
                        } else {
                            // CASO NÃO SEJA DE NENHUM, RETORNARÁ ERRO 404
                            require_once Views . "/html/404.html";
                        }
                    }
                } else {
                    if (UrlOpen()) { // A URL É PUBLICA ?
                        if ($URI[0] == 'inicio') { // É A PAGINA INICIAL?
                            require_once Views . '/html/BemVindo.html';
                        } elseif (is_file(Controller . "/main/$URI[0].php")) {
                            // O ARQUIVO EXISTE NO DIRETORIO GLOBAL?
                            require_once Controller . "/main/$URI[0].php";
                        } else {
                            // APRESENTA ERRO
                            require_once Views . '/html/404.html';
                        }
                    } else {
                        require_once Controller . '/main/home.php';
                    }
                }
                ?>
                <?php
                // require_once __DIR__ . '/src/cookies_accept.php';

                if (Logado()) {
                    // require_once __DIR__ . '/src/modal_turno.php'; 
                    require_once __DIR__ . '/src/modal_aguarde.php';
                    // require_once __DIR__ . '/src/modal_myFiles.php'; 
                    require_once __DIR__ . '/src/modal_cfm.php';
                    // require_once __DIR__ . '/src/box_sct.php'; 
                    // require_once __DIR__ . '/src/modal_year.php'; 
                }
                ?>
                <?php FimAPP: ?>

            </div>
            <?php require_once Views . '/engine/footer.php'; ?>
        </div>



    </div>
    <script src="/assets/js/LearnJSReady.js" type="text/javascript"></script>

</body>

</html>