<?php

_wpl_import('libraries.addon_idx.config');

class wpl_addon_idx_service {

    /**
     * Intiger type
     * @var UNIT_DOLLAR_ID
     */
    const UNIT_DOLLAR_ID = 260;

    /**
     * Intiger type
     * @var PRICE_PERIOD
     */
    const PRICE_PERIOD = 30; // Per Month (For Rent Listings)

    /**
     * Integer Value
     * @var FINAL PAGE VALUE
     */
    const FINAL_PAGE = 0;

    /**
     * @var array
     * All error codes
     */

    static protected $response_codes = array(

        0 => 'OK',
        1 => 'Restricted access',
        2 => 'Incorrect username/password',
        3 => 'networking error'

    );

    /**
     * @var array
     * All Listing Types
     */

    static protected $liting_types = array(
        1 => 'For Rent',
        2 => 'For Sale'
    );

    /**
     * @var array
     * All Property Types
     */

    static protected $property_types = array(
        1  => 'ResidentialLease',
        2  => 'CommercialLease',
        3  => 'Residentail',
        4  => 'CommercialSale',
        5  => 'BusinessOpportunity',
        6  => 'Land',
        7  => 'ManufacturedInPark',
        8  => 'CrossProperty',
        9  => 'ResidentialIncome',
        10 => 'Rental',
        11 => 'Income',
        12 => 'Commercial',
        13 => 'Residential',
        14 => 'Vacant Land'
    );

    /**
     * @var array
     * All Area Unit Types
     */

    static protected $unit_types = array(
        'Square Feet' => 'Sqft',
        'Acres'       => 'Acre'
    );

    /**
     * Initializes logging system
     * @param Request $request request containing ip addr,username and password
     * @return void
     */

