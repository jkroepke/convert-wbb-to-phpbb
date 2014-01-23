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

    $text   = preg_replace("!\[url='([^']+)'\]!mu", '[url=$1]', $text);

    //TODO: Check permission to use BBCodes
    $convertConfig  = array(
            'enableBBCodes' => true,
            'enableSmilies' => true,
    ) + $convertConfig;


    $phpBBBitfield = new bitfield();

    $bbcodes = array(
        'quote'			=> array('bbcode_id' => 0,	'regexp' => array('#\[quote(?:=&quot;(.*?)&quot;)?\](.+)\[/quote\]#uise')),
        'b'				=> array('bbcode_id' => 1,	'regexp' => array('#\[b\](.*?)\[/b\]#uise')),
        'i'				=> array('bbcode_id' => 2,	'regexp' => array('#\[i\](.*?)\[/i\]#uise')),
        'url'			=> array('bbcode_id' => 3,	'regexp' => array('#\[url(=(.*))?\](?(1)((?s).*(?-s))|(.*))\[/url\]#uiUe')),
        'img'			=> array('bbcode_id' => 4,	'regexp' => array('#\[img\](.*)\[/img\]#uiUe')),
        'size'			=> array('bbcode_id' => 5,	'regexp' => array('#\[size=([\-\+]?\d+)\](.*?)\[/size\]#uise')),
        'color'			=> array('bbcode_id' => 6,	'regexp' => array('!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)\](.*?)\[/color\]!uise')),
        'u'				=> array('bbcode_id' => 7,	'regexp' => array('#\[u\](.*?)\[/u\]#uise')),
        'code'			=> array('bbcode_id' => 8,	'regexp' => array('#\[code(?:=([a-z]+))?\](.+\[/code\])#uise')),
        'list'			=> array('bbcode_id' => 9,	'regexp' => array('#\[list(?:=(?:[a-z0-9]|disc|circle|square))?].*\[/list]#uise')),
        'email'			=> array('bbcode_id' => 10,	'regexp' => array('#\[email=?(.*?)?\](.*?)\[/email\]#uise')),
        'flash'			=> array('bbcode_id' => 11,	'regexp' => array('#\[flash=([0-9]+),([0-9]+)\](.*?)\[/flash\]#uie')),
        'attachment'	=> array('bbcode_id' => 12,	'regexp' => array('#\[attachment=([0-9]+)\](.*?)\[/attachment\]#uise'))
    );

    foreach ($bbcodes as $bbcode_data)
    {
        foreach ($bbcode_data['regexp'] as $regexp)
        {
            if (preg_match($regexp, $text))
            {
                $phpBBBitfield->set($bbcode_data['bbcode_id']);
            }
        }
    }

    $bitfield = $phpBBBitfield->get_base64();

    return array(
        'text'         	=> $text,
        'checksum'     	=> md5($text),
        'bitfield'   	=> $bitfield,
        'uid'        	=> substr(base_convert(unique_id(), 16, 36), 0, BBCODE_UID_LEN),
    );
}

function insertData($table, $data)
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "INSERT INTO {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "`".$key."` = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).';';

    if (!$phpBBDb->query($sql))
    {
        throw new Exception(sprintf("[ERROR/PHPBB] MySQL error: %s\n\nQuery:%s", $phpBBDb->error, $sql));
    }
}

function updateData($table, $data, $where = '1=1')
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "UPDATE {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "`".$key."` = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).' ';
	$sql	.= "WHERE {$where};";

    if (!$phpBBDb->query($sql))
    {
        throw new Exception(sprintf("[ERROR/PHPBB] MySQL error: %s\n\nQuery:%s", $phpBBDb->error, $sql));
    }
}

function exception_handler(Exception $exception) {
    echo "\n\n[ERROR] ", $exception->getMessage(), "\n";
    exit(1);
}

function output($action)
{
    static $i = 0;

    switch($action)
    {
        case 'row':
            $i++;

            echo '.';

            if($i % 10 === 0)
            {
                echo " ";
            }

            if($i % 100 === 0)
            {
                echo "[{$i}]\n";
            }

            break;
        case 'end':
            if($i % 100 !== 0)
            {
                echo " [{$i}]";
            }
            $i = 0;
            break;
    }
}

function replaceInFile($path, $search, $replace, $usePreg = false)
{
    global $phpBBPath;

    $file = $phpBBPath.$path;

    if(!file_exists($file))
    {
        throw new Exception("Can not find file '{$file}'!");
    }

    if(!is_writeable($file))
    {
        throw new Exception("Can not write to file '{$file}'!");
    }

    $fileData = file_get_contents($file);
    if($usePreg)
    {
        //TODO: Add preg replace
    }
    else
    {
        $fileData = str_replace($search, $replace, $fileData);
    }

    file_put_contents($file, $fileData);
}