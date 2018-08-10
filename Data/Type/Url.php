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


namespace Team\Data\Type;

class Url extends Type
{

    protected $args = [];


    /**
     * Parseamos direcciones amigables a partir de una url
     * @param $_url , url completa que queremos parsear
     * return array con todos los parámetros encontrados en dicha url
     */
    public function initialize($_url = null, array $_options = [])
    {
        if(!isset($_url)) return ;

        $_url = '/'.trim($_url, '/');


        //initial arguments
        $main_data =  [
                                 'raw' => $_url, //url tal y como llega al sistema
                                 'location' => $_url, //url después de validar raw
                                 'url_path_list' => [], //elementos de url desglosado en elementos de un array
                                 'anchor' => '', //ancla si la tuviera
                                 'item_name' => '', //la url de antes de una extension(ej: html) y desde el ultimo / que la contiene
                                 'item_id' => 0,
								 'item_ext' => null
                            ];

        $this->args =  $main_data;

        //Extraemos toda la informacion de la url
        $url = $this->getUrlParams();

        //Asignamos los parámetros get por si no los ha cogido bien por la configuracion de apache
        $query_strings = $this->parseQueryString($url['query']?? []);
        $this->args = $this->args + $query_strings;

        //Assign defaults params
        $this->args =  $this->args + $this->data;

        $this->parsePath($url['path']?? '');

		$this->data = $this->args;
    }

    protected function getUrlParams() {
       return parse_url($this->args["raw"]);
    }

    protected function parseQueryString($query_string) {
        if (!empty($query_string)) {
            parse_str($query_string, $query);
        }

        return (array) $query_string;
    }

    protected function parsePath($path) {
        if(!empty($path)) {
            $url_path_list = explode('/', trim($path, '/'));

            //Quitamos todo lo que no sea adecuado
            $this->args['url_path_list'] = array_filter( $url_path_list, ['\team\data\Sanitize', 'key']);

            //Vamos a analizar el último elemento
            $last = end(  $this->args['url_path_list']);

            $this->parseLastElement($last);

            $this->args['location'] = '/'.implode('/', (array)$this->args["url_path_list"]);

            if (!empty($this->args['item_name'])) {
                $this->args['location'] .= '/'.$this->args['item_name'];

                if (!empty($this->args['item_id']))
                    $this->args['location'] .= '-'.$this->args['item_id'];

                if (!empty($args['item_ext']))
                    $this->args['location'] .= '.'.$this->args['item_ext'];
            }
        }
    }

    protected function parseLastElement($last) {
        if(!empty($last)) {
            //¿Es un elemento?
            $ext_position = strpos($last, '.');
            $item_extension = null;
            if (false !== $ext_position) {
                $extension = strtolower(substr($last, $ext_position + /* point */ 1) );
                $extensions = \Team\Data\Filter::apply('\team\url\extensions', ['html' => 'html', 'htm' => 'html', 'json' => 'json', 'php' => 'html', 'xml' => 'xml']);
                if(isset($extensions[$extension])) {
                    $out = $extensions[$extension];
                    $item_extension = $extension;
                }
            }


            if ($item_extension) {
                $item = array_pop( $this->args['url_path_list']);

                $this->args['item_ext'] = $item_extension;

                $item = \Team\Data\Sanitize::urlFriendly($item);


                $item_expression = '/(?<item_name>[a-z0-9\-\_\+]+?)(-(?<item_id>\d+))?$/x';

                if (preg_match($item_expression, $item, $result)) {
                    if(!isset( $this->args['out']))
                        $this->args['out'] = $out;

                    if (isset($result['item_name']))
                        $this->args['item_name'] = $result['item_name'];

                    if (isset($result['item_id']))
                        $this->args['item_id'] = \Team\Data\Check::id($result['item_id']);
                }
                $last = end(  $this->args['url_path_list']);
            }

        }
    }


	public function check($pattern, &$new_args = [], $defaults = []) {
		return \Team\Client\Url::match($this->data['raw'], $pattern, $new_args, $defaults);
	}

	/**
		Añade valores acorde a un patrón que se lanzará contra la url de raw
		@param string $pattern es el patrón que se usará contra raw y por el que se obstendrá nuevos valores
		@params array $others son otros valores que se usaran por defecto,tenga o no, éxito.
		@param callable $callback es una función que se podrá usar para validar los valores de data si hace match el patrón. 
	*/
	public function fromPattern($pattern = '', array $others = [], callable $callback = null) {
	     $args = $others;
		 if( $this->check($pattern, $args) ) {

			$with_callback = isset($callback);
			if($with_callback ) {
				$result = $callback($this, $args);
				
				$the_result_is_new_data = isset($result) && is_array($result);
				if($the_result_is_new_data  ) {
					$this->data = $result;
				}
			}else {
				$this->data = $args  + $this->data;
			}
 		 }

		return $this->data;
	}


    public function export($_target = null, Array $_data = [] ) {
		return \Team\Client\Url::to($_target, $_data);

    }
}
