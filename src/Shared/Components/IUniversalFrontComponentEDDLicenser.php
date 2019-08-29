<?php
namespace shellpress\v1_3_76\src\Shared\Components;

use shellpress\v1_3_76\src\Shared\RestModels\UniversalFrontResponse;
use WP_Error;
use WP_REST_Request;

/**
 * @author jakubkuranda@gmail.com
 * Date: 29.08.2019
 * Time: 15:00
 */

abstract class IUniversalFrontComponentEDDLicenser extends IUniversalFrontComponent {

	/** @var string */
	private $_apiUrl = '';

	/**
	 * Returns name of shortcode.
	 *
	 * @return string
	 */
	public function getShortCodeName() {

		return sanitize_key( __CLASS__ );

	}

	/**
	 * Returns array of action names to refresh this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToRefreshOn() {

		return array();

	}

	/**
	 * Returns array of action names to submit this shortcode on.
	 *
	 * @return string[]
	 */
	public function getActionsToSubmitOn() {

		return array();

	}

	/**
	 * Called when front end form is sent to rest API.
	 * Returns UniversalFrontResponse object.
	 *
	 * @param UniversalFrontResponse $universalFrontResponse
	 * @param WP_REST_Request        $request
	 *
	 * @return UniversalFrontResponse
	 */
	protected function processUniversalFrontResponse( $universalFrontResponse, $request ) {

		return $universalFrontResponse;

	}

	/**
	 * Returns inner component's HTML based on request.
	 * Hints:
	 * - this method is designed to be used by developers by packing it inside UniversalFrontResponse
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return string
	 */
	public function getInnerHtml( $request ) {

		$html = '';

		return $html;

	}

	/**
	 * @return string
	 */
	private function _getApiUrl() {

		return $this->_apiUrl;

	}

	/**
	 * Sets API url. This should be your EDD store website.
	 *
	 * @param string $apiUrl
	 *
	 * @return void
	 */
	public function setApiUrl( $apiUrl ) {

		$this->_apiUrl = $apiUrl;

	}

	/**
	 * @return WP_Error|array
	 */
	public function remoteActivateLicense(){

		if( ! $this->_getApiUrl() ) return new WP_Error( '', 'API url is not defined.' );   //  Bail early.

		//  Prepare URL with arguments.
		$fullUrl = add_query_arg( array(
			'edd_action'        =>  'activate_license',
			'item_id'           =>  '',
			'license'           =>  '',
			'url'               =>  get_home_url()
		), $this->_apiUrl );

		if( $request = wp_remote_get( $fullUrl ) ){

		}

	}

	//  ================================================================================
	//
	//  ================================================================================

}