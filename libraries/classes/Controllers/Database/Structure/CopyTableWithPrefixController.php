<?php

declare(strict_types=1);

namespace PhpMyAdmin\Controllers\Database\Structure;

use PhpMyAdmin\Controllers\AbstractController;
use PhpMyAdmin\Controllers\Database\StructureController;
use PhpMyAdmin\Http\ServerRequest;
use PhpMyAdmin\Message;
use PhpMyAdmin\ResponseRenderer;
use PhpMyAdmin\Table;
use PhpMyAdmin\Template;

use function count;
use function mb_strlen;
use function mb_substr;

final class CopyTableWithPrefixController extends AbstractController
{
    /** @var StructureController */
    private $structureController;

    public function __construct(
        ResponseRenderer $response,
        Template $template,
        StructureController $structureController
    ) {
        parent::__construct($response, $template);
        $this->structureController = $structureController;
    }

    public function __invoke(ServerRequest $request): void
    {
        $selected = $_POST['selected'] ?? [];
        $fromPrefix = $_POST['from_prefix'] ?? null;
        $toPrefix = $_POST['to_prefix'] ?? null;

        $selectedCount = count($selected);

        for ($i = 0; $i < $selectedCount; $i++) {
            $current = $selected[$i];
            $newTableName = $toPrefix . mb_substr($current, mb_strlen((string) $fromPrefix));

            Table::moveCopy(
                $GLOBALS['db'],
                $current,
                $GLOBALS['db'],
                $newTableName,
                'data',
                false,
                'one_table',
                isset($_POST['drop_if_exists']) && $_POST['drop_if_exists'] === 'true'
            );
        }

        $GLOBALS['message'] = Message::success();

        ($this->structureController)($request);
    }
}
