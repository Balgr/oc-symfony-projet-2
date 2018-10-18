<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

_wpl_import($this->tpl_path.'.scripts.js');
?>


<div class="wpl-idx-addon wrap wpl-wp settings-wp">
    <div class="wpl-idx-wizard-main wpl-idx-valid">
        <header>
            <div id="icon-settings" class="icon48"></div>
            <h2><?php echo __('Organic IDX / Settings', 'wpl'); ?></h2>
        </header>
        <section class="sidebar-wp">
          <div class="wpl_idx_servers_list"><div class="wpl_show_message"></div></div>
          <table id="wpl-idx-setting-table" class="wpl-idx-addon-table">
              <thead>
                  <tr class="wpl-idx-addon-table-row">
                      <td class="wpl-idx-addon-table-title" colspan="3">
                          <?php echo __('MLS Provider','wpl'); ?>
                      </td>
                      <td class="wpl-idx-addon-table-title">
                          <?php echo __('Status','wpl'); ?>
                      </td>
                      <!-- <td class="wpl-idx-addon-table-title">
                          <?php echo __('Actions','wpl'); ?>
                      </td> -->
                  </tr>
              </thead>
              <tbody>
                  <tr>
                      <td colspan="5">
                          <div class="message">
                              <?php echo __('No MLS Provider is Found! In order to add one please ', 'wpl').'<a href="'.wpl_global::get_wpl_admin_menu('wpl_addon_idx').'">'.__('Click here', 'wpl').'</a>';?>
                          </div>
                      </td>
                  </tr>
              </tbody>
          </table>
        </section>
    </div>
</div>