<?php

namespace App\Controller;

use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{

    public function currencyList()
    {

        $em = $this->getDoctrine()->getManager();
        $currencies = $em->getRepository(Currency::class)->getCurrencyArray();
        return $this->json($currencies);
    }

    public function currentRate($currCode)
    {
        $em = $this->getDoctrine()->getManager();
        $currentRate = $em->getRepository(Currency::class)->getCurrentCurrencyMidPrice($currCode);

        if ($currentRate) {
            return $this->json($currentRate);
        }
        throw new Exception('No currency with this code');
    }

    public function avrageRate($currCode)
    {
        $em = $this->getDoctrine()->getManager();
        $avrageRate = $em->getRepository(Currency::class)->getAvgCurrencyMidPrice($currCode);
        if ($avrageRate) {
            return $this->json($avrageRate);
        }
        throw new Exception('No currency with this code');
    }

}
