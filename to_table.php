<?php

include 'data.php';

if(isset($_FILES) != null){
	send_sql($conn, "DROP TABLE IF EXISTS `domestic_data_strings`.`data`");
	send_sql($conn, "CREATE TABLE data (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY);");
	$data = $_FILES['data']['name'];
}

$col_names =  fgetcsv( fopen($data, "r"), 1000, ",");
$cols = "";
foreach ($col_names as $col) {
	$cols .= "". $col .", ";
	send_sql($conn, "ALTER TABLE data ADD $col VARCHAR(50)"); 
}
$cols = substr($cols, 0, -2);

//LOAD DATA INFILE of LOAD DATA LOCAL INFILE
$sql = "LOAD DATA LOCAL INFILE '$data' INTO TABLE data FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n' ($cols)";


send_sql($conn, $sql);

/*echo "<pre>";
print_r($data);
echo "</pre>"; */
header("Location: string_simulator_v3.php?complete=1");
?>