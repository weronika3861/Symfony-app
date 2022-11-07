<?php

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 *
 * @method ProductImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductImage[]    findAll()
 * @method ProductImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductImageRepository extends ServiceEntityRepository implements ProductImageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    public function add(ProductImage $image): void
    {
        $this->_em->persist($image);
        $this->_em->flush();
    }

    public function deleteImages(array $images): void
    {
        foreach ($images as $image) {
            $this->_em->remove($image);
        }

        $this->_em->flush();
    }
}
