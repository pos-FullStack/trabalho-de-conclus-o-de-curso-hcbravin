<div class="row justify-content-center">
    <div class="col-12 col-sm-8 mb-2 text-end">
        <button class="btn btn-sm btn-warning"><i class="fa fa-circle-dollar-to-slot me-1"></i> Novo Investimento</button>
    </div>

    <div class="col-12 col-md-8">
        <div class="row">
            <div class="col-12 mb-2">
                <div class="infomain bd-1 bd-success shadow-sm"><i class="fa fa-money-bill-trend-up me-1"></i> Meus Investimentos</div>
            </div>

            <div class="col-12">
                <div class="row">
                    <?php if (!is_array($InvestimentoLista) or count($InvestimentoLista) == 0) { ?>
                        <div class="col-12">
                            <div class="infomain bd-1 bd-danger py-3 text-center">Você não possui nenhum investimento ativo!</div>
                        </div>
                        <?php } else {
                        foreach ($InvestimentoLista as $KeyI => $ViewI) { ?>
                            <div class="col-12 col-sm-6 mb-2">
                                <div class="infomain shadow-sm card-hover">
                                    <div class="row">

                                        <div class="col-5 text-end fw-bold">Tipo</div>
                                        <div class="col-7 text-start">
                                            <?= $Investimentos->Tipos($ViewI['inv_tipo'])['nome']; ?>
                                        </div>

                                        <div class="col-5 text-end fw-bold">Taxa</div>
                                        <div class="col-7 text-start"><?= $ViewI['inv_taxa']; ?> % a.m.</div>

                                        <div class="col-5 text-end fw-bold">Capital</div>
                                        <div class="col-7 text-start">R$ <?= number_format($ViewI['inv_capital'], 2, ',', '.'); ?></div>

                                        <div class="col-5 text-end fw-bold">Restante</div>
                                        <div class="col-7 text-start"><?= $ViewI['inv_tempo'] - $ViewI['inv_meses']; ?> meses</div>

                                        <div class="col-5 text-end fw-bold">Juros</div>
                                        <div class="col-7 text-start">
                                            R$ <?= number_format(($ViewI['inv_saldo'] ? 0 : ($ViewI['inv_saldo'] - $ViewI['inv_capital'])), 2, ',', '.'); ?>
                                        </div>
                                        <div class="col-12 mt-2 text-end">
                                            <button class="btn btn-sm btn-success"><i class="fa fa-sack-dollar me-1"></i> Resgatar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php }
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" tabindex="-1" id="InvestimentoNovo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-bg-warning">
                <span class="modal-title"><i class="fa fa-circle-dollar-to-slot me-1"></i> Novo Investimento</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8 mb-2">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-money-bill-transfer me-1"></i> Tipo de Investimento</label>
                            <select name="" id="" class="form-select form-select-sm">
                                <?php foreach ($Investimentos->Tipos() as $KeyI => $ViewI) { ?>
                                    <option value="<?= $KeyI; ?>"><?= $ViewI['nome']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-4 mb-2">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-percentage me-1"></i> Taxa</label>
                            <span class="form-control form-control-sm text-center">0,00</span>
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-dollar me-1"></i> Valor</label>
                            <input type="number" step="0.01" class="form-control form-control-sm text-center" max="<?= $MS['contas'][$URI[1]]['cl_saldo']; ?>" name="pixValor" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-6 mb-2">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-calendar me-1"></i> Período</label>
                            <input type="number" step="1" min="1" max="48" class="form-control form-control-sm text-center" max="<?= $MS['contas'][$URI[1]]['cl_saldo']; ?>" name="pixValor" placeholder="Nº de Meses">
                        </div>
                    </div>
                    <div class="col-12 mt-4 text-end">
                        <button type="button" class="btn btn-warning btn-sm disabled w-px-150" id="InvestirNovoSubmit"><i class="fa fa-circle-dollar-to-slot me-1"></i> Investir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#InvestimentoNovo').modal('show');
    })
</script>