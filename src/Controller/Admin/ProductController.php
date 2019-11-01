<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/admin/product", name="admin_product")
     */
    public function index(CategoryRepository $categoryRepository)
    {

        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->findAll();
        /*
                $em = $this->getDoctrine()->getManager();
                  foreach($product as $event)
                   {
                       $sql = "SELECT title FROM category WHERE id=" . $event->getAncestorId();
                       $statement = $em->getConnection()->prepare($sql);
                       $statement->execute();
                       $result[] = $statement->fetchAll();
                       //title çağrıldı ama result indexte yazdırılamadı
                   }
        */

        return $this->render('admin/product/index.html.twig', [
            'product' => $product,

        ]);
    }

    /**
     * @Route("/admin/product/new", name="admin_product_new", methods="GET|POST")
     */
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $catlist = $categoryRepository->findAll();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute('admin_product');

        }
        return $this->render('admin/product/create_form.html.twig', [
            'catlist' => $catlist,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/product/edit/{id}", name="admin_product_edit", methods="GET|POST")
     */
    public function edit(Request $request, CategoryRepository $categoryRepository, Product $product): Response
    {
        $catlist = $categoryRepository->findAll();
        $catname = $categoryRepository->findBy(
            ['id' => $product->getCategoryId()]
        );

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('succes', 'Kayıt Guncelleme Basarili');
            return $this->redirectToRoute('admin_product_edit', ['id' => $product->getId()]);
        }
        return $this->render('admin/product/edit_form.html.twig', [
            'product' => $product,
            'catlist' => $catlist,
            'catname' => $catname,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/product/{id}", name="admin_product_delete")
     */
    public function delete(Product $product): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('admin_product');
    }

    /**
     * @Route("/admin/product/iedit/{id}", name="admin_product_iedit", methods="GET|POST")
     */
    public function iedit(Request $request, $id, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_product');
        }
        return $this->render('admin/product/iedit_form.html.twig', [
            'product' => $product,
            'id' => $id,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/product/iupdate/{id}", name="admin_product_iupdate", methods="POST")
     */
    public function iupdate(Request $request, $id, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $file = $request->files->get('imagename');
        $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

        // Move the file to the directory where brochures are stored
        try {
            $file->move(
                $this->getParameter('images_directory'),
                $fileName
            );
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }
        $product->setImage($fileName);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('admin_product_iedit', ['id' => $product->getId()]);
    }

    /**
     * @Route("/admin/product/iedit/{id}/", name="admin_product_idel", methods="GET")
     */
    public function idel($id, Request $request, Product $product): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sql = "UPDATE product SET image=null WHERE id=" . $id;
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();

        return $this->redirectToRoute('admin_product_iedit', ['id' => $product->getId()]);


    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

}
