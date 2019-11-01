<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category", name="admin_category")
     */
    public function index()
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();
        return $this->render('admin/category/index.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/category/new", name="admin_category_new", methods="GET|POST")
     */
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            return $this->redirectToRoute('admin_category');

        }
        $pcatname = $categoryRepository->findAll();
        return $this->render('admin/category/create_form.html.twig', [
            'form' => $form->createView(),
            'pcatname' => $pcatname,
        ]);
    }

    /**
     * @Route("/admin/category/edit/{id}", name="admin_category_edit", methods="GET|POST")
     */
    public function edit(Request $request, Category $category,CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_category');
        }
        $pcatname = $categoryRepository->findAll();
        $fcatname = $categoryRepository->findBy(
            ['id' => $category->getParentId()]
        );


        return $this->render('admin/category/edit_form.html.twig', [
            'category' => $category,
            'pcatname' => $pcatname,
            'fcatname' => $fcatname,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/{id}", name="admin_category_delete")
     */
    public function delete(Category $category): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('admin_category');


    }
}