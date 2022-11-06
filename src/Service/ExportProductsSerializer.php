<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\Serializer\SerializerInterface;

class ExportProductsSerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Product[] $products
     * @param string $format
     * @return string
     */
    public function serialize(array $products, string $format): string
    {
        return $this->serializer->serialize($products, $format, ['groups' => 'export']);
    }
}
