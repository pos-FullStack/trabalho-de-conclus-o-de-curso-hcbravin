        <!-- Ações Rápidas -->
        <div class="row mb-4">
            <div class="col-12">

                <div class="row justify-content-center overflow-auto pb-2">
                    <div class="col-12 col-sm-12 col-md-8">
                        <h5 class="mb-3">Ações rápidas</h5>
                    </div>
                </div>
                <div class="row justify-content-center overflow-auto pb-2">
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/transferir" class="btn btn-outline-primary w-100">
                            <i class="fa fa-circle-arrow-up fs-4"></i>
                            <br>
                            <small>Transferir</small>
                        </a>
                    </div>
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/pagar" class="btn btn-outline-primary w-100">
                            <i class="fa fa-bolt fs-4"></i>
                            <br>
                            <small>Pagar</small>
                        </a>
                    </div>
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/pix" class="btn btn-outline-primary w-100">
                            <i class="fab fa-pix fs-4"></i>
                            <br>
                            <small>Área Pix</small>
                        </a>
                    </div>
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/cartoes" class="btn btn-outline-primary w-100">
                            <i class="fa fa-credit-card fs-4"></i>
                            <br>
                            <small>Cartões</small>
                        </a>
                    </div>
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/investimento" class="btn btn-outline-primary w-100">
                            <i class="fa fa-dollar-sign fs-4"></i>
                            <br>
                            <small>Investir</small>
                        </a>
                    </div>
                    <div class="col-4 col-sm-3 col-md-1 mb-2">
                        <a href="/conta/<?=$URI[1];?>/extrato" class="btn btn-outline-primary w-100">
                            <i class="fa fa-file-lines fs-4"></i>
                            <br>
                            <small>Extrato</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartões -->
        <div class="row mb-4 justify-content-center">
            <div class="col-12 col-sm-12 col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Meus cartões</h5>
                    <div>
                        <a href="/conta/<?=$URI[1];?>/cartoes/novo" class="btn btn-warning btn-sm <?=(count($Cartoes) >= 2)?'d-none disabled':'';?>"><i class="fa fa-credit-card me-1"></i> Solicitar Cartão</a>
                        <a href="/conta/<?=$URI[1];?>/cartoes" class="btn btn-sm btn-outline-primary w-px-120"><i class="fa fa-credit-card me-1"></i> Ver todos</a>
                    </div>
                </div>
                <div class="row">
                    <?php if (count($Cartoes)) {
                        foreach ($Cartoes as $KeyC => $ViewC) { ?>
                            <div class="col-md-6 mb-3">
                                <div class="card card-hover">
                                    <div class="card-body p-2 ps-4">
                                        <div class="row">
                                            <div class="col-9 mt-2">
                                                <h6 class="card-subtitle mb-2 text-muted">
                                                    Cartão de <?= $Card->Tipo($ViewC['card_tipo']); ?>
                                                </h6>
                                            </div>
                                            <div class="col-3 text-end">
                                                <span class="badge-alt text-bg-<?= ($ViewC['card_ativo']) ? 'success' : 'danger'; ?>"><?= ($ViewC['card_ativo']) ? 'Ativo' : 'Bloqueado'; ?></span>
                                            </div>
                                            <div class="col-12">
                                                <h5 class="card-title">**** **** **** <?= substr($ViewC['card_num'], -4); ?></h5>
                                            </div>
                                            <div class="col-9">
                                                <p class="card-text mb-1">Limite disponível</p>
                                                <h6 class="text-success">R$ <?= number_format($ViewC['card_limite_livre'], 2, ',', '.'); ?></h6>
                                            </div>
                                            <div class="col-3 text-end align-self-end mb-2">
                                                <a href="/cartoes/abrir/<?=$KeyC;?>" class="btn btn-sm btn-secondary ft-9"><i class="fa fa-arrow-up-right-from-square me-1"></i> Abrir</a>
                                            </div>
                                        </div>
                                        <i class="bi bi-credit-card-2-front fs-1 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="col-md-12 mb-3">
                            <div class="text-center bd-1 bd-warning shadow-md p-4 border">
                                Você não possui cartões no momento.
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Últimas Transações -->
        <div class="row justify-content-center">
            <div class="col-12 col-sm-12 col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Últimas transações</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary w-px-120"><i class="fa fa-file-invoice me-1"></i> Ver todas</a>
                </div>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 p-2 rounded me-3 w-px-50 text-center">
                                    <i class="fa fa-cart-shopping text-danger"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Supermercado</h6>
                                    <small class="text-muted">15/05/2023 - Compras</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1 text-danger">- R$ 248,90</h6>
                                <small class="text-muted">Cartão final 1234</small>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded me-3 w-px-50 text-center">
                                    <i class="fa fa-arrow-down text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Depósito</h6>
                                    <small class="text-muted">14/05/2023 - Salário</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1 text-success">+ R$ 5.200,00</h6>
                                <small class="text-muted">Transferência</small>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded me-3 w-px-50 text-center">
                                    <i class="fa fa-bolt text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Conta de Luz</h6>
                                    <small class="text-muted">10/05/2023 - Pagamento</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1 text-danger">- R$ 187,35</h6>
                                <small class="text-muted">Débito automático</small>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 p-2 rounded me-3 w-px-50 text-center">
                                    <i class="fa fa-arrow-up text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Transferência</h6>
                                    <small class="text-muted">08/05/2023 - Maria Silva</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <h6 class="mb-1 text-danger">- R$ 300,00</h6>
                                <small class="text-muted">Pix</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>