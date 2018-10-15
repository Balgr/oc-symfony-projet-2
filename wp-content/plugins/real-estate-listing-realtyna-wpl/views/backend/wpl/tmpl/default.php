<?php
/** no direct access * */
defined('_WPLEXEC') or die('Restricted access');

$this->_wpl_import($this->tpl_path . '.scripts.css');
$this->_wpl_import($this->tpl_path . '.scripts.js');
?>
<div class="wrap wpl-wp wpl-dashboard-wp">

    <header>
        <div class="wpl-icon-header wpl-icon-dashboard"></div>
        <h2>
            <?php echo __('WPL', 'wpl'); ?>&nbsp;<?php echo(wpl_global::check_addon('pro') ? 'PRO' : 'Basic'); ?>
            <span class="wpl-dashboard-ver">v<?php echo wpl_global::wpl_version(); ?></span>
        </h2>
    </header>
    
    <div class="wpl-flashes-container"><?php echo wpl_flash::get(); ?></div>
    
    <!-- Generating announcements -->
    <?php $this->announcements(); ?>
    
    <div id="dashboard-links-wp">
        <ul>
            <?php foreach($this->submenus as $submenu): if(!wpl_users::has_menu_access($submenu->menu_slug, wpl_users::get_cur_user_id())) continue; ?>
                <li class="link-<?php echo $submenu->id; ?>">
                    <a href="<?php echo wpl_global::get_wp_admin_url(); ?>admin.php?page=<?php echo $submenu->menu_slug; ?>">
                        <span class="box"><i></i></span>
                        <span class="title">
                            <?php echo __($submenu->menu_title, 'wpl'); ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <?php if(wpl_users::is_super_admin()): ?>
    <div class="sidebar-wp sidebar-float">
        <div class="side-ni-addons">
            <div class="sidebar-wp sidebar-float">
                
                <div class="rt-same-height sidebar-float">

                    <!-- Generating optional addons -->
                    <?php $this->not_installed_addons(); ?>

                    <!-- Special Discount -->
                    <?php if(!wpl_global::check_addon('pro')): ?>
                    <div class="side-4 side-discount" id="wpl_dashboard_discount">
                        <div class="panel-wp">
                            <h3><?php echo __('Special Discount', 'wpl'); ?></h3>

                            <div class="panel-body">
                                <div>
                                    <?php echo __('Get <span>25%</span> discount on <span>WPL</span> <br/> and <span>all the add-ons!</span>', 'wpl'); ?>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <?php echo __('Special Hidden Discount!', 'wpl'); ?>
                            </div>
                            <a class="wpl_shop_discount" href="https://realtyna.com/coupon/DASHBOARD8ab448b28a03" target="_blank"></a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- WPL change-log -->
                    <div class="side-<?php echo wpl_global::check_addon('pro') ? '7' : '3'; ?> side-changes js-full-height" data-minuse-size="56" id="wpl_dashboard_changelog">
                        <div class="panel-wp">
                            <h3><?php echo __('Changelog', 'wpl'); ?></h3>

                            <div class="panel-body">
                                <?php _wpl_import('assets.changelogs.wpl'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rt-same-height sidebar-float">
                    <!-- Generating addons -->
                    <?php $this->generate_addons(); ?>
                </div>

                <!-- Generating statistic section -->
                <?php $this->knowledgebase(); ?>

                <div class="rt-same-height sidebar-float">
                    <!-- Generating support section -->
                    <?php $this->support(); ?>

                    <!-- Generating share box -->
                    <?php $this->sharebox(); ?>
                </div>
                
                <div class="rt-same-height sidebar-float">
                    <!-- Generating statistic section -->
                    <?php $this->statistic(); ?>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer>
        <div class="logo"></div>
    </footer>
</div>