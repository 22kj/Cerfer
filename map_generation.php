<?php
require 'connection.php';
//format: int_id ip_add mask int_name status      													   contains all interface of a router 
//format: switch_id int_id(connected to which interface) router_id(interface of which router)          contains all switches connected to the router			  //format: source_int source_router dest_int dest_router         									     contains all routers connected to the router 
if (isset($_GET['js_var'])) 
{
	$ipaddress='';
	$interface='';
	$querytype='';
	$php_var = $_GET['js_var'];
	//echo "<br>";
	//echo "<br>";
	//echo "<br>";
	//echo $php_var;
	$i=0;
	while($php_var[$i]!='-')
	{
		if($php_var[$i]=='_')
		{
			break;
		}
		$ipaddress=$ipaddress.''.$php_var[$i];
		$i++;
		//for($i=0;$i<$php_var.length;$i++)
	}
	$i++;

	 while($php_var[$i]!='-')
	 {
	 	$interface=$interface.''.$php_var[$i];
	 	$i++;
	}
	$i++;
	while($php_var[$i]!='*')
	{
		$querytype=$querytype.''.$php_var[$i];
		$i++;
	}
	//echo $querytype;
	if($querytype=='rip')
	{
		$query4="update interface_table set ip_address='".$ipaddress."' where interface_id=".$interface.";";
		//echo 'rip';
	}
	else if($querytype=='rmk')
	{
		$query4="update interface_table set mask='".$ipaddress."' where interface_id=".$interface.";";
		//echo 'rmk';
	}
	else if($querytype=='pcip')
	{
		$query4="update pc_table set ip_address='".$ipaddress."' where pc_id=".$interface.";";
		//echo 'pcip';
	}
	elseif($querytype=='pcmk')
	{
		$query4="update pc_table set mask='".$ipaddress."' where interface_id=".$interface.";";
		//echo 'pcmk';
	}

	if(mysqli_query($con,$query4))
	{
		 echo '<script type="text/javascript">',
     'alert("Successful!");',
     '</script>';
	}
	else
	{
		echo '<script type="text/javascript">',
     'alert("Could no connect to the database!");',
     '</script>';
	}
	 /*echo $ipaddress;
	 echo '<br>';
	 echo $interface;*/

}		
$query="select router_id, region_id,Latitude,Longitude from router_table;";
$result = mysqli_query($con,$query);
$n = mysqli_num_rows($result);
$r_ind=0;
$int_ind=0;
$pc_ind=0;
$sw_ind=0;
$pc=array(array());
$switch=array(array());
$router=array(array());
while($row = mysqli_fetch_array($result))
{
	$router_id[$r_ind]=$row['router_id'];
	$region_id[$r_ind]=$row['region_id'];
	$Router_Latitude[$r_ind]=$row['Latitude'];
	$Router_Longitude[$r_ind]=$row['Longitude'];
	//echo $router_id[$i];
	//echo $region_id[$i];
	$query1 = "select ip_address, mask, interface_id, con_interface, interface_name, line_status, line_protocol, routing_protocol from interface_table where router_id = '$router_id[$r_ind]';";
	$result1 = mysqli_query($con,$query1);
	while($row1 = mysqli_fetch_array($result1))
	{
		$int_ip_address[$int_ind]=$row1['ip_address'];
		$interface_id[$int_ind]=$row1['interface_id'];
		$interface_name[$int_ind]=$row1['interface_name'];
		$int_mask[$int_ind]=$row1['mask'];
		$status[$int_ind]=$row1['line_status'];
		$con_interface[$int_ind]=$row1['con_interface'];
		$l_protocol[$int_ind]=$row1['line_protocol'];
		$r_protocol[$int_ind]=$row1['routing_protocol'];
		$router[$int_ind][0]=$router_id[$r_ind];					//router id of a particular interface
		$router[$int_ind][1]=$region_id[$r_ind];					//region id of that particular router id
		$router[$int_ind][2]=$interface_id[$int_ind];				//interface id on that particular router id
		$router[$int_ind][3]=$interface_name[$int_ind];				//interface name of that interface id
		$router[$int_ind][4]=$int_ip_address[$int_ind];				//ip address of that interface id
		$router[$int_ind][5]=$int_mask[$int_ind];					//mask of the ip address
		$router[$int_ind][6]=$status[$int_ind];						//status of the link (up/down)
		$router[$int_ind][7]=0;										//no of switches connected to the router (calculated later)
		$router[$int_ind][8]=0;										//no. of router connected to that router
		$router[$int_ind][9]=$con_interface[$int_ind];				//connected to that interface of another router
		$router[$int_ind][10]=-1;									//connected to that router id
		if($con_interface[$int_ind]!=-1)
		{
			$query2="select router_id from interface_table where interface_id= '$con_interface[$int_ind]';";
			$result2=mysqli_query($con,$query2);
			$row2=mysqli_fetch_array($result2);
			$router[$int_ind][10]=$row2['router_id'];				//calculating the connected router id
		}
		//echo $router[$int_ind][10];
		$query2 = "select switch_id, ip_address, interface_id,latitude,longitude from switch_table where interface_id='$interface_id[$int_ind]';";
		$result2=mysqli_query($con,$query2);
		$row2=mysqli_fetch_array($result2);
		if($row2)
		{
			$switch_id[$sw_ind]=$row2['switch_id'];
			$sw_ip_address[$sw_ind]=$row2['ip_address'];
			$sw_int[$sw_ind]=$row2['interface_id'];
			$Switch_Latitude[$sw_ind]=$row2['latitude'];			//latitude of switch
			$Switch_Longitude[$sw_ind]=$row2['longitude'];			//longitude of switch
			$switch[$sw_ind][0]=$switch_id[$sw_ind];				//switch id
			$switch[$sw_ind][1]=$router_id[$r_ind];					//connected to router id
			$switch[$sw_ind][2]=$sw_int[$sw_ind];					//connected to interface_id of that particular router id
			$switch[$sw_ind][3]=$interface_name[$sw_ind];			//connected to interface of that particular router id
			$switch[$sw_ind][4]=$sw_ip_address[$sw_ind];			//switch ip address
			$switch[$sw_ind][5]=0;									//no of pc connected to the switch (calculated later)	
			//echo $switch_id[$j];
			$query3 = "select pc_id, ip_address, mask,latitude,longitude,status,os from pc_table where router_id = '$router_id[$r_ind]' and switch_id='$switch_id[$sw_ind]';";
			$result3 = mysqli_query($con,$query3);
			while($row3 = mysqli_fetch_array($result3))
			{
				$pc_id[$pc_ind]=$row3['pc_id'];
				$pc_ip_address[$pc_ind]=$row3['ip_address'];
				$pc_mask[$pc_ind]=$row3['mask'];
				$Pc_Latitude[$pc_ind]=$row3['latitude'];
				$Pc_Longitude[$pc_ind]=$row3['longitude'];
				$pc_status[$pc_ind]=$row3['status'];
				$pc_os[$pc_ind]=$row3['os'];
				//echo $pc_id[$k];
				$pc[$pc_ind][0]=$pc_id[$pc_ind];						//pc_id
				$pc[$pc_ind][1]=$switch_id[$sw_ind];					//connected to switch id
	 			$pc[$pc_ind][2]=$router_id[$r_ind];						//connected to router id
				$pc[$pc_ind][3]=$pc_ip_address[$pc_ind];				//pc ip address
				$pc[$pc_ind][4]=$pc_mask[$pc_ind];						//pc ip address mask
				$pc[$pc_ind][5]=$Pc_Latitude[$pc_ind];					//pc latitude location
				$pc[$pc_ind][6]=$Pc_Longitude[$pc_ind];					//pc longitude location
				$pc[$pc_ind][7]=$pc_status[$pc_ind];					//pc status(up/down)
				$pc[$pc_ind][8]=$pc_os[$pc_ind];						//pc operating system
				$pc_ind++;
			}
			//echo "<br>";
			$sw_ind++;
		}
		$int_ind++;
	}
	$r_ind++;
}
$count=0;
for($i=0;$i<$sw_ind;$i++)            					//calculating pc count for each switch
{
	for($l=0;$l<$pc_ind;$l++)            
	{
		if($pc[$l][1]==$i)
		$count++;
	}
	if($count!=0)
	$switch[$i][5]=$count;
	$count=0;
}
$count=0;
for($i=0;$i<$sw_ind;$i++)            					//calculating switch count for each router interface
{
	for($l=0;$l<$int_ind;$l++)            
	{
		if($switch[$i][2]==$router[$l][2])
		{
			$count++;
			$router[$l][7]=$count;		
		}
	}
	$count=0;
}
$count=0;												//calculating the router count connected to each router
for($i=0;$i<$r_ind;$i++)
{
	for($j=0;$j<$int_ind;$j++)
	{
		if($router[$j][0]==$i && $router[$j][9]!=-1)
		{
			$count++;
			$router[$i][8]=$count;
		}
	}
	//echo $router[$i][8];
	$count=0;
}
// for($i=0;$i<$int_ind;$i++)							//contains all router data
// {
// 	echo $router[$i][0]." <br>";
// 	echo $router[$i][1]." <br>";
// 	echo $router[$i][2]." <br>";
// 	echo $router[$i][3]." <br>";
// 	echo $router[$i][4]." <br>";
// 	echo $router[$i][5]." <br>";
// 	echo $router[$i][6]." <br>";
// 	echo $router[$i][7]." <br>";
// 	echo $router[$i][8]." <br>";
// 	echo $router[$i][9]." <br>";
// 	echo $router[$i][10]." <br>";
//	echo $l_protocol[$i]."<br>";
//	echo $r_protocol[$i]."<br>";
//	echo"<br>";
//}
// for($i=0;$i<$sw_ind;$i++)            			//contains all switch data
// {
// 	echo $switch[$i][0]." <br>";
// 	echo $switch[$i][1]." <br>";
// 	echo $switch[$i][2]." <br>";
// 	echo $switch[$i][3]." <br>";
// 	echo $switch[$i][4]." <br>";
// 	echo $switch[$i][5]." <br>";
// 	echo "<br>";
// }
// for($i=0;$i<$pc_ind;$i++)            			//contains all pc data
// 	{
// 		echo $pc[$i][0]." <br>";
// 		echo $pc[$i][1]." <br>";						
// 		echo $pc[$i][2]." <br>";
// 		echo $pc[$i][3]." <br>";
// 		echo $pc[$i][4]." <br>";
// 	}
?>

