<?php

class ControllerItem extends PaperWorks
{
    const NoCache   = true ;
    
    protected $objModel ;
    
    public function __construct( )
    {
        $this->objModel     =   &PaperWorks::RegisterModel( 'Account' ) ;
        
        //$this->objModel->CookieLogin( ) ;
    }
    
    public function Index( )
    {
        $this->Home( ) ;
    }
    
    public function Home( )
    {
        if ( ! isset( $_SESSION[ 'intID' ] ) )
            header( 'Location: ' . Config::BasePath . 'Account/Login' ) ; 
            
         header( 'Location: ' . Config::BasePath . 'ua' ) ;
    }
    
    public function Register( )
    {
        header( 'Location: ' . Config::BasePath . 'Account/Home' ) ;
        
        if ( isset( $_SESSION[ 'intID' ] ) )
            header( 'Location: ' . Config::BasePath . 'Account/Home' ) ;
        
        $strError               =   NULL ;
        
        $frm                    =   &PaperWorks::RegisterModel( 'Form' ) ;

        $strFrm                 =    $frm->Create   ( 
            [ ] ,
            [
                'Username'	    =>	
                [
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Username' ,
        		] ,
        		'Password'	    =>	
        		[
        			'Type'	    =>  'password' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Password' ,
        		] ,
        		'PasswordConfirm'	=>	
        		[
        		   	'Type'	    =>  'password' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Confirm Password' ,
        		] ,
        		'TOS'	        =>	
        		[
        			'Type'	    =>  'checkbox' ,
        			'Attr'	    =>  'class="Test" checked' ,
        			'Label'	    =>  'Do you accept the TOS?' ,
        		] , 
        		'Register'	    =>	
        		[
        			'Type'	    =>  'button' ,
        			'Attr'	    =>  'value="d"' ,
        		] ,
            ]
        ) ;

        $varData    = 
        $frm->Validate
        ( 
            [
                'Username'	        =>  NULL ,
        		'Password'	        =>  NULL ,
        		'PasswordConfirm'   =>	NULL ,
        		'TOS'               =>	NULL ,
            ]   
        ) ;
        
        if ( $varData === false )
            return PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Form' => $strFrm, 'FormTitle' => 'Registration form', 'Text' => 'Please sign-up.' ] )->RenderHTML( 'Account/manage.html' ) ;
            
        if ( $varData[ 'Password' ] !== $varData[ 'PasswordConfirm' ] )
            return ;
            
        if( $this->objModel->PasswordStrength( $varData[ 'Password' ] ) )
            return ;

        if ( ! ctype_alnum( $varData[ 'Username' ] ) )
            return ;
            
        if ( ! filter_var( $varData[ 'Email' ], FILTER_SANITIZE_EMAIL ) )
            return ;

        if ( ! $this->objModel->Exists( $varData[ 'Username' ], $varData[ 'Email' ] ) )
            header( 'Location: ' . Config::BasePath . 'Account/Login' ) ;
        else
            $strError   =   'Already exists' ;
            
        PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Form' => $strFrm, 'FormTitle' => 'Registration form', 'Text' => 'Please sign-up.' ] )->RenderHTML( 'Account/manage.html' ) ;
    }

    
    public function Admin( )
    {
        if ( $this->objModel->GetUserPower( $_SESSION[ 'intID' ] ) !== true )
            header( 'Location: ' . Config::BasePath . 'Account/Home' ) ;
            
        if ( isset( $_POST[ 'intDelete' ] ) )
            $this->objModel->Delete( $_POST[ 'intDelete' ] ) ;
            
        if ( isset( $_POST[ 'arrUser' ] ) )
        {
            $arr                =   [ 'strName' => $_POST[ 'arrUser' ][ 'strName' ], 'strPassword' => $this->objModel->Hash( $_POST[ 'arrUser' ][ 'strPassword' ] ) ] ;
            $this->objModel->Update( $_POST[ 'intID'], $arr ) ;
        }
            
        $arrUsers               =   $this->objModel->GetAccounts( ) ; 
        
        $frm                    =   &PaperWorks::RegisterModel( 'Form' ) ;

        $strFrm                 =   $frm->Create   ( 
            [ ] ,
            [
                'Username'	    =>	
                [
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Username' ,
        		] ,
        		'Password'	    =>	
        		[
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Password' ,
        		] ,
        		'Create'	    =>	
        		[
        			'Type'	    =>  'button' ,
        			'Attr'	    =>  'class="bluesteel-btn"' ,
        		] ,
            ]
        ) ;
        
        $varData    = 
        $frm->Validate
        ( 
            [
                'Username'	        =>  NULL ,
        		'Password'	        =>  NULL ,
            ]   
        ) ;
        
        if ( $varData === false )
            return PaperWorks::RegisterCore( 'Template', [ 'Users' => $arrUsers, 'BasePath' => Config::BasePath, 'Error' => NULL, 'Form' => $strFrm, 'FormTitle' => 'Admin account creation form', 'Text' => 'Fill in the field in order to create an account' ] )->RenderHTML( 'Account/admin.html' ) ;
            
        if( $this->objModel->PasswordStrength( $varData[ 'Password' ] ) )
            return PaperWorks::RegisterCore( 'Template', [ 'Users' => $arrUsers, 'BasePath' => Config::BasePath, 'Error' => NULL, 'Form' => 'Password is too weak', 'FormTitle' => 'Admin account creation form', 'Text' => 'Fill in the field in order to create an account' ] )->RenderHTML( 'Account/admin.html' ) ;

        if ( ! ctype_alnum( $varData[ 'Username' ] ) )
            return PaperWorks::RegisterCore( 'Template', [ 'Users' => $arrUsers, 'BasePath' => Config::BasePath, 'Error' => NULL, 'Form' => 'Username must be alphanumeric!', 'FormTitle' => 'Admin account creation form', 'Text' => 'Fill in the field in order to create an account' ] )->RenderHTML( 'Account/admin.html' ) ;
            

        if ( ! $this->objModel->Exists( $varData[ 'Username' ] ) )
        {
            $this->objModel->Create( $varData[ 'Username' ], $varData[ 'Password' ] ) ;
            return PaperWorks::RegisterCore( 'Template', [ 'Users' => $arrUsers, 'BasePath' => Config::BasePath, 'Error' => 'Account was created!', 'Form' => $strFrm, 'FormTitle' => 'Admin account creation form', 'Text' => 'Fill in the field in order to create an account' ] )->RenderHTML( 'Account/admin.html' ) ;
        }
        else
            return PaperWorks::RegisterCore( 'Template', [ 'Users' => $arrUsers, 'BasePath' => Config::BasePath, 'Error' => 'Account is already taken', 'Form' => $strFrm, 'FormTitle' => 'Admin account creation form', 'Text' => 'Fill in the field in order to create an account' ] )->RenderHTML( 'Account/admin.html' ) ;      
    }    
    
