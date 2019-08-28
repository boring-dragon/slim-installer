<?php
namespace Acme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\ClientInterface;
use ZipArchive;

class NewCommand extends Command{

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    public function configure()
    {
        $this->setName('new')
             ->setDescription('Create a new slim application.')
             ->addArgument('name', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = getcwd() . '/' . $input->getArgument('name');
        
        $output->writeln('<comment>Crafting The Slim Application.....</comment>');

        $this->assertApplicationDoesNotExist($directory, $output);

        $this->download($zipFile = $this->makeFileName())
             ->extract($zipFile, $directory)
             ->cleanUp($zipFile);

        $output->writeln('<info>Application Ready!!, Make something epic :)</info>');
    }

    private function assertApplicationDoesNotExist($directory, OutputInterface $output)
    {
        if (is_dir($directory))
        {
            $output->writeln('<error>Application already exist!</error>');
            exit(1);        
        }
    }

    private function makeFileName()
    {
        return getcwd() . '/slim_' . md5(time().uniqid()) . '.zip';
        
    }

    private function download($zipFile)
    {
        $response = $this->client->get('https://jinas.me/slim.zip')->getBody();

        file_put_contents($zipFile, $response);

        return $this;
    }

    public function extract($zipFile, $directory)
    {

        $archive = new ZipArchive;

        $archive->open($zipFile);
        $archive->extractTo($directory);
        $archive->close();

        return $this;

    }

    public function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);

        return $this;

    }



}    