<div class="infomain bd-1 bd-primary shadow mb-3">
    Os convites recebidos por você estão abaixo:
</div>

<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-8">
        <ol class="list-group list-group-numbered">
            <?php foreach($Convites['subgerentes'] as $ViewC){ ?>
            <li class="list-group-item d-sm-flex justify-content-sm-between align-items-start">
                <div class="ms-2 me-auto mb-3 mb-sm-0">
                    <div class="fw-bold mb-2 mb-sm-0"><?= $ViewC['user_nome']; ?></div>
                    Venha ser subgerente na minha agência <strong><?= ZeroEsquerda($ViewC['ag_num']); ?></strong>, <strong><?= $ViewC['ag_info']; ?></strong>. Localizada no CEP <?= $ViewC['ag_cep']; ?>.
                </div>
                <a href="/upg/account/convites/<?= $ViewC['agg_id']; ?>" class="btn btn-primary btn-sm align-self-center float-end float-sm-none"><i class="bi bi-envelope-check-fill me-1"></i> Aceitar</a>
            </li>
            <?php } ?>
        </ol>      
    </div>
</div>