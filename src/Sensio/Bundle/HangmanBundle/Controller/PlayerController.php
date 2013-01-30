<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\HangmanBundle\Form\PlayerType;

class PlayerController extends Controller
{
    public function signupAction(Request $request)
    {
        $form = $this->createForm(new PlayerType());

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $player = $form->getData();

                // get the encoder and encode the password
                // ...

                $em = $this->getDoctrine()->getManager();
                $em->persist($player);
                $em->flush();

                return $this->redirect($this->generateUrl('hangman_game'));
            }
        }

        return $this->render(
            'SensioHangmanBundle:Player:signup.html.twig',
            array('form' => $form->createView())
        );
    }
}