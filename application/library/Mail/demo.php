<?php

require 'PHPMailerAutoload.php';

$mail = new PHPMailer;
$mail->SMTPDebug = 3;
$mail->isSMTP();


$mail->SMTPAuth = true;
$mail->Host = "smtp.qq.com"; // 这个要到qq邮箱，设置-账户，开启smtp
$mail->Port = 25; //设置邮件服务器的端口，默认为25
$mail->Username = ""; // 邮箱名称
$mail->Password = ""; // 邮箱密码



$mail->From = ''; // 邮箱名称
$mail->FromName = ''; // 发信人名称
$mail->addAddress(""); // 发给谁
$mail->isHTML(true);
$mail->CharSet = "utf-8";
$mail->Subject = "test";
$mail->Body    = "test";

if(!$mail->send()) {
    echo '发送失败';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo '发送成功';
}