<div class="<?=$Mobile?'table-responsive':'';?>">
    <table class="table table-hover table-sm mb-0 ft-10" id="TableClientes">
        <thead>
            <tr class="main">
                <td>Conta</td>
                <td>Titular</td>
                <td>Saldo</td>
                <td>Status</td>
                <td>Movimentação</td>
                <td>Opções</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($Contas as $KeyC => $ViewC) { ?>
                <tr class="text-center">
                    <td class="align-middle"><?= $ViewC['cl_conta'] . ' - ' . $ViewC['cl_digito']; ?></td>
                    <td class="align-middle text-uppercase"><?= $ViewC['ui_nome']; ?></td>
                    <td class="align-middle text-<?=$ViewC['cl_saldo']>0?'success':'danger';?>">
                        R$ <?= number_format($ViewC['cl_saldo'], 2, ',', ''); ?>
                    </td>
                    <td class="align-middle">
                        <div class="w-75 text-bg-<?= ($ViewC['cl_ativo'] ? 'success' : 'danger'); ?> rounded mx-auto">
                            <?= ($ViewC['cl_ativo'] ? 'Ativo' : 'Inativo'); ?>
                        </div>
                    </td>
                    <td class="align-middle">
                        <?=Data($ViewC['cl_movimentacao'],'datatime-br');?>
                    </td>
                    <td class="align-middle">
                        <a href="/gerencia/<?=$URI[1];?>/contas/<?=$ViewC['cl_id'];?>" class="btn btn-sm btn-primary w-px-100 ft-10"><i class="fa fa-folder-open me-1"></i> Acessar</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script>
    $(function(){
        $('#TableClientes').DataTable({"paging": false,"ordering": false,"info": false});
        $('#TableClientes_wrapper').find('input[type="search"]').focus();
    });
</script>