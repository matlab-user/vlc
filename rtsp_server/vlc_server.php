<?php
	// 需要以 root 权限运行
	
	require_once( 'vlc_server_lib.php' );
	
	error_reporting( E_ALL ^ E_NOTICE );
	
	set_time_limit( 0 );
	ob_implicit_flush();
	
	$dev_info_array = array();
		
	$port = 8090;
	
// 仅仅测试时使用
	$v_ip = '';
	$v_port = 0;
	
START:
	$socket = start_udp_server( $port );
	if( $socket===FALSE ) {
		echo time()."\tsend udp port failed!\r\n";
		return;	
	}
	echo "The udp server is running!\n";
	
	while( true ) {
		
		$r = array( $socket );
		$w = NULL;
		$e = NULL;

		$num = socket_select( $r, $w, $e, 24 );
		if( $num===false ) {
			echo "socket_select() failed, reason: ".socket_strerror(socket_last_error())."\n";
			socket_close( $socket );
			sleep( 10 );
			goto START;
		}
		elseif( $num>0 ) {
				socket_recvfrom( $socket, $buf, 1024*2, 0, $f_ip, $f_port );
				if( strlen($buf)>1 ) {
					
					echo time()."---$buf\r\n";
					
					$h = substr( $buf, 0, 2 );
					switch( $h ) {
						case 'ID':
							$id = get_dev_id( $buf );
							if( empty($id) )
								break;
							
							if( !isset($dev_info_array[$id]) )	
								$dev_info_array[$id] = new dev_info();
									
							$dev_info_array[$id]->ip= $f_ip;
							$dev_info_array[$id]->port= $f_port;
							$dev_info_array[$id]->at= time();	
						
							break;
							
						case 'UP':
							$recv_id = '';
							$recv_port = 0;

							decode_id_port( $buf, $recv_id, $recv_port );
							echo "$recv_id      $recv_port\r\n";
							if( $recv_id=='' || $recv_port<=0 )
								break;
							
							foreach( $dev_info_array as &$v ) {
								if( $v->server_id==$recv_id ) {
									$v->reflector_port = $recv_port;								
									$msg = strval( $recv_port );
									socket_sendto( $socket, $msg, strlen($msg), 0, $v->ip, $v->port );
									break;
								}
							}
							
							break;
						
						case 'ON':
													
							$id = get_dev_id( $buf );
							if( empty($id) || !isset($dev_info_array[$id]) )
								break;
								
							$to_ip = $dev_info_array[$id]->ip;
							$to_port = $dev_info_array[$id]->port;
							
							if( empty($to_ip) || $to_port<=0 )
								break;
							
							$msg = 'ON';
							socket_sendto( $socket, $msg, 2, 0, $to_ip, $to_port );
							
							if( $dev_info_array[$id]->server_id=='' ) {
								$dev_info_array[$id]->v_ip = $f_ip;
								$dev_info_array[$id]->v_port = $f_port;
								$dev_info_array[$id]->server_id = uniqid( $id );
								
								$com = "php reflector.php --v_ip $f_ip --v_port $f_port --id ".($dev_info_array[$id]->server_id)." >/dev/null &";
								exec( $com );
								echo "$com\r\n";
							}

							break;
							
						default:
							break;
					}
					
				}
		}
		
		clean_dev_info( 30 );
		//var_dump( $dev_info_array );
	}
	
	socket_close( $socket );
	
?>