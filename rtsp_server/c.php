<?php

	set_time_limit(0); 
	ob_implicit_flush();
	
	pcntl_signal( SIGCHLD, SIG_IGN );
	
	$id = 'wdh_1';
	
	$s_ip = '127.0.0.1';		// 服务器ip
	$s_port = 8090;				// 服务端口
	
	$shm_key = ftok( __FILE__, 'w' );
	$shm_id = shmop_open( $shm_key, 'c', 0644, 1 );
	if( $shm_id==FALSE )
		die( "shmop_open failed\r\n" );
	
//------------------------------------------------	

	$pid = pcntl_fork();
	if( $pid==-1 ) {
		 die('could not fork');
	} elseif( $pid ) {						// main_process
		
		$i = 0;
		$socket = init_udp( 0 );
		$recv_len = 0;
		$msg = "ID$id;";
		$len = strlen( $msg );
		
		while( 1 ) {

			if( $recv_len==0 )
				socket_sendto( $socket, $msg, $len, 0, $s_ip, $s_port );
			
			$f_ip = '';
			$f_port = 0;
			$buf = '';
			$recv_len = socket_recvfrom( $socket, $buf, 64, 0, $f_ip, $f_port );
			echo "UDP recv $recv_len bytes ---".$buf."\n";
			
			$str = '';
			if( $recv_len<=0 )
				$str = 'C';
			elseif( $buf==='ON' )
				$str = 'O';
			
			shmop_write( $shm_id, $str, 0 );			// O - open;  C - close
		}
		
		shmop_delete( $shm_id );
		shmop_close( $shm_id );
	} else {
		//子进程执行逻辑。
		while( 1 ) {
			$shm_data = shmop_read( $shm_id, 0 , 1 );
			if( $shm_data==='C' )
				break;
		}
	}
	

//-------------------------------------------------------------------------------
// 								funs
//-------------------------------------------------------------------------------
function init_udp( $port ) {
	
	if( ($sock=socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))<0 ) { 
        echo "failed to create socket: ".socket_strerror($sock)."\n"; 
        return FALSE; 
    } 
	
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>20, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
	$rval = socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	if( $rval===false ) {
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()) . PHP_EOL;
		return FALSE;
	}
	
	$ok = socket_bind( $sock, '0.0.0.0', $port );
	if( $ok===false ) {
		echo "UDP socket_bind() failed:reason:" . socket_strerror( socket_last_error($sudp) )."\r\n";
		return FALSE;
	}
	
	return $sock;
} 
?>