<?php
namespace shellpress\v1_1_0\src\Handlers;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-21
 * Time: 22:14
 */

use shellpress\v1_1_0\ShellPress;

abstract class Handler {

    /** @var ShellPress */
    protected $sp;

    /**
     * Handler constructor.
     *
     * @param $shellPress
     */
    public function __construct( $shellPress ) {

        $this->sp = $shellPress;

    }

    /**
     * Returns ShellPress instance.
     *
     * @return ShellPress
     */
    protected function getShellPress() {

        return $this->sp;

    }

}