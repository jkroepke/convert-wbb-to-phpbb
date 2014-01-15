<?php

$wbbAttachments    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_attachment;");

while($wbbAttachment = $wbbAttachments->fetch_assoc())
{
    $phpBBAttachment = array(
        'attach_id'      => $wbbAttachment['attachmentID'],
        'post_msg_id'       => $wbbAttachment['containerID'],
        'in_message'        => ($wbbAttachment['containerType'] == 'pm' ? 1 : 0),
        'poster_id'         => $wbbAttachment['userID'],
        'is_orphan'         => 0,
        'physical_filename' => $wbbAttachment['userID']."_".md5(unique_id()),
        'real_filename'     => $wbbAttachment['attachmentName'],
        'download_count'    => $wbbAttachment['downloads'],
        'extension'         => substr(strrchr($wbbAttachment['attachmentName'],'.'),1),
        'mimetype'          => $wbbAttachment['fileType'],
        'filesize'          => $wbbAttachment['attachmentsSize'],
        'filetime'          => $wbbAttachment['uploadTime']
    );
    //TODO: Am Anfang des Converts checken, ob die Pfade les- und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.

    $wbbAttachmentPath = $wbbPath.'wcf/attachments/attachment-'.$wbbAttachment['attachmentID'];
    $phpBBAttachmentPath = $phpBBPath.$phpBBConfig['upload_path'].'/'.$phpBBAttachment['physical_filename'];

    if (copy($wbbAttachmentPath, $phpBBAttachmentPath))
        insertData('attachments', $phpBBAttachment);

}

$wbbAttachments->close();