<?php
/*
Plugin Name: Article Voting
Plugin URI: https://mywebsite.com/article-voting
Description: Allow website visitors to vote on articles.
Version: 1.0.0
Author: Marc Luther Capulong
Author URI: https://mywebsite.com
License: GPL v2 or later
Text Domain: article-voting
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants.
define('ARTICLE_VOTING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ARTICLE_VOTING_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once ARTICLE_VOTING_PLUGIN_DIR . 'includes/class-article-voting.php';
require_once ARTICLE_VOTING_PLUGIN_DIR . 'admin/class-admin.php';

// Initialize the plugin.
function article_voting_init()
{
    $article_voting = new Article_Voting();
    $article_voting->init();
}
add_action('plugins_loaded', 'article_voting_init');
