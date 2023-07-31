<?php
namespace shellpress\v1_4_1\src\Components\Internal;

/**
 * Date: 12.04.2018
 * Time: 21:39
 */

use shellpress\v1_4_1\src\Shared\Components\IComponent;

class ExtractorHandler extends IComponent {

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {

		if( is_admin() ){

			//  ----------------------------------------
			//  Filters
			//  ----------------------------------------

			add_filter( 'plugin_row_meta',      array( $this, '_f_addPluginDownloadToTable' ), 10, 2 );

			//  ----------------------------------------
			//  Actions
			//  ----------------------------------------

			add_action( 'admin_init',           array( $this, '_a_downloadPluginCallback' ) );

		}

	}

	/**
	 * Returns current plugin base file name with extension.
	 *
	 * @return string
	 */
	protected function getCurrentPluginFileName() {

		$pluginFile = $this->s()->getMainPluginFile();

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

		if( $this->s()->isInsidePlugin() ){

			$currentPluginFile = $this->s()->getMainPluginFile();

			if( $pluginName === plugin_basename( $currentPluginFile ) ){

				$downloadUrl = add_query_arg( 'sp_download', $this->getCurrentPluginFileName() );
				$downloadUrl = wp_nonce_url( $downloadUrl, 'sp_download' );

				$pluginMeta[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $downloadUrl, __( 'Download as file' ) );

			}

		}

		return $pluginMeta;

	}

	/**
	 * Called on init.
	 */
	public function _a_downloadPluginCallback() {

		if( array_key_exists( 'sp_download', $_GET )
		    && $this->s()->isInsidePlugin()
		    && $_GET['sp_download'] === $this->getCurrentPluginFileName()
		){

			if( array_key_exists( '_wpnonce', $_GET ) && wp_verify_nonce( $_GET['_wpnonce'], 'sp_download' ) ) {

				//  ----------------------------------------
				//  Prepare Names
				//  ----------------------------------------

				$newFileName        = str_replace( '.php', '.zip', $this->getCurrentPluginFileName() );
				$newFileFullPath    = rtrim( sys_get_temp_dir(), '/' ) . '/' . $newFileName;

				//  ----------------------------------------
				//  Pack plugin
				//  ----------------------------------------

				$currentPluginDir = dirname( $this->s()->getMainPluginFile() );

				$result = $this->s()->utility->zipData( $currentPluginDir, $newFileFullPath, true );

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