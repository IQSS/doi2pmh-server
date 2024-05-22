<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Folder;
use App\Entity\User;
use App\Form\Doi\DoiType;
use App\Form\Folder\FolderDeleteType;
use App\Form\Folder\FolderType;
use App\Form\User\UserDeleteType;
use App\Form\User\UserType;
use App\Services\DoiService;
use App\Services\FolderService;

/**
 * Class FolderController
 * @package App\Controller\Admin
 * @Route("/folder", name="folder_")
 */
class FolderController extends AbstractController
{

    private Configuration $configuration;

    public function __construct(
        private FolderService $folderService,
        private DoiService $doiService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator
    )
    {
        $this->configuration = Configuration::getConfigurationInstance($this->entityManager);
    }

    /**
     * @Route("/{id?}", methods={"GET"}, name="index")
     * @param mixed $id
     * @return Response
     * @noinspection PhpUnused
     */
    public function index($id): Response
    {
        if ($this->getUser()->isFirstConnexion() && !$this->configuration->isCasAuthentication()) {
            $this->addFlash('info', $this->translator->trans('admin.folder.user.firstConnexion'));
            return $this->redirectToRoute('user_index_edit');
        }

        /**
         * @var Folder $folder
         */
        $folder = $this->entityManager->getRepository(Folder::class)->find($id);

        return $this->render('admin/index.html.twig', [
            'folder' => $this->entityManager->getRepository(Folder::class)->find($id),
            'steps' => $this->folderService->getParents($folder),
            'folderCreateForm' => $this->createForm(FolderType::class)->createView(),
            'folderEditForm' => $this->createForm(FolderType::class, $folder)->createView(),
            'folderDeleteForm' => $this->createForm(FolderDeleteType::class, $folder)->createView(),
            'doiCreateForm' => $this->createForm(DOIType::class)->createView(),
            'userAddForm' => $this->createForm(UserType::class)->createView()
        ]);
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
        $form = $this->createForm(FolderType::class);
        $form->handleRequest($request);

        // If error redirect
        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->hasRightsFor($folder)) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.create.error'));
            return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
        }

        if ($this->entityManager->getRepository(Folder::class)->findOneBy(['name' => $form->getData()->getName()])) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.create.duplicate'));
            return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
        }

        $newFolder = $form->getData();
        $newFolder->setParent($folder);

        $this->entityManager->persist($newFolder);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.folder.create.success'));
        return $this->redirectToRoute('folder_index', ['id' => $newFolder->getId()]);
    }

    /**
     * @Route("/{folder}/edit", methods={"POST"}, name="edit")
     * @param Request $request
     * @param Folder $folder
     * @return Response
     * @noinspection PhpUnused
     */
    public function edit(Request $request, Folder $folder): Response
    {
        $form = $this->createForm(FolderType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->hasRightsFor($folder)) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        $folder->setName($form->getData()->getName());

        $this->entityManager->persist($folder);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.folder.create.success'));
        return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
    }

    /**
     * @Route("/{folder}/delete", methods={"POST"}, name="delete")
     * @param Request $request
     * @param Folder $folder
     * @return Response
     * @noinspection PhpUnused
     */
    public function delete(Request $request, Folder $folder): Response
    {
        $form = $this->createForm(FolderDeleteType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() 
            || !$form->isValid() 
            || !$this->getUser()->hasRightsFor($folder) 
            || $folder->isRootFolderApp()) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        if (!$folder->getChildren()->isEmpty()) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.delete.hasChildren'));
            return $this->redirectToRoute('folder_index', array('id' => $folder->getId()));
        }

        foreach ($folder->getDois() as $doi) {
            $doi->setFolder($doi->getFolder()->getParent());
            if (!$doi->isDeleted()){
                $this->doiService->deleteDoi($doi);
            }
        }

        $folder->setParent(null);

        foreach ($folder->getOwners() as $user) {
            $user->setRootFolder(null);
        }

        $this->entityManager->remove($folder);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('admin.folder.delete.success'));
        return $this->redirectToRoute('folder_index', ['id' => null]);
    }

    /**
     * @Route("/{folder}/{user}/delete", methods={"GET"}, name="delete_user_index")
     * @param Folder $folder
     * @param User $user
     * @return Response
     * @noinspection PhpUnused
     */
    public function deleteIndex(Folder $folder, User $user): Response
    {
        return $this->render('admin/modals/user/deleteFromFolder.html.twig', [
            'user' => $user,
            'folder' => $folder,
            'userDeleteForm' => $this->createForm(UserDeleteType::class)->createView()
        ]);
    }
}
