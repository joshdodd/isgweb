<?php
/*
Plugin Name: WordPress IM
Plugin URI: http://meshfresh.com
Description: Messaging for front-end users. Modified and implemented by MESH. Based on a plugin by Cartpauj.
Version: 1.0
Author: MESH
Author URI: http://meshfresh.com

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//INCLUDE THE CLASS FILE
include_once("pm-class.php");

//DECLARE AN INSTANCE OF THE CLASS
if(class_exists("cartpaujPM"))
	$cartpaujPMS = new cartpaujPM();

//HOOKS
if (isset($cartpaujPMS))
{
	//ACTIVATE PLUGIN
	register_activation_hook(__FILE__ , array(&$cartpaujPMS, "pmActivate"));

	//SETUP TEXT DOMAIN FOR TRANSLATIONS
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain('cartpaujpm', false, $plugin_dir.'/i18n/');

	//ADD SHORTCODES
	add_shortcode('cartpauj-pm', array(&$cartpaujPMS, "displayAll"));

	//ADD ACTIONS
	add_action('init', array(&$cartpaujPMS, "jsInit"));
	add_action('wp_head', array(&$cartpaujPMS, "addToWPHead"));
	add_action('admin_menu', array(&$cartpaujPMS, "addAdminPage"));

	//ADD WIDGET
	register_sidebar_widget(__("Cartpauj-PM Widget", "cartpaujpm"), array(&$cartpaujPMS, "widget"));
}


?>
