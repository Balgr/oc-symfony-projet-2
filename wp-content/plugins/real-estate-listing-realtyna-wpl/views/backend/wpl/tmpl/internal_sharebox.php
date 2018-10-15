<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');
?>
<div class="side-6 side-share" id="wpl_dashboard_side_share">
    <div class="panel-wp">
        <h3><?php _e('Share / Review', 'wpl'); ?></h3>
        <div class="panel-body">
            <div class="share-box-container">
                <a class="wpl_review_wp" href="https://wordpress.org/plugins/real-estate-listing-realtyna-wpl/" target="_blank"><?php echo __('Inspire us by Rating at', 'wpl'), '<span class="wpl_sharebox_icon-wp"></span>'. __('community', 'wpl'); ?></a>
                <div class="wpl-dashboard-social-icons">
                    <p><?php _e('Share WPL with Others', 'wpl'); ?></p>
                    <ul>
                        <li>
                            <a class="wpl_dashboard_fb" href="<?php echo esc_url('https://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/real-estate-listing-realtyna-wpl/'); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php _e('Share on Facebook', 'wpl'); ?>"><?php _e('Share on Facebook', 'wpl'); ?></a>
                        </li>
                        <li>
                            <a class="wpl_dashboard_google" href="<?php echo esc_url('https://plus.google.com/share?url=https://wordpress.org/plugins/real-estate-listing-realtyna-wpl/'); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php _e('Google+', 'wpl'); ?>"><?php _e('Share on Google+', 'wpl'); ?></a>
                        </li>
                        <li>
                            <a class="wpl_dashboard_twit" href="<?php echo esc_url('https://twitter.com/share?url=https://wordpress.org/plugins/real-estate-listing-realtyna-wpl/'); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php _e('Tweet', 'wpl'); ?>"><?php _e('Share on Twitter', 'wpl'); ?></a>
                        </li>
                        <li>
                            <a class="wpl_dashboard_in" href="<?php echo esc_url('https://www.linkedin.com/shareArticle?mini=true&url=https://wordpress.org/plugins/real-estate-listing-realtyna-wpl/'); ?>" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600'); return false;" title="<?php _e('Share on Linkedin', 'wpl'); ?>"><?php _e('Share on Linkedin', 'wpl'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>