<div class="row justify-content-between mb-2">
    <div class="col-12 col-sm-4 col-md-6">
        <a href="/gerencia/<?=$URI[1];?>/contas">
            <div class="btn-group w-px-200">
                <button class="btn btn-sm btn-outline-dark w-px-75">Agência</button>
                <button class="btn btn-sm btn-dark w-px-75"><?=ZeroEsquerda($MS['gerente'][$URI[1]]['ag_num']);?></button>
            </div>
            <div class="btn-group w-px-200">
                <button class="btn btn-sm btn-outline-success w-px-75">Contas</button>
                <button class="btn btn-sm btn-success w-px-75"><?=$MS['gerente'][$URI[1]]['total_clientes'];?></button>
            </div>
        </a>
        <a href="/gerencia/<?=$URI[1];?>/configuracoes" class="btn btn-sm btn-secondary w-px-150"><i class="fa fa-cog me-1"></i> Configurações</a>
    </div>
    <div class="col-12 col-sm-4 col-md-6 text-end">
        <form action="/upg/agencia/prorrogar" method="post">
            <div class="btn-group">
                <div class="infomain alert alert-primary mb-0">
                    <strong>CHAVE:</strong>
                    <span class="mx-1"><?=(strlen($MS['gerente'][$URI[1]]['ag_key'])) ? $MS['gerente'][$URI[1]]['ag_key'] : 'Não Requer';?></span>
                    <i class="fa fa-<?=strlen($MS['gerente'][$URI[1]]['ag_key'])?'lock':'unlock';?> ms-2"></i>
                </div>
            </div>
            <div class="btn-group">
                <span class="btn btn-warning ft-10 text-dark w-100">
                    Sua agência ficará aberta por <?=$MS['gerente'][$URI[1]]['ag_dias'];?> dias
                </span>
                <?php if($MS['gerente'][$URI[1]]['ag_dias'] < 30){ ?>
                <input type="hidden" name="agencia" value="<?=$URI[1];?>">
                <button type="submit" class="btn btn-sm btn-success w-px-150"><i class="fa fa-hourglass-half me-1"></i> +30 Dias</button>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="col-12">
        <div class="infomain bd-1 bd-primary shadow-md mt-2">
            <?=(!$URI[2])?'<i class="fa fa-bank me-1"></i> GERENCIA DE CONTAS':strtoupper(str_replace('-',' ',$URI[2]));?>
        </div>
    </div>
</div>