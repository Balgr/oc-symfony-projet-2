<?php

_wpl_import('libraries.addon_idx.AbstractClass');

class delete extends AbstractClass
{

    /**
     * @param $secret
     * @return mixed
     * Delete Provider Information from WPL
     */

    public function provider($secret)
    {
        $query = "DELETE FROM `#__wpl_addon_idx_users_providers` WHERE `user_secret_key` = '$secret'";
        return wpl_db::q($query);
    }

    /**
     * @param $secret
     * @return mixed
     * Delete Payment Information from WPL
     */

    public function payment($secret)
    {
        $query = "DELETE FROM `#__wpl_addon_idx_payments` WHERE `secret_key` = '$secret'";
        return wpl_db::q($query);
    }

    /**
     * @param $secret
     * Delete Client wizard steps
     */

    public function configuration($secret)
    {
        $rows = wpl_db::select("SELECT `id` FROM `#__wpl_addon_idx_user_wizard_steps`", 'loadObjectList');

        foreach ($rows as $row)
        {
            $query = "DELETE FROM `#__wpl_addon_idx_user_wizard_steps` WHERE `id` = '$row->id'";
            wpl_db::q($query);
        }
    }

    /**
     * @param $secret
     * @return mixed
     * Deletes The IDX User by secret key
     */

    public function idx_user($secret)
    {
        $query = "DELETE FROM `#__wpl_addon_idx_users` WHERE `secret_key` = '$secret'";
        return wpl_db::q($query);
    }

    /**
     * @param $secret
     * @return mixed
     * Delete Service Task List
     */

    public function service_log($secret)
    {
        $query = "DELETE FROM `#__wpl_addon_idx_tasks` WHERE `secret` = '$secret'";
        return wpl_db::q($query);
    }

    /**
     * Get Listing id and call listing delete method and item delete method
     */

    public function listings()
    {
        $rows = wpl_db::select("SELECT `id` FROM `#__wpl_properties` WHERE `mls_id` LIKE '%CRMLS%' or `mls_id` LIKE '%MFRLMS%'", 'loadObjectList');

        foreach ($rows as $row)
        {
            if(self::delete_listings($row->id) != false)
            {
                self::delete_items($row->id);
            }
        }
    }

    /**
     * @param $listing_id
     * @return mixed
     * Delete Listing by Listing Id
     */
    protected function delete_listings($listing_id)
    {
        $query = "DELETE FROM `#__wpl_properties` WHERE `id` = '$listing_id'";
        return wpl_db::q($query);
    }

    /**
     * @param $listing_id
     * Delete Item by Listing Id
     */

    protected function delete_items($listing_id)
    {
        $query = "DELETE FROM `#__wpl_items` WHERE `parent_id` = '$listing_id'";
        wpl_db::q($query);
    }
}