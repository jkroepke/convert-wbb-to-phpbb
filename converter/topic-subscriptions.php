<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 16.01.14
 * Time: 23:45
 */

$wbbTopicSubscriptions = $wbbDb->query("SELECT * FROM wbb{$wbbMySQLConnection['wbbNum']}_1_thread_subscription;");

while($wbbTopicSubscription = $wbbTopicSubscriptions->fetch_assoc())
{
    if($wbbTopicSubscription['enableNotification'] && $wbbTopicSubscription['emails'])
    {
        $notifyStatus   = NOTIFY_BOTH;
    }
    elseif($wbbTopicSubscription['enableNotification'])
    {
        $notifyStatus   = NOTIFY_IM;
    }
    elseif($wbbTopicSubscription['emails'])
    {
        $notifyStatus   = NOTIFY_EMAIL;
    }

    $phpBBTopicSubscription = array(
            'topic_id'                  => $wbbTopicSubscription['threadID'],
            'user_id'                   => $wbbTopicSubscription['userID'],
            'notify_status'             => $notifyStatus
        );


    insertData("topics_watch", $phpBBTopicSubscription);
}

$wbbTopics->close();