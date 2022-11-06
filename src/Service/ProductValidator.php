<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Product;
use InvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Product $product
     * @throws InvalidArgumentException
     */
    public function validate(Product $product): void
    {
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $invalidFields = [];

            /** @var ConstraintViolationInterface $error */
            foreach ($errors as $error) {
                $invalidFields[] = $error->getPropertyPath();
            }

            throw new InvalidArgumentException(implode(', ', $invalidFields));
        }
    }
}
