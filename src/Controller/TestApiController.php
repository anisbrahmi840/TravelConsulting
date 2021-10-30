<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestApiController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    /**
     * @Route("/traveltodo", name="traveltodo")
     */
    public function traveltodo(): Response
    {

        $response = $this->client->request('GET', 'https://www.traveltodo.com/hotels/data/RQ.cfm?cityId=3&checkin=2021-10-13&checkout=2021-10-15&pax=2', [
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

        dd($content['hotels']);
    }

    /**
     * @Route("/voyage2000", name="voyage2000")
     */
    public function voyage200(): Response
    {

        $response = $this->client->request('GET', 'https://api.voyages2000.com.tn/hotels/availability?cityId=3&checkin=2021-10-10&checkout=2021-10-11&pax=2');
        $content = $response->toArray();
        dd($content);

        return $this->render('search/index.html.twig', [
            'hotels' => $content['hotels'],
        ]);
    }
}
