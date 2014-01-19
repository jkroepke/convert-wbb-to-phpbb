<?php

$wbbAvatars    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_avatar;");

while($wbbAvatar = $wbbAvatars->fetch_assoc())
{
    $phpBBAvatar = array(
        'user_avatar_height'    => $wbbAvatar['height'],
        'user_avatar_width'     => $wbbAvatar['width'],
        'user_avatar'           => $phpbbDb->real_escape_string($phpBBConfig['avatar_salt']."_".$wbbAvatar['userID'].".".$wbbAvatar['avatarExtension']),
        'user_avatar_type'      => 1
    );

    $wbbAvatarPath = $wbbPath.'wcf/images/avatars/avatar-'.$wbbAvatar['avatarID'].'.'.$wbbAvatar['avatarExtension'];
    $phpBBAvatarPath = $phpBBPath.$phpBBConfig['avatar_path'].'/'.$phpBBAvatar['user_avatar'];

    //TODO: phpBB Pfade vielleicht leeren.

    if (is_readable($wbbAvatarPath) || @chmod($wbbAvatarPath, 0777))
    {
        updateData('users', $phpBBAvatar, "userid = '".$wbbAvatar['userID']."'");
    }
    elseif (copy($wbbAvatarPath, $phpBBAvatarPath))
    {
        throw new Exception("No read access for file '{$wbbAvatarPath}'!");
    }

    echo '.';
}

$wbbAvatars->close();