<?php
	ob_start();
	session_start();
	include_once("dbconnect.php");
	include_once("navigation.php");

	// if admin presses the add product button
	if (isset($_POST['btn-add'])) {

		// get data from the submited form
		$name = $_POST['ProductName'];
		$price = $_POST['ProductPrice']*1;
		$size = $_POST['ProductSize'];
		$stock = $_POST['ProductStock']*1;
		$description = $_POST['ProductDescription'];
		$image = $_POST['ProductImage'];

		// insert product data into database
		$query = "INSERT INTO Product(ProductName, ProductPrice, ProductStock, ProductSize, ProductDescription, ProductImage) VALUES('$name','$price','$stock','$size','$description','$image')";
		$result = mysqli_query(connectionToDB(), $query);

		// if insertion of product was succesful, open alert box with msg that it has been added, else open alert box with msg that something went wrong
		if ($result) {
			echo '<script type="text/javascript">alert("The product was added!");</script>';
			unset($name);
			unset($price);
			unset($size);
			unset($stock);
			unset($description);
			unset($image);
		} else {
			echo '<script type="text/javascript">alert("Something went wrong when adding the product, try again.");</script>';
		}
	} // -------------------END admin add product button press

	// if admin presses the change product button
	if (isset($_POST['btn-change'])) {

		// get data from the submited form
		$CurrentName = $_POST['ProductCurrentName'];
		$name = $_POST['ProductName'];
		$price = $_POST['ProductPrice']*1;
		$size = $_POST['ProductSize'];
		$stock = $_POST['ProductStock']*1;
		$description = $_POST['ProductDescription'];
		$image = $_POST['ProductImage'];

		//  update data in database
		$query = "UPDATE `Product` SET `ProductName`='$name', `ProductPrice`=$price, `ProductSize`='$size', `ProductStock`=$stock, `ProductDescription`='$description', `ProductImage`='$image' WHERE `ProductName`='$CurrentName'";
		$result = mysqli_query(connectionToDB(), $query);

		// if update was succesful, update prodcuts that are added to carts, , else open alert box with msg that something went wrong
		if ($result) {
			$getid = mysqli_query(connectionToDB(), "SELECT ProductID FROM Product WHERE ProductName='$name'");
			$productid = mysqli_fetch_array($getid, MYSQLI_ASSOC);
			$getid2 = $productid['ProductID'];
			$price = mysqli_query(connectionToDB(), "SELECT ProductPrice FROM Product WHERE ProductID ='$getid2'");
			$Row2 = mysqli_fetch_array($price, MYSQLI_ASSOC);
			$price2 = $Row2['ProductPrice'];
			$getquant = mysqli_query(connectionToDB(), "SELECT CartItemQuantity FROM CartItem WHERE Product_ProductID ='$getid2'");
			$Row2 = mysqli_fetch_array($getquant, MYSQLI_ASSOC);
			$quantity = $Row2['CartItemQuantity'];
			$newprice = $price2*$quantity;
			$updatecart = mysqli_query(connectionToDB(), "UPDATE CartItem SET CartItemPrice='$newprice' WHERE Product_ProductID = '$getid2'");

			// if the cart update is successful, open alert box with msg that it is changed, else open alert box with msg that something went wrong
			if($updatecart) {
				echo '<script type="text/javascript">alert("The product was changed.");</script>';
			} else {
				echo '<script type="text/javascript">alert("Something want wrong when changing the product, try again.");</script>';
			}
		} else {
			echo '<script type="text/javascript">alert("Something went wrong, try again.");</script>';
		}
	} // --------------END button change press

	// if admin presses the remove product button
	if (isset($_POST['btn-remove'])) {

		// get data from the submited form
		$name = $_POST['ProductName'];
		$productid = mysqli_query(connectionToDB(), "SELECT ProductID FROM Product WHERE ProductName = '$name'");
		$row = mysqli_fetch_array($productid, MYSQLI_ASSOC);
		$productid = $row['ProductID'];

		// Delete product from cartitem, orderitem and products
		$delcart = mysqli_query(connectionToDB(), "DELETE FROM CartItem WHERE Product_ProductID = '$productid'");
		$delorder = mysqli_query(connectionToDB(), "DELETE FROM OrderItem WHERE Product_ProductID = '$productid'");
		$query = "DELETE FROM Product WHERE ProductName='$name'";
		$result = mysqli_query(connectionToDB(), $query);

		// if delete was succesfull, open alert box with msg that it is removed, else open alert box with msg something went wrong.
		if ($result) {
			echo '<script type="text/javascript">alert("The product was removed.");</script>';
			unset($name);
		} else {
			echo '<script type="text/javascript">alert("Something went wrong when removing the product, try again.");</script>';
		}
	} // --------------END button remove press

	// if user presses the add product to cart
	if (isset($_POST['add'])) {

		// get data from database
		$user = $_SESSION['user'];
		$cartid = mysqli_query(connectionToDB(), "SELECT CartID FROM Cart WHERE User_UserPSN ='$user'");
		$Row = mysqli_fetch_array($cartid, MYSQLI_ASSOC);
		$cartid2 = $Row['CartID'];
		$productid = $_POST['product'];
		$quantity = $_POST['Quantity'];
		$checkcart = mysqli_query(connectionToDB(), "SELECT Product_ProductID, CartItemQuantity, CartItemPrice FROM CartItem WHERE Cart_CartID = '$cartid2'");
		$price = mysqli_query(connectionToDB(), "SELECT ProductPrice, ProductStock FROM Product WHERE ProductID ='$productid'");
		$Row2 = mysqli_fetch_array($price, MYSQLI_ASSOC);
		$price2 = $Row2['ProductPrice'];
		$stock = $Row2['ProductPrice'];
		$quantitycheck = 0;

		// check if user already has an item in cart
		if(mysqli_num_rows($checkcart) > 0) {
			while($row3 = mysqli_fetch_array($checkcart, MYSQLI_ASSOC)) {
				$productid2 = $row3['Product_ProductID'];

				// check if the product that is in the cart is the same as the product that the user want to add, if true try to update the product in the cart to display the new quantity and price
				if ($productid == $productid2) {
					$quantity2 = $row3['CartItemQuantity'];
					$newquantity = $quantity2 + $quantity;

					// check that the newquantity doesnt exceds the product quantity, if it doesnt update the item in the cart, else open alert box with msg that the stock isnt enough
					if ($newquantity < $stock) {
						$newprice = $newquantity * $price2;
						$update = mysqli_query(connectionToDB(), "UPDATE `CartItem` SET `CartItemQuantity`='$newquantity', `CartItemPrice`='$newprice' WHERE `Cart_CartID` ='$cartid2' AND `Product_ProductID` ='$productid'");

						// if update was succesful open alert box with msg that it worked, and break the loop since it can only be one of each productid in the cart
						if ($update == 1) {
							break;
							echo '<script type="text/javascript">alert("The product was added to the shopping cart.");</script>';
						}
					} else {
						echo '<script type="text/javascript">alert("There aren`t enough products in stock for your request.");</script>';
						$quantitycheck = 1;
					}
				}
			}
		}

		// if no items was in the cart and no item was updated
		if (!$update and $quantitycheck == 0) {

			// Insert product into cartitem database
			$price2 = $price2 * $quantity;
			$query = "INSERT INTO CartItem (Product_ProductID, Cart_CartID, CartItemQuantity, CartItemPrice) VALUES ('$productid', '$cartid2', '$quantity', '$price2')";
			$result = mysqli_query(connectionToDB(), $query);

			// if succesful open alret box with succes msg, else open alert box with failure msg
			if ($result) {
				unset($update);
				echo '<script type="text/javascript">alert("The product was added to the shopping cart.");</script>';
			} else {
				echo '<script type="text/javascript">alert("You have to be logged in to add items to the shopping cart.");</script>';
			}
		}
	} // -------------------END user add to cart button press

	// Check if user presses the add comment/rating button
	if (isset($_POST['btn-add-rating'])) {

		// get data from the submited form
		$user = $_SESSION['user'];
		$rating = $_POST['ReviewRating']*1;
		$comment = $_POST['ReviewComments'];
		$productid2 = $_POST['productid']*1;
		$checkcomment = mysqli_query(connectionToDB(), "SELECT ReviewID FROM `Review` WHERE `User_UserPSN` = '$user' AND `Product_ProductID` = $productid2");
		// Check if the user aldready rated and commented on this product
		if (mysqli_num_rows($checkcomment) > 0) {
			echo '<script type="text/javascript">alert("Remove your old comment if you want to add another one.");</script>';
		} else {
			// Insert data into Review table
			$query = "INSERT INTO Review(User_UserPSN, Product_ProductID, ReviewRating, ReviewComments) VALUES ('$user',$productid2,$rating,'$comment')";
			//$query = "INSERT INTO `Review` VALUES ('$user', '$productid2', '$rating', '$comment')";
			$result = mysqli_query(connectionToDB(), $query);

			// if the insert was not succesful open alertbox with failure msg
			if (!$result) {
				echo '<script type="text/javascript">alert("You have to be logged in to add a review to a product.");</script>';
			}
		}
	} // -------------------END user add rating button press

	// check if user/admin presses the delete commecnt button
	if (isset($_POST['delete-comment'])) {

		// get data from submited form
		$Reviewid = $_POST['ReviewID'];

		// delete Review from the Review table
		$del = mysqli_query(connectionToDB(), "DELETE FROM Review WHERE ReviewID = '$Reviewid'");

		// if delete was succesful open alert box with succes msg
		if($del) {
			echo '<script type="text/javascript">alert("The comment was deleted.");</script>';
		}
	} // --------------------END user check delete comment button
