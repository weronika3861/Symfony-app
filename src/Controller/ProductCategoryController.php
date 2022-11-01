<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\ProductCategory;
use App\Exception\InvalidFormException;
use App\Form\ProductCategoryType;
use App\Service\ProductCategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product-categories")
 */
class ProductCategoryController extends AbstractController
{
    private ProductCategoryService $productCategoryService;

    public function __construct(ProductCategoryService $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * @Route("/", name="product_category_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('product_category/index.html.twig', [
            'product_categories' => $this->productCategoryService->findAll()
        ]);
    }

    /**
     * @Route("/new", name="product_category_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        try {
            $productCategory = new ProductCategory();
            $form = $this->createForm(ProductCategoryType::class, $productCategory);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $this->checkIfFormIsValid($form);
                $this->productCategoryService->add($productCategory);
                $this->addFlash('success', 'Product category created.');

                return $this->redirectToRoute('product_category_index');
            }
        } catch (InvalidFormException $invalidFormException) {
            $this->addFlash('error', $invalidFormException->getMessage());

            return $this->redirectToRoute('product_category_index');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Creating product category failed. Try again.');

            return $this->redirectToRoute('product_category_index');
        }

        return $this->render('product_category/new.html.twig', [
            'product_category' => $productCategory,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="product_category_show", methods={"GET"})
     */
    public function show(ProductCategory $productCategory): Response
    {
        return $this->render('product_category/show.html.twig', [
            'product_category' => $productCategory,
            'products' => $productCategory->getProducts()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_category_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, ProductCategory $productCategory): Response
    {
        try {
            $form = $this->createForm(ProductCategoryType::class, $productCategory);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $this->checkIfFormIsValid($form);
                $this->productCategoryService->edit();
                $this->addFlash('success', 'Product category updated.');

                return $this->redirectToRoute('product_category_index');
            }
        } catch (InvalidFormException $invalidFormException) {
            $this->addFlash('error', $invalidFormException->getMessage());

            return $this->redirectToRoute('product_category_index');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Updating product category failed. Try again.');

            return $this->redirectToRoute('product_category_index');
        }

        return $this->render('product_category/edit.html.twig', [
            'product_category' => $productCategory,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="product_category_delete", methods={"POST"})
     */
    public function delete(Request $request, ProductCategory $productCategory): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $productCategory->getId(), $request->get('_token'))) {
                $this->productCategoryService->delete($productCategory);

                $this->addFlash('success', 'Product category deleted.');
            }
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Deleting product category failed. Try again.');
        }

        return $this->redirectToRoute('product_category_index');
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
