<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	include_once("navigation.php");

	/* Function for listing the orders of the users*/
	function create_order_list($productid, $quantity, $date, $productname, $orderid, $shipped) {
?>

		<tr>
			<?php if ($id == FALSE) {?>
			<td><?php echo $orderid?></td> <?php
			$id = TRUE;
			} ?>
			<td><?php echo $productname ?></td>
			<td><?php echo $productid ?></td>
			<td><?php echo $quantity ?></td>
			<td><?php echo $date ?></td>
			<td>
			<?php 	if($shipped == 1) {
					echo "Yes";
				} else {
					echo "No";
				}?>
			</td>
		</tr>
<?php
	}
	if (isset($_POST['btn-send'])) {
		$orderid = $_POST['OrderID'];
		mysqli_query($link, "UPDATE `Order` SET `OrderShipped` = 1 WHERE `OrderID` = '$orderid'");
	}
?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="styling.css">
	<title>Box123 - Orders</title>
</head>
<body>

<div class="order-box">
	<div class="cart-title">
		<h1>Orders</h1>
	</div>
	<div class="cart-box-data">
		<table>
			<!-- Head of table displaying orders -->
			<thead>
				<tr>
					<th><h3>Order ID</h3></th>
					<th><h3>Product name</h3></th>
					<th><h3>Product ID</h3></th>
					<th><h3>Amount</h3></th>
					<th><h3>Order date</h3></th>
					<th><h3>Sent</h3></th>
				</tr>
			</thead>

			<!-- Body of the table displaying the items ordered -->
			<tbody>
				<?php
				/* Gets the current users PSN */
				$user = $_SESSION['user'];
				$checkadmin = mysqli_query($link, "SELECT UserAdmin FROM User WHERE UserPSN = '$user'");
				$rad = mysqli_fetch_array($checkadmin, MYSQLI_ASSOC);
				$admin = $rad['UserAdmin'];
				if($admin == "1") {
					/* If the user has admin privleges, display all order placed*/
					$order = mysqli_query($link, "SELECT `OrderID`, `OrderShipped` FROM `Order`");
				} else {
					/* Gets the order id that belongs to the current user*/
					$order = mysqli_query($link, "SELECT `OrderID`, `OrderShipped` FROM `Order` WHERE `User_UserPSN` ='$user'");
				}


				/* Runs until all items that belong to the users order is displayed in the order table*/
				while($Row = mysqli_fetch_array($order, MYSQLI_ASSOC)) {
					$orderid = $Row['OrderID'];
					$shipped = $Row['OrderShipped'];
					/* Gets the data that is to be displayed in the order table */
					$orderinfo = mysqli_query($link, "SELECT `OrderHistoryQuantity`, `OrderHistoryProductID`, `OrderHistoryDate`, `OrderHistoryName` FROM `OrderHistory` WHERE `Order_OrderID` = '$orderid'");
					/* Checks if the number of rows in the recieved query result is greater than 0, which means there's data to be displayed */
					if (mysqli_num_rows($orderinfo) > 0) {
							/* Adds wanted data to the displayed order table */
    						while ($Row = mysqli_fetch_array($orderinfo, MYSQLI_ASSOC)) {
							create_order_list($Row['OrderHistoryProductID'],$Row['OrderHistoryQuantity'], $Row['OrderHistoryDate'], $Row['OrderHistoryName'], $orderid, $shipped);
						}
					}
				}
			?>
			</tbody>
		</table>
		<?php
		/* check if someone is logged in */
		if(isset($_SESSION['user'])!="") {
			$user = $_SESSION['user'];
			$admin = mysqli_query($link, "SELECT UserAdmin FROM User WHERE UserPSN='$user'");
			$Row = mysqli_fetch_array($admin);

			/* check if logged in user is and admin */
			if ($Row['UserAdmin'] == 1) { ?>
				<!-- display admin buttons for adding, editing and removing products -->
				<div class="admin-product-settings">
					<button onclick="document.getElementById('id01').style.display='block'" class="addproductbtn mark-order-sent" name="btn-add-product">Mark order as sent</button>
				</div><?php
			}
			unset($admin);
		}
		?>

	</div>
</div>

<!-- Form for marking the order as shipped -->
<div id="id01" class="modal">
	<form method="post" class="modal-content" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    		<div class="container">
			<?php unset($orderid); ?>
      			<label><b>Order ID:</b></label>
      			<input type="text" placeholder="Skriv in order id" name="OrderID" value="<?php echo $orderid; ?>" required>

        		<!-- add product button -->
      			<button type="submit" name="btn-send" class="addproductbtn" >Mark order as sent</button>

      			<!-- close the add product modal -->
			<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
    		</div>
  	</form>
</div>

<!-- Java script for modal function -->
<script>
var modal = document.getElementById('id01');
// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

</body>
</html>
<?php ob_end_flush(); ?>
