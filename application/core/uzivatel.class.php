<?php 

class uzivatel extends db
{
	
	/**
	* Konstruktor.
	*/
	public function uzivatel($connection)
	{
		$this->connection = $connection;	
	}
	
	private function CheckUser($username, $passwd){

		$wheres = array(array('column'=>'username', 'value'=>$username, 'symbol'=>'='),array('column'=>'passwd', 'value'=>$passwd, 'symbol'=>'='));
		$user = $this->DBSelectOne('uzivatele', 'username, passwd', $wheres, 'limit 1');

        if($user != null) {
            return $user["username"];
        }
        
        return null;
    }


    public function LoginUser($data) {

        $username = $data["username"];
        $passwd = $data["passwd"];

        $username;
        if(($username = $this->CheckUser($username, $passwd)) != null) {
            $sessna["username"] = $username;

            if($this->CheckPrivileges($username)){
                $sessna["admin"] = 1;
            } else {
                $sessna["admin"] = 0;
            }

            return $sessna;
        }

        return null;
    }

    private function CheckPrivileges($username){
        $rs = $this->DBSelectOne('zamestnanci z inner join uzivatele u on z.id_uzivatele = u.id_uzivatele',
                           'z.id_uzivatele', array(array("column"=>"username","value"=>$username,"symbol"=>"like")));
        
        if($rs != null) {
			return true;
		}
		return false;
    }

    public function SignupUser($data){

        if(($rs = $this->CheckDataExistance('uzivatele', array('username' => $data['username']))) != null) {
            //chyba, uzivatelske jmeno existuje
            return 'Uživatelské jméno existuje.';
        } else if(($rs = $this->CheckDataExistance('ctenari', array('email' => $data['email']))) != null){
            //email existuje
            return 'Uživatel se zvolenou e-mailovou adresou již existuje.';
        } else {
            //uzivatel neni registrovan, zaregistrujeme jej
            $data_uzivatele = array('username' => $data["username"], 'passwd' => $data["passwd"]);
            $this->DBInsert('uzivatele', $data_uzivatele);
            $uzivatele_id = $this->DBSelectOne('uzivatele','id_uzivatele', null, 'order by id_uzivatele desc limit 1');
            
            //uzivatel vlozen
            $data_ctenare['id_uzivatele'] = $uzivatele_id["id_uzivatele"];

            foreach($data as $key => $val) {
                if($key != "username" && $key != "passwd") {
                    $data_ctenare[$key] = $val;    
                }
            }

            $this->DBInsert('ctenari', $data_ctenare);
            //ctenar vlozen

            return 'ok';
        }
    }

    public function GetUser($username){
        $tables = 'ctenari c inner join uzivatele u on c.id_uzivatele = u.id_uzivatele';
        $columns = 'concat(concat(c.jmeno, " "), c.prijmeni) as jmeno, c.datum_narozeni, c.bydliste_adresa, c.bydliste_mesto, c.email, u.username';
        $wheres = array(array("column"=>"username", "value"=>$username, "symbol"=>"="));
        return $this->DBSelectOne($tables, $columns, $wheres, "limit 1");
    }

    public function EditUzivatele($id_uzivatele, $data){
        $data_uzivatele = array();
        $data_ctenari = array();

        foreach ($data as $key => $value) {
            if($key == "passwd") {
                $data_uzivatele[$key] = $value;
            } else {
                $data_ctenare[$key] = $value;
            }
        }

        $this->DBUpdate('uzivatele', $data_uzivatele, array(array('column' => 'id_uzivatele', 'value'=>$id_uzivatele, 'symbol'=>'=' )));
        $this->DBUpdate('ctenari', $data_ctenare, array(array('column' => 'id_uzivatele', 'value'=>$id_uzivatele, 'symbol'=>'=' )));
    }

    public function GetCtenarID($username) {
        $cols = 'uzivatele u inner join ctenari c on u.id_uzivatele = c.id_uzivatele';
        $wheres = array(array("column"=>"username", "value"=>$username, "symbol"=>"="));
        $id = $this->DBSelectOne($cols, 'c.id_ctenari', $wheres, "limit 1");
        return $id["id_ctenari"];
    }

    public function GetAutoriID(){
        return $this->DBSelectAll('autori', 'id_autori, concat(concat(jmeno, " "), prijmeni) as jmeno_autora', null);
    }
}


?>
	
	