<?php

/**
 * Hlavni konfiguracni soubor.
 */
	SetLocale(LC_ALL, "Czech");
	
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	define('WEB_DOMAIN', 'http://localhost/semestralka');
	
	
	/* PRIJPOJENI K DB */
	
	//local

	define('DB_TYPE', 'mysql');
	define('DB_HOST', '127.0.0.1');
	define('DB_DATABASE_NAME', 'pittl_knihovna');
	define('DB_USER_LOGIN', 'root');
	define('DB_USER_PASSWORD', '');


	//online
	/*
	define('DB_TYPE', 'mysql');
	define('DB_HOST', 'localhost');
	define('DB_DATABASE_NAME', 'pittl_knihovna');
	define('DB_USER_LOGIN', '...');
	define('DB_USER_PASSWORD', '...');
	*/
	
?>