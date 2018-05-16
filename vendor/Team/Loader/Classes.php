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

namespace Team\Loader;

/**
	Gestiona de la autocarga de clases.
*/
class  Classes{
    protected static $base;

	/**
		Others es una lista de otros autoloaders que se pueden anexar, mediante el método addLoader,
		para la busqueda de clase. En próximas versiones esto dejará de tener sentido ya que está
		pediente de aprobación autoloaders sin devolución de error
	*/
	private static $loaders = array();


    /**
    Cuando cargamos archivos de clases un requiere es ineficiente, ya que php tiene que parsear dichos archivos
    se carguen luego las clases o no. Usar autoloader es una manera más eficiente en tanto en cuanto se carga sólo
    lo que se necesita, sin embargo, clases con namespace complejos suponen una carga extra para el autoloader. Para evitar
    esto he ideado un registro de clases, de tal manera que se especifica una clase y un path hacia su ruta. Si la clase
    al final se carga, el autoloader ya tendría un registro de ella y podrá cargar el archivo requerido en poco tiempo.
    @example: Cargamos en la cabecera de uno de nuestros archivos:
    \Team\Loader\Classes::add("\mipackage\micomponente\MiClase", "/mipackage/micomponente/directorioOdirectorios/MiClase");
    Cuando se haga un uso de la clase "\mipackage\micomponente\MiClase" en el ejemplo, el autoloader ya sabrá dónde se encuentra y la podrá
    cargar sin problema. Además de mejorar la eficiencia, esto permite carga de clases un poco enrevesadas o también externas
    que no calcen con la reglas de Team Framework.
    También nos permite como clases hooks, esto es, imaginate que registramos la clase:
    CSV en /mipaquete/common/lib/CSV.class.php
    Sin embargo, uno que quiera usar su propiaclase lo único que tendría que hacer es, registrar CSV pero de otra ubicación. Ejmplo:
    CSV en /miotropaquete/common/exports/CSV.class.php

     */
    private static $registers = array();



    /**
		Añade un Loader asociado
		@param callable $func es la función que queremos que nos ayude en la carga de clases 
	*/
	public static function addLoader($func) {
		self::$loaders[] = $func;
	}


	/**
		Añade una clase al registro de clases
		@param String $class: Nombre de la clase con namespace completo
		@param $path: Path relativo desde el raiz del framework del archivo donde se encuentra la clase.
	*/
	public static function add($class, $path = null, $base = _SCRIPT_) {


        $class = ltrim($class, '\\');;
		if(!isset(self::$registers[$class]) ) {

            if(!isset($path)) {
                $name = \Team\System\NS::basename($class);
                $path = \Team\System\Context::get('_COMPONENT_').'/classes/'.$name.'.php';
            }

			self::$registers[$class] = ['path' => $path, 'base' => $base, 'name'=> $class, 'initialized' => false ];
			if(\team\Config::get("TRACE_AUTOLOAD_CLASS", false) ) {
				\team\Debug::me("Registrada clase '$class' con path '$path' y base '$base'");
			}
			return true;
		}
		return false;
	}

	public static function get($class_name_full) {
	    return self::factory($class_name_full, $instance = true, $with_alias = true);
    }

    public static function alias($_alias, $class, $path = null, $base = _SCRIPT_) {
        $_alias = ltrim($_alias,'\\');
        $class = ltrim($class,'\\');

        \Team\System\Context::add('classs_alias', $_alias, $class);
        return self::add($class, $path, $base);
    }

