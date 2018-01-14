<?php

namespace team\db\queries;

trait Update {
  
    /**
    Lanzamos la consulta de actualización
     Ejemplo 1:
    $query = new \team\db\Query();
    $query->email("minuevoemail@este.es"); //Pasando un dato
    $query->where = "idAccess > :min_id and IdAccess < :max_id OR IdAccess = :max_id"; //literal
    $query->min_id(400); //pasando un dato
    $query->max_id(500); //pasando un dato
    $query->update("@Access");  //Accion

    Ejemplo 2:
    $query = new \team\db\Query();
    $query->email("minuevoemail@este.es"); //pasando un dato
    $query->where = " id = 20 ";  //literal
    $query->update("@Access"); /Accion

    Ejemplo 3:
    $query = new \team\db\Query();
    $query->email("minuevoemail@este.es"); //pasando un dato
    $query->name("Team"); //pasando un dato
    $query->web("http://latrasweb.net"); //pasando un dato
    $query->where = "id = :id "; //literal
    $query->id($id); //id contiene 4000 por ejemplo
    $query->update("@User"); //pasando un dato

    Ejemplo 4:

    //Valores necesarios
    $data = new \team\data\Data();
    $data->title = $title;
    $data->idwidget = $idwidget;
    $data->iduser = \team\User::get();

    //Consulta
    $db = new \team\db\Query($data);
    $db->posx = $posx;
    $db->width($width);
    $db->posy = $posy;
    $db->height = $height;
    $db->where = " id = :idwidget and user = :iduser ";
    Da como resultado:
    UPDATE tw_desktops SET  title = :title, posx = 371, posy = 268, width = :width, height = 48 WHERE  id = :idwidget and user = :iduser
    Con los datos:  Array(    [title] => Mensajes    [idwidget] => 3    [iduser] => 1  [width] => 48)

    Ejemplo 5:
    $db = new \team\db\Query();

    //Valores necesarios
    $db->idwidget($idwidget);
    $db->iduser(\team\User::get() );

    //Consulta
    $db->title($title);
    $db->posx($posx);
    $db->posy($posy);
    $db->width($width);
    $db->height($height);
    $db->where = " id = :idwidget and user = :iduser ";
    Da como resultado:
    UPDATE tw_desktops SET  title = :title, posx = :posx, posy = :posy, width = :width, height = :height WHERE  id = :idwidget and user = :iduser
    Con los datos:Array( [title] => Mensajes[posx] => 357 [posy] => 463 [width] => 48  [height] => 48 [idwidget] => 3  [iduser] => 1)

	//En modo seguro sólo se actualizará un registro
     */
    public function update($table, $secure = true) {

		$sentences =  $this->get();

		$sets = (array) $this->values;

		$where = '';
		if(isset( $sentences['where']) ) {
			$where =  (array) $sentences['where'];
			
			unset($sentences['where']);
		}

       $sets  = $this->removeBecauseOfWhere($where, $sets );


		$sets = $this->paramSets($sets);

		/*
			los sentences que no son where son sets y por tanto, hay que pasarlo a sets.
			Ejemplo:
			$update = new Update(@tabla);
			$update->contador = contador + 1;
			$update->id = 10;
			$update->contador(10);
			$update->where = 'contador > :contador'; 
	
			contador = contador + 1. Es un sets y no un sentences.
		*/

		$sets = $sentences + $sets;


		return $this->database->update($table, $sets, $where,   $this->values /** Valores */, $secure);
    }

   /**
        Las etiquetas( :cadena ) que hubiera en el where
        se quita de los set para evitar problemas. Ejemplo a evitar:
		$update = new Update(@tabla);
		$update->contador = contador + 1;
		update->id = 10;
		$update->where = 'id > :id'; 
	
		que equivaldría a la consulta:
		update @tabla set id = 10, contador = contador + 1 where id > 10;

		sin embargo, si las variables de los where la quitamos de los set sería:
		update @tabla set contador = contador + 1 where id > 10;

        Si se quería actualizar todos los ids entonces que se usen dos etiquetas diferentes 	
        aunque luego tengan el mismo valor
     */
    protected function removeBecauseOfWhere($wheres, $sets) {
        

		foreach($wheres as $_where) {

			if(is_array($_where) ) {
				//estamos en un caso tipo: ['id' => ':idtag' ]

				$keys = array_keys($_where);		
				$key = $keys[0];
				$tag = trim($_where[$key],':');

			 	if(array_key_exists($tag, $sets) ) {
			            unset($sets[$tag]);
			     }

			}else {
				//estamos ante un caso  id = ':id'
				$matches = array();
				$result = preg_match_all("/[:](.*?) /", $_where." ", $matches);

				if(!empty($matches) && 2 == count($matches) ) {
					foreach($matches[1] as $_index => $key) {
					    if(array_key_exists($key, $sets) ) {
					        unset($sets[$key]);
					    }
					}
				}
			}
		}	


        return $sets;
    }


	/** 
		Transform sets in params 
		$sets['title'] = 'Mi post'   <Se transforma a> $sets['title'] => ':title';
	*/
	protected function paramSets($sets) {
  	 $_set = [];

	 foreach($sets as $key => $value) {
		$_set[$key] = ":$key";
	 }

	  return $_set;
	}
}
