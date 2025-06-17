<div class="accordion accordion-flush" id="accordionFlushMenu">

	<!--	<a class="list-group-item list-group-item-action list-group-item-light p-3" href="/turmas"><i class="fa fa-chalkboard me-1"></i> Turmas</a> -->

	<?php if ($MEUTIPO == 0) { // ADMIN 
	?>

		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-admin" aria-expanded="<?= SMenu('admin', 'true', 'false'); ?>" aria-controls="flush-collapse-secretaria"><i class="fa fa-user-secret me-1"></i> Admin</button></h2>
			<div id="flush-collapse-admin" class="accordion-collapse collapse <?= SMenu('admin', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/admin/superuser"><i class="fa fa-user me-1"></i> Super Usuários</a></li>
						<li class="nav-item"><a class="nav-link" href="/admin/supersct"><i class="fa fa-school me-1"></i> Super Secretaria</a></li>
						<li class="nav-item">
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/admin/modulos"><i class="fa me-1 fa-box"></i> Modulos Ativos</a></li>
					</ul>
				</div>
			</div>
		</div>

	<?php } ?>

	<?php if ($MEUTIPO <= 31) { // SECRETARIA 
	?>

		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-secretaria" aria-expanded="<?= SMenu('secretaria', 'true', 'false'); ?>" aria-controls="flush-collapse-secretaria"><i class="fa fa-building-flag me-1"></i> Secretaria</button></h2>
			<div id="flush-collapse-secretaria" class="accordion-collapse collapse <?= SMenu('secretaria', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/secretaria/usuario"><i class="fa fa-user me-1"></i> Gerenciar Usuários</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/carteirinha"><i class="fa me-1 fa-id-card"></i> Carteirinha</a></li>
						<li class="nav-item">
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/turmas"><i class="fa me-1 fa-chalkboard-user"></i> Turmas</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/enturmar/estudante"><i class="fa me-1 fa-person-circle-plus"></i> Enturmar Estudante</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/enturmar/professor"><i class="fa me-1 fa-person-circle-check"></i> Enturmar Professor</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/relatorio/professor-aula"><i class="fa me-1 fa-chart-bar"></i> Relação Professor/Aulas</a></li>
						<li class="nav-item">
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/notas-faltas"><i class="fa me-1 fa-file-lines"></i> Notas e Faltas</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/boletim"><i class="fa me-1 fa-file-lines"></i> Boletim</a></li>
						<li class="nav-item"><a class="nav-link" href="/secretaria/pautas"><i class="fa me-1 fa-file-lines"></i> Pautas</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-pedagogico" aria-expanded="<?= SMenu('pedagogico', 'true', 'false'); ?>" aria-controls="flush-collapse-pedagogico"><i class="fa fa-graduation-cap me-1"></i> Pedagogico</button></h2>
			<div id="flush-collapse-pedagogico" class="accordion-collapse collapse <?= SMenu('pedagogico', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/pedagogico/rendimento"><i class="far me-1 fa-file-lines"></i> Rendimento</a></li>
						<!--
						<li>
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/pedagogico/pautas"><i class="far me-1 fa-file"></i> Estudo Orientado</a></li>
						-->
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($MEUTIPO <= 31 or $MEUTIPO == 34) { ?>
		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-diversificadagestor" aria-expanded="<?= SMenu('diversificada', 'true', 'false'); ?>" aria-controls="flush-collapse-diversificadagestor"><i class="fa fa-heart-pulse me-1"></i> Diversificada</button></h2>
			<div id="flush-collapse-diversificadagestor" class="accordion-collapse collapse <?= SMenu('diversificada', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<?php if ($MEUTIPO <= 31) { ?>
							<li class="nav-item"><a class="nav-link" href="/diversificada/notas"><i class="far me-1 fa-file-lines"></i> Notas</a></li>
							<li>
								<hr class="my-0">
							</li>
							<li class="nav-item"><a class="nav-link" href="/diversificada/estudo-orientado"><i class="far me-1 fa-file"></i> Estudo Orientado</a></li>
						<?php } ?>
						<li class="nav-item"><a class="nav-link" href="/diversificada/eletiva"><i class="fa me-1 fa-charging-station"></i> Eletivas</a></li>
						<?php if ($MEUTIPO <= 31) { ?>
							<li class="nav-item"><a class="nav-link" href="/diversificada/projeto-de-vida"><i class="fa me-1 fa-heart-circle-bolt"></i> Projeto de Vida</a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($MEUTIPO == 32) { ?>
		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-turmas" aria-expanded="<?= SMenu('turmas', 'true', 'false'); ?>" aria-controls="flush-collapse-turmas"><i class="fa fa-chalkboard me-1"></i> Turmas</button></h2>
			<div id="flush-collapse-turmas" class="accordion-collapse collapse <?= SMenu('turmas', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">

					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/turmas/minha"><i class="fa me-1 fa-chalkboard-user"></i> Minhas Turmas</a></li>
						<li class="nav-item"><a class="nav-link" href="/turmas/eletiva"><i class="fa me-1 fa-charging-station"></i> Eletivas</a></li>
						<li>
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/turmas/provas"><i class="fa me-1 fa-file-edit"></i> Provas</a></li>
					</ul>

				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($MEUTIPO == 33) { ?>
		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-academico" aria-expanded="<?= SMenu('academico', 'true', 'false'); ?>" aria-controls="flush-collapse-academico"><i class="far fa-compass me-1"></i> Acadêmico</button></h2>
			<div id="flush-collapse-academico" class="accordion-collapse collapse <?= SMenu('academico', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/academico/notas"><i class="fa me-1 fa-file-waveform"></i> Minhas Notas</a></li>
						<li class="nav-item"><a class="nav-link" href="/academico/programa-de-acao"><i class="fa me-1 fa-location-dot"></i> Programa de Ação</a></li>
						<li class="nav-item">
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/academico/carteirinha-estudantil"><i class="fa me-1 fa-id-card"></i> Carteirinha Estudantil</a></li>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="accordion-item">
		<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-tutoria" aria-expanded="<?= SMenu('tutoria', 'true', 'false'); ?>" aria-controls="flush-collapse-tutoria"><i class="far fa-compass me-1"></i> Tutoria</button></h2>
		<div id="flush-collapse-tutoria" class="accordion-collapse collapse <?= SMenu('tutoria', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
			<div class="accordion-body p-1">
				<ul class="nav flex-column px-0">
					<?php if ($MEUTIPO <= 31) { ?>
						<li class="nav-item"><a class="nav-link" href="/tutoria/gerenciar"><i class="fa me-1 fa-wrench"></i> Gerenciar Tutoria</a></li>
					<?php }
					if ($MEUTIPO <= 31 or $MEUTIPO == 34) { ?>
						<li class="nav-item"><a class="nav-link" href="/tutoria/ocorrencia/registro"><i class="fa me-1 fa-file-lines"></i> Registros de Ocorrências</a></li>
						<li>
							<hr class="my-0">
						</li>
					<?php } ?>
					<?php if ($MEUTIPO != 33) { ?>
						<li class="nav-item"><a class="nav-link" href="/tutoria/tutorados"><i class="fa me-1 fa-street-view"></i> Meus Tutorados</a></li>
						<li class="nav-item"><a class="nav-link" href="/tutoria/ocorrencia/criar"><i class="fa me-1 fa-person-military-pointing"></i> Criar Ocorrência</a></li>
						<li class="nav-item"><a class="nav-link" href="/tutoria/ocorrencia/devolutivas"><i class="fa me-1 fa-clipboard"></i> Ver Devolutivas</a></li>
						<li class="nav-item"><a class="nav-link" href="/tutoria/projeto-de-vida"><i class="fa me-1 fa-heart"></i> Projetos de Vida</a></li>
					<?php } else { ?>
						<li class="nav-item"><a class="nav-link" href="/tutoria/minhas-ocorrencias"><i class="fa me-1 fa-clipboard"></i> Minhas Ocorrênicas</a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>

	<div class="accordion-item">
		<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-calendario" aria-expanded="<?= SMenu('calendario', 'true', 'false'); ?>" aria-controls="flush-collapse-calendario"><i class="fa fa-calendar-day me-1"></i> Calendário</button></h2>
		<div id="flush-collapse-calendario" class="accordion-collapse collapse <?= SMenu('calendario', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
			<div class="accordion-body p-1">

				<ul class="nav flex-column px-0">
					<?php if ($MEUTIPO != 33) { ?>
						<li class="nav-item"><a class="nav-link" href="/calendario/agenda"><i class="fa me-1 fa-pen"></i> Minha Agenda</a></li>
					<?php } ?>
					<li class="nav-item"><a class="nav-link" href="/calendario/servidor"><i class="fa me-1 fa-user"></i> Servidores</a></li>
					<li class="nav-item"><a class="nav-link" href="/calendario/escola"><i class="fa me-1 fa-school"></i> Escola</a></li>
					<li class="nav-item"><a class="nav-link" href="/calendario/turma"><i class="fa me-1 fa-chalkboard"></i> Turmas</a></li>
					<?php if ($MEUTIPO != 33) { ?>
						<li class="nav-item"><a class="nav-link" href="/calendario/espacos"><i class="fa me-1 fa-map-location-dot"></i> Espaços</a></li>
					<?php } ?>
				</ul>

			</div>
		</div>
	</div>

	<?php if ($MEUTIPO != 33) { ?>
		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-socioe" aria-expanded="<?= SMenu('socioemocional', 'true', 'false'); ?>" aria-controls="flush-collapse-socioe"><i class="fa fa-sun me-1"></i> Socioemocional</button></h2>
			<div id="flush-collapse-socioe" class="accordion-collapse collapse <?= SMenu('socioemocional', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/socioemocional/turma/perfil"><i class="fa me-1 fa-file-waveform"></i> Perfil de Turma</a></li>
						<li class="nav-item"><a class="nav-link" href="/socioemocional/turma/estudante"><i class="fa me-1 fa-snowflake"></i> Socioemocional</a></li>
						<!-- 					
					<li class="nav-item"><a class="nav-link" href=""><i class="fa me-1 fa-clipboard"></i> Ver Devolutivas</a></li>
					<li class="nav-item"><a class="nav-link" href=""><i class="fa me-1 fa-heart"></i> Projetos de Vida</a></li> -->
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>

	<?php if ($MEUTIPO <= 31) { ?>

		<div class="accordion-item">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-financeiro" aria-expanded="<?= SMenu('financeiro', 'true', 'false'); ?>" aria-controls="flush-collapse-financeiro"><i class="fa fa-file-invoice-dollar me-1"></i> Financeiro</button></h2>
			<div id="flush-collapse-financeiro" class="accordion-collapse collapse <?= SMenu('financeiro', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">
					<ul class="nav flex-column px-0">
						<li class="nav-item d-none"><a class="nav-link" href="/financeiro/mensalidade"><i class="fa fa-vault me-1"></i> Mensalidade</a></li>
						<li class="nav-item d-none"><a class="nav-link" href="/financeiro/material-didatico"><i class="fa fa-money-bill-transfer me-1"></i> Material Didático</a></li>
						<li class="nav-item d-none">
							<hr class="my-0">
						</li>
						<li class="nav-item"><a class="nav-link" href="/financeiro/cantina"><i class="fa me-1 fa-cash-register"></i> Cantina</a></li>
					</ul>
				</div>
			</div>
		</div>

	<?php } ?>

	<?php if ($MEUTIPO != 33) { ?>
		<div class="accordion-item <?= is_localhost(true); ?>">
			<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-documentos" aria-expanded="<?= SMenu('documentos', 'true', 'false'); ?>" aria-controls="flush-collapse-documentos"><i class="fa fa-file-signature me-1"></i> Documentos</button></h2>
			<div id="flush-collapse-documentos" class="accordion-collapse collapse <?= SMenu('documentos', 'show'); ?>" data-bs-parent="#accordionFlushMenu">
				<div class="accordion-body p-1">

					<ul class="nav flex-column px-0">
						<li class="nav-item"><a class="nav-link" href="/documentos/arquivos"><i class="fa me-1 fa-box-archive"></i> Meus Arquivos</a></li>
						<li class="nav-item"><a class="nav-link" href="/documentos/relatorio/pdi"><i class="fa me-1 fa-chart-pie"></i> Plano de Des. Individual</a></li>
						<li class="nav-item"><a class="nav-link" href="/documentos/relatorio/aula"><i class="fa me-1 fa-comments"></i> Relatório de Aula</a></li>
						<li class="nav-item"><a class="nav-link" href="/documentos/atas"><i class="fa me-1 fa-file-signature"></i> Atas de Reuniões</a></li>
					</ul>

				</div>
			</div>
		</div>
	<?php } ?>

	<?php if (is_Admin()) { ?>
		<a class="list-group-item list-group-item-action list-group-item-light p-3" href="/configuracoes"><i class="fa fa-cogs me-1"></i> Configurações</a>
	<?php } ?>


</div>