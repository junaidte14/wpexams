<?php
/**
 * Result detail metabox
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register result detail metabox
 */
function wpexams_register_result_metabox() {
	add_meta_box(
		'wpexams_result_detail_box',
		__( 'Exam Results Detail', 'wpexams' ),
		'wpexams_render_result_metabox',
		'wpexams_result',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'wpexams_register_result_metabox' );

/**
 * Render result detail metabox
 *
 * @param WP_Post $post Post object.
 */
function wpexams_render_result_metabox( $post ) {
	// Get result data
	$result_data = wpexams_get_post_data( $post->ID );
	$exam_result = $result_data->exam_result;
	$exam_detail = $result_data->exam_detail;

	if ( ! $exam_result || ! $exam_detail ) {
		echo '<p>' . esc_html__( 'No result data available.', 'wpexams' ) . '</p>';
		return;
	}

	// Get exam info
	$exam_id = get_post_meta( $post->ID, 'wpexams_exam_id', true );
	$exam_type = isset( $exam_detail['role'] ) ? $exam_detail['role'] : 'unknown';
	$exam_name = '';
	
	if ( $exam_id && 'admin_defined' === $exam_type ) {
		$exam_post = get_post( $exam_id );
		$exam_name = $exam_post ? $exam_post->post_title : __( 'N/A', 'wpexams' );
	} else {
		$exam_name = __( 'User Defined Exam', 'wpexams' );
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
	
	$correct_question_ids = array_unique( $correct_question_ids );
	$correct_count = count( $correct_question_ids );
	
	$total_questions = isset( $exam_result['total_questions'] ) ? $exam_result['total_questions'] : 0;
	$percentage = $total_questions > 0 ? round( ( $correct_count / $total_questions ) * 100 ) : 0;
	$exam_status = isset( $exam_result['exam_status'] ) ? $exam_result['exam_status'] : 'pending';
	$exam_time = isset( $exam_result['exam_time'] ) ? $exam_result['exam_time'] : '00:00:00';
	$is_timed = isset( $exam_detail['is_timed'] ) && '1' === $exam_detail['is_timed'];
	
	?>
	<style>
		.wpexams-result-summary {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 15px;
			margin-bottom: 20px;
			padding: 15px;
			background: #f8f9fa;
			border-radius: 6px;
		}
		.wpexams-result-stat {
			padding: 10px;
			background: white;
			border-radius: 4px;
			border-left: 3px solid #2271b1;
		}
		.wpexams-result-stat strong {
			display: block;
			color: #666;
			font-size: 12px;
			margin-bottom: 5px;
		}
		.wpexams-result-stat span {
			font-size: 18px;
			font-weight: 600;
			color: #333;
		}
		.wpexams-question-review {
			margin: 15px 0;
			padding: 15px;
			background: white;
			border: 1px solid #ddd;
			border-radius: 6px;
		}
		.wpexams-question-review h4 {
			margin: 0 0 10px 0;
			color: #333;
		}
		.wpexams-answer-option {
			padding: 10px;
			margin: 5px 0;
			border: 1px solid #ddd;
			border-radius: 4px;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.wpexams-answer-option.correct {
			background: #e8f5e9;
			border-color: #4caf50;
		}
		.wpexams-answer-option.wrong {
			background: #ffebee;
			border-color: #f44336;
		}
		.wpexams-answer-option.user-selected {
			border-width: 2px;
			font-weight: 600;
		}
		.wpexams-answer-badge {
			padding: 4px 8px;
			border-radius: 3px;
			font-size: 11px;
			font-weight: 600;
			text-transform: uppercase;
		}
		.wpexams-answer-badge.correct {
			background: #4caf50;
			color: white;
		}
		.wpexams-answer-badge.wrong {
			background: #f44336;
			color: white;
		}
		.wpexams-answer-badge.user {
			background: #2271b1;
			color: white;
		}
		.wpexams-explanation {
			margin-top: 10px;
			padding: 10px;
			background: #f8f9fa;
			border-left: 3px solid #2271b1;
			font-style: italic;
		}
	</style>

	<!-- Summary Stats -->
	<div class="wpexams-result-summary">
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Exam', 'wpexams' ); ?></strong>
			<span><?php echo esc_html( $exam_name ); ?></span>
		</div>
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Type', 'wpexams' ); ?></strong>
			<span><?php echo 'admin_defined' === $exam_type ? esc_html__( 'Predefined', 'wpexams' ) : esc_html__( 'User Defined', 'wpexams' ); ?></span>
		</div>
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Score', 'wpexams' ); ?></strong>
			<span style="color: <?php echo $percentage >= 50 ? '#4caf50' : '#f44336'; ?>;">
				<?php echo esc_html( $correct_count . '/' . $total_questions . ' (' . $percentage . '%)' ); ?>
			</span>
		</div>
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Status', 'wpexams' ); ?></strong>
			<span><?php echo esc_html( ucfirst( $exam_status ) ); ?></span>
		</div>
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Time', 'wpexams' ); ?></strong>
			<span><?php echo 'expired' === $exam_time ? esc_html__( 'Expired', 'wpexams' ) : esc_html( $exam_time ); ?></span>
		</div>
		<div class="wpexams-result-stat">
			<strong><?php esc_html_e( 'Timed', 'wpexams' ); ?></strong>
			<span><?php echo $is_timed ? esc_html__( 'Yes', 'wpexams' ) : esc_html__( 'No', 'wpexams' ); ?></span>
		</div>
	</div>

	<!-- Questions and Answers -->
	<h3><?php esc_html_e( 'Questions & Answers', 'wpexams' ); ?></h3>
	
	<?php
	if ( isset( $exam_result['filtered_questions'] ) && ! empty( $exam_result['filtered_questions'] ) ) :
		foreach ( $exam_result['filtered_questions'] as $index => $question_id ) :
			$question_data = wpexams_get_post_data( $question_id );
			$question_fields = $question_data->question_fields;
			
			if ( ! $question_fields ) {
				continue;
			}

			// Get user's answer
			$user_answer = wpexams_get_user_answer( $exam_result, $question_id );
			
			// Get question time
			$question_time = wpexams_get_question_time( $exam_result, $question_id );
			
			// Get correct option
			$correct_option = $question_fields['correct_option'];
			
			?>
			<div class="wpexams-question-review">
				<h4>
					<?php echo esc_html( ( $index + 1 ) . '. ' . get_the_title( $question_id ) ); ?>
					<small style="float: right; color: #666; font-weight: normal;">
						<?php echo esc_html( $question_time ); ?>
					</small>
				</h4>
				
				<?php foreach ( $question_fields['options'] as $key => $option ) : ?>
					<?php
					$is_correct = ( 'wpexams_c_option_' . $key === $correct_option );
					$is_user_answer = ( $key == $user_answer );
					$option_class = '';
					
					if ( $is_correct ) {
						$option_class .= ' correct';
					}
					if ( $is_user_answer ) {
						$option_class .= ' user-selected';
						if ( ! $is_correct ) {
							$option_class .= ' wrong';
						}
					}
					?>
					<div class="wpexams-answer-option<?php echo esc_attr( $option_class ); ?>">
						<span><?php echo esc_html( ( $key + 1 ) . '. ' . str_replace( '_', ' ', $option ) ); ?></span>
						<?php if ( $is_correct ) : ?>
							<span class="wpexams-answer-badge correct"><?php esc_html_e( 'Correct', 'wpexams' ); ?></span>
						<?php endif; ?>
						<?php if ( $is_user_answer ) : ?>
							<span class="wpexams-answer-badge user"><?php esc_html_e( 'User Answer', 'wpexams' ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				
				<?php if ( ! empty( $question_fields['description'] ) ) : ?>
					<div class="wpexams-explanation">
						<strong><?php esc_html_e( 'Explanation:', 'wpexams' ); ?></strong>
						<?php echo esc_html( $question_fields['description'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No questions data available.', 'wpexams' ); ?></p>
	<?php endif; ?>
	<?php
}