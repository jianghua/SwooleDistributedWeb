<?php
/**
 * Created by PhpStorm.
 * User: zhangjincheng
 * Date: 16-7-15
 * Time: 下午3:51
 */
namespace Server\Controllers;

use Server\Asyn\TcpClient\SdTcpRpcPool;
use Server\Components\Consul\ConsulServices;
use Server\CoreBase\Controller;
use Server\CoreBase\SelectCoroutine;
use Server\Memory\Lock;
use Server\Models\TestModel;
use Server\Tasks\TestTask;

class TestController extends Controller
{
    /**
     * @var TestTask
     */
    public $testTask;

    /**
     * @var TestModel
     */
    public $testModel;

    /**
     * @var SdTcpRpcPool
     */
    public $sdrpc;


    public function tcp()
    {
        $this->sdrpc = get_instance()->getAsynPool('RPC');
        $data = $this->sdrpc->helpToBuildSDControllerQuest($this->context, 'MathService', 'add');
        $data['params'] = [1, 2];
        $result = yield $this->sdrpc->coroutineSend($data);
        $this->http_output->end($result);
    }

    public function http_ex()
    {
        throw new \Exception("1");
        $value = yield $this->redis_pool->getCoroutine()->ping();

    }

    public function mysql()
    {
        $model = $this->loader->model('TestModel', $this);
        $result = yield $model->testMysql();
        $this->http_output->end($result);
    }

    /**
     * tcp的测试
     */
    public function tcp_testTcp()
    {
        $this->send($this->client_data->data);
    }

    public function tcp_add()
    {
        $max = $this->client_data->max;
        if (empty($max)) {
            $max = 100;
        }
        $sum = 0;
        for ($i = 0; $i < $max; $i++) {
            $sum += $i;
        }
        $this->send($max);
    }

    public function testContext()
    {
        $this->getContext()['test'] = 1;
        $this->testModel = $this->loader->model('TestModel', $this);
        $this->testModel->contextTest();
        $this->http_output->end($this->getContext());
    }

    /**
     * mysql 事务协程测试
     */
    public function mysql_begin_coroutine_test()
    {
        $id = yield $this->mysql_pool->coroutineBegin($this);
        $update_result = yield $this->mysql_pool->dbQueryBuilder->update('user_info')->set('sex', '1')->where('uid', 10000)->coroutineSend($id);
        $result = yield $this->mysql_pool->dbQueryBuilder->select('*')->from('user_info')->where('uid', 10000)->coroutineSend($id);
        if ($result['result'][0]['channel'] == 1000) {
            $this->http_output->end('commit');
            yield $this->mysql_pool->coroutineCommit($id);
        } else {
            $this->http_output->end('rollback');
            yield $this->mysql_pool->coroutineRollback($id);
        }
    }

    /**
     * 绑定uid
     */
    public function tcp_bind_uid()
    {
        $this->bindUid($this->fd, $this->client_data->data);
        $this->destroy();
    }

    /**
     * 效率测试
     * @throws \Server\CoreBase\SwooleException
     */
    public function tcp_efficiency_test()
    {
        $data = $this->client_data->data;
        $this->sendToUid(mt_rand(1, 100), $data);
    }

    /**
     * 效率测试
     * @throws \Server\CoreBase\SwooleException
     */
    public function tcp_efficiency_test2()
    {
        $data = $this->client_data->data;
        $this->send($data);
    }

    /**
     * mysql效率测试
     * @throws \Server\CoreBase\SwooleException
     */
    public function tcp_mysql_efficiency()
    {
        yield $this->mysql_pool->dbQueryBuilder->select('*')->from('account')->where('uid', 10004)->coroutineSend();
        $this->send($this->client_data->data);
    }

    /**
     * 获取mysql语句
     */
    public function mysqlStatement()
    {
        $value = $this->mysql_pool->dbQueryBuilder->insertInto('account')->intoColumns(['uid', 'static'])->intoValues([[36, 0], [37, 0]])->getStatement(true);
        $this->http_output->end($value);
    }

