<div class="row">
    <?php foreach ($Cards as $KeyC => $ViewC) { ?>
        <div class="col-12 col-sm-4 col-md-2 mb-3">
            <div class="card card-hover h-100 shadow-md">
                <div class="card-body p-1 h-100 d-flex flex-column">
                    <div>
                        <span class="ft-9 badge-alt text-bg-<?= ($ViewC['sr_tipo'] ? 'success' : 'danger'); ?>"><?= ($ViewC['sr_tipo'] ? 'Sorte' : 'Revés'); ?></span>
                        <p class="text-center mx-2 my-3">
                            <?= $ViewC['sr_nome']; ?>
                        </p>
                    </div>
                    <div class="mt-auto text-center">
                        <div class="ft-9 alert py-1 mb-0 alert-<?= ($ViewC['sr_tipo'] ? 'success' : 'danger'); ?>">
                            <span class="me-2">R$</span> <?= $ViewC['sr_calc']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>