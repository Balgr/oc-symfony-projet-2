<?php

require 'vendor/autoload.php';

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;


class wpl_addon_idx_base {

    private static $listing_settings = [
        'property_type' => 'All',
        'listing_type'  => 'All',
        'error_code'    =>  500,
        'status'        => 'started'
    ];

    /**
     * @var string - IDX API endpoint prefix
     */
    const api_endpoint = 'http://52.4.224.178/api/';


    /**
     * Util function to make post request
     * @param string  $endpoint
     * @param array   $params
     * @param string  $type form_params or multipart
     */

    public static function make_post_request($endpoint, array $params, $type = 'form_params')
    {

        $client = new GuzzleHttp\Client();

        $params[$type] = $params;

        $response = $client->request('POST', self::api_endpoint.$endpoint, [
            $type => $params['params']
        ]);

        $status = json_decode($response->getBody(),true);

        $response = $response->getBody();

        //combine the function name
        $functionName = $endpoint."_action";

        if($functionName == 'register_action' and $status['status'] != self::$listing_settings['error_code'])
        {
            //execute it
            self::$functionName($response,$params['params']);
        }

        wp_send_json(json_decode($response,true));
    }

    public static function make_post_auth_request_without_params($endpoint, array $params)
    {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['auth']['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('POST', self::api_endpoint.$params['auth']['secret'].'/'.$endpoint, [
            'headers' => $headers
        ]);

        $response = $response->getBody();

        $decode_response = json_decode($response,true);

