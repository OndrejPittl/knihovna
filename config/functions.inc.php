<?php
	
	function printr($val)
	{
	  echo "<hr><pre>";
	  print_r($val);
	  echo "</pre><hr>";
	}

	function getDnesniDatum(){
        return StrFTime("%Y-%m-%d %H:%M:%S", Time());
	}

	function FormatDate($datum){
		return date_format(date_create($datum), "d. m. Y, H:i:s");
	}


?>