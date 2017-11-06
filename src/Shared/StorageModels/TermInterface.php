<?php
namespace shellpress\v1_0_9\src\Shared\StorageModels;
use WP_Error;
use WP_Term;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-06
 * Time: 22:35
 */
class TermInterface {

    /** @var string */
    const TAXONOMY = '';

    /** @var WP_Term */
    protected $term;

    /**
     * TermInterface constructor.
     *
     * @param WP_Term $term
     */
    public function __construct( $term ) {

        $this->term = $term;

    }

    /**
     * Returns WP_Term object bundled with this wrapper.
     *
     * @return WP_Term
     */
    public function getTerm() {

        return $this->term;

    }

    /**
     * Runs wp_update_term() method on base WP_Term object.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return array|WP_Error
     */
    protected function updateObjectArg( $key, $value ) {

        $args           = array();
        $args[ $key ]   = $value;

        return wp_update_term( $this->getId(), $this::TAXONOMY, $args );

    }

    /**
     * Runs wp_update_term() method on base WP_Term object.
     *
     * @param array $args Key-value arguments
     *
     * @return array|WP_Error
     */
    protected function updateObjectArgs( $args ) {

        return wp_update_term( $this->getId(), $this::TAXONOMY, $args );

    }

    /**
     * Returns term ID.
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

        return $this->term->name;

    }

    /**
     * Sets name of term.
     *
     * @param string $name
     *
     * @return array|WP_Error
     */
    public function setName( $name ) {

        return $this->updateObjectArg( 'name', $name );

    }

    /**
     * Returns term description.
     *
     * @return string
     */
    public function getDescription() {

        return $this->term->description;

    }

    /**
     * Sets description of term.
     *
     * @param string $description
     *
     * @return array|WP_Error
     */
    public function setDescription( $description ) {

        return $this->updateObjectArg( 'description', $description );

    }

    /**
     * Returns term slug.
     *
     * @return string
     */
    public function getSlug() {

        return $this->term->slug;

    }

    /**
     * Sets term slug.
     *
     * @param string $slug
     *
     * @return array|WP_Error
     */
    public function setSlug( $slug ) {

        return $this->updateObjectArg( 'slug', $slug );

    }

}