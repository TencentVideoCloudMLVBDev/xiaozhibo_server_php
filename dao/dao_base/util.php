<?php


function CreateMd5Sign64($string)
{
    $m = md5($string, true);
    $d = unpack('N2', $m);
    $n = $d[1] << 32 | $d[2];
    return sprintf('%u', $n);
}

?>
