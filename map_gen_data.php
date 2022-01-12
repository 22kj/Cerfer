<?php

require 'connection.php';

//format: int_id ip_add mask int_name status      													   contains all interface of a router 
//format: switch_id int_id(connected to which interface) router_id(interface of which router)          contains all switches connected to the router			   //format: source_int source_router dest_int dest_router         									   contains all routers connected to the router 

$query="select router_id, region_id from router_table;";

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
	//echo $router_id[$i];
	//echo $region_id[$i];

	$query1 = "select ip_address, mask, interface_id, interface_name, status from interface_table where router_id = '$router_id[$r_ind]';";
	$result1 = mysqli_query($con,$query1);
	while($row1 = mysqli_fetch_array($result1))
	{

		$int_ip_address[$int_ind]=$row1['ip_address'];
		$interface_id[$int_ind]=$row1['interface_id'];
		$interface_name[$int_ind]=$row1['interface_name'];
		$int_mask[$int_ind]=$row1['mask'];
		$status[$int_ind]=$row1['status'];

		$router[$int_ind][0]=$router_id[$r_ind];					//router id of a particular interface
		$router[$int_ind][1]=$region_id[$r_ind];					//region id of that particular router id
		$router[$int_ind][2]=$interface_id[$int_ind];				//interface id on that particular router id
		$router[$int_ind][3]=$interface_name[$int_ind];				//interface name of that interface id
		$router[$int_ind][4]=$int_ip_address[$int_ind];				//ip address of that interface id
		$router[$int_ind][5]=$int_mask[$int_ind];					//
		$router[$int_ind][6]=$status[$int_ind];
		$router[$int_ind][7]=0;										//no of switches connected to the router (calculated later)

		//echo $router[$j][0];
		
		$query2 = "select switch_id, ip_address, interface_id from switch_table where interface_id='$interface_id[$int_ind]';";
		$result2=mysqli_query($con,$query2);
		$row2=mysqli_fetch_array($result2);
		if($row2)
		{
			$switch_id[$sw_ind]=$row2['switch_id'];
			$sw_ip_address[$sw_ind]=$row2['ip_address'];
			$sw_int[$sw_ind]=$row2['interface_id'];

			$switch[$sw_ind][0]=$switch_id[$sw_ind];				//switch id
			$switch[$sw_ind][1]=$router_id[$r_ind];					//connected to router id
			$switch[$sw_ind][2]=$sw_int[$sw_ind];					//connected to interface_id of that particular router id
			$switch[$sw_ind][3]=$interface_name[$sw_ind];			//connected to interface of that particular router id
			$switch[$sw_ind][4]=$sw_ip_address[$sw_ind];			//switch ip address
			$switch[$sw_ind][5]=0;									//no of pc connected to the switch (calculated later)	


			//echo $switch_id[$j];
			$query3 = "select pc_id, ip_address, mask from pc_table where router_id = '$router_id[$r_ind]' and switch_id='$switch_id[$sw_ind]';";
			$result3 = mysqli_query($con,$query3);
			while($row3 = mysqli_fetch_array($result3))
			{
				$pc_id[$pc_ind]=$row3['pc_id'];
				$pc_ip_address[$pc_ind]=$row3['ip_address'];
				$pc_mask[$pc_ind]=$row3['mask'];
				//echo $pc_id[$k];
	
				$pc[$pc_ind][0]=$pc_id[$pc_ind];						//pc_id
				$pc[$pc_ind][1]=$switch_id[$sw_ind];					//connected to switch id
	 			$pc[$pc_ind][2]=$router_id[$r_ind];						//connected to router id
				$pc[$pc_ind][3]=$pc_ip_address[$pc_ind];				//pc ip address
				$pc[$pc_ind][4]=$pc_mask[$pc_ind];						//pc ip address mask
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


for($i=0;$i<$int_ind;$i++)							//contains all router data
{
	echo $router[$i][0]." <br>";
	echo $router[$i][1]." <br>";
	echo $router[$i][2]." <br>";
	echo $router[$i][3]." <br>";
	echo $router[$i][4]." <br>";
	echo $router[$i][5]." <br>";
	echo $router[$i][6]." <br>";
	echo $router[$i][7]." <br>";
	echo"<br>";
}

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
