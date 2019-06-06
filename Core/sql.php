<?php

/*
@class PaperWorksSQL

PaperWorksSQL

Fork of Medoo (MIT license)
Commercial Use, Distribution, Modification and Private use are allowed.
*/

class PaperWorksSQL
{
    /*
        @var array
    */
	protected $arrLogs      =   [ ] ;
	
	/*
        @var boolean
    */
	protected $boolDebug    =   true ;

    /*
        @var string
    */
    protected $strDatabaseType ;
    
    /*
        @var string
    */
    protected $strCharset ;
    
    /*
        @var string
    */
    protected $strDatabaseName ;
    
    /*
        @var intiger
    */
    protected $intPort ;
    
    /*
        @var string
    */
    protected $strPrefix ;
    
    /*
        @var array
    */
    protected $arrOpt       =   [ ] ;
    
    /*
        @var array
    */
    protected $arrDatabases =   [ 'mariadb', 'mysql', 'pgsql', 'sybase', 'mssql' ] ;


    /*
        MySQL, MariaDB, MSSQL, Sybase, PostgreSQL, Oracle
        
        @var string
    */
    protected $strServer ;
    
    /*
        @var string
    */
    protected $strUser ;
    
    /*
        @var string
    */
    protected $strPass ;

    /*
        SQLite
        
        @var string
    */
    protected $strDatabaseFile ;


	/*
        Construct
            This function is ran when the class is constructed ;
            
        @param array $arrConfig Defaults to [ ], if empty read Config::Database
    */
    public function __construct( $arrConfig = [ ] )
    {
        try
        {
            $arrCmds                =   [ ] ;
            $strDataSourceName      =   '' ;
            
            if ( empty( $arrConfig ) )
                $arrConfig          =   Config::Database ;
                
            if ( is_array( $arrConfig ) )
                foreach( $arrConfig as $strOpt => &$strVal )
                   $this->$strOpt   =   $strVal ;
            else
                return false ;

            $strType                =   isset( $this->strDatabaseType ) ? strtolower( $this->strDatabaseType ) : 'mysql' ;

			switch ( $strType )
			{
				case 'mariadb':
				case 'mysql':
					$strDataSourceName      =   ( 'mysql:host='                 . $this->strServer .    ( isset( $this->intPort )
																											? ( ';port=' . ( (int) $this->intPort ) )
																											: ' '
																										)     . ';dbname=' . $this->strDatabaseName ) ;

					// Make MySQL using standard quoted identifier
					$arrCmds[ ]             =    'SET SQL_MODE=ANSI_QUOTES' ;

					break ;

				case 'pgsql':
					$strDataSourceName      =   ( 'pgsql:host='                 . $this->strServer .    ( isset( $this->intPort )
																											? ( ';port=' . ( (int) $this->intPort ) )
																											: ' '
																										)    . ';dbname=' . $this->strDatabaseName ) ;

					break ;

				case 'sybase':
					$strDataSourceName      =   ( 'dblib:host='                 . $this->strServer .    ( isset( $this->intPort )
																											? ( ';port=' . ( (int) $this->intPort ) )
																											: ' '
																										)     . ';dbname=' . $this->strDatabaseName ) ;

					break ;

				case 'mssql':
					$strDataSourceName     	=   strstr( PHP_OS, 'WIN' )
													? ( 'sqlsrv:server='        . $this->strServer .   ( isset( $this->intPort )
																												? ( ';port=' . ( (int) $this->intPort ) )
																												: ' '
																											)     . ';database=' . $this->strDatabaseName )
						                        	: ( 'dblib:host='           . $this->strServer .   ( isset( $this->intPort )
																												? ( ';port=' . ( (int) $this->intPort ) )
																												: ' '
																											)     . ';dbname=' . $this->strDatabaseName ) ;

					// Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
					$arrCmds[ ]            	=   'SET QUOTED_IDENTIFIER ON' ;

					break ;

				case 'sqlite':
					$strDataSourceName		=	( 'sqlite:' . $this->strDatabaseFile ) ;
					$this->strUser        	=   NULL ;
					$this->strPass        	=   NULL ;

					break ;
			}

			if ( in_array( $strType, $this->arrDatabases ) && ( $this->strCharset ) )
				$arrCmds[ ]                	=   "SET NAMES '" . $this->strCharset . "'" ;

			/*
				Create the PDO object that we will later access with $this->pdo
			*/
			$this->pdo                     	=   new PDO
			(
				$strDataSourceName ,
				$this->strUser ,
				$this->strPass ,
				$this->arrOpt
			) ;

			foreach ( $arrCmds as &$strCmd )
                $this->pdo->exec( $strCmd ) ;
        }
       	catch ( PDOException $errMsg )
       	{
			throw new Exception( $errMsg->getMessage( ) ) ;
		}
    }

