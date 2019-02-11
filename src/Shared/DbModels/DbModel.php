<?php
namespace shellpress\v1_3_5\src\Shared\DbModels;

/**
 * Date: 04.02.2019
 * Time: 21:51
 */

class DbModel {

	/** @var int */
	private $_id;

	/** @var string */
	private $_modelName;

	/**
	 * DbModel constructor.
	 *
	 * @param string $modelName
	 * @param int $id
	 */
	public function __construct( $modelName, $args = array() ) {

		$this->_modelName = $modelName;

		//  ----------------------------------------
		//  Prepare args
		//  ----------------------------------------

		$defArgs = array(
			'id'            =>  0
		);

		$args = wp_parse_args( $args, $defArgs );

		//  ----------------------------------------
		//  Set obj properties.
		//  ----------------------------------------

		$this->_id = $args['id'];

	}

	/**
	 * @return string
	 */
	public function getModelName() {

		return $this->_modelName;

	}

	/**
	 * @return void
	 */
	public function setId( $id ) {

		$this->_id = (int) $id;

	}

	/**
	 * @return int
	 */
	public function getId() {

		return $this->_id;

	}

}