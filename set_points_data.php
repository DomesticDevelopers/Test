<?php
include 'data.php';

if(isset($_FILES)){
	$data = $_FILES["file"]["name"];
	$db_name = mysqli_escape_string($conn, $_POST["tblname"]);
	return_ajax(create_table($conn, $db_name, $data));
}else{
	return_ajax("error");
}

function create_table($conn, $name, $data){
	send_sql($conn, "DROP TABLE IF EXISTS domestic_data_strings.".$name."");
	send_sql($conn, "CREATE TABLE $name (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY);");

	$col_names =  fgetcsv( fopen($data, "r"), 1000, ",");
	$cols = "";
	foreach ($col_names as $col) {
		$cols .= "". $col .", ";
		send_sql($conn, "ALTER TABLE $name ADD $col VARCHAR(50)"); 
	}
	$cols = substr($cols, 0, -2);


	send_sql($conn, "LOAD DATA LOCAL INFILE '".$data."' INTO TABLE $name FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' ($cols)");

	return array(getData($conn, "SELECT * FROM $name"), $name);

}
?>