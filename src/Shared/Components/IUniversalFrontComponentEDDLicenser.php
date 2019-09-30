<?php
namespace shellpress\v1_3_77\src\Shared\Components;

use shellpress\v1_3_77\src\Shared\Front\Models\HtmlElement;
use shellpress\v1_3_77\src\Shared\RestModels\UniversalFrontResponse;
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

	/** @var string */
	private $_productId = '';

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

		$oldLicense = $this->getLicense();
		$newLicense = $request->get_param( 'license' ) ?: '';

		//  ----------------------------------------
		//  Just remove cached data when switched
		//  ----------------------------------------

		if( $oldLicense !== $newLicense ){

			$this->_setCachedData( array() );

		}

		//  ----------------------------------------
		//  Process deactivation
		//  ----------------------------------------

		if( $oldLicense && $oldLicense !== $newLicense ){

			$this->_setLicense( $oldLicense );

			$deactivationResult = $this->remoteDeactivateLicense();

			if( is_wp_error( $deactivationResult ) ){
				$request->set_param( 'noticeError', $deactivationResult->get_error_message() );
			}

		}

		//  ----------------------------------------
		//  Process activation
		//  ----------------------------------------

		if( $newLicense ){

			$this->_setLicense( $newLicense );
			
			$activationResult = $this->remoteActivateLicense();

			if( is_wp_error( $activationResult ) ){
				$request->set_param( 'noticeError', $activationResult->get_error_message() );
			}

		}

		//  ----------------------------------------
		//  Always save
		//  ----------------------------------------

		$this->_setLicense( $newLicense );

		//  ----------------------------------------
		//  Process display
		//  ----------------------------------------

		$universalFrontResponse->setReplacementHtml( $this->getInnerHtml( $request ) );

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

		$inputLicenseEl = HtmlElement::create( 'input', false );
		$inputLicenseEl->setAttributes( array(
			'type'          =>  'password',
			'class'         =>  'regular-text',
			'value'         =>  esc_attr( $this->getLicense() ),
			'name'          =>  'license'
		) );

		$html .= $inputLicenseEl->getDisplay();

		$buttonUpdateLicense = HtmlElement::create( 'button' );
		$buttonUpdateLicense->setAttributes( array(
			'type'          =>  'submit',
			'class'         =>  'button'
		) );
		$buttonUpdateLicense->setContent( 'Update License' );

		$html .= $buttonUpdateLicense->getDisplay();

		//  ----------------------------------------
		//  Notifications
		//  ----------------------------------------

		if( $noticeError = $request->get_param( 'noticeError' ) ){
			$html .= sprintf( '<div class="notice notice-error" style="margin: 0.5em 0 0;"><p>%1$s</p></div>', $noticeError );
		}

		if( $noticeSuccess = $request->get_param( 'noticeSuccess' ) ){
			$html .= sprintf( '<div class="notice notice-success" style="margin: 0.5em 0 0;"><p>%1$s</p></div>', $noticeSuccess );
		}

		if( $noticeInfo = $request->get_param( 'noticeInfo' ) ){
			$html .= sprintf( '<div class="notice notice-success" style="margin: 0.5em 0 0;"><p>%1$s</p></div>', $noticeInfo );
		}

		if( $cachedData = $this->_getCachedData() ){
			$html .= '<div class="notice" style="margin: 0.5em 0 0;">';
			$html .= sprintf( '<p><small>Licensed to: <strong>%1s</strong></small></p>', $this::s()->get( $cachedData, 'customer_name' ) );
			$html .= sprintf( '<p><small>Expires on: <strong>%1s</strong></small></p>', $this::s()->get( $cachedData, 'expires' ) );
			$html .= '</div>';
		}

		if( $this->getLicense() && ! $this->_getCachedData() || $this->getLicense() && ! $this->isLicenseActive() ){
			$html .= sprintf( '<div class="notice notice-warning" style="margin: 0.5em 0 0;"><p>%1$s</p></div>', 'License is inactive' );
		}

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
	 * @return string
	 */
	private function _getProductId() {

		return $this->_productId;

	}

	/**
	 * Sets product ID from your EDD store.
	 *
	 * @param string $productId
	 *
	 * @return void
	 */
	public function setProductId( $productId ) {

		$this->_productId = $productId;

	}

	/**
	 * Sets license to operate with.
	 *
	 * @return bool
	 */
	private function _setLicense( $license ) {

		return update_option( $this->getOptionKeyLicense(), $license );

	}

	/**
	 * @return string
	 */
	private function getLicense() {

		return get_option( $this->getOptionKeyLicense(), '' );

	}

	/**
	 * Sets license data.
	 *
	 * @param array
	 *
	 * @return bool
	 */
	private function _setCachedData( $data ) {

		return update_option( $this->getOptionKeyData(), (array) $data );

	}

	/**
	 * Returns cached license data.
	 *
	 * @return array
	 */
	private function _getCachedData() {

		return (array) get_option( $this->getOptionKeyData(), array() );

	}

	/**
	 * Returns name of option.
	 *
	 * @return string
	 */
	public function getOptionKeyLicense() {

		return sanitize_key( __CLASS__ ) . '_license';

	}

	/**
	 * Returns name of option.
	 *
	 * @return string
	 */
	public function getOptionKeyData() {

		return sanitize_key( __CLASS__ ) . '_data';

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine and license is active, cached data is set to requested body.
	 *
	 * @return WP_Error|bool
	 */
	public function remoteActivateLicense(){

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );
		if( ! $this->getLicense() ) return new WP_Error( 'error', 'License is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		//  Prepare URL with arguments.
		$fullUrl = add_query_arg( array(
			'edd_action'        =>  'activate_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $this->getLicense(),
			'url'               =>  get_home_url()
		), $this->_apiUrl );

		if( is_wp_error( $response = wp_remote_get( $fullUrl ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$licenseStatus = $this::s()->get( $responseBody, 'license' );

				if( $licenseStatus === 'valid' ){
					$this->_setCachedData( $responseBody );
					return true;
				}

				if( $licenseStatus === 'invalid' ){
					return new WP_Error( 'error', 'License is invalid.' );
				}

				return new WP_Error( 'error', 'License check failed.' );

			} else {

				return new WP_Error( 'error', 'Remote data has wrong format.' );

			}

		}

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine and license is active, cached data is set to requested body.
	 *
	 * @return WP_Error|bool
	 */
	public function remoteCheckLicense(){

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );
		if( ! $this->getLicense() ) return new WP_Error( 'error', 'License is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$apiParams = array(
			'edd_action'        =>  'check_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $this->getLicense(),
			'url'               =>  get_home_url()
		);

		if( is_wp_error( $response = wp_remote_post( $this->_getApiUrl(), array( 'body' => $apiParams, 'timeout' => 15, 'sslverify' => false ) ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$licenseStatus = $this::s()->get( $responseBody, 'license' );

				if( $licenseStatus === 'valid' ){
					$this->_setCachedData( $responseBody );
					return true;
				}

				if( $licenseStatus === 'invalid' ){
					return new WP_Error( 'error', 'License is invalid.' );
				}

				return new WP_Error( 'error', 'License check failed.' );

			} else {

				return new WP_Error( 'error', 'Remote data has wrong format.' );

			}

		}

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine, cached data is set to empty array.
	 *
	 * @return bool|WP_Error
	 */
	public function remoteDeactivateLicense() {

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );
		if( ! $this->getLicense() ) return new WP_Error( 'error', 'License is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$apiParams = array(
			'edd_action'        =>  'deactivate_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $this->getLicense(),
			'url'               =>  get_home_url()
		);

		if( is_wp_error( $response = wp_remote_post( $this->_getApiUrl(), array( 'body' => $apiParams, 'timeout' => 15, 'sslverify' => false ) ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$actionStatus = $this::s()->get( $responseBody, 'success' );

				if( $actionStatus ){

					$this->_setCachedData( array() );
					return true;

				} else {

					return new WP_Error( 'error', 'Could not deactivate license.' );

				}

			} else {

				return new WP_Error( 'error', 'Remote data has wrong format.' );

			}

		}

	}

	/**
	 * @return bool
	 */
	public function isLicenseActive() {

		if( $this->getLicense() ){

			if( $this::s()->get( $this->_getCachedData(), 'license' ) === 'valid' ){

				$timeNow    = time();
				$timeExpire = $this::s()->get( $this->_getCachedData(), 'expires' );

				if( $timeNow > $timeExpire ){

					return true;

				}

			}

		}

		return false;

	}

}