<?php
/**
 * About page
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render about page
 */
function wpexams_settings_about_page() {
	?>
	<h2><?php esc_html_e( 'About WP Exams', 'wpexams' ); ?></h2>

	<div class="wpexams-dashboard-about">
		<div class="wpexams-about-section">
			<h3><?php esc_html_e( 'Shortcode', 'wpexams' ); ?></h3>
			<p><?php esc_html_e( 'You can display the exam interface with the following shortcode in any post or page:', 'wpexams' ); ?></p>
			<pre><code>[wpexams]</code></pre>
		</div>

		<div class="wpexams-about-section">
			<h3><?php esc_html_e( 'Dashboard Sections', 'wpexams' ); ?></h3>
			<ul>
				<li>
					<strong><?php esc_html_e( 'Dashboard', 'wpexams' ); ?>:</strong>
					<?php esc_html_e( 'Manage general settings, customize colors, and view plugin information.', 'wpexams' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Questions', 'wpexams' ); ?>:</strong>
					<?php esc_html_e( 'Create and manage multiple-choice questions with explanations.', 'wpexams' ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Exams', 'wpexams' ); ?>:</strong>
					<?php esc_html_e( 'Build exams by selecting questions, set time limits, and configure display options.', 'wpexams' ); ?>
				</li>
			</ul>
		</div>

		<div class="wpexams-about-section">
			<h3><?php esc_html_e( 'Features', 'wpexams' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Create unlimited questions with 2-4 answer options', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Organize questions using WordPress categories', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Build custom exams with selected questions', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Create predefined exams for all users', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Timed and untimed exam options', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Show answers immediately or at the end', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Track exam history and scores', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Review completed exams with explanations', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Filter unused questions for new exams', 'wpexams' ); ?></li>
				<li><?php esc_html_e( 'Customizable colors for buttons and progress bar', 'wpexams' ); ?></li>
			</ul>
		</div>

		<div class="wpexams-about-section">
			<h3><?php esc_html_e( 'Plugin Information', 'wpexams' ); ?></h3>
			<p>
				<strong><?php esc_html_e( 'Version:', 'wpexams' ); ?></strong> 
				<?php echo esc_html( WPEXAMS_VERSION ); ?>
			</p>
			<p>
				<strong><?php esc_html_e( 'Author:', 'wpexams' ); ?></strong> 
				<?php esc_html_e( 'WP Exams Team', 'wpexams' ); ?>
			</p>
		</div>

		<div class="wpexams-about-section">
			<h3><?php esc_html_e( 'Support', 'wpexams' ); ?></h3>
			<p><?php esc_html_e( 'For support, documentation, and updates, visit:', 'wpexams' ); ?></p>
			<p><a href="https://wpexams.com" target="_blank">https://wpexams.com</a></p>
		</div>
	</div>

	<style>
		.wpexams-dashboard-about {
			max-width: 800px;
		}
		.wpexams-about-section {
			background: #fff;
			border: 1px solid #ccd0d4;
			padding: 20px;
			margin: 20px 0;
			box-shadow: 0 1px 1px rgba(0,0,0,.04);
		}
		.wpexams-about-section h3 {
			margin-top: 0;
		}
		.wpexams-about-section pre {
			background: #f6f7f7;
			padding: 15px;
			border-left: 4px solid #2271b1;
		}
		.wpexams-about-section ul {
			list-style-type: disc;
			padding-left: 20px;
		}
		.wpexams-about-section ul li {
			margin-bottom: 8px;
		}
	</style>
	<?php
}