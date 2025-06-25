<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Home</button>
        <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Profile</button>
        <button class="nav-link" id="nav-debitos-tab" data-bs-toggle="tab" data-bs-target="#nav-debitos" type="button" role="tab" aria-controls="nav-debitos" aria-selected="false"><i class="fa fa-file-invoice-dollar me-1"></i> Débitos Automáticos</button>
        <button class="nav-link" id="nav-taxas-tab" data-bs-toggle="tab" data-bs-target="#nav-taxas" type="button" role="tab" aria-controls="nav-taxas" aria-selected="false"><i class="fa fa-chart-line me-1"></i> Taxas</button>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">...</div>
    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">...</div>
    <div class="tab-pane fade p-3" id="nav-debitos" role="tabpanel" aria-labelledby="nav-debitos-tab" tabindex="0">
        <div class="row mb-2">
            <div class="col-12 col-sm-8 col-md-10">
                <div class="infomain bd-1 bd-info shadow-sm ">
                    Os itens podem ter valores fixos de débitos ou poderá corresponder a um percentual do salário do cliente. Em caso de preenchimento dos dois valores sempre prevalecerá o percentual. A variação corresponderá a uma randomização pelo sistema que poderá cobrar +/- o percentual, ou seja, em um valor de 100 reais com variação de 5% a conta poderá corresponder de 95 à 105 reais.
                </div>
            </div>
            <div class="col-12 col-sm-4 col-md-2 mt-1 mt-sm-0">
                <button type="button" class="btn btn-sm btn-warning w-px-150" id="nav-debitos-itens-insert"><i class="fa fa-plus me-1"></i> Novo Item</button>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8">
                <table class="table table-sm table-striped" id="nav-debitos-itens">
                    <thead>
                        <tr class="main">
                            <td class="w-40">Nome</td>
                            <td class="w-17">Valor R$</td>
                            <td class="w-17">Porcentagem %</td>
                            <td class="w-17">Variação <i class="fa fa-plus-minus"></i></td>
                            <td class="w-9">Excluir</td>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                    
                    <tfoot class="d-none">
                        <tr>
                            <td class="text-center align-middle"><input type="text" name="debitos[{id}][nome]" class="form-control form-control-sm text-center"></td>
                            <td class="text-center align-middle"><input type="number" step="0.01" name="debitos[{id}][valor]" class="form-control form-control-sm text-center" placeholder="0.00" min="0"></td>
                            <td class="text-center align-middle"><input type="number" step="0.1" name="debitos[{id}][porcentagem]" class="form-control form-control-sm text-center" placeholder="10" min="0" max="100"></td>
                            <td class="text-center align-middle"><input type="number" step="1" name="debitos[{id}][variacao]" class="form-control form-control-sm text-center" placeholder="5" min="0" max="100"></td>
                            <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger iTrash"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade p-3" id="nav-taxas" role="tabpanel" aria-labelledby="nav-taxas-tab" tabindex="0">
        <div class="row">
            <div class="col-12 mb-1">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="taxas[comportamento]" value="historico" id="taxas[comportamento]historico">
                    <label class="form-check-label" for="taxas[comportamento]historico">
                        O comportamento das taxas será baseado na série histórica <a href="https://api.bcb.gov.br/dados/serie/bcdata.sgs.4391/dados?formato=json" class="text-primary" target="_blank">(aqui)</a>
                    </label>
                </div>
            </div>
            <div class="col-12 mb-1">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="taxas[comportamento]" value="randomico" id="taxas[comportamento]randomico">
                    <label class="form-check-label" for="taxas[comportamento]randomico">
                        O comportamento das taxas será baseado em valores randômicos que vão de 1% há 10%.
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(function(){
        $(document).on('click','button.iTrash',function(){$(this).closest('tr').remove();});
        $('#nav-debitos-itens-insert').click(function(){
            const id = UniqID().replace('_','');
            $('#nav-debitos-itens tbody').append($('#nav-debitos-itens tfoot').html().replaceAll('{id}',id));
        })
    });
</script>