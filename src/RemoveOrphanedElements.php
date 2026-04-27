<?php

namespace Sunnysideup\RemoveOrphanedElementals;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Versioned\Versioned;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class RemoveOrphanedElements extends BuildTask
{
    protected static string $commandName = 'remove-orphaned-elements';

    protected string $title = 'Remove orphaned elements from the database.';

    protected static string $description = 'Checks for orphaned elements and elemental areas and deletes them.';

    private static bool $is_enabled = true;

    protected $confirmed = false;

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $confirmMessage = '
==========================================================
Please add ?confirm=1 to the url
(or from the command line run: vendor/bin/sake tasks:remove-orphaned-elements --confirm)
to confirm deletion.
==========================================================
';
        
        $this->confirmed = (bool) $input->getOption('confirm');

        if (!$this->confirmed) {
            $output->writeln($confirmMessage);
        } else {
            $output->writeln('<info>Confirmed deletion.</info>');
        }

        $output->writeln('Checking for orphaned element areas');
        
        $elementalArea = ElementalArea::get();
        foreach ($elementalArea as $area) {
            $ownerPage = $area->getOwnerPage();

            if ($ownerPage && $ownerPage->exists()) {
                $output->write('✓');
            } else {
                $output->writeln('');
                $output->writeln(
                    '<error>Removing: ' . $area->getTitle() . '</error>'
                );

                if ($this->confirmed) {
                    $area->deleteFromStage(Versioned::DRAFT);
                    $area->deleteFromStage(Versioned::LIVE);
                }
            }
        }

        $output->writeln('');
        $output->writeln('Checking for orphaned elements');
        
        $elements = BaseElement::get();
        foreach ($elements as $element) {
            $area = $element->Parent();
            if ($area && $area->exists()) {
                $output->write('✓');
            } else {
                $output->writeln('');
                $output->writeln(
                    '<error>Removing: ' . $element->getTitle() . '</error>'
                );
                if ($this->confirmed) {
                    $element->deleteFromStage(Versioned::DRAFT);
                    $element->deleteFromStage(Versioned::LIVE);
                }
            }
        }

        $output->writeln('');
        if ($this->confirmed) {
            $output->writeln(
                '<info>Removed all orphaned elements and elemental areas.</info>'
            );
        } else {
            $output->writeln($confirmMessage);
        }

        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('confirm', 'c', InputOption::VALUE_NONE, 'Confirm deletion of orphaned elements and elemental areas'),
        ];
    }
}
