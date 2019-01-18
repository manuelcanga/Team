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

namespace Team\Db;

/**
 * Simple ActiveRecord class for Team Framework
 */
abstract class ActiveRecord implements \ArrayAccess
{

    use \Team\Data\Storage, \Team\Db\Database;

    const ID    = '';
    const TABLE = '';

    protected $safeId = 0;

    /**
     * Construct a ActiveRecord
     *
     * @param mixed $id   :  primary key or key used in order to initialize
     * @param array $data : data for initializing
     */
    public function __construct($id = 0, array $data = [])
    {

        $this->setSafeId($id);

        if ($this->safeId) {
            $this->initializeIt($this->safeId);
        }

        $this->onImport($data, $id);
    }

    /* ----------------- Checks----------------- */

    public function setSafeId($newId)
    {
        $this->safeId = $this->checkId($newId, 0);
    }

    /**
     * Validamos el campo clave ID del activerecord
     *
     * @param $id es el valor a usar como campo clave
     */
    public function checkId($id, $default = 0)
    {

        return \Team\Data\Check::key($id, $default);
    }

    /**
     * Initialize by default
     */
    protected function initializeIt($id)
    {

        $query          = $this->newQuery([static::ID => $id]);
        $query->where[] = [static::ID => ':' . static::ID];
        $record         = $query->getRow(static::TABLE);

        $this->onInitialize($id, (array)$record);
    }

    /* ----------------- Geters and Setters ----------------- */

    protected function onInitialize($id, $data = [])
    {

        $this->loadData($data);
    }

    protected function loadData(array $data = [])
    {

        if (! empty($data)) {
            $this->set($data);
            $this->safeId = $this[ static::ID ];
        }

        $this->onUnserialize();
    }

    protected function onUnserialize()
    {
    }

    /* ----------------- Results----------------- */

    /**
     * Initialize by default
     */
    protected function onImport($data)
    {
        if (is_array($data)) {
            $this->import($data);
        }
    }


    /* ----------------- QUERIES ----------------- */

    /**
     * Retrieve all rows from table TABLE
     *
     * @param array $sentences list of params to query. Excepcionally, you can pass a 'order' params(ASC or DESC)
     * @param array $data      list of data to query
     */
    public static function findAll(array $sentences = [], array $data = [], $result_type = null)
    {
        $sentences = $sentences??[];

        $order = 'DESC';
        if (isset($sentences[ 'order' ])) {
            $order = $sentences[ 'order' ];
            unset($sentences[ 'order' ]);
        }

        $default = ['select' => '*', 'limit' => -1, 'order_by' => [static::ID => $order]];

        $sentences = $sentences + $default;

        $query = self::getNewQuery($data, $sentences);

        $records = $query->getAll(static::TABLE);

        if (is_string($result_type) && "array" == $result_type) {
            return $records;
        }

        if (! isset($result_type)) {
            $result_type = static::CLASS;
        }

        return new \Team\Db\Collection($records, $result_type);
    }

    public function isSafe()
    {
        return (bool)$this->safeId;
    }

    public function exists($name = null)
    {
        if (! isset($name)) {
            return $this->exists(static::ID);
        }

        return isset($this->data[ $name ]);
    }

    public function & getId()
    {
        return $this->safeId;
    }

    /**
     * Create a iterator for registers
     */
    public function newCollection(array $registers, array $defaults = [])
    {
        return new \Team\Db\Collection($registers, get_class($this), $defaults);
    }

    public function save()
    {
        if ($this->safeId) {
            $result = $this->updateIt($secure = true);
        } else {
            $result = $this->insertIt();
        }

        return $result;
    }

    public function updateIt($secure = true)
    {
        $this->onSerialize('update');
        $this->commons('update');

        $this->data[ static::ID ] = $this->safeId;

        $query          = $this->newQuery($this->data);
        $query->where[] = [static::ID => ':' . static::ID];

        $result = $query->update(static::TABLE, $secure);

        if ($result) {
            $this->custom("update");
        }

        return $result;
    }

    protected function onSerialize($operation)
    {
    }

    protected function commons($operation)
    {
    }

    protected function custom($operation)
    {
    }

    /* ----------------- EVENTS ----------------- */

    public function insertIt()
    {
        $this->onSerialize('insert');
        $this->commons('insert');

        if (! isset($this[ static::ID ])) {
            $this[ static::ID ] = null;
        }

        $query = $this->newQuery($this->data);
        $newId = $query->add(static::TABLE);

        if ($newId) {
            $this->setSafeId($newId);
            $this->data[ static::ID ] = $this->safeId;

            $this->custom('insert');
        }

        return $newId;
    }

    //Before updating, creating register

    public function serializeIt($field)
    {
        $this[ $field ] = '';

        if (is_array($this->$field)) {
            $this[ $field ] = json_encode($this->$field);
        }
    }

    //After updating, creating or removing  register

    public function unSerializeIt($field)
    {
        $this->$field = [];

        if (is_string($this[ $field ])) {
            $array_with_values = json_decode($this[ $field ], $assoc = true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $this->$field = $array_with_values;
            }
        }
    }

    /**
     * Realiza el borrado en la base de datos.
     * Si $secure es true, no se podrá hacer un delete sin where y los delete con where estarán limitados a un elemento.
     */
    public function removeIt($secure = true)
    {
        if (! $this->safeId) {
            return false;
        }

        $query = $this->newQuery([static::ID => $this->safeId]);

        $query->where[] = [static::ID => ':' . static::ID];

        $result = $query->delete(static::TABLE, $secure);

        if ($result) {
            $this->custom('remove');
        }

        return $result;
    }

    /**
     * This public function changes a value of database field in current record.
     * Be careful, this public function does a eval with arguments
     *
     * @example  $this->changeIt('counter','+', 1)
     * This example add + 1 in counter field for current record
     *
     * @param        $field
     * @param string $operation
     * @param int    $amount
     *
     * @return mixed
     */
    public function changeIt($field, $operation = '+', $amount = 1)
    {
        if (! $this->safeId) {
            return false;
        }

        $query = $this->newQuery([static::ID => $this->safeId]);

        $query->$field = "{$field} {$operation} {$amount}";

        $query->where = [static::ID => ':' . static::ID];

        $query_result = $query->update(static::TABLE);

        if ($query_result) {
            $initial_amount = $this->$field;
            $result         = 0;
            eval('$result  = ' . $initial_amount . ' ' . $operation . ' ' . $amount . ';');
            $this->$field = $result;
        }

        return $query_result;
    }

    /**
     * Count all rows in table
     */
    public function countAll(array $sentences = [], array $data = [])
    {
        $query = $this->newQuery($data, $sentences);

        return $query->getVar('total', static::TABLE, 'count(' . static::ID . ') as total');
    }

    //This public function from Collection for everytime a newRecord is created

    public function onNewRecord(array $data = [])
    {
        $this->loadData($data);
    }
}
