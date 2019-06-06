<?php

/*
@class PaperWorksCommon
*/

class PaperWorksCommon
{
    public static function GetIP( )
    {
        if ( empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) )
            return $_SERVER[ 'HTTP_CLIENT_IP' ] ;
        elseif ( ! empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) )
            return $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ;
        else
            return $_SERVER[ 'REMOTE_ADDR' ] ;     
    }
    
	public function Download( $varFile = '', $varData = '' )
	{
		if ( ( $varFile === '' ) || ( $varData === '' ) )
			return ;
		elseif ( $varData === NULL )
		{
			if ( is_array( $varFile ) )
			{
				if ( count( $varFile ) !== 1 )
					return ;
				
				$strPath    =   key( $varFile ) ;
				$varFile    =   current( $varFile ) ;
				
				if ( is_int( $strPath ) )
					return ;
				
				if ( ( ! @is_file( $strPath ) ) || ( ( $intFileSize = @filesize( $strPath ) ) === false ) )
					return ;
			}
			else
			{
				if ( ( ! @is_file( $varFile ) ) || ( ( $intFileSize = @filesize( $varFile ) ) === false ) )
					return ;
				
				$strPath    =   $varFile ;
				$varFile    =   end( explode( '/', str_replace( DIRECTORY_SEPARATOR, '/', $varFile ) ) ) ;
			}
		}
		else
			$intFileSize    =   strlen( $varData ) ;

		$strMime            =   'application/octet-stream' ;
		$arrExtensions      =   explode( '.', $varFile ) ;
		$strExtension       =   end( $arrExtensions ) ;

		if ( ( count( $arrExtensions ) !== 1 ) && ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) && ( preg_match( '/Android\s(1|2\.[01])/', $_SERVER[ 'HTTP_USER_AGENT' ] ) ) )
		{
			$arrExtensions[ count( $arrExtensions ) - 1 ]   =   strtoupper( $strExtension ) ;
			$varFile        =   implode( '.', $arrExtensions ) ;
		}
		
		if ( ( $varData === NULL ) && ( ( $fp = @fopen( $strPath, 'rb' ) ) === false ) )
			return ;
		
		if ( ( ob_get_level( ) !== 0 ) && ( @ob_end_clean( ) === false ) )
			@ob_clean( ) ;
		
		header( 'Content-Type: ' . $strMime ) ;
		header( 'Content-Disposition: attachment; filename="'. $varFile .'"' ) ;
		header( 'Expires: 0' ) ;
		header( 'Content-Transfer-Encoding: binary' ) ;
		header( 'Content-Length: '. $intFileSize ) ;
		header( 'Cache-Control: private, no-transform, no-store, must-revalidate' ) ;

		if ( $varData !== null )
			exit( $varData ) ;
		 
		while ( ( ! feof( $fp ) ) && ( ( $varData = fread( $fp, 1048576 ) ) !== false ) )
			echo $varData ;
		
		fclose( $fp ) ;
		
		exit ;
	}
	
}