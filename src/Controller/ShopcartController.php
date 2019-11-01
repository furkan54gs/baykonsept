<?php

namespace App\Controller;

use App\Entity\Shopcart;
use App\Form\ShopcartType;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ShopcartController extends AbstractController
{
    /**
     * @Route("/shopcart", name="shopcart_index", methods="GET")
     */
    public function index(ShopcartRepository $shopcartRepository)
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();
        $sql= "SELECT p.title,p.sprice,p.image,p.sprice * s.quantity AS 'total', s.*
        FROM shopcart s, product p
        WHERE s.productid = p.id and userid= :userid";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('userid',$user->getid());
        $statement->execute();
        $shopcart = $statement->fetchAll();

        $total=0;
        return $this->render('shopcart/index.html.twig', [
            'shopcarts' => $shopcart,
            'total' => $total,
        ]);
    }
    /**
     * @Route("/shopcart/new", name="shopcart_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $shopcart = new Shopcart();
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);

        $submittedToken=$request->request->get('token');

        if($this->isCsrfTokenValid('additem',$submittedToken)){

            if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $shopcart->setUserid($user->getid());

            $em->persist($shopcart);
            $em->flush();

            }
        }

        return $this->redirectToRoute('shopcart_index');
    }

    /**
     * @Route("/shopcart/{id}", name="shopcart_delete")
     */
    public function delete(Shopcart $shopcart): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($shopcart);
        $em->flush();

        return $this->redirectToRoute('shopcart_index');


    }
}
