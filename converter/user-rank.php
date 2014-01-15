<?php

$wbbUserRanks = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");

while($wbbUserRank = $wbbUserRanks->fetch_assoc())
{
    $phpBBUserRank = array(
        'rank_title'   => $wbbUserRank['rankTitle'],
        'rank_special' => 0,
        'rank_min'     => $wbbUserRank['neededPoints'] / 5
    );

    insertData("ranks", $phpBBUserRank);
}

$wbbUserRanks->close();