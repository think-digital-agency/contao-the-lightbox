<?php

declare(strict_types=1);

namespace ThinkDigital\ContaoTheLightbox\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Removes the legacy `js_glightbox` entry from `tl_layout.scripts`.
 *
 * A previous lightbox extension was activated by adding its `js_glightbox`
 * JavaScript template to every page layout. Once that extension is gone the
 * template no longer exists and Contao throws while compiling the layout, so
 * the reference has to be stripped from every layout that still carries it.
 *
 * Contao The Lightbox needs no layout entry — RegisterLightboxAssetsListener
 * wires the assets up on `generatePage`.
 *
 * Key-matched (by string value), not id-based: customer layouts have arbitrary
 * ids but the same serialized `js_glightbox` value (ADR-11). Idempotent via the
 * shouldRun() LIKE guard.
 */
class RemoveGLightboxLayoutScriptMigration extends AbstractMigration
{
    private const LEGACY_SCRIPT = 'js_glightbox';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_layout'])) {
            return false;
        }

        return (bool) $this->connection->fetchOne(
            "SELECT TRUE FROM tl_layout WHERE scripts LIKE '%\"js_glightbox\"%' LIMIT 1",
        );
    }

    public function run(): MigrationResult
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, scripts FROM tl_layout WHERE scripts LIKE '%\"js_glightbox\"%'",
        );

        $changed = 0;

        foreach ($rows as $row) {
            $scripts = StringUtil::deserialize($row['scripts'], true);
            $filtered = array_values(array_filter(
                $scripts,
                static fn ($value): bool => self::LEGACY_SCRIPT !== $value,
            ));

            if ($filtered === $scripts) {
                continue;
            }

            $this->connection->update(
                'tl_layout',
                ['scripts' => serialize($filtered)],
                ['id' => $row['id']],
            );

            ++$changed;
        }

        return $this->createResult(
            true,
            sprintf('Removed the legacy "js_glightbox" layout script from %d layout(s).', $changed),
        );
    }
}
