<?php
/**
 * @package LCR_Instagram_Profile
 * @version 1.0
 */
/*
Plugin Name: Instagram Profile by LCR
Plugin URI: --
Description: Instagram profile widget for winecodeavocado.com
Author: Ligia Cavallini
Version: 1.0
Author URI: http://ligiacavallini.com
*/

class InstagramProfile extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'instagrams_widget', // Base ID
			__( 'Instagram profile by LCR', 'lcr' ), // Name
			array( 'description' => __( 'Widget with your instagram profile. User ID and Client ID is necessary and it must be of a public account.', 'lcr' ), )
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		if($instance['userid'] && $instance['clientid'] && is_numeric($instance['userid'])){

			$url = "https://api.instagram.com/v1/users/".$instance['userid']."?client_id=".$instance['clientid'];
			$response = get_headers($url);

			if($response[0]=='HTTP/1.1 200 OK'){
				$json = file_get_contents($url);
				$obj = json_decode($json);
				$dados = $obj->data;
				?>
					<div id="instagram-profile">
						<a href="http://instagram.com/<?php echo $dados->username;?>" target="_blank">
							<img src="<?php echo $dados->profile_picture; ?>" class="img-circle" />
						</a>
						<div class="icons">
							<span class="glyphicon glyphicon-send middle" aria-hidden="true"></span>
						</div>
						<h4>
							<a href="http://instagram.com/<?php echo $dados->username;?>" target="_blank">
								/<?php echo $dados->username;?>
							</a>
						</h4>
						<div><?php echo $dados->counts->media;?> fotos</div>
						<p><?php echo $dados->bio;?></p>
					</div>
				<?php
			}else{
				echo "É necessário informar o userID e o clientID  válidos para que o widget funcione corretamente";
			}
		}else{
			echo "É necessário informar o userID e o clientID para que o widget funcione corretamente";
		}
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Instagram', 'lcr' );
		$userid= ! empty( $instance['userid'] ) ? $instance['userid'] : __( 'User ID', 'lcr' );
		$clientid = ! empty( $instance['clientid'] ) ? $instance['clientid'] : __( 'Client ID', 'lcr' );
	?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'userid' ); ?>"><?php _e( 'User ID:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'userid' ); ?>" name="<?php echo $this->get_field_name( 'userid' ); ?>" type="text" value="<?php echo esc_attr( $userid ); ?>">
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'clientid' ); ?>"><?php _e( 'Client ID:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'clientid' ); ?>" name="<?php echo $this->get_field_name( 'clientid' ); ?>" type="text" value="<?php echo esc_attr( $clientid ); ?>">
		</p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['userid'] = ( ! empty( $new_instance['userid'] ) ) ? strip_tags( $new_instance['userid'] ) : '';
		$instance['clientid'] = ( ! empty( $new_instance['clientid'] ) ) ? strip_tags( $new_instance['clientid'] ) : '';
		return $instance;
	}
}

add_action( 'widgets_init', function(){
     register_widget( 'InstagramProfile' );
});
