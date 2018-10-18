<?php

defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.idx.idx_property_mapper');

class addon_idxn extends idx_property_mapper
{
	 /*
  	* @var string
	  */
    protected $_api = 'http://192.168.5.193/';

   /*
  	* @var array
  	*/
    protected $idxUserCredentials;
    
   /*
	  * @var array
	  */
    protected $mlsInfo;
    

   /*
	  * @var int
	  */
    protected $stepsDone;
    
   /*
	  * @var int
	  */
    protected $userId;
    
   /*
  	* @var string
  	*/
    protected $token;
    
   /*
    * @var int
    */
    protected $wpUserId;
   
    public function __construct()
    {
       $this->idxUserCredentials = get_option('wpl_addon_idx_user_credentials');
       $this->stepsDone = get_option('wpl_addon_idx_user_steps_done');
       $this->mlsInfo = get_option('wpl_addon_idx_mls_data');

       if ( $this->stepsDone >= 1  && false == $this->idxUserCredentials && !is_array( $this->idxUserCredentials ) ) {
           wp_send_json(array(
            'status'  => 404,
            'message' => 'Authorize First'
           ));
       }

       if ( $this->stepsDone >= 2  && false == $this->mlsInfo && !is_array( $this->mlsInfo ) ) {
           wp_send_json(array(
             'status' => 404,
             'message' => 'First you have to choose MLS Provider.'
           ));
       }


       $this->userId    = self::_('user_id',$this->idxUserCredentials);
       $this->token     = self::_('token',$this->idxUserCredentials);
       $this->wpUserId  = self::_('wp_user_id',$this->idxUserCredentials);

    }



    /*
    * @desc register user in idx cache server
    * @param array $fields
    * @return array 
    */
	public static function register( array $fields )
	{
     if ( empty( $fields ) ) {
         return array(
           'status'  => 500,
           'message' => 'Fill all required fields'
         );
       }
        
       $apiEndpoint = (new static)->_api.'api/create-user/'; 
        
       $fields['phone_number'] = '+'.$fields['phone_number'];
       
       $request = wp_remote_post($apiEndpoint,array(
        'body' => $fields
       ));
       

       
       self::checkRequestError( $request );
       
       $statusCode = wp_remote_retrieve_response_code($request);
      
       if ($statusCode != 201) {
           return array(
            'status' => $statusCode,
            'message' => json_decode(self::_('body',$request),true)
           );
       }

       # if user registered succesfully

       $idxUser = (array) json_decode(self::_('body',$request));
       $idxUser['wp_user_id'] = get_current_user_id();

       self::addOption( 'wpl_addon_idx_user_credentials' ,$idxUser );
       self::addOption('wpl_addon_idx_user_steps_done',1);


       return array(
         'status'  => 201,
         'message' => 'Idx user created succesfully'
       );

	}

    
 /*
  * @desc Get all available MLS Providers
  * @return array 
  */
	public function getProviders()
	{
		
		$apiEndpoint = $this->_api.'api/providers/';

		$request = wp_remote_get($apiEndpoint,array(
          'headers' => array(
             'Authorization' => 'Token '.$this->token
          )
     ));

    self::checkRequestError( $request );
       
    $statusCode = wp_remote_retrieve_response_code($request);
    
    if ($statusCode != 200) {
       return array(
         'status' => $statusCode,
         'message' => json_decode(self::_('body',$request),true)
         );
     }

    return array(
            'status' => $statusCode,
            'message' => json_decode(self::_('body',$request))
     );

        
	}

