<?php
/**
 * Created by PhpStorm.
 * User: jgeerts
 * Date: 29-9-15
 * Time: 0:02
 */

namespace AppBundle\Services;

use AppBundle\Entity\Game;

class GameCreation
{
    private $wordlistFile;

    /**
     * @param string $wordlistFile
     */
    function __construct($wordlistFile)
    {
        $this->wordlistFile = $wordlistFile;
    }

    /**
     * Create a game using a random word from our word file.
     *
     * @return Game
     */
    public function createGame()
    {
        //  Read word list whenever it is required.
        //  If, in general, this method starts being used more then once per request,
        //  refactor to only read word file the first time.
        $words = file($this->wordlistFile);
        $word = trim($words[array_rand($words)]);

        return new Game($word);
    }
}