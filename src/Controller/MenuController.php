<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MenuController extends AbstractController
{
    public function menu(ProductCategoryRepository $productCategoryRepository): Response
    {
        return $this->render('menu/_menu.html.twig', [
            'categories' => $productCategoryRepository->findAll()
        ]);
    }
}
