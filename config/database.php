<?php
	
	if(@$_SERVER['HTTP_HOST']=='meubanco.com'){
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}
	
	$db = new mysqli(
		'localhost',
		'admin_tesa',
		'@Henrique1991',
		'edbank'
	);

	if($db -> connect_errno){
		require_once __DIR__.'/../public_html/views/html/dbErro.html';
		exit;
	} mysqli_set_charset($db,'UTF8MB4');
	
