<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use App\Entity\Folder;
use App\Entity\User;
use App\Form\User\UserDeleteType;
use App\Form\User\UserEditType;
use App\Form\User\UserType;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @Route("/user", name="user_")
 */
class UserController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private UserPasswordHasherInterface $passwordEncoder,
        private MailerService $mailer
    )
    {
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @return Response
     * @noinspection PhpUnused
     */
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return $this->render('admin/user/list.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/edit", name="index_edit", methods={"GET"})
     * @return Response
     * @noinspection PhpUnused
     */
    public function indexEdit(): Response
    {
        return $this->render('admin/user/edit.html.twig', [
            'user' => $this->getUser(),
            'passwordForm' => $this->createForm(UserEditType::class, $this->getUser())->createView()
        ]);
    }

    /**
     * @Route("/edit", name="edit", methods={"POST"})
     * @param Request $request
     * @return Response
     * @noinspection PhpUnused
     */
    public function edit(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        // If error redirect
        if (!$form->isSubmitted() || !$form->isValid() ) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.create.error'));
            return $this->redirectToRoute('folder_index', ['id' => $user->getRootFolder()->getId()]);
        }

        $oldPassword = $request->request->get('user_edit')['oldPassword'];
        $plainPassword = $request->request->get('user_edit')['plainPassword'];

        if ($plainPassword['first'] !== $plainPassword['second']) {
            $this->addFlash('danger', $this->translator->trans('admin.form.user.password.repeat.invalid'));
            return $this->redirectToRoute('folder_index', ['id' => $user->getRootFolder()->getId()]);
        }

        if ($this->passwordEncoder->isPasswordValid($user, $oldPassword)) {
            $newEncodedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword['first']);
            $user->setPassword($newEncodedPassword);
            $user->setFirstConnexion(false);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('admin.user.edit.success'));
            return $this->redirectToRoute('user_index_edit', ['id' => $user->getId()]);
        }

        $this->addFlash('danger', $this->translator->trans('admin.folder.create.error'));
        return $this->redirectToRoute('folder_index', ['id' => $user->getRootFolder()->getId()]);
    }

    /**
     * @Route("/{userToEdit}/edit/isAdmin", name="edit_admin", methods={"POST"})
     * @param Request $request
     * @param User $userToEdit
     * @return Response
     * @noinspection PhpUnused
     */
    public function editIsAdmin(Request $request, User $userToEdit): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user->isAdmin() || !$userToEdit) {
            $this->addFlash('danger', $this->translator->trans('admin.user.edit.isAdmin.error'));
            return $this->redirectToRoute('user_index');
        }

        $userToEdit->setIsAdmin((bool) $request->request->get('isAdmin'));
        $this->entityManager->persist($userToEdit);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.user.edit.isAdmin.success'));
        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/{folder}/create", methods={"POST"}, name="create")
     * @param Request $request
     * @param Folder $folder
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function create(Request $request, Folder $folder): RedirectResponse
    {
        if (empty(Configuration::getConfigurationInstance($this->entityManager)->getAdminEmail())) {
            $this->addFlash('danger', $this->translator->trans('admin.user.create.error.noAdminEmail'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->isAdmin()) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        /**
         * @var User
         */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $form->getData()->getEmail()]);

        // If user not exist, create it
        if (!$user) {
            try {
                $user = new User();
                $user->setEmail($form->getData()->getEmail());
                $plainPassword = bin2hex(random_bytes(10));
                $user->setPassword($this->passwordEncoder->hashPassword($user, $plainPassword));
                $user->setFirstConnexion(true);
                $this->mailer->sendPassword($form->getData()->getEmail(), $plainPassword);
            } catch (Exception | TransportExceptionInterface $e) {
                $this->addFlash('danger', $this->translator->trans('admin.user.create.error'));
                return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
            }
        }

        if ($folder->getOwners()->contains($user)) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.user.exist'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        // Then add it to folder
        $folder->addOwner($user);
        $user->setRootFolder($folder);
        $this->entityManager->persist($folder);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.user.create.success'));
        return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
    }

    /**
     * @Route("/{user}/{folder}/remove", methods={"POST"}, name="removeFromFolder")
     * @param Request $request
     * @param User $user
     * @param Folder $folder
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function removeFromFolder(Request $request, User $user, Folder $folder): RedirectResponse
    {
        $form = $this->createForm(UserDeleteType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->isAdmin() ) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
        }

        if (!$user || $user->getRootFolder() !== $folder) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        $folder->removeOwner($user);
        $user->setRootFolder(null);
        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.form.folder.remove.user.success'));
        return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
    }

    /**
     * @Route("/{user}/delete", methods={"GET"}, name="delete_index")
     * @param User $user
     * @return Response
     * @noinspection PhpUnused
     */
    public function deleteIndex(User $user): Response
    {
        return $this->render('admin/modals/user/delete.html.twig', [
            'user' => $user,
            'userDeleteForm' => $this->createForm(UserDeleteType::class)->createView()
        ]);
    }

    /**
     * @Route("/{user}/delete", methods={"POST"}, name="delete")
     * @param User $user
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function delete(User $user): RedirectResponse
    {
        if (!$this->getUser()->isAdmin()) {
            $this->addFlash('danger', $this->translator->trans('admin.user.remove.error'));
            return $this->redirectToRoute('folder_index');
        }
        $user->setRootFolder(null);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.user.remove.success'));
        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/autocomplete/{email?}", name="autocomplete", methods={"GET"})
     * @param string|null $email
     * @return JsonResponse
     * @noinspection PhpUnused
     */
    public function autocomplete(?string $email = ''): JsonResponse
    {
        return new JsonResponse($this->entityManager->getRepository(User::class)->findLike(['email' => $email]));
    }

    /**
     * @Route("/apiToken", name="api_token", methods={"GET"})
     * @return JsonResponse
     */
    public function apiToken(JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        $token = $JWTManager->create($this->getUser());
       return new JsonResponse($token);
    }
}
