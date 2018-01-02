<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	include_once("navigation.php");
?>

<!DOCTYPE html>
<html>
<head>
	<title> Box123 </title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/styling.css">
</head>

<body>
	<div class="slideshow-container" >
	    <div class="images">
	      <img src="http://18.195.185.45/box123/images/imagesv2/brown.png" alt="Sorry! Image not found!"style="width:100%">
	    </div>

	    <div class="images">
	      <img src="http://18.195.185.45/box123/images/imagesv2/red.png" style="width:100%">
	    </div>

	    <div class="images">
	      <img src="http://18.195.185.45/box123/images/imagesv2/green.png" style="width:100%">
	    </div>

	    <div class="images">
	      <img src="http://18.195.185.45/box123/images/imagesv2/blue.png" style="width:100%">
	    </div>
	</div> <!--END slideshow-container-->
	<br>

	<div style="text-align:center">
		<span class="dot"></span>
	    <span class="dot"></span>
	    <span class="dot"></span>
	    <span class="dot"></span>
	</div>

<!-- script for box slideshow -->
<script>
 	var slideIndex = 0;
	slideshow();

	function slideshow() {
		var i;
  		var slides = document.getElementsByClassName("images");
      	var dots = document.getElementsByClassName("dot");
      	for (i = 0; i < slides.length; i++) {
        	slides[i].style.display = "none";
      	}
      	slideIndex++;
      	if (slideIndex> slides.length) {slideIndex = 1}
      	for (i = 0; i < dots.length; i++) {
          	dots[i].className = dots[i].className.replace(" active", "");
      	}
      	slides[slideIndex-1].style.display = "block";
      	dots[slideIndex-1].className += " active";
      	setTimeout(slideshow, 2000); // Change image every 2 seconds
  	}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
