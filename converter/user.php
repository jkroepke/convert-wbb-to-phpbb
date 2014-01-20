<?php

$wbbUserIpAddress = array();

// If the wbb has non dafault optionIDs, we can ask them here.
$wbbUserOptions = $wbbDb->query("SELECT optionID,optionName FROM wcf{$wbbMySQLConnection['wbbNum']}_user_option
    WHERE optionName IN
    ('birthday','aboutMe','enableDaylightSavingTime','timezone','location','homepage','icq','aim','jabber','msn','yim');");

$wbbUserOptionNames = array();
while($option = $wbbUserOptions->fetch_assoc())
{
    $wbbUserOptionNames[$option['optionName']] = 'userOption'.$option['optionID'];
}

$wbbUserOptions->close();

$wbbUsers = $wbbDb->query("SELECT wcfu.*, boardLastMarkAllAsReadTime, boardLastActivityTime, ".implode(', ', $wbbUserOptionNames)."
    FROM wcf{$wbbMySQLConnection['wbbNum']}_user wcfu
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_user wbbu USING (userID)
    INNER JOIN wcf{$wbbMySQLConnection['wbbNum']}_user_option_value USING (userID);");

while($wbbUser = $wbbUsers->fetch_assoc())
{
    $userSignature                        = convertBBCode($wbbUser['signature']);
    $wbbUserIpAddress[$wbbUser['userID']] = $wbbUser['registrationIpAddress'];

    $birthday = '';

    // don't convert dates without year
    if(!empty($wbbUser[$wbbUserOptionNames['birthday']]) && substr($wbbUser[$wbbUserOptionNames['birthday']], 0, 4) !== '0000')
    {
        $birthday    = sprintf('%2d-%2d-%4d',
            substr($wbbUser[$wbbUserOptionNames['birthday']], 8, 2), // day
            substr($wbbUser[$wbbUserOptionNames['birthday']], 5, 2), // month
            substr($wbbUser[$wbbUserOptionNames['birthday']], 0, 4)  // year
        );
    }

    $phpBBUser = array(
        'user_id'                  => $wbbUser['userID'],
        'user_type'                => USER_NORMAL,
        'group_id'                 => 2,
        'user_permissions'         => '',
        'user_perm_from'           => 0,
        'user_ip'                  => $wbbUser['registrationIpAddress'],
        'user_regdate'             => $wbbUser['registrationDate'],
        'username'                 => $phpBBDb->real_escape_string($wbbUser['username']),
        'username_clean'           => $phpBBDb->real_escape_string(utf8_clean_string($wbbUser['username'])),
        'user_password'            => phpbb_hash($wbbUser['password']),
        'user_passchg'             => time(),
        'user_pass_convert'        => 1,
        'user_email'               => $phpBBDb->real_escape_string($wbbUser['email']),
        'user_email_hash'          => phpbb_email_hash($wbbUser['email']),
        'user_birthday'            => $birthday,
        'user_lastvisit'           => $wbbUser['boardLastActivityTime'],
        'user_lastmark'            => $wbbUser['boardLastMarkAllAsReadTime'],
        'user_lastpost_time'       => 0,
        'user_lastpage'            => '',
        'user_last_confirm_key'    => '',
        'user_last_search'         => 0,
        'user_warnings'            => 0,
        'user_last_warning'        => 0,
        'user_login_attempts'      => 0,
        'user_inactive_reason'     => 0,
        'user_inactive_time'       => 0,
        'user_posts'               => 0,
        'user_lang'                => $phpBBConfig['default_lang'],
        'user_timezone'            => $phpBBConfig['board_timezone'],
        'user_dst'                 => $phpBBConfig['board_dst'],
        'user_dateformat'          => $phpBBConfig['default_dateformat'],
        'user_style'               => $phpBBConfig['default_style'],
        'user_rank'                => 0,
        'user_colour'              => '',
        'user_new_privmsg'         => 0,
        'user_unread_privmsg'      => 0,
        'user_last_privmsg'        => 0,
        'user_message_rules'       => 0,
        'user_full_folder'         => PRIVMSGS_NO_BOX,
        'user_emailtime'           => 0,
        'user_notify'              => 0,
        'user_notify_pm'           => 1,
        'user_notify_type'         => NOTIFY_EMAIL,
        'user_allow_pm'            => 1,
        'user_allow_viewonline'    => 1,
        'user_allow_viewemail'     => 1,
        'user_allow_massemail'     => 1,
        'user_options'             => 230271,
        'user_sig'                 => $phpBBDb->real_escape_string($userSignature['text']),
        'user_sig_bbcode_uid'      => $userSignature['uid'],
        'user_sig_bbcode_bitfield' => $userSignature['bitfield'],
        'user_from'                => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['location']]),
        'user_icq'                 => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['icq']]),
        'user_aim'                 => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['aim']]),
        'user_yim'                 => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['yim']]),
        'user_msnm'                => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['msn']]),
        'user_jabber'              => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['jabber']]),
        'user_website'             => $phpBBDb->real_escape_string($wbbUser[$wbbUserOptionNames['homepage']]),
        'user_occ'                 => '',
        'user_interests'           => '',
        'user_form_salt'           => unique_id(),
        'user_new'                 => 0
    );

    if($wbbUser['email'] == $rootUser['user_email'])
    {
        // we found the old admin user. yey

        $phpBBUser['user_type']         = USER_FOUNDER;
        $phpBBUser['group_id']          = 3;
        $phpBBUser['user_rank']         = $rootUser['user_rank'];
        $phpBBUser['user_colour']       = $rootUser['user_colour'];
        $phpBBUser['user_pass_convert'] = 0;

        // let them login directly without request a new password.
        $phpBBUser['user_password']     = $rootUser['user_password'];

        // register the admin as an admin
        $phpBBAclUser = array(
            'user_id'        => $phpBBUser['user_id'],
            'forum_id'       => 0,
            'auth_option_id' => 0,
            'auth_role_id'   => 5,
            'auth_setting'   => 0
        );

        insertData("acl_users", $phpBBAclUser);

        // add the founder to global mod ...
        $phpBBUserToGroup = array(
            'group_id'     => 4,
            'user_id'      => $wbbUser['userID'],
            'group_leader' => 0,
            'user_pending' => 0
        );

        insertData("user_group", $phpBBUserToGroup);

        // ... and admin group.
        $phpBBUserToGroup = array(
            'group_id'     => 5,
            'user_id'      => $wbbUser['userID'],
            'group_leader' => 1,
            'user_pending' => 0
        );

        insertData("user_group", $phpBBUserToGroup);
    }

    insertData("user", $phpBBUser);

    // add user to user group
    $phpBBUserToGroup = array(
        'group_id'     => 5,
        'user_id'      => $wbbUser['userID'],
        'group_leader' => 0,
        'user_pending' => 0
    );

    insertData("user_group", $phpBBUserToGroup);
    echo '.';
}

$wbbUsers->close();

$phpBBConfigUpdate  = array(
    'config_value'  => $wbbUser['userID'],
);

updateData('config', $phpBBConfigUpdate, "config_name = 'newest_user_id'");

$phpBBConfigUpdate  = array(
    'config_value'  => $phpBBDb->real_escape_string($wbbUser['username']),
);

updateData('config', $phpBBConfigUpdate, "config_name = 'newest_username'");

$phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}config SET
config_value = (
    SELECT COUNT(*) FROM {$phpBBMySQLConnection['prefix']}user
    WHERE user_type IN (".USER_FOUNDER.",".USER_NORMAL.")
) WHERE config_name = 'num_users';");