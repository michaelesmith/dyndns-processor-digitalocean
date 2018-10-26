<?php

namespace DynDNSKit\Tests\Unit;

use DigitalOceanV2\Api\DomainRecord;
use DigitalOceanV2\DigitalOceanV2;
use DigitalOceanV2\Entity\DomainRecord as DomainRecordEntity;
use DigitalOceanV2\Exception\HttpException;
use DynDNSKit\Processor\DigitalOceanApiProcessor;
use DynDNSKit\Processor\ProcessorException;
use DynDNSKit\Query;
use DynDNSKit\Tests\Common\TestCase;

class DigitalOceanApiProcessorTest extends TestCase
{
    public function dpTestProcess()
    {
        return [
            0 => [
                new Query($ip = '192.168.1.1', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->with($tld = 'myhost.com')->once()->andReturn([
                        $this->getDomainRecordEntity(1,'MX', 'mx'),
                        $this->getDomainRecordEntity(2, 'NS', 'ns'),
                        $this->getDomainRecordEntity(3, 'A', 'test2'),
                        $this->getDomainRecordEntity($id = 4, 'A', $host = 'test'),
                    ]);
                    $dr->shouldReceive('update')->with($tld, $id, $host, $ip)->once();

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
            ], // IPv4 update existing
            1 => [
                new Query($ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->with($tld = 'myhost.com')->once()->andReturn([
                        $this->getDomainRecordEntity(1,'MX', 'mx'),
                        $this->getDomainRecordEntity(2, 'NS', 'ns'),
                        $this->getDomainRecordEntity(3, 'A', 'test2'),
                        $this->getDomainRecordEntity($id = 4, 'AAAA', $host = 'test'),
                    ]);
                    $dr->shouldReceive('update')->with($tld, $id, $host, $ip)->once();

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
            ], // IPv6 update existing
            2 => [
                new Query($ip = '2001:db8:85a3::8a2e:370:7334', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->with($tld = 'myhost.com')->once()->andReturn([
                        $this->getDomainRecordEntity(1,'MX', 'mx'),
                        $this->getDomainRecordEntity(2, 'NS', 'ns'),
                        $this->getDomainRecordEntity(3, 'A', 'test2'),
                        $this->getDomainRecordEntity($id = 4, 'AAAA', $host = 'test'),
                    ]);
                    $dr->shouldReceive('update')->with($tld, $id, $host, $ip)->once();

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
            ], // IPv6 (short) update existing
            3 => [
                new Query($ip = '192.168.1.1', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->with($tld = 'myhost.com')->once()->andReturn([
                        $this->getDomainRecordEntity(1,'MX', 'mx'),
                        $this->getDomainRecordEntity(2, 'NS', 'ns'),
                        $this->getDomainRecordEntity(3, 'A', 'test2'),
                        $this->getDomainRecordEntity($id = 4, 'AAAA', $host = 'test'),
                    ]);
                    $dr->shouldReceive('delete')->with($tld, $id)->once();
                    $dr->shouldReceive('create')->with($tld, 'A', $host, $ip)->once();

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
            ], // IPv4 switch from IPv6
            4 => [
                new Query($ip = '192.168.1.1', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->with($tld = 'myhost.com')->once()->andReturn([
                        $this->getDomainRecordEntity(1,'MX', 'mx'),
                        $this->getDomainRecordEntity(2, 'NS', 'ns'),
                        $this->getDomainRecordEntity(3, 'A', 'test2'),
                    ]);
                    $dr->shouldReceive('create')->with($tld, 'A', 'test', $ip)->once();

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
            ], // IPv4 new
            5 => [
                new Query($ip = '192.168.1.1', ['test.myhost.com']), // $query
                function() use ($ip) {
                    $dr = \Mockery::mock(DomainRecord::class);
                    $dr->shouldReceive('getAll')->andThrow(HttpException::class);

                    $api = \Mockery::mock(DigitalOceanV2::class);
                    $api->shouldReceive('domainRecord')->once()->andReturn($dr);

                    return $api;
                }, // $api
                ProcessorException::class, // $exceptionClass
                '/An exception was thrown by the API client/', // $exceptionMessage
            ], // Exception form API
            6 => [
                new Query($ip = '999.168.1.1', ['test.myhost.com']), // $query
                function() {
                    $api = \Mockery::mock(DigitalOceanV2::class);

                    return $api;
                }, // $api
                ProcessorException::class, // $exceptionClass
                sprintf('/The ip "%s" is not a valid IPV4 or IPV6 address/', $ip), // $exceptionMessage
            ], // Exception form API
        ];
    }

    private function getDomainRecordEntity($id, $type, $name)
    {
        $dre = new DomainRecordEntity();
        $dre->id = $id;
        $dre->type = $type;
        $dre->name = $name;

        return $dre;
    }

    /**
     * @dataProvider dpTestProcess
     */
    public function testProcess($query, $api, $exceptionClass = null, $exceptionMessage = null)
    {
        $api = $api();

        if ($exceptionClass) {
            $this->expectException($exceptionClass);
            $this->expectExceptionMessageRegExp($exceptionMessage);
        }

        $sut = new DigitalOceanApiProcessor(['myhost.com'], $api);
        $this->assertTrue($sut->process($query));
    }
}
