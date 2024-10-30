<?php
/*
Plugin Name: List Posts with Pingbacks
Plugin URI:
Description: Adds a list of Posts with Pingbacks and Trackbacks to WordPress with a widget, shortcode, or theme functions.
Author: Hors Hipsrectors
Author URI:
Version: 2017.08.13
*/


/**
 * List Posts with Pingbacks core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/list-posts-with-pingbacks-trackbacks/
 *
 * @package	List Posts with Pingbacks
 * @copyright	Copyright ( c ) 2017, Hors Hipsrectors
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 ( or newer )
 *
 * @since		List Posts with Pingbacks 1.0
 */




/**
 *
 * List Posts with Pingbacks core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/list-posts-with-pingbacks-trackbacks/
 *
 * @package	List Posts with Pingbacks
 * @copyright	Copyright ( c ) 2017, Hors Hipsrectors
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 ( or newer )
 *
 * @since		List Posts with Pingbacks 15.01
 *
 *
 */

/* if the plugin is called directly, die */
if ( ! defined( 'WPINC' ) )
	die;


define( 'horshipsrectors_LPWTB_NAME', 'List Posts with Pingbacks' );
define( 'horshipsrectors_LPWTB_SHORTNAME', 'List Posts with Pingbacks' );

define( 'horshipsrectors_LPWTB_FILENAME', plugin_basename( __FILE__ ) );
define( 'horshipsrectors_LPWTB_FILEPATH', dirname( plugin_basename( __FILE__ ) ) );
define( 'horshipsrectors_LPWTB_FILEPATHURL', plugin_dir_url( __FILE__ ) );

define( 'horshipsrectors_LPWTB_NAMESPACE', basename( horshipsrectors_LPWTB_FILENAME, '.php' ) );
define( 'horshipsrectors_LPWTB_TEXTDOMAIN', str_replace( '-', '_', horshipsrectors_LPWTB_NAMESPACE ) );

define( 'horshipsrectors_LPWTB_VERSION', '15.01' );

include_once( 'horshipsrectors-common.php' );

/**
 * Creates the class required for List Posts with Pingbacks
 *
 * @author	Hors Hipsrectors <info@horshipsrectors.com>
 * @version	Release: @15.01@
 * @see		wp_enqueue_scripts()
 * @since	Class available since Release 15.01
 *
 */
