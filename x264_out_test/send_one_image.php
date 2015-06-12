<?php
	
	$descriptorspec = array(
	   0 => array("pipe", "r"),
	   1 => array("pipe", "w"),
	   2 => array("file", "/tmp/error-output.txt", "a")
	);
	
	$fid = fopen( '../matlab_get_image/out.yuv', 'r' );
	//$fid = fopen( 'x264_images_out.h264', 'r' );

	//$proc = proc_open( 'x264 -o - --input-res 176x144 - | vlc -vvv - --demux=h264 vlc://quit', $descriptorspec, $pipes );
	$proc = proc_open( 'x264 -o x264_first_frame_out --input-res 176x144 -', $descriptorspec, $pipes );
	//$proc = proc_open( 'vlc -vvv - --demux=h264', $descriptorspec, $pipes );
	if( is_resource($proc) ) {
		
		fclose( $pipes[1] );
		
		while( feof( $fid )!=TRUE ) {
			$data = fread( $fid, 176*144*1.5 );
			fwrite( $pipes[0], $data );
			//fwrite( STDOUT, $data );
			break;
		}
		
		fclose( $fid );
		fclose( $pipes[0] );

		proc_close( $proc );
	}
	
?>