<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Exception\InvalidFormException;
use App\Form\ProductType;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/products")
 */
class ProductController extends AbstractController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @Route("/", name="product_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $this->productService->findAll()
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        try {
            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $this->checkIfFormIsValid($form);
                $this->productService->addProduct($product);
                $this->addFlash('success', 'Product created.');

                return $this->redirectToRoute('product_index');
            }
        } catch (InvalidFormException $invalidFormException) {
            $this->addFlash('error', $invalidFormException->getMessage());

            return $this->redirectToRoute('product_index');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Creating product failed. Try again.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'categories' => $product->getCategories()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        try {
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $this->checkIfFormIsValid($form);
                $this->productService->editProduct();
                $this->addFlash('success', 'Product updated.');

                return $this->redirectToRoute('product_index');
            }
        } catch (InvalidFormException $invalidFormException) {
            $this->addFlash('error', $invalidFormException->getMessage());

            return $this->redirectToRoute('product_index');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Updating product failed. Try again.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $product->getId(), $request->get('_token'))) {
                $this->productService->deleteProduct($product);

                $this->addFlash('success', 'Product deleted.');
            }
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Deleting product failed. Try again.');
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @param FormInterface $form
     * @throws InvalidFormException
     */
    private function checkIfFormIsValid(FormInterface $form): void
    {
        if (!$form->isValid()) {
            throw new InvalidFormException((string)$form->getErrors(true, false));
        }
    }
}
