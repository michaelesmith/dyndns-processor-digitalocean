<?php
declare(strict_types = 1);

namespace DynDNSKit\Processor;

use DigitalOceanV2\DigitalOceanV2;
use DigitalOceanV2\Exception\ExceptionInterface;
use DynDNSKit\Query;
use DynDNSKit\Validator\IPValidator;

class DigitalOceanApiProcessor implements ProcessorInterface
{
    /**
     * @var string[]
     */
    private $tlds = [];

    /**
     * @var DigitalOceanV2
     */
    private $api;

    /**
     * @param string[] $tlds
     * @param DigitalOceanV2 $api
     */
    public function __construct(array $tlds, DigitalOceanV2 $api)
    {
        $this->tlds = $tlds;
        $this->api = $api;
    }

    /**
     * @inheritdoc
     */
    public function process(Query $query): bool
    {
        $ip = $query->getIp();
        if (!IPValidator::ip($ip)) {
            throw new ProcessorException(sprintf('The ip "%s" is not a valid IPV4 or IPV6 address', $ip));
        }

        try {
            $api = $this->api->domainRecord();
            foreach ($query->getHostnames() as $hostname) {
                if (list($tld, $host) = $this->getTld($hostname)) {
                    $matched = false;
                    foreach ($api->getAll($tld) as $domainRecord) {
                        if (in_array($domainRecord->type, ['A', 'AAAA']) && ($domainRecord->name . '.' . $tld) == $hostname) {
                            if (('A' == $domainRecord->type) == IPValidator::ipv4($ip)) {
                                $api->update($tld, $domainRecord->id, $host, $ip);
                                $matched = true;
                            } else {
                                $api->delete($tld, $domainRecord->id);
                            }
                        }
                    }
                    if (!$matched) { // we don't have an existing one let's create a new one
                        $api->create($tld,  IPValidator::ipv4($ip) ? 'A' : 'AAAA', $host, $ip);
                    }
                }
            }
        } catch (ExceptionInterface $e) {
            throw new ProcessorException('An exception was thrown by the API client', 0, $e);
        }

        return true;
    }

    private function getTld($hostname)
    {
        foreach ($this->tlds as $tld) {
            if (substr($hostname, -1 * strlen($tld)) == $tld) {
                return [$tld, substr($hostname, 0, -1 * (strlen($tld) + 1))];
            }
        }
    }
}
