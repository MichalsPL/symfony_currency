<?php

namespace App\Command;

use App\Entity\Currency;
use App\Entity\ExchangeRateHistory;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command
{
    protected $em;

    protected $container;

    protected $cache;

    public function __construct($name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->cache=new FilesystemCache();
    }

    protected function configure()
    {
        $reflect = new \ReflectionClass($this);
        $className = $reflect->getShortName();
        $this
            ->setName('get:' . $className)
            ->setDescription('Getting current rates for ' . $className)
            ->setHelp('This command is getting curent rates for class' . $className);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rawData = $this->fetchData();
        if (!$rawData) {
        throw new Exception('no data to fetch check api address and internet connection');
        }
        $preparedData = $this->prepareData($rawData);
        $this->saveData($preparedData);
    }

    abstract protected function fetchData(): array;

    abstract protected function prepareData(array $rawData): array;

    protected function saveData(array $data)
    {
        foreach ($data as $currencyData) {
            $currency = $this->prepareCurrency($currencyData);
            $this->addExchangeRate($currency, $currencyData);
        }

        $this->cache->clear();
    }

    protected function prepareCurrency(array $currencyData): Currency
    {
        $currency = $this->em->getRepository(Currency::class, 'currency')
            ->findOneBy(['code' => $currencyData['code']]);
        if (!$currency) {
            $currency = new Currency();
            $currency->setName($currencyData['name']);
            $currency->setCode($currencyData['code']);
            $this->em->persist($currency);
            $this->em->flush();
        }
        return $currency;
    }

    protected function addExchangeRate(Currency $currency, array $currencyData)
    {
        $exchangeRate = new ExchangeRateHistory();
        $exchangeRate->setCurrency($currency);
        $exchangeRate->setMidPrice($currencyData['mid']);
        $exchangeRate->setDate(new \DateTime('now'));
        $this->em->persist($exchangeRate);
        $this->em->flush();
    }


}