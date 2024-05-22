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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminAuthenticator
 *
 * @package App\Security
 */
class AdminAuthenticator extends AbstractGuardAuthenticator implements LogoutSuccessHandlerInterface
{
    use TargetPathTrait;

    private Configuration $repoConfiguration;


    /**
     * AdminAuthenticator constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param FolderService $folderService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private CsrfTokenManagerInterface $csrfTokenManager,
        private UserPasswordHasherInterface $passwordEncoder,
        private FolderService $folderService,
        private TranslatorInterface $translator
    ) {
        $this->repoConfiguration = Configuration::getConfigurationInstance($this->entityManager);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return ('security_login' === $request->attributes->get('_route')
            && $request->isMethod('POST')) || $this->repoConfiguration->isCasAuthentication();
    }

    /**
     * @param Request $request
     *
     * @return array|mixed
     */
    public function getCredentials(Request $request): ?array
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
                return ['email' => phpCAS::getUser()];
            }

            return null;
        } else {
            $credentials = [
                'email' => $request->request->get('email'),
                'password' => $request->request->get('password'),
                'csrf_token' => $request->request->get('_csrf_token'),
            ];
            $request->getSession()->set(
                Security::LAST_USERNAME,
                $credentials['email']
            );

            return $credentials;
        }
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        if (!$this->repoConfiguration->isCasAuthentication()) {
            $token = new CsrfToken('authenticate', $credentials['csrf_token']);
            if (!$this->csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException();
            }
        }

        /**
         * @var User $user
         */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // Fail authentication with a custom error !
            throw new CustomUserMessageAuthenticationException($this->translator->trans('admin.login.email.notFound'));
        }

        return $user;
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->repoConfiguration->isCasAuthentication() || $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param mixed          $providerKey
     *
     * @return null|Response
     */
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

    public function onLogoutSuccess(Request $request): RedirectResponse
    {
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
        return new RedirectResponse($url);
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->getLoginUrl());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }
        $url = $this->getLoginUrl();

        return $this->repoConfiguration->isCasAuthentication() ? null : new RedirectResponse($url);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
