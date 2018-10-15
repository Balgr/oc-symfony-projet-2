<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/**
 * Profile Listing Shortcode for Divi Builder
 * @author Howard <howard@realtyna.com>
 * @package WPL PRO
 */
class wpl_page_builders_divi_profile_listing extends ET_Builder_Module
{
    public $fields_defaults;
    public $settings;

    public function init()
    {
        $this->name = __('Profile/Agent Listing', 'wpl');
        $this->slug = 'et_pb_wpl_profile_listing';
        $this->fb_support = false;

        $this->whitelisted_fields = array();
		$this->fields_defaults = array();
        
        // Global WPL Settings
		$this->settings = wpl_global::get_settings();
	}
    
    public function get_fields()
    {
        // Module Fields
        $fields = array();
        
        $profile_listing_layouts = wpl_global::get_layouts('profile_listing', array('message.php'), 'frontend');
        
        $profile_listing_layouts_options = array();
        foreach($profile_listing_layouts as $profile_listing_layout) $profile_listing_layouts_options[$profile_listing_layout] = esc_html__($profile_listing_layout, 'wpl');
        
        $fields['tpl'] = array(
            'label'           => esc_html__('Layout', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $profile_listing_layouts_options,
            'description'     => esc_html__('Layout of the page', 'wpl'),
        );
        
        $user_types = wpl_users::get_user_types();
        
        $user_types_options = array();
        $user_types_options[''] = '-----';
        
        foreach($user_types as $user_type) $user_types_options[$user_type->id] = esc_html__($user_type->name, 'wpl');
        
        $fields['sf_select_membership_type'] = array(
            'label'           => esc_html__('User Type', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $user_types_options,
            'description'     => esc_html__('You can select different user type for filtering the users', 'wpl'),
        );
        
        $memberships = wpl_users::get_wpl_memberships();
        
        $memberships_options = array();
        $memberships_options[''] = '-----';
        
        foreach($memberships as $membership) $memberships_options[$membership->id] = esc_html__($membership->membership_name, 'wpl');
        
        $fields['sf_select_membership_id'] = array(
            'label'           => esc_html__('Membership', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $memberships_options,
            'description'     => esc_html__('You can filter the users by their membership package', 'wpl'),
        );
        
        $pages = wpl_global::get_wp_pages();
        
        $pages_options = array();
        $pages_options[''] = '-----';
        
        foreach($pages as $page) $pages_options[$page->ID] = esc_html__($page->post_title, 'wpl');
        
        $fields['wpltarget'] = array(
            'label'           => esc_html__('Target Page', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $pages_options,
        );
        
        $page_sizes = explode(',', trim($this->settings['page_sizes'], ', '));
        
        $page_sizes_options = array();
        foreach($page_sizes as $page_size) $page_sizes_options[$page_size] = esc_html__($page_size, 'wpl');
        
        $fields['limit'] = array(
            'label'           => esc_html__('Page Size', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $page_sizes_options,
        );
        
        $fields['wplpagination'] = array(
            'label'           => esc_html__('Pagination', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => array(
                '' => '-----',
                'scroll' => esc_html__('Scroll Pagination', 'wpl'),
            ),
        );
        
        $sorts = wpl_sort_options::render(wpl_sort_options::get_sort_options(2, 1));
        
        $sorts_options = array();
        foreach($sorts as $sort) $sorts_options[$sort['field_name']] = esc_html__($sort['name'], 'wpl');
        
        $fields['orderby'] = array(
            'label'           => esc_html__('Order By', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => $sorts_options,
        );
        
        $fields['order'] = array(
            'label'           => esc_html__('Order', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => array(
                'ASC' => esc_html__('Ascending', 'wpl'),
                'DESC' => esc_html__('Descending', 'wpl'),
            ),
        );
        
        $fields['wplcolumns'] = array(
            'label'           => esc_html__('Columns Count', 'wpl'),
            'type'            => 'select',
            'option_category' => 'basic_option',
            'options'         => array(
                '3' => '3',
                '1' => '1',
                '2' => '2',
                '4' => '4',
                '6' => '6',
            ),
        );

		return $fields;
	}
    
    public function shortcode_callback($atts, $content = NULL, $function_name = NULL)
    {
        $shortcode_atts = '';
        foreach($atts as $key=>$value)
        {
            if(trim($value) == '' or $value == '-1') continue;
            if($key == 'tpl' and $value == 'default') continue;
            
            $shortcode_atts .= $key.'="'.$value.'" ';
        }
        
        return do_shortcode('[wpl_profile_listing'.(trim($shortcode_atts) ? ' '.trim($shortcode_atts) : '').']');
    }
}