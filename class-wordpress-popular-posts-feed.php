<?php
/**
 * Wordpress Popular Posts Feed.
 *
 * @package   WordpressPopularPostsFeed
 * @author    Hector Cabrera <hcabrerab@gmail.com>
 * @license   GPL-2.0+
 * @link      http://cabrerahector.com
 * @copyright 2013 Hector Cabrera
 */

/**
 * Plugin feed class.
 *
 * @package WordpressPopularPostsFeed
 * @author  Hector Cabrera <hcabrerab@gmail.com>
 */

if ( class_exists('WordpressPopularPosts') ) {
	
	class WordpressPopularPostsFeed extends WordpressPopularPosts {
	
		/**
		 * Initialize the feed.
		 *
		 * @since     3.0.0
		 */
		public function __construct() {			
			add_action( 'init', array( $this, 'create_feed' ) );			
		}
		
		/**
		 * Creates the feed.
		 *
		 * @since     3.0.0
		 */
		public function create_feed() {
			add_feed( 'popularposts', array( $this, 'popular_posts_feed' ) );
		}
		
		/**
		 * Builds the feed.
		 *
		 * @since     3.0.0
		 */
		public function popular_posts_feed() {
			
			$this->__feed_header();
			
			$options = array(
				'title' => '',
				'limit' => 10,
				'range' => 'daily',
				'order_by' => 'views',
				'post_type' => 'post,page',
				'pid' => '',
				'author' => '',
				'cat' => '',
				'shorten_title' => array(
					'active' => false,
					'length' => 25,
					'words'	=> false
				),
				'post-excerpt' => array(
					'active' => false,
					'length' => 55,
					'keep_format' => false,
					'words' => true
				),
				'thumbnail' => array(
					'active' => false,
					'width' => 15,
					'height' => 15
				),
				'rating' => false,
				'stats_tag' => array(
					'comment_count' => false,
					'views' => false,
					'author' => false,
					'date' => array(
						'active' => false,
						'format' => 'F j, Y'
					),
					'category' => false
				),
				'markup' => array(
					'custom_html' => false,
					'wpp-start' => '&lt;ul class="wpp-list"&gt;',
					'wpp-end' => '&lt;/ul&gt;',
					'post-html' => '&lt;li&gt;{thumb} {title} {stats}&lt;/li&gt;',
					'post-start' => '&lt;li&gt;',
					'post-end' => '&lt;/li&gt;',
					'title-start' => '&lt;h2&gt;',
					'title-end' => '&lt;/h2&gt;'
				)
			);
			
			// TODO: build user options
			$result = parent::_query_posts( $options );
			
			foreach($result as $p) {
				echo '<item>';
				echo '<link>' . get_permalink( $p->id ) . '</link>';
				echo '<title><![CDATA[' . esc_html( $p->title ) . ']]></title>';
				echo '<description><![CDATA[ '. parent::_get_summary( $p->id, $options ) .' ]]></description>';
				echo '<pubDate>' . $this->__date_to_RFC822( $p->date ) . '</pubDate>';
				echo '</item>';
			}
						
			$this->__feed_footer();
			
		}
		
		/**
		 * Builds the feed header.
		 *
		 * @since     3.0.0
		 */
		private function __feed_header() {
			
			header( 'Content-Type: text/xml; charset=' . get_option('blog_charset') );			
			echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?>';
			
			global $blog_id;
			
			$sitename = get_bloginfo('name');
			$siteurl = ( function_exists( 'is_multisite' ) && is_multisite() ) 
			  ? network_home_url() 
			  : home_url();
			$description = get_bloginfo('description');
			$language = get_bloginfo( 'language' );
			$wp_ver = get_bloginfo( 'version' );
			
			?>            
            <rss version="2.0"  
              xmlns:content="http://purl.org/rss/1.0/modules/content/"
              xmlns:wfw="http://wellformedweb.org/CommentAPI/"
              xmlns:dc="http://purl.org/dc/elements/1.1/"
              xmlns:atom="http://www.w3.org/2005/Atom"
              xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
              xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
            >            
            	<channel>                    
                    <title><![CDATA[<?php echo $sitename; ?>]]></title>
                    <atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
                    <link><?php echo $siteurl; ?></link>
                    <description><![CDATA[<?php echo esc_html( $description ); ?>]]></description>
                    <lastBuildDate><?php echo $this->__date_to_RFC822( date('Y-m-d H:i:s', time()) ); ?></lastBuildDate>
                    <language><?php echo $language; ?></language>
                    <sy:updatePeriod>hourly</sy:updatePeriod>
                    <sy:updateFrequency>1</sy:updateFrequency>
                    <generator>http://wordpress.org/?v=<?php echo $wp_ver; ?></generator>                    
                    	<items>
            <?php
			
		}
		
		/**
		 * Builds the feed footer.
		 *
		 * @since     3.0.0
		 */
		private function __feed_footer() {
			
			?>
            		</items>
            	</channel>
			</rss>
            <?php
			
		}
		
		/**
		 * Returns date in RFC822 format.
		 *
		 * @since     3.0.0
		 */
		private function __date_to_RFC822( $date ) {
			 return date(DATE_RFC822, strtotime( $date ));
		}
	
	}
	
}
?>