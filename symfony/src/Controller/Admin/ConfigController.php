<?php

namespace App\Controller\Admin;

use App\Entity\Configuration;
use App\Form\Configuration\ConfigurationType;
use App\Services\DoiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ConfigController
 * @package App\Controller\Admin
 * @Route("/configuration", name="config_")
 */
class ConfigController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private DoiService $doiService
    ) {
    }

    /**
     * @Route("/edit", methods={"GET"}, name="edit_index")
     * @return Response
     * @noinspection PhpUnused
     */
    public function editIndex(): Response
    {
        /**
         * @var Configuration $configuration
         */
        $configuration = Configuration::getConfigurationInstance($this->entityManager);

        return $this->render('admin/configuration/edit.html.twig', [
            'configForm' => $this->createForm(ConfigurationType::class, $configuration)->createView(),
            'doiUpdatedLogs' => $configuration->getUpdatedDoiLogs()
        ]);
    }


    /**
     * @Route("/edit", methods={"POST"}, name="edit")
     * @param Request $request
     * @return RedirectResponse
     * @noinspection PhpUnused
     */
    public function edit(Request $request): RedirectResponse
    {
        $form = $this->createForm(ConfigurationType::class);
        $form->handleRequest($request);

        // If error redirect
        if (!$form->isSubmitted() || !$form->isValid() || !$this->getUser()->isAdmin()) {
            $this->addFlash('danger', $this->translator->trans($form->getErrors(true)[0]->getMessage()));
            return $this->redirectToRoute('config_edit_index');
        }

        $configuration = Configuration::getConfigurationInstance($this->entityManager);
        $configuration->setAdminEmail($form->getData()->getAdminEmail());
        $configuration->setRepositoryName($form->getData()->getRepositoryName());
        $configuration->setEarliestDatestamp($form->getData()->getEarliestDatestamp());
        $configuration->setCasAuthentication($form->getData()->isCasAuthentication());
        $configuration->setExcludedTypes($form->getData()->getExcludedTypes());
        if ($form->getData()->isCasAuthentication()) {
            if (is_null($form->getData()->getCasVersion())) {
                $this->addFlash('danger', $this->translator->trans('admin.configuration.edit.error', ['fieldName' => 'Version']));
                return $this->redirectToRoute('config_edit_index');
            }
            $configuration->setCasVersion($form->getData()->getCasVersion());

            if (is_null($form->getData()->getCasHost())) {
                $this->addFlash('danger', $this->translator->trans('admin.configuration.edit.error', ['fieldName' => 'Host / Nom de domaine']));
                return $this->redirectToRoute('config_edit_index');
            }
            $configuration->setCasHost($form->getData()->getCasHost());

            if (is_null($form->getData()->getCasPort())) {
                $this->addFlash('danger', $this->translator->trans('admin.configuration.edit.error', ['fieldName' => 'Port']));
                return $this->redirectToRoute('config_edit_index');
            }
            $configuration->setCasPort($form->getData()->getCasPort());

            if (is_null($form->getData()->getCasUri())) {
                $this->addFlash('danger', $this->translator->trans('admin.configuration.edit.error', ['fieldName' => 'URI']));
                return $this->redirectToRoute('config_edit_index');
            }
            $configuration->setCasUri($form->getData()->getCasUri());
        }
        $this->entityManager->persist($configuration);
        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('admin.configuration.edit.success'));
        return $this->redirectToRoute('config_edit_index');
    }

    /**
     * @Route("/refreshDoi", methods={"GET"}, name="refresh_doi")
     * @return Response
     * @noinspection PhpUnused
     */
    public function refresh(): Response
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        $this->doiService->refreshDois();
        return new Response();
    }
}