<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
<style type="text/css">
	line{
		z-index: 1;
		position: absolute;
		stroke-width: 1.5px;
		fill:rgba(0,0,0,0.5);
		stroke:black;
	}
	svg{
		position: absolute;
		height: 100%;
		width: 100%;
	}
	line:hover{
		stroke:blue;
	}
	label{
		color: white;
	}
	.button1{

		color:white;
		width: 95%;
		border-radius: 5px;
		margin: 1px;
		background-color: rgba(0,60,60,1);
		border:solid 0px;
		z-index: 4;
		padding: 5px;
	}
	.text1{
		
		margin: 1.5px;
		border-radius: 5px;
		z-index: 4;
		background-color: rgba(235, 255, 254,1);
		
	}
	.label1{
		font-size: 	13px;
	}
	.pulse {
  	  
	  	z-index: 2;
		display: block;
		width: 22px;
		height: 22px;
		border-radius: 50%;
		background:  rgba(0,255,255,1);
		cursor: pointer;
		box-shadow: 0 0 0 rgba(0,255,255, 0.4);
	 	animation: pulse 1s infinite;
	}
	
	#dropdownNPM1{
		background-color: rgba(0,60,60,0.7);
		border:2px solid rgba(0,255,255,0.8);
		border-radius: 5px;
		display: none;
		overflow-y: scroll;
	}
	.closebutton{
		font-size: 20px;
		font-family: Century Gothic;
		background-color: rgba(0,60,60,1);
		color: white;
		border-radius: 15px;
		display: block;
		border-color: rgba(0,0,0,0);
		right: 0px;
		top: 2px;
		position: absolute;
		z-index: 3;
	}

	.closebutton1{
		
		font-family: Century Gothic;
		background-color: rgba(0,60,60,0.8);
		color: white;
		border-radius: 5px;
		display: block;
		border-color: rgba(0,160,160,0.8);
		position: absolute;
		z-index: 3;
		margin: 5px;
		padding: 0.3em;
		padding-left: 1em;
		padding-right: 1em;
		margin-left: 35%;
	}
	.labelViewDet{
		position: absolute;
		right: 20px;
	}
	#labelSpAlert{

	    position: absolute;
		color:white;    
		text-align: center;
		background-color: rgba(0,60,60,0.9);
		border:2px solid rgba(0,255,255,0.8);
		border-radius: 5px;
		display: none;
		left: 45%;
		top: 40%;
		padding: 1em;
		height:8%;
		align-content: center;
	}

	div[class^="dropdownNPM-content"]{
		background-color: rgba(0,245,255,0.1);
		margin:5px;
		padding:2px;
		width: 95%;
		color: rgba(0,200,200,0.8);
		line-height: 25px;
	}


	@-webkit-keyframes pulse {
		0% {
	        -webkit-box-shadow: 0 0 0 0 rgba(0,255,255, 0.4);
		}
	 	70% {
	        -webkit-box-shadow: 0 0 0 10px rgba(0,255,255, 0);
	    }
	    100% {
	        -webkit-box-shadow: 0 0 0 0 rgba(0,255,255, 0);
	    }
	}
	@keyframes pulse {
	    0% {
	      -moz-box-shadow: 0 0 0 0 rgba(0,255,255, 0.4);
	      box-shadow: 0 0 0 0 rgba(0,255,255, 0.4);
	    }
	    70% {
	        -moz-box-shadow: 0 0 0 10px rgba(0,255,255, 0);
	        box-shadow: 0 0 0 10px rgba(0,255,255, 0);
	    }
	    100% {
	        -moz-box-shadow: 0 0 0 0 rgba(0,255,255, 0);
	        box-shadow: 0 0 0 0 rgba(0,255,255, 0);
	    }
	}
	
	#dropdownNPM1::-webkit-scrollbar-track
	{
		-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
		background-color: rgba(0,60,60,1);
	}

	#dropdownNPM1::-webkit-scrollbar
	{
		width: 6px;
		background-color: #F5F5F5;
	}

	#dropdownNPM1::-webkit-scrollbar-thumb
	{

		border-radius: 5px;
		background-color: #000000;
	}
	.switch{
		position: relative;
		z-index: 2;
		display: inline-block;
		}
	.classpc{
		position: relative;
		z-index: 2;
		display: inline-block;
		margin-right: 5px;
	}
	.imgpc{
		z-index: 2;
		position: relative;
		width: 70px;
		height: 70px;
	}
	.imgrouter{
		z-index: 2;
		margin-top: -30px;
		position: relative;	
		width: 70px;
		height: 70px;
	}
	.imgswitch{
		z-index: 2;
		width: 70px;	
		height: 30px;
		position: relative;
		margin-right: 30px;	
	}
	.skillbar{

		background-color: rgba(0,200,200,0.9);
		height: 8px;
		margin: 1px;
	}
	.skillcontainer{
		background-color: rgba(0,0,0,0.5);
		border: 1px solid rgba(0,255,255,0.5);
		height: 10px;
	}
	.pulse:hover .dropdown-content {
   		display: block;
	}
	.pc:hover .dropdown-content{
		display: block;
	}
	.classpc:hover .dropdown-content {
		display: block;
	}
	.router:hover .dropdown-content {
   		display: inline-block;
	}
	.out_router{
		display: inline-block;
   		min-height: 40%;
   		z-index: -2;
	}
	.router {
		margin: 50px;
   		position: relative;
   		display: inline-block;
		border-radius: 35px;
		z-index: 3;
	}


	.dropdown-content a {
   		color: white;
   		padding: 12px 16px;
   		text-decoration: none;
   		display: block;
		z-index: 3;
	}
	.dropdown-content {
	    display: none;
	    position: absolute;
		font-color:white;    
		text-align: center;
		background-color:rgba(0,200,200,0.5); 
		border-radius:8px;
		border-color:black;   
	    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
   		z-index: 3;
	}
