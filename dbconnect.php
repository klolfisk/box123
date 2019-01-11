<?php
	function connectionToDB() {
		/* Inputs to connect to our DB */
		define('DBHOST', '');
		define('DBUSER', '');
		define('DBPASS', '');
		define('DBNAME', '');

		/* Open connection to MySQL server */
		$link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);

		/* If connection fails, exit and display message*/
		if ( !$link ) {
			die("Connection failed : " . mysqli_error($link));
		}
		return $link;
	}
?>
