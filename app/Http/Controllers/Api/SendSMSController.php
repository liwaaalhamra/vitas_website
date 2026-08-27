<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SendSMSController extends Controller
{

    public function index($mobile_number, $reset_code) {
        header('Content-Type: text/html; charset=utf-8');
        function fsockopenTest()
        {
            $testValue = 0;
            if(function_exists("fsockopen"))
                ++$testValue;
            if(function_exists("fputs"))
                ++$testValue;
            if(function_exists("feof"))
                ++$testValue;
            if(function_exists("fread"))
                ++$testValue;
            if(function_exists("fclose"))
                ++$testValue;
            return $testValue;
        }


        function curlTest()
        {
            $testValue = 0;
            if(function_exists("curl_init"))
                ++$testValue;
            if(function_exists("curl_setopt"))
                ++$testValue;
            if(function_exists("curl_exec"))
                ++$testValue;
            if(function_exists("curl_close"))
                ++$testValue;
            if(function_exists("curl_errno"))
                ++$testValue;
            return $testValue;
        }


        function fopenTest()
        {
            $testValue = 0;
            if(function_exists("fopen"))
                ++$testValue;
            if(function_exists("fclose"))
                ++$testValue;
            if(function_exists("fread"))
                ++$testValue;
            return $testValue;
        }


        function fileTest()
        {
            $testValue = 0;
            if(function_exists("file"))
                ++$testValue;
            if(function_exists("http_build_query"))
                ++$testValue;
            if(function_exists("stream_context_create"))
                ++$testValue;
            return $testValue;
        }


        function filegetcontentsTest()
        {
            $testValue = 0;
            if(function_exists("file_get_contents"))
                ++$testValue;
            if(function_exists("http_build_query"))
                ++$testValue;
            if(function_exists("stream_context_create"))
                ++$testValue;
            return $testValue;
        }

        if(fsockopenTest() == 5)
        {

            function sendStatus($viewResult=1)
            {
                global $arraySendStatus;
                $fsockParameter = "POST /api/sendStatus.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: 0 \r\n\r\n";
                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendStatus);
                return $result;
            }


            function changePassword($userAccount, $passAccount, $newPassAccount, $viewResult=1)
            {
                global $arrayChangePassword;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&newPassword=".$newPassAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&newPassword=".$newPassAccount;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/changePassword.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";
                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);
                $result = "";
                $clearResult = false;
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayChangePassword);
                return $result;
            }


            function forgetPassword($userAccount, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $stringToPost = "mobile=".$userAccount."&type=".$sendType;
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/forgetPassword.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayForgetPassword);
                return $result;
            }


            function forgetPasswordApiKey($apiKey, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $stringToPost = "apiKey=".$apiKey."&type=".$sendType;
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/forgetPassword.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayForgetPassword);
                return $result;
            }


            function balanceSMS($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayBalance;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/balance.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;
                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayBalance, 'Balance');
                return $result;
            }


            function sendSMS($userAccount, $passAccount, $numbers, $sender, $msg, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsg;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/msgSend.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn, $fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsg);
                return $result;
            }


            function sendSMSWK($userAccount, $passAccount, $numbers, $sender, $msg, $msgKey, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsgWK;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&msgKey=".$msgKey."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&msgKey=".$msgKey."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/msgSendWK.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn, $fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsgWK);
                return $result;
            }


            function deleteSMS($userAccount, $passAccount, $deleteKey=0, $viewResult=1)
            {
                global $arrayDeleteSMS;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&deleteKey=".$deleteKey;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&deleteKey=".$deleteKey;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/deleteMsg.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayDeleteSMS);
                return $result;
            }


            function addSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&sender=".$sender;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&sender=".$sender;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/addSender.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";
                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayAddSender, 'Normal');
                return $result;
            }


            function activeSender($userAccount, $passAccount, $senderId, $activeKey, $viewResult=1)
            {
                global $arrayActiveSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&senderId=".$senderId."&activeKey=".$activeKey;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&senderId=".$senderId."&activeKey=".$activeKey;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/activeSender.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost \r\n";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayActiveSender);
                return $result;
            }


            function checkSender($userAccount, $passAccount, $senderId, $viewResult=1)
            {
                global $arrayCheckSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&senderId=".$senderId;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&senderId=".$senderId;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/checkSender.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckSender);
                return $result;
            }


            function addAlphaSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&sender=".$sender;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&sender=".$sender;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/addAlphaSender.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost \r\n";

                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayAddAlphaSender);
                return $result;
            }


            function checkAlphasSender($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayCheckAlphasSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount;
                }
                $stringToPostLength = strlen($stringToPost);
                $fsockParameter = "POST /api/checkAlphasSender.php HTTP/1.0\r\n";
                $fsockParameter.= "Host: www.alfa-cell.com \r\n";
                $fsockParameter.= "Content-type: application/x-www-form-urlencoded \r\n";
                $fsockParameter.= "Content-length: $stringToPostLength \r\n\r\n";
                $fsockParameter.= "$stringToPost \r\n";
                $fsockConn = fsockopen("www.alfa-cell.com", 80, $errno, $errstr, 10);
                fputs($fsockConn,$fsockParameter);

                $result = "";
                $clearResult = false;

                while(!feof($fsockConn))
                {
                    $line = fgets($fsockConn, 10240);
                    if($line == "\r\n" && !$clearResult)
                        $clearResult = true;

                    if($clearResult)
                        $result .= trim($line);
                }

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckAlphasSender, 'Senders');
                return $result;
            }
        }
        elseif(curlTest() == 5)
        {


            function sendStatus($viewResult=1)
            {
                global $arraySendStatus;
                $url = "http://www.alfa-cell.com/api/sendStatus.php";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendStatus);
                return $result;
            }

            function changePassword($userAccount, $passAccount, $newPassAccount, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                $url = "http://www.alfa-cell.com/api/changePassword.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&newPassword=".$newPassAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&newPassword=".$newPassAccount;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayChangePassword);
                return $result;
            }

            function forgetPassword($userAccount, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $stringToPost = "mobile=".$userAccount."&type=".$sendType;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayForgetPassword);
                return $result;
            }

            function forgetPasswordApiKey($apiKey, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $stringToPost = "apiKey=".$apiKey."&type=".$sendType;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayForgetPassword);
                return $result;
            }

            function balanceSMS($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayBalance;
                $url = "http://www.alfa-cell.com/api/balance.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayBalance, 'Balance');
                return $result;
            }

            function sendSMS($userAccount, $passAccount, $numbers, $sender, $msg, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsg;
                $url = "http://www.alfa-cell.com/api/msgSend.php";
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsg);
                return $result;
            }

            function sendSMSWK($userAccount, $passAccount, $numbers, $sender, $msg, $msgKey, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsgWK;
                $url = "http://www.alfa-cell.com/api/msgSendWK.php";
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&msgKey=".$msgKey."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&numbers=".$numbers."&sender=".$sender."&msg=".$msg."&msgKey=".$msgKey."&timeSend=".$timeSend."&dateSend=".$dateSend."&applicationType=".$applicationType."&domainName=".$domainName."&msgId=".$MsgID."&deleteKey=".$deleteKey."&lang=3";
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsgWK);
                return $result;
            }

            function deleteSMS($userAccount, $passAccount, $deleteKey=0, $viewResult=1)
            {
                global $arrayDeleteSMS;
                $url = "http://www.alfa-cell.com/api/deleteMsg.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&deleteKey=".$deleteKey;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&deleteKey=".$deleteKey;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayDeleteSMS);
                return $result;
            }

            function addSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddSender;
                $url = "http://www.alfa-cell.com/api/addSender.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&sender=".$sender;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&sender=".$sender;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayAddSender, 'Normal');
                return $result;
            }

            function activeSender($userAccount, $passAccount, $senderId, $activeKey, $viewResult=1)
            {
                global $arrayActiveSender;
                $url = "http://www.alfa-cell.com/api/activeSender.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&senderId=".$senderId."&activeKey=".$activeKey;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&senderId=".$senderId."&activeKey=".$activeKey;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayActiveSender);
                return $result;
            }

            function checkSender($userAccount, $passAccount, $senderId, $viewResult=1)
            {
                global $arrayCheckSender;
                $url = "http://www.alfa-cell.com/api/checkSender.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&senderId=".$senderId;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&senderId=".$senderId;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckSender);
                return $result;
            }

            function addAlphaSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                $url = "http://www.alfa-cell.com/api/addAlphaSender.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount."&sender=".$sender;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount."&sender=".$sender;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayAddAlphaSender);
                return $result;
            }

            function checkAlphasSender($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayCheckAlphasSender;
                $url = "http://www.alfa-cell.com/api/checkAlphasSender.php";
                if(!empty($userAccount) && empty($passAccount)) {
                    $stringToPost = "apiKey=".$userAccount;
                } else {
                    $stringToPost = "mobile=".$userAccount."&password=".$passAccount;
                }
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $stringToPost);
                $result = curl_exec($ch);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckAlphasSender, 'Senders');
                return $result;
            }
        }
        elseif(fopenTest() == 3)
        {

            function sendStatus($viewResult=1)
            {
                global $arraySendStatus;
                $contextOptions['http'] = array('method' => 'GET', 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/sendStatus.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);

                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendStatus);
                return $result;
            }

            function changePassword($userAccount, $passAccount, $newPassAccount, $viewResult=1)
            {
                global $arrayChangePassword;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'newPassword'=>$newPassAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'newPassword'=>$newPassAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/changePassword.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result), $arrayChangePassword);
                return $result;
            }

            function forgetPassword($userAccount, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function forgetPasswordApiKey($apiKey, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('apiKey'=>$apiKey, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function balanceSMS($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayBalance;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/balance.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result), $arrayBalance, 'Balance');
                return $result;
            }

            function sendSMS($userAccount, $passAccount, $numbers, $sender, $msg, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsg;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSend.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendMsg);
                return $result;
            }

            function sendSMSWK($userAccount, $passAccount, $numbers, $sender, $msg, $msgKey, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsgWK;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSendWK.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsgWK);
                return $result;
            }

            function deleteSMS($userAccount, $passAccount, $deleteKey=0, $viewResult=1)
            {
                global $arrayDeleteSMS;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'deleteKey'=>$deleteKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'deleteKey'=>$deleteKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/deleteMsg.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayDeleteSMS);
                return $result;
            }

            function addSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addSender.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result), $arrayAddSender, 'Normal');
                return $result;
            }

            function activeSender($userAccount, $passAccount, $senderId, $activeKey, $viewResult=1)
            {
                global $arrayActiveSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/activeSender.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayActiveSender);
                return $result;
            }

            function checkSender($userAccount, $passAccount, $senderId, $viewResult=1)
            {
                global $arrayCheckSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'senderId'=>$senderId));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkSender.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckSender);
                return $result;
            }

            function addAlphaSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addAlphaSender.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayAddAlphaSender);
                return $result;
            }

            function checkAlphasSender($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayCheckAlphasSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }

                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkAlphasSender.php";
                $handle = fopen($url, 'r', false, $contextResouce);
                $result = stream_get_contents($handle);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckAlphasSender, 'Senders');
                return $result;
            }
        }
        elseif(fileTest() == 3)
        {

            function sendStatus($viewResult=1)
            {
                global $arraySendStatus;
                $contextOptions['http'] = array('method' => 'GET', 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/sendStatus.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendStatus);
                return $result;
            }

            function changePassword($userAccount, $passAccount, $newPassAccount, $viewResult=1)
            {
                global $arrayChangePassword;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'newPassword'=>$newPassAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'newPassword'=>$newPassAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/changePassword.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayChangePassword);
                return $result;
            }

            function forgetPassword($userAccount, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function forgetPasswordApiKey($apiKey, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('apiKey'=>$apiKey, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function balanceSMS($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayBalance;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/balance.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayBalance, 'Balance');
                return $result;
            }

            function sendSMS($userAccount, $passAccount, $numbers, $sender, $msg, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsg;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSend.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendMsg);
                return $result;
            }

            function sendSMSWK($userAccount, $passAccount, $numbers, $sender, $msg, $msgKey, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsgWK;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSendWK.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsgWK);
                return $result;
            }

            function deleteSMS($userAccount, $passAccount, $deleteKey=0, $viewResult=1)
            {
                global $arrayDeleteSMS;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'deleteKey'=>$deleteKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'deleteKey'=>$deleteKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/deleteMsg.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayDeleteSMS);
                return $result;
            }

            function addSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addSender.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayAddSender, 'Normal');
                return $result;
            }

            function activeSender($userAccount, $passAccount, $senderId, $activeKey, $viewResult=1)
            {
                global $arrayActiveSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/activeSender.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayActiveSender);
                return $result;
            }

            function checkSender($userAccount, $passAccount, $senderId, $viewResult=1)
            {
                global $arrayCheckSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'senderId'=>$senderId));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkSender.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckSender);
                return $result;
            }

            function addAlphaSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addAlphaSender.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = $arrayResult[0];

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayAddAlphaSender);
                return $result;
            }

            function checkAlphasSender($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayCheckAlphasSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkAlphasSender.php";
                $arrayResult = file($url, FILE_IGNORE_NEW_LINES, $contextResouce);
                $result = implode('',$arrayResult);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckAlphasSender, 'Senders');
                return $result;
            }
        }
        elseif(filegetcontentsTest() == 3)
        {

            function sendStatus($viewResult=1)
            {
                global $arraySendStatus;
                $contextOptions['http'] = array('method' => 'GET', 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/sendStatus.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendStatus);
                return $result;
            }

            function changePassword($userAccount, $passAccount, $newPassAccount, $viewResult=1)
            {
                global $arrayChangePassword;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'newPassword'=>$newPassAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'newPassword'=>$newPassAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/changePassword.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayChangePassword);
                return $result;
            }

            function forgetPassword($userAccount, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function forgetPasswordApiKey($apiKey, $sendType, $viewResult=1)
            {
                global $arrayForgetPassword;
                $contextPostValues = http_build_query(array('apiKey'=>$apiKey, 'type'=>$sendType));
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/forgetPassword.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result), $arrayForgetPassword);
                return $result;
            }

            function balanceSMS($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayBalance;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/balance.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result), $arrayBalance, 'Balance');
                return $result;
            }

            function sendSMS($userAccount, $passAccount, $numbers, $sender, $msg, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsg;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount,'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSend.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result), $arraySendMsg);
                return $result;
            }

            function sendSMSWK($userAccount, $passAccount, $numbers, $sender, $msg, $msgKey, $MsgID, $timeSend=0, $dateSend=0, $deleteKey=0, $viewResult=1)
            {
                global $arraySendMsgWK;
                $applicationType = "68";
                $sender = urlencode($sender);
                $domainName = $_SERVER['SERVER_NAME'];
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'numbers'=>$numbers, 'sender'=>$sender, 'msg'=>$msg, 'msgKey'=>$msgKey, 'timeSend'=>$timeSend, 'dateSend'=>$dateSend, 'applicationType'=>$applicationType, 'domainName'=>$domainName, 'msgId'=>$MsgID, 'deleteKey'=>$deleteKey,'lang'=>'3'));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/msgSendWK.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arraySendMsgWK);
                return $result;
            }

            function deleteSMS($userAccount, $passAccount, $deleteKey=0, $viewResult=1)
            {
                global $arrayDeleteSMS;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'deleteKey'=>$deleteKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'deleteKey'=>$deleteKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/deleteMsg.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayDeleteSMS);
                return $result;
            }

            function addSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addSender.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result), $arrayAddSender, 'Normal');
                return $result;
            }

            function activeSender($userAccount, $passAccount, $senderId, $activeKey, $viewResult=1)
            {
                global $arrayActiveSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId, 'activeKey'=>$activeKey));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/activeSender.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayActiveSender);
                return $result;
            }

            function checkSender($userAccount, $passAccount, $senderId, $viewResult=1)
            {
                global $arrayCheckSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'senderId'=>$senderId));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'senderId'=>$senderId));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkSender.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckSender);
                return $result;
            }

            function addAlphaSender($userAccount, $passAccount, $sender, $viewResult=1)
            {
                global $arrayAddAlphaSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount, 'sender'=>$sender));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount, 'sender'=>$sender));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/addAlphaSender.php";
                $result = file_get_contents($url, false, $contextResouce);

                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayAddAlphaSender);
                return $result;
            }

            function checkAlphasSender($userAccount, $passAccount, $viewResult=1)
            {
                global $arrayCheckAlphasSender;
                if(!empty($userAccount) && empty($passAccount)) {
                    $contextPostValues = http_build_query(array('apiKey'=>$userAccount));
                } else {
                    $contextPostValues = http_build_query(array('mobile'=>$userAccount, 'password'=>$passAccount));
                }
                $contextOptions['http'] = array('method' => 'POST', 'header'=>'Content-type: application/x-www-form-urlencoded', 'content'=> $contextPostValues, 'max_redirects'=>0, 'protocol_version'=> 1.0, 'timeout'=>10, 'ignore_errors'=>TRUE);
                $contextResouce  = stream_context_create($contextOptions);
                $url = "http://www.alfa-cell.com/api/checkAlphasSender.php";
                $result = file_get_contents($url, false, $contextResouce);
                if($viewResult)
                    $result = printStringResult(trim($result) , $arrayCheckAlphasSender, 'Senders');
                return $result;
            }
        }
        else
        {
            echo "There is no method available<br>Please contact your server supput to activate one of those methods: fsockopen or curlSMS or fopen or file or fopenSMS";
        }


        function printStringResult($apiResult, $arrayMsgs, $printType = 'Normal')
        {
            global $undefinedResult;

            switch ($printType)
            {
                case 'Alpha':
                {
                    if(array_key_exists($apiResult, $arrayMsgs))
                        return $arrayMsgs[$apiResult];
                    else
                        return $arrayMsgs[0];
                }
                break;

                case 'Balance':
                {
                    if(array_key_exists($apiResult, $arrayMsgs))
                        return $arrayMsgs[$apiResult];
                    else
                    {
                        list($originalAccount, $currentAccount) = explode("/", $apiResult);
                        if(!empty($originalAccount) && !empty($currentAccount))
                        {
                            return sprintf($arrayMsgs[3], $currentAccount, $originalAccount);
                        }
                        else
                            return $arrayMsgs[0];
                    }
                }
                break;

                case 'Senders':
                {
                    $apiResult = str_replace('[pending]', '[pending]<br>', $apiResult);
                    $apiResult = str_replace('[active]', '<br>[active]<br>', $apiResult);
                    $apiResult = str_replace('[notActive]', '<br>[notActive]<br>', $apiResult);
                    return $apiResult;
                }
                break;

                case 'Normal':
                {
                    if($apiResult[0] != '#')
                        return $arrayMsgs[$apiResult];
                    else
                        return $apiResult;
                }
                break;
            }
        }

        $undefinedResult = "The result of the operation is undefined, please try again";


        $arraySendStatus = array();
        $arraySendStatus[0] = "We are sory, you can't send now";
        $arraySendStatus[1] = "You can send the message now";


        $arrayChangePassword = array();
        $arrayChangePassword[0] = "Connection failed to alfa-cell.com server";
        $arrayChangePassword[1] = "Your mobile number (userName) is Invalid";
        $arrayChangePassword[2] = "Invalid old password";
        $arrayChangePassword[3] = "Your password has been changed successfully";
        $arrayChangePassword[4] = "Invalid new password";


        $arrayForgetPassword = array();
        $arrayForgetPassword[0] = "Connection failed to alfa-cell.com server";
        $arrayForgetPassword[1] = "Your mobile number (userName) is Invalid";
        $arrayForgetPassword[2] = "Your email is unavailable";
        $arrayForgetPassword[3] = "The password has been sent to your mobile number successfully";
        $arrayForgetPassword[4] = "Your balance is not enough to send the new password as SMS";
        $arrayForgetPassword[5] = "The password has been sent to your email successfully";
        $arrayForgetPassword[6] = "Your mobile number (userName) is Invalid";
        $arrayForgetPassword[7] = "Your email is incorrect";


        $arraySendMsg = array();
        $arraySendMsg[0] = "Connection failed to alfa-cell.com server";
        $arraySendMsg[1] = "SMS message sent successfully";
        $arraySendMsg[2] = "Your balance is 0";
        $arraySendMsg[3] = "Your balance is not enough";
        $arraySendMsg[4] = "Your mobile number (userName) is Invalid";
        $arraySendMsg[5] = "Your Password is incorrect";
        $arraySendMsg[6] = "Sms send operation failed, try again later";
        $arraySendMsg[7] = "The schools system is unavailable";
        $arraySendMsg[8] = "Repetition of the school code for the same user";
        $arraySendMsg[9] = "Trial version is expired ";
        $arraySendMsg[10] = "The count of mobile number does not match the count of messages";
        $arraySendMsg[11] = "Your subscription does not allow you to send messages to this school";
        $arraySendMsg[12] = "Incorrect portal version";
        $arraySendMsg[13] = "Your number does not active or the (BS) symbol is missing in the end of the message";
        $arraySendMsg[14] = "Sender Name not accepted, or you not authorized to perform this action";
        $arraySendMsg[15] = "Number(s) is empty or incorrect";
        $arraySendMsg[16] = "Sender Name is empty or invalid";
        $arraySendMsg[17] = "Incorrect message encode";
        $arraySendMsg[18] = "Sending stoped from the provider";
        $arraySendMsg[19] = "No applicationType";
        $arraySendMsg[101]= "Sending by API is disabled.";
        $arraySendMsg[102]= "Your IP is not authorized to use this API";
        $arraySendMsg[103]= "The country from which it was sent is not defined within the countries allowed to be sent";

        $arrayDeleteSMS = array();
        $arrayDeleteSMS[1] = "Deleted successfully";
        $arrayDeleteSMS[2] = "Your mobile number (userName) is Invalid";
        $arrayDeleteSMS[3] = "Invalid Password";
        $arrayDeleteSMS[4] = "Invalid deleteKey";


        $arrayBalance = array();
        $arrayBalance[0] = "Connection failed to alfa-cell.com server";
        $arrayBalance[1] = "Your mobile number (userName) is Invalid";
        $arrayBalance[2] = "Invalid Password";
        $arrayBalance[3] = "Your balance is %s point(s) from %s point(s)";


        $arrayCheckAlphasSender = array();
        $arrayCheckAlphasSender[0] = "Connection failed to alfa-cell.com server";
        $arrayCheckAlphasSender[1] = "Your mobile number (userName) is Invalid";
        $arrayCheckAlphasSender[2] = "Invalid Password";


        $arrayAddAlphaSender = array();
        $arrayAddAlphaSender[0] = "Connection failed to alfa-cell.com server";
        $arrayAddAlphaSender[1] = "Your mobile number (userName) is Invalid";
        $arrayAddAlphaSender[2] = "Invalid Password";
        $arrayAddAlphaSender[3] = "The length of the sender name is greater than 11 character";
        $arrayAddAlphaSender[4] = "Your request has been added successfully";


        $arrayAddSender = array();
        $arrayAddSender[0] = "Connection failed to alfa-cell.com server";
        $arrayAddSender[1] = "Your mobile number (userName) is Invalid";
        $arrayAddSender[2] = "Invalid Password";
        $arrayAddSender[3] = "Sender name 'international number' is incorrect";
        $arrayAddSender[4] = "Your name does not need to activate! ";
        $arrayAddSender[5] = "Your balance is not enough to send the activation code";
        $arrayAddSender[6] = "Invalid Password";


        $arrayCheckSender = array();
        $arrayCheckSender[0] = "Sender name is not activated";
        $arrayCheckSender[1] = "Sender name is activated";
        $arrayCheckSender[2] = "Sender name rejected";
        $arrayCheckSender[3] = "Your mobile number (userName) is Invalid";
        $arrayCheckSender[4] = "Invalid Password";
        $arrayCheckSender[5] = "Invalid senderId";


        $arrayActiveSender = array();
        $arrayActiveSender[0] = "Connection failed to alfa-cell.com server";
        $arrayActiveSender[1] = "Your mobile number (userName) is Invalid";
        $arrayActiveSender[2] = "Invalid Password";
        $arrayActiveSender[3] = "Sender name is activated";
        $arrayActiveSender[4] = "Invalid activation code";
        $arrayActiveSender[5] = "Invalid senderId";


        $arraySendMsgWK = array();
        $arraySendMsgWK[0] = "Connection failed to alfa-cell.com server";
        $arraySendMsgWK[1] = "Successful send operation";
        $arraySendMsgWK[2] = "Your balance is 0";
        $arraySendMsgWK[3] = "Your balance is not enough";
        $arraySendMsgWK[4] = "Your mobile number (userName) is Invalid";
        $arraySendMsgWK[5] = "Your Password is incorrect";
        $arraySendMsgWK[6] = "Sms send operation failed, try again later";
        $arraySendMsgWK[7] = "The schools system is unactivated";
        $arraySendMsgWK[8] = "Repetition of the school code for the same user";
        $arraySendMsgWK[9] = "Trial version is expired";
        $arraySendMsgWK[10] = "The count of mobile number does not match the count of messages";
        $arraySendMsgWK[11] = "Your subscription does not allow you to send messages to this school";
        $arraySendMsgWK[12] = "Incorrect portal version";
        $arraySendMsgWK[13] = "Your number does not active or the (BS) symbol is missing in the end of the message";
        $arraySendMsgWK[14] = "Sender Name not accepted, or you not authorized to perform this action";
        $arraySendMsgWK[15] = "Number(s) is empty or incorrect";
        $arraySendMsgWK[16] = "Sender Name is empty or invalid";
        $arraySendMsgWK[17] = "Incorrect message encode";
        $arraySendMsgWK[18] = "Sending stoped from the provider";
        $arraySendMsgWK[19] = "No applicationType";
        $arraySendMsgWK[101]= "Sending by API is disabled.";
        $arraySendMsgWK[102]= "Your IP is not authorized to use this API";
        $arraySendMsgWK[103]= "The country from which it was sent is not defined within the countries allowed to be sent";

        $mobile = "vitasapp";
        $password = "vitasapp2020";
        $apiKey = "";
        $sender = "VITAS"; // 11 Characters
        $numbers = $mobile_number;
        $msg = "Your verification code is: " . $reset_code;


        $MsgID = rand(1,99999);

        $timeSend = 0;

        $dateSend = 0;

        $deleteKey = 152485;

        $resultType = 0;

        sendSMS($mobile, $password, $numbers, $sender, $msg, $MsgID, $timeSend, $dateSend, $deleteKey, $resultType);
    }

}
