<?php
/**
 * Uninstall procedures
 * 
 * @package LiveDive
 * @author LiveDive <team@livedive.co>
 * @version 1.0.0
 */

// Exit if not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

// Remove options
delete_option( 'livedive_options' );