</style>

<style type="text/css">
		#map-canvas {
		width: 100%;
		height: 100%;
		}

</style>

<script type="text/javascript" src="./Scripts/d3.v2.js"></script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCJQEXFigi7hNOxif0g_QozQ_t8ZNcE1nY"></script>

<script type="text/javascript">

function ref() {
    location.reload(true);
}
	function close1()
{
	document.getElementById("dropdownNPM1").style.display = "none";
    // Removes an element from the document
    var element = document.getElementById("dropdownNPM");
    element.parentNode.removeChild(element);
}
	function close2()
{
    var element = document.getElementById("labelSpAlert");
    element.parentNode.removeChild(element);
}
</script>

<script type="text/javascript">
	function Viewdetails(id,type)
{
	var router = <?php echo json_encode($router) ?>;
	var int_ind=<?php echo json_encode($int_ind)?>;
	var pc = <?php echo json_encode($pc)?>;
	var pc_ind =<?php echo json_encode($pc_ind)?>;
	var l_p= <?php echo json_encode($l_protocol)?>;
	var r_p= <?php echo json_encode($r_protocol)?>;
	this.type = type;
	this.id = id; 
	if(type==1)
	{
		//alert("Router id is: "+id);
		//	var divid;
		//divid=document.getElementById('View_details_router');
		//element=document.createElement("input");
		//element.type="text";
		//divid.appendChild(element);
		//attr=document.createAttribute("id");
		//attr.value="text1";
		//element.setAttributeNode(attr);
			if(document.getElementById('dropdownNPM')){
			var element = document.getElementById("dropdownNPM");
	    	element.parentNode.removeChild(element);
		}
		var p = document.getElementById('dropdownNPM1');
		var newElement = document.createElement('div');
		newElement.setAttribute('id','dropdownNPM');
		p.appendChild(newElement);

		var p = document.getElementById('dropdownNPM');
		var newElement = document.createElement('div');
		newElement.setAttribute('class','dropdownNPM-content');
		newElement.innerHTML='Device ID: ';
		p.appendChild(newElement);
		p=newElement;
		var newElement = document.createElement('label');
		newElement.setAttribute('id','ro_id');
		newElement.setAttribute('class','labelViewDet');
		p.appendChild(newElement);
		document.getElementById("ro_id").innerHTML = id;
		//element=document.createElement('br');
		//divid.appendChild(element);
		document.getElementById("dropdownNPM1").style.display = "block";
		for(var m=0;m<int_ind;m++)
		{
			if(router[m][0]==id)
			{		
	    		var p = document.getElementById('dropdownNPM');
				var newElement = document.createElement('div');
	    		newElement.setAttribute('id','interface_details');
	    		//before1=document.getElementById('close_router');
	    		p.appendChild(newElement);
	    		//p = document.getElementById('interface_details');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Interface IP: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = router[m][4];

	    		p.appendChild(newElement);
				
				newElement=document.createElement('br');
	    		p.appendChild(newElement);    		

	    		p = document.getElementById('dropdownNPM');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Line Status: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = router[m][6];
	    		p.appendChild(newElement);
	    		newElement=document.createElement('br');
	    		p.appendChild(newElement);

	    		p = document.getElementById('dropdownNPM');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Line Protocol: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = l_p[m];
	    		p.appendChild(newElement);
	    		newElement=document.createElement('br');
	    		p.appendChild(newElement);

	    		p = document.getElementById('dropdownNPM');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Routing Protocol: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = r_p[m];
	    		p.appendChild(newElement);

	    		p = document.getElementById('dropdownNPM');
	    		newElement=document.createElement('br');
	    		p.appendChild(newElement);
			}
		}
			p = document.getElementById('dropdownNPM');
			newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Memory Utilization: ';
    		p.appendChild(newElement);
    		p=newElement;
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		var value1= Math.floor((Math.random() * 100) + 1);
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
			p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
    		newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
			newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Router Load: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
			p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
    		newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Packet Loss: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
			newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Average Response Time: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+' ms';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
			
			newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Bandwidth Utilization: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		//newElement.innerHTML = 0;
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   


	}
	else if(type==0)
	{

		if(document.getElementById('dropdownNPM')){
			var element = document.getElementById("dropdownNPM");
	    	element.parentNode.removeChild(element);
		}
		var p = document.getElementById('dropdownNPM1');
		var newElement = document.createElement('div');
		newElement.setAttribute('id','dropdownNPM');
		p.appendChild(newElement);

		var p = document.getElementById('dropdownNPM');
		var newElement = document.createElement('div');
		newElement.setAttribute('class','dropdownNPM-content');
		newElement.innerHTML='Device ID: ';
		p.appendChild(newElement);
		p=newElement;
		var newElement = document.createElement('label');
		newElement.setAttribute('id','ro_id');
		newElement.setAttribute('class','labelViewDet');
		p.appendChild(newElement);
		document.getElementById("ro_id").innerHTML = id;
		//element=document.createElement('br');
		//divid.appendChild(element);
		document.getElementById("dropdownNPM1").style.display = "block";
		for(var m=0;m<pc_ind;m++)
		{
			if(pc[m][0]==id)
			{		
	    		var p = document.getElementById('dropdownNPM');
				var newElement = document.createElement('div');
	    		newElement.setAttribute('id','interface_details');
	    		//before1=document.getElementById('close_router');
	    		p.appendChild(newElement);
	    		//p = document.getElementById('interface_details');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Interface IP: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = pc[m][3];

	    		p.appendChild(newElement);
				
				newElement=document.createElement('br');
	    		p.appendChild(newElement);    		

	    		p = document.getElementById('dropdownNPM');
	    		newElement = document.createElement('div');
	    		newElement.setAttribute('class','dropdownNPM-content');
	    		newElement.innerHTML = 'Line Status: ';
	    		p.appendChild(newElement);
	    		p=newElement;
	    		newElement = document.createElement('label');
	    		newElement.setAttribute('class','labelViewDet');
	    		newElement.innerHTML = pc[m][7];
	    		p.appendChild(newElement);
	    		newElement=document.createElement('br');
	    		p.appendChild(newElement);

	    		p = document.getElementById('dropdownNPM');
	    		newElement=document.createElement('br');
	    		p.appendChild(newElement);
			}
		}
			p = document.getElementById('dropdownNPM');
			newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Memory Utilization: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
			p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
    		newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
			newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'CPU Load: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
			p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
    		newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Packet Loss: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
			newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Average Response Time: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+' ms';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   
			
			newElement=document.createElement('br');
    		p.appendChild(newElement);

    		p = document.getElementById('dropdownNPM');
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','dropdownNPM-content');
    		newElement.innerHTML = 'Bandwidth Utilization: ';
    		p.appendChild(newElement);
    		p=newElement;
    		value1=Math.floor((Math.random() * 100) + 1);
			newElement = document.createElement('label');
    		newElement.setAttribute('class','labelViewDet');
    		newElement.innerHTML = value1+'%';
    		p.appendChild(newElement);
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillcontainer');
    		//newElement.innerHTML = 0;
    		p.appendChild(newElement);   
    		p=newElement; 		    		
    		newElement = document.createElement('div');
    		newElement.setAttribute('class','skillbar');
    		newElement.style.width= value1+'%';
    		p.appendChild(newElement);   


	}

	else
	{
		alert("PC id is: "+id);
	//	var divid;
	//divid=document.getElementById('dropdownNPM');
	//element=document.createElement("input");
	//element.type="text";
	//divid.appendChild(element);
	//attr=document.createAttribute("id");
	//attr.value="text1";
	//element.setAttributeNode(attr);
	//document.getElementById("pc_id").innerHTML = id;
	//element=document.createElement('br');
	//divid.appendChild(element);
	//document.getElementById("dropdownNPM").style.display = "block";
	}
	//window.location = "./View_details.php";
	//var element;
	//var attr;
}
</script>

