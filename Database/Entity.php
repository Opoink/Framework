<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Database;

class Entity {

    const COLUMNS = [];
	
	protected $tablename;
    protected $primaryKey;
    protected $_request;

    protected $data = [];

    /**
     * instance of \Of\Database\Connection
     */
    protected $_connection;

    public $_di;
    
    public function __construct(
        \Of\Database\Connection $Connection,
        \Of\Http\Request $Request
    ){
        $this->_connection = $Connection;
        $this->_di = new \Of\Std\Di();
        $this->_request = $Request;
    }

    /**
     * set the primarykey of the table
     * @param $primaryKey string
     */
    public function setPrimaryKey($primaryKey){
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * return the current connect
     */
    public function getConnection(){
        return $this->_connection->getConnection();
    }

    /**
     * return instance of \Of\Std\Pagination
     */
    protected function getPagination(){
        return $this->_di->make('\Of\Std\Pagination');
    }

    /**
     * return new instance of select
     */
    public function getSelect(){
        $di = new \Of\Std\Di();
        return $di->get('\Of\Database\Sql\Select');
    }

    /**
     * return new instance of DeleteStatement
     */
    public function getDelete(){
        return $this->_di->make('\Of\Database\Sql\DeleteStatement');
    }

    /**
     * fetch all data
     * @param $select instance of \Of\Database\Sql\Select
     * @param $isReturnInstance if we are going to return an instance or just data from db
     */
    public function fetchAll(\Of\Database\Sql\Select $select, $isReturnInstance=true){
        $data = $this->getConnection()->fetchAll($select->getQuery(), $select->_whereStatement->unsecureValue);
        if($isReturnInstance){
            return $this->setCollection($data);
        } else {
            return $data;
        }
    }

    /**
     * set the collected data and put that into an array
     * of current class instance
     */
    public function setCollection($data){
        $result = null;
        if(count($data) == 1){
            if(isset($data[0])){
                $result = $this->_di->make(get_class($this));
                $result->setData($data[0]);
            }
        } else {
            $result = [];
            foreach($data as $d){
                $newDataEntity = $this->_di->make(get_class($this));
                $newDataEntity->setData($d);
                $result[] = $newDataEntity;
            }
        }
        return $result;
    }

    /**
     * return the tototal count
     * @param $select instance of \Of\Database\Sql\Select
     * @param $col field name from database
     * @alias $col alias to be used for the result
     */
    public function count(\Of\Database\Sql\Select $select, $col = null, $alias=''){
        $_select = clone $select;
        $_select->count($col, $alias);
        $count = $this->fetchAll($_select);
        return $count->getData($alias);
    }

    /**
     * set the table name
     * @param $tablename string without prefix
     */
    public function setTablename($tablename){
        $this->tablename = $tablename;
        return $this;
    }

    /**
     * return table name
     * with prefix if set 
     */
    public function getTablename($tableName=null){
        if(!$tableName) {
            $tn = $this->tablename;
        } else {
            $tn = $tableName;
        }

        return $this->_connection->getTablename($tn);
    }

    /**
     * return value of data if available
     * @param $params array key value pair
     */
    public function getByColumn($params, $limit=1, $isReturnInstance=true, $by=null, $criterion='ASC'){
        $mainTable = $this->getTablename();
        $s = $this->getSelect()
        ->select()
        ->from($mainTable);

        if($limit){
            $s->limit($limit);
        }
        if($by){
            $s->orderBy($by, $criterion);
        }

        foreach ($params as $key => $value) {
            if($value == null){
                $s->where($key)->isnull(true);
            } else {
                $s->where($key)->eq($value);
            }
        }
        
        $data = $this->fetchAll($s, $isReturnInstance);
        return $data;
    }

    public function setData($key, $val=null){
        if(is_array($key)){
            foreach($key as $k => $v){
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $val;
        }
        return $this;
    }

    public function getData($key=null){
        if(!$key){
            return $this->data;
        } else {
            if(isset($this->data[$key])){
                return $this->data[$key];
            }
        }

        return null;
    }

    /**
     * remove an specific data by key
     * @param $key string the field to be removed
     * if not set iw will remove all set data
     */
    public function removeData($key=null){
        if(!$key){
            $this->data = [];
        } else {
            if(isset($this->data[$key])){
                unset($this->data[$key]);
            }
        }
    }

    /**
     * save new entry or update existing one
     * if the primary key is set 
     * will update the exisitng entry
     */
    public function save(){
        $columns = get_class($this)::COLUMNS;
        $data = $this->data;

        $d = [];
        foreach($data as $key => $val){
            if(in_array($key, $columns)){
                $d[$key] = $val;
            }
        }
        if(count($d) > 0){
            if(isset($d[$this->primaryKey])){
                $this->updateByColumn([$this->primaryKey => $d[$this->primaryKey]], $d);
                return $d[$this->primaryKey];
            } else {
                return $this->getConnection()->insert($this->getTablename(), $d);
            }
        }
    }

    /**
     * update table by its column
     * @param col array key value pair to set in where statement
     */
    public function updateByColumn($cols, $data){
        $rowCount = $this->getConnection()->update($this->getSelect(), $cols, $data, $this->getTablename());
        return $rowCount;
    }

    /**
     * delete an entry from database, based on current data primary key
     */
    public function _delete(){
        $data = $this->data;

        $rows = null;
        if(isset($data[$this->primaryKey])){
            $rows = $this->deleteByColumn([$this->primaryKey => $data[$this->primaryKey]]);
        }
        return $rows;
    }

    /**
     * delete entry from database with current Entity table name
     * @param $cols, array key value pair
     */
    public function deleteByColumn($cols){
        $delete = $this->getDelete();

        $mainTable = $this->getTablename();
        $delete->from($mainTable);

        foreach ($cols as $key => $col) {
            $delete->where($key)->eq($col);
        }
        $rows = $this->getConnection()->_delete($delete);

        return $rows;
    }

    /**
     * check if the returned data is an instance of the 
     * current class (entity)
     */
    protected function isInstance($val){
        $class =  get_class($this);
        return $val instanceof $class;
    }

    /**
     * return paginated data
     * @param $select instance of \Of\Database\Sql\Select
     */
    public function getFinalResponse(\Of\Database\Sql\Select $select){
        $count = (int)$this->count($select, $this->getTablename() . '.' . $this->primaryKey, 'count');

        $page = (int)$this->_request->getParam('page');
        if(!$page){
            $page = 1;
        }

        $limit = (int)$this->_request->getParam('limit');
        if(!$limit){
            $limit = 10;
        }

        $pagination = $this->getPagination();
        $pagination->set($page, $count, $limit);

        $select->offset($pagination->offset())
        ->limit($limit);

        $data = $this->fetchAll($select);

        $o = [
            'total_count' => $count,
            'total_page' => $pagination->total_pages(),
            'current_page' => $pagination->currentPage(),
            'per_page' => $select->_limit,
            'pages' => $pagination->pagesArray(),
            'data' => []
        ];

        if($this->isInstance($data)){
            $o['data'][] = $data->getData();
        } else {
            foreach ($data as $key => $value) {
                $o['data'][] = $value->getData();
            }
        }
        return $o;
    }
}