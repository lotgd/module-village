<?php
declare(strict_types=1);

namespace LotGD\Module\Village\SceneTemplates;

use LotGD\Core\SceneTemplates\SceneTemplateInterface;
use LotGD\Module\Village\Module;

class VillageScene implements SceneTemplateInterface
{
    public static function getNavigationEvent(): string
    {
        return Module::VillageScene;
    }
}