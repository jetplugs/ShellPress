<?php
namespace shellpress\v1_3_5\src\Shared\DbModels;

/**
 * Date: 04.02.2019
 * Time: 21:51
 */

class DbModel {

	/** @var int */
	private $_id;

	/** @var string MySQL DateTime format */
	private $_date;

	/** @var string MySQL DateTime format */
	private $_modified;

	/** @var int */
	private $_author;

	/** @var string */
	private $_value;

	/**
	 * DbModel constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $args = array() ) {

		$defArgs = array(
			'id'            =>  0,
			'date'          =>  current_time( 'mysql', true ),
			'modified'      =>  current_time( 'mysql', true ),
			'author'        =>  get_current_user_id(),
			'value'         =>  ''
		);

		$args = wp_parse_args( $args, $defArgs );

		//  ----------------------------------------
		//  Set obj properties.
		//  ----------------------------------------

		$this->_id          = $args['id'];
		$this->_date        = $args['date'];
		$this->_modified    = $args['modified'];
		$this->_author      = $args['author'];
		$this->_value       = $args['value'];

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

	/**
	 * Date of creation in mysql format without GMT offset.
	 *
	 * @return string
	 */
	public function getDate() {

		return $this->_date;

	}

	/**
	 * Sets date of modification. It should not be modified by GMT offset.
	 * If date is not given, it will be filled automatically with current dateitme.
	 *
	 *
	 * @param string|null $mysqlDate
	 *
	 * @return void
	 */
	public function setModified( $mysqlDate = null ) {

		if( empty( $mysqlDate ) ){
			$mysqlDate = current_time( 'mysql', true );
		}

		$this->_modified = $mysqlDate;

	}

	/**
	 * Date of modification in mysql format without GMT offset.
	 *
	 * @return string
	 */
	public function getModified() {

		return $this->_modified;

	}

	/**
	 * @param int $author Author ID
	 *
	 * @return void
	 */
	public function setAuthor( $author ) {

		$this->_author = (int) $author;

	}

	/**
	 * Returns author ID.
	 *
	 * @return int
	 */
	public function getAuthor() {

		return $this->_author;

	}

	/**
	 * @return mixed
	 */
	public function getValue() {

		return $this->_value;

	}

	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function setValue( $value ) {

		$this->_value = $value;

	}

}