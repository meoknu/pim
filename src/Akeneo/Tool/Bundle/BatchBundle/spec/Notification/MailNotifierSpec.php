<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class MailNotifierSpec extends ObjectBehavior
{
    function let(
        LoggerInterface       $logger,
        TokenStorageInterface $tokenStorage,
        Environment           $twig,
        MailNotifierInterface $mailer
    )
    {
        $twig->render(Argument::type('string'), Argument::type('array'))->willReturn('');
        $this->beConstructedWith($logger, $tokenStorage, $twig, $mailer, 'null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com');
        $this->setRecipientEmail('destEmail');
    }

    function it_notifies(JobExecution $jobExecution, $mailer)
    {
        $mailer->notifyByEmail(
            'destEmail',
            'Job has been executed',
            Argument::any(),
            Argument::any()
        )->shouldBeCalled();

        $this->notify($jobExecution);
    }

    function it_should_log_error_if_notification_failed(JobExecution $jobExecution, $mailer, $logger)
    {
        $mailer->notifyByEmail(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willThrow(\Throwable::class);

        $logger->error(Argument::any(), Argument::any())->shouldBeCalled();

        $this->notify($jobExecution);
    }
}