        return wpl_addon_idx_service::import_trial_data($decode_response,$params['auth']['secret'],$params['auth']['token'],$params['user_id']);

    }

    /**
     * Util function to make delete request
     * @param string  $endpoint
     * @param array   $params
     */

    public static function make_delete_auth_request($endpoint, array $params,$type = 'form_params')
    {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['auth']['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('DELETE', self::api_endpoint.$params['auth']['secret'].'/'.$endpoint, [
            'headers' => $headers,
            $type     => $params['params']
        ]);

        $status = json_decode($response->getBody(),true);

        if ($status['status'] == 200)
        {
            return true;
        }

        return false;
    }

    public static function make_post_auth_update_request($endpoint, array $params,$type = 'form_params')
    {
        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('POST', self::api_endpoint.$params['secret'].'/'.$endpoint, [
            'headers' => $headers,
            $type     => $params['params']
        ]);

        $status = json_decode($response->getBody(),true);

        if ($status['status'] == 200)
        {
            $functionName = $endpoint."_action";
            self::$functionName($status['page_total'],$params['secret']);
        }
    }

    /**
     * Util function to make post request
     * @param string  $endpoint
     * @param array   $params
     * @param string  $type form_params or multipart
     */

    public static function make_post_auth_request($endpoint, array $params,$type = 'form_params')
    {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['auth']['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('POST', self::api_endpoint.$params['auth']['secret'].'/'.$endpoint, [
            'headers' => $headers,
            $type     => $params['params']
        ]);

        $status = json_decode($response->getBody(),true);

        $response = $response->getBody();

        //combine the function name
        $functionName = $endpoint."_action";

        if ($functionName == 'back_action' and $status['status'] == 200)
        {
            $back = self::$functionName($params['auth']['secret'],$params['params']['step_name']);

            if($back != false)
            {
                $return = array(
                    'message'   => 'backed',
                    'status'    => 200
                );

                wp_send_json($return);
            }
        }

        // First We need to check Parameters
        if($functionName == 'save_action' and $status['status'] != self::$listing_settings['error_code'])
        {

            // Execute it
            $save = self::$functionName($params['params'],$params['auth']['secret']);

            if($save == false)
            {

                $return = array(
                    'message'   => 'duplicate records',
                    'status'    => 500
                );

                wp_send_json($return);
            }
        }

        if($functionName == 'check_payment_action' and $status['status'] != self::$listing_settings['error_code'])
        {
            if ($status['status'] == 200)
            {
                $return = array(
                    'message'   => 'payed',
                    'status'    => 200
                );

                wp_send_json($return);
            }

            $return = array(
                'message'   => 'not payed',
                'status'    => 402
            );

            wp_send_json($return);
        }

        // Payment Action
        if($functionName == 'payment_action' and $status['status'] != self::$listing_settings['error_code'])
        {
            if($status['status'] != 401) {

                //execute it
                $save = self::$functionName($params['auth']['secret'],$status['token'],$params['params']['mls']);

                if($save == false)
                {

                    $return = array(
                        'message'   => 'duplicate records',
                        'status'    => 500
                    );

                    wp_send_json($return);
                }
            }
        }

        // Configuration Action
        if($functionName == 'config_action' and $status['status'] != self::$listing_settings['error_code'])
        {
            //execute it
            $save = self::$functionName($params['auth']['secret'],$params['auth']['token'],$status['page_total'],$params['params']['provider'],$params['params']['mls_id']);

            if($save == false)
            {

                $return = array(
                    'message'   => 'duplicate records',
                    'status'    => 500
                );

                wp_send_json($return);
            }
        }

        // Import Action
        if($functionName == 'load_action')
        {
            return json_decode($response,true);
        }

        wp_send_json(json_decode($response,true));
    }

    /**
     * Util function to make get request
     * @param string $endpoint
     * @param array  $params
     */

    public static function make_get_request($endpoint, array $params)
    {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('GET', self::api_endpoint.$params['secret'].'/'.$endpoint, [
            'headers' => $headers
        ]);

        $response = $response->getBody();

        wp_send_json(json_decode($response,true));
    }

    /**
     * @param $endpoint
     * @param array $params
     * Get external images for the listings
     */

    public static function get_images($endpoint, array $params,$type = 'form_params')
    {

        $client = new GuzzleHttp\Client();

        $headers = [
            'Authorization' => 'Bearer ' . $params['token'],
            'Accept'        => 'application/json',
        ];

        $response = $client->request('POST', self::api_endpoint.$params['secret'].'/'.$params['listingNumber'].'/'.$endpoint, [
            'headers' => $headers,
            $type     => $params['params']
        ]);

        $response = $response->getBody();

        return json_decode($response,true);
    }

    protected static function register_action($response,$params)
    {

        $response = json_decode($response, true);
        // check if email exist
        $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_users` WHERE `email` = '{$params['email']}'", 
            'loadObject'
        );

        if(!$query)
        {

            $params = array(
                'secret_key' => $response['success']['secret'],
                'token'      => $response['success']['token'],
                'email'      => $params['email'],
                'created_at' => date("Y-m-d H:i:s")
            );

            if(wpl_db::insert('wpl_addon_idx_users', $params) != false)
            {
                // update step value and step name
                $step_values = array(
                    'secret_key' => $response['success']['secret'],
                    'step_name'  => __FUNCTION__,
                    'step_value' => 1,
                    'created_at' => date("Y-m-d H:i:s")
                );

                return wpl_db::insert('wpl_addon_idx_user_wizard_steps', 
                    $step_values
                );
            }
        }
    }

    protected static function save_action($params,$secret)
    {

        $query = wpl_db::select("SELECT `id` FROM `#__wpl_addon_idx_users_providers` WHERE `user_secret_key` = '{$secret}' AND `mls_id` = '{$params['mls_id']}'", 
            'loadObject'
        );

        if(!$query)
        {

            $params = array(
                'mls_id'             => $params['mls_id'],
                'user_secret_key'    => $secret,
                'provider_name'      => $params['name'],
                'provider_short_name'=> $params['provider'],
                'listing_type'       => self::$listing_settings['listing_type'],
                'property_type'      => self::$listing_settings['property_type'],
                'created_at'         => date("Y-m-d H:i:s")
            );

            if(wpl_db::insert('wpl_addon_idx_users_providers', $params) != false)
            {
                // update step value and step name
                $step_values = array(
                    'secret_key' => $secret,
                    'step_name'  => __FUNCTION__,
                    'step_value' => 2,
                    'created_at' => date("Y-m-d H:i:s")
                );

                return wpl_db::insert('wpl_addon_idx_user_wizard_steps', 
                    $step_values
                );
            }

        }

        return false;
    }

    protected static function payment_action($secret,$transaction_token,$provider)
    {

        $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_payments` WHERE `secret_key` = '{$secret}' AND `provider` = '{$provider}'", 'loadObject');

        if(!$query)
        {
            $params = array(
                'secret_key'        => $secret,
                'provider'          => $provider,
                'transaction_token' => $transaction_token,
                'created_at'        => date("Y-m-d H:i:s")
            );

            if(wpl_db::insert('wpl_addon_idx_payments', $params) != false)
            {
                // update step value and step name
                $step_values = array(
                    'secret_key' => $secret,
                    'step_name'  => __FUNCTION__,
                    'step_value' => 3,
                    'created_at' => date("Y-m-d H:i:s")
                );

                return wpl_db::insert('wpl_addon_idx_user_wizard_steps', 
                    $step_values
                );
            }
        }

        return false;
    }

    protected static function config_action($secret,$token,$total,$provider,$mls_id)
    {
        $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_tasks` WHERE `secret` = '{$secret}' AND `mls_id` = '{$mls_id}'", 'loadObject');

        if(!$query)
        {
            $params = array(
                'provider'   => $provider,
                'mls_id'     => $mls_id,
                'secret'     => $secret,
                'token'      => $token,
                'page'       => ($total == 0) ? $total + 1 : $total,
                'first_page' => ($total == 0) ? $total + 1 : $total,
                'status'     => self::$listing_settings['status'],
                'ts'         => date("Y-m-d H:i:s"),
                'ts_updated' => date("Y-m-d H:i:s")
            );

            if(wpl_db::insert('wpl_addon_idx_tasks', $params) != false)
            {
                // update step value and step name
                $step_values = array(
                    'secret_key' => $secret,
                    'step_name'  => __FUNCTION__,
                    'step_value' => 4,
                    'created_at' => date("Y-m-d H:i:s")
                );

                if (wpl_db::insert('wpl_addon_idx_user_wizard_steps', $step_values) != false)
                {
                    $trial_logs = array(
                        'secret' => $secret,
                        'status' => 1,
                        'date' => date("Y-m-d H:i:s")
                    );

                    return wpl_db::insert('wpl_addon_idx_trial_logs', 
                        $trial_logs
                    );
                }
            }
        }

        return false;
    }

    protected static function back_action($secret,$step_name)
    {

        if ($step_name == 'save_action')
        {
            $query = "DELETE FROM `#__wpl_addon_idx_user_wizard_steps` WHERE `step_name` = '$step_name' and `secret_key` = '$secret'";

            if (wpl_db::q($query) != false)
            {
                
                $query = "DELETE FROM `#__wpl_addon_idx_users_providers` WHERE `user_secret_key` = '$secret'";

                return wpl_db::q(
                    $query
                );
            }
        }

        if ($step_name == 'register_action')
        {
            $query = "DELETE FROM `#__wpl_addon_idx_user_wizard_steps` WHERE `step_name` = '$step_name' and `secret_key` = '$secret'";

            if (wpl_db::q($query) != false)
            {
                
                $query = "DELETE FROM `#__wpl_addon_idx_users` WHERE `secret_key` = '$secret'";

                return wpl_db::q(
                    $query
                );
            }
        }

        return false;

    }

    protected static function update_action($total,$secret)
    {

        $query = "UPDATE `#__wpl_addon_idx_tasks` SET `page` = '$total', `completed_page` = `first_page`,`first_page` = '$total' WHERE `secret`= '$secret'";
        // execute query
        wpl_db::q($query, 
            'UPDATE'
        );
    }
}