<script type="text/javascript">
//function to change ip address from gui placeholder
//verification of proper input also done here

function spalert(val){


	//	var p = document.getElementByType('body');
		var newElement = document.createElement('div');
		newElement.setAttribute('id','labelSpAlert');
		newElement.innerHTML= val;
		document.body.appendChild(newElement);
		document.getElementById("labelSpAlert").style.display = "block";
		
		newElement2=document.createElement('br');
    	newElement.appendChild(newElement2);

		var newElement1 = document.createElement('button');
		newElement1.setAttribute('class','closebutton1');
		newElement1.setAttribute('onClick','close2()');
		newElement1.innerHTML= 'Ok';
		newElement.appendChild(newElement1);
		

	//	alert(val);

}

function ipchange(i,type,adtype)
{
	 	var router = <?php echo json_encode($router) ?>;
	 	var pc = <?php echo json_encode($pc)?>;
	 	var pc_ind =<?php echo json_encode($pc_ind)?>;
	 	var name;
	 	var i;
	 	var ip_address;
	 	var pcflag=0;
	 	var routerflag=0;
	 	var oct1=0, k, count, temp='',mask1=0, octcount=0;
	 	this.i=i;
	 	this.type=type;
	 	this.adtype=adtype;
	 	//alert(i);
	 	if(type==1)
	 	{
	 	name = document.getElementById("r_ip"+i).value;
	 	//alert(name);
	 	ip_address=router[i-1][4];							//because we are using a loop starting from 0 and indexes in db are from 1
	 	mask=router[i-1][5];
	 	var j;
	 	for(j=0;j<mask.length;j++)
	 	{
	 		if(mask[j]=='0')
	 		{
	 			break;
	 		}
	 	}
	 	mask1=j;
	 	//alert(mask1);
	 	//alert(ip_address);
	 	var ans= name.match(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/);
	 	if(ans!=null)
	 	{
	 		//alert("valid ip till now :P");
	 		count=0;
	 	k=0;
	 	for(j=0;j<=name.length;j++)
	 	{
	 		//alert(count);
	 		//alert(j);
	 		//alert(name[j]);
	 		if(name[j]=='.')// && count>0)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;
	 			octcount++;
	 			if(mask1==4 && octcount==1 && (oct1>126 || oct1<0))
	 			{
	 					spalert('Please enter a proper class A address.');
	 					routerflag=1;
	 			}
	 			else if(mask1==8 && octcount==1 && (oct1<127 || oct1>191))
	 			{
	 					spalert('Please enter a proper class B address.');
	 					routerflag=1;
	 			}
	 			else if(mask1==12 && octcount==1 && (oct1<192 || oct1>223))
	 			{
	 					spalert('Please enter a proper class C address.');
	 					routerflag=1;
	 			}
	 		}
	 		temp=''+temp+name[j];
	 		count++;
	 		if(j==name.length)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;	
	 		}
	 	}
	 	}
	 	else 
	 	{
	 		spalert("This is not a valid ip address.");
	 		routerflag=1;	
	 	}
	 	if(routerflag==0)
	 	{
	 		window.location = "?js_var=" + name+"_"+i+"-"+adtype+"*";
	 		alert('this is executed');
	 		//$.post('map_gen_160418.php', {variable: name});
	 		//code to enter it into database////////////////////////////////////////////////////////
	 	}
	 }

	 else if(type==0)
	 {
	 	name = document.getElementById("p_ip"+i).value;
	 	//alert(name);
	 	//alert(i);
	 	for(var m=0;m<pc_ind;m++)
	 	{
	 		if(pc[m][0]==i)
	 			break;
	 	}
	 	ip_address=pc[m][3];							//because we are using a loop starting from 0 and indexes in db are from 1
	 	//alert(pc[m][3]);
	 	//alert(ip_address);
	 	mask=pc[m][4];
	 	var j;
	 	for(j=0;j<mask.length;j++)
	 	{
	 		if(mask[j]=='0')
	 		{
	 			break;
	 		}
	 	}
	 	mask1=j;
	 	//alert(mask1);
	 	//alert(ip_address);
	 	var ans= name.match(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/);
	 	if(ans!=null)
	 	{
	 		//alert("valid ip till now :P");
	 	count=0;
	 	k=0;
	 	for(j=0;j<=name.length;j++)
	 	{
	 		//alert(count);
	 		//alert(j);
	 		//alert(name[j]);
	 		if(name[j]=='.')// && count>0)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;
	 			octcount++;
	 			if(mask1==4 && octcount==1 && (oct1>126 || oct1<0))
	 			{
	 					spalert('Please enter a proper class A address.');
	 					pcflag=1;
	 			}
	 			else if(mask1==8 && octcount==1 && (oct1<127 || oct1>191))
	 			{
	 					spalert('Please enter a proper class B address.');
	 					pcflag=1;
	 			}
	 			else if(mask1==12 && octcount==1 && (oct1<192 || oct1>223))
	 			{
	 					spalert('Please enter a proper class C address.');
	 					pcflag=1;
	 			}
	 		}
	 		temp=''+temp+name[j];
	 		count++;
	 		if(j==name.length)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;	
	 		}
	 	}
	 	}
	 	else 
	 	{
	 		spalert("This is not a valid ip address.");	
	 		pcflag=1;
	 	}
	 	//alert('correct button pressed');
	 	//alert(i);
	 	if(pcflag==0)
	 	{
	 		//code for entering mask into database
	 		//alert(i);
	 		window.location = "?js_var=" + name+"_"+i+"-"+adtype+"*";
	 	}
	 }
		//alert(j);
		//if(j==8)
		//if(j==12)
}
</script>



