<?php

namespace Fenguoz\Distribution\Exceptions;

class UsersMachineException extends \Exception
{
    const MAC_ERROR = 72300;
    const RESULT_ERROR = 72301;
    const TYPE_ERROR = 72302;
    const NOT_LOTTERY = 72303;
    const MAC_EXIST = 72304;

    static public $__names = array(
        self::MAC_ERROR => 'MAC_ERROR',
        self::TYPE_ERROR => 'TYPE_ERROR',
        self::NOT_LOTTERY => 'NOT_LOTTERY',
        self::MAC_EXIST => 'MAC_EXIST',
    );

    /**
     * CommonException constructor.
     * @param string $code
     * @param string $replace
     */
    public function __construct($code, $replace = '')
    {
        $message = self::$__names[$code];
        if (!empty($replace)) {
            if (is_string($replace)) {
                $message = $replace;
            }
            if (is_array($replace)) {
                foreach ($replace as $k => $v) {
                    $message = str_replace(':' . $k, $v, $message);
                }
            }
        }
        parent::__construct($message, $code);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        return response()->json([
            'code' => $this->code,
            'message' => $this->message,
            'data' => []
        ]);
    }
}
