<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Attribute\AsTwigFunction;
use Symfony\Component\Intl\Locales;

/**
 * Class AppExtension
 *
 * @package App\Twig
 */
class AppExtension
{
    private ?array $localeCodes = null;

    private ?array $locales = null;

    /**
     * AppExtension constructor.
     *
     * @param string $locales
     */
    public function __construct(string $locales)
    {
        $this->localeCodes = explode('|', $locales);
    }

    /**
     * @return array
     */
    #[AsTwigFunction(name: 'locales')]
    public function getLocales(): array
    {
        if (null !== $this->locales) {
            return $this->locales;
        }

        $this->locales = [];

        foreach ($this->localeCodes as $localeCode) {
            $this->locales[] = ['code' => $localeCode, 'name' => Locales::getName($localeCode, $localeCode)];
        }

        return $this->locales;
    }
}
