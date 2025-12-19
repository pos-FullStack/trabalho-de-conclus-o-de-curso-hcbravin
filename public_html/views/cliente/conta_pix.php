<div class="row justify-content-center">
    <div class="col-12 col-md-8 mb-2">
        <div class="card shadow-sm">
            <div class="card-header text-bg-verpsc">
                <i class="fab fa-pix me-1"></i> Trasnferir
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-2">
                        <span class="me-1">Destinatário:</span> <strong id="TransferenciaUserNome"><i class="fa fa-user"></i></strong>
                    </div>
                    <hr class="my-1">
                    <div class="col-12 col-sm-6">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-key me-1"></i> Chave PIX</label>
                            <input type="text" class="form-control form-control-sm text-center" name="pixChave" autofocus>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3">
                        <div class="form-group">
                            <label for="" class="main"><i class="fa fa-dollar me-1"></i> Valor</label>
                            <input type="number" step="0.01" class="form-control form-control-sm text-center" max="<?=$MS['contas'][$URI[1]]['cl_saldo'];?>" name="pixValor" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-12 col-sm-3 align-self-end">
                        <button class="btn btn-sm btn-verpsc disabled w-100" type="submit"><i class="fab fa-pix me-1"></i> Transferir</button>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <i class="fa fa-key me-1"></i> Minhas Chaves
            </div>
            <div class="card-body">
                
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 text-end">
                <button type="button" class="btn btn-sm btn-warning"><i class="fa fa-plus me-1"></i> Criar nova chave</button>
            </div>
        </div>
    </div>
</div>
