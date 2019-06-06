<?php

/*
@class PaperWorksForm
*/

class PaperWorksForm
{
	/*
		[
			Name
					Required	=	boolean
					Default 	=	value
					Type 		=	string || integer || boolean


		]
	*/
	public function Validate( $arr = [ ], $arrMethod = null )
	{
	    if ( $arrMethod === null )
	        $arrMethod      =   &$_POST ;
	        
		$arrOut 		    =	[ ] ;

		foreach( $arr as $strName => $arrVal )
		{
		    $bool           =   ( ! isset( $arrMethod[ $strName ] ) ) || ( empty( $arrMethod[ $strName ] ) ) ;    
		    
			if ( isset( $arrVal[ 'Required' ] ) )
			{
				if ( ( $arrVal[ 'Required' ] === true ) && $bool )
					return false ;
				elseif ( ( $arrVal[ 'Required' ] === true ) && ! $bool )
				    $var    =	$arrMethod[ $strName ] ;
				elseif ( $bool )
				{
				    if ( isset( $arrVal[ 'Default' ] ) )
				        $var=   $arrVal[ 'Default' ] ;
				    else
				        continue ;
				}
			}
			else
			{
				if ( $bool )
					return false ;
				elseif ( ! $bool )
				    $var    =	$_POST[ $strName ] ;
			}

			$arrOut[ $strName ]	=	$var ;
		}

		return $arrOut ;
	}

	public function Create( $arrForm = [ ], $arr = [ ] )
	{
		$str 			=	'<form method="' . ( $arrForm[ 'Method' ] ?: 'post' ) . '" action="' . ( $arrForm[ 'Action' ] ?: '' ) . '">' ;

		foreach( $arr as $strName => $arrVal )
			$str 		.=	$this->HTMLType( $arrVal[ 'Type' ], $strName, $arrVal[ 'Label' ] ?: NULL, $arrVal[ 'Attr' ] ?: '' ) ;

		return $str . '</form>'  ;
	}

	protected function HTMLType( $strType, $strName, $strLabel, $strAttr )
	{
		switch ( $strType )
		{
			case 'select':
				$str			=	( '<select ' . '" name="' . $strName . '" '  . $strAttr . ' >' ) ;

				if ( $strLabel !== NULL )
					foreach( $strLabel as $sub )
						$str 	.=	( '<option value="' . $sub[ 'Value' ] . '">' . $sub[ 'Text' ] . '</option>' ) ;

				return ( $str . '</select><br/>' ) ;
			case 'button':
			    return  ( '<button type="submit" ' . $strAttr . '>' . $strName . '</button>' ) ;
			case 'captcha' :
			    return ( '<div class="g-recaptcha" data-sitekey="' . Config::CaptchaClientKey . '"></div>' ) ;
			default:
				return 	( ( $strLabel !== NULL )
							? '<label for="' . $strName . '.form">' . $strLabel . '<br/>'
							: ' '
						) . '<input type="' . $strType . '" name="' . $strName . '" id="' . $strName . '.form" ' . $strAttr . ' ><br/><br/>' . ( ( $strLabel !== NULL ) ? '</label>' : '' ) ;
		}
	}
}

?>
