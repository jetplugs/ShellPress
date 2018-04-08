<?php
namespace shellpress\v1_1_8\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-08-21
 * Time: 18:33
 */

class OptionsHandler extends Handler {

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

            $keys = explode( '/', $arrayPath );

            return $this->shell()->utility->getValueByKeysPath( $this->optionsData, $keys, $defaultValue );

        }

    }

    /**
     * Sets value in options array by given path.
     *
     * @param string $arrayPath - Next array keys separated by `/`
     * @param mixed $value
     */
    public function set( $arrayPath = '', $value ) {

        if( empty( $arrayPath ) ){

            $this->optionsData = $value;

        } else {

            $keys               = explode( '/', $arrayPath );
            $this->optionsData  = $this->shell()->utility->setValueByKeysPath( $this->optionsData, $keys, $value );

        }

    }

    /**
     * Saves current options to database.
     *
     * @return bool
     */
    public function flush() {

        return update_option( $this->getOptionsKey(), $this->optionsData );

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

        $updateOptions =    $this->shell()->utility->arrayMergeRecursiveDistinctSafe( $currentOptions, $defaultOptions );

        $this->set( '', $updateOptions );

    }

    /**
     * Replaces all options with defaults.
     *
     * @return void
     */
    public function restoreDefaults() {

        $this->set( '', $this->getDefaultOptions() );

    }

}