<?php

/*
@class PaperWorksLanguage
*/

class PaperWorksLanguage extends PaperWorks
{
    public function Get( $str = NULL )
    {
        if ( $str === NULL )
            $str    =   Config::DefaultLanguage ?: 'en' ;
        
        if ( ! is_file( $strFile = __DIR__ . '/../System/Language/' . $str . '.php' ) )
            return ;
            
        return include( $strFile ) ;
    }
}