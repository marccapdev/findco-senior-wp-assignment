<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options and data stored in the database.
delete_option('article_voting_plugin_options');

// Remove post meta created by the plugin.
$plugin_posts = get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'meta_key'       => '_article_voting_votes',
    'fields'         => 'ids',
));

foreach ($plugin_posts as $post_id) {
    delete_post_meta($post_id, '_article_voting_votes');
}
