<?php
namespace shellpress\v1_3_82\src\Shared\Components;

use shellpress\v1_3_82\lib\EasyDigitalDownloads\EDDPluginUpdater;
use shellpress\v1_3_82\src\Shared\Front\Models\HtmlElement;
use shellpress\v1_3_82\src\Shared\RestModels\UniversalFrontResponse;
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

	/** @var string */
	private $_licenseForUpdates = '';

	/**
	 * Returns name of shortcode.
	 *
	 * @return string
	 */
	public function getShortCodeName() {

		return sanitize_key( get_class( $this ) );

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

		$newLicense = $request->get_param( 'license' ) ?: '';

		//  ----------------------------------------
		//  Just remove cached data when switched
		//  ----------------------------------------


		if( $request->get_param( 'deactivateSubmit' ) ){

			//  ----------------------------------------
			//  Process deactivation
			//  ----------------------------------------

			$deactivationResult = $this->remoteDeactivateLicense( $newLicense );

			if( is_wp_error( $deactivationResult ) ){
				$request->set_param( 'noticeError', $deactivationResult->get_error_message() );
			} else {
				$this->_setLicense( '' );
				$this->_setCachedData( array() );
				$request->set_param( 'noticeSuccess', "Successfully deactivated license." );
			}

		} else if( ! $this->isLicenseActive() && $newLicense ){

			//  ----------------------------------------
			//  Process activation
			//  ----------------------------------------
			
			$activationResult = $this->remoteActivateLicense( $newLicense );

			if( is_wp_error( $activationResult ) ){
				$request->set_param( 'noticeError', $activationResult->get_error_message() );
			} else {

				if( $activationResult ){
					$this->_setLicense( $newLicense );
					$this->_setCachedData( $activationResult );
					$request->set_param( 'noticeSuccess', "Successfully activated license." );
				} else {
					$this->_setCachedData( array() );
					$request->set_param( 'noticeError', "License is invalid." );
				}

			}

		}

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

		if( $this->isLicenseActive() ){

			$inputLicenseEl = HtmlElement::create( 'input', false );
			$inputLicenseEl->setAttributes( array(
				'type'          =>  'password',
				'class'         =>  'regular-text',
				'value'         =>  esc_attr( $this->getLicense() ),
				'name'          =>  'license',
				'readonly'      =>  'readonly'
			) );

			$html .= $inputLicenseEl->getDisplay();

			$buttonUpdateLicense = HtmlElement::create( 'button' );
			$buttonUpdateLicense->setAttributes( array(
				'type'          =>  'submit',
				'class'         =>  'button',
				'name'          =>  'deactivateSubmit',
				'value'         =>  '1'
			) );
			$buttonUpdateLicense->setContent( 'Deactivate License' );

			$html .= $buttonUpdateLicense->getDisplay();

		} else {

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
				'class'         =>  'button',
				'name'          =>  'updateSubmit',
				'value'         =>  '1'
			) );
			$buttonUpdateLicense->setContent( 'Update License' );

			$html .= $buttonUpdateLicense->getDisplay();

		}

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

		if( ( $cachedData = $this->_getCachedData() ) && $this->getLicense() ){
			$html .= '<div class="postbox" style="margin: 0.5em 0 0;">';
			$html .= '<div class="inside" style="margin: 0;">';
			$html .= sprintf( '<p><small>Licensed to: <strong>%1s</strong></small></p>', $this::s()->get( $cachedData, 'customer_name' ) );
			$html .= sprintf( '<p><small>Expires on: <strong>%1s</strong></small></p>', $this::s()->get( $cachedData, 'expires' ) );
			$html .= '</div>';
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

		return sanitize_key( get_class( $this ) ) . '_license';

	}

	/**
	 * Returns name of option.
	 *
	 * @return string
	 */
	public function getOptionKeyData() {

		return sanitize_key( get_class( $this ) ) . '_data';

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine and license is active, it will return data.
	 * If license is inactive it will return false.
	 *
	 * @return WP_Error|false|array
	 */
	public function remoteActivateLicense( $license ){

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		//  Prepare URL with arguments.
		$fullUrl = add_query_arg( array(
			'edd_action'        =>  'activate_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $license,
			'url'               =>  get_home_url()
		), $this->_apiUrl );

		if( is_wp_error( $response = wp_remote_get( $fullUrl ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$licenseStatus = $this::s()->get( $responseBody, 'license' );

				if( $licenseStatus === 'valid' ){
					return $responseBody;
				}

				if( $licenseStatus === 'invalid' ){
					return false;
				}

				return new WP_Error( 'error', 'License check failed.' );

			} else {

				return new WP_Error( 'error', 'Remote data has wrong format.' );

			}

		}

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine and license is active, it will return data.
	 * If license is inactive, it will return false.
	 *
	 * @param string $license
	 *
	 * @return WP_Error|false|array
	 */
	public function remoteCheckLicense( $license ){

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$apiParams = array(
			'edd_action'        =>  'check_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $license,
			'url'               =>  get_home_url()
		);

		if( is_wp_error( $response = wp_remote_post( $this->_getApiUrl(), array( 'body' => $apiParams, 'timeout' => 15, 'sslverify' => false ) ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$licenseStatus = $this::s()->get( $responseBody, 'license' );

				if( $licenseStatus === 'valid' ){
					return $responseBody;
				}

				if( $licenseStatus === 'invalid' ){
					return false;
				}

				return new WP_Error( 'error', 'License check failed.' );

			} else {

				return new WP_Error( 'error', 'Request failed. Please try again in a few minutes.' );

			}

		}

	}

	/**
	 * If something goes wrong, it will return WP_Error object.
	 * If everything is fine, it will return true.
	 * If could not deactivate license, because it is disabled etc. it will return false.
	 *
	 * @param string $license
	 *
	 * @return true|false|WP_Error
	 */
	public function remoteDeactivateLicense( $license ) {

		//  ----------------------------------------
		//  Check requirements
		//  ----------------------------------------

		if( ! $this->_getApiUrl() ) return new WP_Error( 'error', 'API url is not defined.' );
		if( ! $this->_getProductId() ) return new WP_Error( 'error', 'Product ID is not defined.' );

		//  ----------------------------------------
		//  Make request
		//  ----------------------------------------

		$apiParams = array(
			'edd_action'        =>  'deactivate_license',
			'item_id'           =>  $this->_getProductId(),
			'license'           =>  $license,
			'url'               =>  get_home_url()
		);

		if( is_wp_error( $response = wp_remote_post( $this->_getApiUrl(), array( 'body' => $apiParams, 'timeout' => 15, 'sslverify' => false ) ) ) ){

			return $response;

		} else {

			$responseBody = json_decode( wp_remote_retrieve_body( $response ), true );

			if( $responseBody ){

				$actionStatus = $this::s()->get( $responseBody, 'success' );
				$actionLicense = $this::s()->get( $responseBody, 'license' );

				if( $actionStatus ){

					return true;

				} else {

					if( $actionLicense === 'failed' ){

						return false;

					} else {

						return new WP_Error( 'error', 'Could not deactivate license.' . $this::s()->utility->getFormattedVarExport( $responseBody ) );

					}

				}

			} else {

				return new WP_Error( 'error', 'Request failed. Please try again in a few minutes.' );

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

				if( $timeExpire === 'lifetime' ){
					return true;
				} else if( $timeNow < strtotime( $timeExpire ) ){
					return true;
				}

			}

		}

		return false;

	}

	/**
	 * Enables software updates from set shop uri.
	 * License is required for updating.
	 *
	 * Should be called before init action.
	 *
	 * @param string|null $licenseForUpdates
	 *
	 * @return void
	 */
	public function enableSoftwareUpdates( $licenseForUpdates = null ) {

		if( ! is_null( $licenseForUpdates ) ){
			$this->_licenseForUpdates = $licenseForUpdates;
		}

		add_action( 'init', array( $this, '_a_registerSoftwareUpdates' ) );

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	public function _a_registerSoftwareUpdates() {

		if( ( $this->_licenseForUpdates || $this->isLicenseActive() ) && $this->_getApiUrl() && $this->_getProductId() ){

			new EDDPluginUpdater( $this->_getApiUrl(), $this::s()->getMainPluginFile(), array(
				'version'   =>  $this::s()->getPluginVersion(),
				'license'   =>  $this->_licenseForUpdates ?: $this->getLicense(),
				'item_id'   =>  $this->_getProductId(),
				'author'    =>  'TheMasterCut.co',
				'beta'      =>  false,
			) );

		}

	}

}