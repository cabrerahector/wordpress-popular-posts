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

        $this->hooks();
    }

    /**
     * Themer's hooks.
     *
     * @since   5.0.0
     */
    public function hooks()
    {
        add_action('after_setup_theme', [$this, 'read']);
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
            if ( $fileinfo->isDot() || $fileinfo->isFile() )
                continue;
            $this->load_theme($fileinfo->getPathName());
        }

        if ( has_filter('wpp_additional_themes') ) {
            $additional_themes = apply_filters('wpp_additional_themes', []);

            if ( is_array($additional_themes) && ! empty($additional_themes) ) {
                foreach( $additional_themes as $additional_theme ) {
                    $this->load_theme($additional_theme);
                }
            }
        }
    }

    /**
     * Reads and loads theme into the class.
     *
     * @since   5.0.0
     * @param   string  $path   Path to theme folder
     */
    private function load_theme($path)
    {
        $theme_folder = is_string($path) && is_dir($path) && is_readable($path) ? basename($path) : null;
        $theme_folder = $theme_folder ? preg_replace("/[^a-z0-9\_\-\.]/i", '', $theme_folder) : null;
        $theme_path = $theme_folder ? $path : null;

        if (
            $theme_path
            && '.' != $theme_folder
            && '..' != $theme_folder
            && false === strpos($theme_path, '..')
            && ! isset($this->themes[$theme_folder])
            && file_exists($theme_path . '/config.json')
            && file_exists($theme_path . '/style.css')
        ) {
            $str = file_get_contents($theme_path . '/config.json');
            $json = json_decode($str, true);

            if ( $this->is_valid_config($json) ) {
                $this->themes[$theme_folder] = [
                    'json' => $json,
                    'path' => $theme_path
                ];
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
