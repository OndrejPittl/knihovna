<?php
	session_start();

	/*	---- IMPORTY ----  */
	require 'config/config.inc.php';
	require 'config/functions.inc.php';
	require_once 'view/twig/twig-master/lib/Twig/Autoloader.php';

	// nacist objekty - soubory *.class.php
	require 'application/core/app.class.php';
	require 'application/core/db.class.php';
	require 'application/core/uzivatel.class.php';
	require 'application/core/zanr.class.php';
	require 'application/core/komentar.class.php';
	require 'application/core/kniha.class.php';
	require 'application/core/tabulka.class.php';
	require 'application/core/vypujcka.class.php';


	Twig_Autoloader::register();
	$loader = new Twig_Loader_Filesystem('view/twig/templates');
	$twig = new Twig_Environment($loader);

	$app = new app();
	$app->Connect();
	$db_connection = $app->GetConnection();


	/*  --- pomocne objekty --- */
	$uzivatel = new uzivatel($db_connection);
	$zanr = new zanr($db_connection);
	$vypujcka = new vypujcka($db_connection);
	$kniha = new kniha($db_connection);
	$komentar = new komentar($db_connection);
	$tabulka = new tabulka($db_connection);


	$URL; $url;
	$template; $template_params = array();

	$err_msg = '';

	if(isset($_GET['url'])){
		$url = $_GET['url'];

		if (strpos($url,'/')) {
			$URL = explode("/", $url);
		} else {
			$URL[0] = $url;
		}
	} else {
		$URL[0] = "";
	}



	if(isset($_POST["login"])) {
	//nekdo se prihlasuje

		$sessna = $uzivatel->loginUser($_POST["login_data"]);

		if($sessna != null) {
			$_SESSION["username"] = $sessna["username"];
			$_SESSION["admin"] = $sessna["admin"];		
		} else {
			$err_msg = 'Uživatel neexistuje. Zkontrolujte své uživatelské jméno a heslo.';

			$template = $twig->loadTemplate('loginTemplate.html');
			$template_params = array("my_error_message"=>$err_msg);
		}


	} else if(isset($_POST["signup"])){
		//nekdo se registruje

		$confirm_msg = '';
		$err_msg = '';

		$result = $uzivatel->SignupUser($_POST["signup_data"]);
		if($result == 'ok') {
			$confirm_msg = 'Registrace proběhla úspěšně.';
		} else {
			$err_msg = $result;
		}

		$template = $twig->loadTemplate('loginTemplate.html');

		if($err_msg != '') {
			$template_params = array("my_confirmation_message"=>$confirm_msg, "my_error_message"=>$err_msg, "data" => $_POST["signup_data"]);	
		} else {
			$template_params = array("my_confirmation_message"=>$confirm_msg, "my_error_message"=>$err_msg);
		}
	}


	if(CheckUserLoggedIn()){

		if(strlen(trim($URL[0])) <= 0 || $URL[0] == "index") {
			$URL[0] = "browsing";
		}

		if(array_key_exists(1, $URL) && strlen(trim($URL[1])) > 0 && $URL[0] == "browsing")  {
			if(is_numeric($URL[1])) {

				//jsme: semestralka/browsing/cislo
				// = nekdo si pujcuje knihu s id = $URL[1]

				$userID = $uzivatel->GetCtenarID($_SESSION["username"]);
				
				if(!$vypujcka->CheckVypujcku($URL[1])){
					$vypujcka->VytvorVypujcku($userID, $URL[1]);
				}
			} else {
				$err_msg = "<h2>Neplatná volba.</h2><br>Kniha, jež se snažíte vypůjčit, neexistuje.";
			}
		}

		$template = $twig->loadTemplate('mainTemplate.html');
		$zanry = $zanr->GetZanry();


		if(isset($_POST["oprava_submit"]) && !CheckAdmin()) {

			//oprava osobnich udaju ctenare
			$data = $_POST["oprava_ucet"];
			$userID = $uzivatel->GetCtenarID($_SESSION["username"]);
			$uzivatel->editUzivatele($userID, $data);
			header("Location:/account/");
		}

		if(isset($_POST["submit_oprava"]) && CheckAdmin()) {
			//admin opravuje zaznam databaze
			$data = $_POST["oprava"];
			$tabulka->UpdateZaznam($data);
			header("Location:/administration/edit/$data[tabulka]");

		}

		if(isset($_POST["submit_oprava_delete"]) && CheckAdmin()) {
			//admin maze zaznam z databaze
			$data = $_POST["oprava"];
			$tabulka->DeleteZaznam($data);
			header("Location:/administration/edit/$data[tabulka]");
		}

		if(isset($_POST["novy_submit"]) && CheckAdmin()) {
			//admin pridava novy zaznam do databaze
			$data = $_POST["novy"];

			printr($data);
			$tabulka->PridatZaznam($data);
			header("Location:/administration/edit/$data[tabulka]");
		}

		if(isset($_POST["nova_vypujcka_submit"]) && CheckAdmin()) {			
			//admin vytvari vypujcku
			$data = $_POST["nova_vypujcka"];
			$data["tabulka"] = "vypujcky";

			$datum = getDnesniDatum();

			$data["datum_vypujceni"] = $datum;
			$tabulka->PridatZaznam($data);
			header("Location:/administration/actual");
		}

		if(isset($_POST["submit_novy_komentar"])) {
			$data = $_POST["novy_komentar"];

			$data["datum"] = getDnesniDatum();

			$komentar->VlozKomentar($data);
		}
		

		if(array_key_exists(0, $URL) && $URL[0] == 'administration' &&
			array_key_exists(1, $URL) && $URL[1] == 'actual' && 
			array_key_exists(2, $URL) && strlen(trim($URL[2])) > 0 && CheckAdmin()) {
			//admin maze vypujcku
			//jsme na administration/actual/whatever

			if(is_numeric($URL[2])) {
				//jsme na administration/actual/number
				//provedeme delete
				$data["tabulka"] = "vypujcky";
				$data["id_exemplare"] = $URL[2];
				$vypujcka->DeleteVypujckaByIdExemplare($data);
				header("Location:/administration/actual");
			}	
		}
		



		if(strpos("browsing", $URL[0]) !== false) {
			//admin/ctenar prohlizi (dostupne) exemplare
			if(isset($_POST["search_submit"])) {
				$data = $_POST["search_data"];
			} else {
				$data = null;
			}

			$knihy = $kniha->GetKnihy($data);
			$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "zanry" => $zanry, "url" => $URL, "knihy" => $knihy, "my_error_message"=>$err_msg);

		} else if(strpos("account", $URL[0]) !== false && !CheckAdmin()){
			//ctenar zobrazuje sve osobni udaje

			$user = $uzivatel->GetUser($_SESSION["username"]);
			$knihy = $kniha->GetKnihyOf($_SESSION["username"]);


			FormatDates($knihy, $user);

			//$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "user" => $user, "knihy"=>$knihy, "my_error_message"=>$err_msg);

		} elseif(strpos("administration", $URL[0]) !== false) {
			//admin je v administraci

			if(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1) {
				$subpage = '';

				if(array_key_exists(1, $URL) && strlen(trim($URL[1])) <= 0) {
					$subpage = 'actual';
					$URL[1] = $subpage;
				} else {
					$subpage = $URL[1];
				}


				$tables = $tabulka->GetTableNames();

				if(strpos("actual", $subpage) !== false) {
					//vypis vypujcek
					
					$result = $vypujcka->GetDeadlines();
					$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "deadlines"=>$result, "tables"=>$tables, "my_error_message"=>$err_msg);

				} elseif(strpos("edit", $subpage) !== false) {
					$table = '';

					if(!array_key_exists(2, $URL) || strlen(trim($URL[2])) <= 0) {
						$table = '';
						$URL[2] = $table;
					}

					if(in_array($URL[2], $tables)) {

						$table = $URL[2];
						
						$cols = $tabulka->GetTableColumns($table);
						$result = $tabulka->GetTableRows($table);



						if($table == 'knihy_has_autori') {

							$knihy = $kniha->GetKnihyID();
							$autori = $uzivatel->GetAutoriID();

							if(array_key_exists(3, $URL) && strlen(trim($URL[3])) > 0) {
								//editujeme konkretni zaznam
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "tables"=>$tables, "my_error_message"=>$err_msg);
							} else {
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "table"=>$result, "columns"=>$cols, "tables"=>$tables, "knihy"=>$knihy, "autori"=>$autori, "my_error_message"=>$err_msg);
							}

						} else if($table == 'knihy_has_zanry') {

							$knihy = $kniha->GetKnihyID();
							$zanry = $zanr->GetZanryID();

							if(array_key_exists(3, $URL) && strlen(trim($URL[3])) > 0) {
								//editujeme konkretni zaznam
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "tables"=>$tables, "my_error_message"=>$err_msg);
							} else {
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "table"=>$result, "columns"=>$cols, "tables"=>$tables, "knihy"=>$knihy, "zanry"=>$zanry, "my_error_message"=>$err_msg);
							}


						} else if($table == 'exemplare') {

							$knihy = $kniha->GetKnihyID();

							if(array_key_exists(3, $URL) && strlen(trim($URL[3])) > 0) {
								//editujeme konkretni zaznam
								$id_zaznam = $URL[3];
								$zaznam = $tabulka->GetOne($table, $id_zaznam);
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "zaznam"=>$zaznam, "columns"=>$cols, "tables"=>$tables, "my_error_message"=>$err_msg);
							} else {
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "table"=>$result, "columns"=>$cols, "tables"=>$tables, "knihy"=>$knihy, "my_error_message"=>$err_msg);
							}

						} else {
							if(array_key_exists(3, $URL) && strlen(trim($URL[3])) > 0) {
								//editujeme konkretni zaznam

								$id_zaznam = $URL[3];
								$zaznam = $tabulka->GetOne($table, $id_zaznam);

								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "zaznam"=>$zaznam, "columns"=>$cols, "tables"=>$tables, "my_error_message"=>$err_msg);
							} else {
								$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "table"=>$result, "columns"=>$cols, "tables"=>$tables, "my_error_message"=>$err_msg);
							}
						}

						

					} elseif($table == '') {
						$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "tables"=>$tables, "my_error_message"=>$err_msg);
					} else {
						//error page #404
						$err_msg = "<h2>Stránka neexistuje.</h2><br>Tabulka, jež se snažíte zobrazit, neexistuje.";
						$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "my_error_message"=>$err_msg);
					}


				} else if(strpos("new", $subpage) !== false){
					//admin zadava novou vypujcku
					$result_ctenari = $vypujcka->GetCtenariRows();
					$result_exemplare = $vypujcka->GetExemplareRows();
					$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "exemplare"=>$result_exemplare, "ctenari"=>$result_ctenari, "tables"=>$tables, "my_error_message"=>$err_msg);
				} else {

					$err_msg = "<h2>Stránka neexistuje.</h2><br>Stránka, jež se pokoušíte zobrazit, neexistuje.";
					$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "my_error_message"=>$err_msg);

				}

			} else {

				//error page, nema dostatecna opravneni
				$err_msg = "<h2>Nedostačující práva.</h2><br>Pro přístup k této službě nemáte dostatečná oprávnění.";
				$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "url" => $URL, "my_error_message"=>$err_msg);

			}


		} else if (strpos("contact", $URL[0]) !== false){

			$nove_komentare = array();
			$komentare = $komentar->GetKomentare();

			foreach($komentare as $key => $koment) {
				foreach ($koment as $key => $value) {
					if($key == 'datum') {
						$val = FormatDate($value);
						$koment[$key] = $val;
					}
				}
				array_push($nove_komentare, $koment);
			}


			$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "zanry" => $zanry, "url" => $URL, "komentare"=>$nove_komentare, "my_error_message"=>$err_msg);

		} else {

			//error page, je uplne nekde jinde
			$err_msg = "<h2>Stránka neexistuje.</h2><br>Stránka, jež se pokoušíte zobrazit, neexistuje.";
			$template_params = array("username"=>$_SESSION["username"], "admin"=>$_SESSION["admin"], "zanry" => $zanry, "url" => $URL, "my_error_message"=>$err_msg);
		}


	} else {
		//NEPRIHLASENY UZIVATEL

		/*
			neprihlaseny uzivatel ma 3 moznosti:
				1. vychozi prihlasovaci/registracni stranka
				2. vypis knih
				3. informacni stranka
		*/

			if(strlen(trim($URL[0])) <= 0) {
				//1. moznost
				$template = $twig->loadTemplate('loginTemplate.html');
				//$template_params = array();

			} else {

				$template = $twig->loadTemplate('mainTemplate.html');
				$zanry = $zanr->GetZanry();

				if(strpos("browsing", $URL[0]) !== false) {

					if(isset($_POST["search_submit"])) {
						$data = $_POST["search_data"];
					} else {
						$data = null;
					}

					$knihy = $kniha->GetKnihy($data);
					$template_params = array("username"=>"", "admin"=>"", "zanry" => $zanry, "url" => $URL, "knihy" => $knihy, "my_error_message"=>$err_msg);


				} else if (strpos("contact", $URL[0]) !== false){

					$nove_komentare = array();
					$komentare = $komentar->GetKomentare();

					foreach($komentare as $key => $koment) {
						foreach ($koment as $key => $value) {
							if($key == 'datum') {
								$val = FormatDate($value);
								$koment[$key] = $val;
							}
						}
						array_push($nove_komentare, $koment);
					}

					$template_params = array("username"=>"", "admin"=>"", "zanry" => $zanry, "url" => $URL, "komentare"=>$nove_komentare, "my_error_message"=>$err_msg);

				} else if (strpos("index", $URL[0]) !== false){

					$template = $twig->loadTemplate('loginTemplate.html');
					$template_params = array("my_error_message"=>$err_msg);

				} else {

					//error page, stranka, na kterou se uzivatel pokousi dostat neexistuje
					$err_msg = '<h2>Stránka neexistuje.</h2><br>Stránka, jež se pokoušíte zobrazit, neexistuje.<br>Na hlavní stránku pokčačujte <a href="index/">ZDE</a>';
					$template = $twig->loadTemplate('loginTemplate.html');
					$template_params = array("my_error_message"=>$err_msg, "page_not_found"=>true);

				}
			}	
		}

		echo $template->render($template_params);


	function CheckUserLoggedIn(){
		return isset($_SESSION["username"]) && $_SESSION["username"] != "";
	}

	function CheckAdmin(){
		if(isset($_SESSION["admin"]) && $_SESSION["admin"] == 1) return true;
		return false;
	}

	function FormatDates($knihy, $user/*=array()*/){
		for($i=0; $i<count($knihy); $i++) {
			$knihy[$i]["deadline"] = FormatDate($knihy[$i]["deadline"]);
		}
		
		$user["datum_narozeni"] = date_format(date_create($user["datum_narozeni"]),"d. m. Y");
	}

	?>