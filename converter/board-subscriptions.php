<?php

/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 17.01.14
 * Time: 00:02
 */

$wbbBoardSubscriptions = $wbbDb->query("SELECT * FROM wbb{$wbbMySQLConnection['wbbNum']}_1_board_subscription;");

while($wbbBoardSubscription = $wbbBoardSubscriptions->fetch_assoc())
{
    $phpBBForumsWatch = array(
        'forum_id'      => $wbbBoardSubscription['boardID'],
        'user_id'       => $wbbBoardSubscription['userID'],
        'notify_status' => (int) $wbbBoardSubscription['emails'] == 0
    );

    insertData("forums_watch", $phpBBForumsWatch);

    output('row');
}

$wbbBoardSubscriptions->close();
output('end');