<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;

final class PageController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('page/index.html.twig', []);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('page/about.html.twig', []);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(ManagerRegistry $doctrine, Request $request): Response
    {
        $contact = new Contact();
    $form = $this->createForm(ContactFormType::class, $contact);
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $contacto = $form->getData();    
        $entityManager = $doctrine->getManager();    
        $entityManager->persist($contacto);
        $entityManager->flush();
        return $this->redirectToRoute('thankyou', []);
    }
    return $this->render('page/contact.html.twig', array(
        'form' => $form->createView()    
    ));
    }

    #[Route('/thankyou', name: 'thankyou')]
    public function thankyou(): Response
    {
        return $this->render('page/thankyou.html.twig', []);
    }

    #[Route('/blog', name: 'blog')]
    public function blog(): Response
    {
        return $this->render('page/blog.html.twig', []);
    }

    #[Route('/single_post/{codigo}?1', name: 'single_post')]
    public function single_post(): Response
    {
        return $this->render('page/single_post.html.twig', []);
    }
    
    #[Route('/admin/images', name: 'app_images')]
    public function images(): Response
    {
        return $this->render('admin/images.html.twig', []);
    }

}
