<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\YourEntity;
use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use App\Entity\UserProfile;
use App\Form\UserProfileType;
use Intervention\Image\Image;
use App\Form\ProfileImageType;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class SettingsProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'app_settings_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profile(
        Request $request,
        UserRepository $users
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $userProfile = $user->getUserProfile() ?? new UserProfile();

        $form = $this->createForm(
            UserProfileType::class,
            $userProfile
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userProfile = $form->getData();
            $user->setUserProfile($userProfile);
            $users->add($user, true);
            $this->addFlash(
                'success',
                'Your user profile settings were saved.'
            );

            return $this->redirectToRoute(
                'app_settings_profile'
            );
        }

        return $this->render(
            'settings_profile/profile.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    #[Route('/settings/profile-image', name: 'app_settings_profile_image')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profileImage(
        Request $request,
        SluggerInterface $slugger,
        UserRepository $users,
        // Imagine $imagine
    ): Response {
        $images = $this->getImagesFromDirectory();
        $form = $this->createForm(ProfileImageType::class);
        /** @var User $user */
        $user = $this->getUser();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profileImageFile = $form->get('profileImage')->getData();

            if ($profileImageFile) {
                $originalFileName = pathinfo(
                    $profileImageFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename . '-' . uniqid() . '.' . $profileImageFile->guessExtension();

                // dd(
                //     $originalFileName,
                //     $safeFilename,
                //     $newFileName
                // );

                try {
                    $imageFile = $profileImageFile->move(
                        $this->getParameter('profiles_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                }

                $profile = $user->getUserProfile() ?? new UserProfile();
                $profile->setImage($newFileName);
                $user->setUserProfile($profile);
                $users->add($user, true);
                //imageresize
                // $profileImageFile = Image::make($imageFile);
                // $profileImageFile->resize(300, 400);
                // $profileImageFile->save('uploads/profiles/profileImage.png');

                $thumbnail = Image::make($this->getParameter('profiles_directory').'/'.$newFileName);
                $thumbnail->resize(300, 400);
                $thumbnail->save($this->getParameter('profiles_directory').'/'.$newFileName);

                $this->addFlash('success', 'Your profile image was updated.');

                return $this->redirectToRoute('app_settings_profile_image',[
                    'profileImage' => 
                    $originalFileName,
                ]);
            }
        }
        

        return $this->render(
            'settings_profile/profile_image.html.twig',
            [   
                'form' => $form->createView(),
                'images' => $images,
            ]
        );
    
    }
    private function getImagesFromDirectory()
    {
        $directory = $this->getParameter('kernel.project_dir').'/public/uploads/profiles';

        return array_diff(scandir($directory), ['.', '..']);
    }

    #[Route('/settings/{id}/data', name: 'app_settings_data')]
    public function getData(): JsonResponse
    {
        $images = $this->getImagesFromDirectory();
          $data = [];
            foreach ($images as $image) {
            $data[] = [
                'images' => $images,
           
        ];
    }

        return new JsonResponse($data);
    }
}

    

