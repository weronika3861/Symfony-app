<?php

namespace App\Repository;

use App\Entity\Product;
use App\Exception\ProductNotExistException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository implements ProductRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $product): void
    {
        $this->_em->persist($product);
        $this->_em->flush();
    }

    public function delete(Product $product): void
    {
        $this->_em->remove($product);
        $this->_em->flush();
    }

    public function edit(): void
    {
        $this->_em->flush();
    }

    public function getProductsByIds(array $productsIds): array
    {
        return $this->findBy(['id' => $productsIds]);
    }

    public function getProductsByNames(array $productsNames): array
    {
        return $this->findBy(['id' => $productsNames]);
    }

    public function get(int $id): Product
    {
        $product = $this->find($id);
        if (!$product) {
            throw new ProductNotExistException((string)$id);
        }

        return $product;
    }
}
