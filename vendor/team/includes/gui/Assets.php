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
 
trait Assets { 


    /**
    Guardamos el path de Css para su posterior uso en una vista
    @param String $_file Fichero Css a añadir
    @param String $component especifica el componente donde se encuentra el recurso( usa "internet", si no es parte del proyecto )
    @param String position lugar del html en que se incrustara la carga del CSS: top( para el head) o bottom( para el pie )
     */
    public function addCss($_file, $component = null, $position = 'top', $package = null) {
        if(empty($_file) )return false;

        $_file = str_replace('.css','',$_file);

        $component = $component?? \team\Context::get('COMPONENT');

        if("internet" === $component)
            $file = $_file;
        else {
            $file = "/".\team\FileSystem::getPath("css", $component, $package )."{$_file}";

        }

        $idfile =    \team\Sanitize::identifier($file);
        $file .= '.css';


        if( "internet" == $component || \team\Filesystem::exists($file) ) {
            \team\Config::add("\\team\\css\\{$position}", $idfile, $file);

        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \team\Debug::me("Css file[$position] $file not found in {$package}/{$component}", 3);
        }
    }




    /**
    Guardamos el path de js para su posterior uso en una vista
    @param String $_file Fichero js a añadir
    @param String $component especifica el componente donde se encuentra el recurso( usa "internet", si no es parte del proyecto )
    @param String position lugar del html en que se incrustara la carga del JS: top( para el head) o bottom( para el pie )
     */
    public function addJs($_file, $component = null, $position = 'bottom', $package = null)  {
        if(empty($_file) )return false;

        $_file = str_replace('.js','',$_file);

        $component = $component?? \team\Context::get('COMPONENT');

        if("internet" === $component)
            $file = $_file;
        else
            $file = "/".\team\FileSystem::getPath("js", $component, $package)."{$_file}";

        $idfile =    \team\Sanitize::identifier($file);
        $file .= '.js';


        if('internet' == $component || \team\Filesystem::exists($file) ) {
            \team\Config::add("\\team\\js\\{$position}", $idfile, $file);
        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \team\Debug::me("Javascript file[$position] $file not found in {$package}/$component", 3);
        }

    }


}
