<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, in version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.body
 * Type:     block
 * Name:     body
 * Purpose:  output a body tag
 * -------------------------------------------------------------
 */

function smarty_block_body($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    $place = 'body';
    if (isset($params['place'])) {
        $place = $params['place'];
        unset($params['place']);
    }

    $out = '';
    if ($repeat) { //open tag
        return $out;
    } else {//close tag

        //Atributos del body
        $app = \Team\System\Context::get('APP');
        $component = \Team\System\Context::get('COMPONENT');
        $response = \Team\System\Context::get('RESPONSE');

        $default = [
            'id' => $app . '_' . $component,
            'data-app' => $app,
            'data-response' => $response,
        ];

        /* Body Classes */
        $body_classes = \Team\System\Context::get('BODY_CLASSES');

        if (\Team\Client\Http::checkUserAgent('mobile')) {
            $body_classes[] = 'movil';
        } else {
            $body_classes[] = 'desktop';
        }
        $body_classes[] = \Team\Client\Http::checkUserAgent('navigator');

	    if (\Team\Client\Http::checkUserAgent('mobile') && strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
		    $body_classes[] = "firefox_movil";
	    }

	    $body_classes[] = $params['class'] ?? '';

        $params['class'] = \Team\Gui\Place::getClasses($place, $body_classes);

        if (empty($params['class'])) {
            unset($params['class']);
        }

        $out .= '<body';
        $params = \Team\Data\Filter::apply('\team\tag\body\params', $params + $default);
        foreach ($params as $attr => $value) {
            $out .= " {$attr}='{$value}'";
        }
        $out .= '>';

        $out .= trim(\team\data\Filter::apply('\team\tag\body', $content, $params, $template));

        /* ******************** BOTTOM CSS Y JS FILES *************** */

        //BOTTOM CSS
        $css_files = \Team\Config::get('\team\css\bottom', []);

        if (!empty($css_files)) {
            foreach ($css_files as $id => $file) {
                $out .= "<link href='{$file}' rel='stylesheet'/>";
            }
        }

        //BOTTOM JS
        $js_files = \Team\Config::get('\team\js\bottom', []);

        if (!empty($js_files)) {
            foreach ($js_files as $id => $file) {
                $out .= "<script src='{$file}'></script>";
            }
        }

        /* ******************** /BOTTOM CSS Y JS FILES *************** */

        $out = \Team\Gui\Place::getHtml($place, $out, $params, $template);
        $out .= '</body></html>';

        return $out;
    }
}
