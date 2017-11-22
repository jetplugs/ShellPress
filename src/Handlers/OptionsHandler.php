<?php
namespace shellpress\v1_1_0\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 18:33
 */

use shellpress\v1_1_0\ShellPress;

class OptionsHandler extends Handler {

    /** @var ShellPress */
    protected $sp;

    /** @var string */
    protected $optionsKey = '';

    /** @var array */
    protected $optionsData = array();

    /** @var array */
    protected $defaultData = array();

    /**
     * Loads saved options from WP database.
     *
     * @return void
     */
    public function load() {

        $options = get_option( $this->optionsKey, array() );

        $this->optionsData = $options;

    }

    /**
     * Gets option from array of WP options.
     *
     * @param string $arrayPath     Next array keys separated by `/`
     * @param mixed $defaultValue   Default value, if this key is not set
     *
     * @return mixed
     */
    public function get( $arrayPath = '', $defaultValue = null ) {

        if( empty( $arrayPath ) ){

            return $this->optionsData;

        } else {

            $pathPieces = explode('/', $arrayPath);

            $option = $this->optionsData;

            foreach( $pathPieces as $pathNode ){

                if( isset( $option[$pathNode] ) ){

                    $option = &$option[$pathNode];

                } else {

                    return $defaultValue;

                }

            }

            return $option;

        }

    }

    /**
     * Sets value in options array by given path.
     *
     * @param string $arrayPath - Next array keys separated by `/`
     * @param mixed $value
     */
    public function set( $arrayPath = '', $value ) {

        $options    = $this->get();
        $keys       = explode( '/', $arrayPath );

        $options    = $this->sp->utility()->injectValueToMultidimensionalArray( $options, $keys, $value );

        $this->update( $options );

    }

    /**
     * Updates WP database option with given value. Caution! It updates whole array!
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function update( $data ) {

        $this->optionsData = $data;

        return update_option( $this->getOptionsKey(), $data );

    }

    /**
     * Sets options key.
     *
     * @param string $key
     */
    public function setOptionsKey( $key ) {

        $this->optionsKey = $key;

    }

    /**
     * Gets options key.
     *
     * @return string
     */
    public function getOptionsKey() {

        return $this->optionsKey;

    }

    /**
     * Gets default options.
     *
     * @return array
     */
    public function getDefaultOptions() {

        return $this->defaultData;

    }

    /**
     * Sets default options.
     *
     * @param $options
     */
    public function setDefaultOptions( $options ) {

        $this->defaultData = $options;

    }

    /**
     * Checks current saved options and fills them with defaults.
     * If some key already exists, it will not be updated.
     *
     * @return void
     */
    public function fillDifferencies() {

        $currentOptions =   $this->get( '', array() );
        $defaultOptions =   $this->getDefaultOptions();

        $updateOptions =    $this->sp->utility()->arrayMergeRecursiveDistinctSafe( $currentOptions, $defaultOptions );

        $this->update( $updateOptions );

    }

    /**
     * Replaces all options with defaults.
     *
     * @return void
     */
    public function restoreDefaults() {

        $this->update( $this->getDefaultOptions() );

    }

}