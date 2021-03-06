<?php

namespace FOS\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use FOS\UserBundle\Model\User;

/*
 * This file is part of the FOS\UserBundle
 *
 * (c) Matthieu Bontemps <matthieu@knplabs.com>
 * (c) Thibault Duplessis <thibault.duplessis@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * DemoteSuperAdminCommand.
 *
 * @package    Bundle
 * @subpackage FOS\UserBundle
 * @author     Antoine Hérault <antoine.herault@gmail.com>
 */
class DemoteSuperAdminCommand extends BaseCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('fos:user:demote')
            ->setDescription('Demote a super administrator as a simple user')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            ))
            ->setHelp(<<<EOT
The <info>fos:user:demote</info> command demotes a super administrator as a simple user

  <info>php app/console fos:user:demote matthieu</info>
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('security.context')->setToken(new UsernamePasswordToken('command.line', null, array(User::ROLE_SUPERADMIN)));

        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($input->getArgument('username'));

        if (!$user) {
            throw new \InvalidArgumentException(sprintf('The user "%s" does not exist', $input->getArgument('username')));
        }
        $user->setSuperAdmin(false);

        $userManager->updateUser($user);

        $output->writeln(sprintf('Super administrator "%s" has been demoted as a simple user.', $user->getUsername()));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('username')) {
            $username = $this->getHelper('dialog')->askAndValidate(
                $output,
                'Please choose a username:',
                function($username)
                {
                    if (empty($username)) {
                        throw new \Exception('Username can not be empty');
                    }
                    return $username;
                }
            );
            $input->setArgument('username', $username);
        }
    }
}
