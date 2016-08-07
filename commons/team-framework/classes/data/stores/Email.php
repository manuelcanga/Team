<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
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

namespace team\data\stores;

/** Seguridad antes que nada  */


class Email  implements \team\interfaces\data\Store  {

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
	 function & import( $_origin, Array $_options = [], Array $_default = []) {
		return $_default;
     }

	function addTo($email, $name = '') { $this->to[] = ['email' => $email, 'name' => $name];}
	function from($email, $name = '') { $this->from = ['email' => $email, 'name' => $name]; }
	function replyTo($email, $name = '') { $this->reply = ['email' => $email, 'name' => $name]; }
	function addCurrent($email, $name = '') { $this->current = ['email' => $email, 'name' => $name];}


	function setSubject($subject) {
		$this->subject = $subject;
	}

    function export($_target, Array $_data = [], Array $_options = [] ) {

		//Si no hay una plantilla de correo, cogemos una genérica
		if(!isset($_data['view']) && !isset($_data['layout']) ) {
			$_data['view'] = \team\Filter::apply('\team\email\template', 'team:framework/data/email.tpl');
		}

		$emails = $this->to;
		if(empty($emails) ) return false;

		foreach($emails as $to) {
			$username = $to['name'];
			$useremail = $to['email'];
	        //Tenemos que generar que tendrá el correo electrónico
			$this->addCurrent($useremail, $username);			

			$email = new \team\Data($_data);
			$email['allData'] = $email->getData();
			$email['toemail'] = $useremail;
			$email['toname'] = $username;
			$body_html = $email->out('html');

			$body =  wordwrap($body_html, 70);

			 $status = mail($this->getTo(), $this->getSubject(), $body, $this->getHeaders() );
			$this->status[] = ['name' => $username, 'email' =>  $useremail, 'status' => $status ];
		}		

		return $this->status;
    }

	function getEmailHeader($target, $type) {
		if(empty($target) ) return '';

		$name = mb_encode_mimeheader($target['name'], "UTF-8", "B");
		return "{$type}:  \"$name\" <{$target['email']}>";
	}

	function getTo() {
		return $this->getEmailHeader($this->current, 'From');
	}

	function getReplyTo() {
		return $this->getEmailHeader($this->reply, 'Reply-To');
	}

	function getFrom() {
		return $this->getEmailHeader($this->from, 'From');
	}

	function getSubject() {
		$subject = $this->subject;
		$subject = str_replace('{$toname}', $this->current['name'], $subject);
		$subject = str_replace('{$toemail}', $this->current['email'], $subject);
	
		return '=?UTF-8?B?'.base64_encode($subject).'?=';
	}

	function getHeaders() {
		$headers[]  = 'MIME-Version: 1.0' . "\r\n";
		$headers[] = 'Content-type: text/html; charset=utf-8' . "\r\n";

		$from = $this->getFrom();
		if($from )
			$headers[] = $from;

		$replyTo = $this->getReplyTo();
		if($replyTo)
			$headers[] = $replyTo;


		$headers = \team\Filter::apply('\team\email\headers',$headers);


		return implode('\r\n', $headers);
	}
}
