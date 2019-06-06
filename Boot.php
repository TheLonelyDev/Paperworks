<?php

//ob_start( 'ob_gzhandler' ) ;

/* Config */
require( __DIR__ . '/Core/Config.php' ) ;


switch ( strtolower( Config::Environment ) )
{
    case 'development':
    case 'dev':
        ini_set( 'display_errors', true ) ;
        ini_set( 'display_startup_errors', true ) ;
        error_reporting( true ) ;
        
        $boolDev    =   true ;
        
        break ;
        
    case 'bench':
    case 'benchmark':
        $boolDev    =   true ;
        
        break ;        
        
    case 'testing':
        ini_set( 'display_errors', true ) ;
        ini_set( 'display_startup_errors', true ) ;
        error_reporting( true ) ;
    
        break ;
        
    default:
        ini_set( 'display_errors', false ) ;
        ini_set( 'display_startup_errors', false ) ;
        error_reporting( false ) ;
        
        break ;
}


$Core           =   [ ] ;
$Model          =   [ ] ;

class PaperWorksDestruct
{
    public function __destruct( ) 
    { 
        
    }
}

$objDestruct    =   new PaperWorksDestruct ;

class PaperWorks
{
    public function Handle( $strType, $str )
    {
        $strFile                =   file_get_contents( $strPath    =   __DIR__ . '/' . $strType . '/' . $str . '.php', NULL, NULL, 5, 40 ) ;
        
        if ( $intPos = strpos( $strFile, '@class ' ) )
        {   
            require_once( $strPath ) ;
        
            return strtok( substr( $strFile, $intPos + 7  ), " \t\n\r\0\x0B" ) ;
        }
        else
            return ;    
    }
    
    public static function RegisterModel( $str, $arr = NULL )
    {
        global $Model ;

        $strMd5                 =   md5( ( $str . serialize( $arr ?: '' ) ) ) ;

        if ( isset( $Model[ $strMd5 ] ) )
            return $Model[ $strMd5 ] ;
        
        $var                    =   self::Handle( 'Model', $str ) ;
        
        if ( ! $var )
            return ;

        return $Model[ $strMd5 ]=   new $var( $arr ) ;
    }
    
    public static function RegisterCore( $str, $arr = NULL )
    {
        global $Core ;
        
        $strMd5                 =   md5( ( $str . serialize( $arr ?: '' ) ) ) ;

        if ( isset( $Core[ $strMd5 ] ) )
            return $Core[ $strMd5 ] ;
            
        $var                    =   self::Handle( 'Core', $str ) ;
        
        if ( ! $var )
            return ;
        
        return $Core[ $strMd5 ] =   new $var( $arr ) ;;
    }

    public static function Model( $str )
    {
        global $Model ;
        return $Model[ $str ] ;
    }
    
    public static function Core( $str )
    {
        global $Core ;
        return $Core[ $str ] ;
    }

    public static function NonCacheItem( $arrData )
    {
        foreach( $arrData as $strKey => &$varVal )
        {
            if ( is_array( $varVal ) ) 
                NonCacheItem( $varVal ) ;
            else
                echo "<input type='hidden' data-name='" . htmlspecialchars( $strKey ) . "' data-value='" . htmlspecialchars( $varVal ) . "' name='Load'>" ; 
        }        
    }
    
    public static function GenerateCSRF( $str = '' )
    {
        if ( session_status( ) === PHP_SESSION_NONE )
            session_start( ) ;
                
        $str                            .=  'CSRF' ;
            
        if ( empty( $_SESSION[ $str ] ) ) 
        {
            if ( function_exists( 'mcrypt_create_iv' ) ) 
                $_SESSION[ $str ]       =   bin2hex( mcrypt_create_iv( 32, MCRYPT_DEV_URANDOM ) ) ;
            else 
                $_SESSION[ $str ]       =   bin2hex( openssl_random_pseudo_bytes( 32 ) ) ;
        }
        
        return $_SESSION[ $str ] ;        
    }
    
