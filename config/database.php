<?php
	
	if(@$_SERVER['HTTP_HOST']=='meubanco.com'){
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}
	
	// $LocalServer = (@$_SERVER['HTTP_HOST']=='learnpilot.com') ? true : false;
	// SERVIDOR ONLINE
	$db = new mysqli(
		'localhost',
		'admin_tesa',
		'@Henrique1991',
		'edbank'
	);
	/*
	$db = new mysqli(
		($LocalServer?'localhost':'191.252.214.178'),			// HOST
		'admin_tesa',											// USERNAME
		'@Henrique1991',										// PASSWORD
		($LocalServer?'admin_novaTesa':'admin_novatesa')		// DATABASE NAME
	); 
	*/

	if(boolval(mysqli_connect_error($db))){
		require_once __DIR__.'/../public_html/views/html/dbErro.html';
		exit;
	} mysqli_set_charset($db,'UTF8MB4');
	
