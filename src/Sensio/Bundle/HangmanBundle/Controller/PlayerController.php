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

                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($player);
                $player->encodePassword($encoder);

                $em = $this->getDoctrine()->getManager();
                $em->persist($player);
                $em->flush();//commit

                return $this->redirect($this->generateUrl('hangman_game'));
            }
        }

        return $this->render(
            'SensioHangmanBundle:Player:signup.html.twig',
            array('form' => $form->createView())
        );
    }
}