<?php
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),
	   1 => array("pipe", "w"),
	   2 => array("file", "/tmp/error-output.txt", "a")
	);
	
	$fid = fopen( 'out.yuv', 'r' );

	$proc = proc_open( 'x264 -o - --input-res 176x144 - | vlc -vvv - --demux=h264 vlc://quit', $descriptorspec, $pipes );
	if( is_resource($proc) ) {
		
		fclose( $pipes[1] );
		
		while( feof( $fid )!=TRUE ) {
			$data = fread( $fid, 176*144*1.5 );
			fwrite( $pipes[0], $data );
			//fwrite( STDOUT, $data );
		}
		
		fclose( $fid );
		fclose( $pipes[0] );

		proc_close( $proc );
	}
	
?>