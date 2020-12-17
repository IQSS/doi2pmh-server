<?php

namespace App\Controller;

use App\Entity\Folder;
use App\Form\FolderType;
use App\Entity\Doi;
use App\Entity\User;
use App\Form\DoiType;
use App\Service\DoiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;



/**
 * @Route("/doi2pmh/admin/{_locale}", requirements={"_locale": "en|fr"})
 * 
 * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")
 */
class AdminController extends AbstractController
{

    private function getFolderOrRoot(int $id = null): ?Folder
    {
        $folder = new Folder();
        if (!$id){
            $folder = $this->getDoctrine()->getRepository(Folder::class)->getRootFolder();
            if (!$folder){
                $folder = (new Folder())->setName("Root");
                $this->getDoctrine()->getManager()->persist($folder);
                $this->getDoctrine()->getManager()->flush();
            }
        } else {
            $folder = $this->getDoctrine()->getRepository(Folder::class)->find($id);
        }
        return $folder;
    }

    /**
     * @Route("/", name="admin_index")
     * 
     */
    public function index()
    {
        return $this->redirectToRoute('admin_get_folder');
    }

    /**
     * @Route("/folder/{id}", methods={"GET"}, name="admin_get_folder", requirements={"id"="\d*"})
     */
    public function showFolder(Request $request, int $id = null)
    {
        $folder = $this->getFolderOrRoot($id);

        if ($id == null){
            return $this->redirectToRoute('admin_get_folder',array("id" => $folder->getId()));
        }

        if (!$folder){
            $this->addFlash('warning', "Unknow folder id");
            return $this->redirectToRoute('admin_get_folder');
        }

        $newfolder = new Folder();
        $form = $this->createForm(FolderType::class, $newfolder, ['action' => $this->generateUrl('admin_create_folder', array('id' => $id))]);
        $editfolderform = $this->createForm(FolderType::class, $folder, ['action' => $this->generateUrl('admin_edit_folder', array('id' => $id))]);
        
        $newdoi = new Doi();
        $formdoi = $this->createForm(DoiType::class, $newdoi, ['action' => $this->generateUrl('admin_create_doi', array("id" => $folder->getId()))]);
        $editformdoi = $this->createForm(DoiType::class, $newdoi, ['action' => $this->generateUrl('admin_create_doi', array("id" => $folder->getId()))]);

        // Compute path to folder for display
        $path = array();
        $step = $folder;
        while ($step->getParent())
        {
            $step = $step->getParent();
            $path[] = $step;
        }
        return $this->render('admin/doitree.html.twig', [
            'current' => 'doi',
            'folderform' => $form->createView(),
            'folder' => $folder,
            'folder_path' => $path,
            'doiform' => $formdoi->createView(),
            'editfolderform' => $editfolderform->createView(),
            'editdoiform' => $editformdoi->createView(),
        ]);
    }

    /**
     * @Route("/folder/{id}/newFolder", methods={"POST"}, name="admin_create_folder", requirements={"id"="\d*"})
     */
    public function createFolder(Request $request, int $id = null)
    {
        $folder = $this->getFolderOrRoot($id);
        if (!$folder){
            $this->addFlash('warning', "Unknow folder id");
            return $this->redirectToRoute('admin_get_folder');
        }

        $this->denyAccessUnlessGranted('OWNER_OR_ADMIN', $folder);


        $newfolder = new Folder();
        $form = $this->createForm(FolderType::class, $newfolder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newfolder = $form->getData();

            $newfolder->setParent($folder);
            foreach ($folder->getOwners() as $o){
                $newfolder->addOwner($o);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newfolder);
            $entityManager->flush();
            return $this->redirectToRoute('admin_get_folder', array('id' => $newfolder->getId()));
        }
        return $this->redirectToRoute('admin_get_folder', array('id' => $id));
    }

