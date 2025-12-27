<?php
/**
 * Exam Detail Form Template
 *
 * Form for users to create custom exams
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get all questions
$all_questions = get_posts(
	array(
		'post_type'      => 'wpexams_question',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'DESC',
	)
);

if ( empty( $all_questions ) ) {
	?>
	<div class="wpexams-content">
		<p><?php esc_html_e( 'No questions available yet. Please contact the administrator.', 'wpexams' ); ?></p>
	</div>
	<?php
	return;
}

// Get categories
$categories = get_categories(
	array(
		'orderby' => 'name',
		'order'   => 'ASC',
	)
);

?>

<div class="wpexams-content">
	<form action="javascript:void(0)" id='wpexams-exam-detail-form'>
		<p><?php esc_html_e( 'Configure your exam by selecting categories and number of questions.', 'wpexams' ); ?></p>

		<!-- Category Selection -->
		<div class='wpexams-category-content' id='wpexams-category-content'> 
			<p class='wpexams-bold'><?php esc_html_e( 'Question Category', 'wpexams' ); ?></p>

			<label>
				<input type="checkbox" checked id='wpexams-all-category' value="-1">
				<?php
				/* translators: %d: number of questions */
				printf( esc_html__( 'All (%d)', 'wpexams' ), count( $all_questions ) );
				?>
			</label><br>

			<?php foreach ( $categories as $category ) : ?>
				<?php
				$cat_questions = get_posts(
					array(
						'post_type'      => 'wpexams_question',
						'category'       => $category->term_id,
						'posts_per_page' => -1,
						'post_status'    => 'publish',
					)
				);
				?>
				<label>
					<input type="checkbox" 
						   class="wpexams-category-checkbox"
						   id="wpexams-cat-<?php echo esc_attr( $category->term_id ); ?>" 
						   value="<?php echo esc_attr( $category->term_id ); ?>">
					<?php
					echo esc_html( $category->name );
					if ( $cat_questions ) {
						/* translators: %d: number of questions */
						printf( esc_html__( ' (%d)', 'wpexams' ), count( $cat_questions ) );
					} else {
						echo ' (0)';
					}
					?>
				</label><br>
			<?php endforeach; ?>
		</div>

		<!-- Number of Questions -->
		<div class='wpexams-q-number-content'>
			<p class='wpexams-m-0 wpexams-bold'><?php esc_html_e( 'Number of Questions *', 'wpexams' ); ?></p>
			<input type="number" 
				   name="wpexams_question_count" 
				   class='wpexams-q-numbers-field' 
				   id="wpexams_question_count"
				   min="1"
				   max="100"
				   required>
		</div>

		<!-- Timed Exam -->
		<div class='wpexams-timed-content wpexams-mt-15'>
			<label>
				<input type="checkbox" value='1' name="wpexams_timed_field" id="wpexams_timed_field">
				<span class="wpexams-bold"><?php esc_html_e( 'Timed Exam?', 'wpexams' ); ?></span>
			</label>
		</div>

		<div id="wpexams-exam-error-message" style="color: red; margin: 10px 0;"></div>

		<button class='wpexams-button wpexams-exam-button' 
				type='button' 
				id='wpexams-start-exam-btn'>
			<?php esc_html_e( 'Start Exam', 'wpexams' ); ?>
		</button>
	</form>
</div>

<script>
jQuery(document).ready(function($) {
	// Handle "All" checkbox
	$('#wpexams-all-category').on('change', function() {
		if ($(this).is(':checked')) {
			$('.wpexams-category-checkbox').prop('checked', false);
		}
	});

	// Uncheck "All" when individual category is selected
	$('.wpexams-category-checkbox').on('change', function() {
		if ($(this).is(':checked')) {
			$('#wpexams-all-category').prop('checked', false);
		}
	});

	// Start exam button
	$('#wpexams-start-exam-btn').on('click', function() {
		var $btn = $(this);
		var $form = $('#wpexams-exam-detail-form');
		var $errorMsg = $('#wpexams-exam-error-message');
		
		$errorMsg.text('');
		
		// Get selected categories
		var categories = [];
		if ($('#wpexams-all-category').is(':checked')) {
			categories.push('-1');
		} else {
			$('.wpexams-category-checkbox:checked').each(function() {
				categories.push($(this).val());
			});
		}

		// Validate
		if (categories.length === 0) {
			$errorMsg.text('<?php esc_html_e( 'Please select at least one category.', 'wpexams' ); ?>');
			return;
		}

		var questionCount = $('#wpexams_question_count').val();
		if (!questionCount || questionCount < 1) {
			$errorMsg.text('<?php esc_html_e( 'Please enter number of questions (minimum 1).', 'wpexams' ); ?>');
			return;
		}

		// Prepare data
		var examData = {
			category_field: categories,
			question_count: questionCount,
			is_timed: $('#wpexams_timed_field').is(':checked') ? '1' : '0'
		};

		// Disable button
		$btn.prop('disabled', true).text('<?php esc_html_e( 'Creating...', 'wpexams' ); ?>');

		// Submit via AJAX
		$.ajax({
			url: wpexamsData.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wpexams_save_exam',
				nonce: wpexamsData.nonce,
				exam_data: examData
			},
			success: function(response) {
				if (response.success) {
					// Redirect to exam
					window.location.href = '?wpexams_exam_id=' + response.data.exam_id;
				} else {
					$errorMsg.text(response.data.message || '<?php esc_html_e( 'Error creating exam.', 'wpexams' ); ?>');
					$btn.prop('disabled', false).text('<?php esc_html_e( 'Start Exam', 'wpexams' ); ?>');
				}
			},
			error: function() {
				$errorMsg.text('<?php esc_html_e( 'Error creating exam. Please try again.', 'wpexams' ); ?>');
				$btn.prop('disabled', false).text('<?php esc_html_e( 'Start Exam', 'wpexams' ); ?>');
			}
		});
	});
});
</script>