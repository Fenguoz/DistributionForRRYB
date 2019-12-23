<?php

namespace Fenguoz\Distribution\Services;

use App\Services\Service;
use Fenguoz\Distribution\Exceptions\CommonException;
use Fenguoz\Distribution\Exceptions\MachineException;
use Fenguoz\Distribution\Exceptions\UsersMachineException;
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
}
