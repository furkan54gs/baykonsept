<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OrderDetailController extends AbstractController
{
    /**
     * @Route("/order/detail", name="order_detail")
     */
    public function index()
    {
        return $this->render('order_detail/index.html.twig', [
            'controller_name' => 'OrderDetailController',
        ]);
    }
}
