<?php 

class kniha extends db
{
	
	/**
	* Konstruktor.
	*/
	public function kniha($connection)
	{
		$this->connection = $connection;	
	}

	public function GetKnihyID(){
        return $this->DBSelectAll('knihy', 'id_knihy, nazev', null);
    }
	
   	private function GetKnihy_subfunction(){
        $table_name = "knihy k inner join knihy_has_autori ka on k.id_knihy = ka.knihy_id_knihy inner join autori a on ka.autori_id_autori = a.id_autori " .
            "left join knihy_has_zanry kz on k.id_knihy = kz.knihy_id_knihy left join zanry z on kz.zanry_id_zanry = z.id_zanry " .
            "inner join vypujcni_kategorie kat on kat.id_vypujcni_kategorie = k.id_vypujcni_kategorie inner join exemplare e on k.id_knihy = e.id_knihy " .
            "left join vypujcky v on v.id_exemplare = e.id_exemplare";

        $columns = "e.id_exemplare id_exemplare, k.nazev as nazev_knihy, concat(concat(a.jmeno, ' '), a.prijmeni) as jmeno_autora, z.nazev as zanr, k.rok_vydani, " .
            "k.popis as popis, k.vekova_hranice as vek, case when e.id_exemplare not in(select e.id_exemplare from knihy k " .
            "inner join exemplare e on k.id_knihy = e.id_knihy inner join vypujcky v on v.id_exemplare = e.id_exemplare) " .
            "then 'k dispozici' else date(DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY)) end as stav";

        $order_by = array(array("column"=>"nazev_knihy", "sort"=>"asc"), array("column"=>"id_exemplare", "sort"=>"asc"));

        return $this->DBSelectAll($table_name, $columns, null, '', $order_by);
    }

    public function GetKnihy($data){
        $knihy;

        if($data != null) {
            //filtrujeme

            $wheres = array();          

            foreach($data as $key => $value) {
                if(strlen(trim($value)) > 0 && $key != 'dostupne') {
                    if($key == 'rok_vydani') {
                        array_push($wheres, array("column"=>$key, "value"=>$value, "symbol"=>"="));
                    } else {
                        array_push($wheres, array("column"=>$key, "value"=>"%".$value."%", "symbol"=>"like"));  
                    }
                }
            }

            if(isset($data["dostupne"])){
                $knihy = $this->GetKnihyBy($wheres, $data["dostupne"]);  
            } else {
                $knihy = $this->GetKnihyBy($wheres, "");
            }

            $knihy = $this->ProcessKnihy($knihy);

        } else {
            $knihy = $this->GetKnihy_subfunction();
            //printr($knihy);
            $knihy = $this->ProcessKnihy($knihy);
            //printr($knihy);
        }       

        return $knihy;
    }

    private function GetKnihyBy($wheres, $dostupne){
      
        //DBSelectAll($table_name, $select_columns_string, $where_array, $limit_string = "", $order_by_array = array())
        $table_name = "knihy k inner join knihy_has_autori ka on k.id_knihy = ka.knihy_id_knihy inner join autori a on ka.autori_id_autori = a.id_autori " .
            "left join knihy_has_zanry kz on k.id_knihy = kz.knihy_id_knihy left join zanry z on kz.zanry_id_zanry = z.id_zanry " .
            "inner join vypujcni_kategorie kat on kat.id_vypujcni_kategorie = k.id_vypujcni_kategorie inner join exemplare e on k.id_knihy = e.id_knihy " .
            "left join vypujcky v on v.id_exemplare = e.id_exemplare";

        $columns = "e.id_exemplare id_exemplare, k.nazev as nazev_knihy, concat(concat(a.jmeno, ' '), a.prijmeni) as jmeno_autora, z.nazev as zanr, k.rok_vydani, " .
            "k.popis as popis, k.vekova_hranice as vek, case when e.id_exemplare not in(select e.id_exemplare from knihy k " .
            "inner join exemplare e on k.id_knihy = e.id_knihy inner join vypujcky v on v.id_exemplare = e.id_exemplare) " .
            "then 'k dispozici' else date(DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY)) end as stav";

        if($dostupne == 'on') {
            array_push($wheres, array("column"=>"id_exemplare", "value_mysql"=>"(select v.id_exemplare from vypujcky v)", "symbol"=>"not in"));
        }

        $order_by = array(array("column"=>"nazev_knihy", "sort"=>"asc"), array("column"=>"id_exemplare", "sort"=>"asc"));

        return $this->DBSelectAll($table_name, $columns, $wheres, '', $order_by);
    }

