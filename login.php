<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	include_once("navigation.php");

	/* Doesn't let you open login page if session is set, doesn't let you log in if you're already logged in*/
	if( isset($_SESSION['user']) != "") {
		header("Location: index.php");
		exit;
	}

	/* When you press the login button */
	if(isset($_POST['btn-login'])) {

		// Prevent SQL injections from client
		$Email = trim($_POST['Email']);
		$Email = strip_tags($Email);
		$Email = htmlspecialchars($Email);

		$Password = trim($_POST['Password']);
		$Password = strip_tags($Password);
		$Password = htmlspecialchars($Password);

		/*Check if user is registered and credentials are correct*/
		$result = mysqli_query($link, "SELECT UserPSN, UserPassword FROM User WHERE UserEmail = '$Email'");
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$validationCount = mysqli_num_rows($result); // If correct the return needs to be 1 row, otherwise multiple users

		if($validationCount == 1 && $row['UserPassword'] == $Password) {
			$_SESSION['user'] = $row['UserPSN'];
			//$loginErrorSuccess = '<script type="text/javascript">alert("Inloggningen lyckades!");</script>';
			header("Location: index.php");
		} else {
			$loginErrorFail = '<script type="text/javascript">alert("Log in failed, please try again!");</script>';
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Box123 - Log in</title>
	<link rel="stylesheet" type="text/css" href="css/styling.css">
	<meta http-equiv="Content-Type" content="text/html; charset=ANSI" />
</head>

<body>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
		<div class="login-box">
			<div class="container">
				<label><b>Email</b></label>
				<input type="Email" name="Email" placeholder="Enter your email" maxlength="45" value="<?php echo $Email; ?>" pattern="[a-z0-9A-Z._%+-]+@[a-z0-9A-Z.-]+\.[a-z]{2,3}$" title="Example@example.com" required>

	      		<label><b>Password</b></label>
				<input type="Password" name="Password" placeholder="Enter your password" maxlength="45" required>

				<button type="submit" class="button" name="btn-login">Log in</button>
	            <p>Not registered? <a href="register.php">Register here!</a></p>
			</div>

			<div class="container">
			   	<?php
			   	if (isset($loginErrorFail)) {
					echo $loginErrorFail;
			   	} elseif (isset($loginErrorSuccess)) {
					echo $loginErrorSuccess;
				}
			   	?>
			</div>
		</div>
	</form>
</body>
</html>
<?php ob_end_flush(); ?>
