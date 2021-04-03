#!/usr/bin/php
<?php

/*
spl_autoload_register(function($classPath){
    $classPath = str_replace("App\\", "src\\", $classPath);
    require_once "{$classPath}.php"; 
});
*/

require 'vendor/autoload.php';

use App\Result;
use App\Engine\Wikipedia\WikipediaEngine;
use App\Engine\Wikipedia\WikipediaParser;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

class QueryCommand extends Command
{
    protected static $defaultName = 'app:query';

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('query by term')
            ->addArgument('term',InputArgument::OPTIONAL, 'the term for the query to be executed (default: \'php\')');

    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $wikipediaEngine = new WikipediaEngine(new WikipediaParser(), HttpClient::create());
        $result = new Result(0,[]);
        $term = (empty($input->getArgument('term'))) ? 'php' : $input->getArgument('term');
        
        try
        {
            $result = $wikipediaEngine->search($term);
        }
        catch (Exception $e) {
            echo 'caught exception: ',  $e->getMessage(), "\n";
            return Command::FAILURE;
        }

        if($result->count() == 0){
            echo 'no results were found';
            return Command::SUCCESS;
        }

        echo("\n");
        echo("{$result->count()} result(s) found for {$term}");
        echo("\n");

        foreach($result as $resultItem){
            $rows[] = [$resultItem->getTitle(), $resultItem->getPreview()];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['title', 'preview'])
            ->setRows($rows)
        ;
        $table->render();

        return Command::SUCCESS;
    }
}

$app = new Application();
$app->add(new QueryCommand());
$app->run();
