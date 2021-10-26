# Cerfer

Result Analysis:
The project implementation was a success and incorporated the following features as shown below by the snapshots of Cerfer. The simulation was run upon a dummy network consisting of 4 routers, 13 PCs and 4 switches preconfigured and interconnected. 

1. AUTHENTICATION
Primarily, the key feature of any application related to a network must be secured access to the application. Cerfer hence authenticates any user before advancing to further levels. Figure 6 shows the login interface of Cerfer.

2. MAP GENERATION
i. With G-Maps
The G-Maps interface is the primary view intended for nonprofessional users without specific domain expertise. Figure 7 shows the network consisting of core connected routers and the faulty devices (router in this case) as well as links highlighted with a red colour whereas the functioning devices are depicted in blue colour. On further exploring (incrementing zoom level) into the map, each core node decomposes into more detailed view of the network exhibiting individual components of the network.

ii. Without G-Maps:
This view is a secondary view of the network map intended for more experienced user who has prior knowledge of the entire network. Figure 9 gives a detailed view of the network consisting of pictorial representations of routers, PCs, switches and their interconnections.


3. VIEWING THE DETAILS
Details of every node can be viewed by an on hover feature. Figure 10 shows the details of a core node consisting of IP Address, Subnet Mask, and Interface name of that particular node. The subsequent Figure 11 shows similar details for a specific device.

A more detailed assessment of a device can be made by clicking on the view details button provided on the on-hover panel. This reveals the device specific network parameters such as bandwidth utilization, packet loss, average response time, link status, routing protocols, etc. Figure 12 shows the details of a router. While figure 13 shows the details of a PC.

The details of every node can also be viewed in the secondary map similarly. 
Figure 15 shows the details of a core node consisting of IP Address, Subnet Mask, and Interface name of that particular node. The subsequent Figure 16 shows similar details for a specific device.

4.  MONITORING AND ALERTING
The network consists of core connected routers and the faulty devices (router in this case) as well as links highlighted with a red colour whereas the functioning devices are in blue colour.


A detailed view of a core unit of the network along with malfunctioning parts can be viewed. A detailed view consists of pictorial representations of working as well as faulty routers, PCs, switches and their interconnections.


5. DEVICE CONFIGURATION
The device specific network parameters such as IP address and network masks can be configured. The effortless IP configuration of a particular interface of a router via the map also represents the configuration of network mask of a specific interface.



Testing
1. USER AUHENTICATION
The security authentication is provided via a unique user ID and corresponding passwords stored in the application database. If the information given by the user matches, access is granted otherwise restricted as shown by the respective figures.

2. CONFIGURATION AND VALIDATION
Input 1:	IP Address: 0.0.0.0
Output 1:	Enter a proper class B address
Input 2:	Address Mask: 255.0.0.0 
Output 2:	Enter a proper class B address
Since the network is configured in a class B IP address space it expects the new configuration to be in the same address space. Hence, 0.0.0.0 is invalid and same case applies to the mask.

3. ADDITION OF NEW DEVICES
New devices can be easily added to the network by inserting corresponding details in the database. These details reflect immediately on the map. 

Conclusion and Future Work
1. CONCLUSION
With networks becoming increasingly complex, domain experts require some degree of network management, monitoring and automation. Cerfer is a web based automated monitoring tool that provides a very intuitive and interactive GUI, real time map generation and monitoring as well as system guided network configuration. The application is capable of generating a geo-network-map in real time hence the users can view their physical location of the device to better visualize the network. It identifies the network devices so that the user can make the network more robust by adding different types of devices and be able to monitor them. It views the details of specific devices allowing the users to monitor device specific parameters. It updates the map for status changes through which users can view real-time changes in the network and finally it allows as well as guides the user for configuring the devices within the network so that naïve users can use the application and manage the network as well. Currently, it is a simulation where the devices are stored as database entries. This will be a very useful application for about any organization that uses a network that needs to be managed and monitored without much domain specific expertise.

