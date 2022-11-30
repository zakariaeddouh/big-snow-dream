<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('pages/category/index.html.twig', [
            'categories' => $categoryRepository->findBy(['user' => $this->getUser()]),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        CategoryRepository $categoryRepository
    ): Response {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUser($this->getUser());
            $categoryRepository->save($category, true);

            $this->addFlash(
                'success',
                'Votre catégorie a été créée avec succès !'
            );

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pages/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === category.getUser()")]
    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->save($category, true);

            $this->addFlash(
                'success',
                'Votre catégorie a été modifiée avec succès !'
            );

            return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('pages/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === category.getUser()")]
    #[Route('/{id}', name: 'category_delete', methods: ['GET'])]
    public function delete(
        Category $category,
        EntityManagerInterface $manager
    ): Response {
        $manager->remove($category);
        $manager->flush();

        $this->addFlash(
            'success',
            'Votre catégorie a été supprimée avec succès !'
        );

        return $this->redirectToRoute('category_index', [], Response::HTTP_SEE_OTHER);
    }
}
