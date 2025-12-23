<?php
	$active_tab = isset( $_GET[ 'tab' ] ) ? esc_html($_GET[ 'tab' ]) : 'general';
	$page_action = isset( $_GET[ 'page_action' ] ) ? esc_html($_GET[ 'page_action' ]) : '';
?>

<h2 class="nav-tab-wrapper">
    <a href="<?php echo esc_url('?page=wp_exams&tab=general');?>" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
    <a href="<?php echo esc_url('?page=wp_exams&tab=color');?>" class="nav-tab <?php echo $active_tab == 'color' ? 'nav-tab-active' : ''; ?>">Color</a>
    <a href="<?php echo esc_url('?page=wp_exams&tab=about');?>" class="nav-tab <?php echo $active_tab == 'about' ? 'nav-tab-active' : ''; ?>">About</a>
</h2>

<?php         
    if($active_tab == 'general') {
	    include( QUESTIONS_BANK_PLUGIN_PATH . '/plugin_pages/includes/admin/general.php');
    }
    elseif($active_tab == 'color'){
        include( QUESTIONS_BANK_PLUGIN_PATH . '/plugin_pages/includes/admin/colors.php');
    }
    elseif($active_tab == 'about'){ 
        include( QUESTIONS_BANK_PLUGIN_PATH . '/plugin_pages/includes/admin/about.php');
    }
     
?>