    /**
     * http测试
     */
    public function test()
    {
        $max = $this->http_input->get('max');
        if (empty($max)) {
            $max = 100;
        }
        $sum = 0;
        for ($i = 0; $i < $max; $i++) {
            $sum += $i;
        }
        $this->http_output->end($sum);
    }

    public function redirect()
    {
        $this->redirectController('TestController','test');
    }

    /**
     * health
     */
    public function health()
    {
        $this->http_output->end('ok');
    }

    /**
     * http redis 测试
     */
    public function redis()
    {
        $testModel = $this->loader->model('TestModel', $this);
        $result = yield $testModel->testRedis();
        $this->http_output->end(1);
    }

    /**
     * http 同步redis 测试
     */
    public function aredis()
    {
        $value = get_instance()->getRedis()->get('test');
        $this->http_output->end(1);
    }

    /**
     * html测试
     */
    public function html_test()
    {
        $template = $this->loader->view('server::error_404');
        $this->http_output->end($template->render(['controller' => 'TestController\html_test', 'message' => '页面不存在！']));
    }

    /**
     * html测试
     */
    public function html_file_test()
    {
        $this->http_output->endFile(SERVER_DIR, 'Views/test.html');
    }

    /**
     * select方法测试
     * @return \Generator
     */
    public function test_select()
    {
        yield $this->redis_pool->getCoroutine()->set('test', 1);
        $c1 = $this->redis_pool->getCoroutine()->get('test');
        $c2 = $this->redis_pool->getCoroutine()->get('test1');
        $result = yield SelectCoroutine::Select(function ($result) {
            if ($result != null) {
                return true;
            }
            return false;
        }, $c2, $c1);
        $this->http_output->end($result);
    }

    public function http_getAllTask()
    {
        $messages = get_instance()->getServerAllTaskMessage();
        $this->http_output->end(json_encode($messages));
    }

    /**
     * @return boolean
     */
    public function isIsDestroy()
    {
        return $this->is_destroy;
    }

    public function lock()
    {
        $lock = new Lock('test1');
        $result = yield $lock->coroutineLock();
        $this->http_output->end($result);
    }

    public function unlock()
    {
        $lock = new Lock('test1');
        $result = yield $lock->coroutineUnlock();
        $this->http_output->end($result);
    }

    public function destroylock()
    {
        $lock = new Lock('test1');
        $lock->destroy();
        $this->http_output->end(1);
    }

    public function testTask()
    {
        $testTask = $this->loader->task('TestTask', $this);
        $testTask->testMysql();
        $result = yield $testTask->coroutineSend();
        $this->http_output->end($result);
    }

    public function testConsul()
    {
        $rest = ConsulServices::getInstance()->getRESTService('MathService', $this->context);
        $rest->setQuery(['one' => 1, 'two' => 2]);
        $reuslt = yield $rest->add();
        $this->http_output->end($reuslt['body']);
    }

    public function testConsul2()
    {
        $rest = ConsulServices::getInstance()->getRPCService('MathService', $this->context);
        $reuslt = yield $rest->add(1, 2);
        $this->http_output->end($reuslt);
    }
    public function testConsul3()
    {
        $rest = ConsulServices::getInstance()->getRPCService('MathService', $this->context);
        $reuslt = yield $rest->call('add',[1, 2],true);
        $this->http_output->end($reuslt);
    }

    public function testRedisLua()
    {
        $value = yield $this->redis_pool->getCoroutine()->evalSha(getLuaSha1('sadd_from_count'),['testlua',100],2);
        $this->http_output->end($value);
    }

    public function testTaskStop()
    {
        $task = $this->loader->task('TestTask',$this);
        $task->testStop();
        yield $task->coroutineSend();
    }

    public function testLeader()
    {
        $ConsulModel = $this->loader->model('ConsulModel',$this);
        $result = yield $ConsulModel->leader();
        var_dump($result);
        $this->http_output->end($result);
    }
}