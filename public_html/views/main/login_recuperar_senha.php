<form action="/login/esqueci-minha-senha" method="post">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-6">
            <div class="card shadow-md">
                <div class="card-header text-bg-success"><i class="fa fa-key me-1"></i> Recuperar Senha</div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-8 col-md-8 mb-3">
                            <div class="form-group">
                                <label for="UserCPF" class="main"><i class="fa fa-id-card me-1"></i> CPF</label>
                                <input type="text" name="UserCPF" class="form-control form-control-sm text-uppercase text-center iCPF" minlength="14" maxlength="14" placeholder="000.000.000-00" required pattern="^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$" data-mask="cpf">
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-8 col-md-4 mb-3">
                            <div class="form-group">
                                <label for="UserNascimento" class="main"><i class="fa fa-calendar-day me-1"></i> Data de Nascimento</label>
                                <input type="date" name="UserNascimento" class="form-control form-control-sm text-uppercase text-center" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 col-md-4 mb-3">
                            <div class="form-group">
                                <label for="UserCaptcha" class="main"><i class="fa fa-eye me-1"></i> <?= $gCaptcha['codigo']; ?></label>
                                <input type="number" step="1" name="UserCaptcha[<?=$gCaptcha['key'];?>]" class="form-control form-control-sm text-uppercase text-center" required min="0">
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 col-md-8 mb-4 text-end">
                            <button class="btn btn-sm btn-secondary" type="submit"><i class="fa fa-key me-1"></i> Recuperar Senha</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>