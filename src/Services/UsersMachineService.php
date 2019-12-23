<?php

namespace Fenguoz\Distribution\Services;

use App\Services\Service;
use Fenguoz\Distribution\Exceptions\CommonException;
use Fenguoz\Distribution\Exceptions\MachineException;
use Fenguoz\Distribution\Exceptions\UsersMachineException;
use Fenguoz\Distribution\Models\LotteryResultModel;
use Fenguoz\Distribution\Models\UsersMachineModel;
use Fenguoz\Distribution\Models\UsersMachineOutputModel;
use Fenguoz\Distribution\Models\UsersModel;

class UsersMachineService extends Service
{

    public function getMachineList(array $params = [], array $option = [])
    {
        $count = isset($option['count']) ? $option['count'] : 10;
        $data = UsersMachineModel::where($params)->paginate($count);
        if (!$data)
            throw new CommonException(CommonException::DATA_ERRPR);
        return $data;
    }

    public function getMachineOutput(array $params = [], array $option = [])
    {
        if (!isset($params['machine_id']))
            throw new CommonException(CommonException::USER_ID_EMPTY);
        if ((int) $params['machine_id'] <= 0)
            throw new MachineException(MachineException::MACHINE_ID_ERROR);

        $count = isset($option['count']) ? $option['count'] : 10;
        $data = UsersMachineOutputModel::where($params)->paginate($count);
        if (!$data)
            throw new CommonException(CommonException::DATA_ERRPR);
        return $data;
    }

    public function getUserInfo(int $user_id)
    {
        if ((int) $user_id <= 0)
            throw new CommonException(CommonException::USER_ID_ERROR);

        $user = UsersModel::where([
            'user_id' => $user_id
        ])->first();
        if (!$user)
            throw new CommonException(CommonException::USER_NOT_EXIST);
        return $user;
    }

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
}
