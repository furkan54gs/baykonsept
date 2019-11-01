<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Messages;
use App\Repository\Admin\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends AbstractController
{
    /**
     * @Route("/admin/message", name="admin_message")
     */
    public function index()
    {
        $messages = $this->getDoctrine()
            ->getRepository(Messages::class)
            ->findAll();
        return $this->render('admin/message/index.html.twig', [
            'message' => $messages,
        ]);
    }

    /**
     * @Route("/admin/message/{id}/", name="admin_message_show", methods="GET")
     */
    public function show($id, Messages $message,MessagesRepository $messagesRepository): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sql = 'UPDATE messages SET status="Okundu" WHERE id=:id';
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('id', $id);
        $statement->execute();


        $data = $this->getDoctrine()
            ->getRepository(Messages::class)
            ->findOneBy(
            ['id' => $id]
        );
        $dateT=$data->getCreatedAt();


        return $this->render('admin/message/show.html.twig', [
            'message' => $message,
            'dateT' => $dateT,
        ]);
    }

    /**
     * @Route("/admin/message/{id}/update", name="admin_message_update", methods="GET|POST")
     */
    public function update($id, Messages $message, Request $request): Response
    {

        $em = $this->getDoctrine()->getManager();
        $sql = "UPDATE messages SET comment=:comment WHERE id=:id";
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('comment', $request->request->get('comment'));
        $statement->bindValue('id', $id);
        $statement->execute();

        $data = $this->getDoctrine()
            ->getRepository(Messages::class)
            ->findOneBy(
                ['id' => $id]
            );
        $dateT=$data->getCreatedAt();
        $this->addFlash('succes', 'Yorum Eklendi');
        return $this->render('admin/message/show.html.twig', [
            'message' => $message,
            'id' => $id,
            'dateT' => $dateT,
        ]);
    }
    /**
     * @Route("/admin/message/{id}", name="admin_message_delete")
     */
    public function delete(Messages $message): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($message);
        $em->flush();

        return $this->redirectToRoute('admin_message_show');


    }



}
