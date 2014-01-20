<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 14.01.14
 * Time: 02:33
 */


function convertBBCode($text, $convertConfig = array())
{
    global $phpBBConfig;
    //TODO: Convert between WBB's and phpBB's bbcode syntax (i.e. [attach]<id>[/attach] -> [attachment=<id>][/attachment])

    $convertConfig  = array(
        'enableBBCodes' => true,
        'enableSmilies' => true,
    ) + $convertConfig;


    return array(
        'text'         	=> $text,
        'checksum'     	=> md5($text),
        'bitfield'   	=> '',
        'uid'        	=> substr(base_convert(unique_id(), 16, 36), 0, BBCODE_UID_LEN),
    );

    //TODO: Fix database connections .....
    //Fatal error: Call to a member function sql_query() on a non-object in .\phpBB3\includes\message_parser.php on line 150

    $message_parser = new parse_message();
    $message_parser->message = str_replace('"', '&quot;', html_entity_decode($text));
    $message_parser->parse($phpBBConfig['allow_bbcode'] ? ($convertConfig['enableBBCodes'] ? true : false) : false, $phpBBConfig['allow_post_links'], $convertConfig['enableSmilies'] ? true : false);

    return array(
        'text'         	=> $message_parser->message,
        'checksum'     	=> md5($message_parser->message),
        'bitfield'   	=> $message_parser->bbcode_bitfield,
        'uid'        	=> $message_parser->bbcode_uid,
    );
}

function insertData($table, $data)
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "INSERT INTO {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "´".$key."´ = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).';';
	$phpBBDb->query($sql);
}

function updateData($table, $data, $where = '1=1')
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "UPDATE {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "´".$key."´ = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).' ';
	$sql	.= "WHERE {$where};";

	$phpBBDb->query($sql);
}

function exception_handler(Exception $exception) {
    echo "[ERROR] ", $exception->getMessage(), "\n";
}