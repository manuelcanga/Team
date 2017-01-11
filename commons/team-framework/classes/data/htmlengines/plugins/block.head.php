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
 * File:     block.head
 * Type:     block
 * Name:     head
 * Purpose:  output a dtd tag + head tag
 * -------------------------------------------------------------
 */

function smarty_block_head($params, $content, Smarty_Internal_Template $template, &$repeat)
{

	if($repeat) { //open tag
        return '';

	}else {//close tag
        $responsive = '';
        if(isset($params['responsive']) ) {
            $responsive = "<meta name='viewport' content='width=device-width, initial-scale=1.0' />";
            unset($params['responsive']);
        }


        /* ******************** HTML TAG *************** */
        $out = '<!DOCTYPE html>';
        $out .= '<html';
        foreach($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }
        $out .= '><head>'.$responsive.trim($content);


        /* ******************** METAS *************** */


        //head tag
        $metas =  (array) \team\Context::get('SEO_METAS');
        $metas =  \team\Filter::apply('\team\tag\metas', $metas);

        $charset = \team\Config::get('CHARSET');
        $out .= "<meta charset='{$charset}'>";


        foreach($metas as $name => $content) {
            if(stripos($name, 'og:') === 0){
                $out .= "<meta property='{$name}' ";
            }else {
                $out .= "<meta name='{$name}' ";
            }

            if(is_array($content)) {
                $options = $content;
                foreach($options as $key => $value) {
                    $out .= $key."='{$value}'";
                }
                $out .=  '>';
            }else {
                $out .= "content='{$content}'>";
            }
        }

        $out =  trim(\team\Filter::apply('\team\tag\head', $out));

        /* ******************** TOP CSS Y JS FILES *************** */
        //TOP CSS
        $css_files =  \team\Config::get('\team\css\top', []);

        if(!empty($css_files) ) {
            foreach($css_files as $id => $file) {
                $out .="<link href='{$file}' rel='stylesheet'/>";
            }
        }

        //TOP JS
        $js_files =  \team\Config::get('\team\js\top', []);

        if(!empty($js_files) ) {
            foreach($js_files as $id => $file) {
                $out .=	"<script src='{$file}'></script>";
            }
        }

        /* ******************** /TOP CSS Y JS FILES *************** */

            return $out;
	}


}
