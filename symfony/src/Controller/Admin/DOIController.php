<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Attribute\Route;
use App\Services\FolderService;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Doi;
use App\Entity\Folder;
use App\Form\Doi\DoiDeleteType;
use App\Form\Doi\DoiType;
use App\Services\DoiService;

/**
 * Class DOIController
 * @package App\Controller\Admin
 */
class DOIController extends AbstractController
{

    public function __construct(
        private DoiService $doiService,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private FolderService $folderService
    )
    {
    }

    /**
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/', methods: ['GET'], name: 'doi_index')]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('folder_index');
    }

    /**
     * @param Request $request
     * @param Folder $folder
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/{folder}/create', methods: ['POST'], name: 'doi_create')]
    public function create(Request $request, Folder $folder): RedirectResponse
    {
        $form = $this->createForm(DoiType::class);
        $form->handleRequest($request);

        // If error redirect
        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->hasRightsFor($folder)) {
            $this->addFlash('danger', $this->translator->trans($form->getErrors(true)[0]->getMessage()));
            return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
        }

        // If it has been deleted update it
        $newDoi = $this->doiService->replaceIfDeleted($form->getData());

        $newDoi->setFolder($folder);
        $newDoi = $this->doiService->fillDoiData($newDoi);
        $this->entityManager->persist($newDoi);
        try {
            $this->entityManager->flush();
        }catch (Exception $e) {
            $existingFolderId = $newDoi->getFolder()->getId();
            $existingFolderUrl = $this->generateUrl('folder_index', ['id' => $existingFolderId], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->addFlash('danger', $this->translator->trans('admin.constraint.doi.exist') . ' : <a href="' . $existingFolderUrl. '">URL</a>');
            return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
        }

        $this->addFlash('success', $this->translator->trans('admin.doi.create.success'));
        return $this->redirectToRoute('folder_index', ['id' => $folder->getId()]);
    }

    /**
     * @param Doi $doi
     * @return Response
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/{doi}/edit', methods: ['GET'], name: 'doi_edit_index')]
    public function editIndex(Doi $doi): Response
    {
        return $this->render('admin/modals/doi/edit.html.twig', [
            'doi' => $doi,
            'doiEditForm' => $this->createForm(DoiType::class, $doi)
        ]);
    }


    /**
     * @param Request $request
     * @param Doi $doi
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/{doi}/edit', methods: ['POST'], name: 'doi_edit')]
    public function edit(Request $request, Doi $doi): RedirectResponse
    {
        $form = $this->createForm(DoiType::class);
        $form->handleRequest($request);

        // If error redirect
        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->hasRightsFor($doi->getFolder())) {
            $this->addFlash('danger', $this->translator->trans($form->getErrors(true)[0]->getMessage()));
            return $this->redirectToRoute('folder_index', ['id' => $doi->getFolder()->getId()]);
        }

        $doi->setUri($form->getData()->getUri());
        $doi->setCitation($form->getData()->getCitation());
        $this->entityManager->persist($doi);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.doi.edit.success'));
        return $this->redirectToRoute('folder_index', ['id' => $doi->getFolder()->getId()]);
    }

    /**
     * @param Doi $doi
     * @return Response
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/{doi}/delete', methods: ['GET'], name: 'doi_delete_index')]
    public function deleteIndex(Doi $doi): Response
    {
        return $this->render('admin/modals/doi/delete.html.twig', [
            'doi' => $doi,
            'doiDeleteForm' => $this->createForm(DoiDeleteType::class)
        ]);
    }

    /**
     * @param Request $request
     * @param Doi $doi
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    #[Route(path: '/doi/{doi}/delete', methods: ['POST'], name: 'doi_delete')]
    public function delete(Request $request, Doi $doi): RedirectResponse
    {
        $form = $this->createForm(DoiDeleteType::class);
        $form->handleRequest($request);

        $folderId = $doi->getFolder()->getId();
        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->hasRightsFor($doi->getFolder())) {
            $this->addFlash('danger', $this->translator->trans('admin.folder.edit.error'));
            return $this->redirectToRoute('folder_index', ['id' => $folderId]);
        }
        $this->doiService->deleteDoi($doi);
        $this->addFlash('success', $this->translator->trans('admin.doi.delete.success'));
        return $this->redirectToRoute('folder_index', ['id' => $folderId]);
    }
}
