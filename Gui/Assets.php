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

namespace Team\Gui;

trait Assets
{

    /**
     * Enqueue a css file in order to include it in views
     * @param string $file it is a file css or url to file css
     * @param string $position place where css file will be included
     * @param string $idfile indentifier for css file
     *
     */
    public function addCss(string $file, string $position = 'top', string $idfile = null)
    {
        $CSS_EXTENSION = '.css';

        $file = \Team\System\FileSystem::stripExtension($file,  $CSS_EXTENSION);

        // maybe double slash(//) is used  => '//file.css'
        $is_external_css = strpos($file, '//') !== false;

        $idfile = $idfile ?? \Team\Data\Sanitize::identifier($file);

        if ($is_external_css) {
            $file = $file . $CSS_EXTENSION;
        } else {
            $file = \Team\System\Context::get('_THEME_') . '/' . ltrim($file, '/');

            $less = new Less();
            $less->addFile($file);
            $file = $less->parser();
        }

        if ($file) {
            \Team\Config::add("\\team\\css\\{$position}", $idfile, $file);
        }
    }

    /**
     * Enqueue a js file in order to include it in views
     * @param string $file it is a file js or url to file js
     * @param string $position place where js file will be included
     * @param string $idfile indentifier for js file
     *
     */
    public function addJs($file, $position = 'bottom', $idfile = null)
    {
        $file = \Team\System\FileSystem::stripExtension($file, '.js');

        // maybe double slash(//) is used  => '//file.js'
        $is_external_js = strpos($file, '//') !== false;
        $idfile = $idfile ?? \Team\Data\Sanitize::identifier($file);

        if (!$is_external_js) {
            $file = \Team\System\Context::get('_THEME_') . '/' . ltrim($file, '/');
        }

        //normalize
        $file = $file . '.js';

        if ($is_external_js || \Team\System\FileSystem::exists($file, _SCRIPTS_)) {
            \Team\Config::add("\\team\\js\\{$position}", $idfile, $file);
        } else {
            \Team\Debug::me("Javascript file[$position] $file not found", 3);
        }
    }
}
