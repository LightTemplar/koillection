<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Album;
use App\Form\Type\Entity\AlbumType;
use App\Repository\AlbumRepository;
use App\Repository\PhotoRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AlbumController extends AbstractController
{
    #[Route(
        path: ['en' => '/albums', 'fr' => '/albums'],
        name: 'app_album_index',
        methods: ['GET']
    )]
    #[Route(
        path: ['en' => '/user/{username}/albums', 'fr' => '/utilisateur/{username}/albums'],
        name: 'app_shared_album_index',
        methods: ['GET']
    )]
    public function index(AlbumRepository $albumRepository): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $albums = $albumRepository->findBy(['parent' => null], ['title' => 'ASC']);
        $photosCounter = 0;
        foreach ($albums as $album) {
            $photosCounter += \count($album->getPhotos());
        }

        return $this->render('App/Album/index.html.twig', [
            'albums' => $albums,
            'photosCounter' => $photosCounter,
        ]);
    }

    #[Route(
        path: ['en' => '/albums/add', 'fr' => '/albums/ajouter'],
        name: 'app_album_add',
        methods: ['GET', 'POST']
    )]
    public function add(Request $request, TranslatorInterface $translator, AlbumRepository $albumRepository, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $album = new Album();
        if ($request->query->has('parent')) {
            $parent = $albumRepository->findOneBy([
                'id' => $request->query->get('parent'),
                'owner' => $this->getUser(),
            ]);
            $album
                ->setParent($parent)
                ->setVisibility($parent->getVisibility())
                ->setParentVisibility($parent->getVisibility())
                ->setFinalVisibility($parent->getFinalVisibility())
            ;
        }

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->persist($album);
            $managerRegistry->getManager()->flush();

            $this->addFlash('notice', $translator->trans('message.album_added', ['%album%' => '&nbsp;<strong>'.$album->getTitle().'</strong>&nbsp;']));

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('App/Album/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: ['en' => '/albums/{id}/edit', 'fr' => '/albums/{id}/editer'],
        name: 'app_album_edit',
        requirements: ['id' => '%uuid_regex%'],
        methods: ['GET', 'POST']
    )]
    public function edit(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_edited', ['%album%' => '&nbsp;<strong>'.$album->getTitle().'</strong>&nbsp;']));

            return $this->redirectToRoute('app_album_show', ['id' => $album->getId()]);
        }

        return $this->render('App/Album/edit.html.twig', [
            'form' => $form->createView(),
            'album' => $album,
        ]);
    }

    #[Route(
        path: ['en' => '/albums/{id}/delete', 'fr' => '/albums/{id}/supprimer'],
        name: 'app_album_delete',
        requirements: ['id' => '%uuid_regex%'],
        methods: ['POST']
    )]
    public function delete(Request $request, Album $album, TranslatorInterface $translator, ManagerRegistry $managerRegistry): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        $form = $this->createDeleteForm('app_album_delete', $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->remove($album);
            $managerRegistry->getManager()->flush();
            $this->addFlash('notice', $translator->trans('message.album_deleted', ['%album%' => '&nbsp;<strong>'.$album->getTitle().'</strong>&nbsp;']));
        }

        return $this->redirectToRoute('app_album_index');
    }

    #[Route(
        path: ['en' => '/albums/{id}', 'fr' => '/albums/{id}'],
        name: 'app_album_show',
        requirements: ['id' => '%uuid_regex%'],
        methods: ['GET']
    )]
    #[Route(
        path: ['en' => '/user/{username}/albums/{id}', 'fr' => '/utilisateur/{username}/albums/{id}'],
        name: 'app_shared_album_show',
        requirements: ['id' => '%uuid_regex%'],
        methods: ['GET']
    )]
    public function show(Album $album, AlbumRepository $albumRepository, PhotoRepository $photoRepository): Response
    {
        $this->denyAccessUnlessFeaturesEnabled(['albums']);

        return $this->render('App/Album/show.html.twig', [
            'album' => $album,
            'children' => $albumRepository->findBy(['parent' => $album]),
            'photos' => $photoRepository->findBy(['album' => $album]),
        ]);
    }
}
