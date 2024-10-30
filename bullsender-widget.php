<?php
/**
 * Plugin Name: Bullsender signup
 * Description: A widget that displays mail signup forms created on bullsender.com
 * Version: 1.1
 * Author: Bullsender
 * Author URI: https://bullsender.com
 */

add_action( 'widgets_init', 'bullsender_widget' );
include('bullsender-options.php');

function bullsender_widget() {
    register_widget( 'BULLSENDER_Widget' );
}

class BULLSENDER_Widget extends WP_Widget {

	private $options;

    function __construct() {
		$this->options = get_option( 'bullsender_options' );

        $widget_ops = array( 'classname' => 'bullsender', 'description' => __('Bullsender ApS ', 'bullsender') );

        $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'bullsender-widget' );

        parent::__construct( 'bullsender-widget', __('Bullsender Signup Form', 'bullsender'), $widget_ops, $control_ops );
    }

    /**
     * Frontend rendering
     *
     * @param $args
     * @param $instance
     */
    function widget( $args, $instance ) {
        extract( $args );

		$api_key = $this->options['id_number'];

        $form = file_get_contents('https://bullsender.com/api.php?api_key=' . $api_key . '&Method=GetForm&form_id=' . $instance['form_id']);

        //check for errors
        if(strpos('List does not exist', $form)) {
            return;
        }

        //Our variables from the widget settings.
        $title = apply_filters('widget_title', $instance['header'] );
        $name = $instance['subheader'];
        $show_info = isset( $instance['show_info'] ) ? $instance['show_info'] : false;

		if($show_info) {
			echo $before_widget;

			// Display the widget title
			if ( $title )
				echo $before_title . ucfirst($title) . $after_title;

			//Display the subheader
			if ( $name )
				printf($name);

			echo $form;

			echo $after_widget;
		}
    }

    //Update the widget @todo: validation here

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        //Strip tags from title and name to remove HTML
        $instance['form_id'] = strip_tags( $new_instance['form_id'] );
        $instance['header'] = strip_tags( $new_instance['header'] );
        $instance['subheader'] = strip_tags( $new_instance['subheader'] );
        $instance['show_info'] = $new_instance['show_info'];

        return $instance;
    }

    /**
     * Settings for backend
     */
    function form( $instance ) {

        //Set up some default widget settings.
        $defaults = array( 'header' => __('Newsletter signup', 'bullsender'), 'subheader' => __('Receive our newsletter', 'bullsender'), 'show_info' => true );
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>


        <?php

		$api_key = $this->options['id_number'];

        $form_list = json_decode(file_get_contents('https://bullsender.com/api.php?api_key=' . $api_key . '&Method=GetFormList'));

        ?>



        <p>
            <label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e('Signup form:', 'bullsender'); ?></label>
            <select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
            <?php

			foreach($form_list as $form) {
				if($instance['form_id'] == $form->id) {
					echo '<option value="' . $form->id . '" style="width:100%;" selected>' . $form->name . '</option>';
				} else {
					echo '<option value="' . $form->id . '" style="width:100%;">' . $form->name . '</option>';
				}
			}

			?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e('Header:', 'bullsender'); ?></label>
            <input id="<?php echo $this->get_field_id( 'header' ); ?>" name="<?php echo $this->get_field_name( 'header' ); ?>" value="<?php echo $instance['header']; ?>" style="width:100%;" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'subheader' ); ?>"><?php _e('Subheader:', 'bullsender'); ?></label>
            <input id="<?php echo $this->get_field_id( 'subheader' ); ?>" name="<?php echo $this->get_field_name( 'subheader' ); ?>" value="<?php echo $instance['subheader']; ?>" style="width:100%;" />
        </p>

        <p>
            <input class="checkbox" type="checkbox" value="1" <?php checked( $instance['show_info'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_info' ); ?>" name="<?php echo $this->get_field_name( 'show_info' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_info' ); ?>"><?php _e('Make public?', 'bullsender'); ?></label>
        </p>

    <?php
    }
}

?>
