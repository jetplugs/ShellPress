<?php
namespace shellpress\v1_3_2\src\Components\External;

/**
 * @author jakubkuranda@gmail.com
 * Date: 14.03.2018
 * Time: 12:39
 */

use shellpress\v1_3_2\src\Shared\Components\IComponent;
use WP_Error;

class MessagesHandler extends IComponent {

	/**
	 * Called on handler construction.
	 *
	 * @return void
	 */
	protected function onSetUp() {
		// TODO: Implement onSetUp() method.
	}

    /**
     * Simple email sending method.
     * It logs native phpmailer errors in ShellPress error log.
     *
     * @param string        $title
     * @param string        $content
     * @param string        $targetAddress
     * @param null|string   $fromAddress
     * @param null|string   $fromName
     * @param null|string   $replyToAddress
     * @param array         $additionalHeaders
     *
     * @return bool
     */
    public function sendEmail( $title, $content, $targetAddress, $fromAddress = null, $fromName = null, $replyToAddress = null, $additionalHeaders = array() ) {

        $defaultHeaders = array(
            'Content-Type: text/html; charset=UTF-8'
        );

        $headers        = wp_parse_args( $additionalHeaders, $defaultHeaders );
        $attachments    = array();

        if( ! $fromName && $fromAddress ){
            $headers[] = sprintf( 'From: %1$s', $fromAddress );                     //  If only $fromAddress is set
        } elseif( $fromName && $fromAddress ) {
            $headers[] = sprintf( 'From: %1$s <%2$s>', $fromName, $fromAddress );   //  If $fromAddress and $fromName are set
        }

        if( $replyToAddress ){
            $headers[] = sprintf( 'Reply-To: %1$s', $replyToAddress );
        }

        //  Try to send email

        add_action( 'wp_mail_failed',               array( $this, '_a_catchEmailError' ) );     //  Add logger.

        $result = wp_mail( $targetAddress, $title, $content, $headers, $attachments );

        remove_action( 'wp_mail_failed',            array( $this, '_a_catchEmailError' ) );     //  Remove logger.

        return $result;

    }

    //  ================================================================================
    //  ACTIONS
    //  ================================================================================

    /**
     * Logs all errors from native phpmailer object.
     *
     * @param WP_Error $wp_error
     */
    public function _a_catchEmailError( $wp_error ) {

        $errors = $wp_error->get_error_messages();

        foreach( $errors as $error ){   /** @var string $error */
           $this->s()->log->error( $error );
        }

    }

}