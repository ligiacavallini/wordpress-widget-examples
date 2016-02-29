<?php
/**
 * @package LCR_Extern_Feed
 * @version 1.0
 */
/*
Plugin Name: Extern Feed by LCR
Plugin URI: --
Description: Extern news feed widget. Input an extern feed url and the amount of news you want to show on your widget.
Author: Ligia Cavallini
Version: 1.0
Author URI: http://ligiacavallini.com
*/

class ExternFeed extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'extern-feed', // Base ID
			__('Extern Feed by LCR', 'text_domain'), // Name
			array( 'description' => __( 'Extern news feed widget. Input an extern feed url and the amount of news you want to show on your widget.', 'text_domain' ), ) // Args
		);
	}

	public function widget( $args, $instance ) {

		$url = esc_attr($instance['url']);
		$qtd = esc_attr($instance['qtd']);

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		$rss = @fetch_feed( $url );

		if ( is_string( $rss ) ) {
			$rss = fetch_feed($rss);
		} elseif ( is_array($rss) && isset($rss['url']) ) {
			$args = $rss;
			$rss = fetch_feed($rss['url']);
		} elseif ( !is_object($rss) ) {
			return;
		}

		if ( is_wp_error($rss) ) {
			if ( is_admin() || current_user_can('manage_options') )
				echo '<p>' . sprintf( __('<strong>RSS Error</strong>: %s'), $rss->get_error_message() ) . '</p>';
			return;
		}

		$items = (int) $qtd;
		$show_summary  = 0;
		$show_author   = 0;
		$show_date     = 1;

		if ( !$rss->get_item_quantity() ) {
			echo '<ul><li>' . __( 'No result.' ) . '</li></ul>';
			$rss->__destruct();
			unset($rss);
			return;
		}

		echo '<ul>';
		foreach ( $rss->get_items(0, $items) as $item ) {
			$link = $item->get_link();
			while ( stristr($link, 'http') != $link )
				$link = substr($link, 1);
			$link = esc_url(strip_tags($link));
			$title = esc_attr(strip_tags($item->get_title()));
			if ( empty($title) )
				$title = __('Untitled');

			$desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
			$excerpt = wp_html_excerpt( $desc, 360 );

			// Append ellipsis. Change existing [...] to [&hellip;].
			if ( '[...]' == substr( $excerpt, -5 ) )
				$excerpt = substr( $excerpt, 0, -5 ) . '[&hellip;]';
			elseif ( '[&hellip;]' != substr( $excerpt, -10 ) && $desc != $excerpt )
				$excerpt .= ' [&hellip;]';

			$excerpt = esc_html( $excerpt );

			if ( $show_summary ) {
				$summary = "<div class='rssSummary'>$excerpt</div>";
			} else {
				$summary = '';
			}

			$date = '';
			if ( $show_date ) {
				$date = $item->get_date( 'U' );

				if ( $date ) {
					$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date ) . '</span>';
				}
			}
			$author = '';
			if ( $show_author ) {
				$author = $item->get_author();
				if ( is_object($author) ) {
					$author = $author->get_name();
					$author = ' <cite>' . esc_html( strip_tags( $author ) ) . '</cite>';
				}
			}
			if ( $link == '' ) {
				echo "<li>$title{$date}{$summary}{$author}</li>";
			} else {
				echo "<li><a class='rsswidget' href='$link' title='$desc' target='_blank'>$title</a>{$date}{$summary}{$author}</li>";
			}
		}
		echo '</ul>';
		echo $args['after_widget'];

	}

 	public function form( $instance ) {
		$instance = wp_parse_args((array)$instance, array('title' => ''));
		$title = esc_attr($instance['title']);
		$url = esc_attr($instance['url']);
		$qtd = esc_attr($instance['qtd']);

?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'natural'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>Cole no campo abaixo a url do feed que você deseja importar:<p>
		<p><label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('URL', 'natural'); ?></label>
		<input id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" size="37" /></p>

		<p><label for="<?php echo $this->get_field_id('qtd'); ?>"><?php _e('Quantidade de notícias que devem ser exibidas', 'natural'); ?></label>
		<input id="<?php echo $this->get_field_id('qtd'); ?>" name="<?php echo $this->get_field_name('qtd'); ?>" type="text" value="<?php echo $qtd; ?>" size="3" /></p>


<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['url'] = $new_instance['url'];
		$instance['qtd'] = $new_instance['qtd'];
		return $instance;
	}
}

add_action( 'widgets_init', function(){
     register_widget( 'ExternFeed' );
});
