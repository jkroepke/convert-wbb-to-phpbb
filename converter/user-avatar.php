<?php

$wbbAvatars    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_avatar;");

while($wbbAvatar = $wbbAvatars->fetch_assoc())
{
    $phpBBAvatar = array(
        'user_avatar_height'    => $wbbAvatar['height'],
        'user_avatar_width'     => $wbbAvatar['width'],
        'user_avatar'           => $phpBBConfig['avatar_salt']."_".$wbbAvatar['userID'].".".$wbbAvatar['avatarExtension'],
        'user_avatar_type'      => 1
    );

    $wbbAvatarPath = $wbbPath.'wcf/images/avatars/avatar-'.$wbbAvatar['avatarID'].'.'.$wbbAvatar['avatarExtension'];
    $phpBBAvatarPath = $phpBBPath.$phpBBConfig['avatar_path'].'/'.$phpBBAvatar['user_avatar'];

    //TODO: Am Anfang des Converts checken, ob die Pfade les- und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.

    if(!is_readable($wbbAvatarPath)) continue;
    if(!is_writeable(dirname($phpBBAvatarPath))) continue;

    if (copy($wbbAvatarPath, $phpBBAvatarPath))
        updateData('users', $phpBBAvatar, "userid = '".$wbbAvatar['userID']."'");
}

$wbbAvatars->close();