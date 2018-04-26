<?php
namespace shellpress\v1_2_1\src\Handlers\Models;
use WP_Customize_Manager;

/**
 * Date: 26.04.2018
 * Time: 21:25
 */

class CustomizerSection {

	/** @var string */
	protected $slug;

	/** @var string */
	protected $title;

	/** @var int */
	protected $priority;

	/** @var string */
	protected $description;

	/** @var callable */
	protected $activeCallback;

	/**
	 * CustomizerSection constructor.
	 *
	 * @param string $slug
	 */
	public function __construct( $slug, $title = '' ) {

		$this->slug             = $slug;
		$this->title            = $title ? $title : 'New ShellPress section';
		$this->priority         = 160;
		$this->description      = '';
		$this->activeCallback   = '';

	}

	/**
	 * @param int $priority
	 *
	 * @return self
	 */
	public function setPriority( $priority ) {

		$this->priority = (int) $priority;

		return $this;

	}

	/**
	 * @param string $title
	 *
	 * @return $this
	 */
	public function setTitle( $title ) {

		$this->title = $title;

		return $this;

	}

	/**
	 * @param string $desc
	 *
	 * @return $this
	 */
	public function setDescription( $desc ) {

		$this->description = $desc;

		return $this;

	}

	/**
	 * Given callable must return boolean.
	 * If given callable return false, section will not be visible.
	 *
	 * @param callable $callable
	 *
	 * @return $this
	 */
	public function setActiveCallback( $callable ) {

		$this->activeCallback = $callable;

		return $this;

	}

	/**
	 * @param WP_Customize_Manager &$wpCustomize
	 */
	public function register( &$wpCustomize ) {

		$wpCustomize->add_section( $this->slug, array(
			'title'             =>  $this->title,
			'description'       =>  $this->description,
			'priority'          =>  $this->priority,
			'active_callback'   =>  $this->activeCallback
		) );

	}

}