    public static function InsertCSRF( $arrData )
    {
        if ( session_status( ) === PHP_SESSION_NONE )
            session_start( ) ;
                
        foreach( $arrData as $strKey => &$strVal )
            $strVal                     =   PaperWorks::GenerateCSRF( $strVal ) ; 
    
        PaperWorks::NonCacheItem( $arrData ) ;     
    }
    
    public static function DelvalidateCSRF( $str = '' )
    {
        if ( session_status( ) === PHP_SESSION_NONE )
            return ;
            
        $str                            .=  'CSRF' ;
        
        unset( $_SESSION[ $str ] ) ;       
    }
    
    public static function CheckCSRF( $str = '' )
    {
        if ( session_status( ) === PHP_SESSION_NONE )
            return ;
            
        $str                            .=  'CSRF' ;
        
        if ( ! empty( $_POST[ $str ] ) ) 
            return hash_equals( $_SESSION[ $str ] ?: '', $_POST[ $str ] ) ;   
    }
    
    public static function AddPayload( $arr = [ ] )
    {
        global $objDestruct ;
        
        foreach( $arr as $strKey => &$strVal )
            $objDestruct->$strKey   =   $strVal ;
    }
}

#register_shutdown_function( function( ) { echo( json_encode( $arrDestruct ) ) ; } ) ;

/* Autoload */
function AutoLoad( )
{
    PaperWorks::RegisterCore( 'sql' ) ;
}

/* Controller */
$objController      =   &PaperWorks::RegisterCore( 'Controller' ) ;
$objController->SetBaseURL( Config::BasePath )->CreateRequest( ) ;

if ( is_file ( __DIR__ . '/Controller/' . $objController->GetController( ) . '.php' ) )
    $strPath        =   __DIR__ . '/Controller/' . $objController->GetController( ) . '.php' ;  
else
    $strPath        =   __DIR__ . '/Controller/index.php' ;

include( $strPath ) ;
$objSelf            =   new ControllerItem ;

/* Check if caching is globally turned on */
if ( Config::Caching > 0 )
{
    function CacheLoad( $strMethod, $func )
    {
        global $objController ; global $objSelf ;

        if ( empty( $_GET ) && empty( $_POST ) && ( ( $objSelf::NoCache ?: false ) === false ) )
        {
            if ( $_SESSION[ 'CacheNonce' ] )
                $strMethod  +=  $_SESSION[ 'CacheNonce' ] ;
            
            $objCache   =   PaperWorks::RegisterCore( 'Cache', $strMethod ) ;
            $obj        =   $objCache->Start( ) ;
            
            if ( ! $obj )
            {
                AutoLoad( );
                call_user_func( $func ) ;
                $objCache->End( ) ;
            }
            else
                echo $obj ;
        }
        else
        {
            AutoLoad( ) ;
            call_user_func_array( $func, $objController->GetParameters( ) ) ;   
        }
    }    
}
else
{
    function CacheLoad( $strMethod, $func )
    {
        global $objController ;
        
        AutoLoad( ) ;
        call_user_func_array( $func, $objController->GetParameters( ) ) ;   
    }
}



//call_user_func_array ( [ $objSelf, $strFunction ], $objController->GetParameters( ) ) ;    
//$objSelf->$strFunction( $objController->GetParameters( ) ) ;
if ( is_callable( [ $objSelf, $strFunction = $objController->GetFunction( ) ?: 'Index' ] ) )
    CacheLoad( $strPath . $strFunction . json_encode( $objController->GetParameters( ) ), [ $objSelf, $strFunction ] ) ;  
else
    CacheLoad( $strPath . 'Index', [ $objSelf, 'Index' ] ) ;
  

/*
$strFunction    =   $objController->GetFunction( ) ?: 'Index' ;
$strClass       =   Nova::Handle( 'Controller', $objController->GetController( ) ?: 'index' ) ;

if ( $strClass )
{
    $objSelf        =   new $strClass ;
    
    if ( is_callable( [ $objSelf, $strFunction ] ) )
        $objSelf->$strFunction( $objController->GetParameters( ) ) ;
    else
        $objSelf->Index( ) ;
}   
*/

