<?php if(@$URI[2]!='multiplus'){ ?>
<div class="row justify-content-center">
	<div class="col-12 col-sm-8 col-md-8 d-off" id="ProcessErro">
		<div class="card">
			<div class="card-header pad-main bg-danger text-white"><i class="fa fa-bug"></i> ERRO</div>
			<div class="card-body text-center pdtb-5 pdrl-10">
				O Servidor atingiu o tempo máximo de espera devido a um erro inesperado.
				<br> Pedimos que retorne a página anterior e repita o processo novamente.
			</div>
		</div>
	</div>
	<script>setTimeout(function(){$('div#ProcessErro').fadeIn(500);},60000);</script>
	<div class="col-12 col-sm-8 col-md-6">
		<div class="card mt-5 mb-5 shadow">
			<div class="card-body text-center">
				<img src="/imagens/tesa.icon.gif" width="75">
				<p class="mb-0 ft10">PROCESSANDO, AGUARDE!</p>
			</div>
		</div>
	</div>
</div>
<?php } ?>