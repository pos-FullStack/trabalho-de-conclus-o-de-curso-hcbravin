<div class="modal" tabindex="-1" id="ModalAguarde" data-bs-keyboard="false" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-bg-warning">
                <h6 class="modal-title"><i class="fa fa-clock"></i> AGUARDE</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-5 text-center">
                <i class="fa fa-clock-rotate-left fa-beat-fade fa-3x mb-4"></i>
                <h5>Estamos realizando sua opereção.</h5>
                <h6>Por favor, aguarde!</h6>
            </div>
        </div>
    </div>
</div>
<script>
    $(function(){
        $(document).on('click','button[type="submit"]',function(){
            let formID = $(this).parents('form').attr('id');
            if(typeof formID == 'undefined'){
                formID = UniqID();
                $(this).parents('form').attr('id',formID);
            }
            var requeridosCount = 0;
            // TIPO REQUERIDOS
            let requeridos = $(document).find('#'+formID).find('input[required],select[required],textarea[required]');
            requeridos.each(function(){ requeridosCount = requeridosCount + ($(this).val() == '' ? 1 : 0); });
            // TIPO NUMERICO COM VALOR DIFERENTE DOS ATRIBUIDOS
            let numericos = $(document).find('#'+formID).find('input[type="number"][value!=""]');
            numericos.each(function(){ requeridosCount = requeridosCount + (($(this).val() < $(this).attr('min') || $(this).val() > $(this).attr('max'))? 1 : 0); });
            
            
            if(requeridosCount == 0){
                $('#ModalAguarde').modal('show');
            }
        });
    });
</script>