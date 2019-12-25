<?php

namespace Fenguoz\Distribution\Http\Controllers;

use App\Libraries\Send;
use Fenguoz\Distribution\Services\UsersMachineService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UsersMachineController extends Controller
{
    use Send;

    public function recordLotteryResult(Request $request, UsersMachineService $usersMachineService)
    {
        $mac = $request->input('mac');
        $result = $request->input('result');
        $type = $request->input('type');
        $data = $usersMachineService->recordLotteryResult($mac, $result, $type);
        return self::success($data);
    }

    public function getLotteryResult(Request $request, UsersMachineService $usersMachineService)
    {
        $mac = $request->input('mac') ?? '';
        $data = $usersMachineService->getLotteryResult($mac);
        return self::success($data);
    }

    public function addMachine(Request $request, UsersMachineService $usersMachineService)
    {
        $user_id = $request->get('user_id');
        $sku_id = $request->input('sku_id') ?? '';
        $number = $request->input('number') ?? '';
        $type = $request->input('type') ?? 1;
        $specify_cycle = $request->input('specify_cycle') ?? '';

        $data = $usersMachineService->addMachine((int) $user_id, (int) $sku_id, (int) $number, (int) $type, $specify_cycle);
        return self::success($data);
    }
}
