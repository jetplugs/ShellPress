<?php
namespace shellpress\v1_0_9\src\Shared\StorageModels;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-06
 * Time: 22:05
 */

use WP_Error;
use WP_Post;

class PostInterface {

    /** @var string */
    const POST_TYPE = '';

    /** @var WP_Post */
    protected $post;

    /**
     * PostInterface constructor.
     *
     * @param WP_Post $post
     */
    public function __construct( $post ) {

        $this->post = $post;

    }

    /**
     * Returns post object bundled with this wrapper.
     *
     * @return WP_Post
     */
    public function getPost() {

        return $this->post;

    }

    /**
     * Runs wp_update_post() method on base WP_Post object.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int|WP_Error
     */
    protected function updateObjectArg( $key, $value ) {

        $args = array(
            'ID'    =>  $this->getId()
        );

        $args[ $key ] = $value;

        return wp_update_post( $args, true );

    }

    /**
     * Runs wp_update_post() method on base WP_Post object.
     * You don't have to pass 'ID' key.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return int|WP_Error
     */
    protected function updateObjectArgs( $args ) {

        $defaultArgs = array(
            'ID'    =>  $this->getId()
        );

        $args = wp_parse_args( $args, $defaultArgs );

        return wp_update_post( $args, true );

    }

    /**
     * Returns post ID.
     *
     * @return int
     */
    public function getId() {

        return (int) $this->post->ID;

    }

    /**
     * Returns post title.
     *
     * @return string
     */
    public function getTitle() {

        return $this->post->post_title;

    }

    /**
     * Sets post title.
     *
     * @param string $title
     *
     * @return int|WP_Error
     */
    public function setTitle( $title ) {

        return $this->updateObjectArg( 'post_title', $title );

    }

    /**
     * Returns post status in raw form.
     *
     * @return string
     */
    public function getStatus() {

        return $this->post->post_status;

    }

    /**
     * Sets raw status slug.
     *
     * @param string $status
     *
     * @return int|WP_Error
     */
    public function setStatus( $status ) {

        return $this->updateObjectArg( 'post_status', $status );

    }

    /**
     * Returns date of creation.
     *
     * @return string
     */
    public function getDateOfCreation() {

        return $this->post->post_date;

    }

    /**
     * Returns metadata.
     *
     * @param string $metaKey
     * @param null $defaultValue
     * @param bool $single
     *
     * @return mixed
     */
    public function getMeta( $metaKey, $defaultValue = null, $single = true ) {

        $value = get_post_meta( $this->getId(), $metaKey, $single );

        return empty( $value ) ? $defaultValue : $value;

    }

    /**
     * Sets metadata.
     *
     * @param string $metaKey
     * @param string $value
     * @param string $prevValue
     *
     * @return bool|int
     */
    public function setMeta( $metaKey, $value, $prevValue = '' ) {

        return update_post_meta( $this->getId(), $metaKey, $value, $prevValue );

    }

}