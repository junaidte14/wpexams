<?php
/**
 * Exam History Template - FIXED VERSION
 *
 * Display user's exam history with scores and options to review
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if viewing specific exam history
if ( isset( $_GET['wpexams_history_id'] ) ) {
	$exam_id = absint( $_GET['wpexams_history_id'] );
	
	// Get exam data
	$exam_data   = wpexams_get_post_data( $exam_id );
	$exam_result = $exam_data->exam_result;
	$exam_detail = $exam_data->exam_detail;

	if ( ! $exam_result || ! $exam_detail ) {
		echo '<p>' . esc_html__( 'Exam history not found.', 'wpexams' ) . '</p>';
		return;
	}

	// Calculate score
	$correct_count = 0;
	$correct_question_ids = array();
	
	if ( isset( $exam_result['correct_answers'] ) && is_array( $exam_result['correct_answers'] ) ) {
		foreach ( $exam_result['correct_answers'] as $answer ) {
			if ( isset( $answer['question_id'] ) && isset( $answer['answer'] ) && 'null' !== $answer['answer'] ) {
				$correct_question_ids[] = (string) $answer['question_id'];
			}
		}
	}
	
	// Remove duplicates and count
	$correct_question_ids = array_unique( $correct_question_ids );
	$correct_count = count( $correct_question_ids );

	$total_questions = isset( $exam_result['total_questions'] ) ? $exam_result['total_questions'] : 0;
	$exam_time       = isset( $exam_result['exam_time'] ) ? $exam_result['exam_time'] : '00:00:00';
	$percentage      = $total_questions > 0 ? round( ( $correct_count / $total_questions ) * 100 ) : 0;

	?>
	<div class="wpexams-content">
		<div class='wpexams-d-flex wpexams-m-tb-20'>
			<h5 class='wpexams-m-0'>
				<?php
				/* translators: 1: correct answers, 2: total questions, 3: percentage */
				printf( esc_html__( 'Score %1$d/%2$d (%3$d%%)', 'wpexams' ), $correct_count, $total_questions, $percentage );
				?>
			</h5>
			<?php if ( 'expired' !== $exam_time ) : ?>
				<span><strong><?php esc_html_e( 'Time Taken:', 'wpexams' ); ?></strong> <?php echo esc_html( $exam_time ); ?></span>
			<?php else : ?>
				<span style="color: #f44336;"><strong><?php esc_html_e( 'Status:', 'wpexams' ); ?></strong> <?php esc_html_e( 'Expired', 'wpexams' ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( isset( $exam_result['filtered_questions'] ) ) : ?>
			<?php $question_index = 0; ?>
			<?php foreach ( $exam_result['filtered_questions'] as $question_id ) : ?>
				<?php
				$question_index++;
				$question_data = wpexams_get_post_data( $question_id );
				$question_fields = $question_data->question_fields;

				// Get user's answer
				$user_answer = wpexams_get_user_answer( $exam_result, $question_id );
				
				// Get question time
				$question_time = wpexams_get_question_time( $exam_result, $question_id );
				?>

				<div class="wpexams-exam-result wpexams-show">
					<div class="wpexams-accordion-container">
						<div class="wpexams-result-header wpexams-accordion-trigger" data-question-index="<?php echo esc_attr( $question_index ); ?>">
							<h3><?php echo esc_html( get_the_title( $question_id ) ); ?></h3>
							<p><span><?php echo esc_html( $question_time ); ?></span> <span class="wpexams-accordion-arrow">▼</span></p>
						</div>
						<div class="wpexams-accordion-content" data-question-index="<?php echo esc_attr( $question_index ); ?>" style="display: none;">
							<?php foreach ( $question_fields['options'] as $key => $option ) : ?>
								<?php
								$is_correct  = ( 'wpexams_c_option_' . $key === $question_fields['correct_option'] );
								$is_selected = ( $key == $user_answer );
								?>
								<a href="javascript:void(0)" 
								   style="display: flex;align-items: center;padding: 12px;margin: 5px 0;border: 2px solid #ddd;border-radius: 6px;text-decoration: none;transition: all 0.2s;" 
								   class="<?php echo $is_selected ? 'wpexams-subscriber-answer-sl' : ''; ?>">
									<span class="wpexams-alpha-options <?php echo $is_correct ? 'wpexams-green' : 'wpexams-red'; ?>">
										<?php echo intval( $key ) + 1; ?>
									</span>
									<span style="flex-grow:1;margin-left: 10px;color: #333;">
										<?php echo esc_html( str_replace( '_', ' ', $option ) ); ?>
									</span>
									<?php if ( $is_correct ) : ?>
										<span class="wpexams-immed-answer-is-true">✓</span>
									<?php else : ?>
										<span class="wpexams-immed-answer-is-false">✗</span>
									<?php endif; ?>
								</a>
							<?php endforeach; ?>
							
							<?php if ( ! empty( $question_fields['description'] ) ) : ?>
								<div style="background: #f8f9fa;padding: 15px;border-left: 4px solid #2196f3;border-radius: 4px;margin-top: 15px;">
									<strong><?php esc_html_e( 'Explanation:', 'wpexams' ); ?></strong>
									<p style="margin: 5px 0 0 0;"><?php echo esc_html( $question_fields['description'] ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		// Accordion functionality
		$('.wpexams-accordion-trigger').on('click', function() {
			var questionIndex = $(this).data('question-index');
			var content = $('.wpexams-accordion-content[data-question-index="' + questionIndex + '"]');
			var arrow = $(this).find('.wpexams-accordion-arrow');
			
			// Toggle content
			content.slideToggle(300);
			
			// Rotate arrow
			if (content.is(':visible')) {
				arrow.css('transform', 'rotate(180deg)');
			} else {
				arrow.css('transform', 'rotate(0deg)');
			}
		});
	});
	</script>
	<?php
	return;
}

