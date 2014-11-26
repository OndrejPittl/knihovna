<?php 

class tabulka extends db
{
	
	/**
	* Konstruktor.
	*/
	public function tabulka($connection)
	{
		$this->connection = $connection;	
	}

	public function GetTableNames(){
		$wheres = array(
			array("column"=>"TABLE_TYPE", "value"=>"BASE TABLE", "symbol"=>"="),
			array("column"=>"TABLE_SCHEMA", "value"=>"pittl_knihovna", "symbol"=>"=")
			);

		$tabs = $this->DBSelectAll_where('INFORMATION_SCHEMA.TABLES', 'INFORMATION_SCHEMA.TABLES.TABLE_NAME', $wheres);
        
		$tabulky = array();
		foreach ($tabs as $tab) {
			array_push($tabulky, $tab["TABLE_NAME"]);
		}

		return $tabulky;
    }

    public function GetTableRows($table_name) {
        return $this->DBSelectAll($table_name, '*', null);
    }

    public function GetTableColumns($table_name){
        return $this->exec("describe $table_name;");
    }
    
    public function GetOne($table_name, $id) {
        return $this->DBSelectOne($table_name, '*', array(array("column"=>"id_" . $table_name, "value"=>$id, "symbol"=>"=")));
    }

    public function UpdateZaznam($data){
        $table_name = $data["tabulka"];
        $id = $data["id_$table_name"];
        unset($data["tabulka"]);

        $this->DBUpdate($table_name, $data, array(array('column' => "id_$table_name", 'value'=>$id, 'symbol'=>'=')));
    }

    public function DeleteZaznam($data){
        $table_name = $data["tabulka"];
        $id = $data["id_$table_name"];
        unset($data["tabulka"]);

        $this->DBDelete("$table_name", array(array('column' => "id_$table_name", 'value'=>$id, 'symbol'=>'=')));
    }
	
	public function PridatZaznam($data){
        $table_name = $data["tabulka"];
        unset($data["tabulka"]);

        $this->DBInsert($table_name, $data);
    }
}

?>	