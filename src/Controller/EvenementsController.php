<?php

namespace App\Controller;

use App\Entity\Evenements;
use App\Entity\Images;
use App\Form\EvenementsType;
use App\Repository\EvenementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/evenements')]
class EvenementsController extends AbstractController
{
    #[Route('/', name: 'evenements_index', methods: ['GET'])]
    public function index(EvenementsRepository $evenementsRepository): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        return $this->render('evenements/index.html.twig', [
            'evenements' => $evenementsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'evenements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenements();
        $form = $this->createForm(EvenementsType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises
    $images = $form->get('images')->getData();
    
    // On boucle sur les images
    foreach($images as $image){
        // On génère un nouveau nom de fichier
        $fichier = md5(uniqid()).'.'.$image->guessExtension();
        
        // On copie le fichier dans le dossier uploads
        $image->move(
            $this->getParameter('images_directory'),
            $fichier
        );
        
        // On crée l'image dans la base de données
        $img = new Images();
        $img->setName($fichier);
        $evenement->addImage($img);
    }
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('evenements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenements/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'evenements_show', methods: ['GET'])]
    public function show(Evenements $evenement): Response
    {
        return $this->render('evenements/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'evenements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenements $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementsType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
    
            // On boucle sur les images
            foreach($images as $image){
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();
                
                // On copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                
                // On crée l'image dans la base de données
                $img = new Images();
                $img->setName($fichier);
                $evenement->addImage($img);
            }
            $entityManager->flush();

            return $this->redirectToRoute('evenements_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('evenements/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'evenements_delete', methods: ['POST'])]
    public function delete(Request $request, Evenements $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('evenements_index', [], Response::HTTP_SEE_OTHER);
    }
}
