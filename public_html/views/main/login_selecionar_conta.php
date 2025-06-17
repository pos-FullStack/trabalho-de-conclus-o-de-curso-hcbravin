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
<?php } else { ?>
    <div class="row justify-content-center">
        <div class="col-12 mb-3">
            <div class="infomain bd-1 bd-danger shadow-md mb-2">
                Poxa, sentimos muito, mas, não encontramos nenhuma conta no nosso banco relacionado ao seus dados.
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-md card-hover">
                <div class="card-body text-center background-virtual-bank">
                    &nbsp;
                </div>
                <div class="card-footer text-end d-flex justify-content-between">
                    <small class="align-self-center text-uppercase ft-9">Para Professores</small>
                    <a class="text-uppercase btn btn-sm btn-primary">Abrir Agência</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-md card-hover">
                <div class="card-body background-virtual-account">
                    &nbsp;
                </div>
                <div class="card-footer text-start d-flex justify-content-between">
                    <a class="text-uppercase btn btn-sm btn-primary">Abrir Conta</a>
                    <small class="align-self-center text-uppercase ft-9">Para Estudantes</small>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="ModalNovaAgencia">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-bg-primary">
                    <h5 class="modal-title"><i class="fa fa-bank me-1"></i> Abrir Agência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="NovaAgenciaEndereco"><i class="fa fa-home me-1"></i> ENDEREÇO</label>
                                <input type="text" id="NovaAgenciaEndereco" name="agencia[endereco]" required="required" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-sm btn-primary">Abrir Agência</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" tabindex="-1" id="ModalNovaConta">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modal body text goes here.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>


<script>
    $(function(){
        $('#ModalNovaAgencia').modal('show');
    })
</script>

<?php } ?>