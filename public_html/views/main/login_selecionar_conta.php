<?php if (count($MS['contas'])) { ?>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="infomain bd-1 bd-primary shadow-md mb-2">
                Que ótimo, você tem várias contas em nosso banco. Qual delas você quer acessar?
            </div>
        </div>
        <?php foreach ($MS['contas'] as $KeyC => $ViewC) { ?>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="card shadow-md card-hover">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="badge-alt text-bg-secondary"><?= Data($ViewC['cl_dref'], 14); ?></span>
                            <span class="badge-alt text-bg-secondary">Conta <?= (new Usuario())->ContaTipo($ViewC['cl_tipo']); ?></span>
                            <span class="badge-alt text-bg-<?= ($ViewC['cl_ativo']) ? 'success' : 'danger'; ?>"><?= ($ViewC['cl_ativo']) ? 'Ativo' : 'Inativo'; ?></span>
                        </div>
                        <hr class="my-2">
                        <div class="my-2 py-3 w-100 ft-10">
                            <strong class="me-1"><i class="fa fa-user me-1"></i> GERENTE:</strong>
                            <div class="alert py-1 mt-0 alert-primary text-center">
                                <?= strtoupper($ViewC['ui_nome']); ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold align-self-center"><?= $ViewC['cl_conta']; ?> - <?= $ViewC['cl_digito']; ?></h5>
                            <a href="/login/acessar-conta/<?= $ViewC['cl_id']; ?>" class="btn btn-warning"><i class="fa fa-piggy-bank me-1"></i> Acessar</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>


<?php } else { require_once Views . '/main/inicio_agencia_conta_nova.php'; } ?>