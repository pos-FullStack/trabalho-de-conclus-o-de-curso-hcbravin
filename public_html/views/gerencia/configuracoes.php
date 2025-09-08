<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Home</button>
        <button class="nav-link" id="nav-sortereves-tab" data-bs-toggle="tab" data-bs-target="#nav-sortereves" type="button" role="tab" aria-controls="nav-sortereves" aria-selected="false"><i class="fa fa-masks-theater me-1"></i> Sorte ou Azar</button>
        <button class="nav-link" id="nav-debitos-tab" data-bs-toggle="tab" data-bs-target="#nav-debitos" type="button" role="tab" aria-controls="nav-debitos" aria-selected="false"><i class="fa fa-file-invoice-dollar me-1"></i> Débitos Automáticos</button>
        <button class="nav-link" id="nav-taxas-tab" data-bs-toggle="tab" data-bs-target="#nav-taxas" type="button" role="tab" aria-controls="nav-taxas" aria-selected="false"><i class="fa fa-chart-line me-1"></i> Taxas</button>
    </div>
</nav>
<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade p-3" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">...</div>
    <div class="tab-pane fade p-3 show active" id="nav-sortereves" role="tabpanel" aria-labelledby="nav-sortereves-tab" tabindex="0">
        <div class="infomain bd-1 bd-primary mb-2 shadow-md">
            Sorte ou Azar é um sistema de cartas aleatórias que buscam simular situações do cotidiano onde o estudante poderá ter sorte (ganho) ou revés (perda), baseado em situações do cotidiano. Por exemplo, o gás acabou (revés), você achou um dinheiro no chão (sorte).
        </div>

        <form action="/upg/agencia/sorte" method="post">
            <div class="row">
                <div class="col-12 col-sm-4 col-md-10">
                    <div class="bd-1 bd-success infomain text-center shadow-md py-2">
                        <div class="row">
                            <div class="col-12 col-sm-3">
                                <label for="sorte[quantidade]">Quantidade de Sorteio Semanais</label>
                                <input type="number" step="1" min="0" max="10" name="sorte[quantidade]" id="sorte[quantidade]" class="w-75 mx-auto form-control form-control-sm placeholder-transparente text-center" placeholder="0 a 10" required value="<?=@$Configuracoes['sorte']['quantidade'];?>">
                            </div>
                            <div class="col-12 col-sm-9 text-start align-self-center">
                                <strong>Quantidade de Sorteio Semanais.</strong>
                                <br />
                                Se o valor de sorteio for 0, então não haverá sorteio. Se for 5, será sorteado 5 cartas por dia para cada cliente.
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-12 col-sm-4 col-md-2 text-end">
                    <button type="submit" class="btn btn-sm btn-success w-px-150"><i class="fa fa-save me-1"></i> Salvar</button>
                    <input type="hidden" name="agencia" value="<?=$URI[1];?>">
                </div>
            </div>
        </form>

        <div class="row mt-5">
            <div class="col-12">
                <?php require_once Views . '/gerencia/configuracoes_sorte_reves_cards.php'; ?>
            </div>
        </div>

    </div>
    <div class="tab-pane fade p-3" id="nav-debitos" role="tabpanel" aria-labelledby="nav-debitos-tab" tabindex="0">
        <form action="/upg/agencia/debitos" method="post">
            <div class="row mb-2">
                <div class="col-12 col-sm-8 col-md-10">
                    <div class="infomain bd-1 bd-info shadow-sm ">
                        Os itens podem ter valores fixos de débitos ou poderá corresponder a um percentual do salário do cliente. Em caso de preenchimento dos dois valores sempre prevalecerá o percentual. A variação corresponderá a uma randomização pelo sistema que poderá cobrar +/- o percentual, ou seja, em um valor de 100 reais com variação de 5% a conta poderá corresponder de 95 à 105 reais.
                    </div>
                </div>
                <div class="col-12 col-sm-4 col-md-2 mt-1 mt-sm-0 text-end">
                    <button type="submit" class="btn btn-sm btn-success w-px-150"><i class="fa fa-save me-1"></i> Salvar</button>
                    <input type="hidden" name="agencia" value="<?= $URI[1]; ?>">
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-12 mt-1 mb-2">
                    <div class="infomain bd-1 bd-warning shadow-sm <?= (array_key_exists('ketmuacfc', $Configuracoes['debitos'])) ? '' : 'd-none'; ?>">
                        Não encontramos nenhuma informação salva, por isso, carregamos as informações padrões do sistema. Lembre-se de <strong>SALVAR</strong> as informações após edita-las.
                    </div>

                </div>
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
                            <?php foreach ($Configuracoes['debitos'] as $KeyD => $ViewD) { ?>
                                <tr>
                                    <td class="text-center align-middle"><input type="text" name="debitos[<?= $KeyD; ?>][nome]" class="form-control form-control-sm text-center" value="<?= $ViewD['nome']; ?>"></td>
                                    <td class="text-center align-middle"><input type="number" step="0.01" name="debitos[<?= $KeyD; ?>][valor]" class="form-control form-control-sm text-center placeholder-transparente" placeholder="0.00" min="0" value="<?= $ViewD['valor']; ?>"></td>
                                    <td class="text-center align-middle"><input type="number" step="0.1" name="debitos[<?= $KeyD; ?>][porcentagem]" class="form-control form-control-sm text-center placeholder-transparente" placeholder="10" min="0" max="100" value="<?= $ViewD['porcentagem']; ?>"></td>
                                    <td class="text-center align-middle"><input type="number" step="1" name="debitos[<?= $KeyD; ?>][variacao]" class="form-control form-control-sm text-center placeholder-transparente" placeholder="5" min="0" max="100" value="<?= $ViewD['variacao']; ?>"></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger iTrash"><i class="fa fa-trash"></i></button></td>
                                </tr>
                            <?php } ?>
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

                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-warning w-px-150" id="nav-debitos-itens-insert"><i class="fa fa-plus me-1"></i> Novo Item</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="tab-pane fade p-3" id="nav-taxas" role="tabpanel" aria-labelledby="nav-taxas-tab" tabindex="0">
        <form action="/upg/agencia/taxas" method="post">
            <div class="row">
                <div class="col-12 mb-2 text-end">
                    <button type="submit" class="btn btn-sm btn-success w-px-150"><i class="fa fa-save me-1"></i> Salvar</button>
                    <input type="hidden" name="agencia" value="<?= $URI[1]; ?>">
                </div>
                <div class="col-12 mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="taxas[comportamento]" value="historico" id="taxas[comportamento]historico" <?= iCheck('historico', @$Configuracoes['taxas']['comportamento']); ?>>
                        <label class="form-check-label" for="taxas[comportamento]historico">
                            O comportamento das taxas será baseado na série histórica <a href="https://api.bcb.gov.br/dados/serie/bcdata.sgs.4391/dados?formato=json" class="text-primary" target="_blank">(aqui)</a>
                        </label>
                    </div>
                </div>
                <div class="col-12 mb-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="taxas[comportamento]" value="randomico" id="taxas[comportamento]randomico" <?= iCheck('randomico', @$Configuracoes['taxas']['comportamento']); ?>>
                        <label class="form-check-label" for="taxas[comportamento]randomico">
                            O comportamento das taxas será baseado em valores randômicos que vão de 1% há 10%.
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function() {
        $(document).on('click', 'button.iTrash', function() {
            $(this).closest('tr').remove();
        });
        $('#nav-debitos-itens-insert').click(function() {
            const id = UniqID().replace('_', '');
            $('#nav-debitos-itens tbody').append($('#nav-debitos-itens tfoot').html().replaceAll('{id}', id));
        })
    });
</script>