<?php

namespace App\Controller\Admin;

use App\Repository\SettingRepository;
use App\Entity\Setting;
use App\Form\SettingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends AbstractController
{
    /**
     * @Route("/admin/setting", name="admin_setting_index", methods="GET")
     */
    public function index(SettingRepository $settingRepository):Response
    {
        $setdata=$settingRepository->findAll();

        if(!$setdata)
        {
            echo "Veritabanı boş";
            $setting=new Setting();
            $em=$this->getDoctrine()->getManager();
            $setting->setTitle("Site");
            $em->persist($setting);
            $em->flush();
            $setdata=$settingRepository->findAll();

        }

        return $this->redirectToRoute('admin_setting_edit',['id'=>$setdata[0]->getId()]);


    }

    /**
     * @Route("/admin/setting/edit/{id}", name="admin_setting_edit", methods="GET|POST")
     */
    public function edit(Request $request, Setting $setting): Response
    {
        $form = $this->createForm(SettingType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('admin_setting_index');
        }

        return $this->render('admin/setting/edit_form.html.twig', [
            'setting' => $setting,
            'form' => $form->createView(),
        ]);
    }

}
