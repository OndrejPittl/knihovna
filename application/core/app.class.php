<?php

/**
 * Ridici trida aplikace.
 *
 */

class app
{
    private $data = null;
    private $db = null;

    /**
     * Konstruktor.
     */
    public function app()
    {
        $this->db = new db();
    }

    public function GetConnection()
    {
    	return $this->db->GetConnection();
    }
    
    /**
     * Pripojit k databazi.
     */
    public function Connect()
    {
    	$this->db->Connect();
    }
   
}

?>