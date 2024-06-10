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
    private const MAIN_MENU = [
        'list Top',
        'find by ticker',
        'buy',
        'sell',
        'wallet',
        'x-actions',
        'exit'
    ];
    public static function showTable(
        array $tColumn,
        array $tContent,
        string $tHeader = "Table",
        string $tFooter = ""): void
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
    public static function menu(string $query, array $options = self::MAIN_MENU): string
    {
        $output = new ConsoleOutput();
        $input = new ArgvInput();
        $helper = new QuestionHelper();
        $choice = new ChoiceQuestion($query, $options);
        $choice->setErrorMessage('Option %s is invalid.');
        return $helper->ask($input, $output, $choice);
    }
    public static function question(string $question): string
    {
        $output = new ConsoleOutput();
        $input = new ArgvInput();
        $helper = new QuestionHelper();
        $question = new ConfirmationQuestion($question);
        return $helper->ask($input, $output, $question);
    }
    public static function getMainMenu(): array
    {
        return self::MAIN_MENU;
    }
}


