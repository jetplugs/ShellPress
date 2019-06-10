<?php
namespace shellpress\v1_3_73\src\Shared\RestModels;

/**
 * @author jakubkuranda@gmail.com
 * Date: 20.02.2019
 * Time: 12:45
 */

use WP_REST_Response;

/**
 * This class is designed to work as WP_REST_Reposne object factory.
 * It should be created as new instance inside rest route callback and return getPackedResponse().
 */
class UniversalFrontResponse {

	/** @var WP_REST_Response */
	private $_response;

	/** @var int */
	private $_status = 200;

	/** @var array - whole data inside response obj */
	private $_data;

	public function __construct() {

		$this->_response = new WP_REST_Response();

		//  ----------------------------------------
		//  Setup default data.
		//  ----------------------------------------

		$this->_data = array(
			'replacementHtml'           =>  false,
			'redirectUrl'               =>  false,
			'triggerFrontActions'       =>  array()
		);

	}

	/**
	 * Static constructor.
	 *
	 * @return UniversalFrontResponse
	 */
	public static function create() {

		return new UniversalFrontResponse();

	}

	/**
	 * Packs data into reponse and returns it.
	 *
	 * @return WP_REST_Response
	 */
	public function getPackedResponse() {

		$response = new WP_REST_Response();
		$response->set_status( $this->_status );
		$response->set_data( $this->_data );

		return $response;

	}

	/**
	 * Sets 3-digit http status.
	 *
	 * @param int $status
	 *
	 * @return void
	 */
	public function setStatus( $status ) {

		$this->_response->set_status( $status );

	}

	/**
	 * Some components allow to replace their html.
	 * If given string is empty, it will not refresh component!
	 *
	 * @param string $html
	 *
	 * @return void
	 */
	public function setReplacementHtml( $html ) {

		$this->_data['replacementHtml'] = '' . $html;

	}

	/**
	 * Some components allow to redirect page, after reponse is finished.
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public function setRedirectUrl( $url ) {

		$this->_data['redirectUrl'] = $url;

	}

	/**
	 * Sets names of actions, that will be triggered at the front end.
	 *
	 * @param string|string[] $actionName
	 *
	 * @return void
	 */
	public function setTriggerFrontActions( $actionName ) {

		$this->_data['triggerFrontActions'] = (array) $actionName;

	}

}