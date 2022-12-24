<?php
// ************************************
// This file is part of a package from:
// www.freecontactform.com
// See license for details

// Version for Form Creator

// ************************************
ini_set('max_execution_time', 300); // 5 minutes to allow time for file upload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
$lk = KEY;

$lang = getLangFile();

// *******************
// CHECK CONFIGURATION
// *******************
checkConfigurationExists($lang);

$expected_fields_check = checkFieldsExist($rules, $lang);

if($expected_fields_check != "") {
    $message = $lang->{'fieldConfigError'};
    $message .= "<ul>".$expected_fields_check."</ul>";
    exitFail($message);
}


// *******************
// VALIDATE THE FIELDS
// *******************
validateFields($rules, $lang);


// ***************
// CHECK RECAPTCHA
// ***************
$response_score = "";
if(doRecaptchaCheck()) {
    
    if (isset($_POST['recaptcha-token'])) {
        $recaptcha_token = $_POST['recaptcha-token'];
    } else {
        exitFail($lang->{'recaptchaMissing'});
    }

    $request_url = "https://www.google.com/recaptcha/api/siteverify?secret=".RECAPTCHA_SECRETKEY.
    "&response=".$recaptcha_token.
    "&remoteip=".$_SERVER['REMOTE_ADDR'];


    if(ini_get('allow_url_fopen') ) {
        $response = file_get_contents($request_url);
     } else {
        if(function_exists('curl_init')) {
            $response = curl_get_contents($request_url);
        } else {
           exitFail("PHP needs to have file_get_contents() or curl_init() available to work with reCAPTCHA.");
        }
    
     }

    $response_decoded = json_decode($response);
    $response_success = $response_decoded->success;
    $response_score = $response_decoded->score;

    if ($response_success === false) {
        $message = $lang->{'recaptchaReject'}."<br>";
        exitError($message);
    }

    if ($response_success === true && $response_score < RECAPTCHA_SCORE) {
        if(strtoupper(RECAPTCHA_ACCEPT_LOW_SCORE) == "NO") {
            // tell the client all is good, do not send any email
            echo 'Success. '.$lang->{'recaptchaPoorScore'};
            exit();
        }
    }

}


// ************
// CREATE EMAIL
// ************
$ss = cs();

require dirname(__FILE__).'/'.'classes/Exception.php';
require dirname(__FILE__).'/'.'classes/PHPMailer.php';
require dirname(__FILE__).'/'.'classes/SMTP.php';

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

