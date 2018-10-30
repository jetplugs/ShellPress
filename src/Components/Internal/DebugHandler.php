<?php
namespace shellpress\v1_2_9_2\src\Components\Internal;

/**
 * Date: 29.05.2018
 * Time: 22:19
 */

use shellpress\v1_2_9_2\src\Shared\Components\IComponent;

class DebugHandler extends IComponent {

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		if( is_admin() ) {

			//  ----------------------------------------
			//  Filters
			//  ----------------------------------------

			add_filter( 'plugin_row_meta', array( $this, '_f_addShellPressVersionToPluginRow' ), 10, 2 );

		}

	}

	/**
	 * Adds ShellPress version info to plugin row.
	 * Called on plugin_row_meta.
	 *
	 * @param string[] $pluginMeta
	 * @param string $pluginName
	 *
	 * return string[]
	 */
	public function _f_addShellPressVersionToPluginRow( $pluginMeta, $pluginName ) {

		if( $this->s()->isInsidePlugin() ){

			if( $pluginName === plugin_basename( $this->s()->getMainPluginFile() ) ){

				$namespaceParts = explode( '\\', __CLASS__ );

				$version = $namespaceParts[1];
				$version = str_replace( '_', '.', $version );

				$pluginMeta[] = sprintf( '<span>ShellPress %1$s</span>', $version );

			}

		}

		return $pluginMeta;

	}

}