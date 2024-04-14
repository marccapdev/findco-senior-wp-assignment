jQuery(document).ready(function($) {
    // Handle click event on vote buttons.
    $('.voting-block__button').on('click', function() {
        var button = $(this);
        var post_id = button.closest('.voting-block').data('post-id');
        var vote_type = button.data('type');

        // Make AJAX request to submit vote.
        $.ajax({
            url: article_voting_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'article_voting_submit_vote',
                nonce: article_voting_ajax.nonce,
                post_id: post_id,
                vote_type: vote_type
            },
            beforeSend: function() {
                // Disable vote buttons during AJAX request.
                $('.voting-block__button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    // Disable voting buttons
                    $('.voting-block__button').prop('disabled', true);

                    // Update voting results on success.
                    $('.voting-block__result--positive span').text(response.data.positive_percentage + '%');
                    $('.voting-block__result--negative span').text(response.data.negative_percentage + '%');

                    // Highlight users's vote type
                    let voteType = response.data.vote_type;

                    if (voteType === 'yes') {
                        $('.voting-block__result--positive').addClass('voting-block__result--active');
                    } else if (voteType === 'no') {
                        $('.voting-block__result--negative').addClass('voting-block__result--active');
                    }
                } else {
                    // Display error message on failure.
                    console.log(response.data);
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                // Display error message on AJAX error.
                console.log(xhr.responseText);
            },
            complete: function() {}
        });
    });
});