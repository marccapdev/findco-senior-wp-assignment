<?php

require_once 'class-voting-system.php';

/**
 * Main plugin class for Article Voting.
 */
class Article_Voting
{

    /**
     * Initialize the plugin.
     */
    public function init()
    {
        // Load translations.
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Enqueue scripts and styles.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));

        // Register AJAX handlers.
        add_action('wp_ajax_article_voting_submit_vote', array($this, 'submit_vote'));
        add_action('wp_ajax_nopriv_article_voting_submit_vote', array($this, 'submit_vote'));

        // Add voting feature to single posts.
        add_filter('the_content', array($this, 'add_voting_to_single_posts'));
    }

    /**
     * Load plugin textdomain for localization.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('article-voting', false, ARTICLE_VOTING_PLUGIN_DIR . '/languages/');
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts_and_styles()
    {
        // Enqueue CSS file.
        wp_enqueue_style('article-voting', ARTICLE_VOTING_PLUGIN_URL . 'assets/css/article-voting.css', array(), '1.0.0');

        // Enqueue JavaScript file with jQuery dependency.
        wp_enqueue_script('article-voting', ARTICLE_VOTING_PLUGIN_URL . 'assets/js/article-voting.js', array('jquery'), '1.0.0', true);

        // Localize script with AJAX URL and nonce.
        wp_localize_script(
            'article-voting',
            'article_voting_ajax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('article-voting-nonce'),
            )
        );
    }

    /**
     * Submit vote via AJAX.
     */
    public function submit_vote()
    {
        // Check if the AJAX request is valid.
        check_ajax_referer('article-voting-nonce', 'nonce');

        // Get the post ID and vote type from the AJAX request.
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $vote_type = isset($_POST['vote_type']) ? sanitize_text_field($_POST['vote_type']) : '';

        // Validate post ID and vote type.
        if (!$post_id || !in_array($vote_type, array('yes', 'no'))) {
            wp_send_json_error(__('Invalid vote data.', 'article-voting'));
        }

        // Get the voter's IP address.
        $ip = $_SERVER['REMOTE_ADDR'];

        // Submit the vote using the Voting_System class.
        $result = Voting_System::submit_vote($post_id, $vote_type, $ip);

        // Check if the vote submission was successful.
        if ($result) {
            // If successful, return the updated voting results.
            $voting_results = array(
                'vote_type' => Voting_System::get_user_vote($post_id, $ip),
                'positive_percentage' => Voting_System::calculate_positive_percentage($post_id),
                'negative_percentage' => Voting_System::calculate_negative_percentage($post_id)
            );
            wp_send_json_success($voting_results);
        } else {
            // If unsuccessful, return an error message.
            wp_send_json_error(__('Failed to submit vote.', 'article-voting'));
        }
    }

    /**
     * Add voting feature to single posts.
     *
     * @param string $content The original post content.
     * @return string The modified post content with the voting feature.
     */
    public function add_voting_to_single_posts($content)
    {
        // Check if we're on a single post page.
        if (is_single() && in_the_loop() && is_main_query()) {
            // Get the current post ID.
            $post_id = get_the_ID();

            // Get the voter's IP address.
            $ip = $_SERVER['REMOTE_ADDR'];

            $button_disabled = '';
            if (Voting_System::has_voted($post_id, $ip)) {
                $button_disabled = 'disabled';
            }

            $user_vote = Voting_System::get_user_vote($post_id, $ip);

            // Get the voting results for the current post.
            $voting_results = array(
                'positive_percentage' => Voting_System::calculate_positive_percentage($post_id),
                'negative_percentage' => Voting_System::calculate_negative_percentage($post_id)
            );

            // Output the voting feature HTML.
            ob_start();
            ?>
            <div class="voting-block" data-post-id="<?php echo $post_id; ?>">
                <div class="voting-block__row">
                    <p class="voting-block__text">
                        <?php _e('WAS THIS ARTICLE HELPFUL?', 'article-voting'); ?>
                    </p>
                    <button class="voting-block__button voting-block__button--yes" data-type="yes" <?php echo $button_disabled; ?>>
                        <svg class="voting-block__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                            <path
                                d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm80 168c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm-160 0c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm194.8 170.2C334.3 380.4 292.5 400 248 400s-86.3-19.6-114.8-53.8c-13.6-16.3 11-36.7 24.6-20.5 22.4 26.9 55.2 42.2 90.2 42.2s67.8-15.4 90.2-42.2c13.4-16.2 38.1 4.2 24.6 20.5z" />
                        </svg>
                        <?php _e('Yes', 'article-voting'); ?>
                    </button>
                    <button class="voting-block__button voting-block__button--no" data-type="no" <?php echo $button_disabled; ?>>
                        <svg class="voting-block__icon" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 496 512">
                            <path
                                d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm-80 168c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm176 192H152c-21.2 0-21.2-32 0-32h192c21.2 0 21.2 32 0 32zm-16-128c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z" />
                        </svg>
                        <?php _e('No', 'article-voting'); ?>
                    </button>
                </div>
                <div class="voting-block__row">
                    <p class="voting-block__text">
                        <?php _e('THANK YOU FOR YOUR FEEDBACK.', 'article-voting'); ?>
                    </p>
                    <div class="voting-block__result voting-block__result--positive <?php if ($user_vote === 'yes') echo 'voting-block__result--active'; ?>">
                        <svg class="voting-block__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512">
                            <path
                                d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm80 168c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm-160 0c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm194.8 170.2C334.3 380.4 292.5 400 248 400s-86.3-19.6-114.8-53.8c-13.6-16.3 11-36.7 24.6-20.5 22.4 26.9 55.2 42.2 90.2 42.2s67.8-15.4 90.2-42.2c13.4-16.2 38.1 4.2 24.6 20.5z" />
                        </svg>
                        <span><?php echo $voting_results['positive_percentage'] ?>%</span>
                    </div>
                    <div class="voting-block__result voting-block__result--negative <?php if ($user_vote === 'no') echo 'voting-block__result--active'; ?>">
                        <svg class="voting-block__icon" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 496 512">
                            <path
                                d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm-80 168c17.7 0 32 14.3 32 32s-14.3 32-32 32-32-14.3-32-32 14.3-32 32-32zm176 192H152c-21.2 0-21.2-32 0-32h192c21.2 0 21.2 32 0 32zm-16-128c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z" />
                        </svg>
                        <span><?php echo $voting_results['negative_percentage']; ?>%</span>
                    </div>
                </div>
            </div>
            <?php
            $voting_feature = ob_get_clean();

            // Append the voting feature to the post content.
            $content .= $voting_feature;
        }

        return $content;
    }

}
