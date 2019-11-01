<?php

namespace App\Controller;

use App\Entity\OrderDetail;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrdersController extends AbstractController
{
    /**
     * @Route("/orders", name="orders_index")
     */
    public function index(OrdersRepository $ordersRepository):Response
    {
        $user = $this->getUser();
        $userid = $user->getid();
        return $this->render('orders/index.html.twig', [
            'orders' => $ordersRepository->findBy(['userid'=>$userid])]);
    }

    /**
     * @Route("/orders/new", name="orders_new", methods="GET|POST")
     */
    public function new(Request $request,ShopcartRepository $shopcartRepository,OrdersRepository $ordersRepository): Response
    {
        $orders = new Orders();
        $form = $this->createForm(OrdersType::class, $orders);
        $form->handleRequest($request);

        $user = $this->getUser();
        $userid = $user->getid();
        $total=$shopcartRepository->getUserShopCartTotal($userid);

        $submittedToken=$request->request->get('token');
        if($this->isCsrfTokenValid('form-order',$submittedToken)) {
            if ($form->isSubmitted()) {
                $em = $this->getDoctrine()->getManager();

                $orders->setUserid($userid);
                $orders->setAmount($total);
                $orders->setStatus("New");
                $em->persist($orders);
                $em->flush();
                $orderid=$orders->getId();
                $shopcart=$shopcartRepository->getUserShopCart($userid);

                foreach ($shopcart as $item){
                    $orderdetail=new OrderDetail();
                    $orderdetail->setOrderid($orderid);
                    $orderdetail->setUserid($user->getid());
                    $orderdetail->setProductid($item["productid"]);
                    $orderdetail->setPrice($item["sprice"]);
                    $orderdetail->setQuantity($item["quantity"]);
                    $orderdetail->setAmount($item["total"]);
                    $orderdetail->setName($item["title"]);
                    $orderdetail->setStatus("Ordered");

                    $em->persist($orderdetail);
                    $em->flush();
                }
                $em = $this->getDoctrine()->getManager();
                $query=$em->createQuery('
                DELETE FROM App\Entity\Shopcart s WHERE s.userid=:userid
                ')
                ->setParameter('userid',$userid);
                $query->execute();

                $this->addFlash('succes', 'Siparişiniz sisteme kaydedilmiştir. Teşekkür Ederiz.');
                return $this->redirectToRoute('orders_index');
            }
        }
        return $this->render('orders/new.html.twig', [
            'order' => $orders,
            'total' => $total,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/orders/{id}", name="orders_show", methods="GET")
     */
    public function show(Orders $orders, OrderDetailRepository $orderDetailRepository):Response
    {

        $orderid = $orders->getid();

        $orderdetail=$orderDetailRepository->findBy(
            ['orderid' => $orderid]
        );
        return $this->render('orders/show.html.twig', [
            'order' => $orders,
            'orderdetail' => $orderdetail,
        ]);
    }


    /**
     * @Route("/orders/delete/{id}", name="orders_delete")
     */
    public function delete(Orders $orders): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($orders);
        $em->flush();

        return $this->redirectToRoute('orders_index');


    }



}
