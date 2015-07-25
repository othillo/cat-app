<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $client = new Client([
            'base_uri' => 'http://thecatapi.com',
            'timeout'  => 2.0,
        ]);

        $key      = $this->getParameter('cat-api.key');
        $response = $client->get('/api/images/get?format=xml&type=gif&results_per_page=3&api_key=' . $key);
        $xml      = simplexml_load_string($response->getBody());

        $cats     = $this->getCats();

        return $this->render('default/index.html.twig', [
            'xml'   => $xml,
            'cats' => $cats,
        ]);
    }

    private function getCats()
    {
        $mountManager = $this->get('oneup_flysystem.mount_manager');

        return $mountManager->listContents('public:///', true);
    }

    /**
     * @Route("/download/{id}", name="download")
     */
    public function downloadAction(Request $request, $id)
    {
        $client = new Client([
            'base_uri' => 'http://thecatapi.com',
            'timeout'  => 2.0,
        ]);

        $key      = $this->getParameter('cat-api.key');
        $response = $client->get('/api/images/get?format=xml&type=gif&image_id=' . $id . '&results_per_page=1&api_key=' . $key);

        $xml      = simplexml_load_string($response->getBody());
        $url      = (string) $xml->data->images->image->url;

        $response = $client->get($url);

        $filesystem = $this->get('oneup_flysystem.public_filesystem');
        $filesystem->put($id . '.gif', $response->getBody()->getContents());

        return new RedirectResponse('/');
    }

    /**
     * @Route("/delete/{file}", name="delete")
     */
    public function deleteAction(Request $request, $file)
    {
        $filesystem = $this->get('oneup_flysystem.public_filesystem');
        $filesystem->delete($file);

        return new RedirectResponse('/');
    }
}
