<?php

class ControllerItem extends PaperWorks
{
    const NoCache   = true ;
    
    public function Index( )
    {
        header( 'Location: ' . Config::BasePath . 'ua' ) ;
    }
}