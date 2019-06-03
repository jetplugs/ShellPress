<?php
namespace shellpress\v1_3_72\demo\src\Components;

use shellpress\v1_3_72\src\Shared\Components\IUniversalFrontComponent;
use shellpress\v1_3_72\src\Shared\Front\Models\HtmlElement;
use shellpress\v1_3_72\src\Shared\RestModels\UniversalFrontResponse;
use WP_REST_Request;

/**
 * @author jakubkuranda@gmail.com
 * Date: 31.05.2019
 * Time: 16:11
 */
class UniversalFrontExample extends IUniversalFrontComponent {

	/**
	 * Returns name of shortcode.
	 *
	 * @return string
	 */
	public function getShortCodeName() {

		return 'demoExample';

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
	 * @param WP_REST_Request $request
	 *
	 * @return UniversalFrontResponse
	 */
	protected function processUniversalFrontResponse( $universalFrontResponse, $request ) {

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

		return HtmlElement::create( 'button' )->setContent( time() )->setAttributes( array(
			'type'  =>  'submit'
		) );

	}

}