?>

<!DOCTYPE html>
<html>
<head>
	<title> Box123 - Products </title>
	<link rel="stylesheet" type="text/css" href="css/styling.css">
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
	<?php
	// function for printing out all the comments in the comment box
	function print_comments($comment, $rating, $Reviewid, $user) { ?>
		<div class="comment">
			Betyg: <?php echo(round($rating)); ?> </br>
			<?php echo $comment; ?>
		</div>
			<?php
			// check if logged in
			if(isset($_SESSION['user'])!="") {

				// get data from User table in database
				$admin = mysqli_query(connectionToDB(), "SELECT UserAdmin FROM User WHERE UserPSN='$user'");
				$Row = mysqli_fetch_assoc($admin);

				// check if the logged in user is admin or if user posted the comment/Review
				if($_SESSION['user'] == $user) { ?>
					<!-- display the delete comment button -->
					<form action="" method="post">
						<input type="hidden" name="ReviewID" value="<?php echo $Reviewid; ?>">
						<input type="submit" name="delete-comment" class="btn-add-to-cart remove-comment" value="Remove">
					</form>
		  		<?php }
			}
	} // -------------------END print_comments()

	// function for creating a modalbox for every created product
	function create_product_modal($productid, $image, $name, $size, $stock, $price, $description, $avgrating) {

		// calls the create comment modal
		create_product_comment_modal($productid);?>
		<div id="<?php echo $productid ?>" class="modal">
			<form action="" method="post" autocomplete="off">
				<div class="modal-content-product">

					<div class="modal-product-header">
						<h1> <?php echo $name; ?> </h1>
					</div>
					<!-- image box for product -->
					<div class="modal-image-container">
						<img src="<?php echo $image; ?>" />
					</div>

					<!-- info box for product-->
					<div class="modal-info-container">
						<div class="info">
							<b>Size:  <?php echo $size; ?> cm</b></br>
							<b>In stock: <?php echo $stock; ?></b></br>
							<b>Price: <?php echo $price; ?> SEK</b></br>
							<b>Rating: <?php echo(round($avgrating)); ?> out of 5</b></br>

							<!-- hidden input for productid, input to choose quantity, and add to cart button -->
							<input type="hidden" name="product" value="<?php echo $productid; ?>">
							<input type="number" name="Quantity" value="1<?php echo $quantity; ?>" pattern="[0-9]{1,3}" min="1" max="<?php echo $stock; ?>" placeholder=" Amount" style="text-align: center" required>
							<input type="submit" name="add" class="btn-add-to-cart" value="Add to cart" >
						</div>
					</div>

					<!-- comment box for product -->
					<div class="modal-rating-container">
						<h5>Rating and comments</h5>
						<?php

						// get Reviews from database and print them in comment box using the print_comments function
						$Reviews = mysqli_query(connectionToDB(), "SELECT ReviewComments, ReviewRating, ReviewID, User_UserPSN FROM Review WHERE Product_ProductID = '$productid'");
						while ($Row = mysqli_fetch_array($Reviews, MYSQLI_ASSOC)) {
							print_comments($Row['ReviewComments'], $Row['ReviewRating'], $Row['ReviewID'], $Row['User_UserPSN']);
						}
						?>
					</div>

				<!-- the add comment button, opens the modal for inserting a comment -->
				<button type="button" onclick="document.getElementById('<?php echo $productid ?>comment').style.display='block'; document.getElementById('<?php echo $productid ?>').style.display='none'" class="modal-add-comment-button">Add comment</button>

				<!-- closes the product modal -->
				<button type="button" onclick="document.getElementById('<?php echo $productid ?>').style.display='none'" class="modal-add-comment-button cancel-modal">Cancel</button>
				</div>
			</form>
		</div>
	<?php } // ------------------------END of create_product_modal()

	// function that creates a modal that takes inputs for creating a comment
	function create_product_comment_modal($productid) { ?>
		<div id="<?php echo $productid ?>comment" class="modal">
			<?php $productid2 = $productid; ?>
			<form method="post" class="modal-content" action="" autocomplete="off">
    			<div class="container">
      				<label><b>Rating:</b></label>
      				<input type="number" placeholder="Skriv in betyg (1-5)" name="ReviewRating" value="<?php echo $rating; ?>" min="1" max="5" required>

      				<label><b>Comment:</b></label>
      				<input type="text" placeholder="Skriv in kommentar" name="ReviewComments" value="<?php echo $comment; ?>" maglength="150">

      				<!-- hidden input with the productid that the comment should be added too -->
        			<input type="hidden" name="productid" value="<?php echo $productid2; ?>">

        			<!-- add comment button -->
      				<button type="submit" name="btn-add-rating" class="addproductbtn">Add comment</button>

      				<!-- close comment modal -->
					<button type="button" onclick="document.getElementById('<?php echo $productid ?>comment').style.display='none'" class="cancelbtn">Cancel</button>
    			</div>
  			</form>
		</div>
	<?php } // ---------------------END create_product_comment_modal()

	//function for creating products
	function create_product($productid, $image, $name, $size, $stock, $price, $description) {

		// get Review data from database
		$result = mysqli_query(connectionToDB(), "SELECT AVG(ReviewRating) FROM Review WHERE `Product_ProductID` = '$productid'");
		//$avgrating = mysql_result($result, 0) *1;
		mysqli_data_seek($result, 0);
		if( !empty($field) ) {
		  while($finfo = mysqli_fetch_field($result)) {
		    if( $field == $finfo->name ) {
		      $f = mysqli_fetch_assoc($result) ;
		      $avgrating =  $f[ $field ];
		    }
		  }
		} else {
		  $f = mysqli_fetch_array($result);
		  $avgrating = $f[0];
		}

		// create a modal for the product
		create_product_modal($productid, $image, $name, $size, $stock, $price, $description, $avgrating);   ?>

		<!-- echos out all the data about a product in container, and set that on click of a product open the products modal -->
		<div class="product-container" onclick="document.getElementById('<?php echo $productid ?>').style.display='block'">
			<div class="product-image-container">
				<img src="<?php echo $image; ?>" />
			</div>
			<div class="product-text-container">
				<b>Name: <?php echo $name; ?></b>
			</div>
			<div class="product-text-container">
				<b>Size: <?php echo $size; ?> cm</b>
			</div>
			<div class="product-text-container">
				<b>In stock: <?php echo $stock; ?></b>
			</div>
			<div class="product-text-container">
				<b>Price: <?php echo $price; ?> SEK</b>
			</div>
			<div class="product-text-container">
				<b>Rating: <?php echo(round($avgrating)); ?> of 5</b>
			</div>
			<div class="product-text-description-container">
				<b><?php echo $description; ?></b>
			</div>
		</div>
	<?php } ?> <!-- ----------------END of create_product() -->

	<div class="product-box">
		<?php
			$query = "SELECT ProductID, ProductName, ProductPrice, ProductSize, ProductStock, ProductDescription, ProductImage FROM Product";
			$result2 = mysqli_query(connectionToDB(), $query);

			// if there are any products in the database
			if ($result2->num_rows > 0) {
		    	while ($row = $result2->fetch_assoc()) {

		    		// create product and display them in the product box
					create_product($row['ProductID'], $row['ProductImage'], $row['ProductName'], $row['ProductSize'], $row['ProductStock'], $row['ProductPrice'], $row['ProductDescription']);
				}
			}
			mysqli_close(connectionToDB());
		?>
	</div>

	<?php
	// check if someone is logged in
	if(isset($_SESSION['user'])!="") {
		$user = $_SESSION['user'];
		$admin = mysqli_query(connectionToDB(), "SELECT UserAdmin FROM User WHERE UserPSN='$user'");
		$Row = mysqli_fetch_assoc($admin);

		// check if logged in user is an admin
		if ($Row['UserAdmin'] == 1) {  ?>
			<!-- display admin buttons for adding, editing and removing products -->
			<div class="admin-product-settings">
				<button onclick="document.getElementById('id01').style.display='block'" class="button-admin" name="btn-add-product" style="background-color: #4CAF50">Add product</button>
				<button onclick="document.getElementById('id02').style.display='block'" class="button-admin" name="btn-change-product" style="background-color: #008CBA">Change product</button>
				<button onclick="document.getElementById('id03').style.display='block'" class="button-admin" name="btn-remove-product" style="background-color: #f44336">Remove product</button>
			</div>
			<?php
		}
		unset($admin);
	} ?>

	<!-- add product modal, takes inputs for adding a product -->
	<div id="id01" class="modal">
		<form method="post" class="modal-content" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" accept-charset="ISO-8859-1" autocomplete="off">
	    	<div class="container">
	      		<label><b>Name:</b></label>
	      		<input type="text" placeholder="Name of the product" name="ProductName" value="<?php echo $name; ?>" required>

	      		<label><b>Size:</b></label>
	      		<input type="text" placeholder="Size of the product(ex. 10x10x10)" name="ProductSize" value="<?php echo $size; ?>" required>

	      		<label><b>In stock:</b></label>
	      		<input type="text" placeholder="Amount of products in stock" name="ProductStock" value="<?php echo $stock; ?>" required>

	      		<label><b>Price:</b></label>
	      		<input type="text" placeholder="Product price" name="ProductPrice" value="<?php echo $price; ?>" required>

	      		<label><b>Description:</b></label>
	      		<input type="text" placeholder="Product description (max 150 letters)" name="ProductDescription" value="<?php echo $description; ?>" required>

	      		<label><b>Image link:</b></label>
	      		<input type="text" placeholder="Image address of the product" name="ProductImage" value="<?php echo $image; ?>" required>

	        	<!-- add product button -->
	      		<button type="submit" name="btn-add" class="addproductbtn" >Add product</button>

	      		<!-- close the add product modal -->
				<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
	    	</div>
	  	</form>
	</div>

	<!-- change product modal, takes inputs for changing a product -->
	<div id="id02" class="modal">
		<form method="post" class="modal-content" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" accept-charset="ISO-8859-1" autocomplete="off">
	    	<div class="container">
	      		<label><b>Current name:</b></label>
				<input type="text" placeholder="Enter current product name" name="ProductCurrentName" value="<?php echo $name; ?>" required>
	      		<label><b>New name:</b></label>
	      		<input type="text" placeholder="Enter new name of product" name="ProductName" value="<?php echo $name; ?>" required>

	      		<label><b>Size:</b></label>
	      		<input type="text" placeholder="Size of the product(ex. 10x10x10)" name="ProductSize" value="<?php echo $size; ?>" required>

	      		<label><b>Amount in stock:</b></label>
	      		<input type="text" placeholder="Amount of products in stock" name="ProductStock" value="<?php echo $stock; ?>" required>

	      		<label><b>Price:</b></label>
	      		<input type="text" placeholder="Product price" name="ProductPrice" value="<?php echo $price; ?>" required>

	      		<label><b>Description:</b></label>
	      		<input type="text" placeholder="Product description (max 150 letters)" name="ProductDescription" value="<?php echo $description; ?>" required>

	      		<label><b>Image link:</b></label>
	      		<input type="text" placeholder="Images address of the product" name="ProductImage" value="<?php echo $image; ?>" required>

	        	<!-- change product button -->
	      		<button type="submit" name="btn-change" class="addproductbtn" style="background-color: #008CBA">Update product</button>

	      		<!-- closes the change product modal -->
				<button type="button" onclick="document.getElementById('id02').style.display='none'" class="cancelbtn">Cancel</button>
	    	</div>
	  	</form>
	</div>

	<!-- remove product modal, takes input for removing a product -->
	<div id="id03" class="modal">
		<form method="post" class="modal-content" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" accept-charset="ISO-8859-1" autocomplete="off">
	    	<div class="container">
	      		<label><b>Name:</b></label>
				<input type="text" placeholder="Enter the name of the product" name="ProductName" value="<?php echo $name; ?>" required>

	        		<!-- remove product button -->
	      		<button type="submit" name="btn-remove" class="addproductbtn" style="background-color: gray">Remove product</button>

	      			<!-- close remove product modal -->
				<button type="button" onclick="document.getElementById('id03').style.display='none'" class="cancelbtn">Cancel</button>
	    	</div>
	  	</form>
	</div>
</body>
<!-- script for closing modal on click outside of modal -->
<script>
	// Get the modal
	var modal = document.getElementById('id01');
	var modal2 = document.getElementById('id02');
	var modal3 = document.getElementById('id03');

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	    if (event.target == modal || event.target == modal2 || event.target == modal3) {
	        modal.style.display = "none";
			modal2.style.display = "none";
			modal3.style.display = "none";
	    }
	}
</script>
</html>
<?php ob_end_flush(); ?>
