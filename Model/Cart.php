<?php

/*
@class PaperWorksCart
*/

class PaperWorksCart
{

	/*
	    @var string
	*/
	public $product_id_rules = '\.a-z0-9_-';

	/*
	    @var string
	*/
	public $product_name_rules = '\w \-\.\:';

	/*
	    @var bool
	*/
	public $product_name_safe = TRUE;

	// --------------------------------------------------------------------------

	/*
	    @var array
	*/
	public $arrContents      =   [ ] ;

	/*
	    @param	array
	    @return	void
	*/
	public function __construct( )
	{
		//$this->arrContents = $this->CI->session->userdata('cart_contents');
		
		if ( $this->arrContents === NULL )
	        $this->arrContents      =   [ 'intCartTotal' => 0, 'intTotalItems' => 0, 'intShipping' => 0, 'intVAT' => 0, 'intCartFinalTotal' => 0 ] ;
	        
	    session_start( ) ;
	    
	    if ( !isset( $_SESSION[ 'arrCart' ] ) )
	        $_SESSION[ 'arrCart' ]  =   [ ] ;
	}

	/**
	 * Insert items into the cart and save it to the session table
	 *
	 * @param	array
	 * @return	bool
	 */
	public function Insert( $arrItems = [ ] )
	{
		if ( ( ! is_array( $arrItems) ) || ( count( $arrItems ) === 0 ) )
		    return false ;

		$bool                   =   false ;
		if ( isset( $arrItems[ 'ID' ] ) )
			if ( ( $intRowID    =   $this->iInsert( $arrItems ) ) )
				$bool           =   true ;
		else
			foreach ( $arrItems as $varVal )
        		if ( ( is_array( $varVal ) ) && ( isset( $varVal[ 'ID' ] ) ) )
					if ( $this->iInsert( $varVal ) )
						$bool   =   true ;

		if ( $bool === true )
		{
			$this->iSaveCart( ) ;
			return ( isset( $intRowID ) ? $intRowID : true ) ;
		}

		return false ;
	}

	// --------------------------------------------------------------------

	/*
	 * @param	array
	 * @return	bool
	 */
	protected function iInsert( $arrItems = [ ] )
	{
		if ( ( ! is_array( $arrItems) ) || ( count( $arrItems ) === 0 ) )
		    return false ;

		if ( ! isset( $arrItems[ 'ID' ], $arrItems[ 'Quantity' ], $arrItems[ 'Price' ], $arrItems[ 'Name' ] ) )
			return false ;

		$arrItems[ 'Quantity' ] =   (float) $arrItems[ 'Quantity' ] ;

		if ( $arrItems['Quantity'] === 0 )
			return false ;

		if ( ! preg_match( '/^[' . $this->product_id_rules . ']+$/i', $arrItems[ 'ID' ] ) )
			return false ;

		if ( $this->product_name_safe && ( ! preg_match( '/^[' . $this->product_name_rules . ']+$/i' . (UTF8_ENABLED ? 'u' : '' ), $arrItems[ 'Name' ] ) ) )
			return false ;


	
    	$arrItems[ 'Price' ]            =   (float) $arrItems[ 'Price' ] ;

        $intRowID                       =   ( isset( $arrItems[ 'Options' ] ) && count( $arrItems[ 'Options' ] ) > 0 ) ? md5( $arrItems[ 'ID' ] . serialize( $arrItems[ 'Options' ] ) ) : md5( $arrItems[ 'ID' ] ) ;
	    
	    /*
		if (isset($arrItems['options']) && count($arrItems['options']) > 0)
			$rowid = md5($arrItems['id'].serialize($arrItems['options']));
		else
			$rowid = md5($arrItems['id']);
        */
        
		$intOld                         =   isset( $this->arrContents[ $intRowID ][ 'Quantity' ] ) ? ( (int) $this->arrContents[ $intRowID ][ 'Quantity' ] ) : 0 ;

		$arrItems[ 'RowID' ]            =   $intRowID ;
		$arrItems[ 'Quantity' ]         +=  $intOld ;
		$this->arrContents[ $intRowID ] =   $arrItems ;

		return $intRowID ;
	}

	// --------------------------------------------------------------------

