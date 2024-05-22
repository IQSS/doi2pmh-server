<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NavigationMenuManager
 *
 * @package App\Services
 */
class NavigationMenuManager
{
    protected array $navigationTree;

    protected ?Request $currentRequest;

    /**
     * NavigationMenuManager constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     * @param RequestStack $requestStack
     * @param TokenStorageInterface $user
     */
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected RequestStack $requestStack,
        protected TokenStorageInterface $user,
        private EntityManagerInterface $entityManager
    ) {
        $this->currentRequest = $this->requestStack->getCurrentRequest();
        $this->navigationTree = $this->getNavigationTree();
    }

    public function getNavigationTree(): array
    {
        $navigationTree =  [
            'admin' => [
                [
                    'name' => 'admin.navigation.doi.label',
                    'route' => 'folder_index',
                    'class' => '',
                    'attributes' => [],
                    'type' => 'link',
                    'granted' => 'IS_AUTHENTICATED_FULLY'
                ],
                [
                    'name' => 'admin.navigation.user.list.label',
                    'route' => 'user_index',
                    'class' => '',
                    'attributes' => [],
                    'type' => 'link',
                    'granted' => 'ROLE_ADMIN'
                ],
                [
                    'name' => 'admin.navigation.configuration.label',
                    'route' => 'config_edit_index',
                    'class' => '',
                    'attributes' => [],
                    'type' => 'link',
                    'granted' => 'ROLE_ADMIN'
                ],
            ],
            'footer' => [
                [
                    'name' => 'admin.navigation.footer.contact',
                    'url' => 'mailto:' . Configuration::getConfigurationInstance($this->entityManager)->getAdminEmail(),
                    'class' => '',
                    'attributes' => [],
                    'type' => 'link',
                ],
            ],
        ];
        if (!Configuration::getConfigurationInstance($this->entityManager)->isCasAuthentication()) {
            $navigationTree['admin'][] = [
                'name' => 'admin.navigation.user.profile.label',
                'route' => 'user_edit',
                'class' => '',
                'attributes' => [],
                'type' => 'link',
                'granted' => 'IS_AUTHENTICATED_FULLY'
            ];
        }
        return $navigationTree;
    }


    /**
     * Returns generated menu entries for given scope.
     *
     * @param string $scope
     *
     * @return array
     */
    public function getLinks(string $scope = 'front'): array
    {
        if (!$this->currentRequest || empty($this->navigationTree[$scope])) {
            return [];
        }

        $entries = $this->navigationTree[$scope];
        $currentRoute = $this->currentRequest->attributes->get('_route');

        $outputLinks = [];

        foreach ($entries as &$entry) {
            if ($entry['type'] == 'separator' ) {
                $outputLinks[] = $entry;
                continue;
            }

            // Ensures we don't generate the link each time it's called !
            if (!empty($entry['route'])) {
                $entry['class'] .= ($entry['route'] == $currentRoute) ? ' active' : '';
            } else if (empty($entry['url']) && !isset($entry['childrenCallback'])) {
                continue;
            }

            // Disable button if marked as such !
            if (isset($entry['disabled'])) {
                $entry['class'] .= ' disabled';
                $entry['url'] = '#';
            }

            // Generate URL from route name or generate for the children.
            if (isset($entry['childrenCallback'])) {
                $entry['children'] = $this->{$entry['childrenCallback']}($entry['route']);
                $entry['class'] .= ' has-children';
                unset($entry['childrenCallback']);
                $entry['url'] = '#';
            } else if (empty($entry['url'])) {
                $entry['url'] = $this->getUrl($entry['route'], $entry['parameters'] ?? []);
            }

            // Unset it to tag it as "generated".
            unset($entry['route']);
            $outputLinks[] = $entry;
        }

        return $outputLinks;
    }

    /**
     * Returns the list of admin links with generated URLs.
     *
     * @return array
     */
    public function getAdminLinks(): array
    {
        return $this->getLinks('admin');
    }

    /**
     * Returns navigation links for footer.
     *
     * @return array
     */
    public function getFooterLinks(): array
    {
        return $this->getLinks('footer');
    }

    /**
     * Generate an URL from given route name.
     *
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    protected function getUrl(string $routeName, array $parameters): string
    {
        return $this->urlGenerator->generate($routeName, $parameters);
    }
}
