<?php

namespace Fenguoz\Distribution;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var \Illuminate\Contracts\Routing\Registrar
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  \Illuminate\Contracts\Routing\Registrar  $router
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for reward and reward order.
     *
     * @return void
     */
    public function all()
    {
        $this->forUsersMachine();
    }

    /**
     * Register the routes for reward.
     *
     * @return void
     */
    public function forUsersMachine()
    {
        $this->router->group(['middleware' => 'token:user'], function ($router) {
            $router->post('/post.add.machine', ['uses' => 'UsersMachineController@addMachine']);
            $router->post('/post.extend.machine', ['uses' => 'UsersMachineController@extendMachine']);
            $router->get('/get.user.team', ['uses' => 'UsersMachineController@team']);
        });
        $this->router->group(['middleware' => 'token:client'], function ($router) {
            $router->get('/get.lottery.result', ['uses' => 'UsersMachineController@getLotteryResult']);
            $router->post('/post.lottery.result', ['uses' => 'UsersMachineController@recordLotteryResult']);
        });
    }
}