// Display exam history list
?>

<div class="wpexams-content">
	<p><?php esc_html_e( 'History of exams that you have taken.', 'wpexams' ); ?></p>

	<?php
	$user_results = new WP_Query(
		array(
			'post_type'      => array( 'wpexams_exam', 'wpexams_result' ),
			'author'         => $current_user_id,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	if ( $user_results->have_posts() ) :
		?>
		<table class='wpexams-data-table'>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'wpexams' ); ?></th>
					<th><?php esc_html_e( 'Exam', 'wpexams' ); ?></th>
					<th><?php esc_html_e( '#Questions', 'wpexams' ); ?></th>
					<th><?php esc_html_e( 'Type', 'wpexams' ); ?></th>
					<th><?php esc_html_e( 'Status', 'wpexams' ); ?></th>
					<th><?php esc_html_e( 'Score', 'wpexams' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php while ( $user_results->have_posts() ) : ?>
					<?php
					$user_results->the_post();
					$result_id = get_the_ID();
					$post_type = get_post_type( $result_id );
					
					$exam_data   = wpexams_get_post_data( $result_id );
					$exam_result = $exam_data->exam_result;
					$exam_detail = $exam_data->exam_detail;

					// Skip if no result data
					if ( ! $exam_result || ! isset( $exam_result['exam_status'] ) || ! $exam_detail ) {
						continue;
					}

					$total_questions = isset( $exam_result['total_questions'] ) ? $exam_result['total_questions'] : 0;
					$exam_status     = $exam_result['exam_status'];

					// Get exam name
					$exam_name = get_the_title( $result_id );
					
					// For result posts, get original exam name
					if ( 'wpexams_result' === $post_type ) {
						$original_exam_id = get_post_meta( $result_id, 'wpexams_exam_id', true );
						if ( $original_exam_id ) {
							$original_exam = get_post( $original_exam_id );
							if ( $original_exam ) {
								$exam_name = $original_exam->post_title;
								
								// If user-defined, show custom exam title with date
								if ( 'user_defined' === $exam_type ) {
									$exam_name = sprintf(
										/* translators: 1: exam title, 2: date */
										__( '%1$s (%2$s)', 'wpexams' ),
										$original_exam->post_title,
										get_the_date( 'Y-m-d H:i', $result_id )
									);
								}
							}
						} else {
							// Old user-defined exam result (no exam_id reference)
							$exam_name = get_the_title( $result_id );
						}
					}

					// Calculate correct answers using unique question IDs only
					$correct_count = 0;
					$correct_question_ids = array();
					
					if ( isset( $exam_result['correct_answers'] ) && is_array( $exam_result['correct_answers'] ) ) {
						foreach ( $exam_result['correct_answers'] as $answer ) {
							if ( isset( $answer['question_id'] ) && isset( $answer['answer'] ) && 'null' !== $answer['answer'] ) {
								$correct_question_ids[] = (string) $answer['question_id'];
							}
						}
					}
					
					// Remove duplicates and count
					$correct_question_ids = array_unique( $correct_question_ids );
					$correct_count = count( $correct_question_ids );
					
					// Calculate percentage
					$percentage = $total_questions > 0 ? round( ( $correct_count / $total_questions ) * 100 ) : 0;
					
					// Determine type
					$type_label = 'admin_defined' === $exam_detail['role'] ? __( 'Predefined', 'wpexams' ) : __( 'User Defined', 'wpexams' );
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Date', 'wpexams' ); ?>">
							<?php echo esc_html( get_the_date( 'Y-m-d' ) . ' ' . get_the_time( 'H:i:s' ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Exam', 'wpexams' ); ?>">
							<?php echo esc_html( $exam_name ); ?>
						</td>
						<td data-label="<?php esc_attr_e( '#Questions', 'wpexams' ); ?>">
							<?php echo esc_html( $total_questions ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Type', 'wpexams' ); ?>">
							<?php echo esc_html( $type_label ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Status', 'wpexams' ); ?>">
							<?php echo esc_html( ucfirst( $exam_status ) ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Score', 'wpexams' ); ?>">
							<?php if ( 'pending' === $exam_status ) : ?>
								<a href='?wpexams_exam_id=<?php echo esc_attr( $result_id ); ?>'>
									<?php esc_html_e( 'Continue', 'wpexams' ); ?>
								</a>
							<?php else : ?>
								<a href='?wpexams_history&wpexams_history_id=<?php echo esc_attr( $result_id ); ?>'>
									<?php
									/* translators: 1: correct answers, 2: total questions, 3: percentage */
									printf( esc_html__( '%1$d/%2$d (%3$d%%)', 'wpexams' ), $correct_count, $total_questions, $percentage );
									?>
								</a>
								<a href='?wpexams_review_id=<?php echo esc_attr( $result_id ); ?>' style="margin-left: 10px;">
									<?php esc_html_e( 'Review', 'wpexams' ); ?>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</tbody>
		</table>
	<?php else : ?>
		<div class='wpexams-m-10'>
			<p><?php esc_html_e( 'No exam history exists yet.', 'wpexams' ); ?></p>
		</div>
	<?php endif; ?>
</div>