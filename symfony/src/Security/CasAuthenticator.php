<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Configuration;
use App\Entity\User;
use App\Services\FolderService;
use phpCAS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Class CasAuthenticator
 *
 * @package App\Security
 */
class CasAuthenticator extends AbstractAuthenticator implements EventSubscriberInterface
{
    use TargetPathTrait;

    private Configuration $repoConfiguration;


    /**
     * CasAuthenticator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param FolderService $folderService
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private FolderService $folderService,
    ) {
        $this->repoConfiguration = Configuration::getConfigurationInstance($this->entityManager);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    #[\Override]
    public function supports(Request $request): bool
    {
        return $this->repoConfiguration->isCasAuthentication();
    }

    #[\Override]
    public function authenticate(Request $request): Passport
    {
        if ($this->repoConfiguration->isCasAuthentication()) {
            phpCAS::setLogger();
            phpCAS::setVerbose(true);
            if (!phpCAS::isInitialized()) {
                phpCAS::client(
                    $this->repoConfiguration->getCasVersion(),
                    $this->repoConfiguration->getCasHost(),
                    $this->repoConfiguration->getCasPort(),
                    $this->repoConfiguration->getCasUri(),
                    $this->repoConfiguration->getCasServiceBaseUri(),
                );
            }
            phpCAS::setLang(PHPCAS_LANG_FRENCH);
            phpCAS::setNoCasServerValidation();
            phpCAS::setFixedServiceURL('https:' . $this->urlGenerator->generate('folder_index', ['id' => $this->folderService->getRootFolder()->getId()], UrlGeneratorInterface::NETWORK_PATH));
            phpCAS::forceAuthentication();

            $request->getSession()->set(
                Security::LAST_USERNAME,
                phpCAS::getUser()
            );

            if (phpCAS::getUser()) {
                return new SelfValidatingPassport(new UserBadge(phpCAS::getUser()));
            }

            throw new CustomUserMessageAuthenticationException('Error during cas authentification');
        }
        throw new CustomUserMessageAuthenticationException('CAS authentification disabled');
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param mixed          $providerKey
     *
     * @return null|Response
     */
    #[\Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        if ($this->repoConfiguration->isCasAuthentication()) {
            if (phpCAS::isInitialized()) {
                $token->setAttributes(phpCAS::getAttributes());
            }
            return null;
        } else {
            if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
                return new RedirectResponse($targetPath);
            }
            return new RedirectResponse($this->urlGenerator->generate('folder_index', ['id' => $this->folderService->getRootFolder()->getId()]));
        }
    }

    /**
     * @return string
     */
    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate('security_login');
    }

    #[\Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
        $url = $this->getLoginUrl();

        return $this->repoConfiguration->isCasAuthentication() ? null : new RedirectResponse($url);
    }

    public function onLogout(LogoutEvent $logoutEvent): void
    {
        if ($logoutEvent->getResponse() !== null) {
            return;
        }
        if ($this->repoConfiguration->isCasAuthentication()) {
            if (!phpCAS::isInitialized()) {
                phpCAS::client(
                    $this->repoConfiguration->getCasVersion(),
                    $this->repoConfiguration->getCasHost(),
                    $this->repoConfiguration->getCasPort(),
                    $this->repoConfiguration->getCasUri(),
                    $this->repoConfiguration->getCasServiceBaseUri(),

                );
            }
            phpCAS::setLang(PHPCAS_LANG_FRENCH);

            //simple logout
            phpCAS::logout();
        }
        $url = $this->getLoginUrl();
        $logoutEvent->setResponse(new RedirectResponse($url));
    }
    /**
     * @return array<string, mixed>
     */
    #[\Override] 
    public static function getSubscribedEvents(): array
    {
        return [LogoutEvent::class => ['onLogout', 64]];
    }
}
