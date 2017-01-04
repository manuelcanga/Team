<?php

namespace team\loaders;

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

namespace team\loaders;

/**
Se encarga de obtener y cargar todos los archivos de configuración de cada namespace.
Así como de ejecutar los initialize de cada namespace para que adapten los archivos de configuración al gusto
o inicialicen filtros, pipelines, etc.

 */
class Config {

    private $cache = [];

    public  function __construct()
    {
        $this->createContextBase();
        $this->cacheOfTheInitialization();
    }

    /**
     * Create context will be used by any package
     */
    private function createContextBase() {
        global $_CONTEXT;

        $contexts_from_team_initialization = $_CONTEXT->getState();
        $user_defined_constants = get_defined_constants(true)['user'];

        //Puede ser que el usuario haya querido inicializar por constantes. así que le damos máxima prioridad
        //La segunda prioridad la tienen los contextos ya creados durante el inicio del framework
        //La tercera prioridad la tienen los contextos base de team framework
        $init_vars = $user_defined_constants + $contexts_from_team_initialization;

        //Namespace asociado al contexto
        $init_vars["NAMESPACE"] =  '\\';

        \team\Context::add($init_vars);

        $this->cache = $init_vars;
    }



    /**
     *  So we don't neet do do the same initizalition for each widget call
     */
    private function cacheOfTheInitialization() {
        $this->cache = \team\Context::getState();
    }

    public function load($namespace) {
        $info_namespace = \team\NS::explode($namespace);

        $current_package = $info_namespace['package'];
        $current_component = $info_namespace['component'];

        if(!$current_component && !$current_package) {
            return ;//   $this->loadRootConfig(); we have already done
        }

        \team\Context::setContexts( $this->cache );

        if($current_component) {
            $this->loadComponentConfig($current_component, $current_package);
        }else {
            $this->loadPackageConfig($current_package);
        }

    }


    private function loadPackageConfig($package) {
        $current_namespace = "\\{$package}";
        \team\Context::set('NAMESPACE', $current_namespace);

        \Team::event("\\team\\load".$current_namespace);
        \Team::event("\\team\\load\\{$package}".$current_namespace);
    }

    private function loadComponentConfig($component, $package) {
        $current_namespace = "\\{$package}\\{$component}";

        \team\Context::set('NAMESPACE', $current_namespace);

        \Team::event("\\team\\load".$current_namespace);
        \Team::event("\\team\\load\\{$package}\\{$component}".$current_namespace);
    }

}
