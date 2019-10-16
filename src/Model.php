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
	use Carbon\Carbon;
	use Iterator;
	use pf\collection\Collection;
	use pf\db\DB;
	use pf\db\Query;
	use pf\model\build\ArrayIterator;
	use pf\model\build\Auto;
	use pf\model\build\Filter;
	use pf\model\build\Relation;
	use pf\model\build\Validate;
	
	class Model implements ArrayAccess, Iterator
	{
		use ArrayIterator, Relation, Auto, Filter,Validate;
		
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
			if (!$this->table) {
				$this->init();
			}
		}
		
		protected function init()
		{
			$this->setTable($this->table);
			$this->setDB(DB::table($this->table));
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
		 * @param $db
		 */
		public function setDB($db)
		{
			$this->db = $db;
		}
		
		public function setPk($pk)
		{
			$this->pk = $pk;
		}
		
		public function getPk($pk)
		{
			return $this->pk;
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
			$obj = new static();
			
			return call_user_func_array([$obj, $method], $params);
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
		
		public function setData(array $data)
		{
			
			$this->data = array_merge($this->data, $data);
			$this->fields = $this->data;
			$this->getFormatAttribute();
			
			return $this;
		}
		
		public function getData()
		{
			return $this->data;
		}
		
		public function getFormatAttribute()
		{
			foreach ($this->fields as $name => $val) {
				$n = preg_replace_callback(
					'/_([a-z]+)/',
					function ($v) {
						return strtoupper($v[1]);
					},
					$name
				);
				$method = "get".ucfirst($n)."AtAttribute";
				if (method_exists($this, $method)) {
					$this->fields[$name] = $this->$method($val);
				}
			}
			
			return $this->fields;
		}
		
		public function toArray()
		{
			$data = $this->fields;
			foreach ($data as $k => $v) {
				if (is_object($v) && method_exists($v, 'toArray')) {
					$data[$k] = $v->toArray();
				}
			}
			
			return $data;
		}
		
		final private function fieldFillCheck(array $data)
		{
			if (empty($this->allowFill) && empty($this->denyFill)) {
				return;
			}
			//允许填充的数据
			if (!empty($this->allowFill) && $this->allowFill[0] != '*') {
				# $data = Arr::filterKeys($data, $this->allowFill, 0);
			}
			//禁止填充的数据
			if (!empty($this->denyFill)) {
				if ($this->denyFill[0] == '*') {
					$data = [];
				} else {
					//TODO
					# $data = Arr::filterKeys($data, $this->denyFill, 1);
				}
			}
			$this->original = array_merge($this->original, $data);
		}
		
		/**
		 * 批量设置做准备数据
		 *
		 * @return $this
		 */
		private function formatFields()
		{
			//更新时设置
			if ($this->action() == self::MODEL_UPDATE) {
				$this->original[$this->pk] = $this->data[$this->pk];
			}
			//字段时间
			if ($this->timestamps === true) {
				$this->original['updated_at'] = Carbon::now(new \DateTimeZone('PRC'));
				//更新时间设置
				if ($this->action() == self::MODEL_INSERT) {
					$this->original['created_at'] = Carbon::now(new \DateTimeZone('PRC'));
				}
			}
			
			return $this;
		}
		
		public function action()
		{
			return empty($this->data[$this->pk]) ? self::MODEL_INSERT : self::MODEL_UPDATE;
		}
		
		public function touch()
		{
			if ($this->action() == self::MODEL_UPDATE && $this->timestamps) {
				$data = ['updated_at' => Carbon::now('PRC')];
				
				return $this->db->where($this->pk, $this->data[$this->pk])->update($data);
			}
			
			return false;
		}
		
		public function getTable()
		{
			return $this->table;
		}
		
		public function save(array $data = [])
		{
			//自动填充数据处理
			$this->fieldFillCheck($data);
			//自动过滤
			$this->autoFilter();
			//自动完成
			$this->autoOperation();
			//处理时期字段
			$this->formatFields();
			if ($this->action() == self::MODEL_UPDATE) {
				$this->original = array_merge($this->data, $this->original);
			}
			//自动验证
			if (!$this->autoValidate()) {
				return false;
			}
			//更新条件检测
			$res = null;
			switch ($this->action()) {
				case self::MODEL_UPDATE:
					if ($res = $this->db->update($this->original)) {
						$this->setData($this->db->find($this->data[$this->pk]));
					}
					break;
				case self::MODEL_INSERT:
					if ($res = $this->db->insertGetId($this->original)) {
						if (is_numeric($res) && $this->pk) {
							$this->setData($this->db->find($res));
						}
					}
					break;
			}
			$this->original = [];
			
			return $res ? $this : false;
		}
		
		
		static public function findOrCreate($id = 0)
		{
			$model = !empty($id) && is_numeric($id) ? static::find($id) : new static();
			if (empty($model)) {
				return new static();
			}
			
			return $model;
		}
		
		public function destory()
		{
			//没有查询参数如果模型数据中存在主键值,以主键值做删除条件
			if (!empty($this->data[$this->pk])) {
				if ($this->db->delete($this->data[$this->pk])) {
					$this->setData([]);
					
					return true;
				}
			}
			
			return false;
		}
		
		public function __set($name, $value)
		{
			$this->original[$name] = $value;
			$this->data[$name] = $value;
		}
		
		public function __get($name)
		{
			if (isset($this->fields[$name])) {
				return $this->fields[$name];
			}
			//关键方法获取
			if (method_exists($this, $name)) {
				return $this->$name();
			}
		}
		
	}