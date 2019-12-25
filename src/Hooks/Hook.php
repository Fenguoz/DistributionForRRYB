<?php

use Fenguoz\Distribution\Models\LotteryResultModel;
use Fenguoz\Distribution\Models\OrderQueueModel;
use Fenguoz\Distribution\Services\UsersMachineService;
use App\Models\User\UserModel;

class PluginMachineleaseHook
{
	public function paySuccess(&$order_info)
	{
		return OrderQueueModel::insert([
			'order_sn' => $order_info['order_sub']['order_sn'],
			'order_sub_sn' => $order_info['order_sub']['sub_sn']
		]);
	}

	public function create_user_after(&$data)
	{
		//抽奖奖励
		// if (!empty($data['mac'])) {
		// 	$info = LotteryResultModel::where('mac', $data['mac'])->first();
		// 	if (!$info) return true;

		// 	$user_id = UserModel::where('mobile', $data['mobile'])->value('id');
		// 	switch ($info->type) {
		// 		case 1: //矿机
		// 			$data = (new UsersMachineService)->addMachine($user_id, $data['mac'], $info->result, 10, $info->result);
		// 			break;
		// 		case 2: //券

		// 			break;
		// 	}
		// }
	}

	public function create_user_rela_after(&$data)
	{
		//上级奖励

		//等级更新
	}
}
