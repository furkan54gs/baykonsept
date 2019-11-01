<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserPanelController extends AbstractController
{
    /**
     * @Route("/userpanel", name="user_panel")
     */
    public function index()
    {
        return $this->render('user_panel/show.html.twig');
    }

    /**
     * @Route("/userpanel/edit", name="user_panel_edit")
     */
    public function edit(Request $request)
    {
        $usersession = $this->getUser();
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($usersession->getid());

        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('token');
            if ($this->isCsrfTokenValid('user-form', $submittedToken)) {

                $user->setName($request->request->get("name"));
                $user->setPassword($request->request->get("password"));
                $user->setAddress($request->request->get("address"));
                $user->setCity($request->request->get("city"));
                $user->setPhone($request->request->get("phone"));
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('succes', 'Bilgileriniz değiştirilmiştir.');

                return $this->redirectToRoute('user_panel_show');

            }
        }
        return $this->render('user_panel/edit.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/userpanel/show", name="user_panel_show")
     */
    public function show()
    {
        return $this->render('user_panel/show.html.twig');
    }

}
