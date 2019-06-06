<?php

/*
@class PaperWorksAccount
*/

class PaperWorksAccount extends PaperWorks
{
    protected $objDB    =   NULL ;
    
    public function __construct( )
    {
        $this->objDB    =   &PaperWorks::RegisterCore( 'sql' ) ;
        session_start( ) ;
        
        $this->CookieLogin( ) ;   
    }
    
    public function __destruct( )
    {
        #$this->CookieLogin( ) ; 
    }
    
    #@COOKIE
    protected function CookieHash( $var )
    {
        return hash( 'sha1', $var + Config::AuthSalt + time( ) ) ;
    }
    
    protected function StoreCookie( $intUserID )
    {
        $strKeyHash         =   $this->CookieHash( $intUserID ) ;

        setcookie( 'Replicated', $strKeyHash, time( ) + Config::AuthCookieExpiration, '/' ) ;
        
        //$this->objDB->InsertOrUpdate( 'tblAccounts', [ 'intID' => $intUserID, 'strCookieID' => $strKeyHash ] ) ;
        $this->objDB->Update( 'tblAccounts', [ 'strCookieID' => $strKeyHash ], [ 'intID[=]' => $intUserID ] ) ;
    }
    
    protected function DestroyCookie( $intUserID )
    {
        setcookie( 'Replicated', '', time( ) - 3600, '/' ) ;
    
        $this->objDB->Update( 'tblAccounts', [ 'strCookieID' => '' ] , [ 'intID[=]' => $intUserID ] ) ;
    }
    
    public function Init( )
    {
        $this->objDB->Query( "CREATE TABLE tblAccounts ( intID INTEGER PRIMARY KEY AUTOINCREMENT, strName VARCHAR(255), strPassword VARCHAR(255), strMail VARCHAR(255), strVerificationCode VARCHAR(255), strCookieID VARCHAR(255) ) ;" ) ;
    }
        
    # Password hash
    public function Hash( $str )
    {
        return hash( 'sha256', $str + Config::AuthSalt ) ;    
    }
    
    public function Session( $strID )
    {   
        if ( $strID === NULL )
            unset( $_SESSION[ 'intID' ] ) ;
        else
            $_SESSION[ 'intID' ]    =   $strID ;
            
        echo $strID ;
    }
    
    public function PasswordStrength( $str )
    {
        return ( preg_match_all( '/(?=^.{8,}$)(?=.*\d{3,})(?=.*[!@#$%^&*]{2,}+)(?![.\n])(?=.*[A-Z]{3,})(?=.*[a-z]{3,}).*$/', $str, $a ) === 1 ? true : false ) ;
    }   
    
    public function Create( $strName, $strPassword, $strMail )
    {
        return $this->objDB->Insert( 'tblAccounts', [ 'strName' => $strName, 'strPassword' => $this->Hash( $strPassword ), 'strMail' => $strMail ] ) ;
    }
    
    public function Delete( $intID )
    {
        return $this->objDB->Delete( 'tblAccounts', [ 'intID[=]' => $intID ] ) ;
    }
    
    public function GetAccounts( )
    {
        return $this->objDB->Select( 'tblAccounts', [ 'intID', 'strName' ] ) ;
    }
    
    public function Update( $intID, $arr = null )
    {
        if ( isset( $arr[ 'intID' ] ) )
            unset( $arr[ 'intID' ] ) ;
        
        if ( empty( $arr ) )
            return false ;
        
        return $this->objDB->Update( 'tblAccounts', $arr, [ 'intID[=]' => $intID ] ) ;
    }
    
    public function Get( $intID = NULL, $strName = NULL )
    {
        return $this->objDB->Select( 
                                'tblAccounts', 
                                [ 
                                    'intID', 'strName' 
                                ], 
                                [ 
                               	    'AND'                   => 
                                    [
                            		    'OR'                => 
                            		    [
                            			    'strName[=]'    =>  $strName ,
                            			    'intID[=]'      =>  $intID
                            		    ] 
                            	    ]
                                ] 
                            )[ 0 ] ;
    }
    
    public function Exists( $strName, $strMail )
    {
        return $this->objDB->Exists( 'tblAccounts', 
            [
        	    'AND'               => 
        	    [
    		        'OR'            => 
    		        [
    			       'strName[=]' =>  [ $strName, $strMail ] ,
    			       'strMail[=]' =>  [ $strName, $strMail ]
    		        ] ,
    	        ]
            ] ) ;
    }
    
    public function GetUserPower( $intID )
    {
        return $this->objDB->Select( 
                                'tblAccountPowers', 
                                'boolPower' ,
                                [ 
                               
                            		'intID[=]'      =>  $intID
                                ] 
                            )[ 0 ] ;     
    }
    
    public function CookieLogin( )
    {
        if ( ! isset( $_COOKIE[ 'Replicated' ] ) )
           return $this->Logout( true ) ;
            
        $var=   
            $this->objDB->Select
            ( 
                'tblAccounts',
                [
                    'intID' ,
                    'strName' ,
                    'strMail' ,
                    'strCookieID' ,
                ] ,
                [
        		    'strCookieID[=]'    =>  $_COOKIE[ 'Replicated' ] ,
                ] 
            ) ;        

        if ( ! isset( $var[ 0 ] ) )
        {
            $this->Logout( true ) ;
            return false ;
        }
        
        $_SESSION[ 'intID' ]    =   $var[ 0 ][ 'intID' ] ;
        $_SESSION[ 'strName' ]  =   $var[ 0 ][ 'strName' ] ;
        
        return $arrQ ;
    }
    
    public function Login( $strName, $strPassword, $boolReplicate = true )
    {
        $var=   
            $this->objDB->Select
            ( 
                'tblAccounts',
                [
                    'intID' ,
                    'strName' ,
                    'strMail' ,
                    'strCookieID' ,
                ] ,
                [
            	    'AND'               => 
            	    [
        		        'OR'            => 
        		        [
        			       'strName[=]' =>  $strName ,
        			       'strMail[=]' =>  $strName
        		        ] ,
        	    	    'strPassword[=]'=>  $this->Hash( $strPassword )
        	        ]
                ] 
            ) ;
            
        if ( ! isset( $var[ 0 ] ) )
        {
            setcookie( 'Replicated', '', time( ) - 3600, '/' ) ;
            return false ;
        }
        
        $arrQ   =   &$var[ 0 ] ;
        
        if ( $boolReplicate )
        {
            if ( ! isset( $arrQ[ $strCookieID ] ) )
                $this->StoreCookie( $arrQ[ 'intID' ] ) ;
            else
                setcookie( 'Replicated', $arrQ[ 'strCookieID' ], time( ) + Config::AuthCookieExpiration, '/' ) ; 
        }
        
        $_SESSION[ 'intID' ]    =   $arrQ[ 'intID' ] ;
        $_SESSION[ 'strName' ]  =   $arrQ[ 'strName' ] ;
        
        return $arrQ ;
    }

    public function Logout( $bool )
    {
        if ( isset( $_SESSION[ 'intID' ] ) or $bool )
        {
            unset( $_SESSION[ 'intID' ] ) ;
            unset( $_SESSION[ 'strName' ] ) ;   
        }
            
        setcookie( 'Replicated', '', time( ) - 3600, '/' ) ;
    }
}

?>