	/*
        @param array $arrConfig Defaults to [ ]
        @return object (self)
    */
    public function Init( $arrConfig = [ ] )
    {
        $this->__construct( $arrConfig ) ;
        return $this ;
    }
    /*
        Misc functions
        @param boolean $bool
        @return object (self)
    */
	public function Debug( $bool )
	{
		$this->boolDebug    =   ( $bool ?: TRUE ) ;

		return $this ;
	}

    /*
        @return string
    */
	public function Error( )
	{
		return $this->pdo->errorInfo( ) ;
	}
    
    /*
        @return string
    */
	public function LastQuery( )
	{
		return end( $this->arrLogs ) ;
	}

    /*
        @return array
    */
	public function Logs( )
	{
		return $this->arrLogs ;
	}

    /*
        @return array
    */
	public function Info( )
	{
		$arr =
		    [
    			'Server'        =>  'SERVER_INFO'       ,
    			'Driver'        =>  'DRIVER_NAME'       ,
    			'Client'        =>  'CLIENT_VERSION'    ,
    			'Version'       =>  'SERVER_VERSION'    ,
    			'Connection'    =>  'CONNECTION_STATUS'
    		] ;

		foreach ( $arr as $strKey => &$strVal )
			$arr[ $strKey ]     =   $this->pdo->getAttribute( constant( 'PDO::ATTR_' . $strVal ) ) ;

		return $arr ;
	}

    /*
        @return string Sort of unqiue string generation
    */
	public function Unique( )
	{
		return md5( uniqid( uniqid( ) ) ) ;
	}
    
    /*
        @param string $strQuery
        @return pdo::query
    */
    public function Query( $strQuery )
    {
        if ( $this->boolDebug )
            $this->arrLogs[ ]		=   $strQuery ;

        return $this->pdo->query( $strQuery ) ;
    }

    /*
        @param string $strQuery
        @return pdo::exec
    */
    public function Exec( $strQuery )
    {
        if ( $this->boolDebug )
            $this->arrLogs[ ]		=   $strQuery ;

        return $this->pdo->exec( $strQuery ) ;
    }

    /*
        @param string $str
        @return string
    */
	public function Quote( $str )
	{
		return $this->pdo->quote( $str ) ;
	}

    /*
        @param string $str
        @return string
    */
	protected function CTable( $str )
	{
		return ( '"' . $this->strPrefix . $str . '"' ) ;
	}

    /*
        @param string $str
        @return string
    */
	protected function CColumn( $str )
	{
		return ( '"' . $str . '"' ) ;
	}

    /*
        @param array $arrColumns (ref)
        @return string
    */
	protected function PushColumn( &$arrColumns )
	{
	    /*
	        Wildcard
	    */
		if ( $arrColumns === '*' )
			return $arrColumns ;
        
        /*
            If string then make $arrColumns a zero based array
        */
		if ( is_string( $arrColumns ) )
			$arrColumns 					=   [ $arrColumns ] ;

		$arr                           		=	[ ] ;

		foreach ( $arrColumns as $strKey 	=> 	&$strVal )
		{
			if ( is_array( $strVal ) )
				$arr[ ]                 	=   $this->PushColumn( $strVal ) ;
			else
			{
				preg_match( '/([_\-\.a-z0-9]*)\s*\(([_\-a-z0-9]*)\)/i' , $strVal, $arrMatch ) ;

				if ( isset( $arrMatch[ 1 ], $arrMatch[ 2 ] ) )
				{
					$arr[ ]           		=	( $this->CColumn( $arrMatch[ 1 ] ) . ' AS ' . $this->CColumn( $arrMatch[ 2 ] ) ) ;

					$arrColumns[ $strKey ]  =	$arrMatch[ 2 ] ;
				}
				else
					$arr[ ] 				=	$this->CColumn( $strVal ) ;
			}
		}

		return implode( $arr, ',' ) ;
	}

    /*
        @param array $arrIn (ref)
        @return string
    */
	protected function CArray( &$arrIn )
	{
		$arr 			=	[ ] ;

		foreach ( $arrIn as &$val )
			$arr[ ] 	=	( is_int( $val )
								? $val
								: $this->Quote( $val )
							) ;

		return implode( $arr, ',' ) ;
	}

    /*
        @param array $arrIn (ref)
        @param string $strInner (ref)
        @param string $strOuter (ref) Defaults to NULL
        @return string
    */
	protected function Stitch( &$arrIn, &$strInner, $strOuter = NULL )
	{
		$arr 			=	[ ] ;

		foreach ( $arrIn as &$val )
			$arr[ ] 	=	( '(' . $this->Implode( $var, $strInner ) . ')' ) ;

		return implode( $strOuter . ' ', $arr ) ;
	}

