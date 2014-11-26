<?php 

class komentar extends db
{
	
	/**
	* Konstruktor.
	*/
	public function komentar($connection)
	{
		$this->connection = $connection;	
	}
	
   	public function GetKomentare(){
        return $this->DBSelectAll('komentare', 'id_komentare, datum, autor, obsah', null);
    }

    public function VlozKomentar($data){
        $this->DBInsert("komentare", $data);
    }

    
}

?>	