<?php

// the first six user groups are wbb builtin groups. Just ignore them.

$phpBBLastGroupId = reset($phpBBDb->query("SELECT MAX(group_id) FROM ".GROUPS_TABLE.";")->fetch_row());
$wbbUserGroups    = $wbbDb->query("SELECT * FROM ".PREFIX_WCF."_group WHERE groupID > 6;");

while($wbbUserGroup = $wbbUserGroups->fetch_assoc())
{
    // on phpbb ranks and groups are splitted, just create a rank and group on phpbb

    $phpBBUserRank = array(
        'rank_title'   => $wbbUserGroup['groupName'],
        'rank_special' => 1,
    );

    insertData(RANKS_TABLE, $phpBBUserRank);

    $rankId    = $phpBBDb->insert_id;

    $groupText = convertBBCode($wbbUserGroup['groupDescription']);

    // wbb knows only group types like GROUP_CLOSED
    $phpBBUserGroup = array(
        'group_id'             => $wbbUserGroup['groupID'] + $phpBBLastGroupId,
        'group_type'           => GROUP_CLOSED,
        'group_founder_manage' => 0,
        'group_skip_auth'      => 0,
        'group_name'           => $phpBBDb->real_escape_string($wbbUserGroup['groupName']),
        'group_desc'           => $phpBBDb->real_escape_string($groupText['text']),
        'group_desc_bitfield'  => $groupText['bitfield'],
        'group_desc_options'   => 7,
        'group_desc_uid'       => $groupText['uid'],
        'group_display'        => $wbbUserGroup['showOnTeamPage'],
        'group_rank'           => $rankId,
    );

    insertData(GROUPS_TABLE, $phpBBUserGroup);

    output('row');
}

$wbbUserGroups->close();
output('end');