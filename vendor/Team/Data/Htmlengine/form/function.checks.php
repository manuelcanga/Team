<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga MuÃ±oz
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
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga MuÃ±oz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */

/*
 * Show check elements
 * -------------------------------------------------------------
 * File:     function.check.php
 * Type:     function
 * Name:     Team
 * Purpose:   tools smarty
 * Example:   {checks name='form[vehiculos][]' layout='<div class="form-control"><p><label>:INPUT :LABEL</label></p></div>' values=$transportista->PesosVehiculos}
 * -------------------------------------------------------------
 */

function smarty_function_checks($params, &$smarty)
{


    $name = $params['name']?? '';
    $class = $params['class']?? '';
    $values = $params['values']??  [];
    $checked = $params['checked']?? [];
    $layout = $params['layout']?? '';

    if(empty($values)) return '';


    $out = '';
    $i = 1;
    foreach($values as $key => $label) {
        $is_checked =  (in_array($key, $checked, $strict = true))? 'checked="checked"' : '';

        $input = '<input type="checkbox" name="'.$name.' class="'.$class.'"  value="' . $key . '"  '.$is_checked.' />';

        $new_check = $layout;
        $new_check = str_replace(':ID', \Team\Data\Sanitize::identifier($label), $new_check);
        $new_check = str_replace(':POS', $i, $new_check);
        $new_check = str_replace(':LABEL', $label, $new_check);
        $new_check = str_replace(':INPUT', $input, $new_check);


        $out .= $new_check;
        $i++;
    }

    return $out;
}

