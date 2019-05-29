<?php
namespace shellpress\v1_3_72\src\Shared\Components;

use shellpress\v1_3_72\src\Shared\Front\Models\HtmlElement;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @author jakubkuranda@gmail.com
 * Date: 28.05.2019
 * Time: 17:55
 */

abstract class IComponentLicenseManager extends IComponent {

	protected function onSetUp() {

		//  ----------------------------------------
		//  Actions
		//  ----------------------------------------

		add_action( 'rest_api_init',    array( $this, '_a_registerRestRoutes' ) );

	}

	/**
	 * Returns whole dynamic component for controlling licenses.
	 *
	 * @return string
	 */
	public function getDisplay() {

		//  Create HTML element.
		$formElement = HtmlElement::create( 'form' );
		$formElement->addAttributes( array(
			'method'        =>  'POST',
			'action'        =>  '',
			'class'         =>  'SPLicenseManager-form'
		) );

		//  Return string representation.
		return $formElement->getDisplay();

	}

	//  ================================================================================
	//  ACTIONS
	//  ================================================================================

	/**
	 * Called on rest_init.
	 *
	 * @return void
	 */
	public function _a_registerRestRoutes() {

		register_rest_route( $this::s()->getPrefix( '/licensemanager/v1' ), 'form/universal', array(
			'methods'       =>  'POST',
			'callback'      =>  array()
		) );

	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function _a_formUniversalCallback( $request ) {

		$response = new WP_REST_Response();

		return $response;

	}

}