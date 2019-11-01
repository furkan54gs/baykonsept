<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\User;
use App\Form\MessageType;
use App\Form\UserType;
use App\Repository\Admin\ImageRepository;
use App\Repository\ProductRepository;
use App\Repository\SettingRepository;
use App\Repository\CategoryRepository;
use App\Repository\ShopcartRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function index(SettingRepository $settingRepository, ShopcartRepository $shopcartRepository, ProductRepository $productRepository)
    {
        $data = $settingRepository->findAll();
        $cats = $this->categorytree();
        $products = $productRepository->findBy(
            ['status' => 'Aktif']
        );
        $firsat = $productRepository->findBy(
            ['discount' => '1']
        );

        //ana sayfa sepet mesajı
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if ($user != null) {
            $userid = $user->getid();
            $shoptotal = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shoptotal = 0;
        }


        $sql = "SELECT * FROM product WHERE amount=0 and sprice=0 ORDER BY ID DESC"; //WHERE status='Aktif'
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $sliders = $statement->fetchAll();

        // print_r($cats);
        // die();
        $cats[0] = '<ul id="menu-v">';

        return $this->render('home/index.html.twig', [
            'data' => $data,
            'cats' => $cats,
            'products' => $products,
            'firsat' => $firsat,
            'sliders' => $sliders,
            'shoptotal' => $shoptotal,
        ]);
    }

    /**
     * @Route("/hakkimizda", name="hakkimizda")
     */
    public function hakkimizda(SettingRepository $settingRepository)
    {
        $data = $settingRepository->findAll();
        return $this->render('home/hakkimizda.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/referanslar", name="referanslar")
     */
    public function referanslar(SettingRepository $settingRepository)
    {
        $data = $settingRepository->findAll();
        return $this->render('home/referanslar.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/iletisim", name="iletisim" ,  methods="GET|POST")
     */
    public function iletisim(SettingRepository $settingRepository, Request $request)
    {
        $message = new Messages();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);


        $submittedToken = $request->request->get('token');
        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('form-message', $submittedToken)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
                $this->addFlash('succes', 'Mesajınız Gönderilmiştir');
                return $this->redirectToRoute('iletisim');
            }
        }
        $data = $settingRepository->findAll();
        return $this->render('home/iletisim.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    public function categorytree($parent = 0, $user_tree_array = '')
    {

        if (!is_array($user_tree_array))
            $user_tree_array = array();


        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM category WHERE status='Aktif' AND parent_id=" . $parent;
        $statement = $em->getConnection()->prepare($sql);
        //   $statement->bindValue('parentid',$parent);
        $statement->execute();
        $result = $statement->fetchAll();

        if (count($result) > 0) {
            $user_tree_array[] = "<ul>";
            foreach ($result as $row) {
                $user_tree_array[] = "<li> <a href='/category/" . $row['id'] . "'>" . $row['title'] . "</a>";
                $user_tree_array = $this->categorytree($row['id'], $user_tree_array);
            }

            $user_tree_array[] = "</li></ul>";

        }
        return $user_tree_array;
    }

    /**
     * @Route("category/{catid}", name="category_poroducts", methods="GET")
     */
    public function CategoryProducts($catid, CategoryRepository $categoryRepository, ProductRepository $productRepository, ShopcartRepository $shopcartRepository): Response
    {
        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $data = $categoryRepository->findBy(
            ['id' => $catid]
        );
        $firsat = $productRepository->findBy(
            ['discount' => '1']
        );


        $em = $this->getDoctrine()->getManager();

        if ($catid == 1 || $catid == 3 || $catid == 4 || $catid == 5 || $catid == 6 || $catid == 17) {
            $sql = 'SELECT * FROM product WHERE status="Aktif" AND ancestor_id=:catid';
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('catid', $catid);
            $statement->execute();
            $products = $statement->fetchAll();
        } else {
            $sql = 'SELECT * FROM product WHERE status="Aktif" AND category_id=:catid';
            $statement = $em->getConnection()->prepare($sql);
            $statement->bindValue('catid', $catid);
            $statement->execute();
            $products = $statement->fetchAll();
        }

        $user = $this->getUser();
        if ($user != null) {
            $userid = $user->getid();
            $shoptotal = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shoptotal = 0;
        }
        return $this->render('home/products.html.twig', [
            'data' => $data,
            'products' => $products,
            'cats' => $cats,
            'firsat' => $firsat,
            'shoptotal' => $shoptotal,
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_detail",methods="GET")
     */
    public function ProductDetail($id, ProductRepository $productRepository, ShopcartRepository $shopcartRepository, ImageRepository $imageRepository)
    {
        $data = $productRepository->findBy(
            ['id' => $id]
        );
        $galery = $imageRepository->findBy(
            ['product_id' => $id]
        );
        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        $firsat = $productRepository->findBy(
            ['discount' => '1']
        );

        $user = $this->getUser();
        if ($user != null) {
            $userid = $user->getid();
            $shoptotal = $shopcartRepository->getUserShopCartCount($userid);
        } else {
            $shoptotal = 0;
        }
        return $this->render('home/product_detail.html.twig', [
            'data' => $data,
            'galery' => $galery,
            'cats' => $cats,
            'firsat' => $firsat,
            'shoptotal' => $shoptotal,
        ]);
    }

    /**
     * @Route("/register", name="register" ,  methods="GET|POST")
     */
    public function register(UserRepository $userRepository, Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        $submittedToken = $request->request->get('token');
        if ($form->isSubmitted()) {
            if ($this->isCsrfTokenValid('user-form', $submittedToken)) {

                $emaildata = $userRepository->findBy(
                    ['email' => $user->getEmail()]
                );

                if ($emaildata == null) {
                    $em = $this->getDoctrine()->getManager();
                    $user->setRoles("ROLE_USER");
                    $user->setStatus("Pasif");
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('succes', 'Üye kaydınız tamamlanmıştır. Giriş yapabilirsiniz.');
                    return $this->redirectToRoute('app_login');
                } else {
                    $this->addFlash('error', 'E-mail adresi zaten kullanılmaktadır.');
                }
            }
        }

        return $this->render('home/register.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


}
