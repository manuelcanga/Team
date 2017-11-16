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

namespace team\data\formats;

\team\loader\Classes::add('team\data\Format', "/includes/interfaces/data/Format.interface.php", _TEAM_);

\team\loader\Classes::add('\team\data\formats\Arrayformat', "/classes/data/formats/Arrayformat.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Html', "/classes/data/formats/Html.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Json', "/classes/data/formats/Json.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Log', "/classes/data/formats/Log.php, _TEAM_");
\team\loader\Classes::add('\team\data\formats\Object', "/classes/data/formats/Object.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\String', "/classes/data/formats/String.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Terminal', "/classes/data/formats/Terminal.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Url', "/classes/data/formats/Url.php", _TEAM_);
\team\loader\Classes::add('\team\data\formats\Xml', "/classes/data/formats/Xml.php", _TEAM_);



class Format {
	final function filter($_type, $_default = null) {
		$type =  ucfirst(strtolower(\team\Check::key($_type, $_default)));
		return ('Array' == $_type)? 'Arrayformat' : $type;
	}

	final function get($_type) {
		$type = $this->filter($_type);
		if(!isset($type) ) return null;
	
		$class = \team\Filter::apply('\team\formats\\'.$type, '\team\data\formats\\'.$type);

		return \team\loader\Classes::factory($class, true);
	}


}