    /**
     * @Route("/folder/{id}/edit", methods={"POST"}, name="admin_edit_folder", requirements={"id"="\d*"})
     */
    public function editFolder(Request $request, int $id = null)
    {
        $folder = $this->getFolderOrRoot($id);
        if (!$folder){
            $this->addFlash('warning', "Unknow folder id");
            return $this->redirectToRoute('admin_get_folder');
        }

        $form = $this->createForm(FolderType::class, $folder);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $folder = $form->getData();
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Folder updated");
        }
        return $this->redirectToRoute('admin_get_folder', array('id' => $id));
    }

    /**
     * @Route("/folder/{id}/delete", methods={"GET"}, name="admin_delete_folder", requirements={"id"="\d*"})
     */
    public function deleteFolder(Request $request, int $id = null)
    {
        $folder = $this->getFolderOrRoot($id);
        if (!$folder){
            $this->addFlash('warning', "Unknow folder id");
            return $this->redirectToRoute('admin_get_folder');
        }

        if ($folder->getChildren()->count() != 0 || $folder->getDois()->count() != 0){
            $this->addFlash('error', "Please empty folder before delete");
            return $this->redirectToRoute('admin_get_folder', array('id' => $id));
        }
        if ($folder->getParent() == null){
            $this->addFlash('error', "Root folder can not be deleted");
            return $this->redirectToRoute('admin_get_folder', array('id' => $id));
        }
        $parentId = $folder->getParent()->getId();
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($folder);
        $manager->flush();

        return $this->redirectToRoute('admin_get_folder', array('id' => $parentId));
    }

    /**
     * @Route("/folder/{id}/newdoi", methods={"POST"}, name="admin_create_doi", requirements={"id"="\d*"})
     */
    public function createDOI(Request $request, DoiService $doiService, int $id = null)
    {
        $folder = $this->getFolderOrRoot($id);
        if (!$folder){
            $this->addFlash('warning', "Unknow folder id");
            return $this->redirectToRoute('admin_get_folder');
        }

        $newdoi = new Doi();
        $form = $this->createForm(DoiType::class, $newdoi);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: check doi url format, exists and unicity
            $newdoi = $form->getData();

            
            if (!$doiService->validate($newdoi)){
                $this->addFlash('warning', "<a href='".$newdoi."' target='_blank'>$newdoi</a> is not a valid DOI");
                return $this->redirectToRoute('admin_get_folder', array('id' => $id));
            }
            if ($doiService->isDuplicate($newdoi)){
                $this->addFlash('warning', "DOI already exists");
                return $this->redirectToRoute('admin_get_folder', array('id' => $id));
            }

            $doiService->getCitation($newdoi);

            $newdoi->setFolder($folder);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newdoi);
            $entityManager->flush();

            return $this->redirectToRoute('admin_get_folder', array('id' => $id));
        }
        return $this->redirectToRoute('admin_get_folder', array('id' => $id));
    }

    /**
     * @Route("/doi/{id}/delete", methods={"GET"}, name="admin_delete_doi", requirements={"id"="\d*"})
     */
    public function deleteDOI(Request $request, int $id = null)
    {
        $doi = $this->getDoctrine()->getRepository(Doi::class)->find($id);
        if (!$doi){
            $this->addFlash('warning', "Unknow DOI");
            return $this->redirectToRoute('admin_get_folder');
        }

        $parentId = $doi->getFolder()->getId();
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($doi);
        $manager->flush();

        return $this->redirectToRoute('admin_get_folder', array('id' => $parentId));
    }

    /**
     * @Route("/doi/{id}/edit", methods={"POST"}, name="admin_edit_doi", requirements={"id"="\d*"})
     */
    public function editDOI(Request $request, DoiService $doiService, int $id = null)
    {
        $doi = $this->getDoctrine()->getRepository(Doi::class)->find($id);
        if (!$doi){
            $this->addFlash('warning', "Unknow DOI");
            return $this->redirectToRoute('admin_get_folder');
        }

        $parentId = $doi->getFolder()->getId();
        $form = $this->createForm(DoiType::class, $doi);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $doi = $form->getData();

            if (!$doiService->validate($doi, true)){
                $this->addFlash('warning', "<a href='".$doi."' target='_blank'>$doi</a> is not a valid DOI");
                return $this->redirectToRoute('admin_get_folder', array('id' => $parentId));
            }
            if ($doiService->isDuplicate($doi)){
                $this->addFlash('warning', "DOI already exists");
                return $this->redirectToRoute('admin_get_folder', array('id' => $parentId));
            }

            $doiService->getCitation($doi, true);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', "Doi updated");
        }

        return $this->redirectToRoute('admin_get_folder', array('id' => $parentId));
    }

    /**
     * @Route("/user", name="admin_user")
     * 
     */
    public function userList()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        return $this->render('admin/users.html.twig', [
            'current' => 'user',
            'users' => $users
        ]);
    }
}
