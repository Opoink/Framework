<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database\Migration;

class Columns {

    const TYPES = [
        'INT' => 'INT',
        'VARCHAR' => 'VARCHAR',
        'TEXT' => 'TEXT',
        'DATE' => 'DATE',
        'TINYINT' => 'TINYINT',
        'SMALLINT' => 'SMALLINT',
        'MEDIUMINT' => 'MEDIUMINT',
        'BIGINT' => 'BIGINT',
        'DECIMAL' => 'DECIMAL',
        'FLOAT' => 'FLOAT',
        'DOUBLE' => 'DOUBLE',
        'REAL' => 'REAL',
        'BIT' => 'BIT',
        'BOOLEAN' => 'BOOLEAN',
        'SERIAL' => 'SERIAL',
        'DATETIME' => 'DATETIME',
        'TIMESTAMP' => 'TIMESTAMP',
        'TIME' => 'TIME',
        'YEAR' => 'YEAR',
        'CHAR' => 'CHAR',
        'TINYTEXT' => 'TINYTEXT',
        'TEXT' => 'TEXT',
        'MEDIUMTEXT' => 'MEDIUMTEXT',
        'LONGTEXT' => 'LONGTEXT',
        'BINARY' => 'BINARY',
        'VARBINARY' => 'VARBINARY',
        'TINYBLOB' => 'TINYBLOB',
        'BLOB' => 'BLOB',
        'MEDIUMBLOB' => 'MEDIUMBLOB',
        'LONGBLOB' => 'LONGBLOB',
        'ENUM' => 'ENUM',
        'SET' => 'SET',
        'GEOMETRY' => 'GEOMETRY',
        'POINT' => 'POINT',
        'LINESTRING' => 'LINESTRING',
        'POLYGON' => 'POLYGON',
        'MULTIPOINT' => 'MULTIPOINT',
        'MULTILINESTRING' => 'MULTILINESTRING',
        'MULTIPOLYGON' => 'MULTIPOLYGON',
        'GEOMETRYCOLLECTION' => 'GEOMETRYCOLLECTION',
        'JSON' => 'JSON' 
    ];

    const ATTR = [
        "BINARY" => 'BINARY',
        "UNSIGNED" => 'UNSIGNED',
        "UNSIGNED ZEROFILL" => 'UNSIGNED ZEROFILL',
        "on update CURRENT_TIMESTAMP" => 'on update CURRENT_TIMESTAMP'
    ];

    protected $columns = [];

    protected $name;
    protected $type;
    protected $length;
    protected $nullable;
    protected $comment;
    protected $default;
    protected $attributes;
    

    /**
	 * set length of the column
	 */
	public function setLength($column){
        $this->length = null;
        if(isset($column['length'])){
            $this->length = $column['length'];
        }
	}

    /**
	 * set is column is nullable or not
	 */
	public function setNullable($column, $_file){
        $this->nullable = null;
        if (array_key_exists('nullable', $column)) {
            if(gettype($column['nullable']) == 'boolean'){
                if($this->default == 'NULL'){ /** always make the column as nullable if the default is NULL */
                    $this->nullable = true;
                } else {
                    $this->nullable = $column['nullable'];
                }
            } else {
                throw new \Exception("Field nullable expects boolean, " . $t . ' is given in '.json_encode($column).' --- ' . $_file, 1);
            }
        }
	}

    /**
	 * set the default value of the column
	 */
	public function setDefault($column){
        $this->default = null;
        if (array_key_exists('default', $column)) {
            if($column['default'] == 'NONE' || $column['default'] == 'none'){
                $this->default = null;
            } 
            elseif($column['default'] == 'CURRENT_TIMESTAMP' || $column['default'] == 'current_timestamp'){
                $this->default = "CURRENT_TIMESTAMP";
            }
            elseif($column['default'] == null || $column['default'] == NULL){
                $this->default = "NULL";
            }
            else {
                $this->default = "'" . $column['default'] . "'";
            }

        }
	}

    /**
	 * set the comment for coulmun
	 */
	public function setComment($column){
        $this->comment = null;
        if(isset($column['comment'])){
            $this->comment = $column['comment'];
        }
	}

    /**
     * set on attributes
     */
    public function setAttributes($column){
		$this->attributes = null;
        if (array_key_exists('attributes', $column)) {
            $attr = $column['attributes'];
            
            $this->attributes = $attr;
            if(in_array($attr, self::ATTR)){
                switch ($attr) {
                    case self::ATTR['BINARY']:
                        $this->attributes = 'BINARY';
                        break;
                    case self::ATTR['UNSIGNED']:
                        $this->attributes = 'UNSIGNED';
                        break;
                    case self::ATTR['UNSIGNED ZEROFILL']:
                        $this->attributes = 'UNSIGNED ZEROFILL';
                        break;
                    case self::ATTR['on update CURRENT_TIMESTAMP']:
                        $this->attributes = "ON UPDATE CURRENT_TIMESTAMP";
                        break;
                }
            }
        }
	}

    /**
     * new column for database table
     * this will generate an SQL string code to add a column
     */
    public function addColumn($column, $_file, $prevColumn=null) {
        if(!isset($column['name'])){
            throw new \Exception("Field name is required: " . json_encode($column) . ' --- ' . $_file, 1);
		}
        elseif(!isset($column['type'])){
            throw new \Exception("Field type is required: " . json_encode($column) . ' --- ' . $_file, 1);
		}

        else {
            $this->name = $column['name'];
            $this->type = $column['type'];
            $this->setLength($column);
            $this->setDefault($column, $_file);
            $this->setNullable($column, $_file);
            $this->setAttributes($column, $_file);
            $this->setComment($column, $_file);
            
			$collate = '';
			if(array_key_exists('collation', $column)){
				$collate = " COLLATE " . $column['collation'] . " ";
			}

            $columnString = "`".$this->name."` " . $this->type;
            if($this->length){
				$columnString .= "(".$this->length.")";
			}

            if($this->attributes){
				$columnString .= " ".$this->attributes." ";
			}

			$columnString .= $collate;

            if($this->nullable){
				$columnString .= " NULL ";
			} else {
				$columnString .= " NOT NULL ";
			}

            if($this->default){
				$columnString .= " DEFAULT ".$this->default." ";
			}

            if($this->comment){
				$columnString .= " COMMENT '".$this->comment."' ";
			}

            if($prevColumn){ /** this part is used in upgrade module */
                $columnString .= ' AFTER `'.$prevColumn['name'].'` ';
            }
			else if(isset($column['after'])){
				$columnString .= ' AFTER `'.$column['after'].'` ';
			}
            $this->columns[] = $columnString;
        }
    }

    public function getColumns(){
        return $this->columns;
    }

    /**
	 * after adding column we have to reset our variable
	 * this function will do it for us
	 */	
	protected function resetColumn(){
		$this->name = null;
		$this->type = null;
		$this->length = null;
		$this->nullable = null;
		$this->comment = null;
		$this->default = null;
		$this->attributes = null;
	}
}
?>
