<div class="infomain bd-1 bd-warning mb-4 shadow-md">
    <i class="fa fa-bank me-1"></i> GERENCIAS DESATIVADAS
</div>

<?php if(count($Map) == 0){ alert('Você não possui nenhuma agência desativada.'); }else{ ?>
    <div class="row justify-content-center">
        <?php foreach($Map as $ViewM){ if(array_key_exists($ViewM,$MS['gerente'])){ ?>
        <div class="col-12 col-sm-4 col-md-3 mb-2">
            <a href="/gerencia/<?=$ViewM;?>">
                <div class="card card-hover shadow-md">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-end fw-bold">AGÊNCIA</div>
                            <div class="col-6"><?=ZeroEsquerda($MS['gerente'][$ViewM]['ag_num']);?></div>
                            <div class="col-6 text-end fw-bold">CEP</div>
                            <div class="col-6"><?=ZeroEsquerda($MS['gerente'][$ViewM]['ag_cep']);?></div>
                            <div class="col-6 mt-2 text-end fw-bold">CLIENTES</div>
                            <div class="col-6 mt-2"><?=($MS['gerente'][$ViewM]['total_clientes']);?></div>
                        </div>
                    </div>
                    <div class="card-footer ft-10 text-center py-1">
                        Desativada em <?=Data($MS['gerente'][$ViewM]['ag_fim'],3);?>
                    </div>
                </div>
            </a>
        </div>
        <?php }} ?>
    </div>
<?php } ?>