<?php

class Mail_mail {

    protected $subject;
    protected $addr_to;
    protected $text_body;
    protected $text_encoded;
    protected $mime_headers;
    protected $mime_boundary = "--==================_846811060==_";
    protected $smtp_headers;

    public function __construct($subject,$to,$from,$msg,$filename,$downfilename,$mimetype = "application/octet-stream",$mime_filename = false) {
        $this->subject = $subject;
        $this->addr_to = $to;
        $this->smtp_headers = $this->write_smtpheaders($from);
        $this->text_body = $this->write_body($msg);
        $this->text_encoded = $this->attach_file($filename,$downfilename,$mimetype,$mime_filename);
        $this->mime_headers = $this->write_mimeheaders($filename, $mime_filename);
    }

    public function attach_file($filename,$downfilename,$mimetype,$mime_filename) {
        $encoded = $this->encode_file($filename);
        if ($mime_filename) $filename = $mime_filename;
        $out = "--" . $this->mime_boundary . "\n";
        $out = $out . "Content-type: " . $mimetype . "; name=\"$filename\";\n";
        $out = $out . "Content-Transfer-Encoding: base64\n";
        $out = $out . "Content-disposition: attachment; filename=\"$downfilename\"\n\n";
        $out = $out . $encoded . "\n";
        $out = $out . "--" . $this->mime_boundary . "--" . "\n";
        return $out;
    }

    public function sendfile() {
        $headers = $this->smtp_headers . $this->mime_headers;
        $message = $this->text_body . $this->text_encoded;
        mail($this->addr_to,$this->subject,$message,$headers);
    }

    public function write_body($msgtext) {
        $out = "--" . $this->mime_boundary . "\n";
        $out = $out . "Content-Type: text/plain; charset=\"utf-8\"\n\n";
        $out = $out . $msgtext . "\n";
        return $out;
    }

    public function encode_file($sourcefile) {
        if (is_readable($sourcefile)) {
            $fd = fopen($sourcefile, "r");
            $contents = fread($fd, filesize($sourcefile));
            $encoded = chunk_split(base64_encode($contents));
            fclose($fd);
        }
        return $encoded;
    }

    public function write_mimeheaders($filename, $mime_filename) {
        if ($mime_filename) $filename = $mime_filename;
        $out = "MIME-version: 1.0\n";
        $out = $out . "Content-type: multipart/mixed; ";
        $out = $out . "boundary=\"$this->mime_boundary\"\n";
        $out = $out . "Content-transfer-encoding: 7BIT\n";
        $out = $out . "X-attachments: $filename;\n\n";
        return $out;
    }

    public function write_smtpheaders($addr_from) {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n"; // Additional headers
        $headers .= 'from:' . $addr_from . "\r\n";
        return $headers;
    }
}

/**

header("Content-type: text/html; charset=utf-8");

//主題
$subject = "=?UTF-8?B?" . base64_encode('这是一封测试邮件') . "?=";

//收件人
$sendto = 'molaifeng@foxmail.com';

//發件人
$replyto = 'molaifeng@foxmail.com';

//內容
$message = "这是一封测试邮件";

//附件
$filename = '1.zip';

$excelname = '1.zip';

//附件類別
$mimetype = "application/octet-stream";

$mailfile = new Mail_mail($subject, $sendto, $replyto, $message, $filename, $excelname, $mimetype);
$mailfile->sendfile();
*/ 