    /**
		Metodo que se encarga de buscar clases para autocargarlas. 
		Normalmente será el autoloader del sistema ( en ese caso $_intance será false ), 
		pero podemos llamarlo nosotros mismos( en ese caso es conveniente pasar true para $instance )

		@param String $class_name_full nombre de la clase, con namespace, a buscar
		@param boolean $instance decidimos que después de encontrar la clase nos devuelva una instancia.
        @param boolean $with_alias se permite alias de clases o no

     */
	public static function factory($class_name_full, $instance = false, $with_alias = false) {
        $class_name_full = ltrim($class_name_full,'\\');

        if($with_alias) {
            $class = \Team\System\Context::getKey($class_name_full, 'classs_alias');

            if(isset($class)) {
                $class_name_full = $class;
            }
        }

        /**
        Comprobamos si es una clase registrada
        Las clases registradas agilizan la carga y por tanto hacen el framework o la web más rápida
        Todo porque ya sabemos donde hay que buscar la clase
         */
        if(isset(self::$registers[$class_name_full]) ) {
            //Recuperamos el fichero donde está la clase
            $class =& self::$registers[$class_name_full];

            //Comprobamos si de verdad existe
            if($class['path'] && self::classExists($class['path'], $class['base'])) {

                if(!$class['initialized']) {
                    $class['initialized'] = true;
                    return self::newClass($class['name'], $instance);
                }
            }

            $class_name_full = $class['name'];
        }


        //if class was instance already then return this
        if($instance  && class_exists($class_name_full, false) ) {
            return new $class_name_full();
        }

        $namespace = explode('\\', $class_name_full);
        $name = array_pop($namespace);


        //Optimización: es una clase smarty salimos
        if(0 === strpos($name, 'Smarty_')) {
            return false;
        }


        $package = null;
        if(!empty($namespace) ) {
            $package = array_shift($namespace);
        }


        /* direct mode  */
        $direct_mode = false;

        $filename = implode("/",  $namespace) ."/{$name}.php";


        if('theme' == $package) {
            $direct_mode = self::load($class_name_full, $filename, \Team\Config::get('_THEME_'));
        }else if('tests' == $package) {
            $direct_mode = self::load($class_name_full, $filename, \Team\Config::get('_TESTS_'));
        }

        $filename = "/".str_replace('\\', '/', $class_name_full).".php";

        if( self::load($class_name_full, $filename, \_SCRIPT_)
            ||  self::load($class_name_full, $filename, \Team\_VENDOR_ ) ) {
            $direct_mode  = true;
        }

        if(  $direct_mode ){
            return self::newClass($class_name_full, $instance);
        }

        /** ------ framework mode on------- */





		$component = null;
		if(!empty($namespace) ) {
			$component = array_shift($namespace);
		}

		if(!isset($component) ) {
				//Comprobamos si es una pseudo clase
				if(self::isPseudoClass($package, $name) ) {
				    return self::newClass($class_name_full, $instance);
				}

		}


		//Comprobamos si es una clase de common pero con un subnamespace a partir de este.
		 if(isset($package) && !isset($component) && ! \Team\System\FileSystem::exists("/{$package}/{$component}")  )  {
			$subpath = '/';
			if(!empty($namespace) ) {
				$subpath = '/'.implode('/', $namespace).'/';
			}


			if( self::findClass($name,"/{$package}/commons/" , $subpath, $class_name_full, self::$base ) ) {
		        return self::newClass($class_name_full, $instance);
	   	   }
		}

		if(isset($component) &&  \Team\System\FileSystem::exists("/{$package}/{$component}") ) {
			$subpath = '/';
			if(!empty($namespace) ) {
				$subpath = '/'.implode('/', $namespace).'/';
			}


			if( self::findClass($name, "/{$package}/{$component}/", $subpath, $class_name_full ) ) {
				    return self::newClass($class_name_full, $instance);
		   	}

		}
		

        //Mostramos una traza si asi lo quiere el programador
		//De que hubo algún error porque no hemos encontrado la clase
		if(\team\Config::get("TRACE_AUTOLOAD_CLASS", false) ) {
			\team\Debug::me("Loading class....false", $class_name_full);
		}
	}

	
	/**
		Comprobamos si existe el archivo de una clase y si es así lo cargamos
		@param $file es el fichero que contiene la clase
		@param $path es el path que nos servirá de base para encontrar la clase
	*/
	public static function classExists($file, $path = _SCRIPT_, $className = null) {

	    $class_exists = false;

		if(!empty($file) && file_exists($path.$file) ) {
            $class_exists = true;

			 include_once($path.$file);

			/** hay muchos tipos de elemtos que se carga con el autoload: clases, excepciones, trait, ...
				para los casos en que sea una clase podemos comprobar si el nombre de la clase
				existe sino para avisar que en el archivo incluido no estaba
			*/
            if(isset($className)) {
                $class_exists = class_exists($className, false);

                if(!$class_exists) {
                    \Team\Debug::me("Not class found in {$path}{$file}", $className);
                }
			}
		}

        if(\team\Config::get('TRACE_AUTOLOAD_CLASS', false) ) {
                \Team\Debug::me($path.$file, "Loading file....".($class_exists? "FOUND" : "Not FOUND") );
        }

        return $class_exists;
	}


