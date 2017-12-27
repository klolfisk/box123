<?php
	session_start();

	// Clears all the data and variables of the session when you logout
	if (isset($_GET['logout'])) {
		unset($_SESSION['user']);
		session_unset();
		session_destroy();
		header("Location: index.php");
		exit;
	}
?>
