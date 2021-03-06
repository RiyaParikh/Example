<?php

namespace Zeeshan\GitProfile\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * @author    Zeeshan Ahmed <ziishaned@gmail.com>
 * @copyright 2016 Zeeshan Ahmed
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */
class BaseCommand extends Command
{
    /**
     * Run the command.
     *
     * @param string $command
     * @param bool   $mustRun
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function runCommand($command, $mustRun = false)
    {
        try {
            $process = new Process($command);
            $process->mustRun();

            return trim($process->getOutput());
        } catch (\Exception $e) {
            if ($mustRun) {
                throw $e;
            }

            return '';
        }
    }

    /**
     * Switch git profile.
     *
     * @param string $profileTitle
     * @param string $flag
     *
     * @return mixed
     */
    public function switchProfile($profileTitle, $flag = null)
    {
        if (null !== $flag) {
            $this->runCommand('git config --global current-profile.name ' . $profileTitle);

            return true;
        }

        $this->runCommand('git config current-profile.name ' . $profileTitle);

        return true;
    }

    /**
     * Retrieve current git profile.
     *
     * @param bool $global
     *
     * @return string
     */
    public function retrieveCurrentProfile($global = false)
    {
        if ($global) {
            return $this->runCommand('git config --global current-profile.name');
        }

        return $this->runCommand('git config current-profile.name');
    }

    /**
     * Check wether or not git profile exist.
     *
     * @param string $profileTitle
     *
     * @return bool
     */
    public function doesProfileExists($profileTitle)
    {
        $commandOutput = $this->runCommand('git config --list');

        if (stripos($commandOutput, 'profile.' . $profileTitle)) {
            return true;
        }

        return false;
    }

    /**
     * On CLI ask quesion to user.
     *
     * @param string $question
     * @param string $message
     * @param bool   $required
     *
     * @return mixed
     */
    public function askQuestion($question, $message, $required = true)
    {
        $helper = $this->getHelper('question');
        $output = $this->output;

        $question = new Question($question);
        $style = new SymfonyStyle($this->input, $this->output);

        if ($required) {
            $question->setValidator(function ($value) use ($output, $message, $style) {
                if (empty($value)) {
                    throw new \Exception($message);
                }

                return $value;
            });
            $question->setMaxAttempts(1);
        }

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Save git profile.
     *
     * @param string $profileTitle
     * @param string $username
     * @param string $email
     * @param string $signingkey
     *
     * @return bool
     */
    public function saveProfile($profileTitle, $username, $email, $signingkey = '')
    {
        $mustRun = true;

        $this->runCommand(sprintf('git config --global profile."%s".name "%s"', $profileTitle, $username), $mustRun);
        $this->runCommand(sprintf('git config --global profile."%s".email "%s"', $profileTitle, $email), $mustRun);

        if ($signingkey) {
            $this->runCommand(sprintf('git config --global profile."%s".signingkey "%s"', $profileTitle, $signingkey), $mustRun);
        }

        return true;
    }
}
