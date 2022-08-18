<?php

use App\Console\Command;

return function (App\Event\BuildConsoleCommands $event) {
    $event->addAliases([
        'HeroPanel:acme:get-certificate' => Command\Acme\GetCertificateCommand::class,
        'HeroPanel:backup' => Command\Backup\BackupCommand::class,
        'HeroPanel:restore' => Command\Backup\RestoreCommand::class,
        'HeroPanel:debug:optimize-tables' => Command\Debug\OptimizeTablesCommand::class,
        'HeroPanel:internal:on-ssl-renewal' => Command\Internal\OnSslRenewal::class,
        'HeroPanel:internal:ip' => Command\Internal\GetIpCommand::class,
        'HeroPanel:locale:generate' => Command\Locale\GenerateCommand::class,
        'HeroPanel:locale:import' => Command\Locale\ImportCommand::class,
        'HeroPanel:queue:process' => Command\MessageQueue\ProcessCommand::class,
        'HeroPanel:queue:clear' => Command\MessageQueue\ClearCommand::class,
        'HeroPanel:settings:list' => Command\Settings\ListCommand::class,
        'HeroPanel:settings:set' => Command\Settings\SetCommand::class,
        'HeroPanel:station-queues:clear' => Command\ClearQueuesCommand::class,
        'HeroPanel:account:list' => Command\Users\ListCommand::class,
        'HeroPanel:account:login-token' => Command\Users\LoginTokenCommand::class,
        'HeroPanel:account:reset-password' => Command\Users\ResetPasswordCommand::class,
        'HeroPanel:account:set-administrator' => Command\Users\SetAdministratorCommand::class,
        'HeroPanel:cache:clear' => Command\ClearCacheCommand::class,
        'HeroPanel:setup:initialize' => Command\InitializeCommand::class,
        'HeroPanel:config:migrate' => Command\MigrateConfigCommand::class,
        'HeroPanel:setup:fixtures' => Command\SetupFixturesCommand::class,
        'HeroPanel:setup' => Command\SetupCommand::class,
        'HeroPanel:radio:restart' => Command\RestartRadioCommand::class,
        'HeroPanel:sync:nowplaying' => Command\Sync\NowPlayingCommand::class,
        'HeroPanel:sync:nowplaying:station' => Command\Sync\NowPlayingPerStationCommand::class,
        'HeroPanel:sync:run' => Command\Sync\RunnerCommand::class,
        'HeroPanel:sync:task' => Command\Sync\SingleTaskCommand::class,
        'HeroPanel:media:reprocess' => Command\ReprocessMediaCommand::class,
        'HeroPanel:api:docs' => Command\GenerateApiDocsCommand::class,
        'locale:generate' => Command\Locale\GenerateCommand::class,
        'locale:import' => Command\Locale\ImportCommand::class,
        'queue:process' => Command\MessageQueue\ProcessCommand::class,
        'queue:clear' => Command\MessageQueue\ClearCommand::class,
        'cache:clear' => Command\ClearCacheCommand::class,
        'acme:cert' => Command\Acme\GetCertificateCommand::class,
    ]);
};
