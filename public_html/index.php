<?php
session_start();
require_once __DIR__ . '/src/ConfigSystem.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once __DIR__ . '/src/header.php'; ?>

<body class="<?=($ExibirPainel?'sb-sidenav-toggled':'');?>">
    <div class="d-flex flex-row min-vh-100" id="wrapper">

        <?php
        // VERIFICA AS POLITICAS DE PRIVACIDADE E SE ESTÃO ACEITAS
        require_once Views . '/main/politicas_load.php';
        if ($PolyLock) {
            goto FimAPP;
        }
        ?>
        <!-- Sidebar-->
        <div class="border-end bg-gray-700 MainMenu d-print-none <?=(Logado(true) AND is_Engine()==false)?NULL:'d-none';?>" id="sidebar-wrapper">
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
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom d-print-none">
                <div class="container-fluid">
                    
                    <button class="btn btn-light py-0 ft-12 <?=(Logado(true) AND !is_engine())?NULL:'d-none';?>" id="sidebarToggle"><i class="fa fa-<?=($Mobile OR $ExibirPainel)?'bars':'square-caret-left';?>"></i></button>
                    <div class="edbank text-white d-none d-sm-inline-block"><i class="fa fa-bank me-1 <?=Logado()?'ms-2':'';?>"></i> <strong>ED Bank</strong></div>
                    <button class="navbar-toggler py-0 border-0 ft-12" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="fa fa-bars"></i></button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                            <?php if(Logado()){ ?>
                            <li class="nav-item"><a class="nav-link px-1" href="/" data-toggle="tooltip" title="Início"><span class="badge-alt text-bg-dark"><i class="fa fa-home"></i> <span class="d-md-none">Home</span></span></a></li>
                            <li class="nav-item"><a class="nav-link px-1" href="/logout"><span class="badge-alt text-bg-danger" data-toggle="tooltip" title="Sair"><i class="fa fa-right-from-bracket"></i> <span class="d-md-none">Sair</span></span></a></li>
                            <?php }else{ ?>
                            <li class="nav-item"><span class="nav-link px-1"><button type="button" id="UserCadastrar" class="UserCadastrar btn btn-sm btn-primary" onclick="$('#UserCadastrarModal').modal('show');"><i class="fa fa-user-plus me-1"></i> Criar Conta</button></span></li>
                            <li class="nav-item"><span class="nav-link px-1"><button type="button" id="UserLogin" class="UserLogin btn btn-sm btn-success" onclick="$('#UserLoginModal').modal('show');"><i class="fa fa-right-to-bracket me-1"></i> Entrar</button></span></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- Page content-->
            <div class="container-fluid pt-2 pb-5">

                <?php
                // ESTÁ LOGADO?
                LoadingPage:
                if (isset($MS['ui_id'], $URI[0])) {
                    if (is_file(Controller . "/main/$URI[0].php")) {
                        // É UM ARQUIVO MAIN ?
                        // ARQUIVOS MAIN SÃO AQUELES QUE SE SOBREPÕE AOS ARQUIVOS LOCAIS DOS TIPOS DE USUÁRIO
                        // UM ARQUIVO MAIN COM NOME meus-dados POR EXEMPLO, É SUPERIOR A UM ARQUIVO LOCAL meus-dados E SERÁ CARREGADO PRIMEIRO
                        require_once Controller . "/main/$URI[0].php";
                    } else { // PROCURA NA PASTA DO TIPO
                        if (isset($MEUTIPO) AND is_file(Controller . "/" . UserTipo($MEUTIPO) . "/$URI[0].php")) {
                            // SE FOR UM ARQUIVO DO TIPO DE USUÁRIO LOCAL, CARREGA-0
                            // NÃO ESTÁ IMPLEMENTADO A DIFERENCIAÇÃO DE ENG PARA MOB
                            require_once Controller . "/" . UserTipo($MEUTIPO) . "/$URI[0].php";
                        } else {
                            // CASO NÃO SEJA DE NENHUM, RETORNARÁ ERRO 404
                            require_once Views . "/html/404.html";
                        }
                    }
                } else {

                     // // VERIFICA SE ESTA LOGADO E EXIBE A PAGINA DE LOGIN, SE NECESSÁRIO
                    if (!$URI[0] AND $URI[0] != 'logout' AND !UrlOpen()) {
                        // EXIBE A PAGINA DE LOGIN PARA ESCOLHA DO USUARIO
                        require_once Controller . '/main/login.php';
                        

                    }else{
                        if (UrlOpen()) { // A URL É PUBLICA ?
                            if (is_file(Controller . "/main/$URI[0].php")) {
                                // O ARQUIVO EXISTE NO DIRETORIO GLOBAL?
                                require_once Controller . "/main/$URI[0].php";

                            } else {
                                // APRESENTA ERRO
                                require_once Views . '/html/404.html';
                            }
                        } else {
                            require_once Views . '/main/home.php';
                        }
                    }
                }
                ?>
                <?php 
                    #require_once __DIR__ . '/src/cookies_accept.php';

                    if(Logado()){
                        // require_once __DIR__ . '/src/modal_turno.php'; 
                        require_once __DIR__ . '/src/modal_aguarde.php'; 
                        // require_once __DIR__ . '/src/modal_myFiles.php'; 
                        // require_once __DIR__ . '/src/modal_cfm.php'; 
                        // require_once __DIR__ . '/src/box_sct.php'; 
                        // require_once __DIR__ . '/src/modal_year.php'; 
                    }else{
                        require_once Views . '/modal/UserCadastrar.php';
                    }
                ?>
                <?php FimAPP: ?>
            </div>
            
        </div>
    </div>
    <?php require_once Src . '/footer.php'; ?>
    <script src="/js/LearnJSReady.js" type="text/javascript"></script>

</body>

</html>