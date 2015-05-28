<?php

	$udp = init_udp();
	
	$len = 14000;
	$msg = '';
	for( $i=0; $i<$len; $i++ )
		$msg .= 'w';
	
	socket_sendto( $udp, $msg, $len, 0, '127.0.0.1', 8090 );
	
    socket_close( $udp );



//--------------------------------------------------------------------------
function init_udp( ) {
	
	$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
	if( $socket===false ) {
		echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		return false;
	}

	$rval = socket_get_option($socket, SOL_SOCKET, SO_REUSEADDR);
		
	socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

	$ok = socket_bind( $socket, '0.0.0.0', 0 );
	if( $ok===false ) {
		return false;
	}
	
	return $socket;		
}
?>