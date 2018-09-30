<?php
/**
 * Helper class to display admin notices.
 *
 * @package    WordPressPopularPosts
 * @subpackage WordPressPopularPosts/includes
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

class WPP_Message {
    /**
     * Message(s) to display.
     *
     * @since    4.2.0
     * @access   private
     * @var      array     $message
     */
    private $message;

    /**
     * CSS class(es) to use on the notice.
     *
     * @since    4.2.0
     * @access   private
     * @var      string    $class
     */
    private $class;

    function __construct( $message, $class ) {
        $this->message = $message;
        $this->class = $class;

        add_action( 'admin_notices', array( $this, 'render' ) );
    }

    function render() {
        printf(
            '<div class="notice ' . $this->class . '"><p>%s</p></div>',
            join( '</p><p>', $this->message )
        );
    }
}
