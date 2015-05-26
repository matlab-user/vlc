<?php

	set_time_limit( 0 );
	ob_implicit_flush();
	
	$port = 8090;
	
START:
	$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
	if( $socket===false ) {
		echo "socket_create() failed:reason:" . socket_strerror( socket_last_error() ) . "\n";
		exit;
	}

	$rval = socket_get_option($socket, SOL_SOCKET, SO_REUSEADDR);
	if( $rval===false )
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()).PHP_EOL;
	elseif( $rval!==0 )
		echo 'SO_REUSEADDR is set on socket !'.PHP_EOL;
		
	socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>6, "usec"=>0 ) );

	$ok = socket_bind( $socket, '0.0.0.0', $port );
	if( $ok===false ) {
		echo "false  \r\n";
		echo "socket_bind() failed:reason:" . socket_strerror( socket_last_error( $socket ) )."\r\n";
		exit;
	}
/*
	socket_getsockname ( $socket, $A, $P );
	echo get_local_ip().'     '.$P.'         '.time()."\n";
	socket_close( $socket );
	exit;
*/	
	echo "The udp server is running!\n";
	
	while( true ) {
		
		$r = array( $socket );

		$num = socket_select( $r, $w=NULL, $e=NULL, 16 );
		if( $num===false ) {
			echo "socket_select() failed, reason: ".socket_strerror(socket_last_error())."\n";
			socket_close( $socket );
			sleep( 20 );
			goto START;
		}
		elseif( $num>0 ) {
				socket_recvfrom( $socket, $buf, 1024*2, 0, $to_ip, $to_port );
				if( strlen($buf)>1 ) {
					echo time()."  data_len: ".bin2hex($buf)."\r\n";
					//echo "op_res---".$buf."\n";
					return;
				
				}
		}
	}
	
//--------------------------------------------------------------------------------------------------------
//			sub_funs 
//--------------------------------------------------------------------------------------------------------	
	function get_local_ip() {
		$preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
		exec ( "ifconfig" , $out , $stats );
		if( !empty($out) ) {
			if( isset($out[1]) && strstr($out[1],'addr:') ) {
				$tmpArray = explode( ":" , $out[1] );
				$tmpIp = explode( " " , $tmpArray[1] );
				if( preg_match($preg,trim($tmpIp[0])) ) {
					return trim( $tmpIp[0] );
				}
			}
		}
		return '127.0.0.1' ;
	} 
?>