    public function Login( )
    {
        if ( isset( $_SESSION[ 'intID' ] ) )
            header( 'Location: ' . Config::BasePath . 'Account/Home' ) ;
            
        $strError               =   NULL ;
        
        $frm                    =   &PaperWorks::RegisterModel( 'Form' ) ;

        $strFrm                 =    $frm->Create   ( 
            [ ] ,
            [
                'Username'	    =>	
                [
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Username' ,
        		] ,
        		'Password'	    =>	
        		[
        			'Type'	    =>  'password' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Password' ,
        		] ,
        		'g-recaptcha-response'=>
        		[
        		    'Type'      =>  'captcha' ,
        		] ,
        		'Login'         =>	
        		[
        			'Type'	    =>  'button' ,
        		    'Attr'	    =>  'class="bluesteel-btn"' ,
        		] ,
            ]
        ) ;

        $varData    = 
        $frm->Validate
        ( 
            [
                'Username'	        =>  NULL ,
        		'Password'	        =>  NULL ,
        		'g-recaptcha-response'=>  NULL 
            ]   
        ) ;
        
        require_once( __DIR__ . '/../System/DumbStorage/ReCaptcha/autoload.php' ) ;
        
        $objRecaptcha   =   new \ReCaptcha\ReCaptcha( Config::CaptchaServerKey ) ;
        
        $objResponse    =   $objRecaptcha->verify( $_POST[ 'g-recaptcha-response' ], $_SERVER[ 'REMOTE_ADDR' ] ) ;
        
        if ( ! $objResponse->isSuccess( ) )
            return PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => 'Please fill in the captcha', 'Form' => $strFrm, 'FormTitle' => 'Login form', 'Text' => 'Please login in order to get access to the site.' ] )->RenderHTML( 'Account/manage.html' ) ;
        
        if ( $varData === false )
            return PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => $strError, 'Form' => $strFrm, 'FormTitle' => 'Login form', 'Text' => 'Please login in order to get access to the site.' ] )->RenderHTML( 'Account/manage.html' ) ;
        
        if ( $bool = $this->objModel->Login( $varData[ 'Username' ], $varData[ 'Password' ] ) === false )
            $strError   =   'Please check your credentials.' ;
        else 
            header( 'Location: ' . $_SERVER[ 'HTTP_REFERER' ] ) ;
            
        PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => $strError, 'Form' => $strFrm, 'FormTitle' => 'Login form', 'Text' => 'Please login in order to get access to the site.' ] )->RenderHTML( 'Account/manage.html' ) ;
    }
 
    public function Logout( )
    {
        $this->objModel->Logout( ) ;
        
        header( 'Location: ' . Config::BasePath . 'Account/Home' ) ;
    }
    
    public function Settings( )
    {
        if ( ! isset( $_SESSION[ 'intID' ] ) )
            header( 'Location: ' . Config::BasePath . 'Account/Login' ) ;
            
        $frm                    =   &PaperWorks::RegisterModel( 'Form' ) ;

        $strFrm                 =    $frm->Create   ( 
            [ ] ,
            [   
                'Old Password'	=>	
                [
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'Old Password' ,
        		] ,
                'Password'	    =>	
                [
        			'Type'	    =>  'text' ,
        			'Attr'	    =>  'class="Test"' ,
        			'Label'	    =>  'New Password' ,
        		] ,
        		'Accept'	    =>	
        		[
        			'Type'	    =>  'button' ,
        			'Attr'	    =>  'class="bluesteel-btn"' ,
        		] ,
            ]
        ) ;

        $varData    = 
        $frm->Validate
        ( 
            [
                'Old Password'      =>  NULL ,
        		'Password'	        =>  NULL 
            ]   
        ) ;
        
        if ( $varData === false )
            return PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => $strError, 'Form' => $strFrm, 'FormTitle' => 'Settings', 'Text' => 'tba' ] )->RenderHTML( 'Account/manage.html' ) ;
            
        if ( $varData[ 'Password' ] !== $varData[ 'Old Password' ] )
            return PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => $strError, 'Form' => $strFrm, 'FormTitle' => 'Settings', 'Text' => 'tba' ] )->RenderHTML( 'Account/manage.html' ) ;
            
        $varData[ 'Password' ]      =   $this->objModel->Hash( $varData[ 'Password' ] ) ;
        unset( $varData[ 'Old Password' ] ) ;
            
        if ( $this->objModel->Exists( $strName, $strMail ) === true )
        {
            PaperWorks::RegisterCore( 'Template', [ 'BasePath' => Config::BasePath, 'Error' => $strError, 'Form' => $strFrm, 'FormTitle' => 'Settings', 'Text' => 'tba' ] )->RenderHTML( 'Account/manage.html' ) ;
            return $this->objModel->Update( $strID, $strName, $strPassword, $strMail ) ;
        }
    }
    
}
