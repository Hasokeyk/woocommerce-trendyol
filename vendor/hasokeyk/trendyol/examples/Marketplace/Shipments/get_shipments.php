<?php

    //Source : https://developers.trendyol.com/tr/marketplace-entegrasyonu/siparis-entegrasyonu/siparis-paketlerini-cekme

    use Hasokeyk\Trendyol\Trendyol;

    require "vendor/autoload.php";

    $supplierId = 'XXXXXX';
    $username   = 'XXXXXXXXXXXXXXXXXXXX';
    $password   = 'XXXXXXXXXXXXXXXXXXXX';

    $trendyol = new Trendyol($supplierId, $username, $password);

    $trendyol_marketplace_shipments = $trendyol->marketplace->TrendyolMarketplaceShipment();

    $shipments = $trendyol_marketplace_shipments->get_shipment_companies();
    print_r($shipments);
