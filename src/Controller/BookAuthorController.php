<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookAuthorController extends AbstractController
{
    #[Route('/book/author', name: 'app_book_author')]
    public function index(): Response
    {
        return $this->render('book_author/index.html.twig', [
            'controller_name' => 'BookAuthorController',
        ]);
    }
}
