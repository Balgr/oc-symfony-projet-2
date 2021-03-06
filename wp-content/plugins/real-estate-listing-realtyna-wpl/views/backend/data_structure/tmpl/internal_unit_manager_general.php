<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');
$this->_wpl_import($this->tpl_path . '.scripts.internal_unit_manager_js');
?>
<div id="wpl_data_structure_units" class="wpl_hidden_element"></div>
<table class="widefat page" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th scope="col" class="manage-column" width="50"><?php echo __('Enabled', 'wpl');?></th>
            <th scope="col" class="manage-column" width="50"><?php echo __('Name', 'wpl');?></th>
            <th scope="col" class="manage-column"><?php echo __('Conv .Factor', 'wpl');?></th>
            <th scope="col" class="manage-column"><?php echo __('Move', 'wpl');?></th>
        </tr>
    </thead>
    <tbody class="sortable_unit">
        <?php foreach($this->units as $id=>$unit): ?>
            <tr id="item_row_<?php echo $unit['id']; ?>">
                <td>
                    <span class="action-btn enabled_check <?php echo $unit['enabled'] == 1 ? "icon-enabled" : "icon-disabled"; ?> " 
                    onclick="wpl_unit_enabled_change(<?php echo $unit['id'].', '.$unit['type']; ?>)" id="wpl_ajax_flag_<?php echo $unit['id'].'_'.$unit['type']; ?>"></span>

                    <span id="wpl_property_unit_<?php echo $unit['id'].'_'.$unit['type']; ?>" data-realtyna-lightbox  class="wpl_hidden_element" data-realtyna-href="#wpl_data_structure_units" onclick="unit_enabled_state_replace_form(<?php echo $unit['type'].', '.$unit['id']; ?>);"></span>

                    <span class="wpl_ajax_loader" id="wpl_ajax_loader_<?php echo $unit['id'].'_'.$unit['type']; ?>"></span>
                </td>
                <td width="100">
                    <input type="text" value="<?php echo __($unit['name'], 'wpl'); ?>" data-wpl-id="<?php echo $unit['id']; ?>" onchange="wpl_change_unit_name(this);" />
                    <span class="wpl-loader"></span>
                </td>
                <td width="100">
                    <input type="text" value="<?php echo __($unit['tosi'], 'wpl'); ?>" data-wpl-id="<?php echo $unit['id']; ?>" onchange="wpl_change_unit_tosi(this);" />
                    <span class="wpl-loader"></span>
                </td>				
                <td class="wpl_manager_td">
                    <span class="action-btn icon-move" id="extension_move_1"></span>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>	
