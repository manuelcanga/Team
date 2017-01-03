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
        $this->notifyStart();
        $this->initializeSite();
        $this->cacheOfTheInitialization();
    }

    /**
     * Create context will be used by any package
     */
    private function createContextBase() {
        global $_CONTEXT;

        $contexts_from_team_initialization = $_CONTEXT->getState();
        $team_base_contexts = $this->loadConfigFiles(_TEAM_.'/config', '\config\team');
        $user_defined_constants = get_defined_constants(true)['user'];

        //Puede ser que el usuario haya querido inicializar por constantes. así que le damos máxima prioridad
        //La segunda prioridad la tienen los contextos ya creados durante el inicio del framework
        //La tercera prioridad la tienen los contextos base de team framework
        $init_vars = $user_defined_constants + $contexts_from_team_initialization + $team_base_contexts;

        $enviroment = $init_vars['ENVIROMENT'];
        //Namespace asociado al contexto
        $init_vars["NAMESPACE"] =  '\\';

        \team\Context::add($init_vars);

        //Parseamos los archivos de configuración de root
        $root_configs_dir = \team\CONFIG_PATH.'/commons/config';
        $root_vars= $this->loadConfigFiles($root_configs_dir, '\config', $enviroment, $init_vars);

        //Añadimos las variables encontradas al contexto actual ( root ):
        \team\Context::setContexts( $root_vars );
        $this->cache = $root_vars;
    }

    private function notifyStart() {
        \team\FileSystem::load('/Start.php', _TEAM_);

        \Team::event('\team\start');
    }

    private function initializeSite() {
        \team\FileSystem::load('/commons/Initialize.php');
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


    private  function loadConfigFiles($configs_path, $namespace, $enviroment = null, $vars = []) {

        $config_dir_exists =file_exists($configs_path);
        if(!$config_dir_exists) return $vars;

        $configs_path = $this->getConfigPathAccordingToEnviroment($enviroment, $configs_path, $namespace);

        //Class config files
        $config_files = glob($configs_path.'/*.conf.php');

        if(!empty($config_files) ) {
            foreach($config_files as $file) {
                $basename = \team\FileSystem::basename($file);
                $disabled_config = '_' == $basename[0];
                if(!$disabled_config) {
                    $vars = $this->loadConfigClassFile($file, $namespace, $basename, $vars);
                }
            }
        }

        return $vars;
    }

    private function getConfigPathAccordingToEnviroment($enviroment, $configs_path, $namespace) {
        //Esto es muy útil para poder depurar ciertas partes de un proyecto sin que afecte a otras
        $enviroment_file = $configs_path.'/Enviroment.conf.php';
        $enviroment_exists = file_exists($enviroment_file);
        if($enviroment_exists) {
            $vars = (array) $this->loadConfigClassFile($enviroment_file, $namespace, 'Enviroment');
            $enviroment = $vars['ENVIROMENT']?? $enviroment;
        }

        \team\Context::set('ENVIROMENT', $enviroment);

        //Cargamos todos los archivos de configuración
        $enviroment_configs_path = $configs_path.'/'.$enviroment;
        $config_per_enviroment_exists = $enviroment && file_exists($enviroment_configs_path);
        if($config_per_enviroment_exists) {
            return $enviroment_configs_path;
        }else {
            return $configs_path;
        }
    }



    //Vamos recorriendo todos los archivos de configuración.
    //Instanciando sus clases
    //Lanzando sus métodos setups
    //Guardando el resultado.
    public  function loadConfigClassFile($file, $namespace, $basename, $last_vars = []) {

        require_once($file);

        $class = $namespace.'\\'.$basename;
        if( !class_exists($class) )  {
            \team\Debug::me("Not class $class found in $file");
            return $last_vars;
        }

        $obj = new $class;

        /**
         * Sometimes we have double or more configuration of same type. For example, three databases
         * In this cases, we can create Config files with '_'
         * Db_main
         * Db_extra
         * Db_forum
         * Then, main, extra and forum configuation is saved in contexts Db['main'], Db['extra'] and Db['forum']
         *
         */
        if(strpos($basename, '_')) {
            list($basename, $index) = explode('_', $basename, 2);

            $basename = strtoupper($basename);

            $last_vars[$basename][$index] =   $obj->getVars();

            return   $vars;
        }

        return $obj->getVars() + $last_vars;
    }

}
