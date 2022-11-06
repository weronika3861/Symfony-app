<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Exception\InvalidFormException;
use App\Exception\ProductNotExistException;
use App\Form\DeletingImagesForm;
use App\Form\MainImageForm;
use App\Form\ProductType;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @Route("/products")
 */
class ProductController extends AbstractController
{
    private ProductService $productService;
    private DeletingImagesForm $deletingImagesForm;
    private MainImageForm $mainImageForm;

    public function __construct(
        ProductService $productService,
        DeletingImagesForm $deletingImagesForm,
        MainImageForm $mainImageForm
    ) {
        $this->productService = $productService;
        $this->deletingImagesForm = $deletingImagesForm;
        $this->mainImageForm = $mainImageForm;
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
                $this->productService->attachImages($product, $form['new_images']->getData());
                $this->productService->add($product);
                $this->addFlash('success', 'Product created.');

                return $this->redirectToRoute('choose_main_image', [
                    'id' => $product->getId()
                ]);
            }
        } catch (InvalidFormException | FileException $exception) {
            $this->addFlash('error', $exception->getMessage());

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
            $productForm = $this->createForm(ProductType::class, $product);
            $productForm->handleRequest($request);

            $deletingImagesForm = $this->deletingImagesForm->createForm(
              $this->createFormBuilder(),
              $product->getImages()->getValues()
            );
            $deletingImagesForm->handleRequest($request);

            if ($deletingImagesForm->isSubmitted()) {
                $this->checkIfFormIsValid($deletingImagesForm);
                $this->productService->removeImages($product, $deletingImagesForm['images_to_delete']->getData());
                $this->productService->edit($product, []);

                return $this->redirectToRoute('product_edit', [
                    'id' => $product->getId()
                ]);
            }

            if ($productForm->isSubmitted()) {
                $this->checkIfFormIsValid($productForm);
                $this->productService->attachImages($product, $productForm['new_images']->getData());
                $this->productService->edit($product, []);
                $this->addFlash('success', 'Product updated.');

                return $this->redirectToRoute('choose_main_image', [
                    'id' => $product->getId()
                ]);
            }
        } catch (FileNotFoundException | IOException $exception) {
            $this->addFlash('error', 'Removing images failed. Try again.');

            return $this->redirectToRoute('product_index');
        } catch (InvalidFormException | FileException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('product_index');
        } catch (\Exception|ExceptionInterface $exception) {
            $this->addFlash('error', 'Updating product failed. Try again.');

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $productForm->createView(),
            'deleting_images_form' => $deletingImagesForm->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"POST"})
     */
    public function delete(Request $request, Product $product): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $product->getId(), $request->get('_token'))) {
                $this->productService->delete($product);

                $this->addFlash('success', 'Product deleted.');
            }
        } catch (FileNotFoundException | IOException $exception) {
            $this->addFlash('error', 'Removing images failed. Try again.');
        } catch (\Exception $exception) {
            $this->addFlash('error', 'Deleting product failed. Try again.');
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/{id}/choose_main_image", name="choose_main_image", methods={"GET", "POST"})
     */
    public function chooseMainImage(int $id, Request $request): Response
    {
        try {
            $product = $this->productService->get($id);

            $form = $this->mainImageForm->createForm(
                $this->createFormBuilder(),
                $product->getImages()->getValues()
            );

            $form->handleRequest($request);
            if ($form->isSubmitted()) {
               $this->checkIfFormIsValid($form);
               $product->setMainImage($form['images']->getData());
               $this->productService->edit($product, []);

               $this->addFlash('success', 'Image has been chosen.');

               return $this->redirectToRoute('product_index');
            }
        } catch (ProductNotExistException $exception) {
            $this->addFlash(
                'erorr',
                'You cannot change main image. Product with id:' .
                $exception->getMessage() .
                ' does not exist.'
            );

            return $this->redirectToRoute('product_index');
        } catch (\Exception | ExceptionInterface $exception) {
            $this->addFlash(
                'erorr',
                'Choosing an image failed.'
            );

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/choose_image.html.twig', [
            'form' => $form->createView()
        ]);
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
