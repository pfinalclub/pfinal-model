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
//			Config::loadFiles('tests/config');
//			DB::execute('drop table if exists model_base');
//			$sql
//				= <<<str
//CREATE TABLE if not exists `model_base` (
//  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
//  `title` varchar(100) DEFAULT '',
//  PRIMARY KEY (`id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8;
//str;
//			DB::execute($sql);
//			Db::execute('truncate model_base');
//			$data = [
//				['title' => 'pfinal-cms'],
//				['title' => 'pfinal'],
//				['title' => 'pf'],
//			];
//			foreach ($data as $d) {
//				Db::table('model_base')->insert($d);
//			}
		}
		
		public function test_get()
		{
			Config::loadFiles('tests/config');
			$model = ModelBase::where('id', 4)->first();
			$this->assertEquals('pfinal', $model['title']);
		}
		
		public function test_add()
		{
			$Model = new ModelBase();
			//然后直接给数据对象赋值
			$Model['title'] = 'pfinal';
			//把数据对象添加到数据库
			$res = $Model->save();
			$this->assertTrue($res >= 1);
		}
//
		public function test_update()
		{
			$Model = ModelBase::find(2);
			//然后直接给数据对象赋值
			$Model['title'] = 'ff';
			//把数据对象添加到数据库
			$res = $Model->save();
			$this->assertObjectHasAttribute('table', $res);
		}
	}
