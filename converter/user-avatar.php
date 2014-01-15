<?php

if (!is_readable($wbbPath.'wcf/images/avatars'))
{
    throw new Exception("No read access for directory '{$wbbPath}wcf/images/avatars'!");
}
elseif (!is_writeable($phpBBPath.$phpBBConfig['avatar_path']))
{
    throw new Exception("No write access to diretory '{$phpBBPath}{$phpBBConfig['avatar_path']}'!");
}
else
{
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

        //TODO: phpBB Pfade vielleicht leeren.

        if (!is_readable($wbbAvatarPath))
        {
            throw new Exception("No read access for file '{$wbbAvatarPath}'!");
        }
        elseif (copy($wbbAvatarPath, $phpBBAvatarPath))
        {
            updateData('users', $phpBBAvatar, "userid = '".$wbbAvatar['userID']."'");
        }
    }

    $wbbAvatars->close();
}