    static function init($request)
    {
        // get config
        $config = wpl_addon_idx_config::config();

        //check if request ip addres is in allowed ip adress list
        if (!in_array($request['ip'], $config['serviceApiIpAddress']))
        {
            return self::save(1,$request['ip']);
        }

        //check username and password authorization
        if(!self::authorization($request['username'],$request['password']))
        {
            return self::save(2,$request['ip']);
        }

        // start to import data
        self::import($request['secret'],$request['ip'],$request['user_id']);
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     * chek authorization information
     */

    static public function authorization($username,$password)
    {
        // get config
        $config = wpl_addon_idx_config::config();

        if($username == $config['username'] && $password == $config['password'])
            return true;

        return false;
    }

    /**
     * @param $errorCode
     * @param $ip
     * @return mixed|null
     * save error log data in database
     */
    static public function save($errorCode,$ip)
    {

        $params = array(
            'ip_addr'    => $ip,
            'error_code' => static::$response_codes[$errorCode],
            'date'       => date("Y-m-d H:i:s")
        );

        return wpl_db::insert('wpl_addon_idx_service_logs', 
            $params
        );

    }

    /**
     * @param $unit
     * @param int $enable
     * @return array
     * Get Unit By Name
     */

    protected static function get_unit_by_name($unit,$enable = 1)
    {
        if (!empty($unit))
        {
            $unit_type = static::$unit_types[$unit];

            $unit_id = wpl_db::select("SELECT `id` FROM `#__wpl_units` WHERE `name` = '{$unit_type}' AND `enabled` = '{$enable}'", 
                'loadObject'
            );

            if ($unit_id)
            {
                return array(
                    'unit_id' => $unit_id->id
                );
            }
        }
    }

    /**
     * @param $listing
     * @param int $enable
     * @return array
     * Get Listing Type id by listing name
     */

    protected static function get_listing_types($listing,$enable = 1)
    {

        if (isset($listing))
        {
            // Listing Type Name
            $listing_type = static::$liting_types[$listing];

            if (isset($listing_type))
            {
                // get listing type id by listing name
                $listing_id = wpl_db::select("SELECT * FROM `#__wpl_listing_types` WHERE `name` = '{$listing_type}' AND `enabled` = '{$enable}'", 
                    'loadObject'
                );

                if($listing_id)
                {
                    return array(
                        'listing_id'   => $listing_id->id,
                        'price_period' => self::PRICE_PERIOD
                    );
                }
            }
        }
    }

    /**
     * @param $property
     * @param int $enable
     * @return array
     * Get Property Type Id by Property Name
     */
    protected static function get_property_types($property,$enable = 1)
    {

        if (isset($property))
        {
            // Property Type Name
            $property_type = static::$property_types[$property];

            if (isset($property_type))
            {
                // get property type id by property name
                $property_id = wpl_db::select("SELECT * FROM `#__wpl_property_types` WHERE `name` = '{$property_type}' AND `enabled` = '{$enable}'", 
                    'loadObject'
                );

                if($property_id)
                {
                    return array(
                        'property_id' => $property_id->id
                    );
                }
            }
        }
    }

    /**
     * @param $features
     * @param $appliances
     * @return array
     * Get Listing Feature Column name and value By $features
     */

    protected static function parse_features($features,$appliances)
    {

        if(is_array($features))
        {
            foreach ($features as $feature)
            {
                //now split individual names for features
                $feature_items = explode(',',$feature,-1);

                if (!empty($feature_items))
                {
                    foreach ($feature_items as $feature_item)
                    {
                        $columns_features [] = $feature_item;
                    }
                }
            }
        }

        //now split individual names for appliances
        $columns_appliances = explode(',',$appliances,-1);

        if (!empty($columns_appliances))
        {
            foreach ($columns_appliances as $appliances_items)
            {
                $columns_appliances [] = $appliances_items;
            }
        }

        if (isset($columns_features))
        {
            $results = self::get_table_by_name(array($columns_features, $columns_appliances));

            if($results)
            {
                return $results;
            }
        }
    }

    protected static function get_table_by_name(array $fieldNames)
    {
        foreach ($fieldNames as $key => $fieldName) {

            if(!empty($fieldName))
            {
                $query = wpl_db::select("SELECT * FROM `#__wpl_dbst` WHERE `name` = '{$fieldName[$key]}'", 
                    'loadObject'
                );

                if($query)
                {
                    return array(
                        'feature_column' => $query->table_column,
                        'kind'           => $query->kind,
                        'value'          => 1
                    );
                }
            }
        }
    }

    /**
     * @param $addr
     * @param $state
     * @return array
     * Get Location Name,Id and State Id
     */

    protected static function get_location_name($addr = false,$state = false)
    {

        if ($addr != false and $state != false)
        {
            // Returns abbreviation by location name
            if(!empty(wpl_locations::get_location_name_by_abbr($addr)))
            {
                $location_name = wpl_locations::get_location_name_by_abbr($addr);
            }
            else
            {
                // Returns location name by abbreviation
                $location_name =  wpl_locations::get_location_abbr_by_name($addr);
            }

            if (!empty($location_name))
            {
                // get location id
                $location_id = wpl_locations::get_location_id(wpl_locations::get_location_name_by_abbr($addr));

                // get state id by name
                $state =  wpl_db::select("SELECT `id` FROM `#__wpl_location2` WHERE `name` = '{$state}'", 
                    'loadObject'
                );

                if ($state)
                {
                    return array(
                        'location_id'   => $location_id,
                        'location_name' => $location_name,
                        'state_id'      => $state->id
                    );
                }
            }

            return false;
        }

        return false;
    }

    protected static function get_view_key($view)
    {
        if(!empty($view))
        {
            $query = wpl_db::select("SELECT `options` FROM `#__wpl_dbst` WHERE `name` = 'View'", 
                'loadObject'
            );

            $options = json_decode($query->options,true);

            foreach ($options['params'] as $param)
            {
                // return key
                if($param['value'] == $view)
                {
                    return $param['key'];
                }
            }
        }

        return false;
    }

    protected static function get_additional_information($param)
    {
        if ($param == 'Yes') {
            return 1;
        }

        return 0;
    }

    /**
     * @param $pid
     * @param $kind
     * @param $url
     * @param string $type
     * @param string $category
     * Save External Images in wpl items table
     */

    protected static function save_external_images($pid,$kind,$url,$type = 'gallery',$category = 'external')
    {

        $index = floatval(wpl_items::get_maximum_index($pid, $type, $kind, $category))+1.00;

        $name = 'external_image'.$index;

        $item_id = wpl_items::save(array(
            'parent_id'    => $pid,
            'parent_kind'  => $kind,
            'item_type'    => $type,
            'item_cat'     => $category,
            'item_name'    => $name,
            'creation_date'=> date("Y-m-d H:i:s"),
            'index'        => $index,
            'item_extra3'  => $url
        ));
    }

    /**
     * @param $secret
     * @return void
     * This method updating page value in idx tasks table
     */

    protected static function update_task_logs($secret)
    {
        // current timestamp
        $time = date("Y-m-d H:i:s");

        $query = "UPDATE `#__wpl_addon_idx_tasks` SET `page` = `page` - 1, `ts_updated` = '$time' WHERE `secret`= '$secret' ORDER BY `ts_updated` ASC";

        // execute query
        wpl_db::q($query, 
            'UPDATE'
        );
    }

    /**
     * Get Listing id and call listing delete method and item delete method
     */

    protected static function delete_trail_data()
    {
        $rows = wpl_db::select("SELECT `id` FROM `#__wpl_properties` WHERE `mls_id` LIKE '%TRIAL%'", 
            'loadObjectList'
        );

        if($rows)
        {
            foreach ($rows as $row)
            {
                if(self::delete_listings($row->id) != false)
                {
                    self::delete_items($row->id);
                }
            }
        }
    }

    /**
     * @param $listing_id
     * @return mixed
     * Delete Listing by Listing Id
     */
    protected static function delete_listings($listing_id)
    {
        $query = "DELETE FROM `#__wpl_properties` WHERE `id` = '$listing_id'";

        return wpl_db::q(
            $query
        );
    }

    /**
     * @param $listing_id
     * Delete Item by Listing Id
     */

    protected static function delete_items($listing_id)
    {
        $query = "DELETE FROM `#__wpl_items` WHERE `parent_id` = '$listing_id'";

        wpl_db::q(
            $query
        );
    }

    /**
     * @param $secret
     * @return mixed|null
     * create trial log record
     */

    protected static function create_trial_logs($secret)
    {
        $trial_logs = array(
            'secret' => $secret,
            'status' => 1,
            'date'   => date("Y-m-d H:i:s")
        );

        return wpl_db::insert('wpl_addon_idx_trial_logs', 
            $trial_logs
        );
    }

    /**
     * @param $params
     * @param $pid
     * updates listing records
     */

    protected static function update_listing($params, $pid)
    {
        //here we will put parts like "fieldName = ?", so that they can be later
        //combined with commas.. that is how update query is required to be, so that is
        //what we make it
        $updateParts = array();

        //go through each column, value pair
        foreach ($params as $fieldName => $fieldValue) {

            //add to list of parts
            $updateParts[] = "`$fieldName` = '$fieldValue'";
        }

        //now implode it into updateSet...
        $updateSet = implode(", ", $updateParts);

        //we combine the string query
        wpl_db::q("UPDATE `#__wpl_properties` SET $updateSet WHERE `id` = '{$pid}'", 
            'update'
        );

    }

    protected static function update_page($token,$secret,$mls_id)
    {

        $data = wpl_addon_idx_base::make_post_auth_update_request('update', array(
            'url'           => false,
            'secret'        => $secret,
            'token'         => $token,
            'params' => array(
                'mls_id' => $mls_id
            ),
        ));
    }

    /**
     * @param $listing_id
     * @return bool
     * checks dublicate records by listing id
     */
    protected static function check_by_listing_id($listing_id)
    {

        $query = "SELECT COUNT(*) AS count FROM `#__wpl_properties` WHERE `mls_id` = '{$listing_id}'";
        // execute query
        $execute = wpl_db::select(
            $query, 
            'loadResult'
        );

        if ($execute > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param $secret
     * @param $ip
     * @param int $kind
     * @param int $deleted
     * @return mixed|null
     * Imports listings From Cache Server (valid version)
     */

    static public function import($secret,$ip,$user_id)
    {

        try {

            $task = wpl_db::select(
                "SELECT * FROM `#__wpl_addon_idx_tasks` WHERE `secret` = '{$secret}' ORDER BY `ts_updated` ASC",
                'loadObject'
            );

            if ($task->page > self::FINAL_PAGE and
                $task->page > $task->completed_page
            )
            {
                // first delete trail data
                self::delete_trail_data();

                // form params
                $formparams = array(
                    'mls_id' => $task->mls_id,
                    'page'   => $task->page,
                    'auth' => array(
                        'secret' => $task->secret,
                        'token'  => $task->token,
                    )
                );

                $data = wpl_addon_idx_base::make_post_auth_request('load', array(
                    'url'    => false,
                    'params' => $formparams,
                    'auth'   => $formparams['auth']
                ));

                if (!isset($data['response']))
                    return false;

                foreach ($data['response'] as $key => $item) {

                    if (isset($item['sections']) and
                        self::check_by_listing_id($item['sections']['property_details']['listingNumber']) == true
                    ) {

                        if (isset($item['sections'])) {
                            // call create_property_default method
                            $pid = wpl_property::create_property_default($user_id);
                            // get location name
                            $location = self::get_location_name($item['sections']['property_location']['country'], $item['sections']['property_location']['state']);
                            // Features
                            $features = self::parse_features($item['sections']['property_features'], $item['sections']['property_appliances']['appliances']);
                            // Get Listing Type
                            $listing_type = self::get_listing_types($item['listingStatusDisplayId']);
                            // Get Property Type
                            $property_type = self::get_property_types($item['PropertyStatusDisplayId']);
                            // Get Unit Id
                            $unit_type = self::get_unit_by_name($item['sections']['property_area']['Unit']);
                        }
                        // insert with fetures
                        if (!empty($features['feture_column'])) {
                            $params = array(
                                // Property Features
                                $features['feature_column'] => $features['value'],
                                // Listing Number
                                'mls_id' => $item['sections']['property_details']['listingNumber'],
                                // Listing Id
                                'listing' => !isset($listing_type['listing_id']) ? 0 : $listing_type['listing_id'],
                                // Property Id
                                'property_type' => !isset($property_type['property_id']) ? '' : $property_type['property_id'],
                                // Location Information Like State,Country,City
                                'location1_name' => !isset($location['location_name']) ? '' : $location['location_name'],
                                // state
                                'location2_name' => $item['sections']['property_location']['state'],
                                // country
                                'location3_name' => $item['sections']['property_location']['country'],
                                // city
                                'location4_name' => $item['sections']['property_location']['city'],
                                // Street num
                                'street_no' => $item['sections']['property_location']['streetNumber'],
                                // Street name
                                'field_42' => $item['sections']['property_location']['street'],
                                // State id
                                'location2_id' => !isset($location['state_id']) ? 0 : $location['state_id'],
                                // Location Id
                                'location1_id' => !isset($location['location_id']) ? 0 : $location['location_id'],
                                // Bed Romm Total
                                'bedrooms' => $item['sections']['property_details']['Bedrooms'],
                                // Bath Romm Total
                                'bathrooms' => $item['sections']['property_details']['Bathrooms'],
                                // Helf Bath
                                'half_bathrooms' => $item['sections']['property_details']['BathsHalf'],
                                // Postal code
                                'post_code' => $item['sections']['property_details']['postalCode'],
                                // Photo count
                                'pic_numb' => $item['sections']['property_details']['photoCount'],
                                // Listing Latitude
                                'googlemap_lt' => !isset($item['Latitude']) ? '' : $item['Latitude'],
                                // Listing Longitude
                                'googlemap_ln' => !isset($item['Longitude']) ? '' : $item['Longitude'],
                                // Property Description
                                'field_308' => str_replace("'", '/', $item['sections']['property_details']['Description']),
                                // Built year of Property
                                'build_year' => $item['sections']['property_details']['year_built'],
                                // Lot Area of Property
                                'lot_area' => $item['sections']['property_area']['Lot'],
                                // Unit Id
                                'lot_area_unit' => !isset($unit_type['unit_id']) ? 0 : $unit_type['unit_id'],
                                // Listing Price
                                'price' => $item['sections']['property_details']['Price'],
                                // If Listing type is For Sale
                                'price_period' => !empty($listing_type['price_period']) ? $listing_type['price_period'] : 0,
                                // Listing View Type
                                'field_7' => self::get_view_key($item['sections']['property_details']['view']),
                                // Swiming Pool
                                'f_131' => self::get_additional_information($item['sections']['property_additional_information']['pool']),
                                // DOLLAR UNIT ID
                                'price_unit' => self::UNIT_DOLLAR_ID
                            );

                        } elseif (isset($item['sections']['property_details']['listingNumber'])) {
                            $params = array(
                                // Listing Number
                                'mls_id' => $item['sections']['property_details']['listingNumber'],
                                // Listing Id
                                'listing' => !isset($listing_type['listing_id']) ? 0 : $listing_type['listing_id'],
                                // Property Id
                                'property_type' => !isset($property_type['property_id']) ? '' : $property_type['property_id'],
                                // Location Information Like State,Country,City
                                'location1_name' => !isset($location['location_name']) ? '' : $location['location_name'],
                                // state
                                'location2_name' => $item['sections']['property_location']['state'],
                                // country
                                'location3_name' => $item['sections']['property_location']['country'],
                                // city
                                'location4_name' => $item['sections']['property_location']['city'],
                                // Street num
                                'street_no' => $item['sections']['property_location']['streetNumber'],
                                // Street name
                                'field_42' => $item['sections']['property_location']['street'],
                                // State id
                                'location2_id' => !isset($location['state_id']) ? 0 : $location['state_id'],
                                // Location Id
                                'location1_id' => !isset($location['location_id']) ? 0 : $location['location_id'],
                                // Bed Romm Total
                                'bedrooms' => $item['sections']['property_details']['Bedrooms'],
                                // Bath Romm Total
                                'bathrooms' => $item['sections']['property_details']['Bathrooms'],
                                // Helf Bath
                                'half_bathrooms' => $item['sections']['property_details']['BathsHalf'],
                                // Postal code
                                'post_code' => $item['sections']['property_details']['postalCode'],
                                // Photo count
                                'pic_numb' => $item['sections']['property_details']['photoCount'],
                                // Listing Latitude
                                'googlemap_lt' => !isset($item['Latitude']) ? '' : $item['Latitude'],
                                // Listing Longitude
                                'googlemap_ln' => !isset($item['Longitude']) ? '' : $item['Longitude'],
                                // Property Description
                                'field_308' => str_replace("'", '/', $item['sections']['property_details']['Description']),
                                // Built year of Property
                                'build_year' => $item['sections']['property_details']['year_built'],
                                // Lot Area of Property
                                'lot_area' => $item['sections']['property_area']['Lot'],
                                // Unit Id
                                'lot_area_unit' => !isset($unit_type['unit_id']) ? 0 : $unit_type['unit_id'],
                                // Listing Price
                                'price' => $item['sections']['property_details']['Price'],
                                // If Listing type is For Sale
                                'price_period' => !empty($listing_type['price_period']) ? $listing_type['price_period'] : 0,
                                // Listing View Type
                                'field_7' => self::get_view_key($item['sections']['property_details']['view']),
                                // Swiming Pool
                                'f_131' => self::get_additional_information($item['sections']['property_additional_information']['pool']),
                                // DOLLAR UNIT ID
                                'price_unit' => self::UNIT_DOLLAR_ID
                            );
                        }

                        if (!empty($item['sections']['property_details']['listingNumber'])) {
                            // call update method
                            self::update_listing($params, $pid);

                            // check if property save is true
                            if (wpl_property::finalize($pid, 'add', $user_id) != false) {

                                $images = wpl_db::select("SELECT * FROM `#__wpl_properties` WHERE `mls_id` = '{$item['sections']['property_details']['listingNumber']}'", 
                                    'loadObject'
                                );

                                if ($images) {

                                    $data = wpl_addon_idx_base::get_images('load/images', array(
                                        'url' => false,
                                        'secret' => $secret,
                                        'listingNumber' => $images->mls_id,
                                        'token' => $task->token,
                                        // Provider Id
                                        'params' => array(
                                            'mls_id' => $task->mls_id
                                        ),
                                    ));

                                    if (is_array($data) and isset($data['images'])) {

                                        foreach ($data['images'] as $item) {
                                            // call image save method
                                            self::save_external_images($images->id, $images->kind, $item);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // update task table
                self::update_task_logs($secret);
            } else {
                // check total pages on cache server via api call
                self::update_page($task->token,$task->secret,$task->mls_id);
            }

        } catch (GuzzleHttp\Exception\RequestException $e) {
            return self::save(3,$ip);
        }
    }

    /**
     * @param $response
     * @param $secret
     * @param $token
     * Import Trial Data From Cache server
     */

    public static function import_trial_data($response,$secret,$token,$user_id)
    {

        foreach ($response['response'] as $item) {

            if (isset($item['sections']))
            {
                // call create_property_default method
                $pid           = wpl_property::create_property_default($user_id);
                // get location name
                $location      = self::get_location_name($item['sections']['property_location']['country'], $item['sections']['property_location']['state']);
                // Features
                $features      = self::parse_features($item['sections']['property_features'], $item['sections']['property_appliances']['appliances']);
                // Get Listing Type
                $listing_type  = self::get_listing_types($item['listingStatusDisplayId']);
                // Get Property Type
                $property_type = self::get_property_types($item['PropertyStatusDisplayId']);
                // Get Unit Id
                $unit_type     = self::get_unit_by_name($item['sections']['property_area']['Unit']);
            }

            // insert with fetures
            if (!empty($features['feture_column']))
            {
                $params = array(
                    // Property Features
                    $features['feature_column'] => $features['value'],
                    // Listing Number
                    'mls_id'          => 'TRIAL'.$item['sections']['property_details']['listingNumber'],
                    // Listing Id
                    'listing'         => $listing_type['listing_id'],
                    // Property Id
                    'property_type'   => $property_type['property_id'],
                    // Location Information Like State,Country,City
                    'location1_name'  => !isset($location['location_name']) ? '' : $location['location_name'],
                    // state
                    'location2_name'  => $item['sections']['property_location']['state'],
                    // country
                    'location3_name'  => $item['sections']['property_location']['country'],
                    // city
                    'location4_name'  => $item['sections']['property_location']['city'],
                    // Street num
                    'street_no'       => $item['sections']['property_location']['streetNumber'],
                    // Street name
                    'field_42'        => $item['sections']['property_location']['street'],
                    // State id
                    'location2_id'    => !isset($location['state_id']) ? 0 : $location['state_id'],
                    // Location Id
                    'location1_id'    => !isset($location['location_id']) ? 0 : $location['location_id'],
                    // Bed Romm Total
                    'bedrooms'        => $item['sections']['property_details']['Bedrooms'],
                    // Bath Romm Total
                    'bathrooms'       => $item['sections']['property_details']['Bathrooms'],
                    // Helf Bath
                    'half_bathrooms'  => $item['sections']['property_details']['BathsHalf'],
                    // Postal code
                    'post_code'       => $item['sections']['property_details']['postalCode'],
                    // Photo count
                    'pic_numb'        => $item['sections']['property_details']['photoCount'],
                    // Listing Latitude
                    'googlemap_lt'    => $item['Latitude'],
                    // Listing Longitude
                    'googlemap_ln'    => $item['Longitude'],
                    // Property Description
                    'field_308'       => str_replace("'", '/', $item['sections']['property_details']['Description']),
                    // Built year of Property
                    'build_year'      => $item['sections']['property_details']['year_built'],
                    // Lot Area of Property
                    'lot_area'        => $item['sections']['property_area']['Lot'],
                    // Unit Id
                    'lot_area_unit'   => $unit_type['unit_id'],
                    // Listing Price
                    'price'           => $item['sections']['property_details']['Price'],
                    // If Listing type is For Sale
                    'price_period'    => !empty($listing_type['price_period']) ? $listing_type['price_period'] : 0,
                    // Listing View Type
                    'field_7'         => self::get_view_key($item['sections']['property_details']['view']),
                    // Swiming Pool
                    'f_131'           => self::get_additional_information($item['sections']['property_additional_information']['pool']),
                    // DOLLAR UNIT ID
                    'price_unit'      => self::UNIT_DOLLAR_ID
                );

            } else {
                // insert without features
                $params = array(
                    // Listing Number
                    'mls_id'         => 'TRIAL'.$item['sections']['property_details']['listingNumber'],
                    // Listing Id
                    'listing'        => !isset($listing_type['listing_id']) ? 0 : $listing_type['listing_id'],
                    // Property Id
                    'property_type'  => !isset($property_type['property_id']) ? '' : $property_type['property_id'],
                    // Location Information Like State,Country,City
                    'location1_name' => !isset($location['location_name']) ? '' : $location['location_name'],
                    // state
                    'location2_name' => $item['sections']['property_location']['state'],
                    // country
                    'location3_name' => $item['sections']['property_location']['country'],
                    // city
                    'location4_name' => $item['sections']['property_location']['city'],
                    // Street num
                    'street_no'      => $item['sections']['property_location']['streetNumber'],
                    // Street name
                    'field_42'       => $item['sections']['property_location']['street'],
                    // State id
                    'location2_id'   => !isset($location['state_id']) ? 0 : $location['state_id'],
                    // Location Id
                    'location1_id'   => !isset($location['location_id']) ? 0 : $location['location_id'],
                    // Bed Romm Total
                    'bedrooms'       => $item['sections']['property_details']['Bedrooms'],
                    // Bath Romm Total
                    'bathrooms'      => $item['sections']['property_details']['Bathrooms'],
                    // Helf Bath
                    'half_bathrooms' => $item['sections']['property_details']['BathsHalf'],
                    // Postal code
                    'post_code'      => $item['sections']['property_details']['postalCode'],
                    // Photo count
                    'pic_numb'       => $item['sections']['property_details']['photoCount'],
                    // Listing Latitude
                    'googlemap_lt'   => !isset($item['Latitude']) ? '' : $item['Latitude'],
                    // Listing Longitude
                    'googlemap_ln'   => !isset($item['Longitude']) ? '' : $item['Longitude'],
                    // Property Description
                    'field_308'      => str_replace("'", '/', $item['sections']['property_details']['Description']),
                    // Built year of Property
                    'build_year'     => $item['sections']['property_details']['year_built'],
                    // Lot Area of Property
                    'lot_area'       => $item['sections']['property_area']['Lot'],
                    // Unit Id
                    'lot_area_unit'  => !isset($unit_type['unit_id']) ? 0 : $unit_type['unit_id'],
                    // Listing Price
                    'price'          => $item['sections']['property_details']['Price'],
                    // If Listing type is For Sale
                    'price_period'   => !empty($listing_type['price_period']) ? $listing_type['price_period'] : 0,
                    // Listing View Type
                    'field_7'        => self::get_view_key($item['sections']['property_details']['view']),
                    // Swiming Pool
                    'f_131'          => self::get_additional_information($item['sections']['property_additional_information']['pool']),
                    // DOLLAR UNIT ID
                    'price_unit'     => self::UNIT_DOLLAR_ID
                );
            }

            if (!empty($item['sections']['property_details']['listingNumber']))
            {
                // call update method
                self::update_listing($params,$pid);

                // check if property save is true
                if (wpl_property::finalize($pid, 'add', $user_id) != false)
                {
                    $listing_key = 'TRIAL'.$item['sections']['property_details']['listingNumber'];

                    $images = wpl_db::select("SELECT * FROM `#__wpl_properties` WHERE `mls_id` = '{$listing_key}'", 
                        'loadObject'
                    );

                    if($images)
                    {
                        $data = wpl_addon_idx_base::get_images('load/images', array(
                            'url'           => false,
                            'secret'        => $secret,
                            'listingNumber' => $item['sections']['property_details']['listingNumber'],
                            'token'         => $token,
                            // Provider Id
                            'params' => array(
                                'mls_id' => 4
                            ),
                        ));


                        if (is_array($data) and isset($data['images']))
                        {
                            foreach ($data['images'] as $item)
                            {
                                // call image save method
                                self::save_external_images($images->id, $images->kind,$item);
                            }
                        }
                    }
                }
            }
        }
        // call create_trial_log method
        self::create_trial_logs($secret);
    }
}