    /*
        @param string $strColumn (ref)
        @param string $str (ref)
        @return string
    */
	protected function QuoteFN( $strColumn, &$str )
	{
		return ( ( ( strpos( $strColumn, '#' ) === 0 ) && preg_match( '#^[A-Z0-9\_]*\([^)]*\)$#U' , $str ) ) ? $str : $this->Quote( $str ) ) ;
	}

    /*
        @param string $strVarType (ref)
        @param string $strColumn (ref)
        @param string $strKey (ref)
        @param string $strVal (ref)
    */
	protected function VarType( &$strVarType, &$strColumn, &$strKey, &$strVal )
	{
		switch ( $strVarType )
		{
			case 'NULL':
				$arrWhere[ ] 	= 	( $strColumn . ' IS NOT NULL' ) ;

				break ;

			case 'array':
				$arrWhere[ ] 	= 	( $strColumn . ' NOT IN (' . $this->CArray( $strVal ) . ')' ) ;

				break ;

			case 'integer':
			case 'double':
				$arrWhere[ ] 	= 	( $strColumn . ' != ' . $strVal ) ;

				break ;

			case 'boolean':
				$arrWhere[ ] 	=	( $strColumn . ' != ' . ( $strVal ? '1' : '0' ) ) ;

				break ;

			case 'string':
				$arrWhere[ ] 	=	( $strColumn . ' != ' . $this->QuoteFN( $strKey, $strVal ) ) ;

				break ;
		}
	}

    /*
        @param array $arrIn (ref)
        @param string $strInner
        @param str $strOuter
    */
	protected function Implode( &$arrIn, $strInner, $strOuter = NULL )
	{
		$arrWhere 		=	[ ] ;
        $arrOperators   =   [ '>', '>=', '<', '<=' ] ;
        
		foreach ( $arrIn as $strKey => &$strVal )
		{
			$strVarType	=	gettype( $strVal ) ;

			if ( ( preg_match( '/^(AND|OR)(\s+#.*)?$/i' , $strKey, $arrMatch ) ) && ( $strVarType === 'array' ) )
				$arrWhere[ ] 	=
					(
						( 0 !== count( array_diff_key( $strVal, array_keys( array_keys( $strVal ) ) ) ) )
							? '(' . $this->Implode( $strVal, ' ' . $arrMatch[ 1 ] ) . ')'
							: '(' . $this->Stitch( $strVal, ' ' . $arrMatch[ 1 ], $strInner ) . ')'
					) ;
			else
			{
				preg_match( '/(#?)([\w\.\-]+)(\[(\>|\>\=|\<|\<\=|\=|\!|\<\>|\>\<|\!?~)\])?/i' , $strKey, $arrMatch ) ;

				$strColumn 			=	$this->CColumn( $arrMatch[ 2 ] ) ;

				if ( isset( $arrMatch[ 4 ] ) )
				{
					$strOperator 	=	$arrMatch[ 4 ] ;

					if ( $strOperator === '!' )
						$this->VarType( $strVarType, $strColumn, $strKey, $strVal ) ;

					if ( ( $strOperator === '<>' ) || ( $strOperator === '><' ) )
					{
						if ( $strVarType === 'array' )
						{
							if ( $strOperator === '><' )
								$strColumn 		.=	' NOT';

							if ( ( is_numeric( $strVal[ 0 ] ) ) && ( is_numeric( $strVal[ 1 ] ) ) )
								$arrWhere[ ] 	=	( '(' . $strColumn . ' BETWEEN ' . $strVal[ 0 ] . ' AND ' . $strVal[ 1 ] . ')' ) ;
							else
								$arrWhere[ ] 	=	( '(' . $strColumn . ' BETWEEN ' . $this->Quote( $strVal[ 0 ] ) . ' AND ' . $this->Quote( $strVal[ 1 ] ) . ')' ) ;
						}
					}

					if ( ( $strOperator === '~' ) || ( $strOperator === '!~' ) )
					{
						if ( $strVarType !== 'array' )
							$strVal 			=	[ $strVal ] ;

						$arrLike 				=	[ ] ;

						foreach ( $strVal as &$str )
						{
							$str 				=	strval( $str ) ;

							if ( preg_match( '/^(?!(%|\[|_])).+(?<!(%|\]|_))$/' , $str ) )
								$str 			=	( '%' . $str . '%' ) ;

							$arrLike[ ]			=	( $strColumn . ( $strOperator === '!~' ? ' NOT' : '' ) . ' LIKE ' . $this->QuoteFN( $strKey, $str ) ) ;
						}

						$arrWhere[ ] 			=	implode( ' OR ', $arrLike ) ;
					}

					if ( ( $strOperator === '=' ) )
					{
						if ( $strVarType !== 'array' )
							$strVal 			=	[ $strVal ] ;

						$arrLike 				=	[ ] ;

						foreach ( $strVal as &$str )
						{
							$str 				=	strval( $str ) ;

							$arrLike[ ]			=	( $strColumn . ' = ' . $this->Quote( $str ) ) ;
						}

						$arrWhere[ ] 			=	( '(' . implode( ' OR ', $arrLike ) . ')' ) ;
					}

					if ( in_array( $strOperator, $arrOperators ) )
					{
						$str 					=	( $strColumn . ' ' . $strOperator . ' ' ) ;

						if ( is_numeric( $strVal ) )
							$str 				.=	$strVal ;
						elseif ( strpos( $strKey, '#' ) === 0 )
							$str 				.=	$this->QuoteFN( $strKey, $strVal ) ;
						else
							$str 				.=	$this->Quote( $strVal ) ;

						$arrWhere[ ] 			=	$str ;
					}
				}
				else
					$this->VarType( $strVarType, $strColumn, $strKey, $strVal ) ;
			}
		}

		return implode( $strInner . ' ', $arrWhere ) ;
	}

