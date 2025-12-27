<?php
/**
 * Exam Resume Template
 *
 * Resume a pending exam from where user left off
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get question count
$question_count = isset( $exam_result['total_questions'] ) ? $exam_result['total_questions'] : count( $exam_detail['filtered_questions'] );

// Determine which question to resume from
if ( isset( $exam_result['exit_question'] ) ) {
	$current_index = array_search( (int) $exam_result['exit_question'], $exam_result['filtered_questions'], true );
	$resume_question_id = $exam_result['exit_question'];
} else {
	$last_solved = end( $exam_result['solved_questions'] );
	$current_index = array_search( (int) $last_solved, $exam_result['filtered_questions'], true );
	$next_index = $current_index + 1;
	$resume_question_id = isset( $exam_result['filtered_questions'][ $next_index ] ) ? $exam_result['filtered_questions'][ $next_index ] : $last_solved;
}

// Get question data
$question_post = get_post( $resume_question_id );
if ( ! $question_post ) {
	echo '<p>' . esc_html__( 'Question not found.', 'wpexams' ) . '</p>';
	return;
}

$question_data = wpexams_get_post_data( $resume_question_id );
$question_fields = $question_data->question_fields;

$exam_time_seconds = 0;
$is_timed = isset( $exam_detail['is_timed'] ) && '1' === $exam_detail['is_timed'];
if ( $is_timed ) {
	$exam_time_seconds = $question_time_seconds * $question_count;
}
// Calculate progress
$progress_percent = round( ( ( $current_index + 1 ) / count( $exam_result['filtered_questions'] ) ) * 100 );

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
				<div id="wpexams_exam_timer">
					<?php 
					$saved_exam_time = isset( $exam_result['exam_time'] ) ? $exam_result['exam_time'] : '00:00:00';
					echo esc_html( $saved_exam_time );
					?>
				</div>
			</h5>
		</div>

		<!-- Progress Bar -->
		<?php if ( '1' === $show_progressbar && $question_count > 1 ) : ?>
			<div class='wpexams-exam-progress'>
				<p class='wpexams-m-0'>
					<span class='wpexams-question-progress-nb'><?php echo ( $current_index + 1 ); ?>/<?php echo esc_html( $question_count ); ?></span> 
					<?php esc_html_e( 'Questions:', 'wpexams' ); ?> 
					<span class="wpexams-percentage"><?php echo esc_html( $progress_percent ); ?>%</span>
				</p>
				<div class="wpexams-progress-container" data-percentage='<?php echo esc_attr( $progress_percent ); ?>'>
					<div class="wpexams-progress wpexams-progress-bg" style="width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>
				</div>
			</div>
		<?php endif; ?>

		<!-- Question Content -->
		<div class='wpexams-exam-question-content'>
			<div id='wpexams-question-head'>
				<p id='wpexams-exam-question-title'>
					<?php echo esc_html( $question_post->post_title ); ?>
					<span class='wpexams-f-right' id="wpexams_question_timer"></span>
				</p>
			</div>

			<form action="javascript:void(0)">
				<table class='wpexams-w-100'>
					<tbody id='wpexams-questions-tbody-container' class='wpexams-questions-tbody-container'>
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
					<button id='wpexamsSubmitQuestion' class='wpexams-button wpexams-exam-button' 
							onclick='wpexamsSubmitAnswer("<?php echo esc_js( $resume_question_id ); ?>", "<?php echo esc_js( $exam_id ); ?>")'>
						<?php esc_html_e( 'Submit', 'wpexams' ); ?>
					</button>
				</div>

				<div class='wpexams-text-center'>
					<?php
					$is_first = ( 0 === $current_index );
					$is_last = ( $current_index === count( $exam_result['filtered_questions'] ) - 1 );
					?>
					<button id="wpexamsPrevQuestion" 
							class='wpexams-button wpexams-exam-button <?php echo $is_first ? 'wpexams-hide' : ''; ?>' 
							data-question="<?php echo esc_attr( $resume_question_id ); ?>" 
							data-action="prev" 
							data-exam="<?php echo esc_attr( $exam_id ); ?>">
						<?php esc_html_e( 'Previous', 'wpexams' ); ?>
					</button>
					<button id="wpexamsNextQuestion" 
							class='wpexams-button wpexams-exam-button wpexams-hide' 
							data-question="<?php echo esc_attr( $resume_question_id ); ?>" 
							data-action="<?php echo $is_last ? 'show_result' : 'next'; ?>" 
							data-exam="<?php echo esc_attr( $exam_id ); ?>">
						<?php echo $is_last ? esc_html__( 'Show Result', 'wpexams' ) : esc_html__( 'Next', 'wpexams' ); ?>
					</button>
					<button id='wpexamsExitExam' 
							class='wpexams-button wpexams-exam-button' 
							data-question="<?php echo esc_attr( $resume_question_id ); ?>" 
							data-action="exit" 
							data-exam="<?php echo esc_attr( $exam_id ); ?>">
						<?php esc_html_e( 'Exit', 'wpexams' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
    <?php if ( $is_timed ) : ?>
        var savedTime = "<?php echo esc_js( $saved_exam_time ); ?>";
        var parts = savedTime.split(':');
        if (parts.length === 3) {
            wpexamsTimedQuizCountdownTimer(parseInt(parts[0], 10), parseInt(parts[1], 10), parseInt(parts[2], 10), "wpexamsTimedTimer");
        }
    <?php else : ?>
        var savedTime = "<?php echo esc_js( $saved_exam_time ); ?>";
        var parts = savedTime.split(':');
        if (parts.length === 3) {
            wpexamsUntimedQuizCountdownTimer(parseInt(parts[0], 10), parseInt(parts[1], 10), parseInt(parts[2], 10), "wpexamsUntimedTimer");
        }
    <?php endif; ?>
    
    wpexamsQuestionCountdownTimer(0, 0, 0, "wpexamsQuestionTimer");
});
</script>