<div class="article-voting-results">
    <p><?php _e('Voting Results:', 'article-voting'); ?></p>
    <p><?php printf(__('Yes: %s%%', 'article-voting'), $voting_results['positive_percentage']); ?></p>
    <p><?php printf(__('No: %s%%', 'article-voting'), $voting_results['negative_percentage']); ?></p>
</div>