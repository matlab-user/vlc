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
					
					//echo time()."---$buf\r\n";
					
					$h = substr( $buf, 0, 2 );
					switch( $h ) {
						case 'ID':
							$id = get_id( $buf );
							if( empty($id) )
								break;
							
							if( !isset($dev_info_array[$id]) )	
								$dev_info_array[$id] = new dev_info();
									
							$dev_info_array[$id]->ip= $f_ip;
							$dev_info_array[$id]->port= $f_port;
							$dev_info_array[$id]->at= time();	
						
							break;
							
						case 'UP':
						case 'TP':
							break;
						
						case 'ON':
							
							$v_ip = $f_ip;
							$v_port = $f_port;
							echo "viewer: ip - $v_ip       port - $v_port\r\n";
/*							
							$id = get_id( $buf );
							if( empty($id) || !isset($dev_info_array[$id]) )
								break;
								
							$to_ip = $dev_info_array[$id]->ip;
							$to_port = $dev_info_array[$id]->port;
							
							if( empty($to_ip) || $to_port<=0 )
								break;
							
							$msg = 'ON';
							socket_sendto( $socket, $msg, 2, 0, $to_ip, $to_port );
							
							$dev_info_array[$id]->v_ip = $f_ip;
							$dev_info_array[$id]->v_port = $f_port;
							$dev_info_array[$id]->if_open = 1;
						
							$msg = 'OK';
							$len = strlen( $msg );
							socket_sendto( $socket, $msg, $len, 0, $f_ip, $f_port );
*/
							break;
							
						default:
								
//							echo ord($buf)."\r\n";
//							echo "$f_ip --  $f_port \r\n";
							if( ord($buf)==128 ) {	
/*							
								$v_ip = '';
								$v_port = 0;
								get_view_addr( $f_ip, $f_port, $v_ip, $v_port );
								
*/								

								if( $v_ip=='' || $v_port<=0 )
									break;
								
//								echo "$f_ip --  $f_port \r\n";
								$len = strlen( $buf );
								$str_array = str_split( $buf );
//								echo "RTP------$len     ".dechex(ord($str_array[0]))."\r\n";

								socket_sendto( $socket, $buf, $len, 0, $v_ip, $v_port );
								
							}
							break;
					}
					
				}
		}
//		echo "wwwwww\r\n";
		clean_dev_info( 60*5 );
//		var_dump( $dev_info_array );
	}
	
	socket_close( $socket );
	
?>