<?php
namespace App\Ui\Console;

use App\Domain\CoinBox;
use App\Infrastructure\Repository\ItemRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Run extends Command
{
    protected static $defaultName = 'run';

    protected ?string $flashMessage = null;

    private const TITLE = <<<TITLE
_  _ ____ _  _ ___  _ _  _ ____    _  _ ____ ____ _  _ _ _  _ ____ 
|  | |___ |\ | |  \ | |\ | | __    |\/| |__| |    |__| | |\ | |___ 
 \/  |___ | \| |__/ | | \| |__]    |  | |  | |___ |  | | | \| |___
TITLE;


    /** @var ItemRepository */
    private ItemRepository $itemRepository;

    /** @var CoinBox */
    private CoinBox $coinBox;

    protected function configure(): void
    {
        $this
            ->setDescription('Start the vending machine app')
        ;
    }

    public function __construct(string $name = null)
    {
        $this->itemRepository = new ItemRepository();
        $this->coinBox = new CoinBox();
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mainMenuQuestion = new Question("Please select an option: ");
        $questionHelper = $this->getHelper('question');
        $headerSection = $output->section();
        $catalogSection = $output->section();
        $availableChangeSection = $output->section();
        $menuSection = $output->section();

        do {
            $output->write(sprintf("\033\143"));
            $this->displayHeader($headerSection);
            $this->displayItems($catalogSection);
            $this->displayAvailableChange($availableChangeSection);
            $this->displayMenu($menuSection);
            $mainMenuOption = strtoupper($questionHelper->ask($input, $output, $mainMenuQuestion));
            switch ($mainMenuOption) {
                case "1":
                    $coinQuestion = new ChoiceQuestion(
                        'Please select the coin do you want to add',
                        CoinBox::getAvailableCoinsForDisplay()
                    );
                    $coinQuestion->setAutocompleterValues(null);
                    $coinOption = $questionHelper->ask($input, $output, $coinQuestion);

                    $quantityQuestion = new Question("How many $coinOption do you want to add:");

                    $quantity = $questionHelper->ask($input, $menuSection, $quantityQuestion);
                    $this->coinBox->addUserCoin($coinOption, $quantity);
                    $this->flashMessage = "{$quantity}x{$coinOption} coins added to your credit";
                    break;

                case "2":
                    $itemQuestion = new ChoiceQuestion(
                        'Please select the item you want to buy',
                        array_column($this->itemRepository->findAll(), 'name')
                    );
                    $itemOption = $questionHelper->ask($input, $output, $itemQuestion);

                    $item = $this->itemRepository->findItemByName($itemOption);
                    if ($this->coinBox->getAvailableCredit() < $item['price']) {
                        $this->flashMessage = "Sorry, you don't have enough credit to buy " . $item['name'];
                        break;
                    }

                    if (!$this->itemRepository->itemHasStock($itemOption)) {
                        $this->flashMessage = "Sorry, there's no stock available of " . $itemOption;
                        break;
                    }

                    $change = $this->coinBox->getAvailableCredit() - $item['price'];
                    if (!$this->coinBox->isChangeAvailable($change)) {
                        $this->flashMessage = "Sorry, there's no change available to return " . $change;
                        break;
                    }

                    $this->itemRepository->changeStock($itemOption, -1);
                    $this->coinBox->chargeUser($item['price']);
                    $this->flashMessage = "Enjoy your {$item['name']}";
                    if ($this->coinBox->getAvailableCredit() > 0) {
                        $refund = $this->coinBox->refundUserCredit();
                        $this->flashMessage .= ", your change is " . implode(', ', array_map(
                                function ($quantity, $coin) { return "$quantity $coin coins"; },
                                $refund,
                                array_keys($refund)
                            ));
                    }

                    break;

                case "3":
                    $this->coinBox->refundUserCredit();
                    $output->writeln("The user credit has been refunded");
                    $questionHelper->ask($input, $menuSection, new Question("Press any key to continue"));
                    break;

                case "A":
                    $stockQuestion = new ChoiceQuestion(
                        'Please select what item do you want to add stock',
                        array_column($this->itemRepository->findAll(), 'name')
                    );
                    $stockOption = $questionHelper->ask($input, $output, $stockQuestion);

                    $quantityQuestion = new Question("How many <fg=green>$stockOption</> units do you want to add: ");

                    $quantity = $questionHelper->ask($input, $menuSection, $quantityQuestion);
                    $this->itemRepository->changeStock($stockOption, $quantity);
                    $this->flashMessage = "$quantity $stockOption units added to the stock";
                    break;

                case "B":
                    $coinQuestion = new ChoiceQuestion(
                        'Please select the coin do you want to add',
                        CoinBox::getAvailableCoinsForDisplay()
                    );
                    $coinQuestion->setAutocompleterValues(null);
                    $coinOption = $questionHelper->ask($input, $output, $coinQuestion);

                    $quantityQuestion = new Question("How many $coinOption coins do you want to add:");

                    $quantity = $questionHelper->ask($input, $menuSection, $quantityQuestion);
                    $output->writeln($coinOption);
                    $this->coinBox->addCoin($coinOption, $quantity);
                    break;

                case "C":
                    $this->coinBox
                        ->addCoin("0.05", 50)
                        ->addCoin("0.25", 50)
                        ->addCoin("1", 50);
                    $this->itemRepository
                        ->changeStock('Soda', 50)
                        ->changeStock('Water', 50)
                        ->changeStock('Juice', 50);
                    break;
            }

        } while ($mainMenuOption !== "0");

        return Command::SUCCESS;
    }

    private function displayHeader(ConsoleSectionOutput $headerSection): void
    {
        $headerSection->clear();
        $headerSection->writeln("<fg=green>" . self::TITLE . "</>");
        $message = $this->flashMessage ? $this->flashMessage : ' Coolest vending machine in town';
        $headerSection->writeln("<fg=black;bg=yellow>   " . $message . "   \n</>");
        $headerSection->writeln("<fg=white;bg=red>   You have {$this->coinBox->getAvailableCredit()} available   \n</>");
        $this->flashMessage = null;
    }

    private function displayItems(ConsoleSectionOutput $catalogSection): void
    {
        $items = $this->itemRepository->findAll();

        $catalogSection->clear();
        $table = new Table($catalogSection);
        $table
            ->setStyle('box-double')
            ->setHeaderTitle('Our products')
            ->setHeaders(['Item', 'Price', 'Stock'])
            ->setColumnWidth(0, 20)
            ->setColumnWidth(1, 10)
            ->setColumnWidth(1, 10)
            ->setRows($items)
        ;
        $table->render();
    }

    private function displayAvailableChange(ConsoleSectionOutput $availableChangeSection): void
    {
        $coins = $this->coinBox->getCoins();

        $availableChangeSection->clear();
        $availableChangeSection->writeln("\n<bg=yellow;fg=black;options=bold>  " . $this->coinBox->getTotalAvailable() . " </> available for change, coin details:");
        $table = new Table($availableChangeSection);
        $table
            ->setStyle('box')
            ->setHeaders(array_keys($coins))
            ->addRow($coins)
        ;
        $table->render();
    }

    private function displayMenu(ConsoleSectionOutput $menuSection): void
    {
        $menuSection->overwrite(
            <<<MENU
<fg=yellow>
   ╔═════════════ User options ═══════════════╗
   ║   1. Insert coins                        ║
   ║   2. Buy a drink                         ║
   ║   3. Refund credit                       ║
   ║   <fg=red>0. Exit</>                                ║
   ╚══════════════════════════════════════════╝
</><fg=blue>
   ╔══════════════ Maintenance ═══════════════╗
   ║   A. Add stock to items                  ║
   ║   B. Add more coins for change           ║
   ║   <fg=cyan>C. Add plenty of stock and change</>      ║
   ╚══════════════════════════════════════════╝
</>
MENU
        );
    }
}
