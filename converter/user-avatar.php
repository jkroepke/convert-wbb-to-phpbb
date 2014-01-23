<?php

$wbbAvatars    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_avatar;");

while($wbbAvatar = $wbbAvatars->fetch_assoc())
{
    $phpBBAvatar = array(
        'user_avatar_height'    => $wbbAvatar['height'],
        'user_avatar_width'     => $wbbAvatar['width'],
        'user_avatar'           => $phpBBDb->real_escape_string($wbbAvatar['userID']."_".time().".".$wbbAvatar['avatarExtension']),
        'user_avatar_type'      => 1
    );

    $wbbAvatarPath = $wbbPath.'wcf/images/avatars/avatar-'.$wbbAvatar['avatarID'].'.'.$wbbAvatar['avatarExtension'];
    $phpBBAvatarPath = $phpBBPath.$phpBBConfig['avatar_path'].'/'.$phpBBConfig['avatar_salt']."_".$wbbAvatar['userID'].".".$wbbAvatar['avatarExtension'];

    //TODO: phpBB Pfade vielleicht leeren.

    if ((is_readable($wbbAvatarPath) || @chmod($wbbAvatarPath, 0777)) && copy($wbbAvatarPath, $phpBBAvatarPath))
    {
        updateData('users', $phpBBAvatar, "user_id = '".$wbbAvatar['userID']."'");
    }
    else
    {
        throw new Exception("No read access for file '{$wbbAvatarPath}'!");
    }

    output('row');
}

$wbbAvatars->close();
output('end');