	/*
	    @param mixed $var (ref)
	*/
	protected function Where( &$var )
	{
		$str                            =   '' ;

		if ( is_array( $var ) )
		{
			$arrKeys                    =   array_keys( $var ) ;
			$arrAND                     =   preg_grep( '/^AND\s*#?$/i', $arrKeys ) ;
			$arrOR                      =   preg_grep( '/^OR\s*#?$/i', $arrKeys ) ;
            
            /*
			$strCondition               =   array_diff_key( $var,
			    array_flip(
				    [ 'AND', 'OR', 'GROUP', 'ORDER', 'HAVING', 'LIMIT', 'LIKE', 'MATCH' ]
			    )
		    ) ;
            */
            
            $strCondition               =   array_diff_key( $var, [ 'AND' => 0, 'OR' => 0, 'GROUP' => 0, 'ORDER' => 0, 'HAVING' => 0, 'LIMIT' => 0, 'LIKE' => 0, 'MATCH' => 0 ] ) ;
            
			if ( $strCondition !== [ ] )
			{
				$strCon                 =   $this->Implode( $strCondition, '' ) ;

				if ( $strCon !== '' )
					$str                =   ( ' WHERE ' . $strCon ) ;
			}
			elseif ( ! empty( $arrAND ) )
				$str                    =   ( ' WHERE ' . $this->Implode( $var[ array_values( $arrAND )[ 0 ] ], ' AND' ) ) ;
			elseif ( ! empty( $arrOR ) )
				$str                    =   ( ' WHERE ' . $this->Implode( $var[ array_values( $arrOR )[ 0 ] ], ' OR' ) ) ;

			if ( isset( $var[ 'MATCH' ] ) )
			{
				$arr                    =   $var[ 'MATCH' ] ;

				if ( ( is_array( $arr ) ) && ( isset( $arr[ 'columns' ], $arr[ 'keyword' ] ) ) )
					$str                .=  ( ( $str !== '' ? ' AND ' : ' WHERE ' ) . ' MATCH ("' . str_replace( '.', '"."', implode( $arr[ 'columns' ], '", "' ) ) . '") AGAINST (' . $this->Quote( $arr[ 'keyword' ] ) . ')' ) ;
			}

			if ( isset( $var[ 'GROUP' ] ) )
			{
				$str                    .=  ( ' GROUP BY ' . $this->CColumn( $var[ 'GROUP' ] ) ) ;

				if ( isset( $var[ 'HAVING' ] ) )
					$str                .=  ( ' HAVING ' . $this->Implode( $var[ 'HAVING' ], ' AND' ) );
			}

			if ( isset( $var[ 'ORDER' ] ) )
			{
				$arrOrder               =   $var[ 'ORDER' ] ;

				if ( is_array( $arrOrder ) )
				{
					$arr                =   [ ] ;

					foreach ( $arrOrder as $strColumn => &$strVal )
						if ( is_array( $strVal ) )
							$arr[ ]     =   ( 'FIELD(' . $this->CColumn( $strColumn ) . ', ' . $this->CArray( $strVal ) . ')' ) ;
						elseif ( ( $strVal === 'ASC' ) || ( $strVal === 'DESC' ) )
							$arr[ ]     =   ( $this->CColumn( $strColumn ) . ' ' . $strVal ) ;
						elseif ( is_int( $strColumn ) )
							$arr[ ]     =   $this->CColumn( $strColumn ) ;
						elseif( $strVal === 'RAND()' )
                            $arr[ ]     =   $strVal ;


					$str                .=  ( ' ORDER BY ' . implode( $arr, ',' ) ) ;
				}
				else
					$str                .=  ( ' ORDER BY ' . $this->CColumn( $arrOrder ) ) ;
			}

			if ( isset( $var[ 'LIMIT' ] ) )
			{
				$varLimit               =   $var[ 'LIMIT' ] ;

				if ( is_numeric( $varLimit ) )
					$str                .=  ( ' LIMIT ' . $varLimit ) ;

				if ( ( is_array( $varLimit ) ) && (	is_numeric( $varLimit[ 0 ] ) ) && (	is_numeric( $varLimit[ 1 ] ) ) )
					if ( $this->strDatabaseType === 'pgsql' )
						$str            .=  ( ' OFFSET ' . $varLimit[ 0 ] . ' LIMIT ' . $varLimit[ 1 ] ) ;
					else
						$str            .=  ( ' LIMIT ' . $varLimit[ 0 ] . ',' . $varLimit[ 1 ] ) ;
			}
		}
		else
			if ( $var !== NULL )
				$str                    .=  ( ' ' . $var ) ;

		return $str ;
	}

