<?php

namespace App\Console\Commands;

use App\Services\CanvasService;
use Exception;
use Illuminate\Console\Command;

class DeployCPanel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:cpanel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For setting the app up for deployment on a cPanel server.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        //        $this->installDependencies();
        $this->refreshCache();
        $this->checkEnvironment();
        $this->checkPublicHtml();
        $this->checkBuild();
        $this->copyPublic();
        $this->refreshDatabase();
        $this->info('Deployment complete');
    }

    private function installDependencies(): void
    {
        $this->info('Installing composer dependencies...');
        exec('composer install');
        $this->info('Composer dependencies installed');
    }

    private function refreshCache(): void
    {
        $this->info('Refreshing cache...');
        $this->call('cache:clear');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');
        $this->info('Cache refreshed');
    }

    private function checkEnvironment(): void
    {
        $this->info('Validating environment...');
        if (! file_exists(base_path() . '/.env')) {
            $this->error('The .env file does not exist.');
            $this->error('Skipping deployment');

            exit();
        }

        if (app()->environment() == 'production') {
            $this->error('This command should not be run in a production environment.');
            $this->error('Skipping deployment');

            exit();
        }

        if (! config('canvas.token') ||
            ! config('canvas.host') ||
            ! config('app.key') ||
            ! config('google.id') ||
            ! config('google.secret')
        ) {
            $this->error('The .env must define CANVAS_API_TOKEN, CANVAS_API_HOST, APP_KEY, GOOGLE_CLIENT_ID, and GOOGLE_CLIENT_SECRET.');
            $this->error('Skipping deployment');

            exit();
        }

        if (CanvasService::getSelf()->status() != 200) {
            $this->error('The Canvas API is unauthorized/unreachable. Check the CANVAS_API_HOST and CANVAS_API_TOKEN in .env');
            $this->error('Skipping deployment');

            exit();
        }

        $this->info('Environment validated');
    }

    private function checkPublicHtml(): void
    {
        if (! in_array('public_html', scandir(base_path() . '/..'))) {
            $this->error('The public_html directory does not exist in the parent directory of the app.');
            $this->error('Skipping deployment');

            exit();
        }

        if (! is_writable(base_path() . '/../public_html')) {
            $this->error('The public_html directory is not writable.');
            $this->error('Skipping deployment');

            exit();
        }
    }

    private function checkBuild(): void
    {
        if (! is_dir(base_path() . '/public/build')) {
            $this->error('The public/build directory does not exist.');
            $this->error('Skipping deployment');

            exit();
        }
    }

    /**
     * Asks what directory to copy the public files to.
     * Sets config('app.base') to the new directory.
     * Copies the public files to the new directory within public_html
     *
     * @return void
     */
    private function copyPublic(): void
    {
        $newDirectory = $this->ask('Path to deploy to:');

        if (! is_dir(base_path() . '/../public_html/' . $newDirectory)) {
            try {
                mkdir(base_path() . '/../public_html/' . $newDirectory, 0755, true);
            } catch (Exception) {
                $this->error("The directory public_html/$newDirectory could not be created.");
                $this->error('Skipping deployment');

                exit();
            }
        }

        if (! is_readable(base_path() . '/../public_html/' . $newDirectory)) {
            $this->error("The directory public_html/$newDirectory is not readable");
            $this->error('Skipping deployment');

            exit();
        }

        if (! is_writable(base_path() . '/../public_html/' . $newDirectory)) {
            $this->error("The directory public_html/$newDirectory is not writable");
            $this->error('Skipping deployment');

            exit();
        }

        if (count(scandir(base_path() . '/../public_html/' . $newDirectory)) > 2) {
            $this->error("The directory public_html/$newDirectory is not empty.");
            $this->error('Skipping deployment');

            exit();
        }

        $this->info('Copying public files to public_html/' . $newDirectory);

        $this->copyDirectory(base_path() . '/public', base_path() . '/../public_html/' . $newDirectory);

        $appName = basename(base_path());
        $newDirectory = realpath(base_path() . '/../public_html/' . $newDirectory);
        $upCount = 1;

        while (basename(dirname($newDirectory)) != 'public_html') {
            $newDirectory = realpath($newDirectory . '/../');
            $upCount++;
            sleep(1);
        }

        $vendorAutoloadPath = '/../' . str_repeat('../', $upCount) . $appName . '/vendor/autoload.php';
        $bootstrapPath = '/../' . str_repeat('../', $upCount) . $appName . '/bootstrap/app.php';
        file_put_contents(base_path() . '/public/.production.json', json_encode([
            'autoload' => $vendorAutoloadPath,
            'bootstrap' => $bootstrapPath,
        ]));

        $this->info('Public files copied');
    }

    private function refreshDatabase(): void
    {
        if (! is_file(database_path() . '/database.sqlite') || $this->confirm('Do you want to refresh the database?')) {
            $this->info('Refreshing database...');
            $this->call('migrate', ['--force' => true]);
            $this->call('db:seed');
            $this->info('Database refreshed');
        } else {
            $this->info('Database refresh skipped');
        }

    }

    private function copyDirectory(string $source, string $destination): void
    {
        $dir = opendir($source);
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    $this->copyDirectory($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
