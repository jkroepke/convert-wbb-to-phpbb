<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 16.01.14
 * Time: 23:45
 */

$wbbThreadSubscriptions = $wbbDb->query("SELECT * FROM ".PREFIX_WBB."_thread_subscription;");

while($wbbThreadSubscription = $wbbThreadSubscriptions->fetch_assoc())
{
    $phpBBTopicsWatch = array(
        'topic_id'                  => $wbbThreadSubscription['threadID'],
        'user_id'                   => $wbbThreadSubscription['userID'],
        'notify_status'             => (int) $wbbThreadSubscription['emails'] == 0
    );

    insertData(TOPICS_WATCH_TABLE, $phpBBTopicsWatch);

    output('row');
}

$wbbThreadSubscriptions->close();
output('end');