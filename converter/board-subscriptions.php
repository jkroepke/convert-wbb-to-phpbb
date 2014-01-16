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
    if($wbbBoardSubscription['emails'] = 0)
    {
        $notifyStatus   = NOTIFY_YES;
    }
    else
    {
        $notifyStatus   = NOTIFY_NO;
    }

    $phpBBForumsWatch = array(
        'topic_id'                  => $wbbBoardSubscription['threadID'],
        'user_id'                   => $wbbBoardSubscription['userID'],
        'notify_status'             => $notifyStatus
    );


    insertData("forums_watch", $phpBBForumsWatch);
}

$wbbTopics->close();