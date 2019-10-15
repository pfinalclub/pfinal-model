<?php
	/**
	 * ----------------------------------------
	 * | Created By pfinal-model                 |
	 * | User: pfinal <lampxiezi@163.com>     |
	 * | Date: 2019/10/14                      |
	 * | Time: 下午5:59                        |
	 * ----------------------------------------
	 * |    _____  ______ _             _     |
	 * |   |  __ \|  ____(_)           | |    |
	 * |   | |__) | |__   _ _ __   __ _| |    |
	 * |   |  ___/|  __| | | '_ \ / _` | |    |
	 * |   | |    | |    | | | | | (_| | |    |
	 * |   |_|    |_|    |_|_| |_|\__,_|_|    |
	 * ----------------------------------------
	 */
	
	namespace pf\model\build;
	
	
	trait Relation
	{
		/**
		 *
		 * @param $class
		 * @param string $foreignKey
		 * @param string $localKey
		 * @return mixed
		 */
		public function hasOne($class, $foreignKey = '', $localKey = '')
		{
			static $cache = [];
			$foreignKey = $foreignKey ?: $this->getTable().'_'.$this->getPk();
			$localKey = $localKey ?: $this->getPk();
			$name = md5($class.$foreignKey.$localKey.$this[$localKey]);
			if (!isset($cache[$name])) {
				$cache[$name] = (new $class())->where($foreignKey, $this[$localKey])->first();
			}
			
			return $cache[$name];
		}
		
		/**
		 * 一对多
		 *
		 * @param        $class      关联模型
		 * @param string $foreignKey 关联表关联字段
		 * @param string $localKey 本模型字段
		 *
		 * @return mixed
		 */
		protected function hasMany($class, $foreignKey = '', $localKey = '')
		{
			static $cache = [];
			$foreignKey = $foreignKey ?: $this->getTable().'_'.$this->getPk();
			$localKey = $localKey ?: $this->getPk();
			$name = md5($class.$foreignKey.$localKey.$this[$localKey]);
			if (!isset($cache[$name])) {
				$cache[$name] = (new $class())->where($foreignKey, $this[$localKey])->get();
			}
			
			return $cache[$name];
		}
		
		/**
		 * 相对的关联
		 *
		 * @param $class
		 * @param $parentKey
		 * @param $localKey
		 *
		 * @return mixed
		 */
		protected function belongsTo($class, $localKey = '', $parentKey = '')
		{
			static $cache = [];
			//父表
			$instance = new $class();
			$parentKey = $parentKey ?: $instance->getPk();
			$localKey = $localKey ?: $instance->getTable().'_'.$instance->getPk();
			$name = md5($class.$localKey.$parentKey.$this[$localKey]);
			if (!isset($cache[$name])) {
				$cache[$name] = $instance->where($parentKey, $this[$localKey])->first();
			}
			
			return $cache[$name];
		}
		
		/**
		 * 多对多关联
		 *
		 * @param string $class 关联中间模型
		 * @param string $middleTable 中间表
		 * @param string $localKey 主表字段
		 * @param string $foreignKey 关联表字段
		 *
		 * @return mixed
		 */
		protected function belongsToMany($class, $middleTable = '', $localKey = '', $foreignKey = '')
		{
			static $cache = [];
			$instance = new $class;
			$middleTable = $middleTable ?: $this->getTable().'_'.$instance->getTable();
			$localKey = $localKey ?: $this->table.'_'.$this->pk;
			$foreignKey = $foreignKey ?: $instance->getTable().'_'.$instance->getPrimaryKey();
			$name = md5($class.$middleTable.$localKey.$foreignKey.$this[$this->pk]);
			if (!isset($cache[$name])) {
				$middle = DB::table($middleTable)->where($localKey, $this[$this->pk])
					->lists($foreignKey);
				$cache[$name] = $instance->whereIn($instance->getPk(), array_values($middle))->get();
			}
			
			return $cache[$name];
		}
	}