   /*
    * @desc Save chosen provider in idx cache server
    * @param array $fields
    * @return array 
    */
	public  function save( array $fields )
	{
       if ( empty( $fields ) ) {
         return array(
           'status'  => 500,
           'message' => 'Fill all required fields'
         );
       }
       
       $postBody = array(
          'user_id' => $this->userId,
          'mls_id'  => self::_('mls_id',$fields)
       );
        
       $apiEndpoint = $this->_api.'api/choose-provider/';

       $request = wp_remote_post($apiEndpoint, array(
         'headers' => array(
            'Authorization' => 'Token '.$this->token
         ),
         'body' => $postBody
       ));

       self::checkRequestError( $request );
       
       $statusCode = wp_remote_retrieve_response_code($request);
      
       if ($statusCode != 201) {
        return array(
            'status' => $statusCode,
            'message' => json_decode(self::_('body',$request),true)
           );
       }

       self::addOption('wpl_addon_idx_mls_data',$fields);
       self::addOption('wpl_addon_idx_user_steps_done',2);

       return array(
          'status' => 200,
          'message' => 'MLS data chosen sucessfully'
       );
   }

    
    /*
    * @desc Get chosen MLS Provider
    * @return array 
    */
	public  function getChosenProvider()
	{
	   $apiEndpoint =  $this->_api.'api/selected-provider/';
       
     $request = wp_remote_post($apiEndpoint,array(
         'headers' => array(
            'Authorization' => 'Token '.$this->token
         ),
         'body' => array(
           'user_id' => $this->userId
         )
       ));
       
       self::checkRequestError( $request );

       $statusCode = wp_remote_retrieve_response_code( $request);
   
       if ($statusCode != 200) {
         return array(
            'status' => $statusCode,
            'message' => json_decode(self::_('body',$request),true)
           );
       }
        
        return array(
          'status' => 200,
          'message' => json_decode(self::_('body',$request))
       );        
    }

    
    /*
    * @desc Get required fields for payment
    * @return array 
    */
    public  function getPaymentCreds()
    {
         $mlsId = self::_('mls_id',$this->mlsInfo);
         $mlsProvider = self::_('provider',$this->mlsInfo);

         return array(
            'status' => 200,
            'message' => array(
               'user_id' => self::_('user_id',$this->idxUserCredentials),
               'provider_id'  => $mlsId,
               'mls_provider' => $mlsProvider,
               'token' => self::_('token',$this->idxUserCredentials)
              )
          );
       }

       
      /*
	   * @desc Check if user paid 
	   * @return array 
	   */
       public  function checkPayment ()
       {
    	  $apiEndpoint = $this->_api.'api/check-payment/'.$this->userId;

	     	$request = wp_remote_get($apiEndpoint,array(
          'headers' => array(
             'Authorization' => 'Token '.$this->token
          )
         ));
		
	       	self::checkRequestError( $request );  

          $statusCode = wp_remote_retrieve_response_code( $request );

          return array(
            'status'  => $statusCode,
            'message' => self::_('response.message',$request)
          );
          
        
     }

     
     /*
    * @desc Listing Configuration
    * @param array $fields
    * @return array 
    */
     public function configure( array $fields )
     {
     	if ( empty( $fields ) ) {
         return array(
           'status'  => 500,
           'message' => 'Fill all required fields'
         );
        }
       
        $apiEndpoint = $this->_api.'api/configuration/';

        $fields['user_id'] = $this->userId;
        $fields['provider_id'] =  self::_('mls_id',$this->mlsInfo);
         
        $request = wp_remote_post($apiEndpoint,array(
	          'headers' => array(
	             'Authorization' => 'Token '.$this->token
	          ),
	          'body' => $fields
          ));

//         echo '<pre>'.print_R($fields,1).'</pre>'; 
//         echo '<pre>'.print_R($request,1).'</pre>'; 
// die;

       self::checkRequestError( $request );

       $statusCode = wp_remote_retrieve_response_code( $request);

        if ( $statusCode != 201 ) {
             return array(
               'status'  => $statusCode,
               'message' => json_decode(self::_('body',$request),true)
             );
         }
         
         self::addOption('wpl_addon_idx_user_steps_done',4);
         
         $this->mapMlsProperties();

         return array(
             'status' => $statusCode,
             'message' => 'User configuration saved succesfully'
         );
     }


     protected function mapMlsProperties()
     {
        $apiEndpoint = $this->_api.'api/listing-types/';

         $request = wp_remote_post($apiEndpoint,array(
            'headers' => array(
               'Authorization' => 'Token '.$this->token
            ),
            'body' => array(
              'mls_id' => self::_('mls_id',$this->mlsInfo)
            )
          ));
         

         self::checkRequestError( $request );

         $statusCode = wp_remote_retrieve_response_code( $request);
         
         if ( $statusCode != 200 ) {
             return array(
               'status'  => $statusCode,
               'message' => json_decode(self::_('body',$request),true)
             );
         }

         $jsonBody = json_decode(self::_('body',$request),true);
         
         $propertyTypes = array();
         
         if (count($jsonBody)) {
            
            foreach ($jsonBody as $k => $propertyType) {
               $propertyTypes[] = self::_('property_type',$propertyType);
            }

           if (!count($propertyTypes)) {
               return;
           }

           $this->mapPTypes( $propertyTypes );

         }

     }