    /*
        @param array $strTable (ref)
        @param mixed $varJoin (ref)
        @param mixed $varColumns (ref) Defaults to NULL
        @param mixed $varWhere (ref) Defaults to NULL
        @param mixed $ColumnFN (ref) Defaults to NULL
        @return string
    */
	protected function Context( &$strTable, &$varJoin, &$varColumns = NULL, &$varWhere = NULL, $ColumnFN = NULL )
	{
		//preg_match( '#([a-zA-Z0-9_\-]*)\s*\(([a-zA-Z0-9_\-]*)\)#i', $strTable, $arrMatch ) ;
		
		preg_match( '#([_\-a-z0-9]*)\s*\(([_\-a-z0-9]*)\)#i', $strTable, $arrMatch ) ;

		if ( isset( $arrMatch[ 1 ], $arrMatch[ 2 ] ) )
		{
			$strTable       =   $this->CTable( $arrMatch[ 1 ] ) ;

			$arrQuery       =   ( $this->CTable( $arrMatch[ 1 ] ) . ' AS ' . $this->CTable( $arrMatch[ 2 ] ) ) ;
		}
		else
		{
			$strTable       =   $this->CTable( $strTable ) ;

			$arrQuery       =   $strTable ;
		}

		$arrJoinKey         =   ( is_array( $varJoin ) ? array_keys( $varJoin ) : NULL ) ;

		if ( ( isset( $arrJoinKey[ 0 ] ) ) && ( strpos( $arrJoinKey[ 0 ], '[' ) === 0 ) )
		{
			$arrTableJoin   =   [ ] ;

			$arr            =
			    [
				    '>'     =>  'LEFT'      ,
				    '<'     =>  'RIGHT'     ,
				    '<>'    =>  'FULL'      ,
				    '><'    =>  'INNER'
			    ] ;

			foreach( $varJoin as $arrKey => &$varVal )
			{
				preg_match( '/(\[(\<|\>|\>\<|\<\>)\])?([_\-a-z0-9]*)\s?(\(([_\-a-z0-9]*)\))?/', $arrKey, $arrMatch ) ;

				if ( ( $arrMatch[ 2 ] !== '' ) && ( $arrMatch[ 3 ] !== '' ) )
				{
					if ( is_string( $varVal ) )
						$varVal         =   ( 'USING ("' . $varVal . '")' ) ;
					elseif ( is_array( $varVal ) )
					{
						if ( isset( $varVal[ 0 ] ) )
							$varVal     =   ( 'USING ("' . implode( $varVal, '", "' ) . '")' ) ;
						else
						{
							$arrJoins   =   [ ] ;

							foreach ( $varVal as $strKey => &$strVal )
								$arrJoins[ ]    =
    								(
    									( strpos( $strKey, '.' ) > 0 )
    										? $this->CColumn( $strKey)
    										: ( $strTable . '."' . $strKey . '"' )
    								) .
    								' = ' .	( $this->CTable( isset( $arrMatch[ 5 ] )
																? $arrMatch[ 5 ]
																: $arrMatch[ 3 ] ) . '."' . $strVal . '"' ) ;

							$varVal             =   ( 'ON ' . implode( $arrJoins, ' AND ' ) ) ;
						}
					}

					$strTableName       =   ( $this->CTable( $arrMatch[ 3 ] ) . ' ' ) ;

					if ( isset( $arrMatch[ 5 ] ) )
						$strTableName   .=  ( 'AS ' . $this->CTabble( $arrMatch[ 5 ] ) . ' ' ) ;

					$arrTableJoin[ ]    =   ( $arrJoins[ $arrMatch[ 2 ] ] . ' JOIN ' . $strTableName . $varVal ) ;
				}
			}

			$arrQuery                   .=  ( ' ' . implode( $arrTableJoin, ' ' ) ) ;
		}
		else
		{
			if ( is_null( $varColumns ) )
			{
				if ( is_null( $varWhere ) )
				{
					if ( is_array( $varJoin ) && isset( $ColumnFN ) )
					{
						$varWhere       =   $varJoin ;
						$varColumns     =   NULL ;
					}
					else
					{
						$varWhere       =   NULL ;
						$varColumns     =   $varJoin ;
					}
				}
				else
				{
					$varWhere           =   $varJoin ;
					$varColumns         =   NULL ;
				}
			}
			else
			{
				$varWhere               =   $varColumns ;
				$varColumns             =   $varJoin ;
			}
		}

		if ( isset( $ColumnFN ) )
		{
			if ( $ColumnFN === 1 )
			{
				$varColumn              =   '1' ;

				if ( is_null( $varWhere ) )
					$varWhere           =   $varColumns ;
			}
			else
			{
				if ( empty( $varColumns ) )
				{
					$varColumns         =   '*' ;
					$varWhere           =   $varJoin ;
				}

				$varColumn              =   ( $ColumnFN . '(' . $this->PushColumn( $varColumns ) . ')' ) ;
			}
		}
		else
			$varColumn                  =   $this->PushColumn( $varColumns ) ;

		return ( 'SELECT ' . $varColumn . ' FROM ' . $arrQuery . $this->Where( $varWhere ) ) ;
	}

