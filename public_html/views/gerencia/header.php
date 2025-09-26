<div class="row mb-2">
    <div class="col-6 col-sm-3 col-md-2 mb-2 mb-sm-0">
        <div class="infomain shadow-md bd-1 bd-primary d-flex justify-content-between">
            <span>Agência</span> <strong><?= ZeroEsquerda($MS['gerente'][$URI[1]]['ag_num']); ?></strong>
        </div>
    </div>
    <div class="col-6 col-sm-3 col-md-2 mb-2 mb-sm-0">
        <div class="infomain shadow-md bd-1 bd-secondary d-flex justify-content-between">
            <span><i class="fa fa-<?= strlen($MS['gerente'][$URI[1]]['ag_key']) ? 'lock' : 'unlock'; ?> me-1"></i> Chave</span>
            <span class="mx-1"><?= (strlen($MS['gerente'][$URI[1]]['ag_key'])) ? $MS['gerente'][$URI[1]]['ag_key'] : 'Não Requer'; ?></span>
        </div>
    </div>
    <div class="col-6 col-sm-3 col-md-2 mb-2 mb-sm-0">
        <form action="/upg/agencia/prorrogar" method="post" id="AgenciaTimeSubmit">
            <div class="infomain shadow-md bd-1 d-flex justify-content-between <?= ($MS['gerente'][$URI[1]]['ag_dias'] < 30 ? 'text-bg-danger bd-dark' : 'bd-primary'); ?>">
                <span><i class="fa fa-hourglass-half me-1"></i> <?= $MS['gerente'][$URI[1]]['ag_dias']; ?> dias</span>

                <?php if ($MS['gerente'][$URI[1]]['ag_dias'] < 30) { ?>
                    <input type="hidden" name="agencia" value="<?= $URI[1]; ?>">
                    <i class="fa fa-repeat mt-1 mpoint text-hover-warning" data-toggle="tooltip" title="Adicionar +30 dias" onclick="$('#AgenciaTimeSubmit').submit();"></i>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="col-6 col-sm-3 col-md-3 col-lg-2 offset-md-3 offset-lg-4 text-end">
        <div class="dropdown">
            <button class="w-100 btn btn-sm btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bars me-1"></i> Opções do Gerente
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/gerencia/<?= $URI[1]; ?>/contas"><i class="fa fa-users me-1"></i> Contas</a></li>
                <li><a class="dropdown-item" href="/gerencia/<?= $URI[1]; ?>/transacoes"><i class="fa fa-circle-dollar-to-slot me-1"></i> Transações</a></li>
                <li><a class="dropdown-item" href="/gerencia/<?= $URI[1]; ?>/pendencias"><i class="fa fa-clock me-1"></i> Pendências</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="/gerencia/<?= $URI[1]; ?>/configuracoes"><i class="fa fa-cog me-1"></i> Configurações</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="infomain bd-1 bd-primary mb-2 shadow-md">
            <?php switch($URI[2]){
                case 'contas': print '<i class="fa fa-users me-1"></i> Contas'; break;
                case 'transacoes': print '<i class="fa fa-circle-dollar-to-slot me-1"></i> Transações'; break;
                case 'pendencias': print '<i class="fa fa-clock me-1"></i> Pendências'; break;
                case 'configuracoes': print '<i class="fa fa-cog me-1"></i> Configurações'; break;
                default: print 'Erro.';
            } ?>
        </div>
        
    </div>
</div>