     public function getStatus()
     { 
         $chosenMlsData = $this->getChosenProvider();
         
         if (isset($chosenMlsData['message']) && $chosenMlsData['status'] == 200) {
             $chosenMlsData['message']->configStatus = self::_('status',$this->checkConfigStatus());
         }

         return $chosenMlsData;
         
     }

     
     public function checkConfigStatus()
     {
        $apiEndpoint = $this->_api.'api/check-status/'.$this->userId;
        $request = wp_remote_get($apiEndpoint,array(
           'headers' => array(
               'Authorization' => 'Token '.$this->token
            )
        ));

        self::checkRequestError( $request );
        
        return json_decode(self::_('body',$request));
     }


     public function backStep( $stepValue )
     {
        $allowedActions = array(
           'register',
           'provider'
        );
        
        if (!in_array($stepValue, $allowedActions)) {
            wp_send_json(array(
              'status'  => 404,
               'message' => 'Step Not Allowed'
            ));
        }


        $apiEndpoint = $this->_api.'api/delete-activity/'.trim($stepValue);

        $request = wp_remote_get($apiEndpoint,array(
           'headers' => array(
               'Authorization' => 'Token '.$this->token
            )
        ));

      self::checkRequestError($request);

      $statusCode = wp_remote_retrieve_response_code( $request );
       
      if ( $statusCode != 200 ) {
        return array(
          'status' => $statusCode,
          'message' => json_decode(self::_('body',$request),true)
        ); 
       }

      self::addOption('wpl_addon_idx_user_steps_done',$this->stepsDone - 1);
      
      if ( $stepValue == 'register' ) {
        delete_option('wpl_addon_idx_user_credentials');
      }elseif ( $stepValue == 'provider' ){
        delete_option('wpl_addon_idx_mls_data');
      }

      return array(
        'status'  => 200,
        'message' => 'previous step'
      );


     }

    private static function addOption($optionName, $data) {

        return ( get_option($optionName) === false) ? add_option($optionName, $data) : update_option($optionName, $data);
    }
   

    private static function checkRequestError( $request )
    {
        if (is_wp_error($request)) {
          wp_send_json(array(
            'status' => 500,
            'message' => $request->get_error_message()
          ));
       }
     }

    
   /*
    * @desc Import Listings from cache server,
    */
    public function import( $request, $fromAPI = true )
    {
      if ($fromAPI) {
        $this->checkAccess( self::_('token',$request->get_params()) ); 

  
        $jsonProperty = $request->get_json_params();
        
        if (!is_array($jsonProperty) || $request->get_header('content-type') != 'application/json') {
            wp_send_json_error(array(
               'message' => 'Wrong request type, Content type have to be application/json'
            ),400);
         }
      }else {
        $jsonProperty = $request;
      }
       
       global $wpdb;

       

       $pid = wpl_property::create_property_default($this->wpUserId);


       $mappedResult = $this->map($jsonProperty); 

        $updateQuery = $wpdb->update($wpdb->prefix.'wpl_properties',$mappedResult,array(
          'id' => $pid
       ));
      
      if (!$updateQuery) {
          if($fromAPI) {
            wp_send_json_error(array(
              'message' => 'There was an error. Please contact administrator'
            ),500);
          }
          
          return array(
              'status' => 500,
              'message' => 'There was an error. Please contact administrator'
            );
      }
      
      $this->setLocations(self::_('address.0',$jsonProperty),$pid,$wpdb);
      $this->saveExternalImages(self::_('image',$jsonProperty),$pid,$wpdb);
      $this->setFeatures(self::_('features',$jsonProperty),$pid,$wpdb);
      $this->setPricePeriod(self::_('price_period',$jsonProperty),$pid,$wpdb);
      $this->getLotAreaUnitId(self::_('lot_area_unit',$jsonProperty),$pid,$wpdb);
      
      

      if (!wpl_property::finalize($pid,'edit',$this->wpUserId)){
        if ($fromAPI) {
          wp_send_json_error(array(
              'message' => 'Cant finalize property'
            ));
        }
         return array(
           'status' => 500,
            'message' => 'Cant finalize property'
         ); 
      }

      if ($fromAPI) {
          wp_send_json_success(array(
          'message' => 'Property created succesfully'
        ),201);
      }
        return array(
          'status' => 201,
          'pid' => $pid,
          'message' => 'Property created succesfully'
        );
        
   }

