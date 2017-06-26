<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
{

    /**
     * @ORM\OneToMany(targetEntity="Quizz", mappedBy="category")
     */
    private $quizzs;

    public function __construct()
    {
        $this->quizzs = new ArrayCollection();
    }
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add quizzs
     *
     * @param \AppBundle\Entity\Quizz $quizzs
     * @return Category
     */
    public function addQuizz(\AppBundle\Entity\Quizz $quizzs)
    {
        $this->quizzs[] = $quizzs;

        return $this;
    }

    /**
     * Remove quizzs
     *
     * @param \AppBundle\Entity\Quizz $quizzs
     */
    public function removeQuizz(\AppBundle\Entity\Quizz $quizzs)
    {
        $this->quizzs->removeElement($quizzs);
    }

    /**
     * Get quizzs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuizzs()
    {
        return $this->quizzs;
    }
}
