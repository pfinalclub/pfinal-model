<?php
	
	namespace tests;
	
	use pf\config\Config;
	use pf\db\DB;
	use tests\models\ModelBase;
	
	/**
	 * ----------------------------------------
	 * | Created By pfinal-model                 |
	 * | User: pfinal <lampxiezi@163.com>     |
	 * | Date: 2019/10/14                      |
	 * | Time: 下午2:15                        |
	 * ----------------------------------------
	 * |    _____  ______ _             _     |
	 * |   |  __ \|  ____(_)           | |    |
	 * |   | |__) | |__   _ _ __   __ _| |    |
	 * |   |  ___/|  __| | | '_ \ / _` | |    |
	 * |   | |    | |    | | | | | (_| | |    |
	 * |   |_|    |_|    |_|_| |_|\__,_|_|    |
	 * ----------------------------------------
	 */
	class BaseTest extends \PHPUnit\Framework\TestCase
	{
		public function setUp()
		{
			Config::loadFiles('tests/config');
			DB::execute('truncate model_base');
			$data = [
				['title' => 'pfinal'],
				['title' => 'pfinal-cms'],
				['title' => 'pfphp'],
			];
			foreach ($data as $d) {
				DB::table('model_base')->insert($d);
			}
		}
		
		/**
		 * @test
		 */
		public function get()
		{
			$model = ModelBase::where('id', 1)->first();
			$this->assertEquals('hdcms', $model['title']);
		}
	}
