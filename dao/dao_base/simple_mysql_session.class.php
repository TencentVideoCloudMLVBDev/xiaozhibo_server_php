<?php
include_once dirname(__FILE__) . '../../../common/GlobalDefine.php';
include_once dirname(__FILE__) . '../../../common/GlobalFunctions.php';

class SimpleMysqlSession
{
    /**
     * @desc 构造函数，连接失败时，抛出异常
     * @param string $db_host，数据库的ip
     * @param int $db_port，端口
     * @param string $db_user，用户名
     * @param string $db_password，密码
     * @param string $db_name，数据库名
     * @throws Exception
     */
    public function __construct($db_host,
        $db_port,
        $db_user,
        $db_password,
        $db_name) {
        $this->connection_ = mysqli_connect($db_host, $db_user,
            $db_password, $db_name, $db_port);
        if (!$this->connection_) {
            mysql_log(ERROR, EC_OK, 'mysqli_connect failed, error:' . mysqli_connect_error());
            throw new Exception(
                'mysqli_connect failed, error:' . mysqli_connect_error());
        }
        mysqli_real_query($this->connection_, "SET NAMES 'utf8mb4'");
    }

    /**
     * @desc mysql转义字符串
     * @param string $input
     * @return string
     */
    public function EscapeString($input)
    {
        return mysqli_real_escape_string($this->connection_, $input);
    }

    /**
     * @desc 获取mysql连接的错误码
     * @return int
     */
    public function GetMysqlErrno()
    {
        return mysqli_errno($this->connection_);
    }

    /**
     * @desc 执行select sql，返回结果集，失败时抛出异常
     * @param string $sql
     * @throws Exception
     * @return array， 列表，rows
     */
    public function ExecuteSelectSql($sql)
    {
        mysql_log(INFO, EC_OK, "" . $sql);
        if (!mysqli_real_query($this->connection_, $sql)) {
            throw new Exception(
                'mysqli_real_query failed, sql:' . $sql . ', error:'
                . mysqli_error($this->connection_));
        }
        $result = mysqli_store_result($this->connection_);
        if (mysqli_errno($this->connection_) != 0) {
            throw new Exception(
                'mysqli_store_result failed, sql:' . $sql . ', error:'
                . mysqli_error($this->connection_));
        }
        $result_set = array();
        while ($object = mysqli_fetch_assoc($result)) {
            array_push($result_set, $object);
        }

        return $result_set;
    }

    /**
     * @desc 执行insert, update, delete，返回影响记录的数目，失败时抛出异常
     * @param string $sql
     * @return int affect rows
     * @throws Exception
     */
    public function ExecuteUpdateSql($sql)
    {
        // echo $sql."\n";
        mysql_log(INFO, EC_OK, "" . $sql);
        if (!mysqli_real_query($this->connection_, $sql)) {
            throw new Exception(
                'mysqli_real_query failed, sql:' . $sql . ', error:'
                . mysqli_error($this->connection_));
        }

        return mysqli_affected_rows($this->connection_);
    }

    public function Begin()
    {$this->ExecuteUpdateSql('begin');}
    public function Commit()
    {$this->ExecuteUpdateSql('commit');}
    public function Rollback()
    {$this->ExecuteUpdateSql('rollback');}

    /**
     * @desc 添加对象
     * @param string $table, 数据库表名
     * @param array $object, 查询到的对象
     * @return int 如果表设置了自增列，返回新生成的id
     */
    public function AddObject($table, $object)
    {
        $sql         = 'INSERT INTO ' . $table;
        $key_array   = array();
        $value_array = array();
        foreach ($object as $key => $value) {
            array_push($key_array, $key);
            array_push($value_array, is_string($value) ? "'$value'" : $value);
        }
        $sql .= ' (`' . join('`,`', $key_array) . '`)';
        $sql .= ' VALUES(' . join(',', $value_array) . ')';
        $this->ExecuteUpdateSql($sql);
        $result_set = $this->ExecuteSelectSql(
            'SELECT LAST_INSERT_ID() AS id');
        $id = $result_set[0]['id'];
        return $id;
    }

