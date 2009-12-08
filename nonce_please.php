<?php
/*
Plugin Name: Nonce, Please!
Plugin URI: http://www.yuriko.net/dist/nonce_please/
Description: Add and confirm wpnonce for comments and trackbacks.
Author: IKEDA Yuriko
Version: 1.1.1
Author URI: http://www.yuriko.net/cat/wordpress/
*/

/*  Copyright (c) 2008-2009 IKEDA Yuriko

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define('NONCE_FIELD', '_wpnonce');
define('COMMENT_NONCE_ACTION', 'post-comments_');
define('TRACKBACK_NONCE_ACTION', 'send-trackbacks_');

$Nonce_Please = new Nonce_Please();

/* ==================================================
 *   Nonce_Please class
   ================================================== */

class Nonce_Please {

function Nonce_Please() {
	return __construct();
}

function __construct() {
	add_action('comment_form', array($this, 'add_co_nonce'));
	add_action('trackback_url', array($this, 'add_tr_nonce'));
	add_action('preprocess_comment', array($this, 'confirm_nonce'), 1);
	if (function_exists('akismet_init')) {
		remove_action('preprocess_comment', 'akismet_auto_check_comment', 1);
		add_action('preprocess_comment', 'akismet_auto_check_comment', 2);
	}
	return;
}

function nonce_tick() {
	if (function_exists('wp_nonce_tick')) {
		return wp_nonce_tick();
	} else {
		return ceil(time() / 43200);
	}
}

function create_anon_nonce($action) {
	$i = $this->nonce_tick();
	return attribute_escape(substr(wp_hash($i . $action), -12, 10));
}

function verify_anon_nonce($nonce, $action = -1) {
	$i = $this->nonce_tick();
	// Nonce generated 0-12 hours ago
	if ( substr(wp_hash($i . $action), -12, 10) == $nonce )
		return 1;
	// Nonce generated 12-24 hours ago
	if ( substr(wp_hash(($i - 1) . $action), -12, 10) == $nonce )
		return 2;
	// Invalid nonce
	return false;
}

function add_co_nonce($post_id) {
	echo '<input type="hidden" id="' . attribute_escape(NONCE_FIELD) . '" name="' . attribute_escape(NONCE_FIELD) . '" value="' . $this->create_anon_nonce(COMMENT_NONCE_ACTION . intval($post_id)) . '" />';
}

function add_tr_nonce($tb_url) {
	global $id;
	return $tb_url . ((strpos($tb_url, '?') !== false) ? '&' : '?') . attribute_escape(NONCE_FIELD) . '=' . $this->create_anon_nonce(TRACKBACK_NONCE_ACTION . intval($id));
}

function confirm_nonce($commentdata) {
	if ($commentdata['comment_type'] == 'pingback') {
		return $commentdata;
	}
	if (! isset($commentdata['comment_post_ID'])) {
		switch ($commentdata['comment_type']) {
		case 'trackback':
			trackback_response(1, 'We cannot accept a trackback without post ID.');
			exit;
		default:
			wp_die(__('Error: Invalid comment form. Please add the comment post ID field.', 'nonce_please'));
		}
		exit;
	} else {
		switch ($commentdata['comment_type']) {
		case 'trackback':		
			if (! isset($_GET[NONCE_FIELD]) || ! $this->verify_anon_nonce($_GET[NONCE_FIELD], TRACKBACK_NONCE_ACTION . intval($commentdata['comment_post_ID']))) {
				trackback_response(1, 'We cannot accept your trackback.');
				exit;
			}
		default:
			$user = wp_get_current_user();
			if ( ( !isset($user->ID) || !$user->ID ) 
			&& 
			( !isset($_POST[NONCE_FIELD]) || 
			  !$this->verify_anon_nonce($_POST[NONCE_FIELD], COMMENT_NONCE_ACTION . intval($commentdata['comment_post_ID'])) ) ) {
				wp_die(__('Error: Please back to comment form, and retry submit.', 'nonce_please'));
				exit;
			}
		}
	}
	return $commentdata;
}

// ===== End of class ====================
}

?>