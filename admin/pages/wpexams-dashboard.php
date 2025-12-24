<?php
/**
 * Admin dashboard page
 *
 * @package WPExams
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render dashboard page
 */
function wpexams_dashboard_page() {
    // Check user capability
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wpexams'));
    }

    // Get current tab
    $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

    ?>
    <div class="wrap wpexams-dashboard-wrap">
        <h1><?php esc_html_e('WP Exams Dashboard', 'wpexams'); ?></h1>

        <?php wpexams_render_migration_notice(); ?>

        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpexams&tab=general')); ?>" 
               class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('General', 'wpexams'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpexams&tab=colors')); ?>" 
               class="nav-tab <?php echo 'colors' === $active_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Colors', 'wpexams'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpexams&tab=about')); ?>" 
               class="nav-tab <?php echo 'about' === $active_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('About', 'wpexams'); ?>
            </a>
        </h2>

        <div class="wpexams-dashboard-content">
            <?php
            switch ($active_tab) {
                case 'general':
                    wpexams_settings_general_page();
                    break;
                case 'colors':
                    wpexams_settings_colors_page();
                    break;
                case 'about':
                    wpexams_settings_about_page();
                    break;
                default:
                    wpexams_settings_general_page();
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Render migration notice
 */
function wpexams_render_migration_notice() {
    // Check if migration is needed
    if (!wpexams_needs_migration()) {
        return;
    }

    $stats = wpexams_get_migration_stats();
    $nonce = wp_create_nonce('wpexams_migration');
    ?>
    <div class="notice notice-warning wpexams-migration-notice" id="wpexams-migration-notice">
        <div class="wpexams-migration-container">
            <div class="wpexams-migration-header">
                <span class="dashicons dashicons-database-import"></span>
                <h2><?php esc_html_e('Database Migration Required', 'wpexams'); ?></h2>
            </div>

            <div class="wpexams-migration-content">
                <p class="wpexams-migration-description">
                    <?php esc_html_e('We detected data from a previous quiz/exam plugin that needs to be migrated to WP Exams. This is a one-time process.', 'wpexams'); ?>
                </p>

                <div class="wpexams-migration-stats">
                    <div class="wpexams-stat-item">
                        <span class="wpexams-stat-icon dashicons dashicons-list-view"></span>
                        <div class="wpexams-stat-content">
                            <strong><?php echo esc_html($stats['questions']); ?></strong>
                            <span><?php esc_html_e('Questions', 'wpexams'); ?></span>
                        </div>
                    </div>
                    <div class="wpexams-stat-item">
                        <span class="wpexams-stat-icon dashicons dashicons-welcome-learn-more"></span>
                        <div class="wpexams-stat-content">
                            <strong><?php echo esc_html($stats['exams']); ?></strong>
                            <span><?php esc_html_e('Exams', 'wpexams'); ?></span>
                        </div>
                    </div>
                    <div class="wpexams-stat-item">
                        <span class="wpexams-stat-icon dashicons dashicons-chart-line"></span>
                        <div class="wpexams-stat-content">
                            <strong><?php echo esc_html($stats['results']); ?></strong>
                            <span><?php esc_html_e('Results', 'wpexams'); ?></span>
                        </div>
                    </div>
                    <div class="wpexams-stat-item">
                        <span class="wpexams-stat-icon dashicons dashicons-admin-settings"></span>
                        <div class="wpexams-stat-content">
                            <strong><?php echo esc_html($stats['settings']); ?></strong>
                            <span><?php esc_html_e('Settings', 'wpexams'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="wpexams-migration-info">
                    <p>
                        <span class="dashicons dashicons-info"></span>
                        <strong><?php esc_html_e('Important:', 'wpexams'); ?></strong>
                        <?php esc_html_e('Please backup your database before running the migration. This process will update your existing data.', 'wpexams'); ?>
                    </p>
                </div>

                <div class="wpexams-migration-actions">
                    <button type="button" class="button button-primary button-large" id="wpexams-run-migration" data-nonce="<?php echo esc_attr($nonce); ?>">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e('Run Migration Now', 'wpexams'); ?>
                    </button>
                    <button type="button" class="button button-link" id="wpexams-dismiss-migration">
                        <?php esc_html_e('Remind Me Later', 'wpexams'); ?>
                    </button>
                </div>

                <div class="wpexams-migration-progress" style="display: none;">
                    <div class="wpexams-progress-bar">
                        <div class="wpexams-progress-fill"></div>
                    </div>
                    <p class="wpexams-progress-text"><?php esc_html_e('Migration in progress...', 'wpexams'); ?></p>
                </div>

                <div class="wpexams-migration-result" style="display: none;"></div>
            </div>
        </div>
    </div>

    <style>
        .wpexams-migration-notice {
            border-left: 4px solid #f39c12;
            padding: 20px;
            margin: 20px 0;
            background: #fff;
        }

        .wpexams-migration-container {
            max-width: 900px;
        }

        .wpexams-migration-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .wpexams-migration-header .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
            color: #f39c12;
        }

        .wpexams-migration-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .wpexams-migration-description {
            font-size: 14px;
            margin-bottom: 20px;
            color: #555;
        }

        .wpexams-migration-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .wpexams-stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }

        .wpexams-stat-icon {
            font-size: 28px;
            width: 28px;
            height: 28px;
            color: #2271b1;
        }

        .wpexams-stat-content {
            display: flex;
            flex-direction: column;
        }

        .wpexams-stat-content strong {
            font-size: 24px;
            color: #2271b1;
            line-height: 1;
        }

        .wpexams-stat-content span {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .wpexams-migration-info {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .wpexams-migration-info p {
            margin: 0;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
        }

        .wpexams-migration-info .dashicons {
            color: #4caf50;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .wpexams-migration-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        #wpexams-run-migration {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        #wpexams-run-migration .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
        }

        #wpexams-run-migration:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .wpexams-migration-progress {
            margin-top: 20px;
        }

        .wpexams-progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .wpexams-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2271b1, #4299e1);
            width: 0%;
            transition: width 0.3s ease;
            position: relative;
        }

        .wpexams-progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .wpexams-progress-text {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .wpexams-migration-result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 6px;
            font-size: 14px;
        }

        .wpexams-migration-result.success {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }

        .wpexams-migration-result.error {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
        }

        .wpexams-migration-result h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }

        .wpexams-migration-result ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .wpexams-migration-result li {
            margin: 5px 0;
        }

        @media (max-width: 768px) {
            .wpexams-migration-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .wpexams-migration-actions {
                flex-direction: column;
                align-items: stretch;
            }

            #wpexams-run-migration,
            #wpexams-dismiss-migration {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        jQuery(document).ready(function($) {
            // Run migration
            $('#wpexams-run-migration').on('click', function() {
                var $button = $(this);
                var $notice = $('#wpexams-migration-notice');
                var $progress = $('.wpexams-migration-progress');
                var $result = $('.wpexams-migration-result');
                var nonce = $button.data('nonce');

                // Confirm before proceeding
                if (!confirm('<?php esc_html_e('Are you sure you want to run the migration? Please ensure you have backed up your database.', 'wpexams'); ?>')) {
                    return;
                }

                // Disable button and show progress
                $button.prop('disabled', true);
                $progress.show();
                $result.hide();

                // Animate progress bar
                var progress = 0;
                var progressInterval = setInterval(function() {
                    progress += 5;
                    if (progress <= 90) {
                        $('.wpexams-progress-fill').css('width', progress + '%');
                    }
                }, 200);

                // Run migration
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpexams_run_migration',
                        nonce: nonce
                    },
                    success: function(response) {
                        clearInterval(progressInterval);
                        $('.wpexams-progress-fill').css('width', '100%');

                        setTimeout(function() {
                            $progress.hide();

                            if (response.success) {
                                var html = '<h3><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</h3>';
                                html += '<ul>';
                                html += '<li><?php esc_html_e('Questions migrated:', 'wpexams'); ?> ' + response.data.results.questions + '</li>';
                                html += '<li><?php esc_html_e('Exams migrated:', 'wpexams'); ?> ' + response.data.results.exams + '</li>';
                                html += '<li><?php esc_html_e('Meta keys updated:', 'wpexams'); ?> ' + response.data.results.meta_keys + '</li>';
                                html += '<li><?php esc_html_e('Settings migrated:', 'wpexams'); ?> ' + response.data.results.settings + '</li>';
                                html += '</ul>';
                                html += '<p><?php esc_html_e('You can now safely delete the old plugin if it is still installed.', 'wpexams'); ?></p>';

                                $result.addClass('success').html(html).show();

                                // Hide the entire notice after 5 seconds
                                setTimeout(function() {
                                    $notice.fadeOut(function() {
                                        $(this).remove();
                                    });
                                }, 5000);
                            } else {
                                var html = '<h3><span class="dashicons dashicons-warning"></span> ' + response.data.message + '</h3>';
                                html += '<p><?php esc_html_e('Please contact support if the problem persists.', 'wpexams'); ?></p>';

                                $result.addClass('error').html(html).show();
                                $button.prop('disabled', false);
                            }
                        }, 500);
                    },
                    error: function() {
                        clearInterval(progressInterval);
                        $progress.hide();

                        var html = '<h3><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Migration failed', 'wpexams'); ?></h3>';
                        html += '<p><?php esc_html_e('An error occurred while running the migration. Please try again or contact support.', 'wpexams'); ?></p>';

                        $result.addClass('error').html(html).show();
                        $button.prop('disabled', false);
                    }
                });
            });

            // Dismiss migration notice
            $('#wpexams-dismiss-migration').on('click', function() {
                if (confirm('<?php esc_html_e('The migration notice will be hidden temporarily. It will appear again when you reload the page.', 'wpexams'); ?>')) {
                    $('#wpexams-migration-notice').fadeOut();
                }
            });
        });
    </script>
    <?php
}