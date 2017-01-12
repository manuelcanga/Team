<?php
/**
New Licence bsd:
Copyright (c) <2016>, Manuel Jesus Canga Muñoz
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
 * Neither the name of the trasweb.net nor the
names of its contributors may be used to endorse or promote products
derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */


namespace team\types;


class Email extends Type
{
    private $id = null;
    private $options = [];

    private $to = [];
    private $from = [];
    private $reply = [];
    private $subject = '';
    private $current = [];
    private $status = [];

    /**
     * Inicializamos el envío de correo
     *
     */
    public function initialize($id =  'email', array $options = []) {
        $this->id = $id;
        $this->options = $options;
    }

    public function to($email, $name = '') { $this->addTo($email, $name); }
    public function setTo($email, $name = '') { $this->addTo($email, $name); }
    public function addTo($email, $name = '') { $this->to[] = ['email' => $email, 'name' => $name];}
    public function from($email, $name = '') { $this->from = ['email' => $email, 'name' => $name]; }
    public function setFrom($email, $name = '') { $this->from($email, $name); }
    public function replyTo($email, $name = '') { $this->reply = ['email' => $email, 'name' => $name]; }
    public function setReplyTo($email, $name = '') { $this->replyTo($email, $name); }
    private function addCurrent($email, $name = '') { $this->current = ['email' => $email, 'name' => $name];}


    public  function setSubject($subject) {
        $this->subject = $subject;
    }

    public function send(Array $_data = [], $template = 'team:framework/data/email.tpl') {

        $emails = $this->to;
        if(empty($emails) ) return false;

        $view = \team\Config::get('EMAIL_TEMPLATE',$template , $this->id);

        foreach((array) $emails as $to) {

            \team\Context::open();

                $username = $to['name'];
                $useremail = $to['email'];
                //Tenemos que generar el correo electrónico que tendrá
                $this->addCurrent($useremail, $username);

                $email = new \team\Data($_data);
                $email['EMAIL'] = $_data;
                \team\Context::set('ToNAME', $username);
                \team\Context::set('ToEMAIL', $useremail);
                \team\Context::set('FromNAME',  $this->from['name']?? '');
                \team\Context::set('FromEMAIL',  $this->from['email']?? '');
                \team\Context::set('VIEW', $view);

                $body_html = $email->out('html');

                $body =  wordwrap($body_html, 70);

            \team\Context::close();





            $status = mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders() );
            $this->status[] = ['name' => $username, 'email' =>  $useremail, 'status' => $status ];
        }

        return new \team\db\Collection($this->status);
    }

    private function getFormatted($target) {
        if(empty($target) ) return '';

        $name = mb_encode_mimeheader($target['name'], "UTF-8", "B");

        return $formatted  = "\"$name\" <{$target['email']}>";
    }

    private function getEmailHeader($target, $type) {
        $formatted_target = $this->getFormatted($target);

        return $header = $header = "{$type}:  {$formatted_target}";
    }


    public function getTo() {
        return $this->getFormatted($this->current);
    }

    private function getToHeader() {
        return $this->getEmailHeader($this->current, 'To');
    }

    public function getReplyTo() {
        return $this->getFormatted($this->reply);
    }

    private function getReplyToHeader() {
        return $this->getEmailHeader($this->reply, 'Reply-To');
    }

    public function getFrom() {
        return $this->getFormatted($this->from);
    }

    private function getFromHeader() {
        return $this->getEmailHeader($this->from, 'From');
    }

    protected function getSubject() {
        $subject = $this->subject;
        $subject = str_replace('{$fromname}', $this->from['name'], $subject);
        $subject = str_replace('{$fromemail}', $this->from['email'], $subject);
        $subject = str_replace('{$toname}', $this->current['name'], $subject);
        $subject = str_replace('{$toemail}', $this->current['email'], $subject);

        return '=?UTF-8?B?'.base64_encode($subject).'?=';
    }

    private function getHeaders() {
        $headers[]  = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';


        $from = $this->getFromHeader();
        if($from )
            $headers[] = $from;

        $replyTo = $this->getReplyToHeader();
        if($replyTo)
            $headers[] = $replyTo;


        $headers = \team\Filter::apply('\team\email\headers',$headers);


        return implode("\r\n", $headers);
    }
}