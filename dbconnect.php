<?php
	function connectionToDB() {
		/* Inputs to connect to our DB */
		define('DBHOST', 'localhost');
		define('DBUSER', 'root');
		define('DBPASS', 'core13');
		define('DBNAME', 'box123db');

		/* Open connection to MySQL server */
		$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

		/* If connection fails, exit and display message*/
		if ( !$link ) {
			die("Connection failed : " . mysqli_error($link));
		}
		return $link;
	}
?>
