<?php

/*
@class PaperWorksLog
*/

class PaperWorksLog extends PaperWorks
{
    protected $objDB    =   NULL ;
    
    protected $strLog   =   NULL ;
    
    public function __construct( $strLog )
    {
        $this->objDB    =   &PaperWorks::RegisterCore( 'sql', Config::LogDatabase ) ;
        
        $this->strLog   =   $strLog ; 
    }

    public function CreateEntry( $strType, $strMessage, $strTag )
    {
        return $this->objDB->Insert( 'tbl' . $this->strLog, [ 'strType' => $strType, 'strMessage' => $strMessage, 'intTime' => time( ), 'strTag' => $strTag ] ) ;
    }
    
    public function Get( $arrArgs = NULL, $intLimit = 10 )
    {
        $arr            =    [ 'ORDER' => [ 'intTime' => 'DESC' ], 'LIMIT' => $intLimit ] ;

        if ( $arrArgs !== NULL )
        {
            $arr[ 'AND' ]                           =   [ ] ;
            
            if ( isset( $arrArgs[ 'strType' ] ) )
                $arr[ 'AND' ][ 'strType[=]' ]       =   $arrArgs[ 'strType' ] ;
                
            if ( isset( $arrArgs[ 'strMessage' ] ) )
                $arr[ 'AND' ][ 'strMessage[~]' ]    =   $arrArgs[ 'strMessage' ] ;
                
            if ( isset( $arrArgs[ 'strTag' ] ) )
                $arr[ 'AND' ][ 'strTag[=]' ]        =   $arrArgs[ 'strTag' ] ;
            
            if ( isset( $arrArgs[ 'intTime' ] ) )
                $arr[ 'AND' ][ 'intTime[><]' ]      =   $arrArgs[ 'intTime' ] ;
        }
        
        return  $this->objDB->Select
                ( 
                    'tbl' . $this->strLog ,
                    '*' ,
                    $arr
                ) ; 
    }
}

?>