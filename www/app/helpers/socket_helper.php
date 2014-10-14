<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function fsk_open($host, $path, $parm='', $header='', $port=80, $retime=30)    {
    $method = ($header || $parm) ? 'POST' : 'GET';

    $fp    = fsockopen    ($host,    $port, $errno, $errstr,    $retime);
    if (!$fp)
        echo $errstr ."(".$errno.")<br/>\n";
    else {
        fputs ($fp, $method." ".$path." HTTP/1.1\r\n");
        fputs ($fp, "Host: ".str_replace('ssl://', '', $host)."\r\n");
        fputs ($fp, "User-Agent: Mozilla/4.0\r\n");
        
        if ($method == 'POST') {
            if ($header) fputs ($fp, $header."\r\n");
            fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
            fputs ($fp, "Content-Length: ".strlen($parm)."\r\n");
            fputs ($fp, "Connection: close\r\n\r\n");
            if ($parm) fputs ($fp, $parm."\r\n");
        }
        else
            fputs($fp, "Content-Type: text/html\r\n\r\n");

        // HEADER 제거
        while(trim(fgets($fp,128)) != '') {}

        $str = '';
        while (!feof($fp)) {
            $str .=    fgets($fp,128);
        }
        fclose($fp);
    }

    return $str;
}
?>