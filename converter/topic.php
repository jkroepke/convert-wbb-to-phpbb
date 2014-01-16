<?php

$wbbTopics = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");

while($wbbTopic = $wbbTopics->fetch_assoc())
{
    $phpBBTopic = array(
        'rank_title'   => $wbbTopic['rankTitle'],
        'rank_special' => 0,
        'rank_min'     => $wbbTopic['neededPoints'] / 5
    );

    insertData("ranks", $phpBBTopic);
}

$wbbTopics->close();