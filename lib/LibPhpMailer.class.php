<?php

/**
  +------------------------------------------------------------------------------
 * 邮箱发送
  +------------------------------------------------------------------------------
 * @param string $tomail 接收邮件的邮箱
 * @param string $title 邮件标题，不要太长，太长接收到的邮件标题可能出现乱码
 * @param string $content 邮件内容
 * @return boolean true表示发送成功，false表示发送失败
  +------------------------------------------------------------------------------
 * 使用方法
  //发送一封邮件
  LibPhpMailer::$web_name = '网站名称';
  LibPhpMailer::$smtp = 'smtp.163.com';
  LibPhpMailer::$send_mail = '发送者的邮箱';
  $email_user = explode('@', LibPhpMailer::$send_mail);
  LibPhpMailer::$email_username = $email_user[0];
  LibPhpMailer::$email_password = '邮箱密码';
  LibPhpMailer::$re_email = '收件人回复时调用的邮箱';
  LibPhpMailer::$accepter_user_name = '收件人姓名';

  //$content = file_get_contents(绝对路径 . '邮件模板页面.html');
  //邮件的内容，比如，邮件模板内容如下：
  $content = <<< HTML
  <dl>
  <dt>标题</dt>
  <dd><%{title}%></dd>
  <dt>内容</dt>
  <dd><%{content}%></dd>
  </dl>
  HTML;

  $content = str_replace('<%{title}%>', '这是标题', $content);
  $content = str_replace('<%{content}%>', '这是内容', $content);
  LibPhpMailer::sendmail('接收者的邮箱', '测试邮件（QQ邮件最好不要超过8个汉字，否则可能出现乱码）', $content);
  +------------------------------------------------------------------------------
 */
class LibPhpMailer {

    static public $web_name = '';
    static public $charset = 'UTF-8';
    static public $smtp = '';
    static public $mail_port = 25;
    static public $send_mail = '';
    static public $email_username = '';
    static public $email_password = '';
    static public $re_email = '';
    static public $accepter_user_name = '';
    /**
     * 发邮件参数配置
     * @param string $tomail 接收者邮箱
     * @param string $title 邮件标题
     * @param string $content 邮件内容
     * @param string $replay_email 接收者邮箱
     * @return boolean true|false 真或假
     */
    static public function sendmail($tomail, $title, $content) {
        require LIB_ROOT_BASE . '/phpmailer/PHPMailerAutoload.php';

        $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.qq.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = self::$send_mail;                 // SMTP username
        $mail->Password = self::$email_password;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        $mail->setFrom(self::$send_mail, $title);
        $mail->addAddress($tomail, self::$accepter_user_name);     // Add a recipient
        $mail->addAddress($tomail);               // Name is optional
        $mail->addReplyTo(self::$re_email, self::$web_name);
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = $title;
        $mail->Body = $content;
        $mail->AltBody = strip_tags($content);

        if (!$mail->send()) {
            //echo 'Message could not be sent.';
            //echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            //echo 'Message has been sent';
            return true;
        }
    }

}
