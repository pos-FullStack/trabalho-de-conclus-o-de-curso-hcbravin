<?php

	$ANOATUAL = (isset($ANOATUAL) AND is_numeric($ANOATUAL)) ? $ANOATUAL : date('Y');
	$Patch = __DIR__.'/../public_html/files/';
	$Pastas = [
		[0,$ANOATUAL],
		// [0,"$ANOATUAL/blog"],
		// [0,"$ANOATUAL/blog/default"],
		// [0,"$ANOATUAL/blog/documents"],
		// [0,"$ANOATUAL/blog/image"],
		// [0,"$ANOATUAL/casf"],
		// [0,"$ANOATUAL/casf/default"],
		// [0,"$ANOATUAL/casf/documents"],
		// [0,"$ANOATUAL/casf/image"],
		[0,"$ANOATUAL/default"],
		[0,"$ANOATUAL/default/default"],
		[0,"$ANOATUAL/default/documents"],
		[0,"$ANOATUAL/default/image"],
		[0,"$ANOATUAL/eo"],
		[0,"$ANOATUAL/eo/default"],
		[0,"$ANOATUAL/eo/documents"],
		[0,"$ANOATUAL/eo/image"],
		// [0,"$ANOATUAL/club"],
		// [0,"$ANOATUAL/club/default"],
		// [0,"$ANOATUAL/club/documents"],
		// [0,"$ANOATUAL/club/image"],
		// [0,"$ANOATUAL/ead"],
		// [0,"$ANOATUAL/ead/default"],
		// [0,"$ANOATUAL/ead/documents"],
		// [0,"$ANOATUAL/ead/image"],
		[0,"$ANOATUAL/system"],
		[0,"$ANOATUAL/system/default"],
		[0,"$ANOATUAL/system/documents"],
		[0,"$ANOATUAL/system/image"],
	];
	
	foreach($Pastas as $K1=>$V1){
		if(!is_dir($Patch.$V1[1])){
			if(mkdir($Patch.$V1[1])){
				$Pastas[$K1][0] = 1;
			}else{
				$Pastas[$K1][0] = -1;
			}
		}
	}
?>
<div class="card">
	<div class="card-header pad-main text-white bg-verpsc">
		GERAÇÃO DE PASTAS PARA <b class="text-dark">FILES</b> EM <b class="text-dark"><?=$ANOATUAL;?></b>
	</div>
	<div class="card-body pad-0">
		<table class="table table-sm table-striped mb-0">
		<?php foreach($Pastas as $K1=>$V1){ ?>
		<tr>
			<td class="w-5 text-center">
				<?php if($V1[0]==0){ ?>
				<i class="fa fa-folder text-info"></i>
				<?php }elseif($V1[0]==1){ ?>
				<i class="fa fa-check text-success"></i>
				<?php }else{ ?>
				<i class="fa fa-times text-danger"></i>
				<?php } ?>
			</td>
			<td class="w-10 text-center">files/</td>
			<td class="pdl-10"><?=$V1[1];?></td>
		</tr>
		<?php } ?>
		</table>
	</div>
</div>