try {
    
    if(strtoupper(SMTP_DEBUG) == "YES") {
        echo "Debug:<br>";
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    } 
    
    if (strtoupper(USE_SMTP) == "YES") {

        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;

        if (strtoupper(SMTP_AUTH) == "YES") {

            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            switch(SMTP_SECURE) {
                case "STARTTLS":
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                break;

                case "SMTPS":
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                break;
            }

        } else {
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;
        }
    } else {
        $mail->isMail();
    }

    // ATTACHMENTS
    if (filesExist()) {
        $added = array();
        foreach ($_FILES as $upload_field) {
            if (isset($_FILES) && count($_FILES) > 0) {
                foreach ($_FILES as $f) {
                    if (is_array($f['tmp_name'])) {
                        $tmpcount=0;
                        foreach ($f['tmp_name'] as $f2) {
                            if (strlen($f2) > 1) {
                                if(!in_array($f2, $added)) {
                                    $added[] = $f2;
                                    $mail->AddAttachment($f2, $f['name'][$tmpcount]);
                                }
                            }
                            $tmpcount++;
                        }
                    } else {
                        if (strlen($f['tmp_name']) > 1) {
                            if(!in_array($f['tmp_name'], $added)) {
                                $added[] = $f['tmp_name'];
                                $mail->AddAttachment($f['tmp_name'], $f['name']);
                            }
                        }
                    }
                }
            }
        }
    }

    // EMAIL FROM
    $email_from = EMAIL_FROM;
    if(useField($email_from)) {
        $email_from = getField($email_from);
    }
    $email_from_name = EMAIL_FROM_NAME;
    if(useField($email_from_name)) {
        $email_from_name = getField($email_from_name);
    }

    // validate email address
    if(!PHPMailer::validateAddress($email_from)) {
        exitError(str_replace(":value", "FROM", $lang->{'emailInvalid'}));
    }

    if($email_from_name == "") {
        $mail->setFrom($email_from);
    } else {
        $mail->setFrom($email_from, $email_from_name);
    }

    // EMAIL REPLY TO
    $email_reply_to = EMAIL_REPLY_TO;
    if(useField($email_reply_to)) {
        $email_reply_to = getField($email_reply_to);
    }
    $email_reply_to_name = EMAIL_REPLY_TO_NAME;
    if(useField($email_reply_to_name)) {
        $email_reply_to_name = getField($email_reply_to_name);
    }

    // validate email address
    if(!PHPMailer::validateAddress($email_reply_to)) {
        exitError(str_replace(":value", "REPLY TO", $lang->{'emailInvalid'}));
    }

    if($email_reply_to_name == "") {
        if($email_reply_to != "") {
            $mail->addReplyTo($email_reply_to);
        }
    } else {
        if($email_reply_to != "") {
            $mail->addReplyTo($email_reply_to, $email_reply_to_name);
        }
    }

    // EMAIL TO
    if(isMultiple(EMAIL_TO)) {
        $email_to_list = getMultiple(EMAIL_TO);
    } else {
        $email_to_list[0] = EMAIL_TO;
    }

    if(isMultiple(EMAIL_TO_NAME)) {
        $email_to_name_list = getMultiple(EMAIL_TO_NAME);
    } else {
        $email_to_name_list[0] = EMAIL_TO_NAME;
    }

    for($i=0; $i < count($email_to_list); $i++) {
        if(itemExistWithValue($email_to_name_list, $i)) {

            // validate email address
            if(!PHPMailer::validateAddress($email_to_list[$i])) {
                exitFail(str_replace(":value", "EMAIL TO", $lang->{'emailInvalid'}));
            }
            
            $mail->addAddress($email_to_list[$i], $email_to_name_list[$i]);
        } else {

            // validate email address
            if(!PHPMailer::validateAddress($email_to_list[$i])) {
                exitFail(str_replace(":value", "EMAIL TO", $lang->{'emailInvalid'}));
            }

            $mail->addAddress($email_to_list[$i]);
        }
    }

    // EMAIL TO CC
    if(isMultiple(EMAIL_TO_CC)) {
        $email_to_cc_list = getMultiple(EMAIL_TO_CC);
    } else {
        $email_to_cc_list[0] = EMAIL_TO_CC;
    }

    if(isMultiple(EMAIL_TO_CC_NAME)) {
        $email_to_cc_name_list = getMultiple(EMAIL_TO_CC_NAME);
    } else {
        $email_to_cc_name_list[0] = EMAIL_TO_CC_NAME;
    }

    for($i=0; $i < count($email_to_cc_list); $i++) {
        if(itemExistWithValue($email_to_cc_name_list, $i)) {

            // validate email address
            if(!PHPMailer::validateAddress($email_to_cc_list[$i])) {
                exitFail(str_replace(":value", "EMAIL TO CC", $lang->{'emailInvalid'}));
            }

            $mail->addCC($email_to_cc_list[$i], $email_to_cc_name_list[$i]);
        } else {
            if(itemExistWithValue($email_to_cc_list, $i)) {

                // validate email address
                if(!PHPMailer::validateAddress($email_to_cc_list[$i])) {
                    exitFail(str_replace(":value", "EMAIL TO CC", $lang->{'emailInvalid'}));
                }
                
                $mail->addCC($email_to_cc_list[$i]);
            }
        }
    }

    // EMAIL TO BCC
    if(isMultiple(EMAIL_TO_BCC)) {
        $email_to_bcc_list = getMultiple(EMAIL_TO_BCC);
    } else {
        $email_to_bcc_list[0] = EMAIL_TO_BCC;
    }

    if(isMultiple(EMAIL_TO_BCC_NAME)) {
        $email_to_bcc_name_list = getMultiple(EMAIL_TO_BCC_NAME);
    } else {
        $email_to_bcc_name_list[0] = EMAIL_TO_BCC_NAME;
    }

    for($i=0; $i < count($email_to_bcc_list); $i++) {
        if(itemExistWithValue($email_to_bcc_name_list, $i)) {

            // validate email address
            if(!PHPMailer::validateAddress($email_to_bcc_list[$i])) {
                exitFail(str_replace(":value", "EMAIL TO BCC", $lang->{'emailInvalid'}));
            }

            $mail->addBCC($email_to_bcc_list[$i], $email_to_bcc_name_list[$i]);
        } else {
            if(itemExistWithValue($email_to_bcc_list, $i)) {

                // validate email address
                if(!PHPMailer::validateAddress($email_to_bcc_list[$i])) {
                    exitFail(str_replace(":value", "EMAIL TO BCC", $lang->{'emailInvalid'}));
                }
                
                $mail->addBCC($email_to_bcc_list[$i]);
            }
        }
    }

    $email_subject = EMAIL_SUBJECT;
    if(useField($email_subject)) {
        $email_subject = getField($email_subject);
    }

    $mail->Subject = trim(EMAIL_SUBJECT_BEFORE." ".$email_subject ." ".EMAIL_SUBJECT_AFTER);

    $mail->isHTML(true); 
    $mail->Body = getHtmlBody($rules, $response_score);
    $mail->AltBody = getPlainBody($rules, $response_score);

    $mail->send();

    if(strtoupper(SMTP_DEBUG) == "YES") {
        echo $mail->ErrorInfo;
    }

    // send the auto-response
    if(SEND_AUTO_RESPONSE == "YES") {
        $mail->clearAddresses();
        $mail->clearAttachments();
        $mail->Subject = EMAIL_OUT_SUBJECT;
        
        $email_out_to = EMAIL_OUT_TO;
        if(useField($email_out_to)) {
            $email_out_to = getField($email_out_to);
        }
        $email_out_to_name = EMAIL_OUT_TO_NAME;
        if(useField($email_out_to_name)) {
            $email_out_to_name = getField($email_out_to_name);
        }
        $mail->addAddress($email_out_to, $email_out_to_name);

        $mail->setFrom(EMAIL_OUT_FROM, EMAIL_OUT_FROM_NAME);

        $body_out = getAutoResponseContent();
        $mail->Body = $body_out["html"];
        $mail->AltBody = $body_out["text"];

        $mail->send();
    }

    if(strtoupper(SMTP_DEBUG) == "YES") {
        $message = $mail->ErrorInfo;
        exitFail($message);
    }

    isSuccess();

} catch (Exception $e) {
    if(strtoupper(SMTP_DEBUG) == "YES") {
        $message = $mail->ErrorInfo;
        exitFail($message);
    } else {
        exitFail($lang->{'tryLater'});
    }
}


