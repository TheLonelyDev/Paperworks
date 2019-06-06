<?php

/*
@class PaperWorksController

NovaController

*/

class PaperWorksController
{
    /*
        @var string
    */
    const strClassname              =   'Index' ;

    /*
        @var int
    */
    protected $intControllerKey     =   0 ;
    
    /*
        @var string
    */
    protected $strBaseURL ;
    
    /*
        @var string
    */
    protected $strController ;
    
    /*
        @var string
    */
    protected $strFunction ;
    
    /*
        @var array
    */
    protected $arrParams            =   [ ] ;

    /*
        Set the default controller
    */
    public function __construct( )
    {
        $this->strController        =   self::strClassname ;
    }
    
    /*
        @param string $strURL
        @return object self
    */
    public function SetBaseURL( $strURL )
    {
        $this->strBaseURL           =   $strURL ;
        
        return $this ;
    }
    
    /*
        @param array $arrParams (ref) [ 'key' => 'value' ... ]
        @return object self
    */
    public function SetParameters( &$arrParams )
    {
        $this->arrParams            =   $arrParams ;
        
        return $this ;
    }

    /*
        @return array [ 'key' => 'value' ... ]
    */
    public function GetParameters( )
    {
        return $this->arrParams ;
    }

    /*
        @return string
    */
    public function GetController( )
    {
        return strtolower( $this->strController ) ;
    }

    /*
        @return string
    */    
    public function GetFunction( )
    {
        return strtolower( $this->strFunction ) ;
    }

    /*
        @param string $strName (ref)
        @param string $strDefault Defaults to NULL
        @return string
    */  
    public function GetParam( &$strName, $strDefault = NULL )
    {
        if ( isset( $this->arrParams[ $strName ] ) )
            return $this->arrParams[ $strName ] ;
        
        return $strDefault ;
    }

    /*
        @return string Either nothing (if the REQUEST_URI is not set) or the base url
    */  
    public function GetRequestURI( )
    {
        if ( ! isset( $_SERVER[ 'REQUEST_URI' ] ) )
            return '' ;

        return trim( str_replace( $this->strBaseURL, '', $_SERVER[ 'REQUEST_URI' ] ), '/' ) ;
    }

    /*
        @return object self
    */  
    public function CreateRequest( )
    {
        $arrURI                 =   explode( '/', $this->GetRequestURI( ) ) ;

        if ( ! isset( $arrURI[ $this->intControllerKey ] ) )
            return $this ;

        $this->strController    =   $this->Format( $arrURI[ $this->intControllerKey ] ) ;

        unset( $arrURI[ $this->intControllerKey ] ) ;

        if ( empty( $arrURI ) )
            return $this ;
            
        
        $this->intControllerKey =   $this->intControllerKey + 1 ;
        
        $this->strFunction      =   $this->Format( $arrURI[ $this->intControllerKey ] ) ;

        unset( $arrURI[ $this->intControllerKey ] ) ;

        if ( empty( $arrURI ) )
            return $this ;
            

        $i                      =   0 ;
        $strKeyName             =   '' ;
        
        foreach ( $arrURI as &$strVal ) 
            $this->arrParams[ ] =   $strVal ;

        return $this ;
    }

    /*
        @param string $str
        @returns string Either empty or the name
    */  
    protected function Format( $str )
    {
        if ( strpos( $str, '-' ) !== false )
            $str            =   join( '', explode( '-', $str )  ) ;
        else 
            $str            =   $str ;

        if ( is_numeric( substr( $str, 0, 1 ) ) ) 
            return '' ;
        
        return strstr( ltrim( $str, '_' ), '?', true ) ?: ltrim( $str, '_' ) ;
    } 

}