<?php
/*
Plugin Name: Profileir
Plugin URI: http://pamjad.me/profileir
Description: A brief description of the Plugin.
Version: 1.0
Author: Pouriya Amjadzadeh
Author URI: http://pamjad.me
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Check if the Dashboard or the administration panel is attempting to be displayed
if ( !is_admin() ) :
function pj_pfir_profileir($avatar, $id_or_email, $size = '80', $default = '', $alt = false  ) {
	//Check if avatars is disable return false
	if ( ! get_option('show_avatars') ) return false;

	//Set size for avatars
	if ( !is_numeric($size) ) $size = '80';
	
    if ( !in_array($size, array(40, 80, 200, 400)) ) {
        if ($size < 40)
            $size = 40;
        elseif ($size > 40 && $size < 80)
            $size = 80;
        elseif ($size > 80 && $size < 200)
            $size = 200;
        elseif ($size > 200)
            $size = 400;
    }
	
	//Set email for get avatar
	$email = '';
	if ( is_numeric($id_or_email) ) {
        $id = (int) $id_or_email;
        $user = get_userdata($id);
        if ( $user )
            $email = $user->user_email;
    } elseif ( is_object($id_or_email) ) {
        // No avatar for pingbacks or trackbacks
        $allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
        if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
            return false;

        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
            $user = get_userdata($id);
            if ( $user )
                $email = $user->user_email;
        }

        if ( ! $email && ! empty( $id_or_email->comment_author_email ) ) $email = $id_or_email->comment_author_email;
    } else {
        $email = sanitize_email($id_or_email);
    }

	//Set default avatar
	if ( empty($default) ) {
		$default = esc_url( plugin_dir_url( __FILE__ ) . 'assets/default-thumb.jpg' );
	}

    //Check for safe alt in html
	if ( false === $alt)
		$safe_alt = esc_attr( 'Profile Avatar' );
	else
		$safe_alt = esc_attr( $alt );

	if ( !empty($email) ) {
		$mdMail = md5($email);
		$pfFetch = file_get_contents("https://avatar.profile.ir/$mdMail.json");
		$pfObj = json_decode($pfFetch);
		if( $pfObj != NULL ) {
			$avatarURL = $pfObj->entry->thumbnailUrl;
			$out = esc_url( $avatarURL.'?s='.$size );
			$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size}' height='{$size}' width='{$size}' />";	
		} else {
			$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} avatar-default' height='{$size}' width='{$size}' />";
		}
	} else {
		$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} avatar-default' height='{$size}' width='{$size}' />";
	}

    return $avatar;
}
add_filter( 'get_avatar', 'pj_pfir_profileir', 10, 5);
endif;

function pj_pfir_default_avatar( $avatar_defaults ) {
	$pfir_default = esc_url( plugin_dir_url( __FILE__ ) . 'assets/default-thumb.jpg' );
	$avatar_defaults[$pfir_default] = 'Profile.ir';
	return $avatar_defaults;
}
add_filter( 'avatar_defaults', 'pj_pfir_default_avatar' );

function pj_pfir_activated() {
	$pfir_default = esc_url( plugin_dir_url( __FILE__ ) . 'assets/default-thumb.jpg' );
	update_option( 'avatar_default' , $pfir_default );
}
register_activation_hook( __FILE__, 'pj_pfir_activated' );
?>