<script type="text/javascript">
//function to change ip address from gui placeholder
//verification of proper input also done here
function maskchange(i,type,adtype)
{
	 var router = <?php echo json_encode($router) ?>;
	 var pc = <?php echo json_encode($pc)?>;
	 var pc_ind =<?php echo json_encode($pc_ind)?>;
	 var mask1, oct1,count, temp='',octcount=0,k;
	 var routerflag=0;
	 var pcflag=0;
	 this.i=i;
	 this.type=type;
	 this.adtype=adtype;
	 //alert(i);
	 if(type==1)
	 {
	 	var name=document.getElementById('r_mask'+i).value;
	 	//alert(name);
	 	ip_address=router[i-1][4];
	 	//alert(ip_address);
	 	mask=name;
	 	var j;
	 	var ans= name.match(/^(255)\.(255|0)\.(255|0)\.(255|0)$/);
	 	if(ans!=null)
	 	{
	 	for(j=0;j<mask.length;j++)
	 	{
	 		if(mask[j]=='0')
	 		{
	 			break;
	 		}
	 	}
	 	mask1=j;
	 	count=0;
	 	k=0;
	 	//alert(mask1);
	 	for(j=0;j<=name.length;j++)
	 	{
	 		//alert(count);
	 		//alert(j);
	 		//alert(name[j]);
	 		if(name[j]=='.')// && count>0)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;
	 			octcount++;
	 			if(mask1!=4 && octcount==1 && oct1<=126)
	 			{
	 					spalert('enter a proper class A network mask');
	 					routerflag=1;
	 			}
	 			else if(mask1!=8 && octcount==1 && oct1>=128 && oct1<=191)
	 			{
	 					spalert('enter a proper class B network mask');
	 					routerflag=1;
	 			}
	 			else if(mask1!=12 && octcount==1 && (oct1>=192 || oct1<=223))
	 			{
	 					spalert('enter a proper class C network mask');
	 					routerflag=1;
	 			}
	 		}
	 		temp=''+temp+ip_address[j];
	 		count++;
	 		if(j==name.length)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;	
	 		}
	 	}
	 }
	 else
	 {
	 	spalert('enter a valid mask');
	 	routerflag=1;
	 }

	 if(routerflag==0)
	 {
	 	window.location = "?js_var=" + name+"_"+i+"-"+adtype+"*";
	 }
	 	//alert(i);
	 }

	 else if(type==0)
	 {
	 	//alert(i);
	 		 	var name=document.getElementById('p_mask'+i).value;
	 	//alert(name);
	 	for(var m=0;m<pc_ind;m++)
	 	{
	 		if(pc[m][0]==i)
	 			break;
	 	}
	 	ip_address=pc[m][3];
	 	//alert(ip_address);
	 	mask=name;
	 	var j;
	 	var ans= name.match(/^(255)\.(255|0)\.(255|0)\.(255|0)$/);
	 	if(ans!=null)
	 	{
	 	for(j=0;j<mask.length;j++)
	 	{
	 		if(mask[j]=='0')
	 		{
	 			break;
	 		}
	 	}
	 	mask1=j;
	 	count=0;
	 	k=0;
	 	//alert(mask1);
	 	for(j=0;j<=name.length;j++)
	 	{
	 		//alert(count);
	 		//alert(j);
	 		//alert(name[j]);
	 		if(name[j]=='.')// && count>0)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;
	 			octcount++;
	 			if(mask1!=4 && octcount==1 && oct1<=126)
	 			{
	 					spalert('enter a proper class A network mask');
	 					pcflag=1;
	 			}
	 			else if(mask1!=8 && octcount==1 && oct1>=128 && oct1<=191)
	 			{
	 					spalert('enter a proper class B network mask');
	 					pcflag=1;
	 			}
	 			else if(mask1!=12 && octcount==1 && (oct1>=192 || oct1<=223))
	 			{
	 					spalert('enter a proper class C network mask');
	 					pcflag=1;
	 			}
	 		}
	 		temp=''+temp+ip_address[j];
	 		count++;
	 		if(j==name.length)
	 		{
	 			oct1=parseInt(temp);
	 			//alert(oct1);
	 			count=0;
	 			temp='';
	 			j++;	
	 		}
	 	}
	 }
	 else
	 {
	 	spalert('enter a valid mask');
	 	pcflag=1;
	 }
	 if(pcflag==0)
	 {
	 	window.location = "?js_var=" + name+"_"+i+"-"+adtype+"*";
	 }
	 }
		//alert(j);
		//if(j==8)
		//if(j==12)
}
</script>


<script type="text/javascript">
var maxZoomLevel=18;
	function initialize() {
		var myLatlng = new google.maps.LatLng(19.2871,72.8688);
		var mapOptions = 
		{
			zoom: 11,
			maxZoom: 18,
			minZoom: 3,
			center: myLatlng,
			disableDefaultUI: true,
			styles:[
  {
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#000409"
      }
    ]
  },
  {
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#00376e"
      }
    ]
  },
  {
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#000a14"
      }
    ]
  },
  {
    "featureType": "administrative.country",
    "elementType": "geometry.stroke",
    "stylers": [
      {
        "color": "#002f91"
      },
      {
        "weight": 0.5
      }
    ]
  },
  {
    "featureType": "administrative.land_parcel",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#667a9f"
      }
    ]
  },
  {
    "featureType": "administrative.province",
    "elementType": "geometry.stroke",
    "stylers": [
      {
        "color": "#002675"
      },
      {
        "weight": 0.5
      }
    ]
  },
  {
    "featureType": "landscape.man_made",
    "elementType": "geometry.stroke",
    "stylers": [
      {
        "color": "#000a14"
      }
    ]
  },
  {
    "featureType": "landscape.natural",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#000714"
      }
    ]
  },
  {
    "featureType": "poi",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#000f2d"
      }
    ]
  },
  {
    "featureType": "poi",
    "elementType": "labels.icon",
    "stylers": [
      {
        "visibility": "off"
      }
    ]
  },
  {
    "featureType": "poi",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#3a565c"
      }
    ]
  },
  {
    "featureType": "poi",
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#16213a"
      }
    ]
  },
  {
    "featureType": "poi.park",
    "elementType": "geometry.fill",
    "stylers": [
      {
        "color": "#000d28"
      }
    ]
  },
  {
    "featureType": "poi.park",
    "elementType": "labels.icon",
    "stylers": [
      {
        "visibility": "off"
      }
    ]
  },
  {
    "featureType": "poi.park",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  },
  {
    "featureType": "poi.park",
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#00070f"
      }
    ]
  },
  {
    "featureType": "road",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#002164"
      },
      {
        "weight": 0.5
      }
    ]
  },
  {
    "featureType": "road",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#0042c8"
      }
    ]
  },
  {
    "featureType": "road",
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#00070f"
      }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "geometry.stroke",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "labels.icon",
    "stylers": [
      {
        "visibility": "off"
      }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#0042ca"
      }
    ]
  },
  {
    "featureType": "road.highway",
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#00070f"
      }
    ]
  },
  {
    "featureType": "transit",
    "elementType": "labels.icon",
    "stylers": [
      {
        "visibility": "off"
      }
    ]
  },
  {
    "featureType": "transit",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#0042ca"
      }
    ]
  },
  {
    "featureType": "transit",
    "elementType": "labels.text.stroke",
    "stylers": [
      {
        "color": "#1d2c4d"
      }
    ]
  },
  {
    "featureType": "transit.line",
    "elementType": "geometry.fill",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  },
  {
    "featureType": "transit.station",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  },
  {
    "featureType": "water",
    "elementType": "geometry",
    "stylers": [
      {
        "color": "#000309"
      }
    ]
  },
  {
    "featureType": "water",
    "elementType": "labels.text.fill",
    "stylers": [
      {
        "color": "#002164"
      }
    ]
  }
]
}

	var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

	////to place routers
	var r_ind=<?php echo json_encode($r_ind)?>;
	var Router_Long=<?php echo json_encode($Router_Longitude)?>;
	var Router_Lat=<?php echo json_encode($Router_Latitude)?>;

	///for placing switches and connecting them to respective routers via polyline
	var Switch_Long=<?php echo json_encode($Switch_Longitude)?>;
	var Switch_Lat=<?php echo json_encode($Switch_Latitude)?>;
	var switches = <?php echo json_encode($switch)?>;
	var sw_ind=<?php echo json_encode($sw_ind)?>;
	
	//for connecting routers via polyline
	var router = <?php echo json_encode($router) ?>;
	var int_ind=<?php echo json_encode($int_ind)?>;
	
	//to place pcs
	var pc_ind=<?php echo json_encode($pc_ind)?>;
	var Pc_Long=<?php echo json_encode($Pc_Longitude)?>;
	var Pc_Lat=<?php echo json_encode($Pc_Latitude)?>;
	var pc = <?php echo json_encode($pc)?>;
	var i=0;
	var rcount=0;

