<?php

namespace Webgk\Service\CatalogSync;

use Bitrix\Main\Application;

class Logger
{
    private $PROCESS_LOG = '/local/logs/catalog_syn/process.log';
    private $ERROR_LOG = '/local/logs/catalog_syn/error.log';
    private $OFFERS_LOG = '/local/logs/catalog_syn/offers.log';

    public function __construct()
    {

    }

    private function log($message, $filePath, $isStart)
    {
        if ($isStart) {
            $file = new IO\File(Application::getDocumentRoot() . $filePath);
            $file->delete();
        }

        \Bitrix\Main\Diag\Debug::writeToFile($message, date("d.m.Y H:i:s"), $filePath);
    }

    public function logProcess($message, $isStart = false)
    {
        $this->log($message, $this->PROCESS_LOG, $isStart);
    }

    public function logError($message, $isStart = false)
    {
        $this->log($message, $this->ERROR_LOG, $isStart);
    }

    public function logOffers($message, $isStart)
    {
        $this->log($message, $this->OFFERS_LOG, $isStart);
    }

    public function logOffersResults($offersResult, $productsResult, $pricesResult)
    {
        $offersCreatedCount = count($offersResult[0]);
        $offersCreated = implode(", ", $offersResult[0]);
        $offersUpdatedCount = count($offersResult[1]);
        $offersUpdated = implode(", ", $offersResult[0]);
        $this->logProcess("Offers created: {$offersCreatedCount} - ${offersCreated}", true);
        $this->logProcess("Offers updated: {$offersUpdatedCount} - ${$offersUpdated}");

        $productsCreatedCount = count($productsResult[0]);
        $productsCreated = implode(", ", $productsResult[0]);
        $productsUpdatedCount = count($productsResult[1]);
        $productsUpdated = implode(", ", $productsResult[0]);
        $this->logProcess("Products created: {$productsCreatedCount} - ${$productsCreated}", true);
        $this->logProcess("Products updated: {$productsUpdatedCount} - ${$productsUpdated}");

        $pricesCreatedCount = count($pricesResult[0]);
        $pricesCreated = implode(", ", $pricesResult[0]);
        $pricesUpdatedCount = count($pricesResult[1]);
        $pricesUpdated = implode(", ", $pricesResult[0]);
        $this->logProcess("Prices created: {$pricesCreatedCount} - ${$pricesCreated}", true);
        $this->logProcess("Prices updated: {$pricesUpdatedCount} - ${$pricesUpdated}");
    }
}