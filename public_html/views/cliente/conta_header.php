<!-- Saudação e Saldo -->
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card text-bg-primary shadow">
            <div class="card-body">
                <div class="row">
                    <div class="col-8 col-sm-9 col-md-10">
                        <h5 class="card-title">Olá, <?= mb_convert_case($MS['ui_nome'], MB_CASE_TITLE, 'UTF-8'); ?>!</h5>
                    </div>
                    <div class="col-4 col-sm-3 col-md-2 text-end <?=(!$URI[2]?'d-none':'');?>">
                        <a href="/conta/<?=$URI[1];?>" class="btn btn-sm btn-warning ft-10"><i class="fa fa-rotate-left"></i> Início</a>
                    </div>
                </div>
                <p class="card-text mb-1">Saldo disponível</p>
                <h2 class="fw-bold">R$ <?=number_format($MS['contas'][$URI[1]]['cl_saldo'],2,',','.');?> </h2>
                <div class="d-flex justify-content-between mt-3">
                    <small>Conta: <?=$MS['contas'][$URI[1]]['cl_conta'].'-'.$MS['contas'][$URI[1]]['cl_digito'];?></small>
                    <small>Agência: <?=ZeroEsquerda($MS['contas'][$URI[1]]['ag_num']);?></small>
                </div>
            </div>
        </div>
    </div>
</div>