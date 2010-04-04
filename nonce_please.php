<?php
/*
Plugin Name: Nonce, Please!
Plugin URI: http://wordpress.org/extend/plugins/nonce-please/
Version: 1.1.3
Description: Add and confirm random nonce for comments and trackbacks to prevent spam.
Author: IKEDA Yuriko
Author URI: http://en.yuriko.net/
Text Domain: nonce_please
Domain Path: /languages
*/

/*  Copyright (c) 2008-2010 IKEDA Yuriko

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

if ( !defined('WP_INSTALLING') || !WP_INSTALLING ) :

define('NONCE_FIELD', '_wpnonce');
define('COMMENT_NONCE_ACTION', 'post-comments_');
define('TRACKBACK_NONCE_ACTION', 'send-trackbacks_');

/* ==================================================
 *   Nonce_Please class
   ================================================== */

class NoncePlease {
	var $plugin_dir;
	var $text_domain = 'nonce_please';
	var $domain_path = '/languages';
	var $textdomain_loaded = false;

function NoncePlease() {
	return __construct();
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	1.1.0
 */
//public 
function __construct() {
	$this->plugin_dir = basename(dirname(__FILE__));
	add_action('plugins_loaded', array($this, 'load_textdomain'));
	add_action('comment_form', array($this, 'add_comment_nonce'));
	add_action('trackback_url', array($this, 'add_trrackback_nonce'));
	add_action('preprocess_comment', array($this, 'confirm_nonce'), 1);
	if (function_exists('akismet_init')) {
		remove_action('preprocess_comment', 'akismet_auto_check_comment', 1);
		add_action('preprocess_comment', 'akismet_auto_check_comment', 2);
	}
	return;
}

/* ==================================================
 * @param	none
 * @return	none
 * @since	1.1.2
 */
//public 
function load_textdomain() {
	if (! $this->textdomain_loaded) {
		$lang_dir = $this->plugin_dir . $this->domain_path;
		$plugin_path = defined('PLUGINDIR') ? PLUGINDIR . '/' : 'wp-content/plugins/';
		load_plugin_textdomain($this->text_domain, $plugin_path . $lang_dir, $lang_dir);
		$this->textdomain_loaded = true;
	}
}

/* ==================================================
 * @param	none
 * @return	int  $tick
 * @since	1.0.0
 */
//private 
function nonce_tick() {
	if (function_exists('wp_nonce_tick')) {
		return wp_nonce_tick();
	} else {
		return ceil(time() / 43200);
	}
}

/* ==================================================
 * @param	string $action
 * @return	string $nonce
 * @since	1.0.0
 */
//public 
function create_anon_nonce($action) {
	$i = $this->nonce_tick();
	return attribute_escape(substr(wp_hash($i . $action), -12, 10));
}

/* ==================================================
 * @param	string  $nonce
 * @param	string  $action
 * @return	boolean $valid
 * @since	1.0.0
 */
//public 
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

/* ==================================================
 * @param	int $post_id
 * @return	none
 * @since	1.0.0
 */
//public 
function add_comment_nonce($post_id) {
	printf('<input type="hidden" id="%s" name="%s" value="%s" />', 
		attribute_escape(NONCE_FIELD), attribute_escape(NONCE_FIELD), 
		$this->create_anon_nonce(COMMENT_NONCE_ACTION . intval($post_id))
	);
}

/* ==================================================
 * @param	string $url
 * @return	string $url
 * @since	1.0.0
 */
//public 
function add_trrackback_nonce($url) {
	global $id;
	$url .= sprintf('%s%s=%s', 
		(strpos($url, '?') !== false ? '&amp;' : '?'),
		attribute_escape(NONCE_FIELD), 
		$this->create_anon_nonce(TRACKBACK_NONCE_ACTION . intval($id))
	);
	return $url;
}


/* ==================================================
 * @param	array $commentdata
 * @return	array $commentdata
 * @since	1.0.0
 */
//public 
function confirm_nonce($commentdata) {
	if ($commentdata['comment_type'] == 'pingback') {
		return $commentdata;
	}
	if ( !isset($commentdata['comment_post_ID'])) {
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
			if (!isset($_GET[NONCE_FIELD]) 
			|| !$this->verify_anon_nonce($_GET[NONCE_FIELD], TRACKBACK_NONCE_ACTION . intval($commentdata['comment_post_ID']))
			) {
				trackback_response(1, 'We cannot accept your trackback.');
				exit;
			}
		default:
			$user = wp_get_current_user();
			if ( ( !isset($user->ID) || !$user->ID ) 
			&& 
			( !isset($_POST[NONCE_FIELD]) 
			  || !$this->verify_anon_nonce($_POST[NONCE_FIELD], COMMENT_NONCE_ACTION . intval($commentdata['comment_post_ID'])) ) 
			) {
				wp_die(__('Error: Please back to comment form, and retry submit.', 'nonce_please'));
				exit;
			}
		}
	}
	return $commentdata;
}

// ===== End of class ====================
}

$NoncePlease = new NoncePlease();
endif;
?>