    /**
     * @desc 添加对象，如果存在则替换，谨慎使用！
     * @param string $table, 数据库表名
     * @param array $object, 查询到的对象
     * @return int, 如果表设置了自增列，返回新生成的id
     */
    public function ReplaceObject($table, $object)
    {
        $sql         = 'REPLACE INTO ' . $table;
        $key_array   = array();
        $value_array = array();
        foreach ($object as $key => $value) {
            array_push($key_array, $key);
            array_push($value_array, is_string($value) ? "'$value'" : $value);
        }
        $sql .= ' (`' . join('`,`', $key_array) . '`)';
        $sql .= ' VALUES(' . join(',', $value_array) . ')';
        $this->ExecuteUpdateSql($sql);
        $result_set = $this->ExecuteSelectSql(
            'SELECT LAST_INSERT_ID() AS id');
        $id = $result_set[0]['id'];
        return $id;
    }

    /**
     * @desc 根据主键查询对象，仅返回一个对象，失败时抛出异常
     * @param string $table，数据库表名
     * @param array $primary_key，主键
     * @throws Exception
     * @return array, 查询到的对象，单条记录
     */
    public function GetObject($table, $primary_key)
    {
        $sql             = 'SELECT * FROM ' . $table;
        $condition_array = $this->MakeConditionArray($primary_key);
        $conditions      = join(' AND ', $condition_array);
        $sql .= ' WHERE ' . $conditions;
        $result_set   = $this->ExecuteSelectSql($sql);
        $result_count = count($result_set);
        if ($result_count == 1) {
            return $result_set[0];
        } else if ($result_count > 1) {
            throw new Exception($table . ': return ' . $result_count
                . ' records, primary_key:' . $conditions);
        }
    }

    /**
     * @desc 删除对象，使用主键索引，失败时抛出异常
     * @param string $table 数据库表名
     * @param array $primary_key 主键
     * @throws Exception
     * @return number
     */
    public function DeleteObject($table, $primary_key)
    {
        $sql             = 'DELETE FROM ' . $table . ' WHERE ';
        $condition_array = $this->MakeConditionArray($primary_key);
        $conditions      = join(' AND ', $condition_array);
        $sql .= $conditions;
        $delete_count = $this->ExecuteUpdateSql($sql);

        return $delete_count;
    }

    /**
     * @desc 更新对象，使用主键索引，失败时抛出异常
     * @param string $table，数据库表名
     * @param array $primary_key，主键
     * @param array $object，需要更新的字段（不能包含主键）
     * @throws Exception
     * @return void|number
     */
    public function UpdateObject($table, $primary_key, $object)
    {
        if (empty($object)) {
            return;
        }

        $sql             = 'UPDATE ' . $table . ' SET ';
        $value_array     = array();
        $condition_array = $this->MakeConditionArray($primary_key);
        $conditions      = join(' AND ', $condition_array);
        foreach ($object as $key => $value) {
            array_push($value_array, is_string($value) ?
                "`$key`='$value'" : "`$key`=$value");
        }
        $sql .= join(',', $value_array) . ' WHERE ';
        $sql .= join(' AND ', $condition_array);
        $update_count = $this->ExecuteUpdateSql($sql);

        return $update_count;
    }

    /**
     * @desc 查询对象，使用条件查询，失败时抛出异常
     * @param string $table 数据库表名
     * @param array $condition 查询条件
     * @param int $start_row 开始的记录行
     * @param int $row_number 返回的最大记录条数
     * @return Ambigous <array，, multitype:>
     */
    public function QueryObjects($table,
        $condition,
        $start_row,
        $row_number) {
        $sql = 'SELECT * FROM ' . $table;
        if (!empty($condition)) {
            $condition_array = $this->MakeConditionArray($condition);
            $sql .= ' WHERE ' . join(' AND ', $condition_array);
        }
        $sql .= " LIMIT $start_row, $row_number";
        $objects = $this->ExecuteSelectSql($sql);

        return $objects;
    }

    /**
     * @desc 查询符合条件的对象个数，返回对象个数
     * @param string $table 数据库表名
     * @param array $condition 查询条件
     * @return int
     */
    public function QueryObjectCount($table, $condition)
    {
        $sql = 'SELECT COUNT(*) AS count FROM ' . $table;
        if (!empty($condition)) {
            $condition_array = $this->MakeConditionArray($condition);
            $sql .= ' WHERE ' . join(' AND ', $condition_array);
        }
        $ret = $this->ExecuteSelectSql($sql);

        return $ret[0]['count'];
    }

    /**
     * @desc 生成where条件组合
     * @param array $condition
     * @return array
     */
    private function MakeConditionArray($condition)
    {
        $condition_array = array();
        foreach ($condition as $key => $value) {
            array_push($condition_array, is_string($value) ?
                "`$key`='$value'" : "`$key`=$value");
        }

        return $condition_array;
    }
}
