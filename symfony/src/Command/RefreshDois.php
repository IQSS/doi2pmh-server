<?php /** @noinspection PhpUnused */

namespace App\Command;

use App\Services\DoiService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshDois extends Command
{
    const SUCCESS = 0;
    private DoiService $doiService;

    public function __construct(DoiService $doiService)
    {
        $this->doiService = $doiService;

        parent::__construct('doi:refresh');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Fetching all content...' . PHP_EOL);

        $count = $this->doiService->refreshDois($output);

        $output->write($count . ' dois updated with success' . PHP_EOL);
        return self::SUCCESS;
    }
}
