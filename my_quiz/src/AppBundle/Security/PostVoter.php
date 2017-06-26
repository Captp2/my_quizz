<?php
namespace AppBundle\Security;

use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class PostVoter extends Voter
{
	const QUIZZ = 'quizz';
	const QUEST = 'question';
	const ANSW = 'answer';

	private $decisionManager;
	private $reflection;

	public function __construct(AccessDecisionManagerInterface $decisionManager)
	{
		$this->decisionManager = $decisionManager;
		$this->reflection = new \ReflectionClass('AppBundle\Security\PostVoter');
	}

	protected function supports($attribute, $subject){
		if(!in_array($attribute, array(self::QUIZZ, self::QUEST, self::ANSW))){
			return false;
		}
		return true;
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token){
		$user = $token->getUser();

		//Check wheter the user is logged in
		if(!$user instanceof User){
			return false;
		}

		//Never say no to Panda
		if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
			return true;
		}
		if($attribute == self::QUIZZ){
			return $this->checkQuizz($subject, $user);
		}
		if($attribute == self::QUEST){
			return $this->checkQuestion($subject, $user);
		}
		if($attribute == self::ANSW){
			return $this->checkAnswer($subject, $user);
		}
		return false;
	}

	private function checkQuizz($quizz, $user){
		if($quizz->getAuthor() == $user->getUsername()){
			return true;
		}
		return false;
	}

	private function checkQuestion($question, $user){
		$quizz = $question->getQuizz();
		if($quizz->getAuthor() == $user->getUsername()){
			return true;
		}
		return false;
	}

	private function checkAnswer($answer, $user){
		$question = $answer->getQuestion();
		$quizz = $question->getQuizz();
		if($quizz->getAuthor() == $user->getUsername()){
			return true;
		}
		return false;
	}
}