<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://#
 * @since             1.0.0
 * @package           Safe_Media_Delete
 *
 * @wordpress-plugin
 * Plugin Name:       Safe Media Delete
 * Plugin URI:        https://#
 * Description:       Prevents users from deleting media items that are being used as featured images, within post content, or in term edit pages.

 * Version:           1.0.0
 * Author:            adeel
 * Author URI:        https://#
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       safe-media-delete
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SAFE_MEDIA_DELETE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-safe-media-delete-activator.php
 */
function activate_safe_media_delete() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-safe-media-delete-activator.php';
	Safe_Media_Delete_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-safe-media-delete-deactivator.php
 */
function deactivate_safe_media_delete() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-safe-media-delete-deactivator.php';
	Safe_Media_Delete_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_safe_media_delete' );
register_deactivation_hook( __FILE__, 'deactivate_safe_media_delete' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-safe-media-delete.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_safe_media_delete() {

	$plugin = new Safe_Media_Delete();
	$plugin->run();

}
run_safe_media_delete();
