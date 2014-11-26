<?php 

class vypujcka extends db
{

	/**
	* Konstruktor.
	*/
	public function vypujcka($connection)
	{
		$this->connection = $connection;	
	}

    public function VytvorVypujcku($id_ctenare, $id_exemplare){
        $datum = getDnesniDatum();
        $this->DBInsert('vypujcky', array("id_ctenare"=>$id_ctenare, "id_exemplare"=>$id_exemplare, "datum_vypujceni"=>$datum));
    }

    public function GetDeadlines(){
        return $this->DBSelectAll('deadline_dluzniku', '*', null);
    }

    public function DeleteVypujckaByIdExemplare($data){
        $table_name = $data["tabulka"];
        $id = $data["id_exemplare"];
        unset($data["tabulka"]);
       	$wheres = array(array("column"=>"id_exemplare", "value"=>$id, "symbol"=>"="));
        $this->DBDelete('vypujcky', $wheres);
    }

    public function GetCtenariRows(){
        $ctenari_cols = 'c.id_ctenari, concat(concat(c.jmeno, " "), c.prijmeni) as jmeno_ctenare, u.username';
        $ctenari_tables = 'ctenari c inner join uzivatele u on c.id_uzivatele = u.id_uzivatele';
        $order_by_ctenari = array(array("column"=>"jmeno_ctenare", "sort"=>"asc"));
        return $this->DBSelectAll($ctenari_tables, $ctenari_cols, null, "", $order_by_ctenari);
    }

    public function GetExemplareRows(){
        $exemplare_tables = 'exemplare e inner join knihy k on e.id_knihy = k.id_knihy';
        $wheres = array(array("column"=>"id_exemplare", "value_mysql"=>"(select e.id_exemplare from exemplare e inner join vypujcky v on e.id_exemplare = v.id_exemplare)", "symbol"=>"not in"));
        $exemplare_cols = 'e.id_exemplare as id_exemplare, k.nazev, k.rok_vydani';
        $order_by_exemplare = array(array("column"=>"nazev", "sort"=>"asc"));
        return $this->DBSelectAll($exemplare_tables, $exemplare_cols, $wheres, "", $order_by_exemplare);
    }

  public function CheckVypujcku($id_exemplare) {
        if($this->DBSelectOne('vypujcky', 'id_exemplare', array(array('column'=>'id_exemplare', 'value'=>$id_exemplare, 'symbol'=>'=')), 'limit 1') == null) {
            return false;
        }
        return true;
    }
}

?>	