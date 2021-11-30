<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            // setting the form
            // last comment
            $user->setPassword(
            $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $profilePicture = $form->get('ProfilePicture')->getData();//getting the profile picture
            //getting the filename
            $originalPicture = pathinfo($profilePicture->getClientOriginalName(),PATHINFO_FILENAME);
            $safePicture = $slugger->slug($originalPicture);
            $newNamePicture = $safePicture.'-'.uniqid().'.'.$profilePicture->guessExtension();
            //Moving the picture to the directory where profile pictures are stored
            try{
                $profilePicture->move(
                    $this->getParameter('profilePictures_directory'),
                    $newNamePicture
                );
            }
            catch(FileException $exception){
                // exceptions
                echo $exception;
            }
            $user->setProfilePicture($newNamePicture);

            //persist the user variable
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
