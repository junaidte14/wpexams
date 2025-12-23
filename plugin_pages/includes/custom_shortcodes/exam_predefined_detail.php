
<?php
include_once( QUESTIONS_BANK_PLUGIN_PATH.'plugin_pages/includes/common/get_all_admins.php');

// GET ALL ADMINS
$qb_AdminIdArray = qb_admin_user_ids();

$allExams = get_posts(array(
    'post_type' => 'qb_exams' ,
    'author__in' => $qb_AdminIdArray ,
    'posts_per_page' => '-1' ,
    'post_status' => array('publish', 'Predefined') ,
    'orderby' => 'ID',
    'order' => 'DESC'
));

?>
<table class='qb_data_tabels'>
    <thead>
        <tr>
           <th>Title</th>
           <th>Questions</th>
           <th>Timed</th>
           <th>Action</th>
       </tr>
    </thead>
    <tbody>
    <?php 
    if (!empty($allExams)) {
        $hasExam = false;
        foreach ($allExams as $exam) {
            
            // GET POST META => get_post_metas.php
            $qb_get_post_meta = new QB_Get_Post_Meta($exam->ID);
            $qb_detail = $qb_get_post_meta->qb_detail;

            if ($qb_detail && array_key_exists('qb_role', $qb_detail) && $qb_detail['qb_role'] == 'admin_defined') {
                $hasExam = true; 
                ?>   
                <tr> 
                    <td data-label="Title"><?php echo empty($exam->post_title) ? "No Title" : substr($exam->post_title, 0, 10); ?></td>
                    <td data-label="Questions"><?php echo array_key_exists("qb_q_IDS", $qb_detail) ? count($qb_detail['qb_q_IDS']) : "0"; ?></td>
                    <td data-label="Timed"><?php echo isset($qb_detail['qb_timed_field']) && $qb_detail['qb_timed_field'] == '1' ? "✔" : "✖"; ?></td>
                    <td data-label="Action"><a href="?qb_subscriber_exam_ID=<?php echo $exam->ID ?>">Take Exam</a></td>
                </tr>
                <?php
            } 
        }

        // If no exams match the condition, show a message
        if (!$hasExam) {
            echo "<tr><td colspan='4' style='text-align: center;'>No predefined available</td></tr>";
        }
    } else {
        echo "<tr><td colspan='4' style='text-align: center;'>No predefined available</td></tr>";
    }
    ?>
    </tbody>
</table>


