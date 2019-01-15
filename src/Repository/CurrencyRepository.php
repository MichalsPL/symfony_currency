<?php

namespace App\Repository;

use App\Entity\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{

    private $cache;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Currency::class);
        $this->cache = new FilesystemCache();
    }


    public function getCurrencyArray()
    {
        if (!$this->cache->has('currencies.list')) {
            $qb = $this->_em->createQueryBuilder()
                ->select(['c.name', 'c.code'])
                ->from($this->_entityName, 'c');

            $result = $qb->getQuery()->getResult(Query::HYDRATE_ARRAY);

            $this->cache->set('currencies.list', $result);
        } else {
            $result = $this->cache->get('currencies.list');
        }
        return $result;
    }

    public function getCurrentCurrencyMidPrice($currencyCode)
    {
        if (!$this->cache->has('rate.current.' . $currencyCode)) {
            $currentExchangeRate = null;
            $currency = $this->findOneBy(['code' => $currencyCode]);

            if ($currency && $exchangerates = $currency->getExchangeRateHistories()) {
                $currentExchangeRate = $exchangerates->last()->getMidPrice();
                $this->cache->set('rate.current.' . $currencyCode, $currentExchangeRate);
            }
        } else {
            $currentExchangeRate = $this->cache->get('rate.current.' . $currencyCode);
        }
        return $currentExchangeRate;
    }

    public function getAvgCurrencyMidPrice($currencyCode)
    {
        if (!$this->cache->has('rate.avg.' . $currencyCode)) {
            $currency = $this->findOneBy(['code' => $currencyCode]);
            if (!$currency || !$exchangeRateArr = $currency->getExchangeRateHistories()->toArray()) {
                return null;
            }

            $midPricesArr = [];
            foreach ($exchangeRateArr as $exchangeRate) {
                $midPricesArr[] = $exchangeRate->getMidPrice();
            }

            $midPricesArr = array_filter($midPricesArr);

            $average = array_sum($midPricesArr) / count($midPricesArr);
            $this->cache->set('rate.avg.' . $currencyCode, $average);

        } else {
            $average = $this->cache->get('rate.avg.' . $currencyCode);
        }
        return $average;
    }
}
