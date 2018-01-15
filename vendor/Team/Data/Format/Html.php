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

/**
	TODO Que se le llame al engine de Smarty(Otros posibles PHP y XML ) segun configuración módulo
*/

namespace Team\Data\Format;

\Team\Loader\Classes::add('Team\Data\Format\IFormat', "/Data/Format/IFormat.php", _TEAM_);

\Team\Loader\Classes::add('Team\Data\Htmlengine\TemplateEngine', "/Data/Htmlengine/TemplateEngine.php", _TEAM_);
\Team\Loader\Classes::add('Team\Data\Htmlengine\HtmlEngine', "/Data/Htmlengine/HtmlEngine.php", _TEAM_);
\Team\Loader\Classes::add('Team\Data\Htmlengine\PhpEngine', "/Data/Htmlengine/PhpEngine.php", _TEAM_);
\Team\Loader\Classes::add('Team\Data\Htmlengine\XmlEngine', "/Data/Htmlengine/XmlEngine.php", _TEAM_);


final class Html implements \Team\Data\Format\IFormat  {
	public function renderer(Array $_data) {
		$type_engine = $_data["HTML_ENGINE"]?? \Team\Config::get("HTML_ENGINE");

        $engine = $this->get($type_engine);

		return $engine->transform($_data);
	}

	public function filter($_type, $_default) {
		return  ucfirst(\team\data\Check::key($_type, $_default));
	}


	 function get($type_engine) {
		$type_engine = $this->filter($type_engine, "TemplateEngine");
		if(!isset($type_engine) ) return null;

	
		$class = \Team\Data\Filter::apply('\team\htmlengine\\'.$type_engine, '\Team\Data\Htmlengine\\'.$type_engine);

		return  \Team\Loader\Classes::factory($class, true);
	}


}
