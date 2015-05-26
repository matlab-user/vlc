% 默认 yuv - 4:2:0 planar
function y4m2yuv( file_path )
    fid = fopen( file_path, 'r' );
    [ W, H ] = read_header( fid );

    fid2 = fopen('out.yuv', 'w+');
    
    IM = zeros( H, W, 3 );
        
    while  feof( fid )~=1
        Y = [];
        U = [];
        V = [];
    
        for i = 1 : H
            IM(i,:,1) = uint8( fread( fid, W )' );
            Y = [ Y IM(i,:,1) ];
        end
        
        for i = 1 : H/2
            for j = 1 : W/2
               mid = fread( fid, 1 ) - 128;
               IM(2*i-1:2*i,2*j-1:2*j,2) = [ mid mid; mid mid ];
               U = [ U mid+128 ];
            end
        end
        
        for i = 1 : H/2
            for j = 1 : W/2
               mid = fread( fid, 1 ) - 128;
               IM(2*i-1:2*i,2*j-1:2*j,3) = [ mid mid; mid mid ]; 
               V = [ V mid+128 ];
            end
        end
        
        YM(:,:,1) = IM(:,:,1) - 0.00093*IM(:,:,2) + 1.401687*IM(:,:,3);
        YM(:,:,2) = IM(:,:,1) - 0.3437*IM(:,:,2) - 0.71417*IM(:,:,3);
        YM(:,:,3) = IM(:,:,1) + 1.77216*IM(:,:,2) + 0.00099*IM(:,:,3);
        
        imshow( uint8(YM) )
        
        mid = fread( fid, 6 )';
        if isempty( mid )
            break;
        end
        if mid(end)~=10
            break;
        else
            if strcmp( char(mid(1:end-1)), 'FRAME' )~=1
                break;
            end
        end

        %pause( 0.05 );
        fwrite( fid2, [Y U V] );
    end
    
    fclose( fid );
    fclose( fid2 );
    
%--------------------------------------------------------------------------
%                sub-functions
%--------------------------------------------------------------------------
% fid -- the handler of the image's file
% w h -- the width and height of the image
% 仅能处理 W H F A I
% 执行此函数后， 后续数据为YUV数据
function [ w, h ] = read_header( fid )
    w = 0;
    h = 0;
    
    str = ( fread( fid, 10 ) )';
    
    if str(end) ~= 32
        return;
    else
        str(end) = [];
    end
    
    if strcmp( char(str), 'YUV4MPEG2' ) == 0
        return;
    end
    
    while 1
        str = [];
        mid = [];
        mstr = [];
                
        str = fread( fid, 1 );
        switch str
            case 'W'
                mid = fread( fid, 1 );
                while mid~=32 && mid~=10
                    mstr = [ mstr mid ];
                    mid = fread( fid, 1 );
                end
                w = str2num( char(mstr) );

            case 'H'
                mid = fread( fid, 1 );
                while mid~=32 && mid~=10
                    mstr = [ mstr mid ];
                    mid = fread( fid, 1 );
                end
                h = str2num( char(mstr) );
                
            case 'A'
                mid = fread( fid, 1 );
                while mid~=32 && mid~=10
                    mid = fread( fid, 1 );
                end
                
            case 'F'
                mid = fread( fid, 1 );
                
                if mid == 'R'
                   while mid~=32 && mid~=10
                        mid = fread( fid, 1 );
                   end
                   break;
                else
                   while mid~=32 && mid~=10
                        mid = fread( fid, 1 );
                   end
                end
                
            case 'I'
                fread( fid, 2 );

            otherwise
                break;
        end
    end
    
    
    
 