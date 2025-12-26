<?php
/**
 * Exam Review Template - FIXED VERSION (Issue #1)
 *
 * Navigate through completed exam questions with explanations
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get exam ID
$exam_id = isset( $_GET['wpexams_review_id'] ) ? absint( $_GET['wpexams_review_id'] ) : 0;

if ( ! $exam_id ) {
	echo '<p>' . esc_html__( 'Invalid exam ID.', 'wpexams' ) . '</p>';
	return;
}

// Get exam data
$exam_data   = wpexams_get_post_data( $exam_id );
$exam_result = $exam_data->exam_result;
$exam_detail = $exam_data->exam_detail;

// FIXED: Better validation - check if result exists and has required data
if ( ! $exam_result || ! $exam_detail ) {
	echo '<p>' . esc_html__( 'Exam not found.', 'wpexams' ) . '</p>';
	return;
}

// FIXED: Check if exam_status exists before comparing
if ( ! isset( $exam_result['exam_status'] ) ) {
	echo '<p>' . esc_html__( 'Exam has not been started yet.', 'wpexams' ) . '</p>';
	return;
}

// Check if exam is completed
if ( 'completed' !== $exam_result['exam_status'] ) {
	echo '<p>' . esc_html__( 'Exam not completed yet.', 'wpexams' ) . '</p>';
	return;
}

// Get first question
$first_question_id = $exam_result['filtered_questions'][0];
$question_data     = wpexams_get_post_data( $first_question_id );
$question_fields   = $question_data->question_fields;

// Get user's answer and time
$user_answer   = wpexams_get_user_answer( $exam_result, $first_question_id );
$question_time = wpexams_get_question_time( $exam_result, $first_question_id );

// Calculate score
$correct_count = 0;
if ( isset( $exam_result['correct_answers'] ) ) {
	foreach ( $exam_result['correct_answers'] as $answer ) {
		if ( 'null' !== $answer['answer'] ) {
			$correct_count++;
		}
	}
}
$total_questions = $exam_result['total_questions'];

?>

<div class="wpexams-content">
	<div class='wpexams-d-flex wpexams-m-tb-20'>
		<h5 class='wpexams-m-0'>
			<?php
			/* translators: 1: correct answers, 2: total questions */
			printf( esc_html__( 'Score %1$d/%2$d', 'wpexams' ), $correct_count, $total_questions );
			?>
		</h5>
		<?php if ( isset( $exam_result['exam_time'] ) && 'expired' !== $exam_result['exam_time'] ) : ?>
			<span><?php echo esc_html( $exam_result['exam_time'] ); ?></span>
		<?php endif; ?>
	</div>

	<div class='wpexams-exam-question-content'>
		<div id='wpexams-question-head'>
			<p id='wpexams-exam-question-title'>
				<?php echo esc_html( get_the_title( $first_question_id ) ); ?>
				<span class='wpexams-f-right'><?php echo esc_html( $question_time ); ?></span>
			</p>
		</div>

		<table class='wpexams-w-100'>
			<tbody id='wpexams-questions-tbody-container' class='wpexams-questions-tbody-container'>
				<?php foreach ( $question_fields['options'] as $key => $option ) : ?>
					<?php
					$is_correct  = ( 'wpexams_c_option_' . $key === $question_fields['correct_option'] );
					$is_selected = ( $key == $user_answer );
					?>
					<tr class='wpexams-question-field-show-immed <?php echo $is_selected ? 'wpexams-subscriber-answer-sl' : ''; ?>'>
						<td>
							<label for="">
								<div>
									<span class='wpexams-alpha-options <?php echo $is_correct ? 'wpexams-green' : 'wpexams-red'; ?>'>
										<?php echo intval( $key ) + 1; ?>
									</span>
									<span style="flex-grow:1;">
										<?php echo esc_html( str_replace( '_', ' ', $option ) ); ?>
									</span>
									<?php if ( $is_correct ) : ?>
										<span class="wpexams-immed-answer-is-true">✓</span>
									<?php else : ?>
										<span class="wpexams-immed-answer-is-false">✗</span>
									<?php endif; ?>
								</div>
							</label>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class='wpexams-mb-20' id='wpexams-questions-explanation-immed'>
			<strong><?php esc_html_e( 'Explanation:', 'wpexams' ); ?></strong>
			<?php echo esc_html( $question_fields['description'] ); ?>
		</div>

		<div class='wpexams-text-center'>
			<?php if ( count( $exam_result['filtered_questions'] ) > 1 ) : ?>
				<button id='wpexamsPrevQuestion' class='wpexams-button wpexams-exam-button wpexams-hide' 
						onclick="wpexamsReviewQuestion('<?php echo esc_js( $first_question_id ); ?>', 'prev', '<?php echo esc_js( $exam_id ); ?>')">
					<?php esc_html_e( 'Previous', 'wpexams' ); ?>
				</button>
				<button id='wpexamsNextQuestion' class='wpexams-button wpexams-exam-button' 
						onclick="wpexamsReviewQuestion('<?php echo esc_js( $first_question_id ); ?>', 'next', '<?php echo esc_js( $exam_id ); ?>')">
					<?php esc_html_e( 'Next', 'wpexams' ); ?>
				</button>
			<?php endif; ?>
			<button id='wpexamsExitExam' class='wpexams-button wpexams-exam-button' 
					onclick="wpexamsExitExam()">
				<?php esc_html_e( 'Exit', 'wpexams' ); ?>
			</button>
		</div>
	</div>
</div>