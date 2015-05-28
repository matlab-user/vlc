function udp_send_yuv
    
    fid = fopen( 'out.h264', 'r' );
    
    u = udp( '192.168.2.101', 8090, 'OutputBufferSize', 1500 );
    
    has_send = 0;
    
    fopen( u );
    while feof( fid )~=1
        A = fread( fid, 1400, 'uint8' )';
        
        has_send = has_send + size(A,2)
        
        fwrite( u, A );
        %pause(1);
    end
    
    fclose( fid );
    fclose( u );
