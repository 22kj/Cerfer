<?php
require 'connection.php';
//format: int_id ip_add mask int_name status      													   contains all interface of a router 
//format: switch_id int_id(connected to which interface) router_id(interface of which router)          contains all switches connected to the router			  //format: source_int source_router dest_int dest_router         									     contains all routers connected to the router 
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
		$router[$int_ind][11]=$l_protocol[$int_ind];          		//line protocol status of the interface
		$router[$int_ind][12]=0;									//flag
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
			stroke:red;
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
			background-color:rgba(205,205,205,0.8); 
			border-radius:8px;
			border-color:black;
    
		    
		    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    		z-index: 3;
		}

	</style>

<script type="text/javascript" src="http://mbostock.github.com/d3/d3.js"></script>
<script>
$(document).ready(function(){
//the router array contains data according to the interface ids
//Code to place devices screen

var i=0;
var rcount=0;
var router = <?php echo json_encode($router) ?>;
var switches = <?php echo json_encode($switch)?>;
var r_ind=<?php echo json_encode($r_ind)?>;
var sw_ind=<?php echo json_encode($sw_ind)?>;
var pc_ind=<?php echo json_encode($pc_ind)?>;
var int_ind=<?php echo json_encode($int_ind)?>;
var pc = <?php echo json_encode($pc)?>;


function myFunction(place_ind,router_cur_ind_int){ 
	
	if(router[place_ind][8]>0 && router[place_ind][0]==router_cur_ind_int){
		
		var div_width=100/router[place_ind][8]-0.5;
		div_width=""+div_width+"%";
		for(var j=0;j<int_ind;j++){
			if(router[j][0]==router_cur_ind_int&&router[j][10]!=-1)
			{
				var str="r_"+router[j][10];				
				var check = document.getElementById(str);	
				if(!check){
					d3.select("body").append("div").attr("id",str).attr("class","out_router").style("width", div_width);
					d3.select("#"+str).append("div").attr("id","r_in_"+router[j][10]).attr("class","router");
					d3.select("#r_in_"+router[j][10]).append("img").attr("src","./img/router.png").attr("class","imgrouter");
					d3.select("#r_in_"+router[j][10]).append("div").attr("id","drop_"+str).attr("class","dropdown-content");
					f=0;
					while(f<int_ind)
					{
						if(router[f][0]==router[j][10])
						{
							d3.select("#drop_"+str).append("label").text("Interface "+router[f][3]);
							d3.select("#drop_"+str).append("br");
							d3.select("#drop_"+str).append("label").text("IP Address: ");
							d3.select("#drop_"+str).append("input").attr("type","text").attr("placeholder",""+router[f][4]);
							d3.select("#drop_"+str).append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
							d3.select("#drop_"+str).append("br");
							d3.select("#drop_"+str).append("label").text("Address Mask: ");
							d3.select("#drop_"+str).append("input").attr("type","text").attr("placeholder",""+router[f][5]);
							d3.select("#drop_"+str).append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
							d3.select("#drop_"+str).append("br");
							d3.select("#drop_"+str).append("br");
						}
						f++;
					}				
					d3.select("#drop_"+str).append("button").attr("type","button").attr("onClick","Viewdetails()").text("View Details").style("background-color","black").style("color","white").style("border-color","black");
					d3.select("#drop_"+str).append("br");
					d3.select("#drop_"+str).append("br");

					//d3.select("#drop_"+str).append("br");
					//to place router class and put it's image
				}
			}

		}
	}


	if(router[place_ind][8]-1>0 && router[place_ind][0]==router_cur_ind_int){


		for(var j=0;j<int_ind;j++){
			if(router[j][0]==router_cur_ind_int&&router[j][10]!=-1)
			{
				var str="r_"+router[j][10];				
				var check = document.getElementById(str);	
					var pass_table_entry_row_number;
					for(var r=0;r<int_ind;r++){
						if(router[j][10]==router[r][0]){
							pass_table_entry_row_number=r;
							break;
						}
					}
					$(document).ready(myFunction(pass_table_entry_row_number,router[j][10]));
			}

		}
	}

}



if(pc_ind!=0&&r_ind!=0){
	
	d3.select("body").append("div").attr("id","r_0").attr("class","out_router").style("width", "100%");

	d3.select("#r_0").append("div").attr("id","r_in_0").attr("class","router");
	d3.select("#r_in_0").append("img").attr("src","./img/router.png").attr("class","imgrouter");
	d3.select("#r_in_0").append("div").attr("id","drop_r_0").attr("class","dropdown-content");
	f=0;
	while(f<int_ind)
	{
		if(router[f][0]==0)
		{
			d3.select("#drop_r_0").append("label").text("Interface "+router[f][3]);
			d3.select("#drop_r_0").append("br");
			d3.select("#drop_r_0").append("label").text("IP Address: ");
			d3.select("#drop_r_0").append("input").attr("type","text").attr("placeholder",""+router[f][4]);
			d3.select("#drop_r_0").append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
			d3.select("#drop_r_0").append("br");
			d3.select("#drop_r_0").append("label").text("Address Mask: ");
			d3.select("#drop_r_0").append("input").attr("type","text").attr("placeholder",""+router[f][5]);
			d3.select("#drop_r_0").append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
			d3.select("#drop_r_0").append("br");
			d3.select("#drop_r_0").append("br");

		}
		f++;
	}		
	d3.select("#drop_r_0").append("button").attr("onClick","Viewdetails()").attr("type","button").text("View Details").style("background-color","black").style("color","white").style("border-color","black");
			d3.select("#drop_r_0").append("br");
			d3.select("#drop_r_0").append("br");	

	var place_ind=0;																										//check for all interface ids
	var router_cur_ind_str="r_0";
	var router_cur_ind_int=0;

	$(document).ready(myFunction(place_ind,router_cur_ind_int));
				
}
//Following is the code to place the switches 

for(var ss=0;ss<int_ind;ss++){
	//var ccstr="r_"+router[ss][0];
	if(router[ss][7]==1){
		d3.select("#r_"+router[ss][0]).append("div").attr("id","sw_"+router[ss][0]).attr("class","switch");
		d3.select("#sw_"+router[ss][0]).append("img").attr("src","./img/swi.png").attr("class","imgswitch").attr("id","swimg_"+router[ss][0]);


		var x1,y1,x2,y2;
		var e="#r_"+router[ss][0];
		var x = $(e).position();
	    //alert(e+"  Top position: " + x.top + " Left position: " + x.left);
	    x1=x.top+85;
	    y1=x.left+75;
		var e1="#sw_"+router[ss][0];
	    var xq = $(e1).position();
	    x2=xq.top+15;
	    y2=xq.left+15;		
		d3.select("svg").append("line").attr("x1", y1).attr("y1", x1).attr("x2", y2).attr("y2", x2);
	}

}


		for(var pcin=0;pcin<pc_ind;pcin++){

			var sw="#sw_"+pc[pcin][1];

			d3.select(sw).append("div").attr("id","pc_"+pc[pcin][0]).attr("class","classpc");
			d3.select("#pc_"+pc[pcin][0]).append("img").attr("src","./img/pcicon.png").attr("id","pc_img"+pc[pcin][0]).attr("class","imgpc");
			d3.select("#pc_"+pc[pcin][0]).append("div").attr("id","drop_pc_"+pc[pcin][0]).attr("class","dropdown-content");
			d3.select("#drop_pc_"+pc[pcin][0]).append("label").text("IP Address: ");
			d3.select("#drop_pc_"+pc[pcin][0]).append("input").attr("type","text").attr("placeholder",""+pc[pcin][3]);
			d3.select("#drop_pc_"+pc[pcin][0]).append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");
			d3.select("#drop_pc_"+pc[pcin][0]).append("label").text("Address Mask: ");
			d3.select("#drop_pc_"+pc[pcin][0]).append("input").attr("type","text").attr("placeholder",""+pc[pcin][4]);
			d3.select("#drop_pc_"+pc[pcin][0]).append("input").attr("type","submit").attr("value","Change").style("background-color","black").style("color","white").style("border-color","black");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");
			d3.select("#drop_pc_"+pc[pcin][0]).append("button").attr("type","button").attr("onClick","Viewdetails()").text("View Details").style("background-color","black").style("color","white").style("border-color","black");//.attr("onClick","show_details");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");
			d3.select("#drop_pc_"+pc[pcin][0]).append("br");

			// var x1,y1,x2,y2;
			// var e="#swimg_"+pc[pcin][1];
			// var x = $(e).position();
	  //   	x1=x.top+75;
	  //   	y1=x.left;
			// var e1="#pc_img"+pc[pcin][0];
	  //   	var xq = $(e1).position();
	  //   	x2=xq.top;
	  //   	y2=xq.left;		
	 	// 	d3.select("svg").append("line").attr("x1", y1).attr("y1", x1).attr("x2", y2).attr("y2", x2);	
	 }
//Following code is to connect elements(darw hoverable lines between connected routers)

for(var ele=0;ele<int_ind;ele++){
var con_str="r_"+router[ele][0]+"to_r_"+router[ele][10];
check_con=document.getElementById(con_str);

	if(router[ele][10]!=-1&&(!check_con)){

	var x1,y1,x2,y2;
	var e="#r_"+router[ele][0];
	var x = $(e).position();
    //alert(e+"  Top position: " + x.top + " Left position: " + x.left);
    x1=x.top+85;
    y1=x.left+75;
	var e1="#r_"+router[ele][10];
    var xq = $(e1).position();
    x2=xq.top+85;
    y2=xq.left+75;		
	d3.select("svg").append("line").attr("x1", y1).attr("y1", x1).attr("x2", y2).attr("y2", x2);
	//Give ID to these lines as r_...to_r_..
	//alert("asasd");

		}
	}
});


function Viewdetails()
{
	window.location = "./View_details.php";
}
</script>
</head>

<body style="background-image: url(./img/mm.jpg);background-size: 100%;">
<button style=" font-size: 20px;padding:0.5em;bottom: 15px;position: absolute;right: 15px;z-index: 5; font-family: Century Gothic;background-color: rgba(0,30,30,0.5);color: white;border-radius: 5px;" onclick="parent.location='map_generation.php'">Primary View</button>
<svg xmlns="http://www.w3.org/2000/svg"></svg>
</body>
</html>