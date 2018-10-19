<?php

//邮件处理类
include PATH_LC . '/org/mail/class.phpmailer.php';

class mail {

    static public $mail;
    public static $error;

    function send($tomail, $toname, $title, $body) {
        $this->config();
        $this->mail->Subject = $title; //邮件标题
        $this->mail->AltBody = strip_tags($body); // 客户端提示信息摘要内容
        $this->mail->Body = $body; //或正文内容
        $tomail = addslashes_d($tomail);
        $this->mail->to = array(
            array($tomail, $toname)
        );
        if ($this->mail->send()) {
            $this->error = $this->mail->errorInfo;
            return true;
        } else {
            $error = $this->mail->smtp->error;
            $this->error = $error['error'];
            return false;
        }
    }

    //配置参数
    private function config() {
        $this->mail = new PHPMailer();
        $this->mail->PluginDir = PATH_LC . '/org/mail/';
        $this->mail->SetLanguage("en", PATH_LC . '/org/mail/language/');
        $this->mail->Username = C("email_username"); //登录用户名
        $this->mail->Password = C("email_password"); //登录密码
        $this->mail->Host = C("email_host"); //SMTP服务器地址
        $this->mail->Port = C("email_port"); //SMTP服务器端口
        $this->mail->CharSet = C("mail_charset")?C("mail_charset"):"utf8"; //字符集
        $this->mail->AddReplyTo(C("replymail"), C("replyusername")); //回复时显示的用户名
        $this->mail->From = C("frommail"); //发件人邮箱地址
        $this->mail->FromName = C("fromname"); //发件人姓名

        $this->mail->IsSMTP(); //是SMTP协议
        $this->mail->SMTPAuth = true; // SMTP是否验证
        $mail->SMTPSecure = "ssl"; //SSL安全验证
        $this->mail->WordWrap = 50; //换行文字
        $this->mail->IsHTML(true); //以HTML形式发送邮件
    }

}

?>
