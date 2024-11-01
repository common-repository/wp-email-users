<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_action('admin_enqueue_scripts', 'tsweu_enqueue_script_list');
if (!function_exists('tsweu_enqueue_script_list')) {
    function tsweu_enqueue_script_list()
    {
        $actual_link = $_SERVER['REQUEST_URI'];
        if (strpos($actual_link, 'weu_send_email') || strpos($actual_link, 'weu-template') || strpos($actual_link, 'weu-smtp-config') || strpos($actual_link, 'weu_email_setting') || strpos($actual_link, 'weu_email_auto_config') || strpos($actual_link, 'weu-manage-list') || strpos($actual_link, 'weu_custom_role') || strpos($actual_link, 'weu_sent_emails') || strpos($actual_link, 'weu-list-editor&listname')) {
            wp_enqueue_script('wp-email-user-datatable-script', plugins_url('js/jquery.dataTables.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_style('wp-email-user-datatable-style', plugins_url('css/dt/jquery.dataTables.min.css', __FILE__));
            /* EXPORT DATA TABLE FILES */
            wp_enqueue_style('wp-email-user-datatable-style-btncss-dt', plugins_url('css/dt/buttons.dataTables.min.css', __FILE__));
            wp_enqueue_script('wp-email-user-script-btn-dt', plugins_url('js/dt/dataTables.buttons.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script-flash-dt', plugins_url('js/dt/buttons.flash.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script-fonts-dt', plugins_url('js/dt/vfs_fonts.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script-jszip-dt', plugins_url('js/dt/jszip.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script-htmlbtn-dt', plugins_url('js/dt/buttons.html5.min.js', __FILE__), array(), '1.0.0', false);
            wp_enqueue_script('wp-email-user-script-btn-print-dt', plugins_url('js/dt/buttons.print.min.js', __FILE__), array(), '1.0.0', false);
        }
    }
}

if (!function_exists('tsweu_admin_manage_list')) {
    function tsweu_admin_manage_list()
    {
?>
        <div id="centeredmenu">
            <div class="wrap">
                <h1 style="margin: 10px 0px;">Manage Subscriber List</h1>
            </div>
            <ul class="w3-navbar w3-black">
                <li><a href="javascript:void(0)" onclick="openCity('London')">Manage Subscriber List</a></li>
                <li><a href="javascript:void(0)" onclick="openCity('Paris')">Manage Unsubscriber List</a></li>
            </ul>
        </div>
        <?php
        global $wpdb;
        $subscibers_arr = array();
        $table_name     = $wpdb->prefix . 'weu_subscribers';
        if (isset($_POST['add_new_list']) && sanitize_text_field($_POST['add_new_list']) == 'Add New List') {
            if (!isset($_POST['new_list_nonce_field']) || !wp_verify_nonce($_POST['new_list_nonce_field'], 'add_new_list_act')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {
                global $wpdb;
                $table_name   = $wpdb->prefix . 'weu_subscribers';
                $list_name    = sanitize_text_field($_POST['new_list_name']);
                $new_listname = isset($list_name) ? $list_name : '';
                tsweu_setup_activation_data();
                $def_list = array();
                // get option
                $pre_list = get_option('weu_subscriber_lists');
                if (empty($pre_list)) {
                    $pre_list = array();
                    $def_list = array(
                        'default'
                    );
                }
                if (in_array($new_listname, $pre_list)) {
                    echo '<div id="" class="notice notice-error is-dismissible"><p>List Already Present</p></div>';
                } else {
                    array_push($pre_list, $new_listname);
                    echo '<div id="" class="notice notice-success is-dismissible"><p>List Added Successfully</p></div>';
                }
                $sub_list = array_merge($def_list, $pre_list);
                update_option('weu_subscriber_lists', $sub_list);
            }
        }
        if (isset($_POST['uploadfile']) && sanitize_text_field($_POST['uploadfile']) == 'Import File') {
            if (!isset($_POST['import_new_list_nonce_field']) || !wp_verify_nonce($_POST['import_new_list_nonce_field'], 'import_new_list_act')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {
                $i             = 0;
                $filename      = $_FILES["uploadfiles"]["tmp_name"];
                $excelFileType = sanitize_text_field($_POST['weu_subscribers_import']);
                $random_token  = rand(1000000, 9999999);
                $curr_date     = current_time('mysql');
                $target_file   = basename($_FILES["uploadfiles"]["name"]);
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $allowed_ext   = array(
                    'csv'
                );
                if (in_array($imageFileType, $allowed_ext)) {
                    if ($_FILES["uploadfiles"]["size"] > 0) {
                        $file       = fopen($filename, "r");
                        $table_name = $wpdb->prefix . 'weu_subscribers';
                        while (($emapData = fgetcsv($file, 1000000, ",")) !== FALSE) {
                            if ($i >= 1) {
                                if (!empty($emapData[0]) && !empty($emapData[1])) {
                                    $wpdb->query("INSERT INTO $table_name SET 

                            `name`='" . htmlentities($emapData[0]) . "', 

                            `email`='" . htmlentities($emapData[1]) . "',

                            `list`='" . $excelFileType . "',

                            `authtoken`='" . $random_token . "',

                            `datetime`='" . $curr_date . "'

                            ");
                                }
                            }
                            $i++;
                        }
                        echo '<div id="" class="notice notice-success is-dismissible"><p>List Added Successfully</p></div>';
                    }
                } else {
                    echo '<div id="" class="notice notice-error is-dismissible"><p>Invalid file extension. Please Upload CSV (.csv) File Format.</p></div>';
                }
            }
        }
        if (isset($_POST['delete'])) {
            $delete_list = sanitize_text_field($_POST['delete']);
            global $wpdb;
            $table_name      = $wpdb->prefix . 'weu_subscribers';
            $mylink          = $wpdb->delete($table_name, array(
                'list' => $delete_list
            ), array(
                '%s'
            ));
            $all_sublist     = get_option('weu_subscriber_lists');
            $del_list_name[] = $delete_list;
            $new_lists       = array_diff($all_sublist, $del_list_name);
            update_option('weu_subscriber_lists', $new_lists);
        }
        echo '<div id="London" class="w3-container city">';
        echo '<div style="float:left; width:75%; background-color: #fff;padding: 18px;border-top: 10px solid #F1F1F1;">';
        echo '<form name="export_form" class="" style="" method="POST" action="#" enctype="multipart/form-data">';
        echo '<div class="wrap"><h1 style="margin: 10px 0px;"> <a href="#TB_inline?width=300&height=250&inlineId=add-new-list" class="page-title-action thickbox">Add New List</a></h1></div>';
        echo '<table id="" class="form-table">';
        echo '<tbody>';
        $table_name = $wpdb->prefix . 'weu_subscribers';
        $wau_lists  = $wpdb->get_results("SELECT DISTINCT list FROM $table_name");
        echo '</tbody>';
        echo '</table>';
        /**
         * Select Users
         */
        echo '<table id="example3" class="display alluser_datatable data_list" cellspacing="0">
    <thead>
        <tr style="text-align:center"> 
            <th>List Name</th>
            <th>List Count</th>
            <th>Manage</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>';
        $all_sublist = get_option('weu_subscriber_lists');
        $list_al     = count($all_sublist);
        $list_tbl    = count($wau_lists);
        $sub_list_nz = array();
        foreach ($wau_lists as $s_list) {
            global $wpdb;
            $table_name       = $wpdb->prefix . 'weu_subscribers';
            $curr_list        = $s_list->list;
            $list_count       = $wpdb->get_row("SELECT COUNT(*) AS list_count FROM $table_name WHERE list='$curr_list'");
            $list_editor_page = get_admin_url('', '/admin.php');
            if (empty($all_sublist))
                $all_sublist = array(
                    'default'
                );
            $list_editor_inst = add_query_arg(array(
                'page' => 'weu-list-editor',
                'listname' => $s_list->list
            ), $list_editor_page);
            echo '<tr style="text-align:center">';
            echo '<td><span id="getDetail">' . $s_list->list . '</span></td>';
            echo '<td><span>' . $list_count->list_count . '</span></td>';
            echo '<td><a style="text-decoration: none;" href="' . esc_url($list_editor_inst) . '" id="' . $s_list->list . '" class="select-all">Manage</a> </td><td> <button class="delete-email-indi" name="delete" type="submit" value="' . $s_list->list . '"><input type="hidden" id="delete-email-indi" name="weu_delete_list" value=""><span class="dashicons dashicons-trash"></span></button></td>';
            echo '</tr>';
            array_push($sub_list_nz, $s_list->list);
        }
        if ($list_al != $list_tbl) {
            $all_sublist_z = array();
            $all_sublist_z = array_diff($all_sublist, $sub_list_nz);
            foreach ($all_sublist_z as $s_list) {
                $curr_list        = (string) $s_list;
                $list_editor_page = get_admin_url('', '/admin.php');
                $list_editor_inst = add_query_arg(array(
                    'page' => 'weu-list-editor',
                    'listname' => $curr_list
                ), $list_editor_page);
                echo '<tr style="text-align:center">';
                echo '<td><span id="getDetail">' . $curr_list . '</span></td>';
                echo '<td><span> 0 </span></td>';
                echo '<td><a style="text-decoration: none;" href="' . esc_url($list_editor_inst) . '" id="' . $curr_list . '" class="select-all">Manage</a> </td><td> <button class="delete-email-indi" name="delete" type="submit" value="' . $curr_list . '"><input type="hidden" id="delete-email-indi" name="weu_delete_list" value=""><span class="dashicons dashicons-trash"></span></button></td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>'; // end user Data table for user
        echo '<table class="form-table" style="background: #f1f1f1;">';
        echo '<tbody>';
        echo '<tr>';
        wp_nonce_field('import_new_list_act', 'import_new_list_nonce_field');
        echo '<td><h4>Import List to </h4></td>';
        echo '<td>';
        echo '<select style="width: 150px;" name="weu_subscribers_import">';
        foreach ($all_sublist as $s_list) {
        ?>
            <option value="<?php echo $s_list; ?>"> <?php echo $s_list; ?> </option>
        <?php
        }

        echo '</select>
    <input type="file" name="uploadfiles" accept=".csv" id="uploadfiles" size="35" class="uploadfiles" style="padding:0 5%;" ><a href="' . plugins_url('WP_Email_Users') . '/sample_csv/sample.csv' . '" download class="sample_csv"> Download Sample CSV</button></a></td>';

        echo '<td><input class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn" value="Import File"></td>';
        echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        add_thickbox();
        //start of unsubscribers list 
        global $current_user, $wpdb, $wp_roles;
        $table_name_un = $wpdb->prefix . 'weu_unsubscriber';
        $count         = $wpdb->get_var("SELECT COUNT(id) FROM " . $table_name_un);
        $myrows        = $wpdb->get_results("SELECT * FROM " . $table_name_un);
        echo '<div id="Paris" class="w3-container city" style="display:none">';
        echo '<div style="float:left; width:75%; background-color: #fff;padding: 18px;border-top: 10px solid #F1F1F1;">';
        echo '<form name="export_form" class="" style="" method="POST" action="" enctype="multipart/form-data">';
        echo '<div class="wrap"><h1 style="margin: 10px 0px;">Manage Unsubscribers List </h1></div> ';
        echo '<table id="example3" class="display alluser_datatable data_list" cellspacing="0" style="width: 100%;">
    <thead>
        <tr style="text-align:center"> 
            <th>Id</th>
            <th>Uid</th>
            <th>Email</th>
            <th>Date & Time</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>';
        foreach ($myrows as $myrow) {
            echo '<tr style="text-align:center">';
            echo '<td><span id="getDetail">' . $myrow->id . '</span></td>';
            echo '<td><span>' . $myrow->uid . '</span></td>';
            echo '<td><span>' . $myrow->email . '</span></td>';
            echo '<td><span>' . $myrow->datetime . '</span></td>';
            echo '<td> <button class="delete-member-indi" name="delete1" type="submit" value="' . $myrow->id . '"><span class="dashicons dashicons-trash"></span></button></td>';
            echo '</tr>';
        }
        echo '</tbody></table>
  <input style="margin: 2em;margin-left: 1107px;" class="button-primary" type="submit" name="delete2" id="delete2" value="Delete All"></form></div> </div> ';
        if (isset($_POST['delete1'])) {
            $id = sanitize_text_field($_POST['delete1']);
            global $wpdb;
            $table_name = $wpdb->prefix . 'weu_unsubscriber';
            $status     = $wpdb->query("DELETE FROM " . $table_name . " WHERE `id`=" . $id);
        }
        //Delete all unsubscriber list
        if (isset($_POST['delete2'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'weu_unsubscriber';
            $status     = $wpdb->query("DELETE FROM " . $table_name);
        }
        //end of unsubscribers list 
        ?>
        <div id="add-new-list" style="display:none;">
            <p>
            <form method="post" name="list_form" action="#" onsubmit="return validation_list()">
                <table>
                    <tr>
                        <?php wp_nonce_field('add_new_list_act', 'new_list_nonce_field'); ?>
                        <td> <label> New List Name </label> </td>
                        <td> <input type="text" id="new_list" name="new_list_name"> </td>
                        <div id="errors" style="color:#ff0000;"></div>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label>
                                <input style="width: 100%;" class="button-primary" type="submit" name="add_new_list" value="Add New List">
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
            </p>
        </div>

        <div id="rename-list" style="display:none;">
            <p></p>
        </div>

    <?php
    }
}

if (!function_exists('tsweu_list_editor')) {
    function tsweu_list_editor()
    {
        $curr_list = '';
        if (isset($_GET['listname']) && !empty($_GET['listname'])) {
            $curr_list = sanitize_text_field($_GET['listname']);
        }
        if (isset($_POST['add_new_mem']) && sanitize_text_field($_POST['add_new_mem']) == 'Add New Member') {
            if (!isset($_POST['add_member_nonce_field']) || !wp_verify_nonce($_POST['add_member_nonce_field'], 'add_member_action')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {
                global $wpdb;
                $table_name   = $wpdb->prefix . 'weu_subscribers';
                $curr_list    = sanitize_text_field($_GET['listname']);
                $memname      = sanitize_text_field($_POST['member_name']);
                $new_memname  = isset($memname) ? $memname : '';
                $mem_email    = sanitize_email($_POST['member_email']);
                $new_mememail = isset($mem_email) ? $mem_email : '';
                $curr_date    = current_time('mysql');
                $random_token = rand(1000000, 9999999);
                tsweu_setup_activation_data();
                $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email =%s and list=%s", $new_mememail, $curr_list));
                if (!$rows_avail) {
                    $status = $wpdb->query($wpdb->prepare("INSERT INTO `" . $table_name . "`(`name`, `email`, `list`, `authtoken`, `datetime`) VALUES (%s,%s,%s,%d,%s)", $new_memname, $new_mememail, $curr_list, $random_token, $curr_date));
                    if ($status == 1) {
                        echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Member added Successfully. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                    } else {
                        echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Fail to add new member. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                    }
                } else {
                    echo '<div id="message" class="updated notice notice-success is-dismissible"><p>Member already exist. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
                }
            }
        }
        if (isset($_POST['delete_subs'])) {
            if (!isset($_POST['delete_member_nonce_field']) || !wp_verify_nonce($_POST['delete_member_nonce_field'], 'delete_member')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {
                global $wpdb;
                if (isset($_GET['listname']))
                    $curr_list = sanitize_text_field($_GET['listname']);
                $delete_subs = sanitize_text_field($_POST['delete_subs']);
                $table_name  = $wpdb->prefix . 'weu_subscribers';
                $mylink      = $wpdb->delete($table_name, array(
                    'list' => $curr_list,
                    'id' => $delete_subs
                ), array(
                    '%s',
                    '%s'
                ));
            }
        }
        if (isset($_POST['uploadfile']) && sanitize_text_field($_POST['uploadfile']) == 'Import File') {
            if (!isset($_POST['import_list_nonce_field']) || !wp_verify_nonce($_POST['import_list_nonce_field'], 'import_list_action')) {
                print 'Sorry, Please refresh page and try again.';
                exit;
            } else {
                $i = 0;
                global $wpdb;
                $filename      = $_FILES["uploadfiles"]["tmp_name"];
                $excelFileType = sanitize_text_field($_POST['weu_subscribers_import']);
                $random_token  = rand(1000000, 9999999);
                $curr_date     = current_time('mysql');
                $target_file   = basename($_FILES["uploadfiles"]["name"]);
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $allowed_ext   = array(
                    'csv'
                );
                if (in_array($imageFileType, $allowed_ext)) {
                    if ($_FILES["uploadfiles"]["size"] > 0) {
                        $file       = fopen($filename, "r");
                        $table_name = $wpdb->prefix . 'weu_subscribers';
                        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
                            $rows_avail = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE email = %s and list=%s", htmlentities($emapData[1]), $excelFileType));
                            if (!$rows_avail) {
                                if ($i >= 1) {
                                    if (!empty($emapData[0]) && !empty($emapData[1])) {
                                        $wpdb->query("INSERT INTO $table_name SET 

                                `name`='" . htmlentities($emapData[0]) . "', 

                                `email`='" . htmlentities($emapData[1]) . "',

                                `list`='" . $excelFileType . "',

                                `authtoken`='" . $random_token . "',

                                `datetime`='" . $curr_date . "'

                                ");
                                    }
                                }
                            }
                            $i++;
                        }
                        echo '<div id="" class="notice notice-success is-dismissible"><p>List added Successfully</p></div>';
                    }
                } else {
                    echo '<div id="" class="notice notice-error is-dismissible"><p>No file attached. please upload file first then import file.</p></div>';
                }
            }
        }
        /**
         * Select Users
         */
        echo '<div style="float:left; width:96%; background-color: #fff;padding: 18px;border-top: 10px solid #F1F1F1;">';
        echo '<form name="member_form" class="" style="" method="POST" action="#" enctype="multipart/form-data">';
        echo '<div class="wrap"><h1 style="margin: 10px 0px;">List Subscriber Editor - ' . $curr_list . ' <a href="#TB_inline?width=250&height=250&inlineId=add-new-member" class="page-title-action thickbox">Add New Member</a></h1></div>';
        wp_nonce_field('delete_member', 'delete_member_nonce_field');
        echo '<table id="example3" class="display alluser_datatable data_expo" cellspacing="0">

    <thead>
        <tr style="text-align:center"> 
            <th>Sr. No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>';
        global $wpdb;
        $table_name = $wpdb->prefix . 'weu_subscribers';
        $wau_lists  = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE list=%s", $curr_list));
        $index      = 0;
        foreach ($wau_lists as $s_list) {
            $table_name       = $wpdb->prefix . 'weu_subscribers';
            $curr_list        = $s_list->list;
            $list_count       = $wpdb->get_row("SELECT COUNT(*) AS list_count FROM $table_name WHERE list='$curr_list'");
            $list_editor_page = get_admin_url('', '/admin.php');
            $list_editor_inst = add_query_arg(array(
                'page' => 'weu-list-editor',
                'listname' => $s_list->list
            ), $list_editor_page);
            ++$index;
            echo '<tr style="text-align:center">';
            echo '<td><span id="getDetail">' . $index . '</span></td>';
            echo '<td><span>' . $s_list->name . '</span></td>';
            echo '<td>' . $s_list->email . '</td>';
            echo '<td> <button class="delete-member-indi" name="delete_subs" type="submit" value="' . $s_list->id . '"><span class="dashicons dashicons-trash"></span></button></td>';
            echo '</tr>';
        }
        echo '</tbody></table>'; // end user Data table for user
        wp_nonce_field('import_list_action', 'import_list_nonce_field');
        echo '<a href="' . plugins_url('WP_Email_Users') . '/sample_csv/sample.csv' . '" download class="sample_csv"> Download Sample CSV</button></a>';
        echo '<input style="margin: 2em;" type="file" accept=".csv" name="uploadfiles" id="uploadfiles" size="35" class="uploadfiles">';
        echo '<input style="margin: 2em;" class="button-primary" type="submit" name="uploadfile" id="uploadfile_btn" value="Import File">';
        echo '<input name="weu_subscribers_list_expo" type="hidden" value="' . $curr_list . '">';
        echo '<input name="weu_subscribers_import" type="hidden" value="' . $curr_list . '">';
        echo '</form>';
        echo '</div>';
        add_thickbox();
    ?>
        <div id="add-new-member" style="display:none;">
            <p>
            <h2> Add New Member to <?php
                                    echo $curr_list;
                                    ?> List </h2>
            <form method="post" action="#" name="add_member_form" onsubmit="return validation_member()">
                <?php
                wp_nonce_field('add_member_action', 'add_member_nonce_field');
                ?>

                <table>
                    <tr>
                        <td> <label> Member Name <font color="red">*</font> </label> </td>
                        <td> <input type="text" name="member_name" id="member_name"> </td>
                    </tr>
                    <tr>
                        <td> <label> Member Email <font color="red">*</font></label> </td>
                        <td> <input type="email" name="member_email" id="member_email"> </td>
                        <div id="errors" style="color:#ff0000;text-align:center;"></div>
                    </tr>
                    <tr>
                        <td colspan="2"><label><input style="width: 100%;" class="button-primary" type="submit" name="add_new_mem" value="Add New Member"></label> </td>
                    </tr>
                </table>
            </form>
            </p>
        </div>
<?php
    }
}
