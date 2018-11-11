<?php

class WPP_Image {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    4.0.0
     * @access   private
     * @var      object|WPP_Image
     */
    private static $instance;

    /**
     * The array of actions registered with WordPress.
     *
     * @since    4.0.0
     * @access   private
     * @var      bool    $can_create_thumbnails    Checks if WPP is able to build thumbnails.
     */
    private $can_create_thumbnails;

    /**
     * Default thumbnail.
     *
     * @since	2.2.0
     * @var		string
     */
    private $default_thumbnail = '';

    /**
     * Plugin uploads directory.
     *
     * @since	3.0.4
     * @var		array
     */
    private $uploads_dir = array();

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    4.0.0
     * @access   private
     */
    private function __construct() {

        // Check if WPP can create images
        $this->can_create_thumbnails = ( extension_loaded('ImageMagick') || extension_loaded('imagick') || (extension_loaded('GD') && function_exists('gd_info')) );

        if ( $this->can_create_thumbnails ) {

            // Set default thumbnail
            $this->default_thumbnail = $this->get_plugin_dir_url() . "public/images/no_thumb.jpg";

            // Set uploads folder
            $wp_upload_dir = wp_get_upload_dir();
            $this->uploads_dir['basedir'] = $wp_upload_dir['basedir'] . "/" . 'wordpress-popular-posts';
            $this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'] . "/" . 'wordpress-popular-posts';

            if ( !is_dir($this->uploads_dir['basedir']) ) {
                if ( !wp_mkdir_p($this->uploads_dir['basedir']) ) {
                    $this->uploads_dir['basedir'] = $wp_upload_dir['basedir'];
                    $this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'];
                }
            }

        }

    }

    /**
     * Get an instance of this class.
     *
     * @since    4.0.0
     * @return object|\WPP_Image
     */
    public static function get_instance() {

        if ( is_null(self::$instance) ) {
            self::$instance = new WPP_Image();
        }

        return self::$instance;

    }

    /**
     * Tells whether WPP can create thumbnails or not.
     *
     * @since	4.0.0
     * @access  public
     * @return	bool
     */
    public function can_create_thumbnails() {
        return $this->can_create_thumbnails;
    }

    public function get_plugin_dir() {
        return WP_PLUGIN_DIR . '/wordpress-popular-posts/';
    }

    public function get_plugin_dir_url() {
        return plugins_url() . '/wordpress-popular-posts/';
    }

    /**
     * Get WPP's uploads folder.
     *
     * @since	4.0.0
     * @access  public
     * @return	array|bool
     */
    public function get_plugin_uploads_dir() {

        if ( is_array($this->uploads_dir) && !empty($this->uploads_dir) )
            return $this->uploads_dir;

        return false;

    }

    /**
     * Retrieves / creates the post thumbnail.
     *
     * @since	2.3.3
     * @param	object   $post_object   Post object (must contain, at least, the properties id and title)
     * @param	string   $url           Image URL
     * @param	array    $size          Thumbnail's width and height
     * @param	array    $crop          Image cropping
     * @param	string   $source        Image source
     * @return	string
     */
    public function get_img( $post_object = null, $url = null, $size = array(80, 80), $crop = true, $source = "featured" ) {

        // WPP cannot create thumbnails
        if ( !$this->can_create_thumbnails )
            return '';

        if (
            ( false === $post_object instanceof stdClass || !isset($post_object->id) ) 
            && !filter_var( $url, FILTER_VALIDATE_URL ) 
        ) {
            return $this->render_image( $this->default_thumbnail, $size, 'wpp-thumbnail wpp_def_no_src wpp_' . $source, $post_object );
        }

        // Get image by post ID (parent)
        if (
            isset( $post_object->id ) 
            && !$url 
        ) {
            $file_path = $this->get_image_file_paths( $post_object->id, $source );

            // No images found, return default thumbnail
            if ( !$file_path ) {
                return $this->render_image( $this->default_thumbnail, $size, 'wpp-thumbnail wpp_def_noPath wpp_' . $source, $post_object );
            }
        }
        // Get image from URL
        else {
            // sanitize URL, just in case
            $image_url = esc_url( $url );
            // remove querystring
            preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches );
            $image_url = $matches[0];

            $attachment_id = $this->get_attachment_id( $image_url );

            // Image is hosted locally
            if ( $attachment_id ) {
                $file_path = get_attached_file( $attachment_id );
            }
            // Image is hosted outside WordPress
            else {
                $external_image = $this->fetch_external_image( $post_object->id, $image_url );

                if ( !$external_image ) {
                    return $this->render_image( $this->default_thumbnail, $size, 'wpp-thumbnail wpp_def_noPath wpp_no_external', $post_object );
                }

                $file_path = $external_image;
            }
        }

        $extension = pathinfo( $file_path, PATHINFO_EXTENSION );

