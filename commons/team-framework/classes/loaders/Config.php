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

    private $scripts_path = '/events';

    public  function __construct()
    {
        global $_CONTEXT;

        //Obtenemos los contextos guardados hasta ahora( provenientes de la inicialización del sistema )
        $context= $_CONTEXT->getState();

        //Al ser el primer nivel, inicializamos con las constantes de usuario(si las hubiera )
        $init_vars = $context + get_defined_constants(true)['user'];

        //Namespace asociado al contexto
        $init_vars["NAMESPACE"] =  '\\';

        \team\Context::add($init_vars);

        //Añadimos también las variables de configuración base
        $team_vars = $this->loadFiles(_TEAM_.'/config', '\config\team');
        $profile = $team_vars['PROFILE'];

         \team\Context::add($team_vars);

        //Ahora, hacemos lo mismo con root.
        $root_vars= $this->loadFiles(\team\CONFIG_PATH.'/commons/config', '\config', $profile);

        //Añadimos las variables encontradas al contexto actual ( root ):
        \team\Context::add($root_vars);

        //Si no existe CONFIG_PATH./confg/.installed se debería de lanzar el evento de instalación y quizás llamar a los config con install


        //Avisamos a los Start de todos los paquetes de que se va a inicializar el raiz
        \team\FileSystem::notify('/', 'Start', '/commons'.$this->scripts_path, '\team\packages');

        //Llamamos al evento Start de Team framework( ya que es como un componente "virtual" )
        \team\FileSystem::load($this->scripts_path.'/Start.php', _TEAM_);

        //Lanzamos el evento start del sistema
        \Team::event('\team\start');

        //Llamamos al initialize del raiz.
        \team\FileSystem::load('/commons'.$this->scripts_path.'/Initialize.php');


        //El resultado lo cacheamos para root
        $this->cache['\\'] = \team\Context::getState();
    }

    public function load($namespace, $path) {
        $cached = false;

        $info_namespace = \team\NS::explode($namespace);
        $down_namespace = \team\NS::shift($namespace);

        //Comprobamos si estaban cacheadas las variables de configuración del namespace actual
        if(array_key_exists($namespace, $this->cache) ) {
            \team\Context::add($this->cache[$namespace] );
            //Aquí quizás deberiamos de cargar y lanzar el evento load
            $cached = true;
        }else if('\\' != $namespace) {
            //El nivel inferior está cacheado seguro porque vamos cargando desde abajo hasta arriba
            \team\Context::add($this->cache[$down_namespace]);
        }


        //Buscamos las variables de configuración del namespace actual.
        if($info_namespace['component']) { //Estamos en un componente
            $this->loadComponent($info_namespace['component'], $info_namespace['package'], $path, $cached);
        }else if($info_namespace['package']){	//Estamos en un paquete.
            $this->loadPackage($info_namespace['package'], $path, $cached);
        }else {
            $this->loadRoot();
        }

    }

    private function loadRoot() {
        //Esto ya está hecho
    }


    private function loadPackage($package, $path, $cached) {
        \team\Context::set('NAMESPACE', "\\{$package}");
        $profile = \team\Context::get('PROFILE');

        //Obtenemos las variables de configuración del paquete.
        //Si ya estaba cacheado significa que ya se inicializó anteriormente.
        if(!$cached) {
            $vars = $this->loadFiles(\team\CONFIG_PATH.$path.'/commons/config', '\config\\'.$package, $profile);
            \team\Context::add($vars);
            //@TODO: Si no existe CONFIG_PATH.$path./.installed se debería de lanzar el evento de instalación y quizás llamar a los config con install

            $type = \team\Context::get('CONTROLLER_TYPE', 'Gui');
            //Cogemos también los archivos de configuración acorde al tipo de acción que se va a lanzar( ojo, el namespace sigue fijado al paquete )
            $vars = $this->loadFiles(\team\CONFIG_PATH.$path."/commons/config/{$type}/", '\config\\'.$package, $profile);
            \team\Context::add($vars);

            //Lanzamos evento de inicialización paquetes
            \Team::event('\team\package',$package);
            \Team::event("\\team\\initialize\\{$package}");

            //Inicializamos el paquete en cuestión
            \team\FileSystem::load('/'.$package.'/commons'.$this->scripts_path.'/Initialize.php');

            //El resultado lo cacheamos para futuras peticiones
            $this->cache["\\{$package}"] = \team\Context::getState();
        }

        \Team::event("\\team\\load\\{$package}}");
    }

    private function loadComponent($component, $package, $path, $cached) {
        \team\Context::set('NAMESPACE', "\\{$package}\\{$component}");
        $profile = \team\Context::get('PROFILE');

        if(!$cached) {
            //Si no existe CONFIG_PATH.$path./confg/.installed se debería de lanzar el evento de instalación y quizás llamar a los config con install

            //Obtenemos los archivos del componente actual
            $vars= $this->loadFiles(\team\CONFIG_PATH.$path.'/config', "\\config\\{$package}\\{$component}", $profile);
            \team\Context::add($vars);

            $type = \team\Context::get('CONTROLLER_TYPE', 'Gui');
            //Cogemos también los archivos de configuración acorde al tipo de acción que se va a lanzar( ojo, el namespace sigue fijado al componente )
            $vars = $this->loadFiles(\team\CONFIG_PATH.$path."/config/{$type}/", '\config\\'.$package, $profile);
            \team\Context::add($vars);

            \Team::event("\\team\\component\\{$package}", $component, $package);
            \Team::event("\\team\\initialize\\{$package}\\{$component}");

            //Initializamos el componente
            \team\FileSystem::load($path.$this->scripts_path.'/Initialize.php');

            //El resultado lo cacheamos para futuras peticiones
            $this->cache["\\{$package}\\{$component}"] = \team\Context::getState();
        }

        \Team::event("\\team\\load\\{$package}\\{$component}");
    }




    public  function loadFiles($path, $namespace, $profile = null) {
        $vars = array();

        //Si existe al archivo profile, lo cargamos
        if(!file_exists($path) ) return $vars;

        //Cargamos un posible archivo de perfil que hubiera
        //Esto es muy útil para poder depurar ciertas partes de un proyecto sin que afecte a otras
        $profile_file = $path.'/Profile.conf.php';
        if(file_exists($profile_file) ) {
            $vars = (array) $this->loadclassFile($profile_file, $namespace);

            if(isset($vars['PROFILE'])) {
                $profile = $vars['PROFILE'];
            }
        }
        \team\Context::set('PROFILE', $profile);


        //Cargamos todos los archivos de configuración
        if($profile && file_exists($path.'/'.$profile)) {
            $path = $path.'/'.$profile;
        }

        //Class config files
        $configs = glob($path.'/*.conf.php');

        if(!empty($configs) ) {
            foreach($configs as $file) {
                $basename = \team\FileSystem::basename($file);
                if('_' != $basename[0])
                    $vars = $this->loadclassFile($file, $namespace, $basename) + $vars;
            }
        }

        //Ini config file
        $configs = glob($path.'/*.conf');
        if(empty($configs) ) return $vars;

        foreach($configs as $file) {
            $basename = \team\FileSystem::basename($file);
            if('_' != $basename[0]) {
                $vars = (parse_ini_file($file, $process_sections = true, INI_SCANNER_TYPED) + $vars );
            }
        }

        return $vars;
    }

    //Vamos recorriendo todos los archivos de configuración.
    //Instanciando sus clases
    //Lanzando sus métodos setups
    //Guardando el resultado.

    public  function loadclassFile($file, $namespace, $basename) {
        $vars =array();

        require_once($file);

        $class = $namespace.'\\'.$basename;
        if( !class_exists($class) )  {
            \team\Debug::me("Not class $class found in $file");
            return $vars;
        }

        $obj = new $class;

        if(strpos($basename, '_')) {
            list($basename, $index) = explode('_', $basename,2);

            $basename = strtoupper($basename);

            $vars[$basename][$index] =   $obj->getVars();

            return   $vars;
        }

        return $obj->getVars();
    }

}