     public function GetKnihyOf($username) {
        $tables = "knihy k inner join vypujcni_kategorie kat on k.id_vypujcni_kategorie = kat.id_vypujcni_kategorie".
                  " inner join exemplare e on e.id_knihy = k.id_knihy inner join vypujcky v on e.id_exemplare = v.id_exemplare".
                  " inner join ctenari c on v.id_ctenare = c.id_ctenari inner join uzivatele u on c.id_uzivatele = u.id_uzivatele";

        $columns = "k.nazev as nazev_knihy, k.rok_vydani as rok_vydani,".
                   " DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY) as deadline,".
                   " DATEDIFF(DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY), NOW())as zbyva_dni,".
                   " CASE WHEN DATEDIFF(DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY), NOW()) < 0".
                   " THEN concat(DATEDIFF(DATE_ADD(v.datum_vypujceni, INTERVAL kat.doba_vypujceni DAY), NOW()) * -100, ',- Kc') ELSE '0,- Kc' END as dluh, ".
                   " u.username as username";

        $wheres = array(array("column"=>"username", "value"=>$username, "symbol"=>"="));
        $order_by = array(array("column"=>"zbyva_dni", "sort"=>"asc"));
        return $this->DBSelectAll($tables, $columns, $wheres, "", $order_by);
    }

    private function ProcessKnihy($knihy){
        
        $nove_knihy = array();

        for($i=0; $i<count($knihy); $i++) {
            $kniha = $knihy[$i];

            $id_exemplare = $kniha["id_exemplare"];
            $nazev = $kniha["nazev_knihy"];
            $autori = array($kniha["jmeno_autora"]);
            $zanry = array($kniha["zanr"]);
            $rok = $kniha["rok_vydani"];
            $popis = $kniha["popis"];
            $vek = $kniha["vek"];
            $stav = $kniha["stav"];

            $ukazatel = $i; $tmp_kniha;                                                         // < count($knihy) pro jistotu
            while(($ukazatel < count($knihy)-1) && $knihy[$ukazatel+1]["id_exemplare"] == $kniha["id_exemplare"]) {
               
                //budeme prochazet nasledujicich n knih, dokud budou nasledovat stejna iD a bude se jednat tedy o stejnou knihu,
                //ktere budeme skladat autory a zanry
                $ukazatel++;

                $tmp_kniha = $knihy[$ukazatel];

                $autor = $tmp_kniha["jmeno_autora"];
                if(!in_array($autor, $autori)) array_push($autori, $autor);

                $zan = $tmp_kniha["zanr"];
                if(!in_array($zan, $zanry)) array_push($zanry, $zan);
            }

            $i = $ukazatel;

            //arrays->string
            $autori_str = '';
            foreach($autori as $aut) {
                if($autori_str == '') {
                    $autori_str .= $aut;
                } else {
                    $autori_str .= ',<br> ' . $aut;
                }
            }

            $zanry_str = '';
            foreach($zanry as $zan) {
                if($zanry_str == '') {
                    $zanry_str .= $zan;
                } else {
                    $zanry_str .= ',<br> ' . $zan;
                }
            }
    
            $nova_kniha = array('id_exemplare' => $id_exemplare, 'nazev_knihy' => $nazev, 'jmeno_autora' => $autori_str, 'zanr' => $zanry_str, 'rok_vydani' => $rok, 'popis' => $popis, 'vek' => $vek, 'stav' => $stav);
            array_push($nove_knihy, $nova_kniha);
        }
        return $nove_knihy;
    }


}

?>	