<?php

$wbbUserFriends = $wbbDb->query("SELECT * FROM ".PREFIX_WCF."_user_whitelist;");

while($wbbUserFriend = $wbbUserFriends->fetch_assoc())
{
    $phpBBUserFriend = array(
        'user_id'  => $wbbUserFriend['userID'],
        'zebra_id' => $wbbUserFriend['whiteUserID'],
        'friend'   => 1,
        'foe'      => 0
    );

    insertData(ZEBRA_TABLE, $phpBBUserFriend);

    output('row');
}

$wbbUserFriends->close();
output('end');