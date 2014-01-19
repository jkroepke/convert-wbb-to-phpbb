<?php

if (!is_readable($wbbPath.'wcf/attachments'))
{
    throw new Exception("No read access to directory '{$wbbPath}wcf/attachments'!");
}
elseif (!is_writeable($phpBBPath.$phpBBConfig['upload_path']))
{
    throw new Exception("No write access to directory '{$phpBBPath}{$phpBBConfig['upload_path']}'!");
}
else
{
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

        $wbbAttachmentPath = $wbbPath.'wcf/attachments/attachment-'.$wbbAttachment['attachmentID'];
        $phpBBAttachmentPath = $phpBBPath.$phpBBConfig['upload_path'].'/'.$phpBBAttachment['physical_filename'];

        //TODO: phpBB Pfade vielleicht leeren.

        if (!is_readable($wbbAttachmentPath))
        {
            throw new Exception("No read access for file '{$wbbAttachmentPath}'!");
        }
        elseif (copy($wbbAttachmentPath, $phpBBAttachmentPath))
        {
             insertData('attachments', $phpBBAttachment);
        }
        echo '.';
    }

    $wbbAttachments->close();
}