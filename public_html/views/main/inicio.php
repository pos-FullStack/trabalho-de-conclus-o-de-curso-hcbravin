<div class="row justify-content-center">
    <?php if(isset($MS['contas']) AND count($MS['contas'])){ ?>
    <div class="col-12 col-sm-10 col-md-8 mb-2">
        <div class="infomain text-bg-primary shadow-md"><i class="fa fa-piggy-bank me-1"></i> Minhas Contas</div>
        <div class="row mt-2">
            <?php foreach($MS['contas'] as $KeyC=>$ViewC){  ?>
            <div class="col-12 col-sm-6 col-md-6 mb-2">
                <a href="/conta/<?= $KeyC; ?>">
                    <div class="card card-hover shadow-sm">
                        <div class="card-body">
                            <p class="mb-0"><span class="badge-alt text-bg-secondary me-1 w-px-80">Gerente</span> <?= mb_strtoupper($ViewC['ui_nome'],'UTF-8'); ?></p>
                            <p class="mb-0"><span class="badge-alt text-bg-secondary me-1 w-px-80">Agência</span> <?= ZeroEsquerda($ViewC['ag_num']); ?></p>
                            <p><span class="badge-alt text-bg-primary me-1 w-px-80">Conta</span> <?= ($ViewC['cl_conta']) . ' - ' . $ViewC['cl_digito']; ?></p>
                            <h2 class="text-center mt-2 fw-bold mb-0 text-<?= $ViewC['cl_saldo'] > 0 ? 'primary' : 'danger'; ?>">
                                <small class="ft-9">R$ </small><?= number_format($ViewC['cl_saldo'],2,',','.'); ?>
                            </h2>
                        </div>
                        <div class="card-footer ft-9 d-flex justify-content-between">
                            <span>CEP: <?= $ViewC['ag_cep']; ?></span>
                            <span><?= Data($ViewC['cl_dref'],'ano'); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php } ?>            
        </div>
    </div>
    <?php } ?>

    <?php if(isset($MS['gerente']) AND count($MS['gerente'])){ ?>
    <div class="col-12 col-sm-10 col-md-8 mb-2">
        <div class="infomain text-bg-secondary shadow-md"><i class="fa fa-bank me-1"></i> Minhas Gerências</div>
        <div class="row mt-2">
            <?php foreach($MS['gerente'] as $KeyC=>$ViewC){ ?>
            <div class="col-12 col-sm-6 col-md-3 mb-2">
                <a href="/gerencia/<?= $KeyC; ?>">
                    <div class="card card-hover shadow-sm">
                        <div class="card-body">
                            <p class="mb-0"><span class="badge-alt text-bg-secondary me-1 w-px-80">Agência</span> <span class="w-px-100"><?= ZeroEsquerda($ViewC['ag_num']); ?></span></p>
                            <p class="mb-0"><span class="badge-alt text-bg-secondary me-1 w-px-80">Chave</span> <span class="w-px-100"><?= $ViewC['ag_key']; ?></span></p>
                            <p class="mb-0"><span class="badge-alt text-bg-secondary me-1 w-px-80">Fechará</span> <span class="text-<?= $ViewC['ag_dias'] < 30 ? 'danger fw-bold':''; ?>"><?= $ViewC['ag_dias']; ?> dias</span></p>
                        </div>
                        <div class="card-footer ft-9 d-flex justify-content-between">
                            <span>CEP: <?= $ViewC['ag_cep']; ?></span>
                            <span><?= Data($ViewC['ag_dref'],'ano'); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php } ?>            
        </div>
    </div>
    <?php } ?>
</div>
<script>
    $(function(){
        $('#page-content-wrapper').css('background-image','url("/images/background-poly.jpg")');
    })
</script>