<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/search", name="search")
     */
    public function index(): Response
    {
        $hotels = $this->getHotels();
        $traveltodo = $this->getTraveltodo();
        $voyage2000 = $this->getVoyage200();
        $me = [];

        foreach ($hotels as $hotel) {

            foreach ($hotel["hotelsId"] as $key => $value) {
                global $v;
                $v = $value;
                $hotel["hotelsPrice"][$key] = array_column(array_filter($$key, function ($val) {                   
                    $hotel["agence"] = "zd";
                    return $val["hotelId"] == $GLOBALS['v'];
                }), null)[0];
            }
            if (count($hotel['hotelsPrice']) > 1) {
                usort($hotel['hotelsPrice'], function ($item1, $item2) {
                    return $item1['minRate'] <=> $item2['minRate'];
                });
            }
            $me[] = $hotel;
        }

        return $this->render('search/index.html.twig', [
            'hotels' => $me,
        ]);
    }


    private function getHotels(): array
    {
        return  $hotels = [
            0 => [
                "name" => "El Mouradi El Menzah",
                "rating" => 4,
                "address" => "Zone Touristique Yasmine Hammamet",
                "image" => "https://www.elmouradi.com/cr27.fwk/images/hotels/Hotel-132-20210429-032423.JPG",
                "city" => "Hammamet",
                "description" => "L’hôtel offre des chambres et suites d’un grand confort avec salle de bains et toilettes, terrasse, téléphone direct, TV satellite, climatisation indi",
                "hotelsId" => [
                    "traveltodo" => 3820725,
                    "voyage2000" => 9884725,
                ],
                "hotelsPrice" => [],
            ],
            1 => [
                "name" => "El Mouradi Hammamet",
                "rating" => 4,
                "address" => "Zone touristique Yasmine Hammamet ",
                "image" => "https://www.elmouradi.com/cr27.fwk/images/hotels/Hotel-100-20141122-105058.jpg",
                "city" => "Hammamet",
                "description" => "Situé au bord d'une large plage de sable fin, au coeur de la nouvelle station touristique et balnaire au Sud de Hammamet...",
                "hotelsId" => [
                    "traveltodo" => 3820706,
                    "voyage2000" => 9884706,
                ],
                "hotelsPrice" => [],
            ],
            2 => [
                "name" => "Medina Solaria & Thalasso",
                "rating" => 5,
                "address" => "Hôtel Medina Solaria & Thalasso , Rue de la Médina – Yasmine Hammamet  8057 – Tunisie",
                "image" => "https://fwk.resabo.com/cr.fwk/images/hotels/Hotel-1582-20210927-100012.jpg",
                "city" => "Hammamet",
                "description" =>  "hotel Solaria & Thalasso HammametHôtel Solaria & Thalasso est un complexe Hôtelier de catégorie 5 étoiles. Un établissement prestigieux ,haut standing",
                "hotelsId" => [
                    "traveltodo" => 3820994,
                    "voyage2000" => 24461077,
                ],
                "hotelsPrice" => [],
            ],
        ];
    }





























    private function getTraveltodo()
    {
        $response = $this->client->request('GET', 'https://www.traveltodo.com/hotels/data/RQ.cfm?cityId=3&checkin=2021-10-13&checkout=2021-10-20&pax=2', [
            'headers' => [
                'method' => 'GET',
                'scheme' => 'https',
                'accept' => 'application/json',
                'x-requested-with' => 'XMLHttpRequest'
            ],
        ]);
        $content = $response->toArray();
        $hotels = [];
        for ($i = 1; $i <= ($content['total'] / $content['perPage']); $i++) {
            $j = $i + 1;
            $response2 = $this->client->request('GET', "https://www.traveltodo.com/hotels/data/RQ-next.cfm?searchCode={$content['searchCode']}&page=$j&minP=1&maxP=9999999&sortBy=score:asc&_=1633792740401", [
                'headers' => [
                    'method' => 'GET',
                    'scheme' => 'https',
                    'accept' => 'application/json',
                    'x-requested-with' => 'XMLHttpRequest'
                ],
            ]);
            $content2 = $response2->toArray();
            $hotels[] = $content2['hotels'];
        }
        for ($i = 0; $i < count($hotels); $i++) {
            for ($k = 0; $k < count($hotels[$i]); $k++) {
                $content['hotels'][] = $hotels[$i][$k];
            }
        }

        // add agence column
        $hotelss= [];
        foreach($content['hotels'] as $hotel){
           $m = $hotel;
           $m["agence"] = "traveltodo";
           $m["logo"] = "https://www.traveltodo.com/assets/img/favicon.ico";
           $hotelss[]= $m;
        }

        return $hotelss ;
    }

    private function getVoyage200(): array
    {
        $response = $this->client->request('GET', 'https://api.voyages2000.com.tn/hotels/availability?cityId=3&checkin=2021-10-13&checkout=2021-10-20&pax=2');
        $content = $response->toArray();

        // add agence column
        $hotelss= [];
        foreach($content['hotels'] as $hotel){
           $m = $hotel;
           $m["agence"] = "voyage2000";
           $m["logo"] = "https://www.voyages2000.com.tn/assets/img/favicon.ico";
           $hotelss[]= $m;
        }

        return $hotelss ;
    }
}
