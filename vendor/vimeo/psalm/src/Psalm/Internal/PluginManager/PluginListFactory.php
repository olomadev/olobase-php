<?php

namespace Psalm\Internal\PluginManager;

use Psalm\Internal\Composer;
use RuntimeException;

use function array_filter;
use function json_encode;
use function urlencode;

use const JSON_THROW_ON_ERROR;

/**
 * @internal
 */
final class PluginListFactory
{
    private string $ROOT;

    private string $psalm_root;

    public function __construct(string $ROOT, string $psalm_root)
    {
        $this->ROOT = $ROOT;
        $this->psalm_root = $psalm_root;
    }

    public function __invoke(string $current_dir, ?string $config_file_path = null): PluginList
    {
        try {
            $config_file = new ConfigFile($current_dir, $config_file_path);
        } catch (RuntimeException $exception) {
            $config_file = null;
        }
        $composer_lock = new ComposerLock($this->findLockFiles());

        return new PluginList($config_file, $composer_lock);
    }

    /** @return non-empty-array<int,string> */
    private function findLockFiles(): array
    {
        // use cases
        // 1. plugins are installed into project vendors - composer.lock is ROOT/composer.lock
        // 2. plugins are installed into separate composer environment (either global or bamarni-bin)
        //  - composer.lock is PSALM_ROOT/../../../composer.lock
        // 3. plugins are installed into psalm vendors - composer.lock is PSALM_ROOT/composer.lock
        // 4. none of the above - use stub (empty virtual composer.lock)

        if ($this->psalm_root === $this->ROOT) {
            // managing plugins for psalm itself
            $composer_lock_filenames = [
                Composer::getLockFilePath($this->psalm_root),
            ];
        } else {
            $composer_lock_filenames = [
                Composer::getLockFilePath($this->ROOT),
                Composer::getLockFilePath($this->psalm_root . '/../../..'),
                Composer::getLockFilePath($this->psalm_root),
            ];
        }

        $composer_lock_filenames = array_filter($composer_lock_filenames, 'is_readable');

        if (empty($composer_lock_filenames)) {
            $stub_composer_lock = (object)[
                'packages' => [],
                'packages-dev' => [],
            ];
            $composer_lock_filenames[] = 'data:application/json,'
                . urlencode(json_encode($stub_composer_lock, JSON_THROW_ON_ERROR));
        }

        return $composer_lock_filenames;
    }
}
