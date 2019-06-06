<?php

/*

*/

class Config
{
    /*
        @var string
    */
    const   Environment         =   '' ;
    
    /*
        @var string
    */
    const   DefaultLanguage     =   'en' ;
    
    /*
        @var integer
    */
    const   Caching             =   0 ;
    
    /*
        @var string
    */
    const   BasePath            =   '/code/workspace/Nova/PaperWorks/Release/' ;
    
    /*
        @var array
    */
    const   Database            =
    [
        'strDatabaseType' 	    => 	'sqlite' ,
		'strDatabaseFile'	    =>	( __DIR__ . '/../System/db/test.db' ) ,        
    ] ;
    
    const   LogDatabase         =
    [
        'strDatabaseType' 	    => 	'sqlite' ,
		'strDatabaseFile'	    =>	( __DIR__ . '/../System/db/log.db' ) ,        
    ] ;

    /*
        
    */
    const   AuthSalt            =   'abc' ;
    const   AuthValueSalt       =   'abcd' ;
    
    const   AuthCookieExpiration=   360000 ;
       
    /*
        @var string
    */
    const   BackupKey           =   'Q4GTDfmB4rEySBpmzYYdan7q' ;
    
    /*
        @var string
    */
    const   APIKey              =   'weK7qVV25mRSqk892UwpYqdE' ;
}

?>
