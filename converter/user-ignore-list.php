<?php

$wbbUserBlackLists = $wbbDb->query("SELECT * FROM ".PREFIX_WCF."_user_blacklist;");

while($wbbUserBlackList = $wbbUserBlackLists->fetch_assoc())
{
    $phpBBUserBlackList = array(
        'user_id'  => $wbbUserBlackList['userID'],
        'zebra_id' => $wbbUserBlackList['blackUserID'],
        'friend'   => 0,
        'foe'      => 1
    );

    insertData(ZEBRA_TABLE, $phpBBUserBlackList);

    output('row');
}

$wbbUserBlackLists->close();
output('end');