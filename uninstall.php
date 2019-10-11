<?php
/**
 * @package Internals
 *
 * This code is used when the plugin is removed (not just deactivated but actively deleted through the WordPress Admin).
 */

//if uninstall not called from WordPress exit
if( !defined('WP_UNINSTALL_PLUGIN') )
    exit();

/* No options are used in this plugin, so none need to be deleted */
