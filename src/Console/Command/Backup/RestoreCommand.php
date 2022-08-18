<?php

declare(strict_types=1);

namespace App\Console\Command\Backup;

use App\Entity\StorageLocation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

use const PATHINFO_EXTENSION;

#[AsCommand(
    name: 'azuracast:restore',
    description: 'Restore a backup previously generated by AzuraCast.',
)]
final class RestoreCommand extends AbstractBackupCommand
{
    protected function configure(): void
    {
        $this->addArgument('path', InputArgument::OPTIONAL)
            ->addOption('restore', null, InputOption::VALUE_NONE)
            ->addOption('release', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');
        $start_time = microtime(true);

        $io->title('AzuraCast Restore');

        if (empty($path)) {
            $filesRaw = glob(StorageLocation::DEFAULT_BACKUPS_PATH . '/*', GLOB_NOSORT) ?: [];
            usort(
                $filesRaw,
                static fn($a, $b) => filemtime($b) <=> filemtime($a)
            );

            if (0 === count($filesRaw)) {
                $io->getErrorStyle()
                    ->error('Backups directory has no available files. You must explicitly specify a backup file.');
                return 1;
            }

            $files = [];
            $i = 1;
            foreach ($filesRaw as $filePath) {
                $files[$i] = basename($filePath);

                if (10 === $i) {
                    break;
                }
                $i++;
            }

            $path = $io->choice('Select backup file to restore:', $files, 1);
        }

        if ('/' !== $path[0]) {
            $path = StorageLocation::DEFAULT_BACKUPS_PATH . '/' . $path;
        }

        if (!file_exists($path)) {
            $io->getErrorStyle()->error(
                sprintf(
                    __('Backup path %s not found!'),
                    $path
                )
            );
            return 1;
        }

        $io->writeln('Please wait while the backup is restored...');

        // Extract tar.gz archive
        $io->section('Extracting backup file...');

        $file_ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($file_ext) {
            case 'tzst':
                $this->passThruProcess(
                    $io,
                    [
                        'tar',
                        '-I',
                        'unzstd',
                        '-xvf',
                        $path,
                    ],
                    '/'
                );
                break;

            case 'gz':
            case 'tgz':
                $this->passThruProcess(
                    $io,
                    [
                        'tar',
                        'zxvf',
                        $path,
                    ],
                    '/'
                );
                break;

            case 'zip':
            default:
                $this->passThruProcess(
                    $io,
                    [
                        'unzip',
                        $path,
                    ],
                    '/'
                );
                break;
        }

        $io->newLine();

        // Handle DB dump
        $io->section('Importing database...');

        $tmp_dir_mariadb = '/tmp/azuracast_backup_mariadb';
        $path_db_dump = $tmp_dir_mariadb . '/db.sql';

        if (!file_exists($path_db_dump)) {
            $io->getErrorStyle()->error('Database backup file not found!');
            return 1;
        }

        $conn = $this->em->getConnection();

        // Drop all preloaded tables prior to running a DB dump backup.
        $conn->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($conn->fetchFirstColumn('SHOW TABLES') as $table) {
            $conn->executeQuery('DROP TABLE IF EXISTS ' . $conn->quoteIdentifier($table));
        }
        $conn->executeQuery('SET FOREIGN_KEY_CHECKS = 1');

        [$commandFlags, $commandEnvVars] = $this->getDatabaseSettingsAsCliFlags();

        $commandEnvVars['DB_DUMP'] = $path_db_dump;

        $this->passThruProcess(
            $io,
            'mysql ' . implode(' ', $commandFlags) . ' $DB_DATABASE < $DB_DUMP',
            $tmp_dir_mariadb,
            $commandEnvVars
        );

        (new Filesystem())->remove($tmp_dir_mariadb);
        $io->newLine();

        // Update from current version to latest.
        $io->section('Running standard updates...');

        $this->runCommand($output, 'azuracast:setup', ['--update' => true]);

        $end_time = microtime(true);
        $time_diff = $end_time - $start_time;

        $io->success(
            [
                'Restore complete in ' . round($time_diff, 3) . ' seconds.',
            ]
        );
        return 0;
    }
}
