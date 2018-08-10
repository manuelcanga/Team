<?php
namespace LesserPhp;

use LesserPhp\Exception\GeneralException;

/**
 * The LESS compiler and parser.
 *
 * Converting LESS to CSS is a three stage process. The incoming file is parsed
 * by `lessc_parser` into a syntax tree, then it is compiled into another tree
 * representing the CSS structure by `lessc`. The CSS tree is fed into a
 * formatter, like `lessc_formatter` which then outputs CSS as a string.
 *
 * During the first compile, all values are *reduced*, which means that their
 * types are brought to the lowest form before being dump as strings. This
 * handles math equations, variable dereferences, and the like.
 *
 * The `parse` function of `lessc` is the entry point.
 *
 * In summary:
 *
 * The `lessc` class creates an instance of the parser, feeds it LESS code,
 * then transforms the resulting tree to a CSS tree. This class also holds the
 * evaluation context, such as all available mixins and variables at any given
 * time.
 *
 * The `lessc_parser` class is only concerned with parsing its input.
 *
 * The `lessc_formatter` takes a CSS tree, and dumps it to a formatted string,
 * handling things like indentation.
 */
interface CompilerInterface
{
    /**
     * Compiles a primitive value into a CSS property value.
     *
     * Values in lessphp are typed by being wrapped in arrays, their format is
     * typically:
     *
     *     array(type, contents [, additional_contents]*)
     *
     * The input is expected to be reduced. This function will not work on
     * things like expressions and variables.
     *
     * @param array $value
     * @param array $options
     *
     * @return string
     * @throws GeneralException
     */
    public function compileValue(array $value, array $options = []);

    /**
     * Helper function to get arguments for color manipulation functions.
     * takes a list that contains a color like thing and a percentage
     *
     * @param array $args
     *
     * @return array
     */
    public function colorArgs(array $args);

    /**
     * @param array $value
     * @param bool  $forExpression
     *
     * @return array|bool|mixed|null // <!-- dafuq?
     */
    public function reduce(array $value, $forExpression = false);

    /**
     * make sure a color's components don't go out of bounds
     *
     * @param array $c
     *
     * @return mixed
     */
    public function fixColor(array $c);

    /**
     * @param string $string
     * @param string $name
     *
     * @return string
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function compile($string, $name = null);

    /**
     * @param string $fname
     * @param string $outFname
     *
     * @return int|string
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function compileFile($fname, $outFname = null);

    /**
     * Based on explicit input/output files does a full change check on cache before compiling.
     *
     * @param string  $in
     * @param string  $out
     * @param boolean $force
     *
     * @return string Compiled CSS results
     * @throws GeneralException
     */
    public function checkedCachedCompile($in, $out, $force = false);

    /**
     * compile only if changed input has changed or output doesn't exist
     *
     * @param string $in
     * @param string $out
     *
     * @return bool
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function checkedCompile($in, $out);

    /**
     * Execute lessphp on a .less file or a lessphp cache structure
     *
     * The lessphp cache structure contains information about a specific
     * less file having been parsed. It can be used as a hint for future
     * calls to determine whether or not a rebuild is required.
     *
     * The cache structure contains two important keys that may be used
     * externally:
     *
     * compiled: The final compiled CSS
     * updated: The time (in seconds) the CSS was last compiled
     *
     * The cache structure is a plain-ol' PHP associative array and can
     * be serialized and unserialized without a hitch.
     *
     * @param mixed $in    Input
     * @param bool  $force Force rebuild?
     *
     * @return array lessphp cache structure
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function cachedCompile($in, $force = false);

    /**
     * parse and compile buffer
     * This is deprecated
     *
     * @param null $str
     * @param null $initialVariables
     *
     * @return int|string
     * @throws \LesserPhp\Exception\GeneralException
     * @deprecated
     */
    public function parse($str = null, $initialVariables = null);

    /**
     * @param string $name
     */
    public function setFormatter($name);

    public function setFormatterClass($formatter);

    /**
     * @param bool $preserve
     */
    public function setPreserveComments($preserve);

    /**
     * @param string   $name
     * @param callable $func
     */
    public function registerFunction($name, callable $func);

    /**
     * @param string $name
     */
    public function unregisterFunction($name);

    /**
     * @param array $variables
     */
    public function setVariables(array $variables);

    /**
     * @param $name
     */
    public function unsetVariable($name);

    /**
     * @param string[] $dirs
     */
    public function setImportDirs(array $dirs);

    /**
     * @param string $dir
     */
    public function addImportDir($dir);

    /**
     * @return string[]
     */
    public function getImportDirs();

    /**
     * @param string $file
     */
    public function addParsedFile($file);

    /**
     * Uses the current value of $this->count to show line and line number
     *
     * @param string $msg
     *
     * @throws GeneralException
     */
    public function throwError($msg = null);

    /**
     * prefix of abstract properties
     *
     * @return string
     */
    public function getVPrefix();

    /**
     * prefix of abstract blocks
     *
     * @return string
     */
    public function getMPrefix();

    /**
     * @return string
     */
    public function getParentSelector();

    public function setImportDisabled();

    /**
     * @return bool
     */
    public function isImportDisabled();
}
