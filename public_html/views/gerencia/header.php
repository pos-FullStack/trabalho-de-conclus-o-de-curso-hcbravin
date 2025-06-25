<div class="row justify-content-center mb-2">
    
    <div class="col-12 col-sm-4 col-md-2">
        <a href="/gerencia/<?=$URI[1];?>/contas">
            <div class="btn-group w-100">
                <button class="btn btn-sm btn-outline-success">Contas</button>
                <button class="btn btn-sm btn-success w-px-125">77</button>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-4 col-md-2">
        <a href="/gerencia/<?=$URI[1];?>/configuracoes" class="btn btn-sm btn-secondary w-100"><i class="fa fa-cog me-1"></i> Configurações</a>
    </div>

    <div class="col-12">
        <div class="infomain bd-1 bd-primary shadow-md mt-2">
            <?=(!$URI[2])?'GERENCIA DE CONTAS - HOME':strtoupper(str_replace('-',' ',$URI[2]));?>
        </div>
    
    </div>
</div>
    