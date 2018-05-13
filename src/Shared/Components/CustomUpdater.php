<?php
namespace shellpress\v1_2_1\src\Shared\Components;

/**
 * Date: 13.05.2018
 * Time: 21:02
 */

abstract class CustomUpdater extends IComponent {

	public function setUpdateSource( $url ) {

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, '_f_modifyUpdatePluginsTransient' ) );

	}

	//  ================================================================================
	//  FILTERS
	//  ================================================================================

	/**
	 * @param object $transient
	 *
	 * @return object
	 */
	public function _f_modifyUpdatePluginsTransient( $transient ) {

//		wp_die( $this::s()->utility->getFormattedVarExport( $transient ) );

		//  Check, if we have an array set. Sometimes there are errors.
		if( ! isset( $transient->response ) ) return $transient;

		$basename = plugin_basename( $this::s()->getMainPluginFile() );

		$responseForPlugin = new \stdClass();   //  TODO

		//  TODO - downloading update info

		if( $responseForPlugin ){

			$transient->response[ $basename ] = $responseForPlugin;

		} else {

			unset( $transient->response[ $basename ]);

		}

		return $transient;

	}

}