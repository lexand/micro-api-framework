<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 30.09.16
 * Time: 14:14
 */

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use tests\utils\TestServer;

class ControllerTest extends TestCase {
    /**
     * @var \GuzzleHttp\Client
     */
    private static $client;

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        static::$client = new Client();
        TestServer::start();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        TestServer::stop();
    }



    public function testGet() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/test6547586/get";

        static::assertArrayHasKey('simple_response', $this->doRequest('get', $endPoint));
    }

    public function testGetparametrized() {
        $port = TestServer::PORT;

        $name = 'alex';
        $age  = 99;

        $endPoint = "http://localhost:{$port}/test6547586/getparametrized/{$name}/{$age}";

        $response = $this->doRequest('get', $endPoint);

        static::assertArrayHasKey('name', $response);
        static::assertArrayHasKey('age', $response);
        static::assertEquals($name, $response['name']);
        static::assertEquals($age, $response['age']);
    }

    public function testPostparametrized() {
        $port = TestServer::PORT;

        $a = 1;
        $b = 99.45;
        $c = 'test';
        $d = 1;

        $endPoint = "http://localhost:{$port}/test6547586/postparametrized/{$a}/{$b}/{$c}/{$d}";

        $response = $this->doRequest('post', $endPoint, []);

        static::assertArrayHasKey('a', $response);
        static::assertArrayHasKey('b', $response);
        static::assertArrayHasKey('c', $response);
        static::assertArrayHasKey('d', $response);
        static::assertEquals($a, $response['a']);
        static::assertEquals($b, $response['b']);
        static::assertEquals($c, $response['c']);
        static::assertEquals($d, $response['d']);
    }


    /**
     * @param string $method
     * @param string $endPoint
     * @param array  $data
     * @return array
     */
    private function doRequest(string $method, string $endPoint, array $data = []): array {
        switch ($method) {
            case 'get':
                if ($data === []) {
                    $response = static::$client->get($endPoint);
                }
                else {
                    $response = static::$client->get(
                        $endPoint,
                        [
                            'content-type' => 'application/json',
                            'json'         => $data
                        ]
                    );
                }
                break;
            case 'post':
                $response = static::$client->post(
                    $endPoint,
                    [
                        'content-type' => 'application/json',
                        'json'         => $data
                    ]
                );
                break;
            default:
                throw new \LogicException('incorrect HTTP method');
        }


        $body = $response->getBody();
        $size = $body->getSize();
        $body = $body->read($size);

        echo "RESPONSE:\n" . $body . "\n";

        return \GuzzleHttp\json_decode($body, true);
    }

    public function testGetPostdtoViaPost() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/test6547586/getpostdto";

        $name = 'Alex';
        $age  = 10;

        $response = $this->doRequest(
            'post',
            $endPoint,
            [
                'name' => $name,
                'age'  => $age
            ]
        );

        static::assertArrayHasKey('name', $response);
        static::assertArrayHasKey('age', $response);
        static::assertEquals($name, $response['name']);
        static::assertEquals($age, $response['age']);
    }

    public function testGetPostdtoViaGet() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/test6547586/getpostdto";

        $name = 'Alex';
        $age  = 10;

        $response = $this->doRequest(
            'get',
            $endPoint,
            [
                'name' => $name,
                'age'  => $age
            ]
        );

        static::assertArrayHasKey('name', $response);
        static::assertArrayHasKey('age', $response);
        static::assertEquals($name, $response['name']);
        static::assertEquals($age, $response['age']);
    }

    public function testPostmixed() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/test6547586/postmixedargs/somestring";

        $name = 'Alex';
        $age  = 10;

        $response = $this->doRequest(
            'post',
            $endPoint,
            [
                'name' => $name,
                'age'  => $age
            ]
        );

        static::assertArrayHasKey('c', $response);
        static::assertArrayHasKey('dto', $response);
        static::assertEquals('somestring', $response['c']);
        static::assertEquals($name, $response['dto']['name']);
        static::assertEquals($age, $response['dto']['age']);
    }

    public function testDefaultController() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/";

        static::assertEquals((new \app\controller\Main6547586Ctl())->actionIndex(), $this->doRequest('get', $endPoint));
    }

    public function testIndex() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/test6547586";

        static::assertEquals((new \app\controller\Test6547586Ctl())->actionIndex(), $this->doRequest('get', $endPoint));
    }

    public function testIndexAdminModule() {
        $port = TestServer::PORT;

        $endPoint = "http://localhost:{$port}/admin/test6547586";

        static::assertEquals((new \admin\controller\Test6547586Ctl())->actionIndex(), $this->doRequest('get', $endPoint));

    }

}