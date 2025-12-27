<?php
/**
 * Exam List Predefined Template
 *
 * Display list of predefined exams and option to create custom exam
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get admin IDs
$admin_ids = wpexams_get_admin_ids();

// Get all admin-created exams
$predefined_exams = get_posts(
	array(
		'post_type'      => 'wpexams_exam',
		'author__in'     => $admin_ids,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'ID',
		'order'          => 'DESC',
	)
);

?>

<div class="wpexams-content">
	<div style="display: flex;align-items: center;margin-bottom: 20px;">
		<p style="margin:0px;flex-grow:1;">
			<?php esc_html_e( 'Explore our collection of predefined exams and test your knowledge!', 'wpexams' ); ?>
		</p>
	</div>

	<table class='wpexams-data-table'>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Title', 'wpexams' ); ?></th>
				<th><?php esc_html_e( 'Questions', 'wpexams' ); ?></th>
				<th><?php esc_html_e( 'Timed', 'wpexams' ); ?></th>
				<th><?php esc_html_e( 'Action', 'wpexams' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $predefined_exams ) ) : ?>
				<?php $has_exam = false; ?>
				<?php foreach ( $predefined_exams as $exam ) : ?>
					<?php
					$exam_data = wpexams_get_post_data( $exam->ID );
					$exam_detail = $exam_data->exam_detail;

					if ( ! $exam_detail || ! isset( $exam_detail['role'] ) || 'admin_defined' !== $exam_detail['role'] ) {
						continue;
					}

					$has_exam = true;
					$question_count = isset( $exam_detail['question_ids'] ) ? count( $exam_detail['question_ids'] ) : 0;
					$is_timed = isset( $exam_detail['is_timed'] ) && '1' === $exam_detail['is_timed'];
					?>
					<tr>
						<td data-label="<?php esc_attr_e( 'Title', 'wpexams' ); ?>">
							<?php echo ! empty( $exam->post_title ) ? esc_html( $exam->post_title ) : esc_html__( 'No Title', 'wpexams' ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Questions', 'wpexams' ); ?>">
							<?php echo esc_html( $question_count ); ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Timed', 'wpexams' ); ?>">
							<?php echo $is_timed ? '✓' : '✗'; ?>
						</td>
						<td data-label="<?php esc_attr_e( 'Action', 'wpexams' ); ?>">
							<a href="?wpexams_exam_id=<?php echo esc_attr( $exam->ID ); ?>">
								<?php esc_html_e( 'Take Exam', 'wpexams' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if ( ! $has_exam ) : ?>
					<tr>
						<td colspan="4" style="text-align: center;">
							<?php esc_html_e( 'No predefined exams available', 'wpexams' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="text-align: center;">
						<?php esc_html_e( 'No predefined exams available', 'wpexams' ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>