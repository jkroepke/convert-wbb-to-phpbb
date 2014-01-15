<?php

$wbbAttachments    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_avatar;");

while($wbbAttachment = $wbbAttachments->fetch_assoc())
{
    $phpBBAttachment = array(
        'attachmentID'      => $wbbAttachment['attachmentID'],
        'post_msg_id'       => $wbbAttachment['containerID'],
        'in_message'        => ($wbbAttachment['containerType'] == 'pm' ? 1 : 0),
        'poster_id'         => $wbbAttachment['userID'],
        'physical_filename' => $wbbAttachment['userID']."_".md5(unique_id()),
        'real_filename'     => $wbbAttachment['attachmentName'],
        'download_count'    => $wbbAttachment['downloads'],
        'extension'         => substr(strrchr($wbbAttachment['attachmentName'],'.'),1),
        'mimetype'          => $wbbAttachment['fileType'],
        'filesize'          => $wbbAttachment['attachmentsSize'],
        'filetime'          => $wbbAttachment['uploadTime']
    );
    //TODO: Am anfang des Convert schecken, ob die Pfade less und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.

    $wbbAttachmentPath = $wbbPath.'wcf/attachments/attachment-'.$wbbAttachment['attachmentID'];
    $phpBBAttachmentPath = $phpBBPath.$phpBBConfig['upload_path'].'/'.$phpBBAttachment['physical_filename'];

    if (copy($wbbAttachmentPath, $phpBBAttachmentPath))
    {
        //TODO: inserData Funktion nutzten
        $phpBBDb->query("INSERT INTO {$phpBBMySQLConnection['prefix']}attachments
            (attach_id, post_msg_id, in_message, poster_id, is_orphan, physical_filename, real_filename, download_count, extension, mimetype, filesize, filetime)
        VALUES
            ({$phpBBAttachment['attachmentID']}, {$phpBBAttachment['post_msg_id']}, {$phpBBAttachment['in_message']} {$phpBBAttachment['poster_id']}, 0, '{$phpBBAttachment['physical_filename']}', '{$phpBBAttachment['real_filename']}', {$phpBBAttachment['download_count']}, '{$phpBBAttachment['extension']}', '{$phpBBAttachment['mimetype']}', {$phpBBAttachment['filesize']}, {$phpBBAttachment['filetime']})
        ;");
    }

}

$wbbAttachments->close();