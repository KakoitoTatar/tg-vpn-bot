<?php

declare(strict_types=1);

use App\Domain\Client\Client;
use App\Domain\Client\ClientRepositoryInterface;
use App\Domain\Connection\Connection;
use App\Domain\Connection\ConnectionRepositoryInterface;
use App\Domain\Hosting\Hosting;
use App\Domain\Hosting\HostingRepositoryInterface;
use App\Domain\Instance\Instance;
use App\Domain\Instance\InstanceRepositoryInterface;
use App\Domain\Mail\Mail;
use App\Domain\Mail\MailRepositoryInterface;
use App\Domain\Media\Media;
use App\Domain\Media\MediaRepositoryInterface;
use App\Domain\Payments\Payment;
use App\Domain\Payments\PaymentRepositoryInterface;
use App\Domain\Promocode\Promocode;
use App\Domain\Promocode\PromocodeRepositoryInterface;
use App\Domain\Rate\Rate;
use App\Domain\Rate\RateRepositoryInterface;
use App\Domain\Saloon\Appointment\Appointment;
use App\Domain\Saloon\Appointment\AppointmentRepositoryInterface;
use App\Domain\Saloon\Customer\Customer;
use App\Domain\Saloon\Customer\CustomerRepositoryInterface;
use App\Domain\Saloon\Service\Service;
use App\Domain\Saloon\Service\ServiceRepositoryInterface;
use App\Domain\Saloon\Specialist\Specialist;
use App\Domain\Saloon\Specialist\SpecialistRepositoryInterface;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Doctrine\Client\ClientRepository;
use App\Infrastructure\Doctrine\Connection\ConnectionRepository;
use App\Infrastructure\Doctrine\Hosting\HostingRepository;
use App\Infrastructure\Doctrine\Instance\InstanceRepository;
use App\Infrastructure\Doctrine\Mail\MailRepository;
use App\Infrastructure\Doctrine\Media\MediaRepository;
use App\Infrastructure\Doctrine\Payment\PaymentRepository;
use App\Infrastructure\Doctrine\Promocode\PromocodeRepository;
use App\Infrastructure\Doctrine\Rate\RateRepository;
use App\Infrastructure\Doctrine\Saloon\Appointment\AppointmentRepository;
use App\Infrastructure\Doctrine\Saloon\Customer\CustomerRepository;
use App\Infrastructure\Doctrine\Saloon\Service\ServiceRepository;
use App\Infrastructure\Doctrine\Saloon\Specialist\SpecialistRepository;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        UserRepository::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(User::class);
            return new \App\Infrastructure\Doctrine\User\UserRepository($em, $cm);
        },
        MailRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Mail::class);
            return new MailRepository($em, $cm);
        },
        MediaRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Media::class);
            return new MediaRepository($em, $cm);
        },
        ClientRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Client::class);
            return new ClientRepository($em, $cm);
        },
        RateRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Rate::class);
            return new RateRepository($em, $cm);
        },
        InstanceRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Instance::class);
            return new InstanceRepository($em, $cm);
        },
        HostingRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Hosting::class);
            return new HostingRepository($em, $cm);
        },
        PaymentRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Payment::class);
            return new PaymentRepository($em, $cm);
        },
        ConnectionRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Connection::class);
            return new ConnectionRepository($em, $cm);
        },
        PromocodeRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Promocode::class);
            return new PromocodeRepository($em, $cm);
        },
        CustomerRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Customer::class);
            return new CustomerRepository($em, $cm);
        },
        AppointmentRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Appointment::class);
            return new AppointmentRepository($em, $cm);
        },
        SpecialistRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Specialist::class);
            return new SpecialistRepository($em, $cm);
        },
        ServiceRepositoryInterface::class => function (ContainerInterface $c) {
            $em = $c->get(EntityManagerInterface::class);
            $cm = $em->getMetadataFactory()->getMetadataFor(Service::class);
            return new ServiceRepository($em, $cm);
        },
    ]);
};
