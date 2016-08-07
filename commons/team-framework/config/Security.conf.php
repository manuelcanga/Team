<?php


namespace config\team;


class Security extends \team\Config{
	
	/**
		Decidimos si cancelamos el codigo extra a las vistas(true si si, false si no )
		Esto ocultara la salida de contenidos que no sean las vistas de las GUI
		Ejemplo, echos de depuracion, espacios en blanco, mensajes de error, etc
	*/
	protected $SHOW_EXTRA = false;
} 
