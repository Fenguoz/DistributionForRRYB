<?php

namespace Fenguoz\Distribution\Services;

use App\Services\Order\Client\GoodsService;
use App\Services\Service;
use Fenguoz\Distribution\Exceptions\CommonException;
use Fenguoz\Distribution\Exceptions\UsersMachineException;
use Fenguoz\Distribution\Models\LotteryResultModel;
use Fenguoz\Distribution\Models\UsersMachineModel;
use Fenguoz\Distribution\Models\UsersModel;
use Fenguoz\MachineLease\Models\LevelModel;
use Illuminate\Support\Facades\DB;

class UsersMachineService extends Service
{
    public function recordLotteryResult(string $mac, int $result, int $type)
    {
        if (!$this->verifyMac($mac))
            throw new UsersMachineException(UsersMachineException::MAC_ERROR);
        if ($result < 0)
            throw new UsersMachineException(UsersMachineException::RESULT_ERROR);
        if (!in_array($type, [1, 2]))
            throw new UsersMachineException(UsersMachineException::TYPE_ERROR);
        if (LotteryResultModel::where('mac', $mac)->exists())
            throw new UsersMachineException(UsersMachineException::MAC_EXIST);

        $result = LotteryResultModel::insert([
            'mac' => $mac,
            'result' => $result,
            'type' => $type,
        ]);
        if (!$result)
            throw new CommonException(CommonException::DATA_ERRPR);
        return $result;
    }

    public function verifyMac(string $mac): bool
    {
        $pattern = "/^[A-F0-9]{2}(-[A-F0-9]{2}){5}$/";
        return preg_match($pattern, $mac) ? true : false;
    }

    public function getLotteryResult(string $mac)
    {
        if (!$this->verifyMac($mac))
            throw new UsersMachineException(UsersMachineException::MAC_ERROR);
        $data = LotteryResultModel::where('mac', $mac)->first();
        if (!$data)
            throw new UsersMachineException(UsersMachineException::NOT_LOTTERY);
        return  $data;
    }

    public function extendMachine(int $user_id, int $type = 1, int $extend_cycle = null)
    {
        if ($user_id <= 0)
            throw new CommonException(CommonException::USER_ID_ERROR);

        $info = UsersMachineModel::where([
            'user_id' => $user_id,
            'type' => $type
        ])->first();
        if (!$info) {
            $this->addMachine($user_id, 4, 2, $type, $extend_cycle);
        } else {
            if ($info->status == 1) {
                $info->expired_time += $extend_cycle * 3600;
                $info->cycle += $extend_cycle;
            } else {
                $info->status = 1;
                $info->start_time = strtotime(date('Y-m-d 0:0:0', time())) + 86400;
                $info->expired_time += strtotime(date('Y-m-d 23:59:59', time())) + $extend_cycle * 3600;
                $info->cycle += $extend_cycle;
            }
            $info->save();
        }
        return true;
    }

    public function addMachine(int $user_id, int $sku_id, int $number, int $type = 1, int $specify_cycle = null)
    {
        if ($user_id <= 0)
            throw new CommonException(CommonException::USER_ID_ERROR);

        $sku_info = (new GoodsService)->good($sku_id);
        if (empty($sku_info))
            throw new CommonException(CommonException::GOODS_NOT_EXIST);
        $cycle = $specify_cycle ? $specify_cycle : $sku_info[0]['cycle'];

        $machine_data = [
            'user_id' => $user_id,
            'sku_id' => $sku_info[0]['id'],
            'sku_name' => $sku_info[0]['title'],
            'start_time' => strtotime(date('Y-m-d 0:0:0', time())) + 86400, //次日生效
            'expired_time' => strtotime(date('Y-m-d 23:59:59', time())) + $cycle * 3600,
            'power' => $sku_info[0]['power'],
            'computing_power' => $number,
            'cycle' => $cycle,
            'machine_type' => 'BTC',
            'type' => $type,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        try {
            $result = UsersMachineModel::insert($machine_data);
            if (!$result) throw new CommonException(CommonException::USER_ID_ERROR);

            $user = UsersModel::where('user_id', $user_id)->first();
            if(!$user){
                $result = UsersModel::insert(['user_id' => $user_id]);
                if (!$result) throw new CommonException(CommonException::USER_ID_ERROR);
            }
            switch ($type) {
                case 1:
                case 2:
                    $result = UsersModel::where('user_id', $user_id)->increment('power', $number);
                    if (!$result) throw new CommonException(CommonException::UPDATE_POWER_ERROR);
                    break;
                case 3:
                    $result = UsersModel::where('user_id', $user_id)->increment('reward_power', $number);
                    if (!$result) throw new CommonException(CommonException::UPDATE_POWER_ERROR);
                    break;
                case 10:
                    $result = UsersModel::where('user_id', $user_id)->increment('reward_team_power', $number);
                    if (!$result) throw new CommonException(CommonException::UPDATE_POWER_ERROR);
                    break;
            }
        } catch (CommonException $e) {
            throw new CommonException($e->getCode());
        }
        return true;
    }

    public function team(int $user_id, int $count = 10)
    {
        $usertolevel = DB::table('rryb_users.user_relation')->where('invite_user_id', $user_id)->pluck('level', 'user_id')->all() ?? [];

        $leveltoname = LevelModel::pluck('name', 'level')->all();
        $leveltoname[0] = '注册会员';
        $user_ids = array_keys($usertolevel);
        $users = DB::table('rryb_users.user_relation')->whereIn('user_id', $user_ids)->paginate($count);
        $user_data = [];
        foreach ($users as $user) {

            $mobile = DB::table('rryb_users.user')->where('id', $user->user_id)->value('mobile') ?? '';
            $child_ids = DB::table('rryb_users.user_relation')->where('root', 'like', "%,{$user->user_id},%")->pluck('user_id') ?? [];
            $team_power = UsersModel::whereIn('user_id', $child_ids)->sum('power') ?? '0.00000000';
            $power = UsersModel::where('user_id', $user->user_id)->value('power') ?? '0.00000000';

            $user_data[] = [
                'mobile' => $mobile,
                'avatar' => '',
                'level_name' => $leveltoname[$usertolevel[$user->user_id]],
                'power' => $power,
                'team_power' => $team_power
            ];
        }

        return $user_data;
    }
}
