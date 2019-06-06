<?php

/*
@class PaperWorksTemplate

PaperWorksTemplate

	Made with CodeIgniter's nice structure in mind. https://github.com/bcit-ci/CodeIgniter/blob/develop/system/libraries/Parser.php

	Examples:

	IF
		{if {var} condition val}

		{else}

		{/if}

		Conditions:
			==	!=	<=	<	>=	>	<>

	SWITCH
		{switch {var}}
			{case val}

				{break}

			{case val}

				{break}

			{default}

				{break}

		{/switch}

	LOOPS
		WIP

		{for var from 0 to 6 step 1}
			<p>{var}</P>
		{/for}

	INDEXES
		{array}
			{index in array}
			{value in array}
		{/array}

	ECHO
		{func(var)}

		eg {microtime()}
			
	VARIABLES
	    {Var}
		    
	    Ternary!
	        {Var or Test}

*/

class PaperWorksTemplate
{
    /*
        @var string
    */
	protected $strDelimiterL	=	'{' ;
	
	/*
        @var string
    */
	protected $strDelimiterR	=	'}' ;
	
    /*
        @var array
    */	
	protected $arrInit			=	[ ] ;

    /*
        @param array $arr The initial data
    */
	public function __construct( $arr = [ ] )
	{
		$this->arrInit	        =	$arr ; //+ PaperWorks::RegisterModel( 'Language' )->Get( ) ;
	}

