<?php

require_once dirname(__FILE__) . '/common/Common.php';
require_once dirname(__FILE__) . '/common/GlobalFunctions.php';

$request = file_get_contents("php://input");
interface_log(INFO, EC_OK, 'request:' . $request);


$mtime = explode(' ', microtime());

$start = $mtime[1] + $mtime[0];

Process($request, $interfaceName, $result, $retval);

interface_log(INFO, EC_OK, "response(" . strlen($result) . "):" . $result);

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("Content-Length:" . strlen($result));
echo $result;


function Process($request,
                 &$interfaceName,
                 &$result,
                 &$retval)
{


    $data = json_decode($request, true);

    $interfaceName = str_replace("/", "", $_SERVER['PATH_INFO']);
    $instance = instance($interfaceName);

    if (!$instance) {
        $errorMsg = genErrMsg(EC_BAD_REQUEST, ParaStrFilter($interfaceName));
        interface_log(ERROR, EC_BAD_REQUEST, $errorMsg);
        $result = genErrorResult(EC_BAD_REQUEST, $errorMsg);
        header("Content-Length:" . strlen($result));
        $retval = EC_BAD_REQUEST;
        return;
    }

    if (!$instance->initialize()) {
        $result = $instance->renderOutput();
        header("Content-Length:" . strlen($result));
        $retval = $instance->getRetValue();
        return;
    }

    if (!$instance->verifyInput($data)) {
        $result = $instance->renderOutput();
        header("Content-Length:" . strlen($result));
        $retval = $instance->getRetValue();
        return;
    }

    if (!$instance->verifySign($request, $_SERVER['HTTP_LITEAV_SIG'])) {
        $errorMsg = genErrMsg(EC_SIGN_ERROR, ParaStrFilter($interfaceName));
        interface_log(ERROR, EC_SIGN_ERROR, $errorMsg);
        $result = genErrorResult(EC_SIGN_ERROR, $errorMsg);
        header("Content-Length:" . strlen($result));
        $retval = EC_SIGN_ERROR;
        return;
    }
    if (!$instance->process()) {
        $result = $instance->renderOutput();
        header("Content-Length:" . strlen($result));
        $retval = $instance->getRetValue();
        return;
    }

    $result = $instance->renderOutput();
    $retval = 0;

    return;
}

?>
