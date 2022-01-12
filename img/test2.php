<html>
<head>
	<style type="text/css">
		.imgpc{

			width: 70px;
			height: 70px;
		}

		.imgpc:hover .dropdown-content {
    		display: block;
		}
		
		.router:hover .dropdown-content {
    		display: inline-block;
		}
		
		
		.router {
			padding-left: 35px;
    		position: relative;
    		display: inline-block;
    		max-width: 150px;
		}

		.dropdown-content a {
    		color: white;
    		padding: 12px 16px;
    		text-decoration: none;
    		display: block;
		}
		.dropdown-content {
		    display: none;
		    position: absolute;
			font-color:white;    

			background-color:rgba(255,255,255,0.5); 
			border-radius:8px;
			border-color:black;
    
		    min-width: 60px;
		    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    		z-index: 1;
		}

	</style>


</head>
<body style="background-image: url(digital-solutions.jpg);">


<?php
$i = 0;

while($i<5){
	echo  "
<div class=\"router\">
<img class=\"imgpc\" src=\"pcicon.png\"><br>
<div class=\"dropdown-content\">";
$j=0;
while($j<3){
	echo "
		<label>Interface Ga".$j."</label>
		<br>
		<label>IP Address:</label>
		<input type=\"text\" placeholder=\"192.168.4.1\"/>
		<br>";
			$j++;
		}
		echo "
		</div>
		</div>
	";
	$i++;
	}
?>
</body>
</html>