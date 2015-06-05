<?php

	set_time_limit(0); 

	$id = 'wdh_1';
	
	//$address = 'www.cdsway.com';
	$address = '127.0.0.1';
	$port = 8090;
	
	$l_ip = '';
	$l_port = '';
	
	$sock = init_socket();
	for( $i=0;$i<20;$i++ ) {
		say_ON( $sock, $address, $port );
		sleep(15);
	}
	socket_getsockname( $sock, $l_ip, $l_port );		// 获取绑定的 ip、port
	echo "l_ip--".$l_ip."    l_port--".$l_port."\n";
	socket_close( $sock );
	
	//exec( "vlc --quiet -vvv rtp://@:$l_port" );
	
	//$buf = '';
	//socket_recvfrom( $sock, $buf, 1024, 0, $address, $port );
	//echo "======$buf\r\n";

//-------------------------------------------------------------------------------
// 								funs
//-------------------------------------------------------------------------------
function init_socket() {
	
	if( ($sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))<0 ) { 
        echo "failed to create socket: ".socket_strerror($sock)."\n"; 
        return -1; 
    } 
	
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>30, "usec"=>0) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0) );
	$rval = socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	if( $rval===false ) {
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()) . PHP_EOL;
		return -1;
	}
	
	return $sock;
}


function say_ON( $sock, $address, $port ) { 

	global $id;
	
	if( $sock<=0 )
		return;
	
	$msg = "ON$id;";
	$len = strlen( $msg );
	socket_sendto( $sock, $msg, $len, 0, $address, $port );
} 

function start_udp_server( $port ) { 

	global $l_ip, $l_port, $host, $port; 

	$sudp = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
	if( $sudp===false ) {
		echo "UDP socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		exit;
	}
	
	$rval = socket_set_option( $sudp, SOL_SOCKET, SO_REUSEADDR, 1 );
	if( $rval===false ) {
		echo 'Unable to set UDP socket option: '. socket_strerror(socket_last_error()) . PHP_EOL;
		exit;
	}
	
	socket_set_option( $sudp, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );
	socket_set_option( $sudp, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
	
	$ok = socket_bind( $sudp, $l_ip, $l_port );
	if( $ok===false ) {
		echo "false  \r\n";
		echo "UDP socket_bind() failed:reason:" . socket_strerror( socket_last_error($sudp) )."\r\n";
		exit;
	}
	
	socket_recvfrom( $sudp, $buf, 9, 0, $host, $port );
	echo "UDP recv---".$buf."\n";
		
	socket_close( $sudp ); 
} 
?>