<?php
namespace shellpress\v1_4_0\src\Components\External;

use shellpress\v1_4_0\src\Shared\Components\IComponent;

/**
 * @deprecated
 */
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

	}

	/**
	 * Hides package information from update_plugins transient.
	 *
	 * @param string $info
	 *
	 * @return void
	 */
	public function disableUpdateOfPackage() {

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

	}

}