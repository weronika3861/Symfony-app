<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * @Route("/register", name="register_index", methods={"GET", "POST"})
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        try {
            $user = new User();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($this->formSubmittedAndValid($form)) {
                $this->registrationService->register($user, $passwordHasher, $form->get('plainPassword')->getData());

                $this->addFlash('success', 'Registration was successful.');

                return $this->redirectToRoute('homepage_index');
            }
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Registration was not successful. Try again.');

            return $this->redirectToRoute('register_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @param FormInterface $form
     * @return bool
     */
    private function formSubmittedAndValid(FormInterface $form): bool
    {
        return $form->isSubmitted() && $form->isValid();
    }
}
