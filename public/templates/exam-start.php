<?php
/**
 * Exam Start Template - FIXED VERSION (Issue #2)
 *
 * Display and handle exam taking interface with consistent behavior
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get exam ID from URL
$exam_id = isset( $_GET['wpexams_exam_id'] ) ? absint( $_GET['wpexams_exam_id'] ) : 0;

if ( ! $exam_id ) {
	echo '<p>' . esc_html__( 'Invalid exam ID.', 'wpexams' ) . '</p>';
	return;
}

// Get exam post
$exam_post = get_post( $exam_id );

if ( ! $exam_post ) {
	echo '<p>' . esc_html__( 'Exam not found.', 'wpexams' ) . '</p>';
	return;
}

// Verify user can take this exam
if ( ! wpexams_user_can_take_exam( $exam_id, $current_user_id ) ) {
	echo '<p>' . esc_html__( 'You do not have permission to take this exam.', 'wpexams' ) . '</p>';
	return;
}

// Get exam data
$exam_data   = wpexams_get_post_data( $exam_id );
$exam_result = $exam_data->exam_result;
$exam_detail = $exam_data->exam_detail;

if ( empty( $exam_detail ) ) {
	echo '<p>' . esc_html__( 'Exam data not found.', 'wpexams' ) . '</p>';
	return;
}

// Check if exam should be resumed or started fresh
if ( $exam_result && isset( $exam_result['solved_questions'] ) && (int) $exam_result['user_id'] === $current_user_id ) {
	// Check if it's a completed predefined exam - if so, show as new exam
	if ( isset( $exam_detail['role'] ) && 'admin_defined' === $exam_detail['role'] && isset( $exam_result['exam_status'] ) && 'completed' === $exam_result['exam_status'] ) {
		// This is a completed predefined exam, treat as new attempt
		// Clear the exam_id to force new instance creation
		unset( $_GET['wpexams_exam_id'] );
		echo '<script>window.location.href = "' . esc_url( remove_query_arg( 'wpexams_exam_id' ) ) . '";</script>';
		return;
	}
	
	// Resume pending exam (only if not admin_defined)
	if ( 'admin_defined' !== $exam_detail['role'] ) {
		wpexams_load_template( 'exam-resume', compact( 'exam_id', 'exam_post', 'exam_result', 'exam_detail', 'current_user_id', 'question_time_seconds', 'show_progressbar' ) );
		return;
	}
}

// Get question count
$question_count = isset( $exam_detail['question_count'] ) ? $exam_detail['question_count'] : count( $exam_detail['filtered_questions'] );

// Check if enough questions available
if ( empty( $exam_detail['filtered_questions'] ) ) {
	echo '<p>' . esc_html__( 'No questions available for this exam.', 'wpexams' ) . '</p>';
	return;
}

// Get first question
$first_question_id = $exam_detail['filtered_questions'][0];
$first_question    = get_post( $first_question_id );

if ( ! $first_question ) {
	echo '<p>' . esc_html__( 'Question not found.', 'wpexams' ) . '</p>';
	return;
}

// Get question fields
$question_data = wpexams_get_post_data( $first_question_id );
$question_fields = $question_data->question_fields;

// Calculate exam time for timed exams
$exam_time_seconds = 0;
if ( '1' === $exam_detail['is_timed'] ) {
	$exam_time_seconds = $question_time_seconds * $question_count;
}

// FIXED: Force show_answer_immediately to '1' for ALL exam types to ensure consistent behavior
$show_answer_immediately = isset( $exam_detail['show_answer_immediately'] ) ? $exam_detail['show_answer_immediately'] : '0';

?>

<div class='wpexams-content'>
	<div class='wpexams-exam-question-content-main'>
		
		<!-- Exam Header -->
		<div class='wpexams-text-center wpexams-bold wpexams-d-flex'>
			<h5><?php printf( esc_html__( 'Exam - %d Questions', 'wpexams' ), $question_count ); ?></h5>
			<h5 class='wpexams-d-flex'>
				<span class='wpexams-d-none wpexams-pointer wpexams-mr-15' id='wpexams_start_timer' 
					  onclick="wpexamsStartTimer('<?php echo '1' === $exam_detail['is_timed'] ? 'wpexamsTimedTimer' : 'wpexamsUntimedTimer'; ?>','wpexamsQuestionTimer')">▶</span>
				<span class='wpexams-mr-15 wpexams-pointer' id='wpexams_pause_timer' 
					  onclick="wpexamsPauseTimer('<?php echo '1' === $exam_detail['is_timed'] ? 'wpexamsTimedTimer' : 'wpexamsUntimedTimer'; ?>','wpexamsQuestionTimer')">⏸</span>
				<div id="wpexams_exam_timer"></div>
			</h5>
		</div>

		<!-- Progress Bar -->
		<?php if ( '1' === $show_progressbar && $question_count > 1 ) : ?>
			<div class='wpexams-exam-progress'>
				<p class='wpexams-m-0'>
					<span class='wpexams-question-progress-nb'>1/<?php echo esc_html( $question_count ); ?></span> 
					<?php esc_html_e( 'Questions:', 'wpexams' ); ?> 
					<span class="wpexams-percentage">0%</span>
				</p>
				<div class="wpexams-progress-container" data-percentage='0'>
					<div class="wpexams-progress wpexams-progress-bg"></div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Question Content -->
		<div class='wpexams-exam-question-content'>
			<div id='wpexams-question-head'>
				<p id='wpexams-exam-question-title'>
					<?php echo esc_html( $first_question->post_title ); ?>
					<span class='wpexams-f-right' id="wpexams_question_timer"></span>
				</p>
			</div>

			<table class='wpexams-w-100'>
				<tbody id='wpexams-questions-tbody-container' class='wpexams-questions-tbody-container'>
					<!-- Result container (hidden initially) -->
					<div class='wpexams-exam-result wpexams-hide' id='wpexams-exam-result'></div>
					
					<?php if ( ! empty( $question_fields['options'] ) ) : ?>
						<?php foreach ( $question_fields['options'] as $key => $option ) : ?>
							<tr>
								<td>
									<label for="wpexams_question_option<?php echo intval( $key ) + 1; ?>">
										<div>
											<span class='wpexams-alpha-options'><?php echo intval( $key ) + 1; ?></span>
											<input id='wpexams_question_option<?php echo intval( $key ) + 1; ?>' 
												   type="radio" 
												   name="wpexams_question_options" 
												   value="<?php echo esc_attr( $key ); ?>" />
											<?php echo esc_html( str_replace( '_', ' ', $option ) ); ?>
										</div>
									</label>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<div class='wpexams-hide wpexams-mb-20' id='wpexams-questions-explanation-immed'>
				<?php esc_html_e( 'Explanation:', 'wpexams' ); ?>
			</div>
			
			<div class='wpexams-text-right'>
				<button id='wpexamsSubmitQuestion' 
						class='wpexams-button wpexams-exam-button' 
						onclick='wpexamsSubmitAnswer("<?php echo esc_js( $first_question_id ); ?>", "<?php echo esc_js( $exam_id ); ?>")'>
					<?php esc_html_e( 'Submit', 'wpexams' ); ?>
				</button>
			</div>

			<div class='wpexams-text-center'>
				<button id='wpexamsPrevQuestion' 
						class='wpexams-button wpexams-exam-button wpexams-hide' 
						onclick="wpexamsNextQuestion('<?php echo esc_js( $first_question_id ); ?>', 'prev', '1', '<?php echo esc_js( $exam_id ); ?>')">
					<?php esc_html_e( 'Previous', 'wpexams' ); ?>
				</button>
				
				<button id='wpexamsNextQuestion' 
						class='wpexams-button wpexams-exam-button wpexams-hide' 
						onclick="wpexamsNextQuestion('<?php echo esc_js( $first_question_id ); ?>', '<?php echo $question_count === 1 ? 'show_result' : 'next'; ?>', '1', '<?php echo esc_js( $exam_id ); ?>')">
					<?php echo $question_count === 1 ? esc_html__( 'Show Result', 'wpexams' ) : esc_html__( 'Next', 'wpexams' ); ?>
				</button>
				
				<button id='wpexamsExitExam' 
						class='wpexams-button wpexams-exam-button' 
						onclick="wpexamsNextQuestion('<?php echo esc_js( $first_question_id ); ?>', 'exit', '1', '<?php echo esc_js( $exam_id ); ?>')">
					<?php esc_html_e( 'Exit', 'wpexams' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	// Initialize timers
	<?php if ( '1' === $exam_detail['is_timed'] ) : ?>
		var examTime = <?php echo absint( $exam_time_seconds ); ?>;
		var examHms = wpexamsConvertSecondsToHms(examTime);
		wpexamsTimedQuizCountdownTimer(parseInt(examHms.hrs), parseInt(examHms.min), parseInt(examHms.sec), "wpexamsTimedTimer");
	<?php else : ?>
		wpexamsUntimedQuizCountdownTimer(0, 0, 0, "wpexamsUntimedTimer");
	<?php endif; ?>
	
	// Initialize question timer
	wpexamsQuestionCountdownTimer(0, 0, 0, "wpexamsQuestionTimer");
});
</script>