    /*
        @param string $index (ref)
        @param string $strKey (ref)
        @param string strVal (ref)
        @param array $arrData (ref)
        @param array $arrStack (ref)
    */
	protected function MapData( &$index, &$strKey, &$strVal, &$arrData, &$arrStack )
	{
		if ( is_array( $strVal ) )
		{
			$arr 												=	[ ] ;

			foreach ( $strVal as $varKey => &$varVal )
			{
				if ( is_array( $varVal ) )
				{
					$arrCurrent 								=	$arrStack[ $index ][ $strKey ] ;

					$this->MapData( false, $varKey, $varVal, $arrData, $arrCurrent ) ;

					$arrStack[ $index ][ $strKey ][ $varKey ] 	=	$arrCurrent[ 0 ][ $varKey ] ;
				}
				else
				{
					$this->MapData( false, preg_replace( '#^[_a-z0-9]*\.#i', '', $varVal ), $varKey, $arrData, $arr ) ;

					$arrStack[ $index ][ $strKey ]				=	$arr ;
				}
			}
		}
		else
		{
			if ( $index !== false )
				$arrStack[ $index ][ $strVal ] 					=	$arrData[ $strVal ] ;
			else
			{
				if ( preg_match( '#[_\-\.a-z0-9]*\s*\(([_\-a-z0-9]*)\)#i', $varKey, $arrMatch ) )
					$strKey 									=	$arrMatch[ 1 ] ;

				$arrStack[ $strKey ] 							=	$arrData[ $strKey ] ;
			}
		}
	}

    /*
        @param string $strTable
        @param mixed $varJoin 
        @param mixed $varColumns (ref) Defaults to NULL
        @param mixed $varWhere (ref) Defaults to NULL
        @return array
    */
	public function Select( $strTable, $varJoin, $varColumns = NULL, $varWhere = NULL )
	{
		$varColumn          =   ( ( $varWhere === NULL ) ? $varJoin : $varColumns ) ;
		$Query              =   $this->Query( $this->Context( $strTable, $varJoin, $varColumns, $varWhere ) ) ;

		if ( ! $Query )
			return false ;

		if ( $varColumns === '*' )
			return $Query->fetchAll( PDO::FETCH_ASSOC ) ;

		if ( ( is_string( $varColumn ) && ( $varColumn !== '*' ) ) )
			return $Query->fetchAll( PDO::FETCH_COLUMN ) ;

		$arr                =   [ ] ;
		$int                =   0 ;
		
		while ( $varRow = $Query->fetch( PDO::FETCH_ASSOC ) )
		{
			foreach ( $varColumns as $strKey => &$varVal )
				if ( is_array( $varVal ) )
					$this->MapData( $int, $strKey, $varVal, $varRow, $arr ) ;
				else
					$this->MapData( $int, $strKey, preg_replace( '#^[_a-z0-9]*\.#i', '', $varVal ), $varRow, $arr ) ;

			$int++ ;
		}

		return $arr ;
	}

    /*
        @param string $strKey (ref)
        @param mixed $varVal (ref)
        @param array $arrData (ref)
        @param string $str (ref) Defaults to NULL
    */
	protected function ProdType( &$strKey, &$varVal, &$arrValues, $str = '' )
	{
		switch ( gettype( $varVal ) )
		{
			case 'NULL':
				$arrValues[ ]   =   ( $str . 'NULL' ) ;

				break ;

			case 'array':
				$arrValues[ ]   =   ( $str . $this->Quote( json_encode( $varVal ) ) ) ;

				break ;

			case 'boolean':
				$arrValues[ ]   =   ( $str . ( $varVal ? '1' : '0' ) ) ;

				break ;

			case 'integer':
			case 'double':
			case 'string':
				$arrValues[ ]   =   ( $str . $this->QuoteFN( $strKey, $varVal ) ) ;

				break ;
		}
	}

