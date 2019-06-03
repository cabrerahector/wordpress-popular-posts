<?php
/**
 * Class that loads WPP's themes.
 *
 * @package    WordPressPopularPosts
 * @author     Hector Cabrera <me@cabrerahector.com>
 */

namespace WordPressPopularPosts;

class Themer {

    /**
     * Path to themes files.
     *
     * @var     string     $config
     * @access  private
     */
    private $path;

    /**
     * Registered themes.
     *
     * @var     array      $config
     * @access  private
     */
    private $themes;

    /**
     * Construct function.
     *
     * @since   5.0.0
     */
    public function __construct()
    {
        $this->themes = [];
        $this->path = plugin_dir_path(dirname(__FILE__)) . 'assets/themes';

        $this->read();
    }

    /**
     * Loads information about existing themes.
     *
     * @since   5.0.0
     */
    public function read()
    {
        $directories = new \DirectoryIterator($this->path);

        foreach( $directories as $fileinfo ) {
            if (
                $fileinfo->isDir()
                && ! $fileinfo->isDot()
                && $fileinfo->isReadable()
                && file_exists($fileinfo->getPathName() . '/config.json')
                && file_exists($fileinfo->getPathName() . '/style.css')
            ) {
                $str = file_get_contents($fileinfo->getPathName() . '/config.json');
                $json = json_decode($str, true);

                if ( $this->is_valid_config($json) ) {
                    $this->themes[$fileinfo->getFilename()] = [
                        'json' => $json,
                        'path' => $fileinfo->getPathName()
                    ];
                }
            }
        }
    }

    /**
     * Returns an array of available themes.
     *
     * @since   5.0.0
     * @return  array
     */
    public function get_themes()
    {
        return $this->themes;
    }

    /**
     * Returns data of a specific theme (if found).
     *
     * @since   5.0.0
     * @param   string  $theme
     * @return  array|bool
     */
    public function get_theme($theme)
    {
        return isset($this->themes[$theme]) ? $this->themes[$theme] : false;
    }

    /**
     * Checks whether a $json array is a valid theme config.
     *
     * @since   5.0.0
     * @param   array
     * @return  bool
     */
    public function is_valid_config($json = [])
    {
        return is_array($json) && ! empty($json) && isset($json['name']) && isset($json['config']) && is_array($json['config']);
    }
}
