<?php
$Time = 30;
if (isset($_GET['clear'])) {
	$input = fopen('error.log', 'w');
	if ($input == false) die('Não foi possível criar o arquivo.');
	fclose($input);
	header('Location: ./error.log.php');
} 

$db = new SQLite3('/home/henrique/www/TesaFix.db');

function ListarTipo(){
    global $db;
    $Ver = $db -> query("SELECT * FROM tipo"); $a = [];
	while($row = $Ver->fetchArray(SQLITE3_ASSOC) ) {
        $a[$row['tid']] = $row;
    }
	return $a;
}
function ListarDados(){
    global $db;
    $Ver = $db -> query("SELECT * FROM dados INNER JOIN tipo ON (tipo.tid = dados.dtipo) WHERE dados.dstatus = 0 ORDER BY dtipo DESC, dref ASC");
	while($row = $Ver->fetchArray(SQLITE3_ASSOC) ) {
        $a[$row['did']] = $row;
    }
	return $a;
}

if(isset($_GET['inserir']) AND $_GET['inserir'] == true){
	$P = $_POST;
	$S = $db -> exec("INSERT INTO dados (dinfo,dtipo,dref) VALUES ('".$P['info']."','".$P['tipo']."','".date('Y-m-d H:i:s')."')");
	if($S){ header("Location: ./error.log.php"); }else{ print 'Erro Inserir!'; }	
}

if(isset($_GET['status']) AND is_numeric($_GET['status'])){
	$G = $_GET['status'];
	$I = $_GET['id'];
	$S = $db -> exec("UPDATE dados SET dstatus = '$G' WHERE dados.did = '$I'");
	if($S){ header("Location: ./error.log.php"); }else{ print '<div class="m-3 alert alert-danger">Erro Status!</div>'; }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<link rel="stylesheet" href="public_html/assets/css/bootstrap.min.css">
	<script src="./public_html/assets/js/jquery.min.js"></script>
	<style>
		.vh-max {
			height: 94vh !important;
		}

		.scroll-y {
			overflow-y: scroll;
		}

		.mpoint {
			cursor: pointer;
		}
		table {font-size: 10pt;}

		table thead tr td:not(:first-child) { border-left: 1px dashed #FFF; }
		table tbody tr td:not(:first-child) { border-left: 1px dashed var(--bs-gray-500); }

		.info {font-size: 10pt; padding: 0.125rem 0.5rem !important;}
		.verCor {width: 90% !important; height: 90% !important; border-radius: 0.25rem; }
	</style>
</head>

<body>
	<div class="container-fluid" id="containerMain">
		<div class="row mt-1">
			<div class="col-8">
				<div class="card">
					<div class="card-header text-bg-danger py-0"><small>ERROS PHP</small></div>
					<div class="card-body p-0 vh-max scroll-y" id="RegErros">
						<table class="table table-striped mb-0">
							<?php
							$input = fopen("error.log", "r");
							while (!feof($input)) {
								echo "<tr><td>" . str_replace(['on line','PHP message: '], ['<b class="bg-danger text-white p-1">NA LINHA</b>','<br/>'], fgets($input)) . "</td></tr>";
							}
							?>
							<tr>
								<td class="text-bg-warning rounded-bottom">
									<a href="?clear=true" class="btn btn-sm btn-danger">CLEAR</a>
									<div class="btn-group">
										<button type="button" onclick="window.location.reload();" class="btn btn-sm btn-info px-4" id="Time"><?= $Time; ?></button>
										<button type="button" id="BTempo" class="btn btn-sm btn-dark">STOP</button>
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="col-4">
				<div class="card">
					<div class="card-header py-0 text-bg-secondary">
						<div class="row">
							<div class="col-6"><small>REGISTROS</small></div>
							<div class="col-6 text-end">
								<span onclick="$('#FormNew').show();" class="mpoint">[ + ]</span>
							</div>
						</div>
					</div>
					<div class="card-body p-0">
						<form action="error.log.php?inserir=true" method="post" class="p-2" id="FormNew" style="display: none;">
							<textarea name="info" class="form-control mb-2" rows="4" required></textarea>

							<div class="row">
								<div class="col-6">
									<label for="tipo">PRIORIDADE</label>
									<select id="tipo" class="form-select" name="tipo" required>
										<option></option>
										<?php foreach(ListarTipo() as $K=>$V){ ?>
										<option value="<?=$V['tid'];?>"><?=$V['tnome'];?></option>
										<?php } ?>
									</select>
								</div>
								<div class="col-6 text-end">
									<button type="button" id="LoadTime" class="btn btn-danger mt-4" onclick="$('#FormNew').hide();">FECHAR</button>
									<button type="submit" class="btn btn-secondary mt-4">SALVAR</button>
								</div>
							</div>
						</form>
					</div>
					<div class="card-body p-0 vh-max scroll-y">
						<table class="table table-sm table-stripad mb-0">
							<thead>
								<tr>
									<td class="text-bg-dark py-1 text-center" style="width: 10%;">TIPO</td>
									<td class="text-bg-dark py-1 px-2">INFO</td>
								</tr>
							</thead>
							<tbody id="Dados">
								<?php foreach(ListarDados() as $K=>$V){ ?>
								<tr>
									<td class="align-middle"><div data-bs-toggle="tooltip" title="<?=$V['tnome'];?>" class="bg-<?=$V['tcor'];?> verCor mpoint" onclick="window.location='./error.log.php?status=<?=$V['dstatus']==0?1:0;?>&id=<?=$V['did'];?>';">&nbsp;</div></td>
									<td class="info"><?=$V['dinfo'];?></td>
								</tr>

								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>


			</div>
		</div>
	</div>



	<script>
		$(function(){
			var ltime = true;


			$("#RegErros").animate({
			scrollTop: $(
				'#RegErros').get(0).scrollHeight
			});

			setInterval(() => {
				let main = $('#Time');
				let tempo = parseInt(main.html());
				if(ltime){ main.html(tempo - 1); }
			}, 1000);

			setTimeout(() => {
				if(ltime){ window.location = './error.log.php'; }
			}, <?= $Time * 1000; ?>)

			$('#BTempo').click(function(){
				ltime = false;
				$('#Time').html('RELOAD');
				$(this).remove();
			});

		});
	</script>
</body>

</html>