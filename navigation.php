<!DOCTYPE html>
<html>
<head>
	<title> Box123 - Home </title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/navigation_bar.css">
</head>

<body>
	<nav class="topnav" id="topNavBar">
		<!-- Navigation bar if NOT logged in -->
		<?php if(!isset($_SESSION['user'])) { ?>
			<a href="index.php">Home</a>
			<a href="product.php">Products</a>
			<a id="navRightFloat" href="register.php">Register</a>
			<a id="navRightFloat" href="login.php">Log in</a>
			<a class="icon" href="javascript:void(0);" style="font-size:15px;" onclick="topNavResponsive()">&#9776;</a>

		<!-- Navigation bar if logged in -->
		<?php } else if(isset($_SESSION['user'])!="") { ?>
			<a href="index.php">Home</a>
			<a href="product.php">Products</a>
			<a href="cart.php">Shopping cart</a>
			<a id="navRightFloat" href="order.php">Orders</a>
			<a id="navRightFloat" href="logout.php?logout">Log out</a>
			<a class="icon" href="javascript:void(0);" style="font-size:15px;" onclick="topNavResponsive()">&#9776;</a>
		<?php } ?>
	</nav>
</body>

<script>
function topNavResponsive() {
    var x = document.getElementById("topNavBar");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";
    }
}
</script>

</html>
