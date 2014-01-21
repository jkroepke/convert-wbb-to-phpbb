<?php

$wbbUserFriends = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_whitelist;");

while($wbbUserFriend = $wbbUserFriends->fetch_assoc())
{
    $phpBBUserFriend = array(
        'user_id'  => $wbbUserFriend['userID'],
        'zebra_id' => $wbbUserFriend['whiteUserID'],
        'friend'   => 1,
        'foe'      => 0
    );

    insertData("zebra", $phpBBUserFriend);

    output('row');
}

$wbbUserFriends->close();
output('end');