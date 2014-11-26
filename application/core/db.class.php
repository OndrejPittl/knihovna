<?php 

class db
{
	public $connection = null;
	
	public function DBSelectOne($table_name, $select_columns_string, $where_array, $limit_string = "")
	{
		$mysql_pdo_error = false;
		$where_pom = "";

		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if ($where_pom != "") $where_pom .= "AND ";

				$column = $item["column"];
				$symbol = $item["symbol"];

				if (key_exists("value", $item))
					$value_pom = "?";
				else if (key_exists("value_mysql", $item))
					$value_pom = $item["value_mysql"];

				$where_pom .= "`$column` $symbol  $value_pom ";
			}

		if (trim($where_pom) != "") $where_pom = "where $where_pom";

		$query = "select $select_columns_string from ".$table_name." $where_pom $limit_string;";
		//echo "$query <br/>";

		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if (key_exists("value", $item))
				{
					$value = $item["value"];
					$statement->bindValue($bind_param_number, $value);
					$bind_param_number ++;
				}
			}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			return $row;
		}
		else
		{
			echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
			printr($errors);
			echo "SQL dotaz: $query";
			return null;
		}
	}

	public function DBSelectAll($table_name, $select_columns_string, $where_array, $limit_string = "", $order_by_array = array())
	{
		$mysql_pdo_error = false;
		$where_pom = "";

		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if ($where_pom != "") $where_pom .= "AND ";

				if (!key_exists("column", $item))
				{
					echo "Chybi klic column <br/>";
					continue;
				}

				$column = $item["column"];
				$symbol = $item["symbol"];

				$value_pom = '';
				if (key_exists("value", $item))
					$value_pom = "?";
				else if (key_exists("value_mysql", $item))
					$value_pom = $item["value_mysql"];

				$where_pom .= "`$column` $symbol  $value_pom ";
			}

		if (trim($where_pom) != "") $where_pom = "having $where_pom";

		$order_by_pom = "";

		if ($order_by_array != null)
			foreach ($order_by_array as $index => $item)
			{
				$column = $item["column"];
				$sort = $item["sort"];

				if (trim($order_by_pom != null))
					$order_by_pom .= ", ";

				$order_by_pom .= "`$column` $sort";
			}

		if (trim($order_by_pom) != "") $order_by_pom = "order by $order_by_pom";

		$query = "select $select_columns_string from ".$table_name." $where_pom $order_by_pom $limit_string;";
		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if (key_exists("value", $item))
				{
					$value = $item["value"];
			 		$statement->bindValue($bind_param_number, $value);
			 		$bind_param_number ++;
				 }
		 	}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
		 	$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		 	return $rows;
		}
		else
		{
		 	echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
		 	printr($errors);
		 	echo "SQL dotaz: $query";
		}
	}

	public function DBSelectAll_where($table_name, $select_columns_string, $where_array, $limit_string = "", $order_by_array = array())
	{
		$mysql_pdo_error = false;
		$where_pom = "";

		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if ($where_pom != "") $where_pom .= "AND ";

				if (!key_exists("column", $item))
				{
					echo "Chybi klic column <br/>";
					continue;
				}

				$column = $item["column"];
				$symbol = $item["symbol"];

				$value_pom = '';
				if (key_exists("value", $item))
					$value_pom = "?";
				else if (key_exists("value_mysql", $item))
					$value_pom = $item["value_mysql"];

				$where_pom .= "`$column` $symbol  $value_pom ";
			}

		if (trim($where_pom) != "") $where_pom = "where $where_pom";

		$order_by_pom = "";

		if ($order_by_array != null)
			foreach ($order_by_array as $index => $item)
			{
				$column = $item["column"];
				$sort = $item["sort"];

				if (trim($order_by_pom != null))
					$order_by_pom .= ", ";

				$order_by_pom .= "`$column` $sort";
			}

		if (trim($order_by_pom) != "") $order_by_pom = "order by $order_by_pom";

		$query = "select $select_columns_string from ".$table_name." $where_pom $order_by_pom $limit_string;";
		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if (key_exists("value", $item))
				{
					$value = $item["value"];
			 		$statement->bindValue($bind_param_number, $value);
			 		$bind_param_number ++;
				 }
		 	}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
		 	$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		 	return $rows;
		}
		else
		{
		 	echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
		 	printr($errors);
		 	echo "SQL dotaz: $query";
		}
	}

	

	/**
	*	Ciste pro vnitrni prikazy, kam nevstupuji vstupni parametry, tj. nehrozi SQL injection
	*	//napr. pro "describe knihy"
	*/
	public function exec($query){
		$statement = $this->connection->prepare($query);
		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
			printr($errors);
			echo "SQL dotaz: $query";

		} else {
			$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
			return $rows;
		}
	}
	
	
	public function DBInsert($table_name, $item)
	{
		$mysql_pdo_error = false;

		$insert_columns = "";
		$insert_values  = "";

		if ($item != null)
			foreach ($item as $column => $value)
			{
				if ($insert_columns != "") {
					$insert_columns .= ", ";
					$insert_values .= ", ";
				}

				$insert_columns .= "`$column`";
				$insert_values .= "?";
			}

 		
		$query = "insert into `$table_name` ($insert_columns) values ($insert_values);";
		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		if ($item != null)
			foreach ($item as $column => $value) {
				$statement->bindValue($bind_param_number, $value);
				$bind_param_number ++;
			}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
			$item_id = $this->connection->lastInsertId();
			return $item_id;
		}
		else
		{
			echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
			printr($errors);
			echo "SQL dotaz: $query";
		}
	}


	public function DBInsertExpanded($table_name, $item)
	{
		$insert_columns = "";
		$insert_values  = "";

		if ($item != null)
			foreach ($item as $row) {
				if ($insert_columns != "") $insert_columns .= ", ";
				if ($insert_columns != "") $insert_values .= ", ";

				$column = $row["column"];

				if (key_exists("value", $row))
	 				$value_pom = "?"; 
	 			else if (key_exists("value_mysql", $row))
	 				$value_pom = $row["value_mysql"]; 		

	 			$insert_columns .= "'$column'";
	 			$insert_values .= "$value_pom";
	 		}

 		$query = "insert into '$table_name' ($insert_columns) values ($insert_values);";
 		$statement = $this->connection->prepare($query);

 		$bind_param_number = 1;
 		if ($item != null)
 			foreach ($item as $row)
 			{
 				if (key_exists("value", $row))
 				{
 					$value = $row["value"];
					$statement->bindValue($bind_param_number, $value);
					$bind_param_number ++;
				}
			}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
			$item_id = $this->connection->lastInsertId();
			return $item_id;
		}
		else
		{
			echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
			printr($errors);
			echo "SQL dotaz: $query";
		}
	}


	public function DBUpdate($table_name, $data, $where_array)
	{
		$mysql_pdo_error = false;
		$sety = '';

		foreach($data as $key => $value) {
			if($sety != "") $sety .= ", ";

			$sety .= "`$key` = ?";
		}

		$where_pom = '';
		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if ($where_pom != "") $where_pom .= "AND ";

				if (!key_exists("column", $item))
				{
					echo "Chybi klic column <br/>";
					continue;
				}

				$column = $item["column"];
				$symbol = $item["symbol"];

				if (key_exists("value", $item))
					$value_pom = "?";
				else if (key_exists("value_mysql", $item))
					$value_pom = $item["value_mysql"];

				$where_pom .= "`$column` $symbol  $value_pom ";
			}
	 	
		if (trim($where_pom) != "") $where_pom = "where $where_pom";

		$query = "update $table_name set $sety $where_pom";
		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		foreach ($data as $index => $item)
		{
			$value = $item;

	 		$statement->bindValue($bind_param_number, $value);
	 		$bind_param_number++;
		}


		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if (key_exists("value", $item))
				{
					$value = $item["value"];
			 		$statement->bindValue($bind_param_number, $value);
			 		$bind_param_number++;
			 	}
			 }


		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == true)
		{
			echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
			printr($errors);
			echo "SQL dotaz: $query";
			return false;
		}

		return true;
	}


	public function DBDelete($table_name, $where_array)
	{
		$mysql_pdo_error = false;
 		$where_pom = "";

		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if ($where_pom != "") $where_pom .= "AND ";

				$column = $item["column"];
				$symbol = $item["symbol"];

				if (key_exists("value", $item))
					$value_pom = "?";
				else if (key_exists("value_mysql", $item))
					$value_pom = $item["value_mysql"];

				$where_pom .= "`$column` $symbol  $value_pom ";
			}

		if (trim($where_pom) != "") $where_pom = "where $where_pom";

		$query = "delete from `$table_name` $where_pom;";
		//echo $query;

		$statement = $this->connection->prepare($query);

		$bind_param_number = 1;
		if ($where_array != null)
			foreach ($where_array as $index => $item)
			{
				if (key_exists("value", $item))
				{
					$value = $item["value"];
			 		$statement->bindValue($bind_param_number, $value);
			 		$bind_param_number ++;
				 }
		 	}

		$statement->execute();
		$errors = $statement->errorInfo();

		if ($errors[0] + 0 > 0)
		{
			$mysql_pdo_error = true;
		}

		if ($mysql_pdo_error == false)
		{
		 	$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
		 	return $rows;
		}
		else
		{
		 	echo "Chyba v dotazu - PDOStatement::errorInfo(): ";
		 	printr($errors);
		 	echo "SQL dotaz: $query";
		}
	}


	public function CheckDataExistance($tableNames, $data){

		$wheres = array();
		foreach($data as $key => $val) {
			array_push($wheres, array('column'=>$key, 'value'=>$val, 'symbol'=>'='));	
		}

		return $this->DBSelectOne($tableNames, '*', $wheres, "limit 1");
	}


	function Connect() {
		try
		{
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',);
			$this->connection = new PDO("mysql:host=".DB_HOST.";dbname=".DB_DATABASE_NAME."", DB_USER_LOGIN, DB_USER_PASSWORD, $options);

			$this->connection->exec("SET NAMES UTF8");

		} 
		catch (PDOException $e)
		{
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	
	
	function Disconnect() {
		$this->connection = null;
	}
	
	public function GetConnection() {
		return $this->connection;
	}
}


?>