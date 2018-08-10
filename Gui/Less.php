<?php

namespace Team\Gui;

use \Team\System\Context;
use \Team\Debug;

//generate a new file.
require_once(\Team\_VENDOR_ . '/lesserphp/lessc.inc.php');
require_once(\Team\_VENDOR_ . '/lesserphp/src/LesserPhp/Formatter/Compressed.php');

class Less
{
    const EXTENSION = '.less';
    const CSS_EXTENSION = '.css';

    protected $base = '';
    protected $file_in = '';
    protected $file_in_extension = self::EXTENSION;
    protected $file_out = '';
    protected $file_lessed = '';
    protected $need_generation = true;

    public function addFile(& $file_in, $base = null) {
        $with_less_support = strpos($file_in, self::EXTENSION) > 0;
        $this->file_in = str_replace(self::EXTENSION, '', $file_in);

        $this->base = $base?: _SCRIPTS_;
        $this->file_out = $this->generateFilenameOut();

        if($this->fileOutCanBeUsed()) {
            $file_in = $this->file_out;
            $this->need_generation = false;
        }

        if(!$with_less_support) {
            $this->file_in_extension = self::CSS_EXTENSION;
        }

        return $this->need_generation;
    }


    protected function generateFilenameOut() {
        $current_version = \Team\System\Context::get('VERSION');
        $extension = self::CSS_EXTENSION;

        $file_out = "{$this->file_in}-{$current_version}.{$extension}";
        return '/'.ltrim($file_out,'/');
    }

    protected function fileOutCanBeUsed() {
        $file_out_can_be_used = file_exists($this->base.$this->file_out) &&  !$this->isDevEnvironment();
        $force_generation = \Team\Config::get('ASSETS_NEED_GENERATION');
        if( $file_out_can_be_used || $force_generation) {
            return true;
        }

        return false;
    }

    protected function isDevEnvironment() {
	return  "dev" !== \Team\Config::get('ENVIRONMENT') ;
    }

    public function parser() {
        $out = $this->file_out;

        if(!$this->need_generation) {
            return $out;
        }

        try {
            $file_in = $this->base.$this->file_in.$this->file_in_extension;
            $file_out = $this->base.$this->file_out;

	    if(!$this->isDevEnvironment() ) {
            	Debug::me("Gnerating css file for {$file_in}", 4);
	    }

            $parser = $this->getParser();
            $parser->compileFile($file_in, $file_out);
        } catch (\Throwable $e) {
            Debug::me("Error generating file {$file_out} from {$file_in}:" . $e->getMessage(), 4);

            if($this->file_in_extension === self::EXTENSION) {
                $out = '';
            }else {
                $out = $this->file_in;
            }

        }

        return $out;
    }

    protected function getParser() {
        $less = new \lessc;
        $less->setFormatter("compressed");
        $less->setVariables(Context::get('theme', []));

        return $less;
    }
}