if( ! class_exists( 'thissimyurl_ListPostsWithPingbacksTrackbacks' ) ) {
class thissimyurl_ListPostsWithPingbacksTrackbacks extends horshipsrectors_Common_LPWTB {
	/**
	* Standard Constructor
	*
	* @access public
	* @static
	* @uses http://codex.wordpress.org/Function_Reference/add_shortcode
	* @since Method available since Release 15.01
	*
	*/
	public function run() {

		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		add_shortcode( 'horshipsrectors_list_posts_with_pingbacks_trackbacks', array( $this, 'list_posts_with_pingbacks_trackbacks_shortcode' ) );

	}



	/**
	* list_posts_with_pingbacks_trackbacks_shortcode helper function
	*
	* @access public
	* @static
	* @since Method available since Release 15.01
	*
	*/
	function list_posts_with_pingbacks_trackbacks_shortcode() {

		$pingback_posts = $this->list_posts_with_pingbacks_trackbacks();

		if ( ! empty( $pingback_posts ) )
			echo '<ul class="horshipsrectors-list-posts-with-pingbacks-trackbacks">' . $pingback_posts . '</ul>';

	}



	/**
	* list_posts_with_pingbacks_trackbacks
	*
	* @access public
	* @static
	* @since Method available since Release 15.01
	*
	*/
	function list_posts_with_pingbacks_trackbacks( $options = NULL ) {

		$options = wp_parse_args( $this->list_posts_with_pingbacks_trackbacks_defaults(), $options );
		$pingback_posts = array();

		/* get all comments of type pingback */
		$all_comments = get_comments();
		if( ! empty( $all_comments ) ) {
			foreach ( $all_comments as $comment ) {

				if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) {

					if ( ! in_array( $comment->comment_post_ID, $pingback_posts ) ) {

						if ( isset( $pingback_posts[$comment->comment_post_ID] ) )
							$pingback_posts[$comment->comment_post_ID] = $pingback_posts[$comment->comment_post_ID] + 1;
						else
							$pingback_posts[$comment->comment_post_ID] = 1;

					}

				}
			}

			sort( $pingback_posts );
			$pingback_posts = array_slice( $pingback_posts, $options['post_count'] );
			$pingback_posts = array_flip( $pingback_posts );

		}

		if( ! empty( $pingback_posts ) ) {
			foreach ( $pingback_posts as $pingback_post ) {

				/* place the post title */
				$list_of_posts_item = sprintf( '<span class="title">%s</span>', esc_html( get_the_title( $pingback_post ) ) );


				/* if there's a link, display it */
				if ( $options['include_link'] == 1 ) {

					if( $options['nofollow'] == 1 )
						$nofollow = 'nofollow';
					else
						$nofollow = '';

					$list_of_posts_item = sprintf( '<span class="title-link"><a href="%s" title="%s" %s >%s</a><span>',
											get_permalink( $pingback_post ),
											esc_attr( get_the_title( $pingback_post ) ),
											$nofollow,
											$list_of_posts_item
									);

				}


				/* feature image, if there is one */
				if ( $options['feature_image'] == 1 && has_post_thumbnail( $pingback_post ) ) {

					$list_of_posts_item = sprintf( '<div class="thumbnail">%s</div>%s',
											get_the_post_thumbnail( $thepost->ID,'thumbnail' ),
											$list_of_posts_item
											);

				}


				/* show the excerpt when it's required */
				if ( $options['show_excerpt'] == 1 && ! empty( $pingback_post->post_excerpt ) ) {

					$list_of_posts_item = sprintf( '%s<div class="excerpt">%s</div>',
											$list_of_posts_item,
											esc_html( $pingback_post->post_excerpt )
											);
				}


				/* wrap the content in the proper tags */
				$list_of_posts[] =  $options['before'] . $list_of_posts_item . $options['after'];

			}

		}

		if( ! empty( $list_of_posts ) ) {
			/* return in the proper format */
			if ( $options['show'] == 1 )
				echo implode( '', $list_of_posts );
			else
				return implode( '', $list_of_posts );
		}

	}

	/**
	* list_posts_with_pingbacks_trackbacks_defaults sets defaults for plugin
	*
	* @access public
	* @static
	* @since Method available since Release 15.01
	*
	*/
	function list_posts_with_pingbacks_trackbacks_defaults() {

		$default_options = array(
									'title'		=> __( 'List Posts with Pingbacks', horshipsrectors_LPWTB_NAME ),
									'post_count'	=> 10,
									'order'			=> 'RAND',
									'include_link'	=> 1,
									'before'		=> '<li>',
									'after'			=> '</li>',
									'nofollow'		=> 0,
									'show_excerpt'	=> 0,
									'feature_image' => 0,
									'show_credit'	=> 1,
									'show'			=> 0,

								);

		return $default_options;

	}



	/**
	* widget_init activates the plugin widgets
	*
	* @access public
	* @static
	* @uses register_widget
	* @since Method available since Release 15.01
	*
	*/
	function widget_init() {

		include_once( 'widgets/thissimyurl_ListPostsWithPingbacksTrackbacks_Widget.php' );
		register_widget( 'thissimyurl_ListPostsWithPingbacksTrackbacks_Widget' );

	}



}
}

global $thissimyurl_ListPostsWithPingbacksTrackbacks;

$thissimyurl_ListPostsWithPingbacksTrackbacks = new thissimyurl_ListPostsWithPingbacksTrackbacks;

$thissimyurl_ListPostsWithPingbacksTrackbacks->run();




/**
  * Allows theme authors to call the fuction from theme files
  *
  * @access public
  * @static
  * @uses $thissimyurl_ListPostsWithPingbacksTrackbacks->list_posts_with_pingbacks_trackbacks
  * @since Method available since Release 15.01
  *
  * @param  array see $thissimyurl_ListPostsWithPingbacksTrackbacks->list_posts_with_pingbacks_trackbacks_defaults() for accepted options
  *
  */
if ( ! function_exists( 'horshipsrectors_listpostswithpingbacks' ) ) {
function horshipsrectors_listpostswithpingbacks( $options = NULL ) {

	$thissimyurl_ListPostsWithPingbacksTrackbacks->list_posts_with_pingbacks_trackbacks( $options );

}
}