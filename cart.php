<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	include_once("navigation.php");

	// function for printing the list of items added to cart
	function create_cart_list($productid, $price, $quantity, $productname ) { ?>
		<tr>
			<td><?php echo $productname?></td>
			<td><?php echo $productid ?></td>
			<td><?php echo $quantity ?></td>
			<td><?php echo $price ?></td>
		</tr><?php
	}

	// if user presses the clear cart button
	if (isset($_POST['cleancartbtn'])) {
		$user=$_SESSION['user'];
		$cartid = mysqli_query($link,"SELECT `CartID` FROM `Cart` WHERE `User_UserPSN` = '$user'");
		$row = mysqli_fetch_array($cartid, MYSQLI_ASSOC);
		$cartid = $row['CartID']*1;

		// delete all cartitems in the users cart
		mysqli_query($link, "DELETE FROM `CartItem` WHERE `Cart_CartID`='$cartid'");
	}

	// if user presses the order button
	if (isset($_POST['btn-order'])) {
		$user = $_SESSION['user'];

		// get which cart is owned by user from database and get all the items that is added to users cart
		$cartid = mysqli_query($link, "SELECT `CartID` FROM `Cart` WHERE `User_UserPSN` = '$user'");
		$row = mysqli_fetch_array($cartid, MYSQLI_ASSOC);
		$cartid = $row['CartID']*1;
		$items = mysqli_query($link, "SELECT Product_ProductID, CartItemQuantity, CartItemPrice FROM CartItem WHERE Cart_CartID = '$cartid'");

		// items is in cart
		if ($items) {

			// get data from submited form
			$shipaddress = $_POST['OrderShipAddress'];
			$zip = $_POST['OrderZip'];
			$city = $_POST['OrderCity'];
			$getdate = mysqli_query($link, "SELECT CURDATE()");
			$row = mysqli_fetch_array($getdate, MYSQLI_ASSOC);
			$date = $row['CURDATE()'];

			// insert data into order
			$query = "INSERT INTO `Order`(`User_UserPSN`, `OrderShipAddress`, `OrderZip`, `OrderCity`, `OrderDate`) VALUES('$user','$shipaddress','$zip','$city','$date')";
			$result = mysqli_query($link, $query);

			// check if insert was succesful
			if ($result) {

				// get orderid from orders that are not shipped
				$orderid = mysqli_query($link,"SELECT OrderID FROM `Order` WHERE User_UserPSN = '$user' AND OrderHandled = 0");
				$row = mysqli_fetch_array($orderid, MYSQLI_ASSOC);
				$orderid = $row['OrderID'] * 1;

				// insert items from cartitems into orderitems
				while($row = mysqli_fetch_array($items)) {

					$productid = $row['Product_ProductID'] * 1;
					$quantity = $row['CartItemQuantity'] * 1;
					$price = $row['CartItemPrice'] * 1;

					// Add the items to the order items table
					$query = "INSERT INTO `OrderItem`(`Order_OrderID`, `Product_ProductID`, `OrderItemQuantity`, `OrderItemPrice`) VALUES($orderid, $productid, $quantity, $price)";
					$result = mysqli_query($link, $query);

					// update the stock in products
					$prodstock = mysqli_query($link, "SELECT `ProductStock` FROM `Product` WHERE `ProductID` = '$productid'");
					$stockrow = mysqli_fetch_array($prodstock, MYSQLI_ASSOC);
					$stock = $stockrow['ProductStock'];
					$newquantity = $stock - $quantity;
					mysqli_query($link, "UPDATE `Product` SET `ProductStock` = '$newquantity' WHERE `ProductID` = '$productid' ");
				}

				// if all items was succesfully added, delete the items from cartitem and set the order to shipped
				if($result) {
					mysqli_query($link, "DELETE FROM `CartItem` WHERE `Cart_CartID`='$cartid'");
					mysqli_query($link, "UPDATE `Order` SET `OrderHandled` = 1 WHERE `User_UserPSN` = '$user'");

					$getorderid = mysqli_query($link, "SELECT `OrderID`, `OrderDate`, `OrderAddedToHistory` FROM `Order` WHERE `User_UserPSN` = '$user'");
					while($row = mysqli_fetch_array($getorderid)) {
						$orderid = $row['OrderID']*1;
						$date = $row['OrderDate'];
						echo $date;
						$history = $row['OrderAddedToHistory']*1;

						// check if order is added to orderhistory
						if ($history == 0) {
							$items = mysqli_query($link, "SELECT OrderItemQuantity, Product_ProductID FROM OrderItem WHERE Order_OrderID = '$orderid'");

							// if order is not added to orderhistory, add it to order history
							while ($row2 = mysqli_fetch_array($items)) {
								$quantity = $row2['OrderItemQuantity']*1;
								$productid = $row2['Product_ProductID']*1;
								$getname = mysqli_query($link, "SELECT ProductName FROM Product WHERE ProductID = '$productid'");
								$row3 = mysqli_fetch_array($getname);
								$name = $row3['ProductName'];
								mysqli_query($link, "INSERT INTO `OrderHistory`(Order_OrderID, OrderHistoryQuantity, OrderHistoryProductID, OrderHistoryDate, OrderHistoryName) VALUES($orderid, $quantity, $productid, '$date', '$name')");

								// update so that the order is set to have been added to history
								mysqli_query($link, "UPDATE `Order` SET `OrderAddedToHistory` = 1 WHERE `OrderID` = '$orderid' ");
							}
						}
					}
					echo '<script type="text/javascript">alert("Your order was successful!");</script>';
				} else {
					echo '<script type="text/javascript">alert("Something went wrong when creating your order...");</script>';
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Box123 - Shopping cart</title>
	<link rel="stylesheet" type="text/css" href="css/styling.css">
</head>

<body>
	<div class="cart-box">
		<div class="cart-title">
			<h1>Shopping cart</h1>
		</div>
		<div class="cart-box-data">
			<table>
				<thead>
					<tr>
						<th><h2>Product name</h2></th>
						<th><h2>Product ID</h2></th>
						<th><h2>Amount</h2></th>
						<th><h2>Price</h2></th>
					</tr>
				</thead>

				<tbody>
					<?php
					/* get data for printing the cart list */
					$user = $_SESSION['user'];
					$cartid = mysqli_query($link, "SELECT CartID FROM Cart WHERE User_UserPSN ='$user'");
					$Row = mysqli_fetch_array($cartid, MYSQLI_ASSOC);
					$cartid = $Row['CartID'];
					$cartitems = mysqli_query($link, "SELECT Product_ProductID, CartItemQuantity, CartItemPrice FROM CartItem WHERE Cart_CartID = '$cartid'");

					/* print all the items in the cart */
					if (mysqli_num_rows($cartitems) > 0) {
	    						while ($Row = mysqli_fetch_array($cartitems, MYSQLI_ASSOC)) {
									$productid = $Row['Product_ProductID'];
									$productname = mysqli_query($link, "SELECT ProductName FROM Product WHERE ProductID = '$productid'");
									$Row2 = mysqli_fetch_array($productname);
									create_cart_list($Row['Product_ProductID'],$Row['CartItemPrice'],$Row['CartItemQuantity'], $Row2['ProductName']);
									$total = $Row['CartItemPrice'] + $total;
								}
					} ?>
				</tbody>

				<tfoot>
	    			<tr>
	      				<th class="right" colspan="3"><h3>Total price:</h3></th><th class="right"><?php echo $total ?></th>
	    			</tr>
	   			</tfoot>
			</table>
		</div>
		<?php
			$user = $_SESSION['user'];
			$cartcheck = mysqli_query($link, "SELECT CartID FROM Cart WHERE User_UserPSN = '$user'");
			$CartRow = mysqli_fetch_array($cartcheck, MYSQLI_ASSOC);
			$cartid = $CartRow['CartID'];
			$cartitems = mysqli_query($link, "SELECT CartItemID FROM CartItem WHERE Cart_CartID = '$cartid'");

			/* check if items in cart, and disaply buttons if true */
			if (mysqli_num_rows($cartitems) > 0) { ?>
				<div class="cart-footer">
					<button onclick="document.getElementById('id05').style.display='block'" class="addproductbtn" name="btn-add-product" style="width: 100%">Place order</button>
					<form method="post" action="">
						<input type="hidden" name="user" value="<?php echo $user?>">
						<button name="cleancartbtn" type="submit" class="cancelbtn" style="width: 100%">Clear shopping cart</button>
					</form>
				</div>
			<?php } ?>

		<!-- Order modal, takes inputs for orders -->
		<div id="id05" class="modal">
			<form method="post" class="modal-content" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
	    		<div class="container">
	      			<label><b>Address:</b></label>
	      			<input type="text" placeholder="Skriv in adress" name="OrderShipAddress" value="<?php echo $shipaddress; ?>" pattern="[a-ö0-9A-Ö\s]+" title="Kan bara innehålla små, stora bokstäver samt siffror." required>

	      			<label><b>Zip code:</b></label>
	      			<input type="text" placeholder="Skriv in zip kod" name="OrderZip" value="<?php echo $zip; ?>"  pattern="[0-9]{5}" required>

	      			<label><b>City:</b></label>
	      			<input type="text" placeholder="Skriv in ort" name="OrderCity" value="<?php echo $city; ?>" pattern="[a-zA-Z\s]+" required>

	      				<!-- order button -->
	      			<button type="submit" name="btn-order" class="addproductbtn">Place order</button>
					<button type="button" onclick="document.getElementById('id05').style.display='none'" class="cancelbtn">Cancel</button>
	    		</div>
	  		</form>
		</div>
	</div>
</body>

<script>
// Get the modal
var modal = document.getElementById('id05');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

</script>
</html>
<?php ob_end_flush(); ?>
