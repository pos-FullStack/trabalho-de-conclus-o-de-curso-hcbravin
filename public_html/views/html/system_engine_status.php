<?php $SHDR = (isset($SHDR)) ? $SHDR : 2; ?>

<div class="row justify-content-center mt-3">
    <div class="col-12 col-sm-10 col-md-9">
        <div class="card shadow-md">
            <div class="card-header text-bg-<?= $C == 0 ? 'success' : 'danger'; ?>">
                <i class="fa fa-<?= $C == 0 ? 'check' : 'triangle-exclamation'; ?> me-1"></i> STATUS DA OPERAÇÃO
            </div>
            <div class="card-body text-center">
                <?php if ($C == 0) { ?>

                    A operação foi concluída com sucesso!

                <?php } else { ?>

                    <p class="">A operação não pode ser concluída.</p>
                    Estamos te redirecionando para página anterior e pedimos que tente novamente.<br />Caso o problema persista entre em contato com o suporte técnico.

                <?php } ?>

                <div class="progress mt-4" role="progressbar" aria-label="Redirecionando" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-<?=$C==0?'success':'danger';?>" id="shdr_bar" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>