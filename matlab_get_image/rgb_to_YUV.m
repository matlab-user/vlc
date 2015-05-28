function out = rgb_to_YUV( rgb_im )
    
    [ H W Z ] = size( rgb_im );
    yuv = rgb2ycbcr( uint8(rgb_im) );

    out = [];
    U = [];
    V = [];
    
    for i = 1 : H/2
%         Y(1:4:2*W) = yuv( 2*i-1, 1:2:W, 1 );
%         Y(2:4:2*W) = yuv( 2*i-1, 2:2:W, 1 );
%         Y(3:4:2*W) = yuv( 2*i, 1:2:W, 1 );
%         Y(4:4:2*W) = yuv( 2*i, 2:2:W, 1 );

        out = [ out yuv(2*i-1,:,1) yuv(2*i,:,1) ];
        U = [ U yuv(2*i-1,1:2:W,2) ];
        V = [ V yuv(2*i,1:2:W,3) ];
    end

    clear yuv Z H W Y
    size( out )
    size( U )
    size( V )
    
    out = [ out U V ];
    size( out )
    
    fd = fopen( 't.yuv', 'w+' );
    fwrite( fd, out, 'uint8' );
    fclose( fd );