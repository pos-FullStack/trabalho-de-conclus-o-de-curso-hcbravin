<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        
        <div class="row">
            <div class="col-8"><div class="infomain bd-1 bd-success shadow-sm"><i class="fa fa-circle-arrow-up me-1"></i> Transferir</div></div>
            <div class="col-4 text-end">
                <a href="/conta/<?=$URI[1];?>/pix" class="btn btn-sm btn-verpsc w-px-100"><i class="fab fa-pix me-1"></i> Pix</a>
            </div>
        </div>

        <div class="row justify-content-center mt-2">
            <div class="col-12 mb-4">
                <div class="infomain bd-1 bd-secondary shadow-sm">
                    Aqui você poderá realizar transações financeiras com base nos seus fundos para outras contas, seja da mesma agência ou de outras agências. O valor será descontado de sua conta (caso haja saldo) e enviado para a conta de destino. Para isso, preencha os campos abaixo.
                </div>
            </div>

            <div class="col-12 mb-2">
                <div class="infomain bd-1 bd-primary shadow-sm">
                    <span class="me-1">Destinatário:</span> <strong id="TransferenciaUserNome"><i class="fa fa-user"></i></strong>
                </div>
            </div>

            <div class="col-8 col-sm-4 col-md-3 mb-2">
                <div class="form-group">
                    <label for="" class="main"><i class="fa fa-bank me-1"></i> AGÊNCIA</label>
                    <input type="number" step="1" min="1" class="form-control form-control-sm text-center" placeholder="Ex: 5 ou 00005">
                </div>
            </div>
            <div class="col-8 col-sm-4 col-md-3 mb-2">
                <div class="form-group">
                    <label for="" class="main"><i class="fa fa-user me-1"></i> CONTA</label>
                    <input type="number" step="1" min="1" maxlength="5" class="form-control form-control-sm text-center" placeholder="Sem dígito verificador">
                </div>
            </div>
            <div class="col-8 col-sm-4 col-md-3 mb-2">
                <div class="form-group">
                    <label for="" class="main"><i class="fa fa-dollar me-1"></i> VALOR</label>
                    <input type="number" step="1" min="0" class="form-control form-control-sm text-center" placeholder="0,00">
                </div>
            </div>
            <div class="col-8 col-sm-4 col-md-3 mb-2 align-self-end">
                <button class="btn btn-sm btn-success w-100 disabled" type="submit"><i class="fa fa-circle-arrow-up me-1"></i> Transferir</button>
            </div>
        </div>
    </div>
</div>