<?php

namespace Sunnysideup\RemoveOrphanedElementals;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use SilverStripe\Versioned\Versioned;

class RemoveOrphanedElements extends BuildTask
{
    private static $segment = 'remove-orphaned-elements';

    protected $title = 'Remove orphaned elements from the database.';

    protected $description = 'Checks for orphaned elements and elemental areas and deletes them.';

    protected $enabled = true;

    protected $confirmed = false;

    public function run($request)
    {
        $confirmMessage = '
==========================================================
Please add ?confirm=1 to the url
(or from the command line run: vendor/bin/sake dev/tasks/remove-orphaned-elements confirm=1)
to confirm deletion.
==========================================================

';
        if ($request && $request->getVar('confirm')) {
            $this->confirmed = (bool) $request->getVar('confirm');
        }
        if (! $this->confirmed) {
            echo $confirmMessage;
        } else {
            DB::alteration_message('Confirmed deletion.');
        }
        DB::alteration_message(
            'Checking for orphaned element areas',
        );

        $elementalArea = ElementalArea::get();
        foreach ($elementalArea as $area) {
            $ownerPage = $area->getOwnerPage();

            if ($ownerPage && $ownerPage->exists()) {
                echo '✓';
            } else {
                echo PHP_EOL;
                DB::alteration_message(
                    'Removing: ' . $area->getTitle(),
                    'deleted'
                );

                if ($this->confirmed) {
                    $area->deleteFromStage(Versioned::DRAFT);
                    $area->deleteFromStage(Versioned::LIVE);
                }
            }
        }
        echo PHP_EOL;
        DB::alteration_message(
            'Checking for orphaned elements',
        );

        $elements = BaseElement::get();
        foreach ($elements as $element) {
            $area = $element->Parent();
            if ($area && $area->exists()) {
                echo '✓';
            } else {
                echo PHP_EOL;
                DB::alteration_message(
                    'Removing: ' . $element->getTitle(),
                    'deleted'
                );
                if ($this->confirmed) {
                    $element->deleteFromStage(Versioned::DRAFT);
                    $element->deleteFromStage(Versioned::LIVE);
                }
            }
        }
        echo PHP_EOL;

        if ($this->confirmed) {
            DB::alteration_message(
                'Removed all orphaned elements and elemental areas.',
                'created'
            );
        } else {
            echo $confirmMessage;
        }
    }
}