//This function is to create a html div of routers on gmaps the & create html dropdown content div     
//This function is to display a division as an alert box showing the details of the clicked link/polyline
	function whichpoly(latlng, map, args,i) {
		this.latlng = latlng;	
		this.args = args;	
		this.setMap(map);	
		this.i = i;
	}

	whichpoly.prototype = new google.maps.OverlayView();
	whichpoly.prototype.draw = function() {
		var self = this;
		var div = this.div;
		var i = this.i;
	    if (!div) {
			// i is the polyline id
			div = this.div = document.createElement('div');
			div.setAttribute("class","polyinfo");
			div.id='dlpoly'+i;
			div.style.position = 'absolute';
			div.style.cursor = 'pointer';
			div.style.width = '300px';
			div.style.height = '15px';
			div.style.background= 'rgba(0,255,255,0.6)';
			div.style.border='solid 1.5px rgba(0,255,255,0.8)';
			div.style.margin='30px';
			div.style.borderRadius='5px';
			div.style.color= 'white';
			d3.select(div).append("label").text(" This link connects Router Number: "+router[i][0]+" and Router Number: "+router[i][10]);
								
			//alert("This link connects Router Number: "+router[polyline.id][0]+" and Router Number: "+router[polyline.id][10]);		
			if (typeof(self.args.marker_id) != 'undefined') {
				div.dataset.marker_id = self.args.marker_id;
			}
			google.maps.event.addDomListener(div, "mouseover", function(event) {
			google.maps.event.trigger(self, "click");
			});
			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
		}
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		if (point) {
			div.style.left = (point.x - 10) + 'px';
			div.style.top = (point.y - 20) + 'px';
		}
	};
	whichpoly.prototype.remove = function() {
		if (this.div) {
			this.div.parentNode.removeChild(this.div);
			this.div = null;
		}	
	};
	whichpoly.prototype.getPosition = function() {
		return this.latlng;	
	};
        
	//This is the place router script/customGoogleMapMarkerScript
	function CustomMarker(latlng, map, args,i) {
		this.latlng = latlng;	
		this.args = args;	
		this.setMap(map);	
		this.i = i;
	}
	
	CustomMarker.prototype = new google.maps.OverlayView();
	CustomMarker.prototype.draw = function() {
		var self = this;
		var div = this.div;
		var i = this.i;
		if (!div) {
			div = this.div = document.createElement('div');
			div.setAttribute("class","pulse");
			//div.className = 'marker';
			div.id='dfklh'+i;
			div.style.position = 'absolute';
			div.style.cursor = 'pointer';
			/*var x = document.createElement("IMG");
	    	x.setAttribute("src", "./img/router.png");
	    	x.setAttribute("width", "70");
	    	x.setAttribute("height", "70");
			div.appendChild(x);	
			*/
			// this code works
			d3.select(div).append("img").attr("src","./img/router.svg").attr("class","imgrouter").style("display","none");
			//d3.select("body").select("#dfklh1").append("img").attr("src","./img/router.png").attr("class","imgrouter");
			d3.selectAll(".pulse").style("background","rgba(0,255,255,1)").style("height","22px").style("width","22px");	
			var div2  = document.createElement('div');
			div.appendChild(div2);
			d3.select(div2).attr("id","drop_router"+i).attr("class","dropdown-content").attr("position","relative");
				
			//for displaying the dropdown content
			var f=0;
			while(f<int_ind)
			{
				if(router[f][0]==i)
				{
					//alert(i);
					d3.select(div2).append("label").text("Interface "+router[f][3]).attr("class","label1");
					d3.select(div2).append("br");
					d3.select(div2).append("label").text("IP Address: ");
					d3.select(div2).append("input").attr("type","text").attr("placeholder",""+router[f][4]).attr("class","text1").attr("id","r_ip"+router[f][2]+"");
					d3.select(div2).append("input").attr("type","button").attr("value","Change").attr("class","button1").attr("onclick","ipchange("+router[f][2]+",1,'rip')");
					d3.select(div2).append("br");
					d3.select(div2).append("label").text("Address Mask: ");
					d3.select(div2).append("input").attr("type","text").attr("placeholder",""+router[f][5]).attr("class","text1").attr("id","r_mask"+router[f][2]+"");
					d3.select(div2).append("input").attr("type","button").attr("value","Change").attr("class","button1").attr("onclick","maskchange("+router[f][2]+",1,'rmk')");
					d3.select(div2).append("br");
					d3.select(div2).append("br");
				}
				f++;
			}		
			d3.select(div2).append("button").attr("onClick","Viewdetails("+i+",1)").attr("type","button").text("View Details").attr("class","button1");
			
			//Ye niche wala code tha, changed it to above, but dunno why the below code was written, cannot understand why many prev versions had it either
			//d3.select(div2).append("button").attr("onClick","Viewdetails("+router[i][10]+",1)").attr("type","button").text("View Details").attr("class","button1");
			d3.select(div2).append("br");
			d3.select(div2).append("br");
			if (typeof(self.args.marker_id) != 'undefined') {
				div.dataset.marker_id = self.args.marker_id;
			}
			//google.maps.event.addDomListener(div, "click", function(event) {
			//	alert('You clicked on a custom marker!');	

			//	google.maps.event.trigger(self, "click");
			//});
			google.maps.event.addDomListener(div, "click", function(event) {
				var myLatlng = new google.maps.LatLng(Router_Lat[i],Router_Long[i]);
				map.setCenter(myLatlng);
				map.setZoom(18);
				d3.selectAll(".pc").style("display","block");
				d3.selectAll(".switch").style("display","block");
				// alert("Clicked here! Position:"+i+"  "+myLatlng);
			});
			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
		}
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		if (point) {
		div.style.left = (point.x - 10) + 'px';
		div.style.top = (point.y - 20) + 'px';
		}
	};

	CustomMarker.prototype.remove = function() {
		if (this.div) {
			this.div.parentNode.removeChild(this.div);
			this.div = null;
		}	
	};
	CustomMarker.prototype.getPosition = function() {
		return this.latlng;	
	};

	//This function is to create a html div of switches on gmaps 
	//This is the placeswitch script
	function placeswitch(latlng, map, args,i) {
		this.latlng = latlng;	
		this.args = args;	
		this.setMap(map);	
		this.i = i;
	}
	placeswitch.prototype = new google.maps.OverlayView();
	placeswitch.prototype.draw = function() {
		var self = this;
		var div = this.div;
		var i = this.i;
		if (!div) {
			div = this.div = document.createElement('div');
			div.setAttribute("class","switch");
			//div.className = 'marker';
			div.id='dlsw'+i;
			div.style.position = 'absolute';
			div.style.cursor = 'pointer';
			div.style.display= 'none';
			div.style.width = '70px';
			div.style.height = '70px';
			d3.select(div).append("img").attr("src","./img/switch5.png").attr("class","imgswitch");
			if (typeof(self.args.marker_id) != 'undefined') {
				div.dataset.marker_id = self.args.marker_id;
			}
			
			//google.maps.event.addDomListener(div, "click", function(event) {
			//	alert('You clicked on a custom marker!');	

			//	google.maps.event.trigger(self, "click");
			//});
			
			google.maps.event.addDomListener(div, "mouseover", function(event) {
				//alert('You clicked on a custom marker!');
				//d3.select("#map-canvas").append("div").attr("id","r_0").attr("class","out_router").style("fromLatLngToDivPixel");

						
				google.maps.event.trigger(self, "click");
			});

			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
		}
		
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		
		if (point) {
			div.style.left = (point.x - 10) + 'px';
			div.style.top = (point.y - 20) + 'px';
		}
	};

	placeswitch.prototype.remove = function() {
		if (this.div) {
			this.div.parentNode.removeChild(this.div);
			this.div = null;
		}	
	};

	placeswitch.prototype.getPosition = function() {
		return this.latlng;	
	};


	//This function is to create a html div of pc on gmaps the & create html dropdown content div 
	//This is the placepc script
	function placepc(latlng, map, args,i) {
		this.latlng = latlng;	
		this.args = args;	
		this.setMap(map);	
		this.i = i;
		//alert(i);
	}
	placepc.prototype = new google.maps.OverlayView();
	placepc.prototype.draw = function() {

		var self = this;
		var div = this.div;
		var i = this.i;
	    
	    if (!div) {
			div = this.div = document.createElement('div');
			div.setAttribute("class","pc");
			div.id='dlpc'+i;
			div.style.position = 'absolute';
			div.style.cursor = 'pointer';
			div.style.width = '70px';
			div.style.height = '70px';
			div.style.display= 'none';///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			var div3  = document.createElement('div');
			div.appendChild(div3);
			d3.select(div3).attr("id","drop_pc_"+i).attr("class","dropdown-content").attr("position","relative");
			
			for(var pcin=0;pcin<pc_ind;pcin++){
				if(pc[pcin][0]==i)
				{
					if(pc[pcin][7]=='down'){
						d3.select(div).append("img").attr("src","./img/pcred.svg").attr("class","imgpc");
					
					}
					else{
						d3.select(div).append("img").attr("src","./img/pc.svg").attr("class","imgpc");
					
					}

					d3.select(div3).append("br");
					d3.select(div3).append("label").text("IP Address: ");
					d3.select(div3).append("input").attr("type","text").attr("placeholder",""+pc[pcin][3]).attr("class","text1").attr("id","p_ip"+pc[pcin][0]+"");
					d3.select(div3).append("input").attr("type","button").attr("value","Change").attr("class","button1").attr("onclick","ipchange("+pc[pcin][0]+",0,'pcip')");
					d3.select(div3).append("br");
					d3.select(div3).append("br");
					d3.select(div3).append("label").text("Address Mask: ");
					d3.select(div3).append("input").attr("type","text").attr("placeholder",""+pc[pcin][4]).attr("class","text1").attr("id","p_mask"+pc[pcin][0]+"");
					d3.select(div3).append("input").attr("type","button").attr("value","Change").attr("class","button1").attr("onclick","maskchange("+pc[pcin][0]+",0,'pcmk')");
					d3.select(div3).append("br");
					d3.select(div3).append("br");
					d3.select(div3).append("button").attr("type","button").attr("onClick","Viewdetails("+pc[pcin][0]+",0)").text("View Details").attr("class","button1");//.attr("onClick","show_details");
					d3.select(div3).append("br");
					d3.select(div3).append("br");
			 	}
			}
			
			if (typeof(self.args.marker_id) != 'undefined') {
				div.dataset.marker_id = self.args.marker_id;
			}	
			
			google.maps.event.addDomListener(div, "mouseover", function(event) {
				google.maps.event.trigger(self, "click");
			});
			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
		}

		var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		if (point) {
			div.style.left = (point.x - 10) + 'px';
			div.style.top = (point.y - 20) + 'px';
		}
	};

	placepc.prototype.remove = function() {
		if (this.div) {
			this.div.parentNode.removeChild(this.div);
			this.div = null;
		}	
	};
	placepc.prototype.getPosition = function() {
		return this.latlng;	
	};
	//Following code places the elements of the map

	//Code for placing routers on map
	for (var i = 0; i <r_ind; i++) {
	
		myLatlng = new google.maps.LatLng(Router_Lat[i],Router_Long[i]);

			overlay = new CustomMarker(
				myLatlng, 
				map,
				{
					marker_id: ''+i
				},i, router, int_ind
			);
	}

