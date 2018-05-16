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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.body
 * Type:     block
 * Name:     body
 * Purpose:  output a body tag
 * -------------------------------------------------------------
 */

function smarty_block_body($params, $content, Smarty_Internal_Template $template, &$repeat)
{

    $place = 'body';
    if(isset($params['place'])) {
        $place = $params['place'];
        unset($params['place']);
    }

    $out = '';
	if($repeat) { //open tag
		return $out;
	}else {//close tag


		//Atributos del body
		$app = \Team\System\Context::get('APP');
		$component = \Team\System\Context::get('COMPONENT');
		$response = \Team\System\Context::get('RESPONSE');

		$default = [
			 'id' =>  $app.'_'.$component,
			 'data-app' => $app,
			 'data-response' => $response,
		];


		/* Body Classes */
		$body_classes = \Team\System\Context::get('BODY_CLASSES');

        if( \Team\Client\Http::checkUserAgent('mobile') ) {
            $body_classes[] = 'movil';
        }else {
            $body_classes[] = 'desktop';
        }
        $body_classes[] = \Team\Client\Http::checkUserAgent('navigator');
        $body_classes[] = $params['class']?? '';

        $params['class'] = \Team\Gui\Place::getClasses($place,$body_classes);

        if(empty( $params['class'] )) {
            unset($params['class']);
        }


        $out .= '<body';
		$params =  \Team\Data\Filter::apply('\team\tag\body\params', $params + $default);
		foreach($params as $attr => $value) {
				$out .= " {$attr}='{$value}'";
		}
		$out .= '>';


		$out .=  trim(\team\data\Filter::apply('\team\tag\body', $content, $params, $template));


		/* ******************** BOTTOM CSS Y JS FILES *************** */

		//BOTTOM CSS
		$css_files =  \Team\Config::get('\team\css\bottom', []);

		if(!empty($css_files) ) {
			foreach($css_files as $id => $file) {
				$out .="<link href='{$file}' rel='stylesheet'/>";
			}
		}
		
		//BOTTOM JS
		$js_files =  \Team\Config::get('\team\js\bottom', []);

		if(!empty($js_files) ) {
			foreach($js_files as $id => $file) {
				$out .=	"<script src='{$file}'></script>";
			}
		}

		/* ******************** /BOTTOM CSS Y JS FILES *************** */

        $out = \Team\Gui\Place::getHtml($place, $out, $params, $template);
		$out .= '</body></html>';



		return $out;
	}


}
