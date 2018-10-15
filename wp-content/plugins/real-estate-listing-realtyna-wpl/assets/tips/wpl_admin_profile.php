<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/** define tips **/
$tips = array();
$index = 1;

$content = '<h3>'.__('Fill your own Profile', 'wpl').'</h3><p>'.__("Here you can update your own profile information.", 'wpl').'</p>';
$tips[] = array('id'=>$index++, 'selector'=>'.wrap.wpl-wp h2:first', 'content'=>$content, 'position'=>array('edge'=>'top', 'align'=>'left'), 'buttons'=>array(2=>array('label'=>__('Next', 'wpl'))));

$content = '<h3>'.__('Finalize the Form', 'wpl').'</h3><p>'.__("After filling your profile information, don't forget to finalize the form.", 'wpl').'</p>';
$tips[] = array('id'=>$index++, 'selector'=>'#wpl_profile_finalize_button', 'content'=>$content, 'position'=>array('edge'=>'bottom', 'align'=>'left'), 'buttons'=>array(2=>array('label'=>__('Next Menu', 'wpl'), 'code'=>'window.location.href = "admin.php?page=wpl_admin_add_listing&wpltour=1";'), 3=>array('label'=>__('Previous', 'wpl'))));

return $tips;