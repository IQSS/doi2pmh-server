<?php
// api/src/Serializer/PlainIdentifierDenormalizer

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Doi;
use App\Entity\Folder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/** from https://api-platform.com/docs/core/serialization/#plain-identifiers-for-symfony */
class PlainIdentifierDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $data['folder'] = $this->iriConverter->getIriFromResource(resource: Folder::class, context: ['uri_variables' => ['id' => $data['folder']]]);
        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \in_array($format, ['json', 'jsonld'], true) && is_a($type, Doi::class, true) && !empty($data['folder']) && !isset($context[__CLASS__]);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => null,
            '*' => false,
            Doi::class => true,
        ];
    }
}