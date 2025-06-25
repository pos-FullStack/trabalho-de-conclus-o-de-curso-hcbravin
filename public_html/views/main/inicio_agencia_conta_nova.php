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
                <button type="button" onclick="$('#ModalNovaAgencia').modal('show');" class="text-uppercase btn btn-sm btn-primary">Abrir Agência</button>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-4">
        <div class="card shadow-md card-hover">
            <div class="card-body background-virtual-account">
                &nbsp;
            </div>
            <div class="card-footer text-start d-flex justify-content-between">
                <button type="button" onclick="$('#ModalNovaConta').modal('show');" class="text-uppercase btn btn-sm btn-primary">Abrir Conta</button>
                <small class="align-self-center text-uppercase ft-9">Para Estudantes</small>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" id="ModalNovaAgencia">
    <form action="/exe/criar-agencia" method="post">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-bg-primary">
                    <h5 class="modal-title"><i class="fa fa-bank me-1"></i> Abrir Agência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-sm-3">
                            <div class="form-group">
                                <label for="NovaAgenciaCEP"><i class="fa fa-envelope me-1"></i> CEP</label>
                                <input type="text" minlength="9" maxlength="9" id="NovaAgenciaCEP" name="agencia[cep]" data-mask="cep" required="required" class="form-control form-control-sm" placeholder="00000-000" pattern="\d{5}-\d{3}">
                            </div>
                        </div>
                        <div class="col-12 col-sm-9 align-self-end">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" role="switch" id="NovaAgenciaKey" name="agencia[key]" checked>
                                <label class="form-check-label" for="NovaAgenciaKey">Bloquear acesso com código</label>
                            </div>
                            <div class="infomain bd-1 bd-secondary">
                                Ao bloquear o acesso com código será gerado uma chave de acesso onde os estudantes só poderão criar uma conta na sua agência usando a chave.
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <div class="infomain bd-1 bd-danger mb-3">
                                Ao criar sua conta você aceita os termos abaixo.
                            </div>
                            <object data="/files/Politicas_de_Privacidade.pdf" type="application/pdf" width="100%" height="300px">
                                <p>Seu navegador não suporta a exibição de PDFs. <a href="/files/Politicas_de_Privacidade.pdf">Clique aqui para baixar</a>.</p>
                            </object>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Abrir Agência</button>
                </div>
            </div>
        </div>
    </form>
</div>


<div class="modal" tabindex="-1" id="ModalNovaConta">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-bg-info">
                <h5 class="modal-title"><i class="fa fa-piggy-bank me-1"></i> Abrir Nova Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-center mb-2">
                    <div class="col-12 col-sm-6">
                        <label for="NovaAgenciaID"><i class="fa fa-bank me-1"></i> AGÊNCIA</label>
                        <div class="input-group">
                            <input type="number" step="1" id="NovaAgenciaID" name="agencia[numero]" required="required" class="form-control form-control-sm text-center">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="form-group">
                            <label for="NovaAgenciaKeyInfo"><i class="fa fa-key me-1"></i> CÓDIGO DE ACESSO</label>
                            <input type="text" id="NovaAgenciaKeyInfo" disabled name="agencia[key]" required="required" class="form-control form-control-sm text-center">
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-12 collapse" id="NovaAgenciaErro">
                        <div class="infomain bd-1 bd-danger text-center">Agência não encontrada.</div>
                    </div>
                    <div class="col-12 col-sm-8 my-1 collapse" id="NovaAgenciaGerente">
                        <span class="ft-9"><i class="fa fa-user me-1"></i> GERENTE</span>
                        <div class="infomain bd-1 bd-info text-uppercase"></div>
                    </div>
                    <div class="col-12 col-sm-4 my-1 collapse" id="NovaAgenciaCEPInfo">
                        <span class="ft-9"><i class="fa fa-city me-1"></i> CEP</span>
                        <div class="infomain bd-1 bd-info text-center"></div>
                    </div>

                </div>
            </div>
            <div class="modal-footer collapse text-end" id="NovaAgenciaFooter">
                <div class="infomain bd-1 bd-warning mb-1 text-center">
                    Ao criar sua conta na agência você concorda com <a href="/files/Politicas_de_Privacidade.pdf">nossas políticas</a>.
                </div>

                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-sm btn-primary" id="NovaAgenciaSubmit">Abrir Conta</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        // Variável para armazenar o timeout
        let timeout = null;

        // Tempo de espera em milissegundos (ex: 500ms = 0.5 segundos)
        const delay = 500;

        $('#NovaAgenciaID').on('input', function() {
            // Limpa o timeout anterior, se existir
            clearTimeout(timeout);

            // Configura um novo timeout
            timeout = setTimeout(function() {
                const agenciaId = $('#NovaAgenciaID').val();
                const agenciaErro = $('#NovaAgenciaErro');
                const agenciaInput = $('#NovaAgenciaID');
                const gerente = $('#NovaAgenciaGerente');
                const cep = $('#NovaAgenciaCEPInfo');
                const key = $('#NovaAgenciaKeyInfo');
                key.prop("disabled", true);
                const footer = $('#NovaAgenciaFooter');
                footer.hide();

                // Verifica se o valor não está vazio
                if (agenciaId) {
                    // Faz a requisição AJAX
                    $.ajax({
                        url: '/load.php/buscar-agencia/' + agenciaId, // Substitua pelo seu endpoint
                        type: 'GET',
                        success: function(response) {
                            agenciaErro.hide(200);
                            cep.hide();
                            gerente.hide();
                            let json = JSON.parse(response);

                            if (json[0] === false) {
                                agenciaErro.show(200);
                                agenciaInput.focus().select();

                            } else {
                                cep.find('div').text(json.cep);
                                cep.show(200)
                                gerente.find('div').text(json.gerente);
                                gerente.show(250);
                                key.prop("disabled", (json.chave ? false : true));
                                footer.show(500);

                            }
                        },
                        error: function(xhr, status, error) {
                            $('#NovaAgenciaErro').show(200);
                            console.error(error);
                        }
                    });
                }
            }, delay);
        });
    });
</script>