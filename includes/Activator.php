<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Reactpress
 * @subpackage Reactpress/includes
 * @author     Marco Laspe <marco@rockiger.com>
 */

namespace Rp\Includes;

class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// check im reactpress directory exists if not create it
		wp_mkdir_p(REPR_APPS_PATH);
	}
}
