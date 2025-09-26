<div class="row mb-4">
    <div class="col-12 col-md-4 mb-2">
        <div class="infomain mb-2 bd-1 bd-success shadow-md text-uppercase d-flex justify-content-between">
            <div>
                <span class="badge-alt me-2 text-bg-primary align-self-center"><?= $Conta['cl_conta'] . ' - ' . $Conta['cl_digito']; ?></span>
                <span><i class="fa fa-user me-1"></i> <?= $Conta['ui_nome']; ?></span>
            </div>
            <span class="badge-alt text-bg-<?= $Conta['cl_ativo'] ? 'success' : 'danger'; ?> align-self-center">
                <?= $Conta['cl_ativo'] ? 'Ativo' : 'Inativo'; ?>
            </span>
        </div>
    </div>
    <div class="col-12 col-md-8 mb-2 text-center text-md-end">
        <button class="btn btn-sm w-px-150 btn-danger"><i class="fa fa-lock me-1"></i> Bloquear</button>
        <!-- <button class="btn btn-sm w-px-150 btn-success"></button>
        <button class="btn btn-sm w-px-150 btn-warning"></button>
        <button class="btn btn-sm w-px-150 btn-primary"></button> -->
    </div>
</div>


<div class="row">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
        <div class="card shadow-md">
            <div class="card-body py-0">
                <small class="ft-10">SALDO</small>
                <p class="text-center ft-18 fw-bold text-<?= $Conta['cl_saldo'] > 0 ? 'success' : 'danger'; ?>">
                    <small class="ft-8">R$</small> <?= number_format($Conta['cl_saldo'], 2, ',', ''); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-2">
        <div class="card shadow-md">
            <div class="card-body py-0">
                <small class="ft-10">EMPRÉSTIMOS ABERTO</small>
                <p class="text-center ft-18 fw-bold">
                    <small class="ft-8">R$</small> 0,00
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-2">
        <div class="card shadow-md">
            <div class="card-body py-0">
                <small class="ft-10">INVESTIMENTOS</small>
                <p class="text-center ft-18 fw-bold">
                    <small class="ft-8">R$</small> 0,00
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-2">
        <div class="card shadow-md">
            <div class="card-body py-0">
                <small class="ft-10">PENDÊNCIAS</small>
                <p class="text-center ft-18 fw-bold">
                    0
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-6 align-self-center fw-bold text-uppercase">
                <i class="fa fa-circle-dollar-to-slot me-1"></i> Últimas Transações
            </div>
            <div class="col-6 text-end">
                <a href="/gerencia/<?= $URI[1]; ?>/contas/<?= $URI[3]; ?>/extrato" class="btn btn-sm btn-primary"><i class="fa fa-list me-1"></i> Extrato Completo</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr class="border-dashed-start text-center">
                        <td><i class="fa fa-calendar-day me-1"></i> Data</td>
                        <td><i class="fa fa-tag me-1"></i> Descrição</td>
                        <td><i class="fa fa-tags me-1"></i> Categoria</td>
                        <td><i class="fa fa-dollar-sign me-1"></i> Valor</td>
                        <td><i class="fa fa-scroll me-1"></i> Saldo</td>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>