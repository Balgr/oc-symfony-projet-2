<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/** define tips **/
$tips = array();
$index = 1;

$content = '<h3>'.__('WPL Payments', 'wpl').'</h3><p>'.__("Here you can find all payment configurations and transactions.", 'wpl').'</p>';
$tips[] = array('id'=>$index++, 'selector'=>'.wrap.wpl-wp h2:first', 'content'=>$content, 'position'=>array('edge'=>'top', 'align'=>'left'), 'buttons'=>array(2=>array('label'=>__('Next', 'wpl'))));

$content = '<h3>'.__('Payment gateways', 'wpl').'</h3><p>'.__("Enable your desired payment gateways and set the credentials here.", 'wpl').'</p>';
$tips[] = array('id'=>$index++, 'selector'=>'#wpl_payments_options_tab', 'content'=>$content, 'position'=>array('edge'=>'top', 'align'=>'left'), 'buttons'=>array(2=>array('label'=>__('Next', 'wpl')), 3=>array('label'=>__('Previous', 'wpl'))));

$content = '<h3>'.__('Payment Transactions', 'wpl').'</h3><p>'.__("Also you can see all transactions here.", 'wpl').'</p>';
$tips[] = array('id'=>$index++, 'selector'=>'#wpl_payments_transactions_tab', 'content'=>$content, 'position'=>array('edge'=>'top', 'align'=>'left'), 'buttons'=>array(2=>array('label'=>__('Next Menu', 'wpl'), 'code'=>'window.location.href = "admin.php?page=wpl_admin_user_manager&wpltour=1";'), 3=>array('label'=>__('Previous', 'wpl'))));

return $tips;