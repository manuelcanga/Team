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

namespace team\gui;
 
trait Seo {
        /** -------------------- Breadscrumb --------------------  */
    public function addCrumb($name, $link, $idcrumb = null, $order = null) {
        if(!$this->isMain()) return ;

        static $position = 5;

        //Dejamos huecos si no se especifico orden
        if(!isset($order)) {
            $position += 5;
            $order = $position;
        }

        \team\Config::push('\team\gui\breadcrumbs', ['name' => $name, 'url' => $link]);
    }

    
        /**
    Añade una metaetiqueta SEO
    $this->seo('description', 'Hola Mundo');
     */
    function seo($key, $value, $options = null) {
        if(!$this->isMain()) return false;

        if(isset($options) ) {
            \team\Config::add('SEO_METAS', $key, ['value'=> $options, 'options' => $options]);
        }else {
            \team\Config::add('SEO_METAS', $key, $value);
        }
    }
    
    
    

    /**
     * Asign a value to SEO_TITLE
     * @param string $title webpage title
     * @param ?bool $separator false(not separator), true(with separator), null(remove previous title )
     *
     */
    function setTitle($title, $separator = true, $before = false) {
        if(!$this->isMain()) return false;

        $SEO_TITLE = \team\Config::get('SEO_TITLE', '', 'setTitle');


        if(null === $separator || !$SEO_TITLE) {
            $SEO_TITLE = $title;
        }else if($separator) {
            if($before) {
                $SEO_TITLE = $title.' '.\team\Config::get('SEO_TITLE_SEPARATOR', '-').' '.$SEO_TITLE;
            }else {
                $SEO_TITLE = $SEO_TITLE.' '.\team\Config::get('SEO_TITLE_SEPARATOR', '-').' '.$title;
            }
        }else {
            if($before) {
                $SEO_TITLE = $title . ' ' . $SEO_TITLE;
            }else {
                $SEO_TITLE = $SEO_TITLE. ' '.$title ;
            }
        }

        \team\Config::set('SEO_TITLE', $SEO_TITLE);
    }
}