	/*
	 * @param	array
	 * @return	bool
	 */
	public function Update( $arrItems = [ ] )
	{
	    if ( ( ! is_array( $arrItems) ) || ( count( $arrItems ) === 0 ) )
		    return false ;

		$bool                   =   false ;
		if ( isset( $arrItems[ 'RowID' ] ) )
			if ( $this->iUpdate( $arrItems ) === true )
				$bool           =   true ;
		else
			foreach ( $arrItems as $varVal )
        		if ( ( is_array( $varVal ) ) && ( isset( $varVal[ 'ID' ] ) ) )
					if ( $this->iUpdate( $varVal ) )
						$bool   =   true ;

		if ( $bool === true )
		{
			$this->iSaveCart( ) ;
			return true ;
		}

		return false ;
	}

	/*
	 * @param	array
	 * @return	bool
	 */
	protected function iUpdate( $arrItems   = [ ] )
	{
		// Without these array indexes there is nothing we can do
		if ( ! isset( $arrItems[ 'RowID' ] , $this->arrContents[ $arrItems[ 'RowID' ] ] ) )
			return false ;

		// Prep the quantity
		if ( isset( $arrItems[ 'Quantity' ] ) )
		{
			$arrItems[ 'Quantity' ]         =   (float) $arrItems[ 'Quantity' ] ;
			// Is the quantity zero?  If so we will remove the item from the cart.
			// If the quantity is greater than zero we are updating
			if ( $arrItems[ 'Quantity' ] === 0 )
			{
				unset( $this->arrContents[ $arrItems[ 'RowID' ] ] ) ;
				return true ;
			}
		}

		// find updatable keys
		$arrKeys                =   array_intersect( array_keys( $this->arrContents[ $arrItems[ 'RowID' ] ] ), array_keys( $arrItems ) ) ;
		// if a price was passed, make sure it contains valid data
		if ( isset( $arrItems[ 'Price' ] ) )
			$arrItems[ 'Price' ]=   (float) $arrItems[ 'Price' ] ;
		
		// product id & name shouldn't be changed
		foreach ( array_diff( $arrKeys, [ 'ID', 'Name' ] ) as $strKey )
			$this->arrContents[ $arrItems[ 'Price' ] ][ $strKey ]   =   $arrItems[ $strKey ] ;

		return true ;
	}

	// --------------------------------------------------------------------

	/**
	 * Save the cart array to the session DB
	 *
	 * @return	bool
	 */
	protected function iSaveCart()
	{
	    /*
	        Price modifiers
	    */
	    $boolFlatShippingRate       =   false ;
	    $intShippingRate            =   20 ;
	    $intVAT                     =   21 ;
	    
		// Let's add up the individual prices and set the cart sub-total
		$this->arrContents[ 'intTotalItems' ]           =   $this->arrContents[ 'intCartTotal' ]  =   0 ;
		
		foreach ( $this->arrContents as $varKey => $arrVal )
		{
			// We make sure the array contains the proper indexes
			if ( ! is_array( $arrVal ) OR ! isset( $arrVal[ 'Price' ], $arrVal[ 'Quantity' ] ) )
				continue ;

			$this->arrContents[ 'intCartTotal' ]        +=  ( $arrVal[ 'Price' ] * $arrVal[ 'Quantity' ] ) ;
			$this->arrContents[ 'intTotalItems' ]       +=  $arrVal[ 'Quantity' ] ;
			$this->arrContents[ $varKey ][ 'subtotal' ] =   ( $this->arrContents[ $varKey ][ 'Price' ] * $this->arrContents[ $varKey ][ 'Quantity' ] ) ;
		}
		
		if ( $boolFlatShippingRate === true )
		    $this->arrContents[ 'intShipping' ]         =   $this->arrContents[ 'intCartTotal' ] * ( $intShippingRate / 100 ) ;
	    else
		    $this->arrContents[ 'intShipping' ]         =   $intShippingRate ;
		    
		if ( $intVAT > 0 )
		    $this->arrContents[ 'intVAT' ]              =   $this->arrContents[ 'intCartTotal' ] * ( $intVAT / 100 ) ;
		    
	    $this->arrContents[ 'intCartFinalTotal' ]       =   $this->arrContents[ 'intCartTotal' ] + $this->arrContents[ 'intShipping' ] + $this->arrContents[ 'intVAT' ] ;   

		// Is our cart empty? If so we delete it from the session
		if ( count( $this->arrContents ) <= 2 )
		{
			//$this->CI->session->unset_userdata('cart_contents');
            $_SESSION[ 'arrCart' ]  =   [ ] ;
			// Nothing more to do... coffee time!
			return false ;
		}

		// If we made it this far it means that our cart has data.
		// Let's pass it to the Session class so it can be stored
		//$this->CI->session->set_userdata(array('cart_contents' => $this->_cart_contents));
        $_SESSION[ 'arrCart' ]      =   $this->arrContents ;
		// Woot!
		return true ;
	}