    /*
        @param string $strTable
        @param array $arrData
        @return integer|array
    */
	public function Insert( $strTable, $arrData )
	{
		$arrIDs         =   [ ] ;

		if ( ! isset( $arrData[ 0 ] ) )
			$arrData    =   [ $arrData ] ;

		foreach ( $arrData as &$var )
		{
			$arrValues  =   [ ] ;
			$arrColumns =   [ ] ;

			foreach ( $var as $strKey => &$varVal )
			{
				$arrColumns[ ]          =   $this->CColumn( $strKey ) ;

                $this->ProdType( $strKey, $varVal, $arrValues ) ;
			}

			$this->Exec( 'INSERT INTO ' . $this->CTable( $strTable ) . ' (' . implode( ', ', $arrColumns ) . ') VALUES (' . implode( $arrValues, ', ' ) . ')' ) ;

			$arrIDs[ ]                  =   $this->pdo->lastInsertId( ) ;
		}

		return ( count( $arrIDs ) > 1 ? $arrIDs : $arrIDs[ 0 ] ) ;
	}

    /*
        @param string $strTable
        @param array $arrData
        @return integer|array
    */
	public function InsertOrUpdate( $strTable, $arrData )
	{
		$arrIDs         =   [ ] ;

		// Check indexed or associative array
		if ( ! isset( $arrData[ 0 ] ) )
			$arrData    =   [ $arrData ] ;

		foreach ( $arrData as &$var )
		{
			$arrValues  =   [ ] ;
			$arrColumns =   [ ] ;

			foreach ( $var as $strKey => &$varVal )
			{
				$arrColumns[ ]          =   $this->CColumn( $strKey ) ;

                $this->ProdType( $strKey, $varVal, $arrValues ) ;
			}

			$this->Exec( 'INSERT OR REPLACE INTO ' . $this->CTable( $strTable ) . ' (' . implode( ', ', $arrColumns ) . ') VALUES (' . implode( $arrValues, ', ' ) . ')' ) ;

			$arrIDs[ ]                  =   $this->pdo->lastInsertId( ) ;
		}

		return ( count( $arrIDs ) > 1 ? $arrIDs : $arrIDs[ 0 ] ) ;
	}
	
    /*
        @param string $strTable
        @param array $arrData 
        @param mixed $varWhere Defaults to NULL
        @return pdo::exec
    */
	public function Update( $strTable, $arrData, $varWhere = NULL )
	{
		$arrValues                      =   [ ] ;

		foreach ( $arrData as $strKey => &$varVal )
		{
			preg_match( '#([\w]+)(\[(\=|\+|\-|\*|\/)\])?#i' , $strKey, $arrMatch ) ;
			
			if ( isset( $arrMatch[ 3 ] ) )
			{
				if ( is_numeric( $varVal ) )
					$arrValues[ ]       =   ( $this->CColumn( $arrMatch[ 1 ] ) . ' = ' . $this->CColumn( $arrMatch[ 1 ] ) . ' ' . $arrMatch[ 3 ] . ' ' . $varVal ) ;
			}
			else
			{
                $this->ProdType( $strKey, $varVal, $arrValues, $this->CColumn( $strKey ) . ' = ' ) ;
			}
		}

		return $this->Exec( 'UPDATE ' . $this->CTable( $strTable ) . ' SET ' . implode( ', ', $arrValues ) . $this->Where( $varWhere ) ) ;
	}

    /*
        @param string $strTable
        @param mixed $varWhere Defaults to NULL
        @return pdo::exec
    */
	public function Delete( $strTable, $varWhere = NULL )
	{
		return $this->Exec( 'DELETE FROM ' . $this->CTable( $strTable ) . $this->Where( $varWhere ) ) ;
	}

