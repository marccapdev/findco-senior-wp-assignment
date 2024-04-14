<?php
// If uninstall is not called from WordPress, exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options and data stored in the database.
delete_option('article_voting_plugin_options');

// Remove post meta created by the plugin.
$plugin_posts = get_posts(array(
    'post_type'      => 'post', // Adjust post type if necessary
    'posts_per_page' => -1,     // Get all posts
    'meta_key'       => '_article_voting_votes', // Meta key used by the plugin
    'fields'         => 'ids',  // Get only post IDs
));

foreach ($plugin_posts as $post_id) {
    delete_post_meta($post_id, '_article_voting_votes');
}
