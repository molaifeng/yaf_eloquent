<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

class EloquentModel extends Model
{

    protected $table;

    public function insert(array $attributes)
    {
        return DB::table($this->table)->insertGetId($attributes);
    }

    public function delete($id)
    {
        return DB::table($this->table)->where('id', $id)->delete();
    }
    
    public function update($id, array $attributes)
    {
        return DB::table($this->table)->where('id', $id)->update($attributes);
    }
    
    public function gets($id, array $attributes)
    {
        return DB::table($this->table)->select($attributes)->where('id', $id)->get();
    }

    public function get($id, array $attributes)
    {
        return DB::table($this->table)->select($attributes)->where('id', $id)->first();
    }
    
}