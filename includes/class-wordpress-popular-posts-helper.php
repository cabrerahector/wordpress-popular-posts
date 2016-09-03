<?php

class WPP_Helper {
	
	/**
	 *
	 * @return	array
	 */
	public static function get_default_options( $options_set = '' ) {
		
		$defaults = array(
			'widget_options' => array(
				'title' => '',
				'limit' => 10,
				'range' => 'daily',
				'freshness' => false,
				'order_by' => 'views',
				'post_type' => 'post,page',
				'pid' => '',
				'author' => '',
				'cat' => '',
				'taxonomy' => 'category',
				'term_id' => '',
				'shorten_title' => array(
					'active' => false,
					'length' => 25,
					'words'	=> false
				),
				'post-excerpt' => array(
					'active' => false,
					'length' => 55,
					'keep_format' => false,
					'words' => false
				),
				'thumbnail' => array(
					'active' => false,
					'build' => 'manual',
					'width' => 75,
					'height' => 75,
					'crop' => true
				),
				'rating' => false,
				'stats_tag' => array(
					'comment_count' => false,
					'views' => true,
					'author' => false,
					'date' => array(
						'active' => false,
						'format' => 'F j, Y'
					),
					'category' => false,
					'taxonomy' => false
				),
				'markup' => array(
					'custom_html' => false,
					'title-start' => '<h2>',
					'title-end' => '</h2>',
					'wpp-start' => '<ul class="wpp-list">',
					'wpp-end' => '</ul>',
					'post-html' => '<li>{thumb} {title} <span class="wpp-meta post-stats">{stats}</span></li>'
					
				)
			),
			'admin_options' => array(
				'stats' => array(
					'order_by' => 'views',
					'limit' => 10,
					'post_type' => 'post,page',
					'freshness' => false
				),
				'tools' => array(
					'ajax' => false,
					'css' => true,
					'link' => array(
						'target' => '_self'
					),
					'thumbnail' => array(
						'source' => 'featured',
						'field' => '',
						'resize' => false,
						'default' => '',
						'responsive' => false
					),
					'log' => array(
						'level' => 1,
						'limit' => 0,
						'expires_after' => 180
					),
					'cache' => array(
						'active' => false,
						'interval' => array(
							'time' => 'hour',
							'value' => 1
						)
					),
					'sampling' => array(
						'active' => false,
						'rate' => 100
					)
				)
			)
		);
		
		if ( !empty($options_set) && isset($defaults[$options_set]) )
			return $defaults[$options_set];
		
		return $defaults;
		
	}
	
	/**
	 * Checks for valid number.
	 *
	 * @since	2.1.6
	 * @param	int	number
	 * @return	bool
	 */
	public static function is_number( $number ){
		return !empty($number) && is_numeric($number) && (intval($number) == floatval($number));
	}
	
	/**
	 * Returns server date.
	 *
	 * @since    2.1.6
	 * @access   private
	 * @return   string
	 */
	public static function curdate() {
		return gmdate( 'Y-m-d', ( time() + ( get_site_option( 'gmt_offset' ) * 3600 ) ) );
	}

	/**
	 * Returns mysql datetime.
	 *
	 * @since    2.1.6
	 * @access   private
	 * @return   string
	 */
	public static function now() {
		return current_time( 'mysql' );
	}
	
	/**
	 * Returns time.
	 *
	 * @since	2.3.0
	 * @return	string
	 */
	public static function microtime_float() {

		list( $msec, $sec ) = explode( ' ', microtime() );

		return (float) $msec + (float) $sec;

	}
	
	/**
	 * Merges two associative arrays recursively.
	 *
	 * @since	2.3.4
	 * @link	http://www.php.net/manual/en/function.array-merge-recursive.php#92195
	 * @param	array	array1
	 * @param	array	array2
	 * @return	array
	 */
	public static function merge_array_r( array $array1, array $array2 ) {

		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {

			if ( is_array( $value ) && isset ( $merged[$key] ) && is_array( $merged[$key] ) ) {
				$merged[$key] = self::merge_array_r( $merged[$key], $value );
			} else {
				$merged[$key] = $value;
			}
		}

		return $merged;

	}

	/**
	 * Debug function.
	 *
	 * @since	3.0.0
	 * @param	mixed $v variable to display with var_dump()
	 * @param	mixed $v,... unlimited optional number of variables to display with var_dump()
	 */
	public static function debug( $v ) {

		if ( !defined('WPP_DEBUG') || !WPP_DEBUG )
			return;

		foreach ( func_get_args() as $arg ) {

			print "<pre>";
			var_dump($arg);
			print "</pre>";

		}

	}
	
	public static function truncate( $text = '', $length = 25, $truncate_by_words = false ) {
		
		if ( '' !== $text ) {
			
			// Truncate by words
			if ( $truncate_by_words ) {
	
				$words = explode( " ", $text, $length + 1 );
				
				if ( count($words) > $length ) {
					array_pop( $words );
					$text = rtrim( implode(" ", $words), ",." ) . " ...";
				}
	
			}
			// Truncate by characters
			elseif ( strlen($text) > $length ) {
				$text = rtrim( mb_substr($text, 0, $length , get_bloginfo('charset')), " ,." ) . "...";
			}
			
		}
		
		return $text;
		
	}
	
	/**
	 * Gets post/page ID if current page is singular
	 *
	 * @since	3.1.2
	 */
	public static function is_single() {
		
		$trackable = array();
		$registered_post_types = get_post_types( array('public' => true), 'names' );
		
		foreach ( $registered_post_types as $post_type ) {
			$trackable[] = $post_type;
		}
		
		$trackable = apply_filters( 'wpp_trackable_post_types', $trackable );
		
		if ( is_singular( $trackable ) && !is_front_page() && !is_preview() && !is_trackback() && !is_feed() && !is_robots() ) {
			global $post;				
			return ( is_object($post) ) ? $post->ID : false;
		}
		
		return false;
		
	}
	
} // End WPP_Helper class
