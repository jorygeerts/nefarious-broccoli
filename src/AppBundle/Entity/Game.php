<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\GameRepository")
 */
class Game
{
    const MAX_TRIES = 11;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="solution", type="string", length=255)
     */
    private $solution;

    /**
     * @var integer
     *
     * @ORM\Column(name="tries_left", type="integer")
     */
    private $triesLeft;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Guess", mappedBy="game", cascade={"persist"})
     */
    protected $guesses;

    public function __construct($solution)
    {
        $this->solution = $solution;
        $this->triesLeft = static::MAX_TRIES;
        $this->status = 'busy';
        $this->guesses = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set solution
     *
     * @param string $solution
     *
     * @return Game
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * Get solution
     *
     * @return string
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * Set triesLeft
     *
     * @param integer $triesLeft
     *
     * @return Game
     */
    public function setTriesLeft($triesLeft)
    {
        $this->triesLeft = $triesLeft;

        return $this;
    }

    /**
     * Get triesLeft
     *
     * @return integer
     */
    public function getTriesLeft()
    {
        return $this->triesLeft;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Game
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Add guess
     *
     * @param \AppBundle\Entity\Guess $guess
     *
     * @return Game
     */
    public function addGuess(\AppBundle\Entity\Guess $guess)
    {
        $this->guesses[] = $guess;

        return $this;
    }

    /**
     * Remove guess
     *
     * @param \AppBundle\Entity\Guess $guess
     */
    public function removeGuess(\AppBundle\Entity\Guess $guess)
    {
        $this->guesses->removeElement($guess);
    }

    /**
     * Get guesses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGuesses()
    {
        return $this->guesses;
    }

    /**
     * Guess a letter
     *
     * @param $letter
     * @throws \InvalidArgumentException If the provided letter is not valid.
     * @throws \DomainException If the current status of the game does not allow taking a guess.
     */
    public function guess($letter)
    {
        if (!preg_match('/[a-z]{1}/', $letter)) {
            throw new \InvalidArgumentException("Provided letter must be a-z, $letter given.");
        }

        if ($this->status != 'busy') {
            throw new \DomainException("It is now allowed to take a guess if the game is not busy.");
        }

        $isCorrect = strpos($this->solution, $letter) !== false;

        $guess = new Guess($this, $letter, $isCorrect);
        $this->addGuess($guess);

        if (!$isCorrect) {
            $this->triesLeft--;
        }

        if ($this->isSuccess()) {
            $this->status = 'success';
        }

        if ($this->triesLeft < 1 && $this->status == 'busy') {
            $this->status = 'fail';
        }
    }

    public function getRevealedText()
    {
        $revealedText = '';
        $letters = str_split($this->solution);

        $guessedLetters = array_map(
            function (Guess $guess) {
                return $guess->getLetter();
            },
            $this->guesses->toArray()
        );

        foreach ($letters as $letter) {
            if (in_array($letter, $guessedLetters)) {
                $revealedText .= $letter;
            } else {
                $revealedText .= '.';
            }
        }

        return $revealedText;
    }

    /**
     * Calculate if the game was completed
     */
    protected function isSuccess()
    {
        if (!$this->status == 'busy') {
            throw new \DomainException("Cannot check state after the game is complete");
        }

       return $this->solution == $this->getRevealedText();
    }
}
