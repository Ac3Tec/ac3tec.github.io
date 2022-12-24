<?php
// ************************************
// This file is part of a package from:
// www.freecontactform.com
// See license for details
// February 2021
// ************************************


// ***********
// LICENSE KEY
// ***********
define('KEY', 'fc2-8feGwRbHw28Fn-X9EBKJUQVC9GA');


// ***********
// LANGUAGE
// ***********
define('LANG','en');


// *********************
// FORM FIELD VALIDATION
// *********************
$rules = array(
  "Name" => array(
    "required" => true,
    "label" => "Your Name",
    "maxLength" => 60
  ),
  "Email" => array(
    "required" => true,
    "label" => "Your Email Address",
    "maxLength" => 100,
    "email" => true
  ),
  "Phone" => array(
    "required" => false,
    "label" => "Your Phone Number (Optional)"
  ),
  "Subject" => array(
    "required" => true,
    "label" => "Message Subject"
  ),
  "Message" => array(
    "required" => true,
    "label" => "Message",
    "maxLength" => 3000
  )
);


// ******************
// THANK YOU PAGE
// ******************
define('THANK_YOU_PAGE','');


// **************************
// EMAIL TEMPLATES - INCOMING
// **************************
define('EMAIL_TEMPLATE_IN_HTML', 'fcf.email-in.htm');
define('EMAIL_TEMPLATE_IN_TEXT', 'fcf.email-in.txt');


// *******************************
// EMAIL TEMPLATES - AUTO-RESPONSE
// *******************************
define('EMAIL_TEMPLATE_OUT_HTML', 'fcf.email-out.htm');
define('EMAIL_TEMPLATE_OUT_TEXT', 'fcf.email-out.txt');

define('SEND_AUTO_RESPONSE', 'YES'); // YES OR NO
define('EMAIL_OUT_SUBJECT', 'IT Ace - Contact Form Received');
define('EMAIL_OUT_TO', 'FIELD:Email');
define('EMAIL_OUT_TO_NAME', 'FIELD:Name');
define('EMAIL_OUT_FROM', 'noreply@itace.net');
define('EMAIL_OUT_FROM_NAME', '');


// *************
// EMAIL MESSAGE
// *************
define('EMAIL_TO', 'admin@itace.net');
define('EMAIL_TO_NAME', 'admin@itace.net');

define('EMAIL_TO_CC', '');
define('EMAIL_TO_CC_NAME', '');

define('EMAIL_TO_BCC', '');
define('EMAIL_TO_BCC_NAME', '');

define('EMAIL_FROM', 'noreply@itace.net');
define('EMAIL_FROM_NAME', 'noreply@itace.net');

define('EMAIL_REPLY_TO', 'FIELD:Email');
define('EMAIL_REPLY_TO_NAME', 'FIELD:Email');

define('EMAIL_SUBJECT_BEFORE', '');
define('EMAIL_SUBJECT', "IT Ace - Contact Form");
define('EMAIL_SUBJECT_AFTER', '');



// ***************
// EMAIL TRANSPORT
// ***************
define('USE_SMTP', 'YES'); // YES or NO
define('SMTP_HOST', 'smtppro.zoho.com');
define('SMTP_USER', 'andrew@itace.net');
define('SMTP_PASS', '7aG3gWuFxN7e');
define('SMTP_AUTH', 'YES');
define('SMTP_SECURE', 'SMTPS'); // STARTTLS, SMTPS (port 465) or empty
define('SMTP_PORT', '465');
define('SMTP_DEBUG', 'NO'); // YES or NO


// ************
// reCAPTCHA V3
// ************
define('RECAPTCHA_SITEKEY', '6Ld8MpwcAAAAAFEKeDx0Tc1SiDe_XGjHtiSi-_3Z');
define('RECAPTCHA_SECRETKEY', '6Ld8MpwcAAAAAGOnTxlLnuo4xFDWPuwfwh8kJpLm');
define('RECAPTCHA_SCORE', '0.5');
define('RECAPTCHA_ACCEPT_LOW_SCORE', 'NO'); // YES or NO



// **************************
//    DON'T CHANGE BELOW
// USED FOR VALIDATION CHECKS
// **************************
define('A', 'Rm9ybSBwcm92aWRlZCBieSB3d3cuZnJlZWNvbnRhY3Rmb3JtLmNvbQ==');
define('B', 'Rm9ybSBwcm92aWRlZCBieSA8YSBocmVmPSJodHRwczovL3d3dy5mcmVlY29udGFjdGZvcm0uY29tIj5GcmVlQ29udGFjdEZvcm0uY29tPC9hPg==');
define('C', 'Rm9ybSBwcm92aWRlZCBieSA8YSBocmVmPSJodHRwczovL3d3dy5mcmVlY29udGFjdGZvcm0uY29tIiB0YXJnZXQ9Il9ibGFuayI+RnJlZUNvbnRhY3RGb3JtLmNvbTwvYT4=');
define('D', 'Y29uZ3JhdHVsYXRpb25zIGZvciBiZWluZyBjbGV2ZXIh');
define('E', 'OGZlR3dSYkh3MjhGbg==');
define('F', 'RlJFRQ==');