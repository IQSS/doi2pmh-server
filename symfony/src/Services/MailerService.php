<?php

namespace App\Services;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerService {

    private Configuration $configuration;

    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    )
    {
        $this->configuration = Configuration::getConfigurationInstance($this->entityManager);
    }

    /**
     * Send Email
     * @param string $to
     * @param string $from
     * @param string $subject
     * @param string $templateName
     * @param array $context
     * @throws TransportExceptionInterface
     */
    public function sendEmail(string $to, string $from, string $subject, string $templateName, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->from($from)
            ->subject($subject)
            ->htmlTemplate($templateName)
            ->context($context);
        $this->mailer->send($email);
    }

    /**
     * Send password to the new user
     * @param string $email
     * @param string $plainPassword
     * @throws TransportExceptionInterface
     */
    public function sendPassword(string $email, string $plainPassword): void
    {
        $this->sendEmail(
            $email,
            $this->configuration->getAdminEmail(),
            $this->translator->trans('admin.email.addUser.subject'),
            'admin/email/addUser.html.twig',
            [
                'plainPassword' => $plainPassword,
                'userEmail' => $email,
                'adminEmail' => $this->configuration->getAdminEmail(),
                'isCasAuthentication' => $this->configuration->isCasAuthentication()
            ]
        );
    }

}
