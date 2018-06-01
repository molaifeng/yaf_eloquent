<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{

    protected $table;

    /**
     * 写入
     * @param array $attributes
     * @param string $table 默认为 model 的 table 变量，如果没有 model 文件则需要显示传入
     * @param bool $flag 默认不返回ID，注意当表主键不为自增主键是一定为false
     * @return int
     */
    public function insert(array $attributes, $flag = false, $table = '')
    {
        $table = $table ? $table : $this->table;
        return $flag ? DB::table($table)->insertGetId($attributes) : DB::table($table)->insert($attributes);
    }

    /**
     * 删除
     * @param $id
     * @return int
     */
    public function del($id)
    {
        return DB::table($this->table)->where('id', $id)->delete();
    }

    /**
     * 更新
     * @param $id
     * @param array $attributes
     * @param string $table  默认为 model 的 table 变量，如果没有 model 文件则需要显示传入
     * @return int
     */
    public function up($id, array $attributes, $table = '')
    {
        return DB::table($table ? $table : $this->table)->where('id', $id)->update($attributes);
    }

    /**
     * 获取多条数据
     * @param array $where
     * @param string $select
     * @param string $table  默认为 model 的 table 变量，如果没有 model 文件则需要显示传入
     * @return array|static[]
     */
    public function gets(array $where, $select = '*', $table = '')
    {
        return DB::table($table ? $table : $this->table)->select($select)->where($where)->get();
    }

    /**
     * 依据 ID 获取某条具体信息
     * @param $id
     * @param string $select
     * @return mixed|static
     */
    public function get($id, $select = '*')
    {
        return DB::table($this->table)->select($select)->where('id', $id)->first();
    }

    /**
     * 校验是否唯一
     * @param $attributes
     * @param bool $id 有此字段为编辑查询，否则为添加
     * @return bool
     */
    public function unique(array $attributes, $id = false)
    {
        $query = DB::table($this->table)->where($attributes)->where('deleted', 0);
        if ($id)
            $query->where('id', '<>', $id);

        return $query->exists();
    }

    /**
     * 查询 in 的情况
     * @param $in
     * @param array $inArray
     * @param string $select
     * @param array $where
     * @param string $table
     * @return array|static[]
     */
    public function whereIn($in, array $inArray, $select = '*', array $where = [], $table = '')
    {
        $query = DB::table($table ? $table : $this->table)->select($select);
        if ($where)
            $query->where($where);
        return $query->whereIn($in, $inArray)->get();
    }

}