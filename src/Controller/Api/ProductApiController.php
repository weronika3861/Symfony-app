<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\InvalidCategoryException;
use App\Exception\MissingAttributeException;
use App\Service\ArrayProductDenormalizer;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/products")
 */
class ProductApiController extends AbstractController
{
    private ArrayProductDenormalizer $arrayProductDenormalizer;
    private SerializerInterface $serializer;
    private ProductService $productService;

    public function __construct(
        ArrayProductDenormalizer $arrayProductDenormalizer,
        SerializerInterface $serializer,
        ProductService $productService
    ) {
        $this->serializer = $serializer;
        $this->productService = $productService;
        $this->arrayProductDenormalizer = $arrayProductDenormalizer;
    }

    /**
     * @Route("/", name="product_api_show_list", methods={"GET"})
     */
    public function showList(): Response
    {
        try {
            if (!$products = $this->productService->findAll()) {
                return $this->json(['NOT FOUND' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->json(
                ['INTERNAL SERVER ERROR' => Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(
            $this->serializer->serialize(
                $products,
                'json',
                ['groups' => 'list']
            )
        );
    }

    /**
     * @Route("/{id}", name="product_api_show_item", methods={"GET"})
     */
    public function showItem(int $id): Response
    {
        try {
            if (!$product = $this->productService->find($id)) {
                return $this->json(['NOT FOUND' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
            }

            $arrayProduct = $this->serializer->serialize(
                $product,
                'json',
                ['groups' => 'item']
            );
        } catch (\Exception $e) {
            return $this->json(
                ['INTERNAL SERVER ERROR' => Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json($arrayProduct);
    }

    /**
     * @Route("/", name="product_api_add", methods={"POST"})
     */
    public function add(Request $request): Response
    {
        try {
            $requestArray = $request->toArray();

            $this->productService->add($this->arrayProductDenormalizer->execute($requestArray));
        } catch (MissingAttributeException $e) {
            return $this->json(
                ['MISSING ATTRIBUTE' => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        } catch (InvalidCategoryException $e) {
            return $this->json(
                ['ASSIGNED INVALID CATEGORY' => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['INVALID ARGUMENTS: ' . $e->getMessage() => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception|ExceptionInterface $e) {
            return $this->json(
                ['INTERNAL SERVER ERROR' => Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(['OK' => Response::HTTP_OK], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="product_api_edit", methods={"PATCH"})
     */
    public function edit(Request $request, int $id): Response
    {
        try {
            if (!$product = $this->productService->find($id)) {
                return $this->json(['NOT FOUND' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
            }

            $requestArray = $request->toArray();

            $this->productService->edit($product, $requestArray);
        } catch (InvalidCategoryException $e) {
            return $this->json(
                ['ASSIGNED INVALID CATEGORY' => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['INVALID ARGUMENTS: ' . $e->getMessage() => Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception|ExceptionInterface $e) {
            return $this->json(
                ['INTERNAL SERVER ERROR' => Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(['OK' => Response::HTTP_OK], Response::HTTP_OK);
    }

    /**
     * @Route("/{id}", name="product_api_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id): Response
    {
        try {
            if (!$this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
                return $this->json(['FORBIDDEN' => Response::HTTP_FORBIDDEN], Response::HTTP_FORBIDDEN);
            }

            if (!$product = $this->productService->find($id)) {
                return $this->json(['NOT FOUND' => Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
            }

            $this->productService->delete($product);
        } catch (\Exception $e) {
            return $this->json(
                ['INTERNAL SERVER ERROR' => Response::HTTP_INTERNAL_SERVER_ERROR],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json(['OK' => Response::HTTP_OK], Response::HTTP_OK);
    }
}