   public function getIdxUserCredentials()
   {
      return $this->idxUserCredentials;
   }

   public function importTrialListings()
   {
      $apiEndpoint = $this->_api.'api/trial-data';
      
      $request = wp_remote_get($apiEndpoint,array(
         'headers' => array(
            'Authorization' => 'Token '.$this->token
         )
       ));



      self::checkRequestError( $request ); 
      
      $statusCode = wp_remote_retrieve_response_code( $request );
      
      if ( $statusCode != 200 ) {
        return array(
          'status' => $statusCode,
          'message' => "Cannot Fetch Listings"
        ); 
       }

       $jsonBody = json_decode(self::_('body',$request),true);
        
        if (!count($jsonBody)) {
          return array(
            'status' => 404,
             'message' => "There is not active listing to import"
           ); 
        }
        
        $lastIimportError = array();
        $savedTrialPids = array();



        foreach ( $jsonBody as $key => $json ) {
            $imported = $this->import($json,false);
            $savedTrialPids[] = self::_('pid',$imported);
            if (self::_('status',$imported) != 201) {
                 $lastIimportError = $imported;
            }  
        }
        
        if (count($lastIimportError) > 0) {
           return $lastIimportError;
        }
        

        self::addOption('wpl_idx_addon_trial_imported',1);
        self::addOption('wpl_idx_addon_saved_trial_pids',$savedTrialPids);

        return array(
          'status' => 201,
          'message' => 'Properties imported succesfully'
         );
   }

   public function resetTrialListings()
   {
    $trialPids = get_option('wpl_idx_addon_saved_trial_pids');
    
     if ( get_option('wpl_idx_addon_trial_imported') != 1 || $trialPids === false) {
        return array(
          'status' => 404,
          'message' => 'Trial listings has not imported yet'
        );
     }
     
    if (get_option('wpl_addon_idx_trial_reseted') == 1) {
      return array(
          'status' => 401,
          'message' => 'You already reseted trial version.'
        );
    }

     $isError = false;

     foreach ($trialPids as $pid) {
       if (wpl_property::delete($pid) != true) {
         $isError = true;
       }
     }
       
    if ($isError) {
      return   array(
         'status' => 500,
         'message' => 'There was an error when trying to delete properties'
       );
    }
      
     self::addOption('wpl_addon_idx_trial_reseted',1);
     return array(
         'status' => 201,
         'message' => 'Listings deleted succesfully'
       );

   }


   public function requestProvider( array $fields )
   {
       if ( empty( $fields ) ) {
         return array(
           'status'  => 500,
           'message' => 'Fill all required fields'
         );
       }

       $fields['user_id'] =  $this->userId;

       $apiEndpoint = $this->_api.'api/request-provider/';

        $request = wp_remote_post($apiEndpoint, array(
         'headers' => array(
            'Authorization' => 'Token '.$this->token
         ),
         'body' => $fields
       ));

        

      self::checkRequestError( $request );
       
       $statusCode = wp_remote_retrieve_response_code($request);
      
       if ($statusCode != 201) {
        return array(
            'status' => $statusCode,
            'message' => json_decode(self::_('body',$request),true)
           );
       }
    
      self::addOption('wpl_addon_idx_requested_provider',$fields);

       return array(
        'status' => $statusCode,
        'message' => 'Provider requested succesfully'
       );

       
   }

  private function checkAccess( $token )
  {
       if ($this->stepsDone < 4) {
         wp_send_json_error(array(
             'message' => 'Not all steps are done.'
          ),400);
      }
     
     if ($token !== $this->token ) {
          wp_send_json_error(array(
             'message' => 'Wrong Token!'
          ),401);
       }
    }

}