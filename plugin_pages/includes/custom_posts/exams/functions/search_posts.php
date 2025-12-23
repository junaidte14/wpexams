<?php 
// AJAX function
add_action('wp_ajax_qb_search_posts', 'qb_search_posts');
add_action('wp_ajax_nopriv_qb_search_posts', 'qb_search_posts');

function qb_search_posts() {
    if (empty($_POST['keyword'])) {
        $the_query = new WP_Query([
            'post_type' => 'qb_questions',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'DESC'
        ]);
    } else {
        $the_query = new WP_Query([
            'posts_per_page' => 5,
            's' => esc_attr($_POST['keyword']),
            'post_type' => 'qb_questions'
        ]);
    }

    if ($the_query->have_posts()) :
        while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <div class='qb_q_options_div'>
                <span><?php echo esc_html(get_the_title()); ?></span>
                <input type="radio" name='qb_q_options' class='qb_q_options' value='<?php the_ID(); ?>'>
            </div>
        <?php endwhile;
        wp_reset_postdata();
    else :
        echo '<h3>No Results Found</h3>';
    endif;

    die();
}

// Add AJAX JavaScript
add_action('admin_footer', 'qb_ajax_search_posts');
function qb_ajax_search_posts() {
?>
<script type="text/javascript">
    jQuery(document).on('focusin', '.qb_q_IDS_rl', function () {
        let data = fetchPostsResults(jQuery(this).val());
        data.then((data) => {
            jQuery(this).parents(".qb_add_questions").children(".qb_q_search_content").html(data);
            jQuery(this).parents(".qb_add_questions").children(".qb_q_search_content").removeClass("qb_close_dropdown");
        });
    });

    jQuery(document).on('focusout', '.qb_q_IDS_rl', function () {
        setTimeout(() => {
            jQuery(this).parents(".qb_add_questions").children(".qb_q_search_content").addClass("qb_close_dropdown");
        }, 1000);
    });

    function fetchPostsResults(keyword) {
        return new Promise(function (resolve, reject) {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                data: { action: 'qb_search_posts', keyword: keyword },
                success: function (data) {
                    if (data) {
                        resolve(data);
                    }
                }
            });
        });
    }

    jQuery(document).on('keyup', '.qb_q_IDS_rl', function () {
        let JQthis = jQuery(this);
        let dt = new Date();
        let time = dt.getTime() / 1000;

        JQthis.parent(".qb_add_questions").children(".qb_q_search_content").attr("id", time);

        let data = fetchPostsResults(jQuery(this).val());
        data.then((data) => {
            if (document.getElementById(time)) {
                document.getElementById(time).innerHTML = data;
            }
            jQuery(this).parent(".qb_add_questions").children(".qb_q_search_content").removeClass("qb_close_dropdown");
        });
    });
</script>
<?php } ?>
