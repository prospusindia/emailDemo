<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//ini_set("SMTP","ssl://smtp.gmail.com");
//for demo purposes we are gonna send an email to ourselves
$from = "kunalprospus@gmail.com";
$to = "amit.prospus@gmail.com";
//$to = "kunalprospus@gmail.com";
//$from = "amit.prospus@gmail.com";
$envelope = array("from"=>$from,"to"=>$to);
$subject = "Testing One";
$htmlmsg = "
First mail compose.
";
$headers = "From: ".$from."\r\n".
           "Reply-To: ".$from."\r\n";
//$headers .= "MIME-Version: 1.0\r\n"; 
//$headers .= "Content-Type: text/plain; charset=utf-8\r\n"; 
//$headers .= "X-Priority: 1\r\n"; 
$cc = '';
$bcc = '';
$return_path = $from;
$body = array();
//send the email using IMAP
$part1["type"]          = 'TYPETEXT';
$part1["subtype"]       = "plain";
$part2["type"]          = 'TYPEAPPLICATION';
$part2["encoding"]      = 'ENCBINARY';
$part2["subtype"]       = "octet-stream";
$part2["description"]   = '';
$part2["contents.data"] = '';
$part3["type"]          = 'TYPETEXT';
$part3["subtype"]       = "plain";
$part3["description"]   = "description3";
$part3["contents.data"] = $htmlmsg;

$body[1] = $part3;
$body[2] = $part2;
$body[3] = $part3;
$message = imap_mail_compose($envelope, $body);
list($msgheader, $msgbody) = preg_split("#\n\s*\n#Uis", $message);

//s$a = mail($to, $subject, $msgbody, $headers);
imap_mail($to, $subject, $msgbody, $headers);
//var_dump(imap_last_error());
//echo "tst Email sent!<br />";
//die;
// connect to the email account
$mbox = imap_open("{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail", $from, "Prospus1");
var_dump(imap_last_error());
// save the sent email to your Sent folder by just passing a string composed 
// of the entire message + headers. 
// Notice the 'r' format for the date function, which formats the date correctly for messaging.
imap_append($mbox, "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail",
     "From: ".$from."\r\n".     
     "To: ".$to."\r\n".
     "Subject: ".$subject."\r\n".
     "Date: ".date("r", strtotime("now"))."\r\n".
     "\r\n".
     $htmlmsg.
     "\r\n"
     );
var_dump(imap_last_error());
//echo "Message saved to Send folder!<br />";

$some   = imap_search($mbox, 'SUBJECT "'.$subject.'"', SE_UID);
print_r($some);
imap_close($mbox);

?>