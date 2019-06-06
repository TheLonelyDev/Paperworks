<?php

/*
@class PaperWorksCache
*/

class PaperWorksCache
{
    protected $strFile  =   '' ;
    protected $intTime  =   Config::Caching ;

    public function __construct( $str = NULL )
    {
        if ( $str === NULL )
            return ;
        
        $this->strFile  =   __DIR__ . '/../System/Cache/' . md5( $str ) ;
    }

    public function Folder( )
    {
        mkdir( __DIR__ . '/../System/Cache/' ) ;
    }
    
    public function Start( )
    {
        if ( is_file( $this->strFile ) )
            if ( ( time( ) - $this->intTime ) < filemtime( $this->strFile ) )
                return file_get_contents( $this->strFile ) ;
        
        ob_start( ) ;
        return false ;
    }
    
    public function End( )
    {
        $objFile        =   fopen( $this->strFile, 'w' ) ; 
        fwrite( $objFile, ob_get_contents( ) ) ; 

        fclose( $objFile ); 

        ob_end_flush( ) ; 
    }
    
    public function Purge( )
    {
        array_map( 'unlink', glob( __DIR__ . '/../System/Cache/*' ) ) ;
    }
}