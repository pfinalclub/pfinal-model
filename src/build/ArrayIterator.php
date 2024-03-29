<?php
	/**
	 * ----------------------------------------
	 * | Created By pfinal-model                 |
	 * | User: pfinal <lampxiezi@163.com>     |
	 * | Date: 2019/10/14                      |
	 * | Time: 下午5:55                        |
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
	
	trait ArrayIterator
	{
		public function offsetSet($key, $value)
		{
			$this->original[$key] = $value;
			$this->data[$key] = $value;
			$this->fields[$key] = $value;
		}
		
		/**
		 * @param $key
		 *
		 * @return null
		 */
		public function offsetGet($key)
		{
			return isset($this->fields[$key]) ? $this->fields[$key] : null;
		}
		
		/**
		 * @param $key
		 *
		 * @return bool
		 */
		public function offsetExists($key)
		{
			return isset($this->data[$key]);
		}
		
		/**
		 * @param $key
		 */
		public function offsetUnset($key)
		{
			if (isset($this->original[$key])) {
				unset($this->original[$key]);
			}
			if (isset($this->data[$key])) {
				unset($this->data[$key]);
			}
			if (isset($this->fields[$key])) {
				unset($this->fields[$key]);
			}
		}
		
		/**
		 *
		 */
		function rewind()
		{
			reset($this->data);
		}
		
		/**
		 * @return mixed
		 */
		public function current()
		{
			return current($this->fields);
		}
		
		/**
		 * @return mixed
		 */
		public function next()
		{
			return next($this->fields);
		}
		
		/**
		 * @return mixed
		 */
		public function key()
		{
			return key($this->fields);
		}
		
		/**
		 * @return mixed
		 */
		public function valid()
		{
			return current($this->fields);
		}
	}