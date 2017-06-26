<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\ManyToOne(targetEntity="Quizz", inversedBy="questions")
     * @ORM\JoinColumn(name="quizz_id", referencedColumnName="id")
     */
    private $quizz;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
     */
    private $answers;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validated = false;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

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
     * Set value
     *
     * @param string $value
     * @return Question
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set quizz
     *
     * @param \AppBundle\Entity\Quizz $quizz
     * @return Question
     */
    public function setQuizz(\AppBundle\Entity\Quizz $quizz = null)
    {
        $this->quizz = $quizz;

        return $this;
    }

    /**
     * Get quizz
     *
     * @return \AppBundle\Entity\Quizz 
     */
    public function getQuizz()
    {
        return $this->quizz;
    }

    /**
     * Add answers
     *
     * @param \AppBundle\Entity\Answer $answers
     * @return Question
     */
    public function addAnswer(\AppBundle\Entity\Answer $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers
     *
     * @param \AppBundle\Entity\Answer $answers
     */
    public function removeAnswer(\AppBundle\Entity\Answer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set validated
     *
     * @param boolean $validated
     * @return Question
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;

        return $this;
    }

    /**
     * Get validated
     *
     * @return boolean 
     */
    public function getValidated()
    {
        return $this->validated;
    }

    public function checkIfValid(){
        if(count($this->getAnswers()) < 4){
            $this->setValidated(false);
            return false;
        }
        foreach($this->getAnswers() as $answer){
            if($answer->getValid()){
                $this->setValidated(true);
                return true;
            }
        }
        $this->setValidated(false);
        return false;
    }

}