/*
	var Po=[{title: 'Keswick',		polyline: 54.60039,		lng: -3.13632},
		{title: 'Coniston',		polyline: 54.36897,		lng: -3.07561},
		{title: 'Lake District',	polyline: 54.5003526,	lng: -3.0844116},
		{title: 'Cumbria',		polyline: 54.57723,		lng: -2.79748},
		{title: 'Coniston',		polyline: 54.36897,		lng: -3.07561},
		{title: 'Lake District',	polyline: 54.5003526,	lng: -3.0844116},
		{title: 'Cumbria',		polyline: 54.57723,		lng: -2.79748}];

*/

	////Code to draw line between routers
	var k=0;
	var polyline;

	for(var ele=0;ele<int_ind;ele++){
		var con_str="r_"+router[ele][0]+"to_r_"+router[ele][10];
		check_con=document.getElementById(con_str);
		if(router[ele][10]!=-1){
			var myLatlnga = new google.maps.LatLng(Router_Lat[router[ele][0]],Router_Long[router[ele][0]]);
			var myLatlngb = new google.maps.LatLng(Router_Lat[router[ele][10]],Router_Long[router[ele][10]]);

			//For animation.....................
			var lineSymbol = {
	        	path:'M 0,-1 0,1',
	        	scale: 4,
	        	strokeColor: '#0099FF'
	        };
	        var color='#00FFFF';
 			var flightPlanCoordinates = [
    		    myLatlnga,myLatlngb
        		];
 
        	polyline = new google.maps.Polyline({
        		path: flightPlanCoordinates,
          		geodesic: true,
          		id: ele,
          		strokeColor: color,
          		strokeOpacity: 1.0,
          		strokeWeight: 1.5,
          		icons: [{									//For animation...................
				            icon: lineSymbol,
				            offset: '100%',
				            repeat: '100px'
				          }]
        	});
        	//k++;

		    animateCircle(polyline);						//For animation...................
	 		polyline.setMap(map);
			google.maps.event.addListener(polyline, 'mouseover', function() {
				polyline=this;
    			polyline.setOptions({strokeColor:'blue'});
    			var myLatlnga = new google.maps.LatLng(Router_Lat[router[polyline.id][0]],Router_Long[router[polyline.id][0]]);
				overlay = new whichpoly(
					myLatlnga, 
					map,
					{
						marker_id: ''+i 
					},polyline.id
				);
			});

			google.maps.event.addListener(polyline, 'mouseout', function() {
				polyline=this;
				polyline.setOptions({strokeColor:'#00FFFF'});
				var d=document.getElementById("dlpoly"+polyline.id);
				$("#dlpoly"+polyline.id).fadeOut(500);
				setTimeout(function(){ 	d. parentNode. removeChild(d); }, 500);
				//setTimeOut(d. parentNode. removeChild(d),1000);
				//d. parentNode. removeChild(d);
			});	
			google.maps.event.addListener(polyline, 'click', function() {
				polyline=this;
				alert("This link connects Router Number: "+router[polyline.id][0]+" and Router Number: "+router[polyline.id][10]);
   			});	
			//k++;
		}
	}

	//For animation..........................
	function animateCircle(line) {
	    var count = 0;
	    window.setInterval(function() {
		    count = (count + 7) % 200;
    		var icons = line.get('icons');
       		icons[0].offset = (count / 2) + '%';
       		line.set('icons', icons);
	   }, 20);
	}

	///Coding to place switches and connect them to respective routers
	for(var ss=0;ss<int_ind;ss++){
		//code to place switches
		if(router[ss][7]==1){
			//alert(router[ss][0]);
			//alert(switches[foo][0]);
			//alert(router[ss][2]);
			myLatlng = new google.maps.LatLng(Switch_Lat[router[ss][0]],Switch_Long[router[ss][0]]);
			overlay = new placeswitch(
				myLatlng, 
				map,
				{
					marker_id: ''+i
				},router[ss][0]
			);
			//code to connect switches to routers
			var myLatlnga = new google.maps.LatLng(Router_Lat[router[ss][0]],Router_Long[router[ss][0]]);
			//alert(""+router[ss][0]);
 			var flightPlanCoordinates = [
    		    myLatlng,myLatlnga
        		];
        	var flightPath = new google.maps.Polyline({
        		path: flightPlanCoordinates,
          		geodesic: true,
          		strokeColor: '#00FFFF',
          		strokeOpacity: 1.0,
          		strokeWeight: 1.5,
          		icons: [{									//For animation...................
				            icon: lineSymbol,
				            offset: '100%'
				          }]
        	});
			
			animateCircle(flightPath);						//For animation...................
        	flightPath.setMap(map);
			google.maps.event.addListener(flightPath, 'mouseover', function() {
    			flightPath=this;
    			flightPath.setOptions({strokeColor:'blue'});
   			});	
			google.maps.event.addListener(flightPath, 'mouseout', function() {
				flightPath=this;
    			flightPath.setOptions({strokeColor:'#00FFFF'});
			});
		}
	}
	//Code to place PCs:   Modify the following code
	for(var pcin=0;pcin<pc_ind;pcin++){
	//	myLatlng = new google.maps.LatLng(-29.9546781,118.852662);
		myLatlng = new google.maps.LatLng(Pc_Lat[pcin],Pc_Long[pcin]);
			overlay = new placepc(
				myLatlng, 
				map,
				{
					marker_id: ''+i
				},pc[pcin][0],pc[pcin][2]
			);
			
			//code to connect pcs to switches
			var myLatlnga = new google.maps.LatLng(Pc_Lat[pcin],Pc_Long[pcin]);
			//alert(Pc_Lat[pcin]);
			//alert(switches[pc[pcin][1]]);
			//alert(pc[pcin][0]);
			var myLatlngb = new google.maps.LatLng(Switch_Lat[pc[pcin][1]],Switch_Long[pc[pcin][1]]);
 			var flightPlanCoordinates = [
    		    myLatlnga,myLatlngb
        		];

        	if(pc[pcin][7]=='down'){
					  
				var flightPath = new google.maps.Polyline({
	        		path: flightPlanCoordinates,
	          		geodesic: true,
	          		strokeColor: 'red',
	          		strokeOpacity: 1.0,
	          		strokeWeight: 1.5,
	          		icons: [{									//For animation...................
					            icon: lineSymbol,
					            offset: '100%'
					          }]
	        	});
				//animateCircle(flightPath);						//For animation...................
	        	flightPath.setMap(map);
				google.maps.event.addListener(flightPath, 'mouseover', function() {
	    			flightPath=this;
	    			flightPath.setOptions({strokeColor:'blue'});
	   			});	
				google.maps.event.addListener(flightPath, 'mouseout', function() {
					flightPath=this;
	    			flightPath.setOptions({strokeColor:'red'});
				});	//alert('rrreeeddd');  	
        	}
        	else{

        		var flightPath = new google.maps.Polyline({
        		path: flightPlanCoordinates,
          		geodesic: true,
          		strokeColor: '#00FFFF',
          		strokeOpacity: 1.0,
          		strokeWeight: 1.5,
          		icons: [{									//For animation...................
				            icon: lineSymbol,
				            offset: '100%'
				          }]
	        	});
	        	
				animateCircle(flightPath);						//For animation...................
	        	flightPath.setMap(map);
				google.maps.event.addListener(flightPath, 'mouseover', function() {
	    			flightPath=this;
	    			flightPath.setOptions({strokeColor:'blue'});
	   			});	
				google.maps.event.addListener(flightPath, 'mouseout', function() {
					flightPath=this;
	    			flightPath.setOptions({strokeColor:'#00FFFF'});
				});       		
        	}
//	        var color='#00FFFF';
   //      	var flightPath = new google.maps.Polyline({
   //      		path: flightPlanCoordinates,
   //        		geodesic: true,
   //        		strokeColor: color,
   //        		strokeOpacity: 1.0,
   //        		strokeWeight: 1.5,
   //        		icons: [{									//For animation...................
			// 	            icon: lineSymbol,
			// 	            offset: '100%'
			// 	          }]
   //      	});
        	
			// animateCircle(flightPath);						//For animation...................
   //      	flightPath.setMap(map);
			// google.maps.event.addListener(flightPath, 'mouseover', function() {
   //  			flightPath=this;
   //  			flightPath.setOptions({strokeColor:'blue'});
   // 			});	
			// google.maps.event.addListener(flightPath, 'mouseout', function() {
			// 	flightPath=this;
   //  			flightPath.setOptions({strokeColor:color});
			// });	
	 }
