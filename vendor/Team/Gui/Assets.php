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

namespace Team\Gui;
 
trait Assets { 


    /**
     * Enqueue a css file in order to include it in views
     * @param string $file it is a file css or url to file css
     * @param string $position place where css file will be included
     * @param string $idfile indentifier for css file
     *
     */
    public function addCss(string $file,  string $position = 'top', string $idfile = null) {
        $file = str_replace('.css','',$file);

        // maybe double slash(//) is used  => '//file.css'
        $is_external_css = strpos(':/', $file) !== null || '/' == $file[1];

        $idfile = $idfile??  \Team\Data\Sanitize::identifier($file);

        //normalize
        $file = $file.'.css';

        if($is_external_css || \Team\System\FileSystem::exists($file) ) {
            \Team\Config::add("\\team\\css\\{$position}", $idfile, $file);

        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \Team\Debug::me("Css file[$position] $file not found", 3);
        }
    }


    /**
     * Enqueue a js file in order to include it in views
     * @param string $file it is a file js or url to file js
     * @param string $position place where js file will be included
     * @param string $idfile indentifier for js file
     *
     */
    public function addJs($file,  $position = 'bottom', $idfile = null)  {
        $file = str_replace('.js','',$file);

        // maybe double slash(//) is used  => '//file.js'
        $is_external_js = strpos(':/', $file) !== null || '/' == $file[1];

        $idfile = $idfile??  \Team\Data\Sanitize::identifier($file);

        //normalize
        $file = $file.'.js';

        if($is_external_js || \Team\System\FileSystem::exists($file) ) {
            \Team\Config::add("\\team\\js\\{$position}", $idfile, $file);
        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \Team\Debug::me("Javascript file[$position] $file not found", 3);
        }

    }


}
