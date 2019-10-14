<?php
	/**
	 * ----------------------------------------
	 * | Created By pfinal-model                 |
	 * | User: pfinal <lampxiezi@163.com>     |
	 * | Date: 2019/10/14                      |
	 * | Time: 下午2:13                        |
	 * ----------------------------------------
	 * |    _____  ______ _             _     |
	 * |   |  __ \|  ____(_)           | |    |
	 * |   | |__) | |__   _ _ __   __ _| |    |
	 * |   |  ___/|  __| | | '_ \ / _` | |    |
	 * |   | |    | |    | | | | | (_| | |    |
	 * |   |_|    |_|    |_|_| |_|\__,_|_|    |
	 * ----------------------------------------
	 */
	
	namespace tests\models;
	
	
	use pf\model\Model;
	
	class ModelBase extends Model
	{
		protected $timestamps = true;
		protected $auto = [
			//更新时对 addtime 字段执行strtotime函数
			['click', 100, 'string', self::MUST_AUTO, self::MODEL_BOTH],
			[
				'addtime',
				'time',
				'function',
				self::MUST_AUTO,
				self::MODEL_INSERT,
			],
		];
	}