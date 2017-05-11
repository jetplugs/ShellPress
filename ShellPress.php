<?php
namespace shellpress;



/**
 * ShellPress is the class framework for
 * rapid plugin development.
 * @author DualJack
 * @version 1.0.0
 * Just extend this class while creating
 * your main plugin object and init() it.
 * @example class Something extends ShellPress {}
 * @example $app = new Something();
 * 			$app->init( $args );
 */

class ShellPress {

	protected $temp_options;
	protected $views;


	

	function init( $arguments ) {

		spl_autoload_register( array( $this, 'autoloader' ) );

		$this->temp_options = new Options( $this );

	}

	function autoloader( $class_name ) {

		if( strpos( $class_name, $this->namespace ) !== false ) {	//	if namespace prefix occure in class name

			$class_path = str_replace( $this->namespace, '', $class_name );	// remove namespace prefix
			$class_path = ltrim( $class_path, '\\' );	// remove leading namespace prefix

			$class_path = $this->path . str_replace('\\', '/', $class_path) . '.php';

			if( file_exists( $class_path ) ) {

				require_once( $class_path );

			}

		}

	}

}