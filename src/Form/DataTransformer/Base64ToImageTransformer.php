<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Base64ToImageTransformer implements DataTransformerInterface
{
    private ?string $originalBase64 = null;

    public function transform($file): ?string
    {
        if ($file instanceof File && $file->getRealPath()) {
            $type = pathinfo($file->getRealPath(), PATHINFO_EXTENSION);
            $data = file_get_contents($file->getRealPath());
            $this->originalBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);

            return $this->originalBase64;
        }

        return null;
    }

    public function reverseTransform($base64): ?UploadedFile
    {
        if (null === $base64 || $base64 === $this->originalBase64) {
            return null;
        }

        preg_match('#data:(image/([\w]+));base64,(.*)#', $base64, $matches);
        $data = base64_decode($matches[3]);
        $name = uniqid('col_').'.'.$matches[2];
        if (!file_exists('tmp/')) {
            if (!mkdir('tmp/', 0777, true)) {
                throw new \Exception('There was a problem while uploading the image. Please try again!');
            }
        }
        $path = 'tmp/'.$name;
        file_put_contents($path, $data);
        $file = new UploadedFile($path, $name, $matches[1], null, true);

        return $file;
    }
}
