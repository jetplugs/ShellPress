<?php
namespace shellpress\v1_2_0\src\Handlers\Internal;

/**
 * Date: 12.04.2018
 * Time: 21:39
 */

use shellpress\v1_2_0\src\Handlers\IHandler;

class ExtractorHandler extends IHandler {

	public function registerDownloadButton() {

		//  ----------------------------------------
		//  Filters
		//  ----------------------------------------

		add_filter( 'plugin_row_meta',      array( $this, '_f_addPluginDownloadToTable' ), 10, 2 );

		//  ----------------------------------------
		//  Actions
		//  ----------------------------------------

		add_action( 'init',                 array( $this, '_a_downloadPluginCallback' ) );

	}

	/**
	 * Returns current plugin base file name with extension.
	 *
	 * @return string
	 */
	protected function getCurrentPluginFileName() {

		$pluginFile = $this->shell()->getMainPluginFile();

		return pathinfo( $pluginFile, PATHINFO_BASENAME );

	}

	/**
	 * Adds download plugin zip to plugin meta row.
	 * Called on plugin_row_meta.
	 *
	 * @param string[] $pluginMeta
	 * @param string $pluginName
	 *
	 * return string[]
	 */
	public function _f_addPluginDownloadToTable( $pluginMeta, $pluginName ) {

		if( true ){ //  TODO

			$currentPluginFile = $this->shell()->getMainPluginFile();

			if( $pluginName === plugin_basename( $currentPluginFile ) ){

				$downloadUrl = add_query_arg( 'sp_download', $this->getCurrentPluginFileName() );
				$downloadUrl = wp_nonce_url( $downloadUrl, 'sp_download' );

				$pluginMeta[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $downloadUrl, __( 'Download' ) );

			}

		}

		return $pluginMeta;

	}

	/**
	 * Called on init.
	 */
	public function _a_downloadPluginCallback() {

		if( is_admin() && array_key_exists( 'sp_download', $_GET ) && $_GET['sp_download'] === $this->getCurrentPluginFileName() ){

			if( array_key_exists( '_wpnonce', $_GET ) && wp_verify_nonce( $_GET['_wpnonce'], 'sp_download' ) ) {

				//  ----------------------------------------
				//  Prepare Names
				//  ----------------------------------------

				$newFileName        = str_replace( '.php', '.zip', $this->getCurrentPluginFileName() );
				$newFileFullPath    = rtrim( sys_get_temp_dir(), '/' ) . '/' . $newFileName;

				//  ----------------------------------------
				//  Pack plugin
				//  ----------------------------------------

				$currentPluginDir = dirname( $this->shell()->getMainPluginFile() );

				$result = $this->shell()->utility->zipData( $currentPluginDir, $newFileFullPath );

				if( ! $result ) return; //  Something went wrong.

				//  ----------------------------------------
				//  Download it
				//  ----------------------------------------

				header( "Content-type: application/zip" );
				header( "Content-Disposition: attachment; filename={$newFileName}" );
				header( "Content-length: " . filesize( $newFileFullPath ) );
				header( "Pragma: no-cache" );
				header( "Expires: 0" );

                readfile( $newFileFullPath );

                exit;

			} else {

				wp_die( 'Please try again.' );

			}

		}

	}

}