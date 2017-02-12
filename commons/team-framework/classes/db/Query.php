<?php
/**
New Licence bsd:
Copyright (c) <2014>, Manuel Jesus Canga Muñoz
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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
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

namespace team\db;


require_once(__DIR__.'/DB.php');
class Query implements \ArrayAccess{
    use \team\data\Box; //En data guardamos las sentencias sql
    use \team\db\queries\Select;
    use \team\db\queries\Update;
    use \team\db\queries\Delete;
    use \team\db\queries\Insert;

    /**
        guarda los campos y valores a reemplazar. Es decir, los valores.
        ej: "web" -> "http://trasweb.net"  insert into @table ( web ) values ( :web )

        Se puede asignar bien pasando un objeto Data o mediante funcion. ejemplo:
        $query
            ->web("http://trasweb.net")
            ->title("Team");
     */
    protected $values = [];
    protected $database;

    function __construct($values = null,  $database, array $sentences = []) {

        if($values instanceof \team\Data ) {
            $this->values = $values->get();
        }else {
            $this->values = (array)$values;
        }
        $this->database = $database;

		$this->set($sentences);

    }


	function getDatabase() {
		return $this->database;
	}



 

    /**
    Necesarios para data: valores para las cadena de substitucion(:cadenaDeSubstitucion)
     */
    public function __call($_name, $_arguments = NULL) { $this->values[$_name] = $_arguments[0]; return $this; }
} 