	// --------------------------------------------------------------------

	/**
	 * Cart Total
	 *
	 * @return	int
	 */
	public function Total( )
	{
		return $this->arrContents[ 'intCartTotal' ] ;
	}

	// --------------------------------------------------------------------

	/**
	 * Remove Item
	 *
	 * Removes an item from the cart
	 *
	 * @param	int
	 * @return	bool
	 */
	 public function Remove( $intRowID )
	 {
		unset( $this->arrContents[ $intRowID ] ) ;
		$this->iSaveCart( ) ;
		return true ;
	 }

	// --------------------------------------------------------------------

	/**
	 * Total Items
	 *
	 * Returns the total item count
	 *
	 * @return	int
	 */
	public function TotalItems( )
	{
		return $this->arrContents[ 'intTotalItems' ] ;
	}

	// --------------------------------------------------------------------

	/**
	 * Cart Contents
	 *
	 * Returns the entire cart array
	 *
	 * @param	bool
	 * @return	array
	 */
	public function Contents( $boolNewsest = false )
	{
		$arrCart        =   ( ( $boolNewsest ) ? array_reverse( $this->arrContents ) : $this->arrContents ) ;

		// Remove these so they don't create a problem when showing the cart table
		unset( $arrCart[ 'intCartTotal' ] ) ;
		unset( $arrCart[ 'intTotalItems' ] ) ;
		unset( $arrCart[ 'intShipping' ] ) ;
		unset( $arrCart[ 'intVAT' ] ) ;
		unset( $arrCart[ 'intCartFinalTotal' ] ) ;

		return $arrCart ;
	}

	// --------------------------------------------------------------------

	/**
	 * Get cart item
	 *
	 * Returns the details of a specific item in the cart
	 *
	 * @param	string	$row_id
	 * @return	array
	 */
	public function GetItem( $intRowID )
	{
		return ( ( in_array( $intRowID, [ 'intCartTotal', 'intTotalItems', 'intShipping', 'intVAT', 'intCartFinalTotal' ], true ) || ( ! isset( $this->arrContents[ $intRowID ] ) ) ) ? false : $this->arrContents[ $intRowID ] ) ;
	}

	// --------------------------------------------------------------------

	/**
	 * Has options
	 *
	 * Returns TRUE if the rowid passed to this function correlates to an item
	 * that has options associated with it.
	 *
	 * @param	string	$row_id = ''
	 * @return	bool
	 */
	public function HasProductOptions( $intRowID )
	{
	    return ( isset( $this->arrContents[ $intRowID ][ 'Options' ] ) && ( count( $this->arrContents[ $intRowID ][ 'Options' ] ) !== 0 ) ) ;
	}

	// --------------------------------------------------------------------

	/**
	 * Product options
	 *
	 * Returns the an array of options, for a particular product row ID
	 *
	 * @param	string	$row_id = ''
	 * @return	array
	 */
	public function ProductOptions( $intRowID )
	{
		return ( isset( $this->arrContents[ $intRowID ][ 'Options' ] ) ? $this->arrContents[ $intRowID ][ 'Options' ] : [ ] ) ;
	}

	// --------------------------------------------------------------------

	/**
	 * Format Number
	 *
	 * Returns the supplied number with commas and a decimal point.
	 *
	 * @param	float
	 * @return	string
	 */
	public function FormatNumber( $i = '' )
	{
		return ( ( $i === '') ? '' : number_format( (float) $i, 2, '.', ',' ) ) ;
	}

	// --------------------------------------------------------------------

	/**
	 * Destroy the cart
	 *
	 * Empties the cart and kills the session
	 *
	 * @return	void
	 */
	public function Destroy( )
	{
		$this->arrContents      =   [ 'intCartTotal' => 0, 'intTotalItems' => 0, 'intShipping' => 0, 'intVAT' => 0, 'intCartFinalTotal' => 0 ] ;
		unset( $_SESSION[ 'arrCart' ] ) ;
	}

}