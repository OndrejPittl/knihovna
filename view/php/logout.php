<?php
	session_start();
	unset($_SESSION["username"]);
	unset($_SESSION["admin"]);
	session_destroy();
	header("Location:/index/");