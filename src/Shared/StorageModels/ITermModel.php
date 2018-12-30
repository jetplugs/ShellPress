<?php

namespace shellpress\v1_3_3\src\Shared\StorageModels;

/**
 * Date: 29.04.2018
 * Time: 21:31
 */

use WP_Term;

abstract class ITermModel {

	/** @var string */
	const TAXONOMY = '';

	/** @var WP_Term */
	private $term;

	/** @var array */
	private $_metaForUpdate = array();

	/**
	 * PostInterface constructor.
	 *
	 * @param WP_Term $term
	 */
	public function __construct( $term ) {

		if( ! $this::TAXONOMY ) wp_die( 'Your TermModel have to change const TAXONOMY string.' );

		$this->term = $term;

	}

	/**
	 * Factory method.
	 *
	 * @return static|null    Returns new instance of term model or nothing.
	 */
	public static function getById( $termId ) {

		$term = get_term( $termId, static::TAXONOMY );

		return $term && ! is_wp_error( $term ) ? new static( $term ) : null;

	}

	/**
	 * Returns post object bundled with this wrapper.
	 *
	 * @return WP_Term
	 */
	public function &getTerm() {

		return $this->term;

	}

	/**
	 * Returns post ID.
	 *
	 * @return int
	 */
	public function getId() {

		return (int) $this->term->term_id;

	}

	/**
	 * Returns term name.
	 *
	 * @return string
	 */
	public function getName() {

		return $this->getTerm()->name;

	}

	/**
	 * Sets term name.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function setName( $name ) {

		$this->getTerm()->name = $name;
	}

	/**
	 * Returns term slug.
	 *
	 * @return string
	 */
	public function getSlug() {

		return $this->getTerm()->slug;

	}

	/**
	 * Sets term slug.
	 *
	 * @param string $slug.
	 *
	 * @return void
	 */
	public function setSlug( $slug ) {

		$this->getTerm()->slug = $slug;

	}

	/**
	 * Returns term description.
	 *
	 * @return string
	 */
	public function getDescription() {

		return $this->getTerm()->description;

	}

	/**
	 * Sets term description.
	 *
	 * @param string $description.
	 *
	 * @return void
	 */
	public function setDescription( $description ) {

		$this->getTerm()->description = $description;

	}

	/**
	 * Returns term parent.
	 *
	 * @return int
	 */
	public function getParent() {

		return $this->getTerm()->parent;

	}

	/**
	 * Sets term parent.
	 *
	 * @param int $parent.
	 *
	 * @return void
	 */
	public function setParent( $parent ) {

		$this->getTerm()->parent = (int) $parent;

	}

	/**
	 * Returns metadata. Supports cached values.
	 *
	 * @param string        $metaKey
	 * @param null|mixed    $defaultValue
	 * @param bool          $single
	 *
	 * @return mixed
	 */
	public function getMeta( $metaKey, $defaultValue = null, $single = true ) {

		if( isset( $this->_metaForUpdate[ $metaKey ] ) ){

			$value = $this->_metaForUpdate[ $metaKey ];
			return empty( $value ) ? $defaultValue : $value;

		} else {

			$value = get_term_meta( $this->getId(), $metaKey, $single );
			return empty( $value ) ? $defaultValue : $value;

		}

	}

	/**
	 * Sets metadata.
	 *
	 * @param string $metaKey
	 * @param string $value
	 *
	 * @return void
	 */
	public function setMeta( $metaKey, $value ) {

		$this->_metaForUpdate[ $metaKey ] = $value;

	}

	/**
	 * Updates post data and all set meta.
	 *
	 * @return void
	 */
	public function flush() {

		$args = array(
			'name'          =>  $this->getName(),
			'slug'          =>  $this->getSlug(),
			'term_group'    =>  $this->getTerm()->term_group,
			'description'   =>  $this->getDescription(),
			'parent'        =>  $this->getParent()
		);

		wp_update_term( $this->getId(), $this->getTerm()->taxonomy, $args );

		foreach( $this->_metaForUpdate as $metaKey => $value ){
			update_term_meta( $this->getId(), $metaKey, $value );
		}

	}

}