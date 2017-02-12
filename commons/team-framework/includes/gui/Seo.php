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

/**
 * Funciones útiles para SEO.
 * Es necesario que aquí se guarde en Config pero se recupere como Contexto.
 * Hay que hacerlo por Config para que distintos niveles de Gui puedan añadir elementos que se recogeran a un mismo nivel.
 * Hay que recogerlo como Contexto porque no sólo las Gui generan tpl, también la clase Template, por ejemplo.
 * Si tomaramos los datos desde Config obtendríamos ls mismos recursos sea la plantilla que sea y eso no es correcto.
 *
 * Class Seo
 * @package team\gui
 */
trait Seo {
        /** -------------------- Breadscrumb --------------------  */
    public function addCrumb($name, $link = '#', $classes = '') {
        \team\Config::push('BREADCRUMB', ['name' => $name, 'url' => $link, 'classes' => $classes]);
    }

    
        /**
    Añade una metaetiqueta SEO
    $this->seo('description', 'Hola Mundo');
     */
    function seo($key, $value, $options = null) {
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
    public function setTitle($title, $separator = true, $after = false) {

        $SEO_TITLE = \team\Context::get('SEO_TITLE', '');


        if(null === $separator || !$SEO_TITLE) {
            $SEO_TITLE = $title;
        }else if(!$after) {
            $SEO_TITLE = $title . ' ' . ($separator? \team\Config::get('SEO_TITLE_SEPARATOR', '-', 'setTitle') : '') . ' ' . $SEO_TITLE;
        }else {
            $SEO_TITLE = $SEO_TITLE.' '.($separator? \team\Config::get('SEO_TITLE_SEPARATOR', '-', 'setTitle') : '').' '.$title;
        }


        \team\Context::set('SEO_TITLE', $SEO_TITLE);

        return $SEO_TITLE;
    }
}
