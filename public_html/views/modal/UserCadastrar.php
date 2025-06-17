<?php $gCaptcha = gCaptcha(true); ?>
<div class="modal" tabindex="-1" id="UserCadastrarModal">
    <form action="/login/criar-conta" method="post">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header text-bg-primary">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-1"></i> Criar Conta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-sm-8 col-md-8 mb-3">
                            <div class="form-group">
                                <label for="UserNome" class="main"><i class="fa fa-id-card me-1"></i> Nome Completo</label>
                                <input type="text" id="UserNome" name="UserNome" class="form-control form-control-sm text-uppercase" minlength="15" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 offset-md-1 mb-3">
                            <div class="form-group">
                                <label for="UserCPF" class="main"><i class="fa fa-id-card me-1"></i> CPF</label>
                                <input type="text" name="UserCPF" class="form-control form-control-sm text-uppercase text-center iCPF" minlength="14" maxlength="14" placeholder="000.000.000-00" required pattern="^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$" data-mask="cpf">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2 mb-3">
                            <div class="form-group">
                                <label for="UserNascimento" class="main"><i class="fa fa-calendar-day me-1"></i> Data de Nascimento</label>
                                <input type="date" name="UserNascimento" class="form-control form-control-sm text-uppercase text-center" required>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2 mb-3">
                            <div class="form-group">
                                <label for="UserSexo" class="main"><i class="fa fa-venus-mars me-1"></i> Sexo</label>
                                <select name="UserSexo" id="UserSexo" class="form-select form-select-sm" required>
                                    <option value=""></option>
                                    <option value="0">Feminino</option>
                                    <option value="1">Masculino</option>
                                    <option value="2">Não Informar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2 mb-3">
                            <div class="form-group">
                                <label for="UserCEP" class="main"><i class="fa fa-location me-1"></i> CEP</label>
                                <input type="text" class="form-control form-control-sm text-center" id="UserCEP" name="UserCEP" placeholder="00000-000" required pattern="^\d{5}-?\d{3}$" data-mask="cep">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2 mb-3">
                            <div class="form-group">
                                <label for="UserTel" class="main"><i class="fa fa-phone me-1"></i> Telefone</label>
                                <input type="text" class="form-control form-control-sm text-center" id="UserTel" name="UserTel" placeholder="(00) 00000-0000" required data-mask="celphone">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 mb-3">
                            <div class="form-group">
                                <label for="UserEmail" class="main"><i class="fa fa-envelope me-1"></i> Email</label>
                                <input type="email" class="form-control form-control-sm" id="UserEmail" name="UserEmail" required>
                            </div>
                        </div>
                        <div class="mt-2 col-12">
                            <span class="me-2 d-none d-sm-inline-block"><i class="fa fa-info-circle me-1"></i> Você quer:</span>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="UserOpen" id="UserOpen1" value="1" required>
                                <label class="form-check-label" for="UserOpen1">Abrir Agência (Professor)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="UserOpen" id="UserOpen2" value="2" required>
                                <label class="form-check-label" for="UserOpen2">Abrir Conta (Estudante)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Criar Conta</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal" tabindex="-1" id="UserLoginModal">
    <form action="/login/entrar" method="post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-bg-success">
                    <h5 class="modal-title"><i class="fa fa-right-to-bracket me-1"></i> Entrar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12 col-sm-8 mb-3">
                            <div class="form-group">
                                <label for="UserCPF" class="main"><i class="fa fa-id-card me-1"></i> CPF</label>
                                <input type="text" name="UserCPF" class="form-control form-control-sm text-uppercase text-center iCPF" minlength="14" maxlength="14" placeholder="000.000.000-00" required pattern="^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$" data-mask="cpf">
                            </div>
                        </div>
                        <div class="col-12 col-sm-8 mb-3">
                            <div class="form-group">
                                <label for="UserSenha" class="main"><i class="fa fa-key me-1"></i> Senha</label>
                                <input type="password" name="UserSenha" class="form-control form-control-sm text-uppercase text-center" required>
                            </div>
                        </div>

                        <div class="col-12 col-sm-8 mb-3">
                            <div class="form-group">
                                <label for="UserCaptcha" class="main"><i class="fa fa-eye me-1"></i> <?= $gCaptcha['codigo']; ?></label>
                                <input type="number" step="1" name="UserCaptcha[<?= $gCaptcha['key']; ?>]" class="form-control form-control-sm text-uppercase text-center" required min="0">
                            </div>
                        </div>

                        <div class="col-12 col-sm-8 mb-3">
                            <a href="/login/esqueci-minha-senha" class="btn-link ft-10 text-uppercase">Esqueci Minha Senha</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-right-to-bracket me-1"></i> Entrar</button>
                </div>
            </div>
        </div>
    </form>
</div>