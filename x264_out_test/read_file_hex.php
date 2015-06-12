<?php
	
	//$fid = fopen( 'x264_first_frame_out', 'r' );
	//$fid = fopen( 'x264_rtp_out', 'r' );
	$fid = fopen( 'x264_images_out.h264', 'r' );
	
	$loop = 0;
	
	while( !feof($fid) ) {
        $buf = fread( $fid, 1328 );
		$len = strlen( $buf );
		
		for( $i=0; $i<$len; $i++ )
			printf( "%02X ", ord($buf[$i]) );
		echo "\r\n";
		break;

	}
	
	fclose( $fid );

?>