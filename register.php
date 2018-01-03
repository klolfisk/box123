<?php
	ob_start();
	session_start();
	include_once("dbconnect.php");
	include_once("navigation.php");

	/* Check if a user is already logged in
	if a user is logged in send to home */
	if ( isset($_SESSION[ 'user'])!="") {
		header("Location: index.php");
	}

	// If register button is pressed, register the user
	if (isset($_POST['btn-register'])) {

		// clean user inputs to prevent sql injection, by using trim, strip_tags and htmlspecialchar
		$Name = trim($_POST['Name']);
		$Name = strip_tags($Name);
		$Name = htmlspecialchars($Name);

		$PSN = trim($_POST['PSN']);
		$PSN = strip_tags($PSN);
		$PSN = htmlspecialchars($PSN);

		$Email = trim($_POST['Email']);
		$Email = strip_tags($Email);
		$Email = htmlspecialchars($Email);

		$Password = trim($_POST['Password']);
		$Password = strip_tags($Password);
		$Password = htmlspecialchars($Password);

		// create an user and cart in the database
		$query = "INSERT INTO User(UserName, UserPSN, UserEmail, UserPassword) VALUES('$Name','$PSN','$Email','$Password')";
		$query2 = "INSERT INTO Cart(User_UserPSN) VALUES('$PSN')";

		$result = mysqli_query(connectionToDB(), $query);
		$result2 = mysqli_query(connectionToDB(), $query2);

		// Check if insertion was succesful
		if ($result && $result2) {
			$errorType = "Success";
				//$registerError = "Registrering lyckades";
			$registerError = '<script type="text/javascript">alert("Registration successful, you can now login!");</script>';
			unset($Name);;
			unset($PSN);
			unset($Email);
			unset($Password);
		} else {
			$errorType = "Danger";
			//$registerError = "Något gick fel, vänligen försök igen";
			$regsiterError = '<script type="text/javascript">alert("Registration failed, please try again!");</script>';
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Box123 - Registration</title>
	<link rel="stylesheet" type="text/css" href="css/styling.css">
	<meta http-equiv="Content-Type" content="text/html; charset=ANSI" />
</head>

<body>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
		<div class="login-box">
			<div class="container">
				<!-- Get inputs for registration -->
				<label><b>Name</b></label>
				<input type="Name" name="Name" placeholder="Enter your name (ex. John Doe)" maxlength="45" value="<?php echo $Name; ?>" pattern="[a-zA-Z\s]+" title="Förnamn och efternamn" required>

				<label><b>Personal number</b></label>
				<input type="PSN" name="PSN" placeholder="Enter in format (YYYYMMDDXXXX)" maxlength="45" value="<?php echo $PSN; ?>" pattern="[0-9]{12}" title="YYYYMMDDXXXX" required>

				<label><b>Email</b></label>
				<input type="Email" name="Email" placeholder="Enter your email" maxlength="45" value="<?php echo $Email; ?>" pattern="[a-z0-9A-Z._%+-]+@[a-z0-9A-Z.-]+\.[a-z]{2,3}$" title="Example@example.com" required>

	      			<label><b>Password</b></label>
				<input type="Password" name="Password" placeholder="Enter your password" maxlength="45" value="<?php echo $Password; ?>" pattern="[a-z0-9A-Z]+" title="Kan bara innehålla små, stora bokstäver samt siffror." required>
				<!-- submit registration inputs if register button is pressed -->
				<button type="submit" class="button" name="btn-register">Register</button>
	            <p>Already registered? <a href="login.php">Log in here!</a></p>
			</div>

			<!-- echo registerError if successful registartion or if not successful -->
			<div class="container">
	   		<?php if (isset($registerError) ) {
				echo $registerError;
	   		} ?>
			</div>
		</div>
	</form>
</body>
</html>
<?php ob_end_flush(); ?>
