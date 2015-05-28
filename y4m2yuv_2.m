% 默认 yuv - 4:2:0 planar
function y4m2yuv( file_path )
    fid = fopen( file_path, 'r' );
    [ W, H ] = read_header( fid );

    fid2 = fopen('out.yuv', 'w+');
    
    frame_num = 0;
    
    while  feof( fid )~=1
        Y = [];
        U = [];
        V = [];
    
        for i = 1 : H
            Y = [ Y uint8( fread(fid,W)' ) ];
        end

        for i = 1 : H/2
            U = [ U fread( fid, W/2 )' ];
        end
        
        for i = 1 : H/2
            V = [ V fread( fid, W/2 )' ];
        end
        
        frame_num = frame_num + 1;
        fwrite( fid2, [Y U V] );  
        
        mid = fread( fid, 6 )';
        if isempty( mid )
            break;
        end
        
        if mid(end)~=10
            break;
        elseif strcmp( char(mid(1:end-1)), 'FRAME' )~=1
            break;
        end
        
    end
    
    frame_num
    
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
    
    
    
 