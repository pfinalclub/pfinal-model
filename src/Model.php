<?php
	/**
	 * ----------------------------------------
	 * | Created By pfinal-model                 |
	 * | User: pfinal <lampxiezi@163.com>     |
	 * | Date: 2019/10/14                      |
	 * | Time: 下午2:10                        |
	 * ----------------------------------------
	 * |    _____  ______ _             _     |
	 * |   |  __ \|  ____(_)           | |    |
	 * |   | |__) | |__   _ _ __   __ _| |    |
	 * |   |  ___/|  __| | | '_ \ / _` | |    |
	 * |   | |    | |    | | | | | (_| | |    |
	 * |   |_|    |_|    |_|_| |_|\__,_|_|    |
	 * ----------------------------------------
	 */
	
	namespace pf\model;
	
	use ArrayAccess;
	use Iterator;
	use pf\db\DB;
	use pf\db\Query;
	use pf\model\build\ArrayIterator;
	use pf\model\build\Relation;
	
	class Model implements ArrayAccess, Iterator
	{
		use ArrayIterator, Relation;
		
		# use Validate, Auto, Filter;
		
		//----------自动验证----------
		//有字段时验证
		const EXIST_VALIDATE = 1;
		//值不为空时验证
		const NOT_EMPTY_VALIDATE = 2;
		//必须验证
		const MUST_VALIDATE = 3;
		//值是空时验证
		const EMPTY_VALIDATE = 4;
		//不存在字段时处理
		const NOT_EXIST_VALIDATE = 5;
		//----------自动完成----------
		//有字段时验证
		const EXIST_AUTO = 1;
		//值不为空时验证
		const NOT_EMPTY_AUTO = 2;
		//必须验证
		const MUST_AUTO = 3;
		//值是空时验证
		const EMPTY_AUTO = 4;
		//不存在字段时处理
		const NOT_EXIST_AUTO = 5;
		//----------自动过滤----------
		//有字段时验证
		const EXIST_FILTER = 1;
		//值不为空时验证
		const NOT_EMPTY_FILTER = 2;
		//必须验证
		const MUST_FILTER = 3;
		//值是空时验证
		const EMPTY_FILTER = 4;
		//不存在字段时处理
		const NOT_EXIST_FILTER = 5;
		//--------处理时机/自动完成&自动验证共用
		//插入时处理
		const MODEL_INSERT = 1;
		//更新时处理
		const MODEL_UPDATE = 2;
		//全部情况下处理
		const MODEL_BOTH = 3;
		//允许填充字段
		protected $allowFill = [];
		//禁止填充字段
		protected $denyFill = [];
		//模型数据
		protected $data = [];
		//读取字段
		protected $fields = [];
		//构建数据
		protected $original = [];
		//数据库连接
		protected $connect;
		//表名
		protected $table;
		//表主键
		protected $pk;
		//字段映射
		protected $map = [];
		//时间操作
		protected $timestamps = false;
		//数据库驱动
		protected $db;
		
		public function __construct()
		{
			if ($this->table) {
				$this->init();
			}
		}
		
		protected function init()
		{
			$this->setTable($this->table);
			$this->setDb(DB::table($this->table));
			$this->db->setModel($this);
			$this->setPk($this->db->getPrimaryKey());
			
			return $this;
		}
		
		/**
		 * 设置表名
		 *
		 * @param $table
		 *
		 * @return $this
		 */
		protected function setTable($table)
		{
			//设置表名
			if (empty($table)) {
				$model = basename(str_replace('\\', '/', get_class($this)));
				$table = strtolower(
					trim(preg_replace('/([A-Z])/', '_\1\2', $model), '_')
				);
			}
			$this->table = $table;
			
			return $this;
		}
		
		/**
		 * @param $method
		 * @param $params
		 * @return mixed
		 */
		public function __call($method, $params)
		{
			$res = call_user_func_array([$this->db, $method], $params);
			
			return $this->returnParse($method, $res);
		}
		
		public static function __callStatic($method, $params)
		{
			return call_user_func_array([new static(), $method], $params);
		}
		
		public function returnParse($method, $result)
		{
			if (!empty($result)) {
				switch (strtolower($method)) {
					case 'find':
					case 'first':
						$instance = new static();
						
						return $instance->setData($result);
					case 'get':
					case 'paginate':
						$Collection = Collection::make([]);
						foreach ($result as $k => $v) {
							$instance = new static();
							$Collection[$k] = $instance->setData($v);
						}
						
						return $Collection;
					default:
						/**
						 * 返回值为查询构造器对象时
						 * 返回模型实例
						 */
						if ($result instanceof Query) {
							return $this;
						}
				}
			}
		}
		
	}