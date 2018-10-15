<?php

defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.addon_idx');
_wpl_import('libraries.addon_idx.delete');


class wpl_addon_idx_controller extends wpl_controller
{

    static protected $steps = array(
        'payment',
        'configuration'
    );

    /**
     * @var integer - final step of registration
     */
    const final_step = 4;

    public function display()
    {
        // first check php version
        if ( version_compare(PHP_VERSION, '5.5', '<') ) {

            $return = array(
                'message'   => 'PHP >= 5.5 is required.',
                'status'    =>  500
            );

            wp_send_json($return);
        };

        wpl_global::min_access('guest');
        // init function name
        $function = wpl_request::getVar('wpl_function');

        // prevent idx user
        if (
            self::prevent_idx_user() != true and
            $function != 'service' and
            $function != 'delete' and
            $function != 'status' and
            $function != 'protect_trial' and
            $function != 'load_trial_data' and
            $function != 'reset' and
            $function != 'save_client_request' and 
            $function != 'check_idx'
        )

        {
            $return = array(
                'message'   => 'idx user already exists',
                'status'    =>  500
            );

            wp_send_json($return);
        }

        // Execute it
        self::$function();
    }

    // The user registration API call can be used to create user accounts in the application.
    // Additionally, the “email”,”password”,”name”,”re_password” fields are required.
    protected static function registration()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {

            $formparams = array(
                'name'        => wpl_request::getVar('name'),
                'second_email'=> wpl_request::getVar('second_email'),
                'email'       => $current_user->user_email, // system email
                'phone'       => wpl_request::getVar('phone')
            );

            return wpl_addon_idx::registration(false,$formparams);
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);

    }

    // Using this method we will receive information about different providers, such as provider name,provider short description,
    // listing name and property type,note that you are required to send a valid Header in your request, in which the key should be Authorization and
    // the value should be the token that you recieved
    protected static function providers()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::providers(false,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    //Using this method you will recieve selected information about the provider or providers, chosen by the IDX client,
    // such as provider name, short description of the provider, listing name and property type.
    protected static function save()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {

            $formparams = array(
                'mls_id'        => wpl_request::getVar('mls_id'),
                'name'          => wpl_request::getVar('name'),
                'provider'      => wpl_request::getVar('provider')
            );

            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::save(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // Using this method you will recieve information about price list according to your chosen MLS provider(s).
    protected static function price()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::price(false,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // Using this method you will recieve information about price by Month
    protected static function calculate_price()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {

            $formparams = array(
                'provider' => wpl_request::getVar('provider'),
                'month'    => wpl_request::getVar('month'),
            );

            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::calculate_price(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // Using this method client can to make payments to use this API or else the client will not be able to use different API calls such as load property data, etc.
    // If the amount is less then the actual price, the payment will not be made.
    protected static function payment()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {

            $formparams = array(
                'token_id'  => wpl_request::getVar('token_id'),
                'mls'       => wpl_request::getVar('mls'),
                'month'     => 1 // Always must be one
            );

            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::payment(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // Using this method we will save client current configuration on cache server
    protected static function configuration()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $formparams = array(
                'mls_id'         => wpl_request::getVar('mls_id'), // Provider id
                'provider'       => wpl_request::getVar('provider'), // Provider name
                'property_type'  => wpl_request::getVar('property_type'),
                'agent_id'       => wpl_request::getVar('agent_id'), // Agent id
                'office_id'      => wpl_request::getVar('office_id'), // Office id
                'agent_name'     => wpl_request::getVar('agent_name'),
                'office_name'    => wpl_request::getVar('office_name'),
                'import_status'  => (wpl_request::getVar('all_listing') == 1) ? 1 : wpl_request::getVar('import_status'),
                'listing_status' => wpl_request::getVar('listing_status'),
                'office_listing' => wpl_request::getVar('office_listing'),
                'agent_listing'  => wpl_request::getVar('agent_listing'),
                'all_listing'    => wpl_request::getVar('all_listing'),
                'min_bathrooms'  => wpl_request::getVar('min_bathrooms'),
                'max_bathrooms'  => wpl_request::getVar('max_bathrooms'),
                'min_bedrooms'   => wpl_request::getVar('min_bedrooms'),
                'max_bedrooms'   => wpl_request::getVar('max_bedrooms'),
                'min_price'      => wpl_request::getVar('min_price'),
                'max_price'      => wpl_request::getVar('max_price'),
                'square_feet_min'=> wpl_request::getVar('square_feet_min'),
                'square_feet_max'=> wpl_request::getVar('square_feet_max'),
                'zip_code'       => wpl_request::getVar('zipcode'),
                'service_url'    => get_site_url(), // full site URL for web service
            );

            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::configuration(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    protected static function get_step($last_step = 4)
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_user_wizard_steps` WHERE `secret_key` = '{$auth['secret']}' ORDER BY `id` DESC ", 
                'loadObject'
            );

            if ($query)
            {
                // check if it's finished
                if ($query->step_value == $last_step)
                {
                    $return = array(
                        'message'    => 'Finished',
                        'step_value' => $last_step
                    );

                    wp_send_json($return);
                }

                $return = array(
                    'step_value' => $query->step_value + 1
                );

                wp_send_json($return);
            }

            $return = array(
                'step_value' => 1
            );

            wp_send_json($return);
        }
    }

    // Service API
    protected static function service()
    {
        // get the secret key and token of the current user by current use email
        $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_users`", 
            'loadObject'
        );

        if ($query)
        {
            $user = get_user_by( 'email', $query->email);

            $auth = array(
                'user_id'    => $user->ID,
                'username'   => wpl_request::getVar('username'),
                'password'   => wpl_request::getVar('password'),
                'secret'     => $query->secret_key,
                'ip'         => $_SERVER['REMOTE_ADDR'],
            );

            return wpl_addon_idx::service($auth);
        }

    }

    // delete client from cache server
    protected static function delete($email = false)
    {

        if ($email == false)
        {
            $current_user = wp_get_current_user();

            if($current_user->user_email)
            {
                $auth = self::get_by_email_address($current_user->user_email);

                if($auth != false)
                {

                    $formparams = array(
                        'mls_id'   => $auth['mls_id'],
                        'provider' => $auth['provider'],
                    );

                    $delete = new delete();
                    $delete_provider = $delete->build($auth['secret']);

                    // call delete method
                    return wpl_addon_idx::delete_client(false,$formparams,$auth);
                }
            }

            $return = array(
                'message'   => 'email address could not be found',
                'status'    => 404
            );

            wp_send_json($return);
        }

        // reset method
        $auth = self::get_by_email_address($email);

        if($auth != false)
        {
            $formparams = array(
                'email'   => $email,
                'secret'  => $auth['secret'],
            );

            // call delete method
            $reset = wpl_addon_idx::reset_client(false,$formparams,$auth);

            if($reset == true)
            {
                $delete = new delete();
                return $delete->build($auth['secret']);
            }

            return false;
        }

        return false;

    }

    // calculate data and time and status
    protected static function status()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                $formparams = array(
                    'mls_id'   => $auth['mls_id']
                );

                return wpl_addon_idx::status(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    protected static function get_keys()
    {
        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
               $response = array(
                   'secret' => $auth['secret']
               );

               wp_send_json($response);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    protected function check_payment()
    {
        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            $formparams = array(
                'email' => $current_user->user_email
            );

            if($auth != false)
            {
                return wpl_addon_idx::check_payment(false,$formparams,$auth);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    protected static function back_step()
    {
        $current_user = wp_get_current_user();

        $step = wpl_request::getVar('step_name');

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                $step_value = self::get_wizard_step_name();

                if ($step_value != false and $step == $step_value)
                {
                    $formparams = array(
                        'email'        => $current_user->user_email,
                        'step_name'    => $step_value
                    );

                    return wpl_addon_idx::back(false,$formparams,$auth);
                }

                // array of response
                $return = array(
                    'status' => 500
                );

                wp_send_json($return);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // This method resets trial data for IDX user
    protected static function reset()
    {

        // first check the valid version status
        $valid_status = self::get_wizard_step_value();

        if ($valid_status > 1 || $valid_status == false)
        {
            $return = array(
                'mesage' => 'you cannot use reset method',
                'status' => 500
            );

            wp_send_json($return);

        }

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                // First we should be call delete method Statically
                self::delete($current_user->user_email);

                // response
                $return = array(
                    'message'   => 'reseted',
                    'status'    => 200
                );

                wp_send_json($return);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // load trial data
    protected static function load_trial_data()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $user = get_user_by( 'email', $current_user->user_email);

            // get the secret key and token of the current user by current user email
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                return wpl_addon_idx::load_trial_data(false,$auth,$user->ID);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // save client request on cache server
    protected static function save_client_request()
    {

        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            $formparams = array(
                'provider' => wpl_request::getVar('provider'),
                'state'    => wpl_request::getVar('state'),
                'domain'   => get_site_url()
            );

            return wpl_addon_idx::save_client_request(false,$formparams,$auth);
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);

    }

    // check if idx add-on is active
    protected static function check_idx()
    {
        $status = self::prevent_idx_user();

        if ($status == false)
        {
            $return = array(
                'message'   => 'IDX is active',
                'status'    => 200
            );

            wp_send_json($return);
        }

        $return = array(
            'message'   => 'IDX is not active',
            'status'    => 500
        );

        wp_send_json($return);

    }

    // protect the trial version
    protected static function protect_trial()
    {
        $current_user = wp_get_current_user();

        if($current_user->user_email)
        {
            $auth = self::get_by_email_address($current_user->user_email);

            if($auth != false)
            {
                $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_trial_logs` WHERE `secret` = '{$auth['secret']}' ORDER BY `id` DESC LIMIT 1", 
                    'loadObject'
                );

                if($query)
                {
                    $return = array(
                        'message'   => 'you cannnot use trial version',
                        'status'    => 500
                    );

                    wp_send_json($return);
                }

                $return = array(
                    'message'   => 'OK',
                    'status'    => 200
                );

                wp_send_json($return);
            }
        }

        $return = array(
            'message'   => 'email address could not be found',
            'status'    => 404
        );

        wp_send_json($return);
    }

    // Gets the Secret key,Token,provider id and name by client email address
    public static function get_by_email_address($email)
    {

        // Verifies that an email is valid.
        if (is_email($email))
        {
            // get the secret key and token of the current user by current use email
            $query = wpl_db::select("SELECT #__wpl_addon_idx_users_providers.provider_short_name,#__wpl_addon_idx_users.secret_key,#__wpl_addon_idx_users_providers.mls_id,#__wpl_addon_idx_users.id,#__wpl_addon_idx_users.token FROM `#__wpl_addon_idx_users` LEFT JOIN #__wpl_addon_idx_users_providers ON #__wpl_addon_idx_users.secret_key = #__wpl_addon_idx_users_providers.user_secret_key WHERE `email` = '{$email}'", 
                'loadObject'
            );

            if($query)
            {
                $formparams = array(
                    'secret'   => $query->secret_key,
                    'token'    => $query->token,
                    'mls_id'   => $query->mls_id,
                    'provider' => $query->provider_short_name,
                );

                return $formparams;
            }

            return false;
        }

        return false;
    }

    // Gets listing type and property type by secret key
    protected static function get_propety_settings_by_secret($secret,$mlsId)
    {

        $query = wpl_db::select("SELECT * FROM `#__wpl_addon_idx_users_providers` WHERE `user_secret_key` = '{$secret}' AND `mls_id` = '{$mlsId}'", 
            'loadObject'
        );

        if($query)
        {
            $settings = array(
                'property_type'  => $query->property_type
            );

            return $settings;
        }

        return false;
    }

    // prevent one website to register multiple times with diffrent users in idx
    protected static function prevent_idx_user()
    {
        $query = wpl_db::select("SELECT `step_value` FROM `#__wpl_addon_idx_user_wizard_steps` ORDER BY id DESC LIMIT 1", 
            'loadObject'
        );

        if ($query)
        {
            if($query->step_value == self::final_step)
            {
                return false;
            }
        }

        return true;
    }

    protected static function get_wizard_step_name()
    {
        $query = wpl_db::select("SELECT `step_name` FROM `#__wpl_addon_idx_user_wizard_steps` ORDER BY id DESC LIMIT 1", 
            'loadObject'
        );

        if($query)
        {
            return $query->step_name;
        }

        return false;
    }

    protected static function get_wizard_step_value()
    {

        $query = wpl_db::select("SELECT `step_value` FROM `#__wpl_addon_idx_user_wizard_steps` ORDER BY id DESC LIMIT 1", 
            'loadObject'
        );

        if($query)
        {
            return $query->step_value;
        }

        return false;
    }
}