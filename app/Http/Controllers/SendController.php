<?php

namespace App\Http\Controllers;

use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\HtmlNode;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\CurlException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;
use PHPHtmlParser\Options;

class SendController extends Controller
{

    /**
     * @throws ChildNotFoundException
     * @throws CurlException
     * @throws CircularException
     * @throws StrictException
     * @throws NotLoadedException
     */
    public static function send()
    {
        $CSVcollect = [];
        $carNumber  = 0;

        $dom = new Dom();
        $dom->loadFromUrl('https://premiumcarsfl.com/listing-list-full/');

        /** @var HtmlNode $pagination */
        $lastPageChild = $dom->find('.pagination')[0]->countChildren() - 2;
        $pagination    = $dom->find('.pagination')[0]->getChildren()[$lastPageChild];


        $LAST_PAGE_NUM = $pagination->firstChild()->firstChild()->text;

        for ($page = 1; $page <= $LAST_PAGE_NUM; $page++) {
            $dom->loadFromUrl('https://premiumcarsfl.com/listing-list-full/page/' . $page);

            $articles = $dom->getElementsByClass('listing-title');

            /** @var HtmlNode $article */
            foreach ($articles as $article) {
                $carUrl = $article->firstChild()->tag->getAttribute('href')['value'];

                $domCar = new Dom();
                $domCar->loadFromUrl($carUrl, ['whitespaceTextNode' => false]);

                //определение первичной структуры
                $CSVcollect[$carNumber]['Condition']                              = 'Used';
                $CSVcollect[$carNumber]['google_product_category']                = '916';
                $CSVcollect[$carNumber]['store_code']                             = 'premium';
                $CSVcollect[$carNumber]['vehicle_fulfillment(option:store_code)'] = 'in_store:premium';
                $CSVcollect[$carNumber]['Brand']                                  = '';
                $CSVcollect[$carNumber]['Model']                                  = '';
                $CSVcollect[$carNumber]['Year']                                   = '';
                $CSVcollect[$carNumber]['Color']                                  = '';
                $CSVcollect[$carNumber]['Mileage']                                = '';
                $CSVcollect[$carNumber]['Price']                                  = '';
                $CSVcollect[$carNumber]['VIN']                                    = '';
                $CSVcollect[$carNumber]['image_link']                             = '';
                $CSVcollect[$carNumber]['link_template']                          = $carUrl . '?store=premium';

                $CSVcollect[$carNumber]['Price'] = $domCar->getElementsByClass('price-text')[0]->innerHTML;

                $CSVcollect[$carNumber]['image_link'] = $domCar->find('img.attachment-voiture-gallery-v2.size-voiture-gallery-v2')[1]?->tag?->getAttribute('src')['value'] ?? null;

                /** @var  HtmlNode $listParams */
                $listParams = $domCar->getElementsByClass('list')[0]->getChildren();
                /** @var  HtmlNode $listParam */
                foreach ($listParams as $listParam) {
                    // $listParam->firstChild()->innerhtml
                    $key = match ($listParam->firstChild()->innerhtml) {
                        'Make:'    => 'Brand',
                        'Model:'   => 'Model',
                        'Year:'    => 'Year',
                        'Color:'   => 'Color',
                        'Mileage:' => 'Mileage',
                        'VIN:'     => 'VIN',
                        default    => null,
                    };
                    if ($key) {
                        $CSVcollect[$carNumber][$key] = $listParam->getChildren()[1]->firstChild()->innerhtml;
                    }
                }
                $CSVcollect[$carNumber]['Mileage'] = $CSVcollect[$carNumber]['Mileage'] . ' miles';

                $carNumber++;
            }
            print_r("page {$page} load\n");
        }
        return $CSVcollect;
    }
}
