<?php

$wbbUserToGroups    = $wbbDb->query("SELECT ug.*,leaderUserID FROM ".PREFIX_WCF."_user_to_groups ug
    LEFT JOIN ".PREFIX_WCF."_group_leader gl
        ON userID = leaderUserID AND ug.groupID = gl.groupID
    WHERE ug.groupID > 6;");

while($wbbUserToGroup = $wbbUserToGroups->fetch_assoc())
{
    $phpBBUserToGroup = array(
        'group_id'     => $wbbUserToGroup['groupID'] + $phpBBLastGroupId,
        'user_id'      => $wbbUserToGroup['userID'],
        'group_leader' => $wbbUserToGroup['userID'] == $wbbUserToGroup['leaderUserID'],
        'user_pending' => 0
    );

    insertData(USER_GROUP_TABLE, $phpBBUserToGroup);

    output('row');
}

$wbbUserToGroups->close();
output('end');