<?php

namespace Kanso\Database\Query;

/**
 * Alter Database Class
 *
 * This class is used by Builder to alter, create, delete
 * tables and columns
 */

class Alter
{

    /**
     * @var string    The current table name to operate on
     */ 
    private $tableName;

    /**
     * @var string    The current column to operate on
     */
    private $column;

    /**
     * @var array    Array of columns and column parameters of current table
     */
    private $columns;

    /**
     * @var \Kanso\Database\Database;
     */
    private $Database;
    
    /**
     * Constructor
     *
     * @param string    $tableName
     * @param \Kanso\Database\Database $Database
     */
    public function __construct($tableName, $Database)
    {
        # Set the current database object instance
        $this->Database  = $Database;

        # Set the current table name to operate on
        $this->tableName = $tableName;

        # Load columns and column parameters of current table
        $this->loadColumns();
    }

    /********************************************************************************
    * PUBLIC ACCESS FOR TABLE AND COLUMN MANAGEMENT
    *******************************************************************************/

    /**
     * Add a new column to the current table
     *
     * @param  string    $column
     * @param  string    $dataType    The column parameters
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_COLUMN($column, $dataType)
    {

        # Convert the column to valid syntax
        $column = $this->indexFilter($column);

        # Validate the column does NOT already exist
        if ($this->columnExists($column)) throw new \Exception("Error adding column $column. The column already exists.");

        # Execute the SQL
        $this->Database->query("ALTER TABLE `$this->tableName` ADD `$column` $dataType");

        # Reload the columns
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop an existing column from the current table
     *
     * @param  string    $column
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_COLUMN($column)
    {
        # Convert the column to valid syntax
        $column = $this->indexFilter($column);

        # Validate the column already exists
        if (!$this->columnExists($column)) throw new \Exception("Error dropping column $column. The column does NOT exist.");

        # Execute the SQL
        $this->Database->query("ALTER TABLE `$this->tableName` DROP `$column`");
        
        # Remove the column from list
        unset($this->columns[$column]);

        # Return Alter for chaining
        return $this;
    }

    /**
     * Modify a column's parameters or set the current 
     * operation to change a columns configuration
     *
     * Note this function must be called before using
     * any of the configuration/constraint methods below
     *
     * @param  string    $column
     * @param  string    $dataType (optional)
     * @return \Kanso\Database\Query\Alter
     */
    public function MODIFY_COLUMN($column, $dataType = null)
    {
        # Convert the column to valid syntax
        $column = $this->indexFilter($column);

        # Validate the column already exists
        if (!$this->columnExists($column)) throw new \Exception("Error modifying column $column. The column does NOT exist.");

        # Set the column
        $this->column = $column;

        # If there is no type return
        if (!$dataType) return $this;

        # Otherwise change the columns datatype
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$column` $dataType");

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add a primary key to the current column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_PRIMARY_KEY()
    {

        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # If PRIMARY KEY is already set on this column return
        if (strpos($colConfig, 'PRIMARY KEY') !== FALSE) return $this;

        # Change the existing column - Drop the PRIMARY KEY
        $PK = $this->getPrimaryKey();
        if ($PK) $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$PK` INT(11) NOT NULL UNIQUE, DROP PRIMARY KEY");
        
        # Set the PRIMARY KEY to this column
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` INT(11) NOT NULL UNIQUE PRIMARY KEY");
        
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop the table's current primary key
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_PRIMARY_KEY()
    {
        # Check if there is an existing primary key
        $PK = $this->getPrimaryKey();

        # Drop the primary key
        if ($PK) $this->Database->query("ALTER TABLE `$this->tableName` DROP PRIMARY KEY");
        
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add not null to a column
     *
     * @param $notNull  mixed (optional) Value to set null values to 
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_NOT_NULL($notNull = 0)
    {
        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # If not null is already set return
        if (strpos($colConfig, 'NOT NULL') !== FALSE) return $this;

        # Remove all null values
        $this->Database->query("UPDATE `$this->tableName` SET `$this->column` = :not_null WHERE `$this->column` IS NULL", ['not_null' => $notNull]);

        # If the default is set to null remove it
        if (strpos($colConfig, 'DEFAULT NULL') !== FALSE) $colConfig = str_replace('DEFAULT NULL', "DEFAULT $notNull", $colConfig);
        $colConfig =  "$colConfig NOT NULL";

        # Change the column config
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop not null on a column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_NOT_NULL()
    {

        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # Only change the config if NOT NULL exists
        if (strpos($colConfig, 'NOT NULL') !== FALSE) {
            
            # Remove the NOT NULL
            $colConfig = str_replace('NOT NULL', '', $colConfig);
            
            # Change the column config
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");

        }

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add unsinged to an integer or number based column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_UNSIGNED()
    {
        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # If UNSIGNED is already set return
        if (strpos($colConfig, 'UNSIGNED') !== FALSE) return $this;

        # Add UNSIGNED as the second value
        $pos       = strpos($colConfig, ' ');
        $colConfig = substr_replace($colConfig, ' UNSIGNED ', $pos, 0);

        # Change the column config
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");
        
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop unsinged on an integer or number based column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_UNSIGNED()
    {

        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # Only change if UNSIGNED is already set 
        if (strpos(strtoupper($colConfig), 'UNSIGNED') !== FALSE) {

            # Remove the UNSIGNED
            $colConfig = str_replace('UNSIGNED', '', $colConfig);
            $colConfig = str_replace('unsigned', '', $colConfig);

            # Change the column config
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");
        }

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add auto increment to an integer primary key column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function SET_AUTO_INCREMENT()
    {

        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # Only change if AUTO_INCREMENT is not set 
        if (strpos(strtoupper($colConfig), 'AUTO_INCREMENT') !== FALSE) return $this;

        # If the column is already the primary key
        if (strpos(strtoupper($colConfig), 'PRIMARY KEY') !== FALSE) {
            
            # Change the column config
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` INT NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY");

        }
        else {

            # Drop the primary key
            if ($this->getPrimaryKey()) $this->DROP_PRIMARY_KEY();
           
            # Change the column config
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` INT NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY");

        }
      
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;

    }

    /**
     * DROP auto increment on an integer primary key column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_AUTO_INCREMENT()
    {

        # Get the existing config
        $colConfig = $this->getColumnConfig();

        # Only change if AUTO_INCREMENT is set 
        if (strpos(strtoupper($colConfig), 'AUTO_INCREMENT') !== FALSE) {
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` INT NOT NULL UNIQUE");
        }      
      
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
       
    }

    /**
     * Set default value for column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function SET_DEFAULT($value = 'NULL')
    {
        # Set the default value
        $this->Database->query("ALTER TABLE `$this->tableName` ALTER `$this->column` SET DEFAULT $value");

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop default value for column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_DEFAULT()
    {
        # Remove default value
        $colConfig = str_replace('DEFAULT NULL', '', $this->setColumnConfig('DEFAULT', 'NULL'));

        # Save the column params
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");

        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add unique contraint on column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_UNIQUE()
    {

        # Only change if UNIQUE is not set 
        if (strpos(strtoupper($this->getColumnConfig()), 'UNIQUE') !== FALSE) return $this;

        # Change the column config
        $colConfig = str_replace('DEFAULT NULL', '', $this->setColumnConfig('Key', 'UNI'));

        # Save the column params
        $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");
        
        # Reload the column config
        $this->loadColumns();

        # Return Alter for chaining
        return $this;
    }

    /**
     * Drop unique contraint on column
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_UNIQUE()
    {

        # Only change if UNIQUE is not set 
        if (strpos(strtoupper($this->getColumnConfig()), 'UNIQUE') !== FALSE) {

            # Change the column config
            $colConfig = str_replace('DEFAULT NULL', '', $this->setColumnConfig('Key', ''));

            # Save the column params
            $this->Database->query("ALTER TABLE `$this->tableName` MODIFY COLUMN `$this->column` $colConfig");
            
            # Reload the column config
            $this->loadColumns();

        }

        # Return Alter for chaining
        return $this;
    }

    /**
     * Add a foreign key constraint to a column
     *
     * @param string    $referenceTable
     * @param string    $referenceKey
     * @param string    $constraint (optional)
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function ADD_FOREIGN_KEY($referenceTable, $referenceKey, $constraint = null) 
    {

        # Create a constraint if it was not provided
        $kCol   = substr($this->column, 0, 3);
        $ktble  = substr($referenceTable, 0, 3);
        $ktble2 = substr($this->tableName, 0, 3);
        $kkey   = substr($referenceKey, 0, 3);
        $constraint   = $constraint ? $constraint : str_replace(" ", "_", "fk $kCol toTable $ktble fromT $ktble2 onCol $kkey");

        $SQL = "ALTER TABLE `$this->tableName` ADD CONSTRAINT `$constraint` FOREIGN KEY (`$this->column`) REFERENCES $referenceTable(`$referenceKey`)";

        # Save the FK
        $this->Database->query("ALTER TABLE `$this->tableName` ADD CONSTRAINT `$constraint` FOREIGN KEY (`$this->column`) REFERENCES $referenceTable(`$referenceKey`)");

        # Reload the column config
        $this->loadColumns();
            
        return $this;

    }

    /**
     * Drop a foreign key constraint on a column
     *
     * @param string    $referenceTable
     * @param string    $referenceKey
     * @param string    $constraint (optional)
     *
     * @return \Kanso\Database\Query\Alter
     */
    public function DROP_FOREIGN_KEY($referenceTable, $referenceKey, $constraint = null)
    {

        # Create a constraint if it was not provided
        $kCol   = substr($this->column, 0, 3);
        $ktble  = substr($referenceTable, 0, 3);
        $ktble2 = substr($this->tableName, 0, 3);
        $kkey   = substr($referenceKey, 0, 3);
        $constraint   = $constraint ? $constraint : str_replace(" ", "_", "fk $kCol toTable $ktble fromT $ktble2 onCol $kkey");

        # Remove the FK
        $this->Database->query("ALTER TABLE `$this->tableName` DROP FOREIGN KEY `$constraint`");

        # Reload the column config
        $this->loadColumns();
            
        return $this;

        return false;
    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Load the current table's columns and column paramaters
     *
     */
    private function loadColumns()
    {
        $columns = [];
        $cols    = $this->Database->query("SHOW COLUMNS FROM `$this->tableName`");
        foreach ($cols as $col) {
            $columns[$col['Field']] = $col;
        }
        $this->columns = $columns;
    }

    /**
     * Validate a column exists
     *
     * @param string    $column
     */
    private function columnExists($column)
    {
        return isset($this->columns[$column]);
    }

    /**
     * Get the table's PRIMARY KEY column name
     *
     * @return string|false
     */
    private function getPrimaryKey()
    {
        $key = $this->Database->query("SHOW KEYS FROM `$this->tableName` WHERE Key_name = 'PRIMARY'");
        if (isset($key[0]['Column_name'])) return $key[0]['Column_name'];
        return false;
    }

    /**
     * Get a column or the current column configuration
     *
     * @param  string    $column (optional)
     * @return string
     */
    private function getColumnConfig($column = null)
    {
        $strConfig = '';
        $config    = !$column ? $this->columns[$this->column] : $this->columns[$column];
        $notNull   = $config['Null'] === 'NO' ? 'NOT NULL' : '';
        $Default   = $config['Default'] === NULL ? 'NULL' : $config['Default'];
        //$key       = $config['Key'] === 'PRI' ? 'PRIMARY KEY' : '';
        $key       = $config['Key'] === 'UNI' ? 'UNIQUE' : '';
        $extra     = strtoupper($config['Extra']);
        $strConfig = $config['Type']." DEFAULT $Default $notNull $key $extra";
        return trim($strConfig);
    }

    /**
     * Set a key/value pair on a column or the current column configuration
     *
     * @param  string    $key
     * @param  string    $value
     * @param  string    $column (optional)
     * @return string
     */
    private function setColumnConfig($key, $value, $column = null)
    {
        $key       = ucfirst(strtolower($key));
        $strConfig = '';
        $config    = !$column ? $this->columns[$this->column] : $this->columns[$column];
        $config[$key] = $value; 
        $notNull   = $config['Null'] === 'NO' ? 'NOT NULL' : '';
        $Default   = $config['Default'] === NULL ? 'NULL' : $config['Default'];
        //$key       = $config['Key'] === 'PRI' ? 'PRIMARY KEY' : '';
        $key       = $config['Key'] === 'UNI' ? 'UNIQUE' : '';
        $extra     = strtoupper($config['Extra']);
        $strConfig = $config['Type']." DEFAULT $Default $notNull $key $extra";
        return trim($strConfig);
    }

    /**
     * Filter a column or table name to valid SQL
     *
     * @param   string    $str 
     * @return  string
     */
    private function indexFilter($str)
    {
        return strtolower(str_replace(' ', '_', $str));
    }
    
}