// ***********************
// EMAIL CONTENT FUNCTIONS
// ***********************
function getHtmlBody($rules, $score) {
    return getEmailBody($rules, $score, 'htm');
}

function getPlainBody($rules, $score) {
    return getEmailBody($rules, $score, 'txt');
}

function getEmailBody($rules, $score, $type) {
    if($type=="htm") {
        $ss = "b";
        $body = file_get_contents('./email-templates/'.EMAIL_TEMPLATE_IN_HTML);
    }  else {
        $ss = "a"; 
        $body = file_get_contents('./email-templates/'.EMAIL_TEMPLATE_IN_TEXT);
    }
    foreach($_POST as $field => $value) {
        if($field == "recaptcha-token") { continue; }
        if(is_array($value)) {
            $value = implode(", ",$value);
        }
        $field_to_find = "{".$field."}";
        if($type=="htm") {
            $field_to_replace = nl2br(htmlspecialchars($value),false);
        }  else {
            $field_to_replace = $value;
        }
        $body = str_replace($field_to_find, $field_to_replace, $body);
    }
    foreach($rules as $rulefield => $ruleoptions) {
        if(isset($ruleoptions['required']) && $ruleoptions['required'] == false) {
            $body = str_replace("{".$rulefield."}", ' ', $body);
        }
    }

    $body = str_replace(array('{IP}','{SCORE}'), array(getUserIp(),$score), $body);
    return str_replace('{CREDIT}', getSsValue($ss), $body);
}

function getAutoResponseContent() {
    $html = file_get_contents('./email-templates/'.EMAIL_TEMPLATE_OUT_HTML);
    $text = file_get_contents('./email-templates/'.EMAIL_TEMPLATE_OUT_TEXT);
    foreach($_POST as $field => $value) {
        if($field == "recaptcha-token") { continue; }
        if(is_array($value)) {
            $value = implode(", ",$value);
        }
        $field_to_find = "{".$field."}";
        $html = str_replace($field_to_find, nl2br(htmlspecialchars($value),false), $html);
        $text = str_replace($field_to_find, $value, $text);
    }
    $html = str_replace('{EMAIL_OUT_FROM_NAME}', EMAIL_OUT_FROM_NAME, $html);
    $text = str_replace('{EMAIL_OUT_FROM_NAME}', EMAIL_OUT_FROM_NAME, $text);
    return array(
        "html" => $html,
        "text" => $text
    );
}


// ********************
// SUPPORTING FUNCTIONS
// ********************
function checkConfigurationExists($lang) {
    if(!defined('EMAIL_TO') || 
        !defined('A') || 
        !defined('B') || 
        !defined('C') || 
        !defined('D') || 
        !defined('F')) {
        exitFail($lang->{'configMissing'});
    }
}

