<?php
/**
 * Admin-related functionality for Article Voting plugin.
 */
class Article_Voting_Admin
{

    /**
     * Initialize the admin-related functionality.
     */
    public function init()
    {
        // Add meta box to display voting results on the post edit screen.
        add_action('add_meta_boxes', array($this, 'add_voting_results_meta_box'));
    }

    /**
     * Add meta box to display voting results on the post edit screen.
     */
    public function add_voting_results_meta_box()
    {
        add_meta_box(
            'article-voting-results',
            __('Article Voting Results', 'article-voting'),
            array($this, 'render_voting_results_meta_box'),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render voting results meta box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_voting_results_meta_box($post)
    {
        // Get the post ID.
        $post_id = $post->ID;

        // Get the voting results for the current post.
        $voting_results = array(
            'positive_percentage' => Voting_System::calculate_positive_percentage($post_id),
            'negative_percentage' => Voting_System::calculate_negative_percentage($post_id)
        );

        // Include the meta box template.
        include ARTICLE_VOTING_PLUGIN_DIR . 'templates/admin/voting-results-meta-box.php';
    }
}

// Initialize the admin class.
$article_voting_admin = new Article_Voting_Admin();
$article_voting_admin->init();
