<?php
/**
 * This file is part of #MobMin Community.
 * 
 * #MobMin Community is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Joshua Project API is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see 
 * <http://www.gnu.org/licenses/>.
 *
 * @author Johnathan Pulos <johnathan@missionaldigerati.org>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
 namespace Resources;

/**
 * The Model object for eavh resource
 * @todo Implement update script
 */
class Model
{
    /**
     * The table name to query
     *
     * @var string
     * @access protected
     **/
    protected $tableName = '';
    /**
     * The primary key of the table
     *
     * @var string
     * @access protected
     **/
    protected $primaryKey = 'id';
    /**
     * An array of whitelisted attributes
     *
     * @var array
     * @access protected
     **/
    protected $accessibleAttributes = array();
    /**
     * The table prefix for the links table
     *
     * @var string
     * @access protected
     **/
    protected $tablePrefix = '';
    /**
     * The database object
     *
     * @var \PDO
     * @access protected
     **/
    protected $db;
    /**
     * The last inserted id. Set to null if the insert failed.
     *
     * @var integer
     * @access public
     **/
    protected $lastID = null;
    /**
     * Construct the model object
     *
     * @param \PDO $db The database connection
     * @return void
     * @throws InvalidArgumentException if $db is not a \PDO Object
     * @throws LogicException if the database table does not exist
     * @author Johnathan Pulos
     **/
    public function __construct($db)
    {
        $this->setDatabaseObject($db);
        $this->tableExists();
    }
    /**
     * Set the table prefix for the database table
     *
     * @param string $prefix the table prefix
     * @return void
     * @access public
     * @author Johnathan Pulos
     **/
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
    }
    /**
     * get the last inserted id
     *
     * @return int The last id
     * @access public
     * @author Johnathan Pulos
     **/
    public function getLastID()
    {
        return $this->lastID;
    }
    /**
     * Checks if the object exists
     *
     * @param string $value The value to look up
     * @param string $column The column to check (default: id)
     * @return boolean exists?
     * @access public
     * @author Johnathan Pulos
     **/
    public function exists($value, $column = null)
    {
        if (is_null($column)) {
            $column = $this->primaryKey;
        }
        if (($column != $this->primaryKey) && (!in_array($column, $this->accessibleAttributes))) {
            throw new \InvalidArgumentException('$column must be accessible on the Model.');
        }
        $stmt = $this->db->prepare("SELECT * FROM " . $this->tablePrefix . $this->tableName . " WHERE " . $column . " = :value");
        $stmt->bindValue(":value", $value);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }
    /**
     * Insert a tweet feed avatar to the avatar table
     *
     * @param array $data The data to be saved
     * @return boolean Did it save the data?
     * @access public
     * @author Johnathan Pulos
     **/
    public function save($data)
    {
        return $this->insertRecord($data);
    }
    /**
     * Update a record
     *
     * @param array $data The data to be saved
     * @param integer $id The id of the record to save
     * @return boolean Did it save the data?
     * @access public
     * @throws InvalidArgumentException if record does not exist
     * @author Johnathan Pulos
     **/
    public function update($data, $id)
    {
        return $this->updateRecord($data, $id);
    }
    /**
     * Find the record by a given column
     *
     * @param string $column The column name to search by
     * @param mixed $val The value of the column
     * @return array The record that was found
     * @access public
     * @author Johnathan Pulos
     **/
    public function findBy($column, $val)
    {
        if ((!in_array($column, $this->accessibleAttributes)) && ($column != $this->primaryKey)) {
            throw new \InvalidArgumentException('$column is unaccessible.');
        }
        $stmt = $this->db->prepare("SELECT * FROM " . $this->tablePrefix . $this->tableName . " WHERE " . $column . " = :val LIMIT 1");
        $stmt->bindValue(":val", strip_tags($val));
        $stmt->execute();
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return ($stmt->rowCount() > 0) ? $data[0] : array();
    }
    /**
     * Set the PDO Database Object
     *
     * @param \PDO $db The database connection
     * @return void
     * @access protected
     * @throws InvalidArgumentException if $db is not a \PDO Object
     * @author Johnathan Pulos
     **/
    protected function setDatabaseObject($db)
    {
        if (is_a($db, 'PDO')) {
            $this->db = $db;
        } else {
            throw new \InvalidArgumentException('$db must be of the class \PDO.');
        }
    }
    /**
     * Checks if the Model's table exists.  Throws an error if it is missing.
     *
     * @return void
     * @access protected
     * @author Johnathan Pulos
     **/
    protected function tableExists()
    {
        $table = $this->db->query("SHOW TABLES LIKE '" . $this->tablePrefix . $this->tableName . "'");
        if ($table->rowCount() == 0) {
            throw new \LogicException("The table '" . $this->tablePrefix . $this->tableName . "' is missing.");
        }
    }
    /**
     * Insert a new Link Resource
     *
     * @param array $data an array of the link data to save
     * @return boolean Did it save?
     * @author Johnathan Pulos
     **/
    protected function insertRecord($data)
    {
        $stmt = $this->db->prepare($this->getInsertQuery($data));
        $stmt = $this->bindValues($stmt, $data);
        $saved = $stmt->execute();
        if ($saved === true) {
            $this->lastID = $this->db->lastInsertId();
        } else {
            $this->lastID =  null;
        }
        return $saved;
    }
    /**
     * update the given record with the given data
     *
     * @param array $data an array of data to update
     * @param integer $id The id of the record to update
     * @return boolean Did it update?
     * @access protected
     * @throws InvalidArgumentException if record does not exist
     * @author Johnathan Pulos
     **/
    protected function updateRecord($data, $id)
    {
        if ($this->exists($id, $this->primaryKey) === false) {
            throw new \InvalidArgumentException("The record with id = " . $id . " does not exist.");
        }
        $stmt = $this->db->prepare($this->getUpdateQuery($data));
        $stmt = $this->bindValues($stmt, $data);
        $stmt->bindValue(":" . $this->primaryKey, intval($id));
        $saved = $stmt->execute();
        if ($saved === true) {
            $this->lastID = $id;
        } else {
            $this->lastID =  null;
        }
        return $saved;
    }
    /**
     * Generates the insert SQL query based on a cleanNonWhitelistedData() data array
     *
     * @param array $data an array of the link data to save
     * @return string The final Query statement
     * @access protected
     * @author Johnathan Pulos
     **/
    protected function getInsertQuery($data)
    {
        $data = $this->cleanNonWhitelistedData($data);
        $query = "INSERT INTO " . $this->tablePrefix . $this->tableName . "(" .
            implode(', ', array_keys($data)) . ") VALUES(:" . implode(', :', array_keys($data)) . ")";
        return $query;
    }
    /**
     * Generates the update SQL query based on a cleanNonWhitelistedData() data array
     *
     * @param array $data an array of the link data to save
     * @return string The final Query statement
     * @access protected
     * @author Johnathan Pulos
     **/
    protected function getUpdateQuery($data)
    {
        $data = $this->cleanNonWhitelistedData($data);
        $query = "UPDATE " . $this->tablePrefix . $this->tableName . " SET ";
        $valueStatements = array();
        foreach ($data as $key => $value) {
            array_push($valueStatements, $key . " = :" . $key);
        }
        $query .= implode(", ", $valueStatements);
        $query .= " WHERE " . $this->primaryKey . " = :" . $this->primaryKey;
        return $query;
    }
    /**
     * Bind the values to the POD statement
     *
     * @param PDOStatement $statement The statement to bind values to
     * @param array $data The data to save regarding the Resource
     * @return \PDOStatement The statement object
     * @author Johnathan Pulos
     **/
    protected function bindValues($statement, $data)
    {
        $data = $this->cleanNonWhitelistedData($data);
        foreach ($data as $key => $value) {
            $newValue = $this->prepareAttribute($key, $value);
            $statement->bindValue(":" . $key, $newValue);
        }
        return $statement;
    }
    /**
     * prepare the attribute before binding to the PDOStatement
     *
     * @param string $key The attribute name
     * @param mixed $value The given value to save
     * @return mixed The final prepared value
     * @access protected
     * @author Johnathan Pulos
     **/
    protected function prepareAttribute($key, $value)
    {
        return $value;
    }
    /**
     * Removes any nonwhitelisted values from the data array
     *
     * @param array $data The data to clean
     * @return array A cleaned data array
     * @access protected
     * @author Johnathan Pulos
     **/
    protected function cleanNonWhitelistedData($data)
    {
        $cleanedData = array();
        foreach ($data as $key => $value) {
            if ((in_array($key, $this->accessibleAttributes)) || ($key == $this->primaryKey)) {
                $cleanedData[$key] = $value;
            }
        }
        return $cleanedData;
    }

}