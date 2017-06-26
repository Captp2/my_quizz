<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Quizz;
use AppBundle\Entity\Category;
use AppBundle\Entity\Question;
use AppBundle\Entity\Answer;
use AppBundle\Form\QuizzType;
use AppBundle\Form\QuestionType;
use AppBundle\Form\AnswerType;
use AppBundle\Repository\QuizzRepository;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
    	$repository = $this->getDoctrine()->getRepository('AppBundle:Quizz');
    	$quizz = $repository->findAll();
    	return $this->render('/default/index.html.twig', array('quizzs' => $quizz));
    }

    /**
     * @Route("/validatedQuizz", name="quizz_success")
     */
    public function quizzValidated(Request $request){
    	return $this->render('/default/quizz_success.html.twig');
    }

    /**
     * @Route("/seeQuizz/{quizzId}", name="see_quizz")
     */
    public function seeQuizz($quizzId){
    	$repository = $this->getDoctrine()->getRepository('AppBundle:Quizz');
    	$quizz = $repository->findById($quizzId);

    	return $this->render('default/see_quizz.html.twig', array('quizz' => $quizz));
    }

    /**
     * @Route("/createquizz", name="create_quizz")
     */
    public function createQuizz(Request $request){
    	$quizz = new Quizz();

        $form = $this->createFormBuilder($quizz)
        ->add('name', TextType::class)
        ->add('category', EntityType::class, array(
            'class' => 'AppBundle:Category',
            'choice_label' => 'name'
            ))
        ->add('description', TextareaType::class)
        ->add('save', SubmitType::class, array('label' => 'Create your Quizz.'))
        ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
            $quizz = $form->getData();
            $quizz->setAuthor($author);
            $em = $this->getDoctrine()->getManager();
            $em->persist($quizz);
            $em->flush();

            return $this->redirectToRoute('quizz_success');
        }
        return $this->render('default/create_quizz.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/seemyquizz", name="see_my_quizz")
     */
    public function seeMyQuizz(Request $request){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Quizz');
        $quizzs = $repository->findByAuthor($this->get('security.token_storage')->getToken()->getUser()->getUsername());
        return $this->render('default/see_my_quizz.html.twig', array('quizzs' => $quizzs));
    }

    /**
     * @Route("/editquizz/{quizzId}", name="edit_quizz")
     */
    public function editQuizz(Request $request, $quizzId){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Quizz');
        $quizz = $repository->find($quizzId);
        if (!$quizz) {
            throw $this->createNotFoundException(
                'No quizz found for id '.$quizzId
                );
        }
        $question = new Question();
        $formQuizz = $this->createForm(QuizzType::class, $quizz);
        $formQuestion = $this->createForm(QuestionType::class, $question);
        $question->setQuizz($quizz);
        $this->denyAccessUnlessGranted('quizz', $quizz);

        $formQuestion->handleRequest($request);
        $formQuizz->handleRequest($request);
        if ($formQuizz->isSubmitted() && $formQuizz->isValid()) {
            $quizz = $formQuizz->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($quizz);
            $em->flush();

            return $this->redirectToRoute('quizz_success');
        }

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
            $question = $formQuestion->getData();
            $question->setQuizz($quizz);
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('quizz_success');
        }

        return $this->render('default/edit_quizz.html.twig', array('quizz' => $quizz, 
            'formQuizz' => $formQuizz->createView(), 
            'formQuestion' => $formQuestion->createView()));
    }

    /**
     * @Route("/editquestion/{questionId}", name="edit_question")
     */
    public function editQuestion(Request $request, $questionId){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Question');
        $question = $repository->find($questionId);
        if (!$question) {
            throw $this->createNotFoundException(
                'No question found for id '.$questionId
                );
        }
        $formQuestion = $this->createForm(QuestionType::class, $question);
        $answer = new Answer();
        $formAnswer = $this->createForm(AnswerType::class, $answer);
        $answer->setQuestion($question);
        $this->denyAccessUnlessGranted('question', $question);

        $formQuestion->handleRequest($request);
        $formAnswer->handleRequest($request);

        if ($formQuestion->isSubmitted() && $formQuestion->isValid()) {
            $question = $formQuestion->getData();
            $question->setValidated($question->checkIfValid());
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('quizz_success');
        }

        if ($formAnswer->isSubmitted() && $formAnswer->isValid()) {
            $answer = $formAnswer->getData();
            $answer->setQuestion($question);
            $question->setValidated($question->checkIfValid());
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->persist($answer);
            $em->flush();

            return $this->redirectToRoute('quizz_success');
        }

        return $this->render('default/edit_question.html.twig', array('question' => $question,
            'formQuestion' => $formQuestion->createView(),
            'formAnswer' => $formAnswer->createView()));
    }

    /**
     * @Route("/editanswer/{answerId}", name="edit_answer")
     */
    public function editAnswer(Request $request, $answerId){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Answer');
        $answer = $repository->find($answerId);

        if (!$answer) {
            throw $this->createNotFoundException(
                'No answer found for id '.$answerId
                );
        }

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        $this->denyAccessUnlessGranted('answer', $answer);

        if($form->isSubmitted() && $form->isValid()){
            $answer = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);

            $question = $answer->getQuestion();
            $question->setValidated($question->checkIfValid());
            $em->persist($question);

            $em->flush();
            return $this->redirectToRoute('quizz_success');
        }

        return $this->render('default/edit_answer.html.twig', array('answer' => $answer,
            'form' => $form->createView()));
    }

    /**
     * @Route("/deleteanswer/{answerId}", name="delete_answer")
     */
    public function deleteAnswer($answerId){
        $repository = $this->getDoctrine()->getRepository('AppBundle:Answer');
        $answer = $repository->find($answerId);
        $em = $this->getDoctrine()->getManager();
        $em->remove($answer);
        $em->flush();

        return $this->redirectToRoute('quizz_success');
    }
}
