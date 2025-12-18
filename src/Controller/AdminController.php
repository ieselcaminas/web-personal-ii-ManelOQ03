<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/images', name: 'app_images')]
    public function images(): Response
    {
        // #[IsGranted('ROLE_ADMIN)]
        // final class PageController extends AbstractController

        return $this->render('admin/images.html.twig', []);
    }
}