    /*
        @param string $strTable
        @param mixed $varColumns 
        @param mixed $varSearch Defaults to NULL
        @param string $strReplace Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return pdo::exec
    */
	public function Replace( $strTable, $varColumns, $varSearch = NULL, $strReplace = NULL, $varWhere = NULL )
	{
		if ( is_array( $varColumns ) )
		{
			$arrQuery               =   [ ] ;

			foreach ( $varColumns as $strColumn => &$varValue )
				foreach ( $varValue as $strReplaceKey => &$strReplaceVal )
					$arrQuery[ ]    =   ( $strColumn . ' = REPLACE(' . $this->CColumn( $strColumn ) . ', ' . $this->Quote( $strReplaceKey ) . ', ' . $this->Quote( $strReplaceVal ) . ')' ) ;

			$arrQuery               =   implode( ', ', $arrQuery ) ;
			$varWhere               =   $varSearch ;
		}
		else
		{
			if ( is_array( $varSearch ) )
			{
				$arrQuery           =   [ ] ;

				foreach ( $varSearch as $strKey => &$strVal )
					$arrQuery[ ]    =   ( $varColumns . ' = REPLACE(' . $this->CColumn( $varColumns ) . ', ' . $this->Quote( $strKey ) . ', ' . $this->Quote( $strVal ) . ')' ) ;

				$arrQuery           =   implode( ', ', $arrQuery ) ;
				$varWhere           =   $strReplace ;
			}
			else
				$arrQuery           =   ( $varColumns . ' = REPLACE(' . $this->CColumn( $varColumns ) . ', ' . $this->Quote( $varSearch ) . ', ' . $this->quote( $strReplace ) . ')' ) ;
		}

		return $this->Exec( 'UPDATE ' . $this->CTable( $strTable ) . ' SET ' . $arrQuery . $this->Where( $varWhere ) ) ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin Defaults to NULL
        @param mixed $varColumns Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Get( $strTable, $varJoin = NULL, $varColumns = NULL, $varWhere = NULL )
	{
		$varColumn          =   ( ( $varWhere === NULL ) ? $varJoin : $varColumns ) ;
		$Query              =   $this->Query( $this->Context( $strTable, $varJoin, $varColumns, $varWhere ) . ' LIMIT 1' ) ;

		if ( $Query )
		{
			$arrData        =   $Query->fetchAll( PDO::FETCH_ASSOC ) ;

			if ( isset( $arrData[ 0 ] ) )
			{
				if ( ( is_string( $varColumn ) && ( $varColumn !== '*' ) ) )
					return $arrData[ 0 ][ preg_replace( '#^[_a-z0-9]*\.#i', '', $varColumn ) ] ;

				if ( $varColumn === '*' )
					return $arrData[ 0 ] ;

				$arr        =   [ ] ;

				foreach ( $varColumns as $strKey => &$varVal )
					if ( is_array( $varVal ) )
						$this->MapData( 0, $strKey, $varVal, $arrData[ 0 ], $arr ) ;
					else
						$this->MapData( 0, $strKey, preg_replace( '#^[_a-z0-9]*\.#i', '', $varVal ), $arrData[ 0 ], $arr) ;

				return $arr[ 0 ] ;
			}
			else
				return false ;
		}
		
		return false ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Exists( $strTable, $varJoin, $varWhere = NULL )
	{
		$Query      =   $this->Query( 'SELECT EXISTS(' . $this->Context( $strTable, $varJoin, $varColumn  =   NULL, $varWhere, 1 ) . ')' ) ;

		if ( $Query )
			return ( $Query->fetchColumn( ) === '1' ) ;

		return false ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin Defaults to NULL
        @param mixed $varColumn Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Count( $strTable, $varJoin = NULL, $varColumn = NULL, $varWhere = NULL )
	{
		$Query      =   $this->Query( $this->Context( $strTable, $varJoin, $varColumn, $varWhere, 'COUNT' ) ) ;

		return ( $Query ? ( 0 + $Query->fetchColumn( ) ) : false ) ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin
        @param mixed $varColumn Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Max( $strTable, $varJoin, $varColumn = NULL, $varWhere = NULL )
	{
		$Query      =   $this->Query( $this->Context( $strTable, $varJoin, $varColumn, $varWhere, 'MAX' ) ) ;

		if ( $Query )
		{
			$var    =   $Query->fetchColumn( ) ;

			return ( is_numeric( $var ) ? ( $var + 0 ) : $var ) ;
		}
		
		return false ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin
        @param mixed $varColumn Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Min( $strTable, $varJoin, $varColumn = NULL, $varWhere = NULL )
	{
		$Query      =   $this->Query( $this->Context( $strTable, $varJoin, $varColumn, $varWhere, 'MIN' ) ) ;

		if ( $Query )
		{
			$var    =   $Query->fetchColumn( ) ;

			return ( is_numeric( $var ) ? ( $var + 0 ) : $var ) ;
		}

		return false ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin
        @param mixed $varColumn Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Average( $strTable, $varJoin, $varColumn = NULL, $varWhere = NULL )
	{
		$Query      =   $this->Query( $this->Context( $strTable, $varJoin, $varColumn, $varWhere, 'AVG' ) ) ;

		return ( $Query ? ( 0 + $Query->fetchColumn( ) ) : false ) ;
	}

    /*
        @param string $strTable
        @param mixed $varJoin
        @param mixed $varColumn Defaults to NULL
        @param mixed $varWhere Defaults to NULL
        @return mixed
    */
	public function Sum( $strTable, $varJoin, $varColumn = NULL , $varWhere = NULL )
	{
		$Query      =   $this->Query( $this->Context( $strTable, $varJoin, $varColumn, $varWhere, 'SUM' ) ) ;

		return ( $Query ? ( 0 + $Query->fetchColumn( ) ) : false ) ;
	}
}
?>
