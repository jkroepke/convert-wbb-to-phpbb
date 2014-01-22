<?php

$wbbAttachments    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_attachment;");

while($wbbAttachment = $wbbAttachments->fetch_assoc())
{
    $wbbAttachmentPath = $wbbPath.'wcf/attachments/attachment-'.$wbbAttachment['attachmentID'];
    if(!file_exists($wbbAttachmentPath))
    {
        //Sometimes, the wbb table contains non exists attachments
        continue;
    }

    $phpBBAttachment = array(
        'attach_id'         => $wbbAttachment['attachmentID'],
        'post_msg_id'       => $wbbAttachment['containerID'],
        'in_message'        => ($wbbAttachment['containerType'] == 'pm' ? 1 : 0),
        'poster_id'         => $wbbAttachment['userID'],
        'is_orphan'         => 0,
        'physical_filename' => $phpBBDb->real_escape_string($wbbAttachment['userID']."_".md5(unique_id())),
        'real_filename'     => $phpBBDb->real_escape_string($wbbAttachment['attachmentName']),
        'download_count'    => $wbbAttachment['downloads'],
        'extension'         => $phpBBDb->real_escape_string(substr(strrchr($wbbAttachment['attachmentName'], '.'), 1)),
        'mimetype'          => $wbbAttachment['fileType'],
        'filesize'          => $wbbAttachment['attachmentSize'],
        'filetime'          => $wbbAttachment['uploadTime']
    );

    $phpBBAttachmentPath = $phpBBPath.$phpBBConfig['upload_path'].'/'.$phpBBAttachment['physical_filename'];

    //TODO: phpBB Pfade vielleicht leeren.
    if ((is_readable($wbbAttachmentPath) || @chmod($wbbAttachmentPath, 0777)) && copy($wbbAttachmentPath, $phpBBAttachmentPath))
    {
        insertData('attachments', $phpBBAttachment);
    }
    else
    {
        throw new Exception("No read access for file '{$wbbAttachmentPath}'!");
    }

    output('row');
}

$wbbAttachments->close();
output('end');