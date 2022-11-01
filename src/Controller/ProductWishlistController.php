<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ProductAlreadyInWishlistException;
use App\Service\ProductWishlistService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product-wishlist")
 */
class ProductWishlistController extends AbstractController
{
    private ProductWishlistService $productWishlistService;

    public function __construct(ProductWishlistService $productWishlistService)
    {
        $this->productWishlistService = $productWishlistService;
    }

    /**
     * @Route("/", name="wishlist_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('wishlist/index.html.twig', [
            'products' => $this->productWishlistService->getProducts()
        ]);
    }

    /**
     * @Route("/add/{id}", name="wishlist_add_product", methods={"POST"})
     */
    public function addProduct(int $id): Response
    {
        try {
            $this->productWishlistService->addByProductId($id);

            $this->addFlash('success', 'Product has been added to wishlist.');
        } catch (ProductAlreadyInWishlistException $exception) {
            $this->addFlash('error', $exception->getMessage());
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Adding to wishlist was not successful. Try again.');
        }

        return $this->redirectToRoute('wishlist_index');
    }
}
