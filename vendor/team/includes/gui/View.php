<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 13/01/17
 * Time: 18:22
 */

namespace team\gui;


trait View
{

    public function getView($_file, $component = null, $package = null ) {

        //Eliminamos la extensión( ya que eso depende del sistema de render escogido )
        $file = \team\system\FileSystem::stripExtension($_file);

        //Es un resource
        if(strpos($_file, ':')) {
            return $file;
        }

        if(empty($file) )
            $file = $this->getContext('RESPONSE');

        $file = \team\system\FileSystem::getPath("views", $component, $package)."{$file}";

        if(\team\system\FileSystem::filename('/'.$file)) {
            return $file;
        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \team\Debug::me("View {$file}[{$_file}] not found in {$package}/{$component}", 3);
            return null;
        }

    }

    public function setView($_file, $component = null, $package = null) {
        $view =  $this->getView($_file, $component, $package);
        $this->setContext('VIEW', $view);

        return $view;
    }

    public function noLayout() {
        $this->setLayout();
    }

    public function setLayout($_file = null, $component = null, $package = null) {
        if(!isset($_file)) {
            $this->setContext('LAYOUT', null);
        }else {
            //para layout el component por defecto siempre será commons
            $component = $component?: 'commons';
            $this->setContext('LAYOUT', $this->getView($_file, $component, $package));
        }
    }

}