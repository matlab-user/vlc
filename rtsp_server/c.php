<?php

	set_time_limit(0); 
	ob_implicit_flush();
	
	$id = 'wdn-1';
	
	$s_ip = '127.0.0.1';		// 服务器ip
	$s_port = 8090;				// 服务端口
	
	$l_ip = '';
	$l_port = 0;
	
	while( 1 ) {
		say_hi( $s_ip, $s_port, $l_ip, $l_port );
		break;
		sleep( 10 );
	}
	

//-------------------------------------------------------------------------------
// 								funs
//-------------------------------------------------------------------------------
function say_hi( $address, $port, &$l_ip, &$l_port ) { 

	global $id;
	
    if( ($sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))<0 ) { 
        echo "failed to create socket: ".socket_strerror($sock)."\n"; 
        exit; 
    } 
	
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
	$rval = socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	if( $rval===false ) {
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()) . PHP_EOL;
		exit;
	}
		
	$msg = "ID$id;";
	$len = strlen( $msg );
	socket_sendto( $sock, $msg, $len, 0, $address, $port );
	
	socket_getsockname( $sock, $l_ip, $l_port );		// 获取绑定的 ip、port
	echo "l_ip--".$l_ip."    l_port--".$l_port."\n";
	
	socket_close( $sock );	
	
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