	/*
	    Only basic variable support, no if/helpers/switch/...
	    @param string $strIn The string to be rendered
	    @param array $arrdata Defaults to [ ], the data [ 'key' => 'value' ... ]
	    @param boolean $boolReturn Defaults to false, false = echo
	*/
	public function RenderSimple( $strIn, $arrData = [ ], $boolReturn = false )
	{
	    if ( $boolReturn )
		    return $this->ParseSimple( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
        else
            echo $this->ParseSimple( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
	}
	
	/*
	    @param string $strIn The string to be rendered
	    @param array $arrdata Defaults to [ ], the data [ 'key' => 'value' ... ]
	    @param boolean $boolReturn Defaults to false, false = echo
	*/
	public function RenderString( $strIn, $arrData = [ ], $boolReturn = false )
	{
	    if ( $boolReturn )
		    return $this->Parse( $strIn, array_merge( $arrData, $this->arrInit ), $boolReturn ) ;
	    else
	        echo $this->Parse( $strIn, array_merge( $arrData, $this->arrInit ), $boolReturn ) ;
	}

	/*
	    @param string $strIn The file to be rendered, can be without .html extension
	    @param array $arrdata Defaults to [ ], the data [ 'key' => 'value' ... ]
	    @param boolean $boolReturn Defaults to false, false = echo
	*/
	public function Render( $strIn, $arrData = [ ], $boolReturn = false )
	{
	    if ( $boolReturn )
		    return $this->Parse( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
        else
            echo $this->Parse( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
	}
	
    /*
	    @param string $strIn The file to be rendered, can be without .html extension
	    @param array $arrdata Defaults to [ ], the data [ 'key' => 'value' ... ]
	    @param boolean $boolReturn Defaults to false, false = echo
	*/
	public function RenderHTML( $strIn, $arrData = [ ], $boolReturn = false )
	{
	    if ( $boolReturn )
		    return $this->ParseHTML( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
        else
            echo $this->ParseHTML( file_get_contents( __DIR__ . '/../View/' . $strIn . ( strpos( $strIn, '.html' ) ? '' : '.html' ) ) ?: '', array_merge( $this->arrInit, $arrData ) ) ;
	}

    /*
        @param string $strIn (ref) The content
    */
	protected function ParseConditionalsOld( &$strIn )
	{
	    preg_match_all( '#{if (.+)}(?(?=.+{else}.+)(.+){else}(.*)|(.+)){\/if}#Us', $strIn, $arrMatches, PREG_SET_ORDER ) ;

		if ( empty( $arrMatches ) )
		    return ;
		    
		print_r( $arrMatches ) ;
		foreach ( $arrMatches as $arrMatch )
		{
		    $strOut         =  $arrMatch[ 4 ] ?: $arrMatch[ 2 ] ;

		    preg_match( '#(.+\s?)(>|>=|<>|!=|==|<=|<)(.+\s?)#', $arrMatch[ 1 ], $arrCompare ) ; 

			if ( ! empty( $arrCompare ) )
			{
                $var1 		=	( trim( $arrCompare[ 1 ] ) !== '' )
									? str_replace( '"', '', trim( $arrCompare[ 1 ] ) )
									: false ;

				$var2 		=	( trim( $arrCompare[ 3 ] ) !== '' )
									? str_replace( '"', '', trim( $arrCompare[ 3 ] ) )
									: false ;

				$str 		=	trim( $arrCompare[ 2 ] ) ;	

				switch ( $str )
				{
					case '>':
						$strOut	=	( $var1 > $var2 ) ? $strOut : '' ;

						break ;

					case '>=':
						$strOut	=	( $var1 >= $var2 ) ? $strOut : '' ;

						break ;

					case '<':
						$strOut	=	( $var1 < $var2 ) ? $strOut : '' ;

						break ;

					case '<=':
						$strOut	=	( $var1 <= $var2 ) ? $strOut : '' ;

						break ;

					case '==':
						$strOut	=	( $var1 == $var2 ) ? $strOut : '' ;

						break ;

					case '!=':
						$strOut	=	( $var1 != $var2 ) ? $strOut : '' ;

						break ;

					case '<>':
						$strOut	=	( $var1 <> $var2 ) ? $strOut : '' ;

						break ;
				}
			}
			else
				$strOut 	    =	'' ;
            
            if ( ( $strOut === '' ) && ( ! isset( $arrMatch[ 4 ] ) ) )
                    $strOut     =   $arrMatch[ 3 ] ;

			$strIn 			=	str_replace( $arrMatch[ 0 ], $strOut, $strIn ) ;
		}
	}

    /*
        @param string $strIn (ref) The content
        @param boolean $varPre If true then do an init function
    */	
	protected function ParseConditionals( &$strIn )
	{
		$strIf 		=	( $this->strDelimiterL . 'if ' ) ;
		$strElse 	=	( $this->strDelimiterL . 'else' . $this->strDelimiterR ) ;
		$strEnd 	=	( $this->strDelimiterL . '\/if' . $this->strDelimiterR ) ;

		preg_match_all( '#' . $strIf . '|' . $strElse . '|' . $strEnd . '#sU', $strIn, $varPre, PREG_SET_ORDER ) ;
		
		if ( ! empty( $varPre ) )
		{
			$int 	=	0 ;
			$arrLast=	[ ] ;

			foreach ( $varPre as $var )
			{
				if ( $var[ 0 ] === $strIf )
				{
					++$int ;
					$arrLast[ ]	=	$int ;
					$strIn      =   substr_replace( $strIn, $this->strDelimiterL . 'if' . $int . ' ', strpos( $strIn, $strIf ), strlen( $strIf  ) ) ;
				}
				elseif ( $var[ 0 ] === $strElse )
				{
					$varLast	=	array_pop( $arrLast ) ;
					$arrLast[ ]	=	$varLast ;
					$strIn 		=	preg_replace( '#' . $strElse . '#', $this->strDelimiterL . 'else' . $varLast . $this->strDelimiterR, $strIn, 1 ) ;
					//$strIn          =   substr_replace( $strIn, $this->strDelimiterL . 'else' . $varLast . $this->strDelimiterR, strpos( $strIn, $strElse ), strlen( $strElse  ) ) ;
				}
				else
				{
					$varLast	=	array_pop( $arrLast ) ;
					$strIn 		=	preg_replace( '#' . $strEnd . '#', $this->strDelimiterL . '/if' . $varLast . $this->strDelimiterR, $strIn, 1 ) ;
					//$strIn          =   substr_replace( $strIn, $this->strDelimiterL . '/if' . $varLast . $this->strDelimiterR, strpos( $strIn, $strEnd ), strlen( $strEnd  ) ) ;
				}
			}
		}

		preg_match_all( '#' . $this->strDelimiterL . 'if(\d+) (.+)' . $this->strDelimiterR . '(.+)' . $this->strDelimiterL . '\/if(\1)' . $this->strDelimiterR . '#sU', $strIn, $arrMatches, PREG_SET_ORDER ) ;

		if ( ! empty( $arrMatches ) )
		{
			foreach ( $arrMatches as $arrMatch )
			{
				$strOut			=	$arrMatch[ 3 ] ;

				preg_match( '#(.+\s?)(>|>=|<>|!=|==|<=|<)(.+\s?)#', $arrMatch[ 2 ], $arrCompare ) ;

				if ( ! empty( $arrCompare ) )
				{
					$var1 		=	( trim( $arrCompare[ 1 ] ) !== '' )
										? str_replace( '"', '', trim( $arrCompare[ 1 ] ) )
										: false ;

					$var2 		=	( trim( $arrCompare[ 3 ] ) !== '' )
										? str_replace( '"', '', trim( $arrCompare[ 3 ] ) )
										: false ;

					$str 		=	trim( $arrCompare[ 2 ] ) ;

					switch ( $str )
					{
						case '>':
							$strOut	=	( $var1 > $var2 ) ? $strOut : '' ;

							break ;

						case '>=':
							$strOut	=	( $var1 >= $var2 ) ? $strOut : '' ;

							break ;

						case '<':
							$strOut	=	( $var1 < $var2 ) ? $strOut : '' ;

							break ;

						case '<=':
							$strOut	=	( $var1 <= $var2 ) ? $strOut : '' ;

							break ;

						case '==':
							$strOut	=	( $var1 == $var2 ) ? $strOut : '' ;

							break ;

						case '!=':
							$strOut	=	( $var1 != $var2 ) ? $strOut : '' ;

							break ;

						case '<>':
							$strOut	=	( $var1 <> $var2 ) ? $strOut : '' ;

							break ;
					}
				}
				else
					$strOut 	=	( ! empty( $arrMatch[ 2 ] ) ? $strOut : '' ) ;

				$intElse 		=	preg_split( '#' . $this->strDelimiterL . 'else' . $arrMatch[ 1 ] . $this->strDelimiterR . '#', $arrMatch[ 3 ] ) ;

				if ( count( $intElse ) > 1 )
					$strOut 	=	( $strOut === '' ) ? $intElse[ 1 ] : $intElse[ 0 ] ;
					
				$strIn 			=	str_replace( $arrMatch[ 0 ], $strOut, $strIn ) ;
			}
		}
	}

    /*
        @param string $strIn (ref) The content
        @param boolean $varPre If true then do an init function
    */
	protected function ParseSwitch( &$strIn )
	{
   		$strSwitch	=	( $this->strDelimiterL . 'switch ' ) ;
        $strEnd 	=	( $this->strDelimiterL . '\/switch' . $this->strDelimiterR ) ;

        preg_match_all( '#' . $strSwitch . '|' . $strEnd . '#sU', $strIn, $varPre, PREG_SET_ORDER ) ;

    	if ( ! empty( $varPre ) )
		{
            $int 	=	0 ;
            $arrLast=	[ ] ;

        	foreach ( $varPre as $var )
			{
                if ( $var[ 0 ] === $strSwitch )
				{
                    ++$int ;
                    $arrLast[ ]	=	$int ;
                    $strIn 		=	preg_replace( '#' . $strSwitch . '#', $this->strDelimiterL . 'switch' . $int.' ', $strIn, 1 ) ;
                }
				else
				{
                	$varLast 	=	array_pop( $arrLast ) ;
                    $strIn 		= 	preg_replace( '#' . $strEnd . '#', $this->strDelimiterL . '/switch' . $varLast . $this->strDelimiterR, $strIn, 1 ) ;
                }
            }
        }

		preg_match_all( '#' . $this->strDelimiterL . 'switch(\d+) (.+)' . $this->strDelimiterR . '(.+)' . $this->strDelimiterL . '\/switch(\1)' . $this->strDelimiterR . '#sU', $strIn, $arrMatches, PREG_SET_ORDER ) ;

		if ( ! empty( $arrMatches ) )
		{

          	foreach ( $arrMatches as $arrMatch )
			{
               	$str 		=	$arrMatch[ 0 ] ;
 		        $strOut		=	'' ;               
                $varSub 	=	$arrMatch[ 3 ] ;

                preg_match_all( '#' . $this->strDelimiterL . 'case (.+)' . $this->strDelimiterR . '(.+)' . $this->strDelimiterL . 'break' . $this->strDelimiterR . '#sU', $varSub, $arrMatches2, PREG_SET_ORDER ) ;

				if ( ! empty( $arrMatches2 ) )
				{
                 	foreach ( $arrMatches2 as $arrMatch2 )
					{
                    	if ( $arrMatch[ 2 ] == $arrMatch2[ 1 ] )
						{
                           $strOut 	=	$arrMatch2[ 2 ] ;

                           break ;
                        }
                    }
                }
                else
				{
                  	preg_match( '#' . $this->strDelimiterL . 'default' . $this->strDelimiterR . '(.+)' . $this->strDelimiterL . 'break' . $this->strDelimiterR . '#sU', $varSub, $arrMatch3 ) ;

					if ( ! empty( $arrMatch3 ) )
                       $strOut	=	$arrMatch3[ 1 ] ;
                }

                $strIn	    =	str_replace( $str, $strOut, $strIn ) ;
          	}
        }
	}

    /*
        @param string $strIn (ref) The content
        @param boolean $varPre If true then do an init function
    */
	protected function LoopParse( &$strIn )
	{
        $strFor  	= 	( $this->strDelimiterL . 'for ' ) ;
        $strEnd 	= 	( $this->strDelimiterL . '\/for' . $this->strDelimiterR ) ;

        preg_match_all( '#' . $strFor . '|' . $strEnd . '#sU', $strIn, $varPre, PREG_SET_ORDER ) ;

        if ( ! empty( $varPre ) )
		{
            $int 		= 	0 ;
            $arrLast	= 	[ ] ;

            foreach ( $varPre as $var )
			{
                if ( $var[ 0 ] === $strFor )
				{
                    ++$int ;
                    $arrLast[ ] =	$int ;
                    $strIn 		=	preg_replace( '#'  . $strFor . '#', $this->strDelimiterL . 'for' . $int . ' ', $strIn, 1 ) ;
                }
				else
				{
                    $varLast	= 	array_pop( $arrLast ) ;
                    $strIn 		= 	preg_replace( '#' . $strEnd . '#', $this->strDelimiterL . '/for' . $varLast . $this->strDelimiterR, $strIn, 1 ) ;
                }
            }
        }

        preg_match_all( '#' . $this->strDelimiterL . 'for(\d+) (\w+) from (\d+) to (\d+) step (\d+)' . $this->strDelimiterR . '(.+?)' . $this->strDelimiterL . '/for(\1)' . $this->strDelimiterR . '#s', $strIn, $arrMatches, PREG_SET_ORDER ) ;
        
		if ( ! empty ( $arrMatches ) )
		{
            $strOut		    =	'' ;		    
            
            foreach ( $arrMatches as $arrMatch )
			{
                $arr 		=	$arrMatch[ 6 ] ;

                for ( $i = $arrMatch[ 3 ] ; $i <= $arrMatch[ 4 ] ; $i = $i + $arrMatch[ 5 ] )
                    $strOut	.=	str_replace( $this->strDelimiterL . $arrMatch[ 2 ] . $this->strDelimiterR, $i, $arr ) ;

                $strIn 		=	str_replace( $arrMatch[ 0 ], $strOut, $strIn ) ;
            }
        }
	}

    /*
        @param string $strIn (ref) The content
    */
	protected function ParseHelpers( &$strIn )
    {
        preg_match_all( '#' . $this->strDelimiterL . '(\w+)(\()(.*)(\))' . $this->strDelimiterR . '#sU', $strIn, $arrMatches , PREG_SET_ORDER ) ;

        if ( ! empty( $arrMatches ) )
		{
            foreach ( $arrMatches as $arrMatch )
			{
                $str 	=	$arrMatch[ 0 ] ;
                $func 	=	$arrMatch[ 1 ] ;

                $strArgs=	( ( ! empty( $arrMatch[ 3 ] ) )
								? $this->ParseHelpersArgs( $arrMatch[ 3 ] )
								: [ ] ) ;

                if ( $func === 'empty' )
                    $strIn 	= 	str_replace( $str, ( $strArgs !== '' ), $strIn ) ;
                elseif ( function_exists( $func ) )
				{
                    try
					{
                        $strIn	=	str_replace( $str, call_user_func_array( $func, $strArgs ), $strIn ) ;
                    }
					catch ( Exception $errMsg )
					{

                    }
                }
            }
        }
    }

    /*
        @param string $strArgs (ref) The args
        @return array
    */
	protected function ParseHelpersArgs( &$strArgs )
    {
        if ( ! empty( $strArgs ) )
		{
            preg_match_all( '#(\w+)(\()(.*)(\))$#sU', $strArgs, $arrMatches, PREG_SET_ORDER ) ;

            if ( ! empty( $arrMatches ) )
			{
                foreach ( $arrMatches as $arrMatch )
				{
                    $func 	= 	$arrMatch[1];
					$strArgs=	( ( ! empty( $arrMatch[ 3 ] ) )
									? $this->ParseHelpersArgs( $arrMatch[ 3 ] )
									: [ ] ) ;

                    if ( function_exists( $func ) )
					{
                        try
						{
                            $strArgs	=	call_user_func_array( $func, $strArgs ) ;
                        }
						catch ( Exception $errMsg )
						{

						}
                    }
                }
            }
        }
        
        return ( ( ! empty( $strArgs ) )
					? ( ! is_array( $strArgs)
						? explode( ',', $strArgs )
						: $strArgs )
					: [ ] ) ;
    }

    /*
        @param string $strIn (ref) The content
        @param array $arrData The data to be rendered
        @return string The rendered content
    */    
	protected function Parse( &$strIn, &$arrData )
	{
		$this->LoopParse( $strIn ) ;

		$arr 		=	[ ] ;

		foreach ( $arrData as $strKey => &$strVal )
			$arr 	=	array_merge	(
										$arr ,
										( is_array( $strVal )
											? $this->ParsePair( $strKey, $strVal, $strIn )
											: $this->ParseSingle( $strKey, (string) $strVal )
									) ) ;

		foreach ( $arr as $strKey => &$strVal )
		    $strIn	=	str_replace( $strKey, ( $strVal ?: '' ), $strIn ) ;

		$this->ReplaceUnused( $strIn ) ;
		$this->ParseHelpers( $strIn ) ;
        
		$this->ParseSwitch( $strIn ) ;
		
		$this->ParseConditionals( $strIn ) ;

        return $strIn ;
	}
	
	protected function ParseHTML( &$strIn, &$arrData )
	{
		$this->LoopParse( $strIn ) ;

		$arr 		=	[ ] ;

		foreach ( $arrData as $strKey => &$strVal )
			$arr 	=	array_merge	(
										$arr ,
										( is_array( $strVal )
											? $this->ParsePair( $strKey, $strVal, $strIn )
											: $this->ParseAsHTML( $strKey, (string) $strVal )
									) ) ;

		foreach ( $arr as $strKey => &$strVal )
		    $strIn	=	str_replace( $strKey, ( $strVal ?: '' ), $strIn ) ;

		$this->ReplaceUnused( $strIn ) ;
		$this->ParseHelpers( $strIn ) ;
        
		$this->ParseSwitch( $strIn ) ;
		
		$this->ParseConditionals( $strIn ) ;

        return $strIn ;
	}

    /*
        @param string $strIn (ref) The content
        @param array $arrData The data to be rendered
        @return string The rendered content
    */ 
	protected function ParseSimple( &$strIn, &$arrData )
	{
		$this->LoopParse( $strIn ) ;

		$arr 		=	[ ] ;

		foreach ( $arrData as $strKey => &$strVal )
			$arr 	=	array_merge	(
										$arr ,
										( is_array( $strVal )
											? $this->ParsePair( $strKey, $strVal, $strIn )
											: $this->ParseSingle( $strKey, (string) $strVal )
									) ) ;

		foreach ( $arr as $strKey => &$strVal )
		    $strIn	=	str_replace( $strKey, ( $strVal ?: '' ), $strIn ) ;

		$this->ReplaceUnused( $strIn ) ;

        return $strIn ;
	}
	
    /*
        @param string $strKey (ref)
        @param string $strVal
        @return string
    */ 
	protected function ParseSingle( &$strKey, $strVal )
	{
		return [ $this->strDelimiterL . $strKey . $this->strDelimiterR	=>	str_replace( $this->strDelimiterL, $this->strDelimiterL . '%CLEAN_UP%', htmlentities( $strVal ) ) ] ;
	}

    /*
        @param string $strKey (ref)
        @param string $strVal
        @return string
    */ 
	protected function ParseAsHTML( &$strKey, $strVal )
	{
		return [ $this->strDelimiterL . $strKey . $this->strDelimiterR	=>	str_replace( $this->strDelimiterL, $this->strDelimiterL . '%CLEAN_UP%', ( $strVal ) ) ] ;
	}

	
    /*
        @param string $var (ref)
        @param array $arrData (ref)
        @param string $str
        @return array
    */ 
	protected function ParsePair( &$var, &$arrData, $str )
	{
		$arr		=	[ ] ;

		preg_match_all( '#' . $this->strDelimiterL . $var . $this->strDelimiterR . '(.+?)' . $this->strDelimiterL . '/' . $var . $this->strDelimiterR . '#s', $str, $arrMatches, PREG_SET_ORDER ) ;

		foreach ( $arrMatches as $arrMatch )
		{
			$str 	=	'' ;

			foreach ( $arrData as $strKey => $varVal )
			{
				$arrTemp	=	[ ] ;

				if ( ! is_string( $varVal ) )
				{
					foreach ( $varVal as $strKey2 => $strVal2 )
					{
                        if ( is_array( $strVal2 ) )
						{
							$arrPair		=	$this->ParsePair( $strKey, $strVal2, $arrMatch[ 1 ] ) ;

							if ( ! empty( $arrPair ) )
								$arrTemp 	=	array_merge( $arrTemp, $arrPair ) ;

							continue ;
						}

						$arrTemp[ $this->strDelimiterL . $strKey2 . $this->strDelimiterR ]	=	( ( ! empty( $strVal2 ) )
																									? str_replace( $this->strDelimiterL, $this->strDelimiterL . '%CLEAN_UP%', htmlentities( $strVal2 ) )
																									: '' ) ;
					}
				}
				else
					$arrTemp[ $this->strDelimiterL . $strKey . $this->strDelimiterR ]		=	( ( ! empty( $varVal ) )
																									? str_replace( $this->strDelimiterL, $this->strDelimiterL .'%CLEAN_UP%', htmlentities( $strVal ) )
																									: '' ) ;
                
				$str 					.=	strtr( $arrMatch[ 1 ], $arrTemp ) ;

				$str 					=	preg_replace( '#' . $this->strDelimiterL . 'index in ' . $var . $this->strDelimiterR . '#', $strKey, $str ) ;

                if ( ! is_array( $varVal ) )
				    $str 				=	preg_replace( '#' . $this->strDelimiterL . 'value in ' . $var . $this->strDelimiterR . '#', $varVal, $str ) ;
			}

			$arr[ $arrMatch[ 0 ] ] 		=	$str ;
		}

		return $arr ;
	}
	
    /*
        @param string $strIn (ref) The content
    */ 
	protected function ReplaceUnused( &$strIn )
    {
        $arr                =   [ '{else}', '{break}', '{default}' ] ;
        
        preg_match_all( '#(?(?=' . $this->strDelimiterL . '\w+ or \w+' . $this->strDelimiterR . ')' . $this->strDelimiterL . '\w+ or (\w+)' . $this->strDelimiterR . '|' . $this->strDelimiterL . '\w+' . $this->strDelimiterR . ')#sU', $strIn, $arrMatches, PREG_SET_ORDER ) ;
        
        if ( ! empty( $arrMatches ) )
        {
            foreach ( $arrMatches as $arrMatch )
            {
                if ( strpos( $arrMatch[ 0 ], ' or ' ) )
                    $strIn  =   str_replace( $arrMatch[ 0 ], str_replace( $this->strDelimiterL, $this->strDelimiterL . '%CLEAN_UP%', htmlentities( $arrMatch[ 1 ] ) ), $strIn ) ;
                elseif ( ! in_array( $arrMatch[ 0 ], $arr ) )
                    $strIn  =   str_replace( $arrMatch[ 0 ], '', $strIn ) ;
            }
        }
        
        $strIn  =   str_replace( '%CLEAN_UP%', '', $strIn ) ;
    }
}
