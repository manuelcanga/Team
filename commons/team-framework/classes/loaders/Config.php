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

    private $cache = array();

    public  function __construct()
    {

        $this->createContextBase();
        $this->notifyStartToAllPackages();
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

        $root_configs_dir = \team\CONFIG_PATH.'/commons/config';
        $root_vars= $this->loadConfigFiles($root_configs_dir, '\config', $enviroment);

        //Añadimos las variables encontradas al contexto actual ( root ):
        \team\Context::add($root_vars);
    }

    private function notifyStartToAllPackages() {
        //root
        \team\FileSystem::load('/commons/Start.php');

        //To Team too
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
        $this->cache['\\'] = \team\Context::getState();
    }

    public function load($namespace, $path) {
        $info_namespace = \team\NS::explode($namespace);

        $cache_exists = $namespace && isset($this->cache[$namespace]);
        //Comprobamos si estaban cacheadas las variables de configuración del namespace actual
        if($cache_exists ) {
            \team\Context::add($this->cache[$namespace] );
        }else if('\\' != $namespace) {
            //El nivel inferior está cacheado seguro porque vamos cargando desde abajo hasta arriba
            $down_namespace = \team\NS::shift($namespace);
            \team\Context::add($this->cache[$down_namespace]);
        }

        $current_package = $info_namespace['package'];
        $current_component = $info_namespace['component'];

        if($current_component) {
            $this->loadComponentConfig($current_component, $current_package, $path, $cache_exists);
        }else if($current_package){
            $this->loadPackageConfig($current_package, $path, $cache_exists);
        }else {
         //   $this->loadRootConfig(); we have already done
        }

    }


    private function loadPackageConfig($package, $current_namespace_path, $cached) {
        $current_namespace = "\\{$package}";
        \team\Context::set('NAMESPACE', $current_namespace);
        $enviroment = \team\Context::get('ENVIROMENT');

        //Obtenemos las variables de configuración del paquete.
        //Si ya estaba cacheado significa que ya se inicializó anteriormente.
        if(!$cached) {
            $package_config_dir = \team\CONFIG_PATH.$current_namespace_path.'/commons/config';
            $package_config_namespace = '\config'.$current_namespace;
            $this->loadConfig($package_config_dir, $package_config_namespace, $enviroment);

            \Team::event('\team\package',$package);
            \Team::event("\\team\\initialize".$current_namespace);

            //Inicializamos el paquete en cuestión
            \team\FileSystem::load('/'.$package.'/commons/Initialize.php');

            //El resultado lo cacheamos para futuras peticiones
            $this->cache[$current_namespace] = \team\Context::getState();
        }

        \Team::event("\\team\\load".$current_namespace);
    }

    private function loadComponentConfig($component, $package, $current_namespace_path, $cached) {
        $current_namespace = "\\{$package}\\{$component}";

        \team\Context::set('NAMESPACE', $current_namespace);
        $enviroment = \team\Context::get('ENVIROMENT');

        if(!$cached) {
            $component_config_dir = \team\CONFIG_PATH.$current_namespace_path.'/config';
            $component_config_namespace = "\\config".$current_namespace;
            
            $this->loadConfig($component_config_dir, $component_config_namespace, $enviroment);

            \Team::event("\\team\\component\\{$package}", $component, $package);
            \Team::event("\\team\\initialize".$current_namespace);

            //Initializamos el componente
            \team\FileSystem::load($current_namespace_path.'/Initialize.php');

            //El resultado lo cacheamos para futuras peticiones
            $this->cache[$current_namespace] = \team\Context::getState();
        }

        \Team::event("\\team\\load".$current_namespace);
    }


    private function loadConfig($path, $namespace, $enviroment) {
        $vars = $this->loadConfigFiles($path, $namespace, $enviroment);
        \team\Context::add($vars);

        $this->loadConfigAccordingToType($path, $namespace, $enviroment);
    }

    private function loadConfigAccordingToType($path, $namespace, $enviroment) {
        $type = \team\Context::get('CONTROLLER_TYPE', 'Gui');

        //Cogemos también los archivos de configuración acorde al tipo de acción que se va a lanzar( ojo, el namespace sigue fijado al componente )
        $vars = $this->loadConfigFiles($path."/{$type}/", $namespace, $enviroment);
        \team\Context::add($vars);
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


    private  function loadConfigFiles($configs_path, $namespace, $enviroment = null) {
        $vars = array();

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
                    $vars = $this->loadConfigClassFile($file, $namespace, $basename) + $vars;
                }
            }
        }

        return $vars;
    }

    //Vamos recorriendo todos los archivos de configuración.
    //Instanciando sus clases
    //Lanzando sus métodos setups
    //Guardando el resultado.

    public  function loadConfigClassFile($file, $namespace, $basename) {
        $vars =array();

        require_once($file);

        $class = $namespace.'\\'.$basename;
        if( !class_exists($class) )  {
            \team\Debug::me("Not class $class found in $file");
            return $vars;
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

            $vars[$basename][$index] =   $obj->getVars();

            return   $vars;
        }

        return $obj->getVars();
    }

}
