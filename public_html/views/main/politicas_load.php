<?php
	$PolyLock = false;	
	if(isset($MS['poly']) AND $MS['poly']==0 AND @$URI[0] != 'nova-politica'){ $PolyLock = true;
		URINull(2);
		require_once __DIR__.'/nova-politica.php';	
	}else{
		if($URI[0]=='nova-politica'){
			if(is_numeric($URI[1]) AND $URI[1]==1){
			$Base = $db -> prepare("UPDATE login SET login_politicas = '1', login_dref = NOW() WHERE login_id = ?");
			$Base -> bind_param("i",$MS['lid']);
			$_SESSION['poly'] = 1;
			hdr('login/user',$Base->execute());
			}else{
				URINull(2);
				require_once __DIR__.'/nova-politica.php';
			}
		}
	}