        $image_meta = array(
            'filename' => $post_object->id . '-' . $source . '-' . $size[0] . 'x' . $size[1],
            'extension' => $extension,
            'width' => $size[0],
            'height' => $size[1],
            'alt' => esc_attr( wp_strip_all_tags( $post_object->title ) ),
            'crop' => $crop,
            'source' => $source,
            'parent_id' => $post_object->id
        );

        // there is a thumbnail already
        if ( is_file( trailingslashit( $this->uploads_dir['basedir'] ) . $image_meta['filename'] . '.' . $image_meta['extension'] ) ) {
            return $this->render_image(
                trailingslashit( $this->uploads_dir['baseurl'] ) . $image_meta['filename'] . '.' . $image_meta['extension'],
                $size,
                'wpp-thumbnail wpp_cached_thumb wpp_' . $source,
                $post_object
            );
        }

        return $this->image_resize( $file_path, $image_meta );

    } // end get_img

    /**
     * Resizes image.
     *
     * @since	3.0.0
     * @access  private
     * @param	object   $post_object   Post object
     * @param	string   $path          Image path
     * @param	array    $size          Image's width and height
     * @param	string   $source        Image source
     * @return	string
     */
    private function image_resize( $path, $image_meta ) {

        $image = wp_get_image_editor( $path );

        // valid image, create thumbnail
        if ( !is_wp_error($image) ) {

            $quality = apply_filters( 'wpp_thumbnail_compression_quality', null );
            if ( ! ctype_digit($quality) )
                $quality = null;
            $image->set_quality( $quality );

            $image->resize( $image_meta['width'], $image_meta['height'], $image_meta['crop'] );
            $new_img = $image->save( trailingslashit($this->uploads_dir['basedir']) . $image_meta['filename'] . '.' . $image_meta['extension'] );

            if ( is_wp_error($new_img) ) {
                return $this->render_image( $this->default_thumbnail, array( $image_meta['width'], $image_meta['height'] ), 'wpp-thumbnail wpp_imgeditor_error wpp_' . $image_meta['source'], null, $new_img->get_error_message() );
            }

            return $this->render_image( trailingslashit($this->uploads_dir['baseurl']) . $new_img['file'], array( $image_meta['width'], $image_meta['height'] ), 'wpp-thumbnail wpp_imgeditor_thumb wpp_' . $image_meta['source'], null );

        }

        // ELSE
        // image file path is invalid
        return $this->render_image( $this->default_thumbnail, array( $image_meta['width'], $image_meta['height'] ), 'wpp-thumbnail wpp_imgeditor_error wpp_' . $image_meta['source'], null, $image->get_error_message() );

    } // end image_resize

    /**
     * Get image absolute path / URL.
     *
     * @since	3.0.0
     * @access  private
     * @param	int       $id       Post ID
     * @param	string    $source   Image source
     * @return	array
     */
    private function get_image_file_paths( $id, $source ) {

        $file_path = '';

        // get thumbnail path from the Featured Image
        if ( "featured" == $source ) {

            if ( $thumbnail_id = get_post_thumbnail_id($id) ) {
                // image path
                return get_attached_file( $thumbnail_id );
            }

        }
        // get thumbnail path from first image attachment
        elseif ( "first_attachment" == $source ) {

            $args = array(
                'numberposts' => 1,
                'order' => 'ASC',
                'post_parent' => $id,
                'post_type' => 'attachment',
                'post_mime_type' => 'image'
            );
            $post_attachments = get_children( $args );

            if ( !empty($post_attachments) ) {
                $first_img = array_shift( $post_attachments );
                return get_attached_file( $first_img->ID );
            }

        }
        // get thumbnail path from post content
        elseif ( "first_image" == $source ) {

            /** @var wpdb $wpdb */
            global $wpdb;

            if ( $content = $wpdb->get_var( "SELECT post_content FROM {$wpdb->posts} WHERE ID = {$id};" ) ) {

                // at least one image has been found
                if ( preg_match( '/<img[^>]+>/i', $content, $img ) ) {

                    // get img src attribute from the first image found
                    preg_match( '/(src)="([^"]*)"/i', $img[0], $src_attr );

                    if ( isset($src_attr[2]) && !empty($src_attr[2]) ) {

                        // image from Media Library
                        if ( $attachment_id = $this->get_attachment_id( $src_attr[2] ) ) {

                            $file_path = get_attached_file( $attachment_id );

                            // There's a file path, so return it
                            if ( !empty($file_path) ) {
                                return $file_path;
                            }

                        } // external image?
                        else {
                            return $this->fetch_external_image( $id, $src_attr[2] );
                        }

                    }

                }

            }

        }

        return false;

    } // end get_image_file_paths

    /**
     * Render image tag.
     *
     * @since	3.0.0
     * @access  public
     * @param	string   src            Image URL
     * @param	array    dimension      Image's width and height
     * @param	string   class          CSS class
     * @param	object   $post_object   Post object (must contain, at least, the properties id and title)
     * @param	string	 error          Error, if the image could not be created
     * @return	string
     */
    public function render_image( $src, $size, $class, $post_object, $error = null ) {

        $img_tag = '';

        if ( $error ) {
            $img_tag = '<!-- ' . $error . ' --> ';
        }

        $img_tag .= '<img src="' . ( is_ssl() ? str_ireplace( "http://", "https://", $src ) : $src ) . '" width="' . $size[0] . '" height="' . $size[1] . '" alt="' . ( ($post_object instanceof stdClass && isset($post_object->title) ? esc_attr( wp_strip_all_tags($post_object->title) ) : '' ) ) . '" class="' . $class . '" />';

        return apply_filters( 'wpp_render_image', $img_tag );

    } // render_image

    /**
    * Get the Attachment ID for a given image URL.
    *
    * @since	3.0.0
    * @access   private
    * @author	Frankie Jarrett
    * @link		http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
    * @param	string    $url
    * @return	bool|int
    */
    private function get_attachment_id( $url ) {

        // Split the $url into two parts with the wp-content directory as the separator.
        $parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

        // Get the host of the current site and the host of the $url, ignoring www.
        $this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
        $file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

        // Return nothing if there aren't any $url parts or if the current host and $url host do not match.
        if (
            !isset( $parse_url[1] ) 
            || empty( $parse_url[1] ) 
            || ( $this_host != $file_host ) 
        ) {
            return false;
        }

        // Now we're going to quickly search the DB for any attachment GUID with a partial path match.
        // Example: /uploads/2013/05/test-image.jpg
        global $wpdb;

        if ( !$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) ) ) {
            // Maybe it's a resized image, so try to get the full one
            $parse_url[1] = preg_replace( '/-[0-9]{1,4}x[0-9]{1,4}\.(jpg|jpeg|png|gif|bmp)$/i', '.$1', $parse_url[1] );
            $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1] ) );
        }

        // Returns null if no attachment is found.
        return isset( $attachment[0] ) ? $attachment[0] : NULL;

    } // get_attachment_id

    /**
    * Fetchs external images.
    *
    * @since   2.3.3
    * @access  private
    * @param   int      $id    Post ID.
    * @param   string   $url   Image url.
    * @return  bool|int
    */
    private function fetch_external_image( $id, $url ){

        $full_image_path = trailingslashit( $this->uploads_dir['basedir'] ) . "{$id}_". sanitize_file_name( rawurldecode(wp_basename( $url )) );

        // if the file exists already, return URL and path
        if ( file_exists($full_image_path) )
            return $full_image_path;

        $accepted_status_codes = array( 200, 301, 302 );
        $response = wp_remote_head( $url, array( 'timeout' => 5, 'sslverify' => false ) );

        if (
            !is_wp_error($response) 
            && in_array( wp_remote_retrieve_response_code($response), $accepted_status_codes )
        ) {

            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            $url = str_replace( 'https://', 'http://', $url );
            $tmp = download_url( $url );

            if ( !is_wp_error( $tmp ) ) {

                if ( function_exists('exif_imagetype') ) {
                    $image_type = exif_imagetype( $tmp );
                } else {
                    $image_type = getimagesize( $tmp );
                    $image_type = ( isset($image_type[2]) ) ? $image_type[2] : NULL;
                }

                if ( in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) {

                    // move file to Uploads
                    if ( @rename($tmp, $full_image_path) ) {
                        // borrowed from WP - set correct file permissions
                        $stat = stat( dirname( $full_image_path ) );
                        $perms = $stat['mode'] & 0000644;
                        @chmod( $full_image_path, $perms );

                        return $full_image_path;
                    }

                }

                // remove temp file
                @unlink( $tmp );

            }

        }

        return false;

    } // end fetch_external_image

    /**
     * Gets list of available thumbnails sizes
     *
     * @since	3.2.0
     * @link	http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     * @param	string	$size
     * @return	array|bool
     */
    public function get_image_sizes( $size = '' ) {

        global $_wp_additional_image_sizes;

        $sizes = array();
        $get_intermediate_image_sizes = get_intermediate_image_sizes();

        // Create the full array with sizes and crop info
        foreach( $get_intermediate_image_sizes as $_size ) {

            if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
                $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
                $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                $sizes[ $_size ] = array(
                    'width' => $_wp_additional_image_sizes[ $_size ]['width'],
                    'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                    'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
                );

            }

        }

        // Get only 1 size if found
        if ( $size ) {

            if( isset( $sizes[ $size ] ) ) {
                return $sizes[ $size ];
            } else {
                return false;
            }

        }

        return $sizes;
    }

    /**
     * Sets default thumbnail image.
     *
     * @since	4.0.2
     * @param   string  $url
     */
    public function set_default( $url ) {
        $this->default_thumbnail = esc_url( $url );
    }

} // End WPP_Image class
