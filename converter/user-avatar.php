<?php

//TODO: Tabelle abändern
$wbbAvatars    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_avatar;");

while($wbbAvatar = $wbbAvatars->fetch_assoc())
{
    $phpBBAvatar = array(
        'user_id'   => $wbbAvatar['userID'],
        'height'    => $wbbAvatar['height'],
        'width'     => $wbbAvatar['width'],
        'filename'  => $phpBBConfig['avatar_salt']."_".$wbbAvatar['userID'].".".$wbbAvatar['avatarExtension']
    );

    $wbbAvatarPath = $wbbPath.'wcf/images/avatars/avatar-'.$wbbAvatar['avatarID'].'.'.$wbbAvatar['avatarExtension'];
    $phpBBAvatarPath = $phpBBPath.$phpBBConfig['avatar_path'].'/'.$phpBBAvatar['filename'];


    //TODO: Am anfang des Convert schecken, ob die Pfade less und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.
    if(!is_readable($wbbAvatarPath)) continue;
    if(!is_writeable(dirname($phpBBAvatarPath))) continue;

    if (copy($wbbAvatarPath, $phpBBAvatarPath))
    {
        //TODO: inserData Funktion nutzten
        $phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}users SET
            user_avatar_height = {$phpBBAvatar['height']},
            user_avatar_width = {$phpBBAvatar['width']},
            user_avatar = '".$phpBBAvatar['filename']."',
            user_avatar_type = 1
        WHERE
            user_id = {$phpBBAvatar['user_id']};");
    }
}

$wbbAvatars->close();