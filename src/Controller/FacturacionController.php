<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FacturacionController extends AbstractController
{
    /**
     * @Route("/facturacion", name="facturacion")
     */
    public function index()
    {
        return $this->render('facturacion/index.html.twig', [
            'controller_name' => 'FacturacionController',
        ]);
    }
}