///////////////////////////////////////////////////////////////////////////////////////////////////////////
	map.addListener('zoom_changed', function() {
		if(map.getZoom()<16){
			d3.selectAll(".pc").style("display","none");
			d3.selectAll(".switch").style("display","none");
		}

		if(map.getZoom()>=16){
			d3.selectAll(".pc").style("display","block");
			d3.selectAll(".switch").style("display","block");
		}

		if(map.getZoom()<12){
			d3.selectAll(".pulse").style("background","rgba(0,255,255,1)").style("height","22px").style("width","22px");	
			d3.selectAll(".imgrouter").style("display","none");
			d3.selectAll(".pulse").style("animation","pulse 1s infinite");
		}

		if(map.getZoom()>=12){
			d3.selectAll(".pulse").style("background","rgba(0,0,0,0)").style("height","70px").style("width","70px");	
			d3.selectAll(".pulse").style("display","block");
			d3.selectAll(".pulse").style("animation","none");
			
			//animation: pulse 1s infinite;
			d3.selectAll(".imgrouter").style("display","block");			
		}
	});
}			//closing bracket of function initialize 

google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>

<body>
	<div id="map-canvas">
	</div>
	<div style="position: absolute; top: 15px;left: 15px; background-color: rgba(0,30,60,0.5); border:solid 1px rgba(0,255,255,0.6); border-radius: 5px; " class="NPM">		
		<button style=" font-size: 20px;padding:0.5em; font-family: Century Gothic;background-color: rgba(0,30,30,0.5);color: white;border-radius: 5px;" onclick="parent.location='secondary_map_generation.php'">Secondary View</button>
		<button style=" font-size: 20px;padding:0.5em; font-family: Century Gothic;background-color: rgba(0,30,30,0.5);color: white;border-radius: 5px;" onclick="ref()">Refresh</button>

	
	</div>
<button style=" font-size: 20px;padding:0.5em; right: 15px;position: absolute;top: 15px;  font-family: Century Gothic;background-color: rgba(0,30,30,0.5);color: white;border-radius: 5px;" onclick="parent.location='index.php'">Logout</button>
	<div id="dropdownNPM1" style="position: absolute; top:68px;left: 15px; height: 86%; width: 23%;"" >
				<button class="closebutton" onclick="close1()">X</button>
	</div>

	<div style="position: absolute; bottom: 20px;right: 15px;text-align: right;font-weight: bold; height: 60px; width:300px; background-color: rgba(0,30,30,0); border:solid 1px rgba(0,255,255,0); border-radius: 5px; ">
		<label style=" font-size: 20px; font-family: Century Gothic;">Cerfer:</label><br>
		<label style=" font-size: 20px; font-family: Century Gothic;">A Network Automation Tool</label>
	</div>

<!--	<svg xmlns="http://www.w3.org/2000/svg"></svg>-->
</body>
</html>