<?php
require "connection.php";

$user_name = $_POST['username'];
$user_pass = $_POST['password'];

$sql_query="select username from login_table where username='$user_name' and password='$user_pass';";
$result= mysqli_query($con,$sql_query);
$row=mysqli_fetch_array($result);
$data=$row[0];
if($data)
{

	echo"<html>
	<body style=\"background-image: url(./img/bck1.png);background-size: 100% 100%; background-repeat: no-repeat;\">
	
	<form action=\"map_generation.php\">
		<div style=\"background-color:rgba(190,221,243,0.3); margin-top: 5%;border-radius:8px;border-color:black;margin-left: 34%;box-shadow:15px 15px 8px black;width: 30%;height: 75%;text-align: center;\">
			<fieldset style=\"background-color:rgba(205,205,205,0);color:rgb(0,0,0);display:inline-block;font-family: verdana;border: none;\">
			<h2 style=\"color:white;\">Cerfer</h2>
			<h4 style=\"color:white;\">Monitor your networks with ease.</h4>
			
			<h3 style=\"color:white;\">Welcome ".$user_name."</h3>
			<img src=\"./img/pp1.png\" width=\"200px\" height=\"200px\"><br>

			<input style=\" shadow: 15px 15px 5px blue;border-color:rgba(8,28,43,1);color: white; width: 250px; padding:10px;border-radius: 5px;background-color: rgba(8,28,43,1);\" type=\"submit\" id=\"button1\" value=\"Show Map\"/>
		</div>
	</form>
	</body>
</html>
	";
}

else 
{

	echo"<html>
	<body style=\"background-image: url(./img/bck1.png);background-size: 100% 100%; background-repeat: no-repeat;\">
	
	<form action=\"index.php\">
		<div style=\"background-color:rgba(190,221,243,0.3); margin-top: 5%;border-radius:8px;border-color:black;margin-left: 34%;box-shadow:15px 15px 8px black;width: 30%;height: 75%;text-align: center;\">
			<fieldset style=\"background-color:rgba(205,205,205,0);color:rgb(0,0,0);display:inline-block;font-family: verdana;border: none;\">
			<h2 style=\"color:white;\">Cerfer</h2>
			<h4 style=\"color:white;\">Monitor your networks with ease.</h4>
			
			<h3 style=\"color:white;\">Login Unsuccessful.</h3>
			<img src=\"./img/pp1.png\" width=\"200px\" height=\"200px\"><br>

			<input style=\" shadow: 15px 15px 5px blue;border-color:rgba(8,28,43,1);color: white; width: 250px; padding:10px;border-radius: 5px;background-color: rgba(8,28,43,1);\" type=\"submit\" id=\"button1\" value=\"Go Back\"/>
		</div>
	</form>
	</body>
</html>
	";

	echo"login unsuccessful";

}

?>