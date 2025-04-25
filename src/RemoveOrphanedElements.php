<?php

namespace Sunnysideup\RemoveOrphanedElementals;

class RemoveOrphanedElements extends BuildTask
{
    protected $segment = 'remove-orphaned-elements';

    protected $description = 'Remove orphaned elements from the database.';

    protected $enabled = true;

    public function run($request)
    {
        // Your code to remove orphaned elements goes here
        // For example:
        // $this->removeOrphanedElements();
    }
}
