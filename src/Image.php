<?php
/**
 * This class builds/retrieves the thumbnail image of each popular posts.
 *
 *
 * @package    WordPressPopularPosts
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts;

class Image {

    /**
     * Default thumbnail.
     *
     * @since   2.2.0
     * @var     string
     */
    private $default_thumbnail = '';

    /**
     * Plugin uploads directory.
     *
     * @since   3.0.4
     * @var     array
     */
    private $uploads_dir = [];

    /**
     * Admin settings.
     *
     * @since   5.0.0
     * @var     array
     */
    private $admin_options = [];

    /**
     * Available image sizes.
     *
     * @since   5.0.0
     * @var     array
     */
    private $available_sizes = [];

    /**
     * Construct.
     *
     * @since   4.0.0
     * @param   array   $admin_options
     */
    public function __construct(array $admin_options)
    {
        $this->admin_options = $admin_options;

        // Set default thumbnail
        $this->default_thumbnail = plugins_url() . "/wordpress-popular-posts/assets/images/no_thumb.jpg";

        if ( $this->is_image_url($this->admin_options['tools']['thumbnail']['default']) )
            $this->default_thumbnail = $this->admin_options['tools']['thumbnail']['default'];

        // Set uploads folder
        $wp_upload_dir = wp_get_upload_dir();
        $this->uploads_dir['basedir'] = $wp_upload_dir['basedir'] . "/" . 'wordpress-popular-posts';
        $this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'] . "/" . 'wordpress-popular-posts';

        if ( ! is_dir($this->uploads_dir['basedir']) ) {
            // Couldn't create the folder, store thumbnails in Uploads
            if ( ! wp_mkdir_p($this->uploads_dir['basedir']) ) {
                $this->uploads_dir['basedir'] = $wp_upload_dir['basedir'];
                $this->uploads_dir['baseurl'] = $wp_upload_dir['baseurl'];
            }
        }
    }

    /**
     * Get WPP's uploads folder.
     *
     * @since   4.0.0
     * @access  public
     * @return  array|bool
     */
    public function get_plugin_uploads_dir()
    {
        if ( is_array($this->uploads_dir) && ! empty($this->uploads_dir) )
            return $this->uploads_dir;
        return false;
    }

    /**
     * Returns an image.
     *
     * @since   5.0.0
     * @param   \stdClass   $post_object    Post object
     * @param   array       $size           Image size (width & height)
     * @param   string      $source         Image source
     * @param   bool        $crop           Whether to crop the image or not
     * @param   string      $build          Whether to build the image or get an existing one
     * @return  string
     */
    public function get($post_object, $size, $source, $crop = true, $build = 'manual')
    {
        // Bail, $post_object is not an actual object
        if ( false === $post_object instanceof \stdClass || ! isset($post_object->id) ) {
            return '';
        }

        $alt = '';
        $classes = ['wpp-thumbnail', 'wpp_' . $source];
        $filename = $post_object->id . '-' . $source . '-' . $size[0] . 'x' . $size[1];
        $cached = $this->exists($filename);

        if ( $this->admin_options['tools']['thumbnail']['lazyload'] ) {
            array_push($classes, 'wpp-lazyload');
        }

        // We have a thumbnail already, return it
        if ( $cached ) {
            $classes[] = 'wpp_cached_thumb';

            /**
             * Filters CSS classes assigned to the thumbnail
             *
             * @since   5.0.0
             * @param   array   CSS classes
             * @param   int     The post ID
             * @return  array   The new CSS classes
             */
            $classes = apply_filters(
                'wpp_thumbnail_class_attribute',
                $classes,
                $post_object->id
            );

            /**
             * Filters CSS classes assigned to the thumbnail
             *
             * @since   5.0.0
             * @param   string  Original ALT attribute
             * @param   int     The post ID
             * @return  string  The new ALT attribute
             */
            $alt = apply_filters(
                'wpp_thumbnail_alt_attribute',
                $this->get_alt_attribute($post_object->id, $source),
                $post_object->id
            );

            return $this->render(
                $cached,
                $size,
                is_array($classes) ? implode(' ', $classes) : 'wpp-thumbnail wpp_' . $source,
                is_string($alt) ? $alt : ''
            );
        }

        $thumb_url = null;

        // Return image as-is, no need to create a new thumbnail
        if (
            ( 'custom_field' == $source && ! $this->admin_options['tools']['thumbnail']['resize'] )
            || ( 'featured' == $source && 'predefined' == $build )
        ){
            /**
             * Filters CSS classes assigned to the thumbnail
             *
             * @since   5.0.0
             * @param   array   CSS classes
             * @param   int     The post ID
             * @return  array   The new CSS classes
             */
            $classes = apply_filters(
                'wpp_thumbnail_class_attribute',
                $classes,
                $post_object->id
            );

            // Get custom field image URL
            if ( 'custom_field' == $source && ! $this->admin_options['tools']['thumbnail']['resize'] ) {
                $thumb_url = get_post_meta(
                    $post_object->id,
                    $this->admin_options['tools']['thumbnail']['field'],
                    true
                );

                if ( ! $thumb_url || ! $this->is_image_url($thumb_url) ) {
                    $thumb_url = null;
                } else {
                    /**
                     * Filters CSS classes assigned to the thumbnail
                     *
                     * @since   5.0.0
                     * @param   string  Original ALT attribute
                     * @param   int     The post ID
                     * @return  string  The new ALT attribute
                     */
                    $alt = apply_filters(
                        'wpp_thumbnail_alt_attribute',
                        '',
                        $post_object->id
                    );
                }
            }
            // Get Post Thumbnail
            else {
                if (
                    current_theme_supports('post-thumbnails')
                    && has_post_thumbnail($post_object->id)
                ) {
                    // Find corresponding image size
                    $stock_size = null;
                    $images_sizes = $this->get_sizes();

                    foreach ( $images_sizes as $name => $attr ) :
                        if (
                            $attr['width'] == $size[0]
                            && $attr['height'] == $size[1]
                            && $attr['crop'] == $crop
                        ) {
                            $stock_size = $name;
                            break;
                        }
                    endforeach;

                    // Couldn't find a matching size so let's go with width/height combo instead 
                    // (this should never happen but better safe than sorry!)
                    if ( null == $stock_size ) {
                        $stock_size = $size;
                    }

                    $featured_image = get_the_post_thumbnail(
                        $post_object->id,
                        $stock_size
                    );

                    if ( strpos($featured_image, 'class="') && is_array($classes) && ! empty($classes) )
                        $featured_image = str_replace('class="', 'class="'. esc_attr(implode(' ', $classes)) . ' ', $featured_image);

                    if ( $this->admin_options['tools']['thumbnail']['lazyload'] ) {
                        $featured_image = str_replace('src="', 'data-img-src="', $featured_image);
                        $featured_image = str_replace('srcset="', 'data-img-srcset="', $featured_image);
                    }

                    return $featured_image;
                }
            }
        }
        // Build a new thumbnail and return it
        else {
            $file_path = null;

            if ( 'custom_field' == $source && $this->admin_options['tools']['thumbnail']['resize'] ) {
                $thumb_url = get_post_meta(
                    $post_object->id,
                    $this->admin_options['tools']['thumbnail']['field'],
                    true
                );

                if ( $thumb_url && $this->is_image_url($thumb_url) ) {
                    $file_path = $this->url_to_path($thumb_url, $post_object->id);
                }
            } else {
                $file_meta = $this->get_file_meta($post_object->id, $source);

                if ( is_array($file_meta) && isset($file_meta['path']) ) {
                    $alt = isset($file_meta['alt']) ? $file_meta['alt'] : '';
                    $file_path = $file_meta['path'];
                }
            }

            if ( $file_path ) {
                $extension = pathinfo($file_path, PATHINFO_EXTENSION);
                $thumb_url = $this->resize(
                    $file_path,
                    $filename . '.' . $extension,
                    $size,
                    $crop
                );
            }
        }

        if ( ! $thumb_url ) {
            $classes[] = 'wpp_def_no_src';
            $thumb_url = $this->get_default_url($post_object->id);
        }

        return $this->render(
            $thumb_url,
            $size,
            is_array($classes) ? implode(' ', $classes) : 'wpp-thumbnail wpp_' . $source,
            is_string($alt) ? $alt : ''
        );
    }

    /**
     * Checks whether a given thumbnail exists.
     *
     * @since   5.0.0
     * @access  private
     * @param   string      $filename
     * @return  string|bool Full URL to image
     */
    private function exists($filename)
    {
        // Do we have thumbnail already?
        $file = $this->resolve(trailingslashit($this->get_plugin_uploads_dir()['basedir']) . $filename);

        if ( $file && is_file($file) ) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            return trailingslashit($this->get_plugin_uploads_dir()['baseurl']) . $filename . '.' . $extension;
        }

        return false;
    }

    /**
     * Resolves filename.
     *
     * @since   5.0.0
     * @access  private
     * @author  Ioan Chiriac
     * @link    https://stackoverflow.com/a/29468093/9131961
     * @param   string      $name
     * @return  string|bool Resolved path, or false if not found
     */
    private function resolve($name)
    {
        $info = pathinfo($name);

        // File already contains an extension, return it
        if ( isset($info['extension']) && ! empty($info['extension']) ) {
            return $name;
        }

        $filename = $info['filename'];
        $len = strlen($filename);

        // open the folder
        $dh = opendir($info['dirname']);

        if ( ! $dh ) {
            return false;
        }

        // scan each file in the folder
        while ( ($file = readdir($dh)) !== false ) {
            if ( strncmp($file, $filename, $len) === 0 ) {
                if ( strlen($name) > $len ) {
                    // if name contains a directory part
                    $name = substr($name, 0, strlen($name) - $len) . $file;
                } else {
                    // if the name is at the path root
                    $name = $file;
                }

                closedir($dh);
                return $name;
            }
        }

        // file not found
        closedir($dh);
        return false;
    }

    /**
     * Retrieves local path to image.
     *
     * @since   5.0.0
     * @access  private
     * @param   string          $url
     * @param   integer         $post_ID
     * @return  string|boolean  Path to image, or false if not found
     */
    private function url_to_path($url, $post_ID = null)
    {
        if ( $this->is_image_url($url) ) {
            $attachment_id = $this->get_attachment_id($url);

            // Image is hosted locally
            if ( $attachment_id ) {
                return get_attached_file($attachment_id);
            }

            // Image hosted elsewhere?
            if ( $post_ID && Helper::is_number($post_ID) )
                return $this->fetch_external_image($post_ID, $url);
        }

        return false;
    }

    /**
     * Gets image meta.
     *
     * @since   5.0.0
     * @access  private
     * @param   int         $id       Post ID
     * @param   string      $source   Image source
     * @return  array|bool
     */
    private function get_file_meta($id, $source)
    {
        // get thumbnail path from the Featured Image
        if ( "featured" == $source ) {
            if ( $thumbnail_id = get_post_thumbnail_id($id) ) {
                // image path
                return [
                    'path' => get_attached_file($thumbnail_id),
                    'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)
                ];
            }
        }
        // get thumbnail path from first image attachment
        elseif ( "first_attachment" == $source ) {
            $args = [
                'numberposts' => 1,
                'order' => 'ASC',
                'post_parent' => $id,
                'post_type' => 'attachment',
                'post_mime_type' => 'image'
            ];
            $post_attachments = get_children($args);

            if ( ! empty($post_attachments) ) {
                $first_img = array_shift($post_attachments);

                return [
                    'path' => get_attached_file($first_img->ID),
                    'alt' => get_post_meta($first_img->ID, '_wp_attachment_image_alt', true)
                ];
            }
        }
        // get thumbnail path from post content
        elseif ( "first_image" == $source ) {
            /** @var wpdb $wpdb */
            global $wpdb;

            if ( $content = $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID = {$id};") ) {
                // at least one image has been found
                if ( preg_match('/<img[^>]+>/i', $content, $img) ) {
                    // get img src attribute from the first image found
                    preg_match('/(src)="([^"]*)"/i', $img[0], $src_attr);

                    if ( isset($src_attr[2]) && ! empty($src_attr[2]) ) {
                        // get img alt attribute from the first image found
                        $alt = '';
                        preg_match('/(alt)="([^"]*)"/i', $img[0], $alt_attr);

                        if ( isset($alt_attr[2]) && !empty($alt_attr[2]) ) {
                            $alt = $alt_attr[2];
                        }

                        // image from Media Library
                        if ( $attachment_id = $this->get_attachment_id($src_attr[2]) ) {
                            return [
                                'path' => get_attached_file($attachment_id),
                                'alt' => $alt
                            ];
                        } // external image?
                        else {
                            return [
                                'path' => $this->fetch_external_image($id, $src_attr[2]),
                                'alt' => $alt
                            ];
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Gets image ALT attribute.
     *
     * @since   5.0.0
     * @access  private
     * @param   int         $id       Post ID
     * @param   string      $source   Image source
     * @return  string
     */
    private function get_alt_attribute($id, $source)
    {
        $alt = '';

        // get thumbnail path from the Featured Image
        if ( "featured" == $source ) {
            if ( $thumbnail_id = get_post_thumbnail_id($id) ) {
                // image path
                $alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
            }
        }
        // get thumbnail path from first image attachment
        elseif ( "first_attachment" == $source ) {
            $args = [
                'numberposts' => 1,
                'order' => 'ASC',
                'post_parent' => $id,
                'post_type' => 'attachment',
                'post_mime_type' => 'image'
            ];
            $post_attachments = get_children($args);

            if ( ! empty($post_attachments) ) {
                $first_img = array_shift($post_attachments);
                $alt = get_post_meta($first_img->ID, '_wp_attachment_image_alt', true);
            }
        }
        // get thumbnail path from post content
        elseif ( "first_image" == $source ) {
            /** @var wpdb $wpdb */
            global $wpdb;

            if ( $content = $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID = {$id};") ) {
                // at least one image has been found
                if ( preg_match('/<img[^>]+>/i', $content, $img) ) {
                    // get img alt attribute from the first image found
                    preg_match('/(alt)="([^"]*)"/i', $img[0], $alt_attr);

                    if ( isset($alt_attr[2]) && !empty($alt_attr[2]) ) {
                        $alt = $alt_attr[2];
                    }
                }
            }
        }

        return $alt;
    }

    /**
     * Get the Attachment ID for a given image URL.
     *
     * @since    3.0.0
     * @access   private
     * @author   Frankie Jarrett
     * @link     http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
     * @param    string    $url
     * @return   int|null
     */
    private function get_attachment_id($url)
    {
        $url = Helper::add_scheme(
            $url,
            is_ssl() ? 'https://' : 'http://'
        );

        // Split the $url into two parts with the wp-content directory as the separator.
        $parse_url  = explode(parse_url(WP_CONTENT_URL, PHP_URL_PATH), $url);

        // Get the host of the current site and the host of the $url, ignoring www.
        $this_host = str_ireplace('www.', '', parse_url(home_url(), PHP_URL_HOST));
        $file_host = str_ireplace('www.', '', parse_url($url, PHP_URL_HOST));

        // Return nothing if there aren't any $url parts or if the current host and $url host do not match.
        if (
            ! isset($parse_url[1]) 
            || empty($parse_url[1]) 
            || ($this_host != $file_host) 
        ) {
            return false;
        }

        // Now we're going to quickly search the DB for any attachment GUID with a partial path match.
        // Example: /uploads/2013/05/test-image.jpg
        global $wpdb;

        if ( ! $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1])) ) {
            // Maybe it's a resized image, so try to get the full one
            $parse_url[1] = preg_replace('/-[0-9]{1,4}x[0-9]{1,4}\.(jpg|jpeg|png|gif|bmp)$/i', '.$1', $parse_url[1]);
            $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parse_url[1]));
        }

        // Returns null if no attachment is found.
        return isset($attachment[0]) ? $attachment[0] : NULL;
    }

    /**
     * Fetchs external images.
     *
     * @since   2.3.3
     * @access  private
     * @param   int         $id    Post ID.
     * @param   string      $url   Image url.
     * @return  string|bool Image path, or false on failure.
     */
    private function fetch_external_image($id, $url)
    {
        $full_image_path = trailingslashit($this->get_plugin_uploads_dir()['basedir']) . "{$id}_" . sanitize_file_name(rawurldecode(wp_basename($url)));

        // if the file exists already, return URL and path
        if ( file_exists($full_image_path) )
            return $full_image_path;

        $url = Helper::add_scheme(
            $url,
            is_ssl() ? 'https://' : 'http://'
        );

        $accepted_status_codes = [200, 301, 302];
        $response = wp_remote_head($url, ['timeout' => 5, 'sslverify' => false]);

        if (
            ! is_wp_error($response) 
            && in_array(wp_remote_retrieve_response_code($response), $accepted_status_codes)
        ) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            $url = str_replace('https://', 'http://', $url);
            $tmp = download_url($url);

            // File was downloaded successfully
            if ( ! is_wp_error($tmp) ) {
                // Determine image type
                if ( function_exists('exif_imagetype') ) {
                    $image_type = exif_imagetype($tmp);
                } else {
                    $image_type = getimagesize($tmp);
                    $image_type = ( isset($image_type[2]) ) ? $image_type[2] : NULL;
                }

                // Valid image, save it
                if ( in_array($image_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG]) ) {
                    // move file to Uploads
                    if ( @rename($tmp, $full_image_path) ) {
                        // borrowed from WP - set correct file permissions
                        $stat = stat(dirname($full_image_path));
                        $perms = $stat['mode'] & 0000644;
                        @chmod($full_image_path, $perms);

                        return $full_image_path;
                    }
                }

                // Invalid file, remove it
                @unlink($tmp);
            }
        }

        return false;
    }

    /**
     * Resizes image.
     *
     * @since   3.0.0
     * @access  private
     * @param   string      $path           Image path
     * @param   string      $filename       Image filename
     * @param   array       $size           Image size
     * @param   bool        $crop           Whether to crop the image or not
     * @return  string|bool Image URL on success, false on error
     */
    private function resize($path, $filename, $size, $crop = true)
    {
        $image = wp_get_image_editor($path);

        // valid image, create thumbnail
        if ( ! is_wp_error($image) ) {
            /**
             * Hook to change the image compression quality of WPP's thumbnails.
             * @since   4.2.1
             */
            $quality = apply_filters('wpp_thumbnail_compression_quality', null);

            if ( ! ctype_digit($quality) )
                $quality = null; // Fallback to core's default

            $image->set_quality($quality);

            $image->resize($size[0], $size[1], $crop);
            $new_img = $image->save(trailingslashit($this->get_plugin_uploads_dir()['basedir']) . $filename);

            if ( ! is_wp_error($new_img) )
                return trailingslashit($this->get_plugin_uploads_dir()['baseurl']) . $filename;
        }

        return false;
    }

    /**
     * Render image tag.
     *
     * @since   3.0.0
     * @access  public
     * @param   string      $src            Image URL
     * @param   array       $dimension      Image's width and height
     * @param   string      $class          CSS class
     * @param   object      $alt            Alternative text
     * @param   string      $error          Error, if the image could not be created
     * @return  string
     */
    public function render($src, $size, $class, $alt = '', $error = null)
    {
        $img_tag = '';

        if ( $error ) {
            $img_tag = '<!-- ' . $error . ' --> ';
        }

        if ( $this->admin_options['tools']['thumbnail']['lazyload'] ) {
            $src = 'data-img-src="' . esc_url(is_ssl() ? str_ireplace("http://", "https://", $src) : $src) . '"';
        } else {
            $src = 'src="' . esc_url(is_ssl() ? str_ireplace("http://", "https://", $src) : $src) . '"';
        }

        $img_tag .= '<img ' . $src . ' width="' . $size[0] . '" height="' . $size[1] . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($class) . '" />';

        return apply_filters('wpp_render_image', $img_tag);
    }

    /**
     * Gets list of available thumbnails sizes
     *
     * @since   3.2.0
     * @link    http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes
     * @param   string  $size
     * @return  array|bool
     */
    public function get_sizes($size = '')
    {
        if ( ! is_array($this->available_sizes) || empty($this->available_sizes) ) {
            global $_wp_additional_image_sizes;

            $this->available_sizes = [];
            $get_intermediate_image_sizes = get_intermediate_image_sizes();

            // Create the full array with sizes and crop info
            foreach( $get_intermediate_image_sizes as $_size ) {
                if ( in_array($_size, ['thumbnail', 'medium', 'large']) ) {
                    $this->available_sizes[$_size]['width'] = get_option($_size . '_size_w');
                    $this->available_sizes[$_size]['height'] = get_option($_size . '_size_h');
                    $this->available_sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');
                } elseif ( isset($_wp_additional_image_sizes[$_size]) ) {
                    $this->available_sizes[$_size] = [
                        'width' => $_wp_additional_image_sizes[$_size]['width'],
                        'height' => $_wp_additional_image_sizes[$_size]['height'],
                        'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
                    ];
                }
            }
        }

        // Get only 1 size if found
        if ( $size ) {
            if ( isset($this->available_sizes[$size]) ) {
                return $this->available_sizes[$size];
            }
            return false;
        }

        return $this->available_sizes;
    }

    /**
     * Returns the URL of the default thumbnail image.
     *
     * @since   5.0.0
     * @param   int|null
     * @return  string
     */
    public function get_default_url($post_ID = null)
    {
        if ( has_filter('wpp_default_thumbnail_url') ) {
            $default_thumbnail_url = apply_filters('wpp_default_thumbnail_url', $this->default_thumbnail, $post_ID);

            if ( $default_thumbnail_url != $this->default_thumbnail && $this->is_image_url($default_thumbnail_url) )
                return $default_thumbnail_url;
        }

        return $this->default_thumbnail;
    }

    /**
     * Checks whether an URL points to an actual image.
     *
     * @since   5.0.0
     * @access  private
     * @param   string
     * @return  array|bool
     */
    private function is_image_url($url)
    {
        if ( ! filter_var($url, FILTER_VALIDATE_URL) )
            return false;

        // sanitize URL, just in case
        $image_url = esc_url($url);
        // remove querystring
        preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $image_url, $matches);

        return ( is_array($matches) && ! empty($matches) ) ? $matches : false;
    }
}
