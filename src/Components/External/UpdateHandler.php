<?php
namespace shellpress\v1_2_4\src\Components\External;

use shellpress\v1_2_4\src\Shared\Components\IComponent;

class UpdateHandler extends IComponent {

	/** @var string */
	protected $serverUrl;

	/** @var array */
	protected $requestBodyArgs;

	/** @var string */
	protected $appDirBasename;

	/**
	 * Called on creation of component.
	 *
	 * @return void
	 */
	protected function onSetUp() {}

	/**
	 * Registers update_plugins transient filter.
	 *
	 * @param string $serverUrl       - URL to server which we will ask for updates.
	 * @param array  $requestBodyArgs - POST arguments passed when making request for updates.
	 *
	 * @return void
	 */
	public function setFeedSource( $serverUrl, $requestBodyArgs = array() ) {

		$this->serverUrl       = $serverUrl;
		$this->requestBodyArgs = $requestBodyArgs;
		$this->appDirBasename  = basename( dirname( $this::s()->getMainPluginFile() ) );

		if( $this->s()->isInsidePlugin() ){
			add_filter( 'pre_set_site_transient_update_plugins',    array( $this, '_f_addUpdateInfoToAppTransient' ) );
		}

		if( $this->s()->isInsideTheme() ){
			add_filter( 'pre_set_site_transient_update_themes',     array( $this, '_f_addUpdateInfoToAppTransient' ) );
		}

		add_filter( 'upgrader_source_selection',                    array( $this, '_f_normalizeDirectoryName' ) );

	}

	/**
	 * Hides package information from update_plugins transient.
	 *
	 * @param string $info
	 *
	 * @return void
	 */
	public function disableUpdateOfPackage() {

		if( $this::s()->isInsidePlugin() ){
			add_filter( 'site_transient_update_plugins', array( $this, '_f_removeUpdatePackageForThisApp' ) );
		}

		if( $this::s()->isInsideTheme() ) {
			add_filter( 'site_transient_update_themes', array( $this, '_f_removeUpdatePackageForThisApp' ) );
		}

	}

	/**
	 * Processes raw response from remote location.
	 *
	 * @param object $transient
	 * @param mixed $response       - Raw remote response.
	 * @param string $responseKey   - Basename ( key ) of plugin/theme.
	 *
	 * @return object
	 */
	protected function addRemoteResponseToTransient( $transient, $response, $responseKey ) {

		$responseBody   = wp_remote_retrieve_body( $response );
		$responseCode   = wp_remote_retrieve_response_code( $response );

		if( is_wp_error( $response ) || $responseCode !== 200 ) return $transient;  //  Something is wrong.

		//  ----------------------------------------
		//  Plugins use json to object conversion.
		//  ----------------------------------------

		if( $this::s()->isInsidePlugin() ){

			$encoded = json_decode( $responseBody );

			if( $encoded && isset( $encoded->new_version ) && version_compare( $encoded->new_version, $this::s()->getPluginVersion(), '>' ) ){

				$transient->response[ $responseKey ] = (object) $encoded;

			} else {

				unset( $transient->response[ $responseKey ]);

			}

		}

		//  ----------------------------------------
		//  Themes use json to array conversion.
		//  ----------------------------------------

		if( $this::s()->isInsideTheme() ){

			$encoded = (array) json_decode( $responseBody, true );

			if( $encoded && isset( $encoded['new_version'] ) && version_compare( $encoded['new_version'], $this::s()->getFullPluginVersion(), '>' ) ){

				$transient->response[ $responseKey ] = $encoded;

			} else {

				unset( $transient->response[ $responseKey ]);

			}

		}

		return $transient;

	}

	//  ================================================================================
	//  FILTERS
	//  ================================================================================

	/**
	 * @param object $transient
	 *
	 * @internal
	 *
	 * @return object
	 */
	public function _f_addUpdateInfoToAppTransient( $transient ) {

		$basename = $this::s()->getPluginBasename();

		if( ! isset( $transient->response ) ) return $transient;                //  Check, if we have an array set. Sometimes there are errors.
		if( isset( $transient->response[ $basename ] ) ) return $transient;     //  Do not fire remote check version twice.

		//  ----------------------------------------
		//  Prepare data for request
		//  ----------------------------------------

		$requestBodyArgs = array(
			'plugin_basename'   =>  $basename
		);
		$requestBodyArgs = wp_parse_args( (array) $this->requestBodyArgs, $requestBodyArgs );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$response = wp_remote_get( $this->serverUrl, array(
			'body'      =>  $requestBodyArgs
		) );

		//  ----------------------------------------
		//  Maybe update transient
		//  ----------------------------------------

		return $this->addRemoteResponseToTransient( $transient, $response, $basename );

	}

	/**
	 * @param (object) $transient
	 *
	 * @internal
	 *
	 * @return (object)
	 */
	public function _f_removeUpdatePackageForThisApp( $transient ) {

		if( ! isset( $transient->response ) ) return $transient;

		$basename = $this::s()->getPluginBasename();

		if( isset( $transient->response[ $basename ] ) ){
			$response =& $transient->response[ $basename ];
			$response->package = '';
		}

		return $transient;

	}

	/**
	 * Sometimes releases are zipped as directory name with tag version suffix.
	 * This method provides same name as current plugin/theme.
	 *
	 * @param $source
	 *
	 * @internal
	 *
	 * @return string
	 */
	public function _f_normalizeDirectoryName( $source ) {

		if( strpos( $source, $this->appDirBasename ) === false ) return $source;  //  Are we talking about this plugin/theme?

		$this::s()->log->close();   //  TODO - This should be called in other hook.

 		$newSource  = trailingslashit( dirname( $source ) ) . trailingslashit( $this->appDirBasename );
		$result     = rename( $source, $newSource );

		return $result ? $newSource : $source;

	}

}