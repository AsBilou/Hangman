<?php

namespace Sensio\Bundle\HangmanBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\HangmanBundle\Game\GameContext;
use Sensio\Bundle\HangmanBundle\Game\WordList;

class GameController extends Controller
{
    private $gameContext;
    private $wordList;

    /**
     * This action handles the homepage of the Hangman game.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $context = $this->getGameContext();

        if (!$game = $context->loadGame()) {
            $size = $request->query->get('length', $this->container->getParameter('sensio_hangman.word_length'));
            $list = $this->getWordList();
            $word = $list->getRandomWord($size);
            $game = $context->newGame($word);
            $context->save($game);
        }

        return $this->render(
            'SensioHangmanBundle:Game:index.html.twig',
            array('game' => $game)
        );
    }

    /**
     * This action allows the player to try to guess a letter.
     *
     * @param string $letter The letter the user wants to try
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function letterAction($letter)
    {
        $context = $this->getGameContext();

        if (!$game = $context->loadGame()) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        $game->tryLetter($letter);
        $context->save($game);

        if ($game->isWon()) {
            return $this->redirect($this->generateUrl('game_won'));
        }

        if ($game->isHanged()) {
            return $this->redirect($this->generateUrl('game_hanged'));
        }

        return $this->redirect($this->generateUrl('hangman_game'));
    }

    /**
     * This action allows the player to try to guess the word.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function wordAction(Request $request)
    {
        $context = $this->getGameContext();

        if (!$game = $context->loadGame()) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        $game->tryWord($request->request->get('word'));
        $context->save($game);

        if ($game->isWon()) {
            return $this->redirect($this->generateUrl('game_won'));
        }

        return $this->redirect($this->generateUrl('game_hanged'));
    }

    /**
     * This action displays the hanged page.
     *
     * @return Symfony\Component\HttpFoundation\Response
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function hangedAction()
    {
        $context = $this->getGameContext();

        if (!$game = $context->loadGame()) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        if (!$game->isHanged()) {
            throw $this->createNotFoundException('User is not yet hanged.');
        }

        return $this->render(
            'SensioHangmanBundle:Game:hanged.html.twig',
            array('word' => $game->getWord())
        );
    }

    /**
     * This action displays the winning page.
     *
     * @return Symfony\Component\HttpFoundation\Response
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function wonAction()
    {
        $context = $this->getGameContext();

        if (!$game = $context->loadGame()) {
            throw $this->createNotFoundException('Unable to load the previous game context.');
        }

        if (!$game->isWon()) {
            throw $this->createNotFoundException('Game is not yet won.');
        }

        return $this->render(
            'SensioHangmanBundle:Game:won.html.twig',
            array('word' => $game->getWord())
        );
    }

    /**
     * This action allows the user to reset the hangman game.
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function resetAction()
    {
        $context = $this->getGameContext();
        $context->reset();

        return $this->redirect($this->generateUrl('hangman_game'));
    }

    /**
     * Returns a GameContext instance.
     *
     * @return Sensio\Bundle\HangmanBundle\Game\GameContext
     */
    private function getGameContext()
    {
        if (null === $this->gameContext) {
            $this->gameContext = new GameContext($this->get('session'));
        }

        return $this->gameContext;
    }

    /**
     * Returns a WordList instance.
     *
     * @return Sensio\Bundle\HangmanBundle\Game\WordList
     */
    private function getWordList()
    {
        if (null === $this->wordList) {
            $dictionaries = $this->container->getParameter('sensio_hangman.dictionaries');
            $this->wordList = new WordList($dictionaries);
            $this->wordList->loadDictionaries();            
        }

        return $this->wordList;
    }
}
