<?php 

class zanr extends db
{
	
	/**
	* Konstruktor.
	*/
	public function zanr($connection)
	{
		$this->connection = $connection;	
	}
	
    public function GetZanry(){
        return $this->DBSelectAll('zanry', 'distinct nazev', null);
    }

    public function GetZanryID(){
        return $this->DBSelectAll('zanry', 'id_zanry, nazev', null);
    }
}

?>	