function checkFieldsExist($rules, $lang) {
    $returnstring = "";
    foreach($rules as $field => $options) {
        if(!isset($_POST[$field]) && !isset($_FILES[$field])) {
            if(isset($options['required']) && $options['required'] == true) {
                $returnstring .= "<li>".str_replace(":field", $field, $lang->{'fieldMissing'})."</li>";
            }
        }
    }
    return $returnstring;
}

function validateFields($rules, $lang) {
    
    require dirname(__FILE__).'/classes/FormValidate.php';
    $validate = new FormValidate;
    $validate->setLang($lang);

    foreach($rules as $real_name => $field_rules) {
        $field_value = '';
        if(isset($_POST[$real_name])) {
            $field_value = $_POST[$real_name];
        }
        if(isset($_FILES[$real_name])) {
            $field_value = $_FILES[$real_name];
        }
        $validate->validate($field_value, $field_rules);
    }
    
    if($validate->anyErrors()) {
        $message =  "<ul>".$validate->getErrorString()."</ul>";
        exitError($message);
    }
}

function getLangFile() {
    $langfile = file_get_contents(dirname(__FILE__).'/js/lang/fcf.'.LANG.'.js');
    $langfile = str_replace(array('var lang = ',';'), array('',''), $langfile);
    $json = json_decode($langfile);
    $server = $json->{'server'};
    return $server;
}

function filesExist() {
    if (isset($_FILES) && count($_FILES) > 0) {
        foreach ($_FILES as $f) {
            if (is_array($f['tmp_name'])) {
                foreach ($f['tmp_name'] as $f2) {
                    if (strlen($f2) > 1) {
                        return true;
                    }
                }
            } else {
                if (strlen($f['tmp_name']) > 1) {
                    return true;
                }
            }
        }
    }
    return false;
}

function doRecaptchaCheck() {
    if(trim(RECAPTCHA_SITEKEY) == "" || 
        trim(RECAPTCHA_SECRETKEY) == "" ||
        trim(RECAPTCHA_SCORE) == "") {
        return false;
    }
    return true;
}

function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function useField($value) {
    if(substr($value,0,6) == "FIELD:") {
        return true;
    }
    return false;
}

function getField($value) {
    $field = explode(":", $value);
    return $_POST[$field[1]];
}

function isMultiple($value) {
    $fields = explode(",", $value);
    if(count($fields) > 1) {
        return true;
    }
    return false;
}

function getMultiple($values) {
    $fields = explode(",", $values);
    return array_map('trim', $fields);
}

function itemExistWithValue($element, $index) {
    if(isset($element[$index]) && trim($element[$index]) != "") {
        return true;
    }
    return false;
}

function exitFail($message) {
    echo "Fail:".$message;
    exit();
}

function exitError($message) {
    echo "Error:".$message;
    exit();
}

function getUserIp() {
    $ip = getenv('HTTP_CLIENT_IP')?:
    getenv('HTTP_X_FORWARDED_FOR')?:
    getenv('HTTP_X_FORWARDED')?:
    getenv('HTTP_FORWARDED_FOR')?:
    getenv('HTTP_FORWARDED')?:
    getenv('REMOTE_ADDR');
    if(trim($ip) == "") {
        return "Unavailable";
    }
    return $ip;
}

function isSuccess() {
    if(strtoupper(SMTP_DEBUG) == "YES") {
        exit();
    }
    if(defined('THANK_YOU_PAGE')) {
        if(strlen(THANK_YOU_PAGE) > 0) {
            echo 'URL:'.THANK_YOU_PAGE;
            exit();
        }
    }
    echo base64_decode("U3VjY2Vzcy4=").getSsValue("c");
    exit();
}

function getSsValue($i) {
    global $ss;
    if(substr($ss[$i],0,2) != 'Y2') {
        return base64_decode($ss[$i]);
    }
    return "";
}


if (strlen($lk) < 4) {
    scf(1);
}
function scf($n) {
    exitFail("Security check failure ($n)");
}
function cs() {
    global $lk;
    if (abc($lk)) {
        return array("a" => A, "b" => B, "c" => C);
    }
    if (cba($lk)) {
        return array("a" => D, "b" => D, "c" => D);
    }
    scf(2);
}
function abc($f) {
    if (strtoupper($f) == base64_decode(F)) {
        return true;
    }
    return false;
}
function cba($h) {
    if (substr($h, 4, 13) == base64_decode(E)) {
        return true;
    }
    return false;
}