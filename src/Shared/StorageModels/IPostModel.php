<?php

namespace shellpress\v1_4_1\src\Shared\StorageModels;

/**
 * Date: 28.04.2018
 * Time: 12:04
 */

use WP_Post;

abstract class IPostModel {

	/** @var string */
	const POST_TYPE = '';

	/** @var WP_Post */
	private $_post;

	/** @var array */
	private $_metaForUpdate = array();

	/**
	 * PostInterface constructor.
	 *
	 * @param WP_Post $post
	 */
	public function __construct( $post ) {

		if( ! $this::POST_TYPE ) wp_die( 'Your PostModel have to change const POST_TYPE string.' );

		$this->_post = $post;

	}

	/**
	 * Factory method.
	 *
	 * @return static|null    Returns new instance of post model or nothing.
	 */
	public static function getById( $postId ) {

		$post = get_post( $postId );

		return $post ? new static( $post ) : null;

	}

	/**
	 * Returns post object bundled with this wrapper.
	 *
	 * @return WP_Post
	 */
	public function &getPost() {

		return $this->_post;

	}

	/**
	 * Returns post ID.
	 *
	 * @return int
	 */
	public function getId() {

		return (int) $this->_post->ID;

	}

	/**
	 * Returns post title.
	 *
	 * @return string
	 */
	public function getTitle() {

		return $this->getPost()->post_title;

	}

	/**
	 * Sets post title.
	 *
	 * @param string $title
	 *
	 * @return void
	 */
	public function setTitle( $title ) {

		$this->getPost()->post_title = $title;

	}


	/**
	 * Returns post content ( raw ).
	 *
	 * @return string
	 */
	public function getContent() {

		return $this->getPost()->post_content;

	}

	/**
	 * Sets post content.
	 *
	 * @param string $content
	 *
	 * @return void
	 */
	public function setContent( $content ) {

		$this->getPost()->post_content = $content;

	}

	/**
	 * Returns post status in raw form.
	 *
	 * @return string
	 */
	public function getStatus() {

		return $this->getPost()->post_status;

	}

	/**
	 * Sets raw status slug.
	 *
	 * @param string $status
	 *
	 * @return void
	 */
	public function setStatus( $status ) {

		$this->getPost()->post_status = $status;

	}

	/**
	 * Returns date of creation (gmt).
	 *
	 * @return string
	 */
	public function getDateOfCreation() {

		return $this->getPost()->post_date_gmt;

	}

	/**
	 * Returns date of modification (gmt).
	 *
	 * @return string
	 */
	public function getDateOfModification() {

		return $this->getPost()->post_modified_gmt;

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

			$value = get_post_meta( $this->getId(), $metaKey, $single );
			return empty( $value ) ? $defaultValue : $value;

		}

	}

	/**
	 * Sets metadata.
	 *
	 * @param string $metaKey
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function setMeta( $metaKey, $value ) {

		$this->_metaForUpdate[ $metaKey ] = $value;

	}

	/**
	 * Updates post data and all set meta.
	 *
	 * @deprecated - use: pushChanges()
	 *
	 * @return void
	 */
	public function flush() {

		$this->pushChanges();

	}

	/**
	 * Updates post data and all set meta.
	 *
	 * @return void
	 */
	public function pushChanges() {

		wp_update_post( $this->getPost(), true );

		foreach( $this->_metaForUpdate as $metaKey => $value ){
			update_post_meta( $this->getId(), $metaKey, $value );
		}

	}

}