/*
$objController  =   &Nova::RegisterCore( 'Controller' ) ;
$objController->SetBaseURL( Config::BasePath )->CreateRequest( ) ;

if ( file_exists( __DIR__ . '/Controller/' . $objController->GetController( ) . '.php' ) )
    include ( __DIR__ . '/Controller/' . $objController->GetController( ) . '.php' ) ;
else
    include ( __DIR__ . '/Controller/index.php' ) ;
*/

/* Benchmark */
if ( ! $boolDev )
    return ;

$arr        =   getrusage( ) ;

echo 
'<style>
    body
    {
        margin: 0 ;
    }
    
    table.GeneratedTable 
    {
        width: 100%;
        background-color: #d26900 !important ;
        border-collapse: collapse !important ;
        border-width: .5em !important ;
        border-color: #ff8000 !important ;
        border-style: solid !important ;
        color: #ebebeb !important ;
    }

    table.GeneratedTable td, table.GeneratedTable th 
    {
        background-color: #d26900 !important ;
        border-width: .5em !important  ;
        border-color: #ff8000 !important ;
        border-style: solid !important ;
        padding: .5em !important ;
        color: #ebebeb !important ;
    }

    table.GeneratedTable thead, table.GeneratedTable th  
    {
        background-color: #9f5000 !important ;
        text-align: left ;
    }
</style>
<table class="GeneratedTable">
    <thead>
        <tr>
            <th colspan="3">Runtime</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>User (μs)</td>
            <td colspan="2">' . $arr[ 'ru_utime.tv_usec' ] . '</td>
        </tr>
        <tr>
            <td>System (μs)</td>
            <td colspan="2">' . $arr[ 'ru_stime.tv_usec' ] . '</td>
        </tr>
        <tr>
            <td>microtime( ) (ms)</td>
            <td colspan="2">' . ( microtime( true ) - $_SERVER[ 'REQUEST_TIME_FLOAT' ] ) * 1000  . '</td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <th colspan="3">Memory (bytes)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Used </td>
            <td colspan="2">' . memory_get_usage( )  . '</td>
        </tr>
        <tr>
            <td>Used (real)</td>
            <td colspan="2">' . memory_get_usage( true ) . '</td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <th colspan="3">System</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Load</td>
            <td colspan="2">' . sys_getloadavg( )[ 0 ] . '</td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <th colspan="3">Variables</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$_SESSION</td>
            <td colspan="2">' . implode( '<br/> ', array_map( function ( $varVal, $varKey ) { return sprintf("%s: '%s'", $varKey, $varVal ) ; }, $_SESSION, array_keys( $_SESSION ) ) ) . '</td>
        </tr>
        <tr>
            <td>$_POST</td>
            <td colspan="2">' . implode( '<br/> ', array_map( function ( $varVal, $varKey ) { return sprintf("%s: '%s'", $varKey, $varVal ) ; }, $_POST, array_keys( $_POST ) ) ) . '</td>
        </tr>
        <tr>
            <td>$_GET</td>
            <td colspan="2">' . implode( '<br/> ', array_map( function ( $varVal, $varKey ) { return sprintf("%s: '%s'", $varKey, $varVal ) ; }, $_GET, array_keys( $_GET ) ) ) . '</td>
        </tr>
    </tbody>
    <thead>
        <tr>
            <th colspan="3">Controller</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Controller</td>
            <td colspan="2">' . $objController->GetController( ) . '</td>
        </tr>
        <tr>
            <td>Function</td>
            <td colspan="2">' . $objController->GetFunction( ) . '</td>
        </tr>
        <tr>
            <td>Variables</td>
            <td colspan="2">' . implode( '<br/> ', array_map( function ( $varVal, $varKey ) { return sprintf("%s: '%s'", $varKey, $varVal ) ; }, $objController->GetParameters( ), array_keys( $objController->GetParameters( ) ) ) ) . '</td>
        </tr>
    </tbody>
</table>' ;