<?php
/**
 * Class for managing the voting system.
 */
class Voting_System
{

    /**
     * Submit a vote for the specified post.
     *
     * @param int    $post_id   The ID of the post being voted on.
     * @param string $vote_type The type of vote ('yes' or 'no').
     * @param string $ip        The IP address of the voter.
     *
     * @return bool True if the vote was successful, false otherwise.
     */
    public static function submit_vote($post_id, $vote_type, $ip)
    {
        // Check if the user has already voted for this post.
        if (self::has_voted($post_id, $ip)) {
            return false; // User has already voted, prevent duplicate voting.
        }

        // Get the stored votes for the post.
        $votes = get_post_meta($post_id, '_article_voting_votes', true);

        // Initialize $votes as an array if it's empty.
        if (empty($votes)) {
            $votes = array('positive' => 0, 'negative' => 0);
        }

        // Increment the vote count based on the vote type. 
        if ($vote_type === 'yes') {
            $votes['positive']++;
        } elseif ($vote_type === 'no') {
            $votes['negative']++;
        }

        // Update the stored votes in post meta data.
        update_post_meta($post_id, '_article_voting_votes', $votes);

        // Store the voter's fingerprint to prevent multiple votes.
        self::store_voter_fingerprint($post_id, $ip, $vote_type);

        return true;
    }


    /**
     * Check if the voter has already voted for the specified post.
     *
     * @param int    $post_id The ID of the post being voted on.
     * @param string $ip      The IP address of the voter.
     *
     * @return bool True if the voter has already voted, false otherwise.
     */
    public static function has_voted($post_id, $ip)
    {
        // Get the stored voter fingerprints for the post.
        $voter_fingerprints = get_post_meta($post_id, '_article_voting_voter_fingerprints', true);

        // If there are no stored fingerprints, no votes have been cast yet.
        if (empty($voter_fingerprints)) {
            return false;
        }

        // Loop through fingerprints to find if the IP address has voted
        foreach ($voter_fingerprints as $fingerprint) {
            if ($fingerprint['ip'] === $ip) {
                // IP address found, return the corresponding vote type
                return $fingerprint['vote_type'];
            }
        }

        // IP address not found in fingerprints, user hasn't voted
        return false;
    }

    /**
     * Get the voting results for the specified post.
     *
     * @param int $post_id The ID of the post.
     *
     * @return array An array containing the counts of 'yes' and 'no' votes.
     */
    public static function get_voting_results($post_id)
    {
        $votes = get_post_meta($post_id, '_article_voting_votes', true);

        if (!$votes) {
            $votes = array('positive' => 0, 'negative' => 0);
        }

        return $votes;
    }

    /**
     * Calculate the percentage of positive votes for the specified post.
     *
     * @param int $post_id The ID of the post.
     *
     * @return float The percentage of positive votes.
     */
    public static function calculate_positive_percentage($post_id)
    {
        $votes = self::get_voting_results($post_id);
        $total_votes = $votes['positive'] + $votes['negative'];

        if ($total_votes > 0) {
            return ($votes['positive'] / $total_votes) * 100;
        } else {
            return 0;
        }
    }

    /**
     * Calculate the percentage of negative votes for the specified post.
     *
     * @param int $post_id The ID of the post.
     *
     * @return float The percentage of negative votes.
     */
    public static function calculate_negative_percentage($post_id)
    {
        $votes = self::get_voting_results($post_id);
        $total_votes = $votes['positive'] + $votes['negative'];

        if ($total_votes > 0) {
            return ($votes['negative'] / $total_votes) * 100;
        } else {
            return 0;
        }
    }

    /**
     * Store the voter's fingerprint to prevent multiple votes.
     *
     * @param int    $post_id   The ID of the post.
     * @param string $ip        The IP address of the voter.
     * @param string $vote_type The vote type of the voter.
     */
    private static function store_voter_fingerprint($post_id, $ip, $vote_type)
    {
        // Get the stored voter fingerprints for the post.
        $voter_fingerprints = get_post_meta($post_id, '_article_voting_voter_fingerprints', true);

        // If there are no stored fingerprints, initialize the array.
        if (empty($voter_fingerprints)) {
            $voter_fingerprints = array();
        }

        // Add the voter's IP address and vote type to the array of fingerprints.
        $voter_fingerprints[] = array(
            'ip' => $ip,
            'vote_type' => $vote_type
        );

        // Update the stored fingerprints in post meta data.
        update_post_meta($post_id, '_article_voting_voter_fingerprints', $voter_fingerprints);
    }

    /**
     * Retrieves the vote type (positive or negative) cast by the user for a specified post.
     *
     * @param int    $post_id The ID of the post for which the vote is being retrieved.
     * @param string $ip      The IP address of the user.
     *
     * @return string|false The type of vote (positive or negative) cast by the user, or false
     *                      if the user hasn't voted for the specified post.
     */
    public static function get_user_vote($post_id, $ip)
    {
        // Get the stored voter fingerprints for the post.
        $voter_fingerprints = get_post_meta($post_id, '_article_voting_voter_fingerprints', true);

        // If there are no stored fingerprints, no votes have been cast yet.
        if (empty($voter_fingerprints)) {
            return false;
        }

        // Loop through fingerprints to find if the IP address has voted
        foreach ($voter_fingerprints as $fingerprint) {
            if ($fingerprint['ip'] === $ip) {
                // IP address found, return the corresponding vote type
                return $fingerprint['vote_type'];
            }
        }

        // IP address not found in fingerprints, user hasn't voted
        return false;
    }
}
