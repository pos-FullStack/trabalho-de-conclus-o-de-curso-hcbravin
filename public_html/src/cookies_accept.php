<?php if(!isset($_COOKIE['cookie_accept']) OR $_COOKIE['cookie_accept'] != true){ ?>
<div class="modal" tabindex="-1" id="CookieModal" data-backdrop="static">
	<div class="modal-dialog modal-xl modal-dialog-centered" style="align-items: flex-end;">
		<div class="modal-content">
			<div class="modal-header pad-main bg-secondary text-white ft10">
				<span class="modal-title"><i class="fa fa-cookie"></i> Cookies</span>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="text-justify">Para melhorar a sua experiência na plataforma e prover serviços personalizados, utilizamos cookies. Ao aceitar, você terá acesso a todas as funcionalidades do site. Se clicar em "Rejeitar Cookies", nesse caso não será possível utilizar o sistema. Resaltamos que nenhuma informação pessoal é salva nos cookies. Saiba mais em nossa <a href="/politicas-de-cookies">Declaração de Cookies</a>.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" onClick="AcceptCookie(false);"><i class="fa fa-ban"></i> NÃO</button>
				<button type="button" class="btn btn-success" onClick="AcceptCookie(true);"><i class="fa fa-check"></i> SIM</button>
			</div>
		</div>
	</div>
</div>
<script>
function AcceptCookie(e) {
	console.log(e);
	if (e==false){ window.location = '/logout'; }else if (e==true) {
		const d = new Date(); d.setTime(d.getTime() + (10000*24*60*60*1000)); let expires = "expires="+ d.toUTCString();
		document.cookie = "cookie_accept=true; "+expires+"; path=/";
		location.reload();
	}
}
$(function(){
	$('#CookieModal').modal('show');
	$('#BtnLogin,#LoginForm').remove();
});
</script>
<?php } ?>