	/**
		Inicializamos e "Instanciamos" una  clase. 

		@param $class_name: nombre de Clase a inicializar y/o instanciar
		@param $instance indicador que nos especifica si instanciamos una nueva clase
	*/
	public static function newClass($class_name, $instance = true) {

		if(class_exists($class_name, false)) {

			if(method_exists($class_name, "__initialize") ) {
				$class_name::__initialize($class_name);
			}

			if(method_exists($class_name, "__load") ) {
				$class_name::__load($class_name);
			}

			if($instance) {
				return new $class_name();
			}

			//Mostramos una traza si asi lo quiere el programador
			if(\team\Config::get("TRACE_AUTOLOAD_CLASS", false) ) {
				\team\Debug::me($class_name, "Loading class....true" );
			}

			return true;
		}

		return false;
	}

	/**
		Una mezcla entre los métodos classExists y newClass
		Muy útil para clases que se han de cargar si o si, y se quiere
		inicializar y llevar un control sobre ellas. 
		Si se da el caso de que la clase no se sabe bien si se a utilizar, es mejor registrarla(\Classes::add )
		@param $class_name nombre clase a cargar 
		@param $file es el fichero, con path relativo, a donde se encuentra
	*/

	public static function load($class_name, $file, $base = _SCRIPT_) {
		if(self::classExists($file, $base) ) {
			if(self::newClass($class_name, false) ) {
				return true;
			}
		}

		return false;
	}
	
	/**
		Comprobamos si es una clase de componente virtual. 
		Ya que en Team Framework simulamos los componentes como si fueran clases y sus métodos son las acciones.
		@param $paquete es el nombre del paquete al que pertenece el componente
		@param $component es el nombre del componente que se cargará virtualmente, este será también el nombre de la nueva clase.
	*/
	public static  function isPseudoClass($package, $component) {

        if('theme' === $package ){
            $is_pseudo_class =  \Team\System\FileSystem::exists("/{$component}", \Team\Config::get('_THEME_') );
        }else if('tests' === $package ){
            $is_pseudo_class = \Team\System\FileSystem::exists("/{$component}", \Team\Config::get('_TESTS_') );
        }else {
            $is_pseudo_class = \Team\System\FileSystem::exists("/{$package}/{$component}");
        }


		if($is_pseudo_class ) {
			//Creamos una clase de componente
			$new_class = "Namespace {$package}; class {$component} extends \\team\\Builder\\Component { }";

			eval($new_class);
			return true;
		}
		return false;
	}


	/**
		Buscamos en las rutas más comunes la clase especificada
		(Lo mejor, siempre que se pueda, es registrar una clase )

		@param $name es el nombre de la clase a buscar
		@param $path hacia el paquete, componente o acción al que pertecene la clase
		@param $subpath es el path hacia la clases tomando como base el parámetro anterior y quitando los directorios comunes intermedios
		@TOOD: ¿Añadir los modelos?
	*/
	public static  function findClass($name, $path = "", $subpath = "/",  $class_name = null, $root = _SCRIPT_) {

		if(!isset($class_name) ) {
			$class_name = $name;
		}

		$classes_class 	 =	"{$path}classes{$subpath}{$name}.php"; 					//model class
		$templates_class  =  "{$path}includes{$subpath}{$name}.php";  				//traits, exceptions, interfaces, vendors, ...

		//echo "<br>{$name} in {$root}<br>{$root}{$classes_class}<br>{$root}|{$templates_class}<br><br>";

		if(     self::classExists($classes_class, $root, $class_name) ||   self::classExists($templates_class, $root) ){
			return true;
		}else {
			return false;
		}
		
	}

}
