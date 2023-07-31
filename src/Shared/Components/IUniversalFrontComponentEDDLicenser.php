<?php
namespace shellpress\v1_4_1\src\Shared\Components;

use shellpress\v1_4_1\src\Shared\RestModels\UniversalFrontResponse;
use WP_REST_Request;

/**
 * @author jakubkuranda@gmail.com
 * Date: 29.08.2019
 * Time: 15:00
 *
 * @deprecated
 */
abstract class IUniversalFrontComponentEDDLicenser extends IUniversalFrontComponent {

	/** @var string */
	private $_apiUrl = '';

	/** @var string */
	private $_productId = '';

	/** @var string */
	private $_licenseForUpdates = '';

	/** @var string */
	private $_thisHost = '';

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

}