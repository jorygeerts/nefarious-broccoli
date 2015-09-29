<?php
/**
 * Created by PhpStorm.
 * User: jgeerts
 * Date: 29-9-15
 * Time: 0:00
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Game;
use AppBundle\Entity\GameRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GameController extends Controller
{
    /**
     * @Route("/games", name="list_games")
     * @Method("GET")
     */
    public function listGamesAction()
    {
        /** @var GameRepository $repo */
        $repo = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle\Entity\Game');

        //  Generate JSON response.
        //  Could use JMS Serializer, but that seems overkill for our requirements.
        $responseData = array(
            'games' => array()
        );
        /** @var Game $game */
        foreach ($repo->findAll() as $game) {
            $responseData['games'][] = array(
                'id' => $game->getId(),
                'status' => $game->getStatus(),
                'details_uri' => $this->generateUrl('game_detail', array('id' => $game->getId()))
            );
        }

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/games", name="start_game")
     * @Method("POST")
     */
    public function startGameAction()
    {
        /** @var Game $game */
        $game = $this->container->get('game_creator')->createGame();
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($game);
        $em->flush();

        return new RedirectResponse("/games/".$game->getId(), Response::HTTP_CREATED);
    }

    /**
     * @Route("/games/{id}", name="game_detail")
     * @Method("GET")
     */
    public function gameDetailsAction($id)
    {
        /** @var GameRepository $repo */
        $repo = $this->getDoctrine()->getEntityManager()->getRepository('AppBundle\Entity\Game');

        /** @var Game $game */
        if (!$game = $repo->find($id)) {
            return new JsonResponse(array("message" => "No game with id $id."), 404);
        }

        $responseData = array(
            'word' => $game->getRevealedText(),
            'tries_left' => $game->getTriesLeft(),
            'status' => $game->getStatus(),
        );

        return new JsonResponse($responseData);
    }

    /**
     * @Route("/games/{id}", name="game_try_letter")
     * @Method("POST")
     */
    public function tryLetterAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();
        /** @var GameRepository $repo */
        $repo = $em->getRepository('AppBundle\Entity\Game');

        /** @var Game $game */
        if (!$game = $repo->find($id)) {
            return new JsonResponse(array("message" => "No game with id $id."), 404);
        }

        try {
            $game->guess($request->request->get('char'));
            $em->persist($game);
            $em->flush();
            return new RedirectResponse("/games/".$game->getId(), Response::HTTP_SEE_OTHER);
        } catch (\InvalidArgumentException $exc) {
            return new JsonResponse(array("message" => $exc->getMessage()), 400);
        } catch (\DomainException $exc) {
            return new JsonResponse(array("message" => $exc->getMessage()), 400);
        }
    }
}