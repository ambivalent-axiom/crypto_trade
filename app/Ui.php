<?php
namespace Ambax\CryptoTrade;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Helper\Table;

class Ui
{
    private array $menuOpt;

    public static function showTable(array $tColumn, array $tContent, string $tHeader = "Table", string $tFooter = "")
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table
            ->setHeaderTitle($tHeader)
            ->setStyle('box-double')
            ->setHeaders($tColumn)
            ->setRows($tContent)
            ->setFooterTitle($tFooter)
            ->render();
    }
}


