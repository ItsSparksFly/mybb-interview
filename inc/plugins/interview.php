<?php

function interview_info()
{
    global $mybb, $lang;
    $lang->load('interview');

    $interview_info = array(
        "name"          => $lang->interview_title,
        "description"   => $lang->interview_desc,
        "website"       => "https://github.com/its-sparks-fly",
        "author"        => "sparks fly",
        "authorsite"    => "https://github.com/its-sparks-fly",
        "version"       => "1.0",
        "compatibility" => "18*"
    );

    return $interview_info;
}

function interview_install() 
{
    global $db;
    // Add a new database table 
    $tables = [ "interview_questions", "interview_answers"];
    foreach($tables as $table) {
        if ($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }

    $collation = $db->build_create_table_collation();
    
    // Create table
    $db->write_query("
        CREATE TABLE ".TABLE_PREFIX."interview_questions (
        `qid` int(10) unsigned NOT NULL auto_increment,
        `title` text NOT NULL DEFAULT '',
        PRIMARY KEY (qid)
        ) ENGINE=MyISAM{$collation};
    ");

    $db->write_query("
        CREATE TABLE ".TABLE_PREFIX."interview_answers (
        `aid` int(10) unsigned NOT NULL auto_increment,
        `qid` int(10) unsigned NOT NULL,
        `uid` int(10) unsigned NOT NULL,
        `text` text NOT NULL DEFAULT '',
        PRIMARY KEY (aid)
        ) ENGINE=MyISAM{$collation};
    ");
}

function interview_is_installed()
{
	global $db;
	if($db->table_exists('interview_questions'))
	{
		return true;
	}
	return false;
}

function interview_uninstall() 
{
    global $db;
    
    $tables = [ "interview_questions", "interview_answers"];
    foreach($tables as $table) {
        if ($db->table_exists($table)) {
            $db->drop_table($table);
        }
    }
}

function interview_activate()
{

    global $db, $lang;
    $lang->load('interview');

    $setting_group = array(
	    'name' => 'interview',
	    'title' => $lang->interview_title,
	    'description' => $lang->interview_options,
	    'disporder' => 1,
	    'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
	    // A text setting
	    'interview_number' => array(
	        'title' => $lang->interview_number,
	        'description' => $lang->interview_number_desc,
	        'optionscode' => 'text',
	        'value' => '', // Default
	        'disporder' => 1
	    ),
	);

	foreach($setting_array as $name => $setting)
	{
	    $setting['name'] = $name;
	    $setting['gid'] = $gid;

	    $db->insert_query('settings', $setting);
	}

	rebuild_settings();

    $usercp_interview = array(
		'title'		=> 'interview_usercp',
		'template'	=> $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->interview}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <form enctype="multipart/form-data" action="usercp.php" method="post">
        <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
        <table width="100%" border="0" align="center"
        <tr>
        {$usercpnav}
            <td valign="top">
                <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" width="80%">
                         {$question_bit}
                </table>
        
                <br />
                <div align="center">
                    <input type="hidden" name="action" value="do_interview" />
                    <input type="submit" class="button" name="submit" value="{$lang->interview_submit}" />
                </div>
            </td>
        </tr>
        </table>
        </form>
        {$footer}
        </body>
        </html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $usercp_interview);
	
	$usercp_interview_bit = array(
		'title'		=> 'interview_usercp_bit',
		'template'	=> $db->escape_string('			<tr>
        <td colspan="2" class="tcat">{$question[\'title\']}</td>
    </tr>
    <tr>
        <td class="trow2" width="30%" align="center">
            <span class="smalltext"><strong>{$answercount}</strong> {$lang->interview_answercount}</span>
        </td>
        <td class="trow2" width="70%" align="center">
<textarea id="text{$question[\'qid\']}" name="text{$question[\'qid\']}" rows="6" cols="120">{$answer}</textarea>
        </td>
    </tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $usercp_interview_bit);

	$member_profile_interview = array(
		'title'		=> 'interview_member_profile',
		'template'	=> $db->escape_string('<div class="tborder">
        <div class="tcat">{$answer[\'title\']}</div>
        <div class="trow2 smalltext">{$answer[\'answer\']}</div>
    </div>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $member_profile_interview);

    $usercp_nav_interview = array(
		'title'		=> 'interview_usercp_nav',
		'template'	=> $db->escape_string('<tbody><tr><td class="trow1 smalltext"><a href="usercp.php?action=interview" class="usercp_nav_item usercp_nav_drafts">{$lang->interview}</a></td></tr></tbody>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $usercp_nav_interview);

    change_admin_permission('config', 'interview', 1);

}

function interview_deactivate()
{

    global $db;

    $db->delete_query('settings', "name IN ('interview_number')");
	$db->delete_query('settinggroups', "name = 'interview'");

	rebuild_settings();

    $db->delete_query("templates", "title LIKE 'interview_%'");
    change_admin_permission('config', 'interview', -1);

}

$plugins->add_hook("admin_config_action_handler", "interview_admin_config_action_handler");
// Set action handler for edit terms
function interview_admin_config_action_handler(&$actions)
{
    $actions['interview'] = array('active' => 'interview', 'file' => 'interview');
}

$plugins->add_hook("admin_config_permissions", "interview_admin_config_permissions");
// Admin permissions
function interview_admin_config_permissions(&$admin_permissions)
{
    global $lang;
    $lang->load('interview');

    $admin_permissions['interview'] = $lang->interview_permission;
}

$plugins->add_hook("admin_config_menu", "interview_admin_config_menu");
// ACP menu entry
function interview_admin_config_menu(&$sub_menu)
{
    global $mybb, $lang;
    $lang->load('interview');

    $sub_menu[] = array(
        'id' => 'interview',
        'title' => $lang->interview_add,
        'link' => 'index.php?module=config-interview'
    );
}

$plugins->add_hook("admin_load", "interview_edit_interview");
function interview_edit_interview()
{
    global $mybb, $db, $lang, $page, $run_module, $action_file;
    $lang->load('interview');

    if ($page->active_action != 'interview') {
        return false;
    }

    if ($run_module == 'config' && $action_file == 'interview') {
        // Show overview
        if ($mybb->input['action'] == "" || !isset($mybb->input['action'])) {
            $page->add_breadcrumb_item($lang->interview_manage);
            $page->output_header($lang->interview_manage);
            $sub_tabs['interview'] = array(
                'title' => $lang->interview_edit,
                'link' => 'index.php?module=config-interview',
                'description' => $lang->interview_add_desc
            );
            $sub_tabs['interview_add'] = array(
                'title' => $lang->interview_add,
                'link' =>'index.php?module=config-interview&amp;action=add',
                'description' => $lang->interview_add_desc
            );
            $page->output_nav_tabs($sub_tabs, 'interview');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Build the overview
            $form = new Form("index.php?module=config-interview", "post");

            $form_container = new FormContainer($lang->interview_edit);
            $form_container->output_row_header('#');
            $form_container->output_row_header($lang->interview_question);
            $form_container->output_row_header('<div style="text-align: center;"> Optionen </div>');

            $i = 1;
            $query = $db->simple_select("interview_questions", "*");
            while ($all_interview = $db->fetch_array($query)) {
                $form_container->output_cell($i);
                $form_container->output_cell('<strong>'.htmlspecialchars_uni($all_interview['title']).'</strong>');
                $popup = new PopupMenu("interview_{$all_interview['qid']}", "Bearbeiten");
                $popup->add_item(
                    "Bearbeiten",
                    "index.php?module=config-interview&amp;action=edit&amp;qid={$all_interview['qid']}"
                );
                $popup->add_item(
                    "Löschen",
                    "index.php?module=config-interview&amp;action=delete&amp;qid={$all_interview['qid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), array("class" => "align_center"));
                $form_container->construct_row();
                ++$i;
            }

            $form_container->end();
            $form->end();
            $page->output_footer();

            exit;
        }

        // Add new interview
        if ($mybb->input['action'] == "add") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['title'])) {
                    $errors[] = "Leeres Feld: Frage";
                }

                // No errors - insert the interview
                if (empty($errors)) {

                    $new_interview = array(
                        "title" => $db->escape_string($mybb->input['title'])
                    );

                    $db->insert_query("interview_questions", $new_interview);

                    $mybb->input['module'] = "interview";
                    $mybb->input['action'] = "interview_add";
                    log_admin_action(htmlspecialchars_uni($mybb->input['title']));

                    flash_message($lang->interview_added, 'success');
                    admin_redirect("index.php?module=config-interview");
                }

            }
            $page->add_breadcrumb_item($lang->interview_add);

            $page->output_header($lang->interview_manage);
            $sub_tabs['interview'] = array(
                'title' => $lang->interview_edit,
                'link' => 'index.php?module=config-interview',
                'description' => $lang->interview_add_desc
            );
            $sub_tabs['interview_add'] = array(
                'title' => $lang->interview_add,
                'link' =>'index.php?module=config-interview&amp;action=add',
                'description' => $lang->interview_add_desc
            );
            $page->output_nav_tabs($sub_tabs, 'interview');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Build the form
            $form = new Form("index.php?module=config-interview&amp;action=add", "post", "", 1);

            $form_container = new FormContainer("interview_add");
            $form_container->output_row(
                $lang->interview_question . '<em>*</em>',
                $lang->interview_question_desc,
                $form->generate_text_box('title', $mybb->input['title'])
            );
            $form_container->end();
            $buttons[] = $form->generate_submit_button("Absenden");
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();
            exit;
        }

        // Edit interview
        if ($mybb->input['action'] == "edit") {
            if ($mybb->request_method == "post") {
                // Check if required fields are not empty
                if (empty($mybb->input['title'])) {
                    $errors[] = "Du hast keinen Titel angegeben.";
                }

                // No errors - insert the terms of use
                if (empty($errors)) {
                    $qid = $mybb->get_input('qid', MyBB::INPUT_INT);

                    $edited_interview = array(
                        "title" => $db->escape_string($mybb->input['title'])
                    );

                    $db->update_query("interview_questions", $edited_interview, "qid='{$qid}'");

                    $mybb->input['module'] = "interview_menu";
                    $mybb->input['action'] = "Frage erfolgreich bearbeitet.";
                    log_admin_action(htmlspecialchars_uni($mybb->input['title']));

                    flash_message("Erfolgreich bearbeitet", 'success');
                    admin_redirect("index.php?module=config-interview");
                }

            }
            $page->add_breadcrumb_item($lang->interview_edit);

            $page->output_header($lang->interview_manage);
            $sub_tabs['interview'] = array(
                'title' => $lang->interview_manage,
                'link' => 'index.php?module=config-interview',
                'description' => ''
            );
            $sub_tabs['interview_add'] = array(
                'title' => $lang->interview_add,
                'link' =>'index.php?module=config-interview&amp;action=add',
                'description' => $lang->interview_add_desc
            );
            $sub_tabs['interview_edit'] = array(
                'title' => $lang->interview_edit,
                'link' =>'index.php?module=config-interview&amp;action=edit',
                'description' => $lang->interview_add_desc
            );
            $page->output_nav_tabs($sub_tabs, 'interview_edit');

            // Show errors
            if (isset($errors)) {
                $page->output_inline_error($errors);
            }

            // Get the data
            $qid = $mybb->get_input('qid', MyBB::INPUT_INT);
            $query_edit = $db->simple_select("interview_questions", "*", "qid={$qid}");
            $edit_interview = $db->fetch_array($query_edit);

            // Build the form
            $form = new Form("index.php?module=config-interview&amp;action=edit", "post", "", 1);
            echo $form->generate_hidden_field('qid', $qid);

            $form_container = new FormContainer("interview bearbeiten");
            $form_container->output_row(
                $lang->interview_question,
                $lang->interview_question_desc,
                $form->generate_text_box('title', htmlspecialchars_uni($edit_interview['title']))
            );
 
            $form_container->end();
            $buttons[] = $form->generate_submit_button("Speichern");
            $form->output_submit_wrapper($buttons);
            $form->end();
            $page->output_footer();

            exit;
        }

        // Remove
        if ($mybb->input['action'] == "delete") {
            // Get data
            $qid = $mybb->get_input('qid', MyBB::INPUT_INT);
            $query_del = $db->simple_select("interview_questions", "*", "qid={$qid}");
            $del_interview = $db->fetch_array($query_del);

            if (empty($qid)) {
                flash_message($lang->interview_invalid, 'error');
                admin_redirect('index.php?module=interview');
            }
            // Cancel button pressed?
            if (isset($mybb->input['no']) && $mybb->input['no']) {
                admin_redirect('index.php?module=interview');
            }

            if (!verify_post_check($mybb->input['my_post_key'])) {
                flash_message($lang->invalid_post_verify_key2, 'error');
                admin_redirect("index.php?module=interview");
            } else {
                if ($mybb->request_method == "post") {
                    // Delete terms entry
                    $db->delete_query("interview_questions", "qid='{$qid}'");

                    $mybb->input['module'] = "interview-Menü";
                    $mybb->input['action'] = "interview erfolgreich gelöscht. ";
                    log_admin_action(htmlspecialchars_uni($del_interview['title']));

                    flash_message($lang->interview_deleted, 'success');
                    admin_redirect('index.php?module=interview');
                } else {
                    $page->output_confirm_action(
                        "index.php?module=config-interview&amp;action=delete&amp;qid={$qid}",
                        "Löschen bestätigen."
                    );
                }
            }
            exit;
        }
    }
}

$plugins->add_hook("usercp_start", "interview_usercp_start");
function interview_usercp_start() {
    global $mybb, $lang, $db, $templates,$theme, $header, $headerinclude, $footer, $usercpnav, $question_bit;
    $lang->load('interview');

    $page = "";
    $question_bit = "";

    if($mybb->input['action'] == "interview") {
        $sql = "SELECT * FROM ".TABLE_PREFIX."interview_questions";
        $query = $db->query($sql);
        while($question = $db->fetch_array($query)) {
            $sql1 = "SELECT text FROM ".TABLE_PREFIX."interview_answers WHERE uid = '{$mybb->user['uid']}' AND qid = '{$question['qid']}'";
            $answer = $db->fetch_field($db->query($sql1), "text");
            $sql2 = "SELECT COUNT(*) AS answercount FROM ".TABLE_PREFIX."interview_answers WHERE qid = '{$question['qid']}' AND text != '' AND uid IN(SELECT uid FROM ".TABLE_PREFIX."users)";
            $answercount = $db->fetch_field($db->query($sql2), "answercount");
            eval("\$question_bit .= \"".$templates->get("interview_usercp_bit")."\";");
        }       
        eval("\$page = \"".$templates->get("interview_usercp")."\";");
        output_page($page);
    }

    if($mybb->input['action'] == "do_interview" && $mybb->request_method == "post") {
        
        // Verify incoming POST request
        verify_post_check($mybb->get_input('my_post_key'));
        
        $query = $db->simple_select("interview_questions", "qid");
        while($question = $db->fetch_array($query)) {
            $sql = "SELECT aid FROM ".TABLE_PREFIX."interview_answers WHERE uid = '{$mybb->user['uid']}' AND qid = '{$question['qid']}'";
            $aid = $db->fetch_field($db->query($sql), "aid");
            if($aid) {
                if(empty($mybb->get_input('text'.$question['qid']))) {
                    $db->delete_query("interview_answers", "aid = '$aid'");
                } else {
                    $new_record = [
                        "text" => $db->escape_string($mybb->get_input('text'.$question['qid']))
                    ];
                    $db->update_query("interview_answers", $new_record, "aid = '$aid'");
                }
            } else {
                if(!empty($mybb->get_input('text'.$question['qid']))) {
                    $new_record = [
                        "qid" => (int)$question['qid'],
                        "uid" => (int)$mybb->user['uid'],
                        "text" => $db->escape_string($mybb->get_input('text'.$question['qid']))
                    ];
                    $db->insert_query("interview_answers", $new_record);
                }
            }
        }                                        
        redirect("usercp.php?action=interview");	
    }
}

$plugins->add_hook("member_profile_end", "interview_profile");
function interview_profile() {
    global $db, $mybb, $memprofile, $templates, $number, $profile_answers;
    $profile_answers = "";

    $number = $mybb->settings['interview_number'];
    $sql = "SELECT aid FROM ".TABLE_PREFIX."interview_answers LEFT JOIN ".TABLE_PREFIX."interview_questions ON ".TABLE_PREFIX."interview_questions.qid = ".TABLE_PREFIX."interview_answers.qid WHERE uid = '{$memprofile['uid']}' ORDER BY aid DESC";
	$query = $db->query($sql);
	$answers = [];
	while($answerr = $db->fetch_array($query)) {
		$answers[] = $answerr['aid'];
        if(!$number) {
            $sql = "SELECT *, ".TABLE_PREFIX."interview_answers.text AS answer FROM ".TABLE_PREFIX."interview_answers LEFT JOIN ".TABLE_PREFIX."interview_questions ON ".TABLE_PREFIX."interview_questions.qid = ".TABLE_PREFIX."interview_answers.qid WHERE aid = '{$answerr[aid]}'";
            $answer = $db->fetch_array($db->query($sql));
            eval("\$profile_answers .= \"".$templates->get("interview_member_profile")."\";");
        }
	}
    if($number) {
		shuffle($answers);
		for($i = 0; $i <= $number - 1; $i++) {
			$sql = "SELECT *, ".TABLE_PREFIX."interview_answers.text AS answer FROM ".TABLE_PREFIX."interview_answers LEFT JOIN ".TABLE_PREFIX."interview_questions ON ".TABLE_PREFIX."interview_questions.qid = ".TABLE_PREFIX."interview_answers.qid WHERE aid = '{$answers[$i]}'";
			$answer = $db->fetch_array($db->query($sql));
			eval("\$profile_answers .= \"".$templates->get("interview_member_profile")."\";");
		}
    }
}

$plugins->add_hook("usercp_menu", "interview_usercp_menu", 40);
function interview_usercp_menu()
{
    global $db, $mybb, $lang, $templates, $theme, $usercpmenu;
    $lang->load('interview');
    $usercpmenu .= eval($templates->render('interview_usercp_nav'));
}

$plugins->add_hook("fetch_wol_activity_end", "interview_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "interview_online_location");
function interview_online_activity($user_activity) {
    global $parameters;
    
        $split_loc = explode(".php", $user_activity['location']);
        if($split_loc[0] == $user['location']) {
            $filename = '';
        } else {
            $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
        }
        
        switch ($filename) {
            case 'usercp':
            if($parameters['action'] == "interview" && empty($parameters['site'])) {
                $user_activity['activity'] = "interview";
            }
            break;
        }
          
    return $user_activity;
    }
    
function interview_online_location($plugin_array) {
    global $mybb, $theme, $lang;
    $lang->load('instaroid');
    
    if($plugin_array['user_activity']['activity'] == "interview") {
        $plugin_array['location_name'] = $lang->interview_online;
    }
    
    return $plugin_array;
}
