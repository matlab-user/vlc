<?php

	$port = 8090;
	
	$recv_all_num = 0;
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),
	   1 => array("pipe", "w"),
	   2 => array("file", "/tmp/error-output.txt", "a")
	);
	
	$socket = start_udp_server( $port );

	$proc = proc_open( 'x264 -o - --input-res 176x144 - | vlc -vvv - --demux=h264', $descriptorspec, $pipes );
	if( is_resource($proc) ) {
		
		fclose( $pipes[1] );
		
		while( true ) {
	
			$r = array( $socket );

			$num = socket_select( $r, $w=NULL, $e=NULL, 16 );
			if( $num===false ) {
				echo "socket_select() failed, reason: ".socket_strerror(socket_last_error())."\n";
				socket_close( $socket );
				exit;
			}
			elseif( $num>0 ) {
				$recv_num = socket_recvfrom( $socket, $buf, 176*144*1.5, 0, $to_ip, $to_port );
				$recv_all_num += $recv_num;
				if( $recv_num>1 ) {
					echo " $recv_num  --   $recv_all_num\r\n";
					fwrite( $pipes[0], $buf, $recv_num );
				}
			}
		}
		
		fclose( $pipes[0] );
		fclose( $socket );
		
		proc_close( $proc );
	}

	

//---------------------------------------------------------------------
function start_udp_server( $port ) {
	
	$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
	if( $socket===false ) {
		echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		return false;
	}

	$rval = socket_get_option($socket, SOL_SOCKET, SO_REUSEADDR);
		
	socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

	$ok = socket_bind( $socket, '0.0.0.0', $port );
	if( $ok===false ) {
		return false;
	}
	
	return $socket;		
}	
?>