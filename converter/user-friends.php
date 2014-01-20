<?php

$wbbUserFriends = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_whitelist;");

if($wbbUserFriends->num_rows > 0)
{
    while($wbbUserFriend = $wbbUserFriends->fetch_assoc())
    {
        $phpBBUserFriend = array(
            'user_id'  => $wbbUserFriend['userID'],
            'zebra_id' => $wbbUserFriend['whiteUserID'],
            'friend'   => 1,
            'foe'      => 0
        );

        insertData("zebra", $phpBBUserFriend);
        echo '.';
    }
}
else
{
    echo '.';
}

$wbbUserFriends->close();